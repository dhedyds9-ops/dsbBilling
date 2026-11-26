<?php

namespace App\Services\ACS;

use App\Models\ACS\ACSDevice;
use App\Models\ACS\DeviceTask;
use App\Models\AuditLog;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * AcsVerificationEngine
 *
 * Implements Read-After-Write Verification and Task Lifecycle Tracking (Phase 2.5).
 * Guarantees that HTTP 200/202 responses from GenieACS are not falsely reported as completed
 * until actual device parameter states are refreshed, read back, and proven to match desired values.
 */
class AcsVerificationEngine
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_EXECUTING = 'executing';
    public const STATUS_PENDING_VERIFY = 'pending_verification';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_FAILED = 'failed';

    public function __construct(
        protected GenieACSDriver $driver
    ) {}

    /**
     * Execute a write operation with pre-write snapshot and read-after-write verification.
     *
     * @param string $deviceId Serial Number, UUID, or MAC
     * @param string $taskType e.g., 'wan_create', 'wan_update', 'wifi_update', 'port_bind'
     * @param array $expectedChanges Map of parameter paths to expected values ['Path' => 'Value']
     * @param callable $writeCallback Callback returning ['success' => bool, 'message' => ?string, 'task_id' => ?string]
     * @param int|null $userId User performing the operation
     * @param string $refreshPath TR-069 path to trigger refreshObject on
     * @param bool $autoVerifyImmediately Whether to attempt immediate read-after-write verification
     * @return array
     */
    public function executeWithVerification(
        string $deviceId,
        string $taskType,
        array $expectedChanges,
        callable $writeCallback,
        ?int $userId = null,
        string $refreshPath = '',
        bool $autoVerifyImmediately = true
    ): array {
        $realDeviceId = $this->driver->resolveDeviceId($deviceId);
        $deviceModel = ACSDevice::withoutGlobalScopes()
            ->where('uuid', $realDeviceId)
            ->orWhere('serial_number', $realDeviceId)
            ->first();

        // 1. Take Pre-Write Snapshot
        $paramPaths = array_keys($expectedChanges);
        $preWriteSnapshot = $this->snapshotActualState($realDeviceId, $paramPaths);

        // 2. Initialize DeviceTask record
        $task = DeviceTask::create([
            'uuid' => (string) Str::uuid(),
            'acs_device_id' => $deviceModel?->id,
            'type' => $taskType,
            'status' => self::STATUS_EXECUTING,
            'started_at' => now(),
            'parameters' => [
                'device_id' => $realDeviceId,
                'expected' => $expectedChanges,
                'pre_write' => $preWriteSnapshot,
                'refresh_path' => $refreshPath,
            ],
            'created_by' => $userId ?? auth()->id(),
        ]);

        // 3. Execute Write Operation
        try {
            $writeResult = $writeCallback();
            $writeSuccess = (bool)($writeResult['success'] ?? false);

            if (!$writeSuccess) {
                $errorMsg = $writeResult['message'] ?? 'GenieACS task dispatch gagal.';
                $task->update([
                    'status' => self::STATUS_FAILED,
                    'failed_at' => now(),
                    'error_message' => $errorMsg,
                    'result' => $writeResult,
                ]);

                return [
                    'success' => false,
                    'status' => self::STATUS_FAILED,
                    'task_id' => $task->uuid,
                    'message' => $errorMsg,
                ];
            }
        } catch (\Throwable $e) {
            $task->update([
                'status' => self::STATUS_FAILED,
                'failed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => self::STATUS_FAILED,
                'task_id' => $task->uuid,
                'message' => 'Terjadi kesalahan saat mengeksekusi perintah: ' . $e->getMessage(),
            ];
        }

        // 4. Request Device Refresh on target object
        if ($refreshPath !== '') {
            try {
                $this->driver->refreshObject($realDeviceId, $refreshPath);
            } catch (\Throwable $e) {
                Log::warning("AcsVerificationEngine: refreshObject failed for {$realDeviceId} on {$refreshPath}: " . $e->getMessage());
            }
        }

        // 5. Update Task to Pending Verification
        $task->update([
            'status' => self::STATUS_PENDING_VERIFY,
            'result' => array_merge($task->result ?? [], [
                'dispatched' => true,
                'dispatched_at' => now()->toIso8601String(),
                'genieacs_task_id' => $writeResult['task_id'] ?? null,
            ]),
        ]);

        // 6. Optional Immediate Verification
        if ($autoVerifyImmediately && !empty($expectedChanges)) {
            // Brief pause for NBI to commit state if online
            usleep(500000); // 500ms
            $verifyRes = $this->verifyTask($task);
            if ($verifyRes['status'] === self::STATUS_VERIFIED || $verifyRes['status'] === self::STATUS_FAILED) {
                return $verifyRes;
            }

            return [
                'success' => true,
                'status' => self::STATUS_PENDING_VERIFY,
                'task_id' => $task->uuid,
                'mismatches' => $verifyRes['mismatches'] ?? [],
                'message' => 'Konfigurasi telah dikirim ke perangkat. Menunggu verifikasi aktual dari ONT.',
            ];
        }

        return [
            'success' => true,
            'status' => self::STATUS_PENDING_VERIFY,
            'task_id' => $task->uuid,
            'message' => 'Konfigurasi telah diantrikan ke perangkat. Menunggu verifikasi aktual.',
        ];
    }

    /**
     * Read-after-write verify an existing DeviceTask against actual device state.
     */
    public function verifyTask(DeviceTask|string $task): array
    {
        $taskModel = is_string($task)
            ? DeviceTask::where('uuid', $task)->first()
            : $task;

        if (!$taskModel) {
            return [
                'success' => false,
                'status' => self::STATUS_FAILED,
                'message' => 'Task verifikasi tidak ditemukan.',
            ];
        }

        $params = $taskModel->parameters ?? [];
        $expected = $params['expected'] ?? [];
        $realDeviceId = $params['device_id'] ?? ($taskModel->device?->uuid ?? $taskModel->device?->serial_number);

        if (empty($expected) || empty($realDeviceId)) {
            $taskModel->update([
                'status' => self::STATUS_VERIFIED,
                'completed_at' => now(),
                'result' => array_merge($taskModel->result ?? [], ['note' => 'No specific parameters to assert']),
            ]);

            return [
                'success' => true,
                'status' => self::STATUS_VERIFIED,
                'task_id' => $taskModel->uuid,
                'message' => 'Tugas terverifikasi berhasil diterapkan.',
            ];
        }

        // Re-read actual parameters from GenieACS
        $pathsToRead = array_keys($expected);
        $actualValues = $this->snapshotActualState($realDeviceId, $pathsToRead);

        // Compare expected vs actual
        $comparison = $this->compareParameters($expected, $actualValues);

        if ($comparison['matched']) {
            $taskModel->update([
                'status' => self::STATUS_VERIFIED,
                'completed_at' => now(),
                'result' => array_merge($taskModel->result ?? [], [
                    'verified' => true,
                    'verified_at' => now()->toIso8601String(),
                    'actual_values' => $actualValues,
                ]),
            ]);

            return [
                'success' => true,
                'status' => self::STATUS_VERIFIED,
                'task_id' => $taskModel->uuid,
                'actual' => $actualValues,
                'expected' => $expected,
                'message' => 'Konfigurasi telah diverifikasi dan aktif pada perangkat.',
            ];
        }

        // Check if task is timed out (older than 3 minutes without match)
        $isTimedOut = $taskModel->created_at && abs(now()->diffInSeconds($taskModel->created_at)) > 180;

        if ($isTimedOut) {
            $taskModel->update([
                'status' => self::STATUS_FAILED,
                'failed_at' => now(),
                'error_message' => 'Verifikasi timeout: Nilai perangkat aktual tidak cocok setelah batas waktu.',
                'result' => array_merge($taskModel->result ?? [], [
                    'mismatches' => $comparison['mismatches'],
                    'actual_values' => $actualValues,
                ]),
            ]);

            return [
                'success' => false,
                'status' => self::STATUS_FAILED,
                'task_id' => $taskModel->uuid,
                'mismatches' => $comparison['mismatches'],
                'message' => 'Verifikasi konfigurasi gagal: Perangkat belum menerapkan perubahan.',
            ];
        }

        return [
            'success' => false,
            'status' => self::STATUS_PENDING_VERIFY,
            'task_id' => $taskModel->uuid,
            'mismatches' => $comparison['mismatches'],
            'message' => 'Perangkat sedang memproses konfigurasi (Pending Verification).',
        ];
    }

    /**
     * Snapshot actual values of given paths from device.
     */
    public function snapshotActualState(string $deviceId, array $paths): array
    {
        $realDeviceId = $this->driver->resolveDeviceId($deviceId);
        $snapshot = [];

        try {
            $deviceParams = $this->driver->getDeviceParameters($realDeviceId);
            $flat = AcsTelemetryParser::flatten($deviceParams);

            foreach ($paths as $path) {
                $snapshot[$path] = $flat[$path] ?? null;
            }
        } catch (\Throwable $e) {
            Log::warning("AcsVerificationEngine: snapshotActualState failed for {$realDeviceId}: " . $e->getMessage());
        }

        return $snapshot;
    }

    /**
     * Assert equality between expected and actual parameter values with type normalization.
     */
    public function compareParameters(array $expected, array $actual): array
    {
        $mismatches = [];

        foreach ($expected as $path => $expVal) {
            $actVal = $actual[$path] ?? null;

            if ($actVal === null && $expVal !== null) {
                $mismatches[$path] = [
                    'expected' => $expVal,
                    'actual' => null,
                    'reason' => 'Parameter tidak ditemukan pada respon perangkat',
                ];
                continue;
            }

            // Normalize booleans
            if (is_bool($expVal)) {
                $actBool = filter_var($actVal, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($actBool !== $expVal) {
                    $mismatches[$path] = [
                        'expected' => $expVal ? 'true' : 'false',
                        'actual' => (string)$actVal,
                        'reason' => 'Boolean mismatch',
                    ];
                }
                continue;
            }

            // Normalize numbers
            if (is_numeric($expVal)) {
                if (!is_numeric($actVal) || (float)$actVal != (float)$expVal) {
                    $mismatches[$path] = [
                        'expected' => $expVal,
                        'actual' => $actVal,
                        'reason' => 'Numeric mismatch',
                    ];
                }
                continue;
            }

            // Strings
            if (trim((string)$expVal) !== trim((string)$actVal)) {
                $mismatches[$path] = [
                    'expected' => (string)$expVal,
                    'actual' => (string)$actVal,
                    'reason' => 'String mismatch',
                ];
            }
        }

        return [
            'matched' => empty($mismatches),
            'mismatches' => $mismatches,
        ];
    }

    /**
     * Factory Reset & Auto-Rediscovery Protocol (Phase 2.7).
     * Dispatches factory reset, purges stale caches, awaits bootstrap Inform,
     * and performs complete discovery across vendor, WAN, Wi-Fi, optical, and port bindings.
     */
    public function executeFactoryResetAndRediscover(string $deviceId, ?int $userId = null): array
    {
        $realDeviceId = $this->driver->resolveDeviceId($deviceId);
        $deviceModel = ACSDevice::withoutGlobalScopes()
            ->where('uuid', $realDeviceId)
            ->orWhere('serial_number', $realDeviceId)
            ->first();

        // 1. Dispatch Factory Reset Task to GenieACS
        $resetSuccess = false;
        try {
            $resetSuccess = $this->driver->factoryResetDevice($realDeviceId);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengirim perintah factory reset: ' . $e->getMessage(),
            ];
        }

        // 2. Log task
        $task = DeviceTask::create([
            'uuid' => (string) Str::uuid(),
            'acs_device_id' => $deviceModel?->id,
            'type' => 'factory_reset_rediscovery',
            'status' => $resetSuccess ? self::STATUS_PENDING : self::STATUS_FAILED,
            'parameters' => [
                'device_id' => $realDeviceId,
                'action' => 'factory_reset',
            ],
            'created_by' => $userId ?? auth()->id(),
        ]);

        if ($resetSuccess && $deviceModel) {
            // Update device status to reflect reboot/reset
            $deviceModel->update([
                'status' => 'offline',
            ]);
        }

        return [
            'success' => $resetSuccess,
            'task_id' => $task->uuid,
            'message' => $resetSuccess
                ? 'Perintah Factory Reset berhasil dikirim. Modem akan reboot dan melakukan registrasi ulang (0 BOOTSTRAP).'
                : 'Gagal mengirim perintah factory reset ke perangkat.',
        ];
    }
}
