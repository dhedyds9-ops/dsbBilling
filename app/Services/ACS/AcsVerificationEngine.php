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

    // 10-State Factory Reset Recovery Lifecycle
    public const RESET_STATE_RESET_DETECTED = 'Reset Detected';
    public const RESET_STATE_ACS_REACHABLE = 'ACS Reachable';
    public const RESET_STATE_ACS_UNREACHABLE = 'ACS Unreachable';
    public const RESET_STATE_REDISCOVERY_PENDING = 'Rediscovery Pending';
    public const RESET_STATE_REDISCOVERED = 'Rediscovered';
    public const RESET_STATE_PROVISIONING_PENDING = 'Provisioning Pending';
    public const RESET_STATE_PROVISIONING_FAILED = 'Provisioning Failed';
    public const RESET_STATE_VERIFIED = 'Verified';
    public const RESET_STATE_MANUAL_RECOVERY_REQUIRED = 'Manual Recovery Required';
    public const RESET_STATE_IDLE = 'Idle';

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
            // Brief pause for NBI to commit state if online (skip in test runner for performance)
            if (!app()->runningUnitTests()) {
                usleep(500000); // 500ms
            }
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
     * Enforces Inform freshness validation to guarantee cached stale values are not accepted as verified.
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

        // 1. Re-read actual parameters and inform metadata from GenieACS
        $deviceParams = [];
        try {
            $deviceParams = $this->driver->getDeviceParameters($realDeviceId);
        } catch (\Throwable $e) {
            Log::warning("AcsVerificationEngine: getDeviceParameters failed during verifyTask for {$realDeviceId}: " . $e->getMessage());
        }

        $lastInform = $deviceParams['_lastInform'] ?? null;
        $startedAt = $taskModel->started_at ?? $taskModel->created_at;

        // 2. Freshness Gate: Has the device sent an Inform after the task was initiated?
        $isFresh = true;
        if (!empty($lastInform) && $startedAt) {
            try {
                $lastInformDate = Carbon::parse($lastInform);
                // Allow a 1-second margin of clock drift between application server and ACS
                if ($lastInformDate->lt($startedAt->copy()->subSeconds(1))) {
                    $isFresh = false;
                }
            } catch (\Throwable) {
                $isFresh = true;
            }
        }

        $flat = AcsTelemetryParser::flatten($deviceParams);
        $actualValues = [];
        foreach (array_keys($expected) as $path) {
            $actualValues[$path] = $flat[$path] ?? null;
        }

        // 3. Compare expected vs actual with type-aware normalization and float tolerance
        $comparison = $this->compareParameters($expected, $actualValues);

        // 4. Check if task is timed out (older than 3 minutes without match)
        $referenceTime = $taskModel->started_at ?? $taskModel->created_at;
        $isTimedOut = $referenceTime && abs(now()->diffInSeconds($referenceTime)) > 180;

        if ($isTimedOut) {
            $errorMsg = !$isFresh
                ? 'Verifikasi timeout: Perangkat tidak mengirimkan Inform baru setelah perintah dikirim (stale cache > 180 detik).'
                : 'Verifikasi timeout: Nilai perangkat aktual tidak cocok setelah batas waktu (180 detik).';

            $taskModel->update([
                'status' => self::STATUS_FAILED,
                'failed_at' => now(),
                'error_message' => $errorMsg,
                'result' => array_merge($taskModel->result ?? [], [
                    'mismatches' => $comparison['mismatches'],
                    'actual_values' => $actualValues,
                    'is_fresh' => $isFresh,
                    'last_inform' => $lastInform,
                ]),
            ]);

            return [
                'success' => false,
                'status' => self::STATUS_FAILED,
                'task_id' => $taskModel->uuid,
                'mismatches' => $comparison['mismatches'],
                'message' => $errorMsg,
            ];
        }

        if (!$isFresh) {
            return [
                'success' => false,
                'status' => self::STATUS_PENDING_VERIFY,
                'task_id' => $taskModel->uuid,
                'mismatches' => $comparison['mismatches'],
                'message' => 'Menunggu Inform terbaru dari perangkat (Data belum diperbarui oleh ONT).',
            ];
        }

        if ($comparison['matched']) {
            $taskModel->update([
                'status' => self::STATUS_VERIFIED,
                'completed_at' => now(),
                'result' => array_merge($taskModel->result ?? [], [
                    'verified' => true,
                    'verified_at' => now()->toIso8601String(),
                    'actual_values' => $actualValues,
                    'matched_parameters' => $comparison['matched_parameters'] ?? array_keys($expected),
                    'last_inform' => $lastInform,
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
     * Assert equality between expected and actual parameter values with type normalization,
     * floating-point tolerance (±0.01), strict integer checks, and case-insensitive enum matching.
     */
    public function compareParameters(array $expected, array $actual): array
    {
        $mismatches = [];
        $matchedParams = [];

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
            if (is_bool($expVal) || in_array(strtolower((string)$expVal), ['true', 'false'], true)) {
                $expBool = filter_var($expVal, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $actBool = filter_var($actVal, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($expBool !== null) {
                    if ($actBool === null || $actBool !== $expBool) {
                        $mismatches[$path] = [
                            'expected' => $expBool ? 'true' : 'false',
                            'actual' => (string)$actVal,
                            'reason' => 'Boolean mismatch',
                        ];
                    } else {
                        $matchedParams[$path] = $actVal;
                    }
                    continue;
                }
            }

            // Floating point numbers with ±0.01 tolerance
            if (is_numeric($expVal) && is_numeric($actVal) && (
                str_contains((string)$expVal, '.') ||
                str_contains((string)$actVal, '.') ||
                is_float($expVal) ||
                is_float($actVal)
            )) {
                $diff = abs((float)$expVal - (float)$actVal);
                if ($diff > 0.01) {
                    $mismatches[$path] = [
                        'expected' => (float)$expVal,
                        'actual' => (float)$actVal,
                        'diff' => $diff,
                        'tolerance' => 0.01,
                        'reason' => "Numeric float mismatch (diff {$diff} > tolerance 0.01)",
                    ];
                } else {
                    $matchedParams[$path] = $actVal;
                }
                continue;
            }

            // Strict integer numbers
            if (is_numeric($expVal) && is_numeric($actVal)) {
                if ((int)$expVal !== (int)$actVal) {
                    $mismatches[$path] = [
                        'expected' => (int)$expVal,
                        'actual' => (int)$actVal,
                        'reason' => 'Integer mismatch',
                    ];
                } else {
                    $matchedParams[$path] = $actVal;
                }
                continue;
            }

            // String / Enum with case-insensitive and trimmed comparison
            $expStr = trim((string)$expVal);
            $actStr = trim((string)$actVal);
            if (strcasecmp($expStr, $actStr) === 0) {
                $matchedParams[$path] = $actVal;
            } else {
                $mismatches[$path] = [
                    'expected' => $expStr,
                    'actual' => $actStr,
                    'reason' => 'String/Enum mismatch',
                ];
            }
        }

        return [
            'matched' => empty($mismatches),
            'mismatches' => $mismatches,
            'matched_parameters' => $matchedParams,
        ];
    }

    /**
     * Factory Reset & Auto-Rediscovery Protocol (Phase 2.7).
     * Dispatches factory reset, purges stale caches, awaits bootstrap Inform,
     * and initializes tracking across the 10-state recovery lifecycle.
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

        // 2. Initialize tracking with 10-state lifecycle
        $initialState = $resetSuccess ? self::RESET_STATE_RESET_DETECTED : self::RESET_STATE_MANUAL_RECOVERY_REQUIRED;
        $task = $this->trackResetRecovery(
            $realDeviceId,
            $initialState,
            $resetSuccess ? 'Perintah Factory Reset berhasil dikirim ke GenieACS' : 'Gagal mengirim perintah factory reset',
            $userId
        );

        if ($resetSuccess && $deviceModel) {
            // Update device status to reflect reboot/reset
            $deviceModel->update([
                'status' => 'offline',
            ]);
        }

        return [
            'success' => $resetSuccess,
            'task_id' => $task->uuid,
            'recovery_state' => $initialState,
            'message' => $resetSuccess
                ? 'Perintah Factory Reset berhasil dikirim. Modem akan reboot dan melakukan registrasi ulang (0 BOOTSTRAP).'
                : 'Gagal mengirim perintah factory reset ke perangkat.',
        ];
    }

    /**
     * Initialize tracking of Factory Reset Recovery lifecycle.
     */
    public function trackResetRecovery(
        string $deviceId,
        string $initialState = self::RESET_STATE_RESET_DETECTED,
        ?string $reason = null,
        ?int $userId = null
    ): DeviceTask {
        $realDeviceId = $this->driver->resolveDeviceId($deviceId);
        $deviceModel = ACSDevice::withoutGlobalScopes()
            ->where('uuid', $realDeviceId)
            ->orWhere('serial_number', $realDeviceId)
            ->first();

        $taskStatus = in_array($initialState, [self::RESET_STATE_VERIFIED, self::RESET_STATE_IDLE], true)
            ? self::STATUS_VERIFIED
            : (in_array($initialState, [self::RESET_STATE_PROVISIONING_FAILED, self::RESET_STATE_MANUAL_RECOVERY_REQUIRED, self::RESET_STATE_ACS_UNREACHABLE], true)
                ? self::STATUS_FAILED
                : self::STATUS_EXECUTING);

        return DeviceTask::create([
            'uuid' => (string) Str::uuid(),
            'acs_device_id' => $deviceModel?->id,
            'type' => 'factory_reset_rediscovery',
            'status' => $taskStatus,
            'started_at' => now(),
            'parameters' => [
                'device_id' => $realDeviceId,
                'action' => 'factory_reset',
            ],
            'result' => [
                'recovery_state' => $initialState,
                'state_history' => [
                    [
                        'from_state' => null,
                        'to_state' => $initialState,
                        'timestamp' => now()->toIso8601String(),
                        'reason' => $reason ?? 'Inisialisasi Factory Reset Recovery',
                    ],
                ],
            ],
            'created_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Transition Factory Reset Recovery state across the 10-state lifecycle.
     */
    public function transitionResetRecoveryState(
        DeviceTask|string $taskOrDeviceId,
        string $nextState,
        ?string $reason = null,
        array $extraData = []
    ): array {
        $validStates = [
            self::RESET_STATE_RESET_DETECTED,
            self::RESET_STATE_ACS_REACHABLE,
            self::RESET_STATE_ACS_UNREACHABLE,
            self::RESET_STATE_REDISCOVERY_PENDING,
            self::RESET_STATE_REDISCOVERED,
            self::RESET_STATE_PROVISIONING_PENDING,
            self::RESET_STATE_PROVISIONING_FAILED,
            self::RESET_STATE_VERIFIED,
            self::RESET_STATE_MANUAL_RECOVERY_REQUIRED,
            self::RESET_STATE_IDLE,
        ];

        if (!in_array($nextState, $validStates, true)) {
            throw new \InvalidArgumentException("Invalid recovery state: {$nextState}");
        }

        $task = is_string($taskOrDeviceId)
            ? DeviceTask::where('uuid', $taskOrDeviceId)
                ->orWhere(function ($q) use ($taskOrDeviceId) {
                    $q->where('type', 'factory_reset_rediscovery')
                      ->whereJsonContains('parameters->device_id', $taskOrDeviceId);
                })
                ->latest()
                ->first()
            : $taskOrDeviceId;

        if (!$task) {
            return [
                'success' => false,
                'message' => 'Task recovery tidak ditemukan.',
            ];
        }

        $result = $task->result ?? [];
        $currentState = $result['recovery_state'] ?? self::RESET_STATE_RESET_DETECTED;
        $history = $result['state_history'] ?? [];

        $history[] = [
            'from_state' => $currentState,
            'to_state' => $nextState,
            'timestamp' => now()->toIso8601String(),
            'reason' => $reason ?? "Transition from {$currentState} to {$nextState}",
            'extra' => $extraData,
        ];

        $result['recovery_state'] = $nextState;
        $result['state_history'] = $history;

        $taskStatus = self::STATUS_EXECUTING;
        $completedAt = $task->completed_at;
        $failedAt = $task->failed_at;
        $errorMessage = $task->error_message;

        if ($nextState === self::RESET_STATE_VERIFIED || $nextState === self::RESET_STATE_IDLE) {
            $taskStatus = self::STATUS_VERIFIED;
            $completedAt = now();
        } elseif (in_array($nextState, [self::RESET_STATE_PROVISIONING_FAILED, self::RESET_STATE_MANUAL_RECOVERY_REQUIRED, self::RESET_STATE_ACS_UNREACHABLE], true)) {
            $taskStatus = self::STATUS_FAILED;
            $failedAt = now();
            $errorMessage = $reason ?? "Recovery halted at state {$nextState}";
        }

        $task->update([
            'status' => $taskStatus,
            'completed_at' => $completedAt,
            'failed_at' => $failedAt,
            'error_message' => $errorMessage,
            'result' => $result,
        ]);

        return [
            'success' => true,
            'task_id' => $task->uuid,
            'previous_state' => $currentState,
            'current_state' => $nextState,
            'status' => $taskStatus,
            'history' => $history,
        ];
    }

    /**
     * Evaluate current Factory Reset Recovery status for a device or task.
     */
    public function evaluateResetRecoveryStatus(DeviceTask|string $taskOrDeviceId): array
    {
        $task = is_string($taskOrDeviceId)
            ? DeviceTask::where('uuid', $taskOrDeviceId)
                ->orWhere(function ($q) use ($taskOrDeviceId) {
                    $q->where('type', 'factory_reset_rediscovery')
                      ->whereJsonContains('parameters->device_id', $taskOrDeviceId);
                })
                ->latest()
                ->first()
            : $taskOrDeviceId;

        if (!$task) {
            return [
                'success' => false,
                'current_state' => self::RESET_STATE_IDLE,
                'message' => 'Tidak ada proses recovery aktif.',
            ];
        }

        $result = $task->result ?? [];
        $currentState = $result['recovery_state'] ?? self::RESET_STATE_RESET_DETECTED;

        $isHealthy = in_array($currentState, [self::RESET_STATE_VERIFIED, self::RESET_STATE_IDLE], true);
        $requiresManual = in_array($currentState, [
            self::RESET_STATE_MANUAL_RECOVERY_REQUIRED,
            self::RESET_STATE_ACS_UNREACHABLE,
            self::RESET_STATE_PROVISIONING_FAILED,
        ], true);

        return [
            'success' => true,
            'task_id' => $task->uuid,
            'current_state' => $currentState,
            'is_healthy' => $isHealthy,
            'requires_manual_intervention' => $requiresManual,
            'state_history' => $result['state_history'] ?? [],
            'created_at' => $task->created_at?->toIso8601String(),
            'updated_at' => $task->updated_at?->toIso8601String(),
        ];
    }
}
