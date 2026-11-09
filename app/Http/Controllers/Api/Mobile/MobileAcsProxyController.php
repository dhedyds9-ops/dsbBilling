<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MobileAcsProxyController extends Controller
{
    public function __construct(
        protected GenieACSDriver $driver
    ) {}

    /**
     * Check if authenticated staff has permission to access ACS operations.
     */
    protected function authorizeAcsAccess(Request $request): ?JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $isAuthorized = $user->isSuperAdmin()
            || $user->hasRole(['noc', 'teknisi', 'administrator', 'manager', 'reseller'])
            || in_array(strtoupper($user->job_function ?? ''), ['TECHNICIAN', 'TEKNISI', 'NOC', 'ADMINISTRATOR', 'MANAGER', 'RESELLER'])
            || str_contains(strtoupper($user->job_title ?? ''), 'NOC')
            || str_contains(strtoupper($user->job_title ?? ''), 'TEKNISI')
            || str_contains(strtoupper($user->job_title ?? ''), 'RESELLER');

        if (!$isAuthorized) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengelola jaringan TR-069.',
            ], 403);
        }

        return null;
    }

    /**
     * Proxy List Devices from GenieACS.
     */
    public function listDevices(Request $request): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        try {
            $limit = (int) $request->input('limit', 100);
            $devices = $this->driver->listDevices([], $limit);

            $user = $request->user();
            if ($user && $user->hasRole('reseller')) {
                $allowedSns = \App\Models\ACS\ACSDevice::forReseller($user)
                    ->pluck('serial_number')
                    ->map(fn($s) => strtoupper(trim((string) $s)))
                    ->filter()
                    ->toArray();

                $devices = array_values(array_filter($devices, function ($dev) use ($allowedSns) {
                    $devSn = strtoupper(trim((string) ($dev['serial_number'] ?? ($dev['_id'] ?? ''))));
                    foreach ($allowedSns as $sn) {
                        if ($sn !== '' && (str_contains($devSn, $sn) || str_contains($sn, $devSn))) {
                            return true;
                        }
                    }
                    return false;
                }));
            }

            return response()->json([
                'success' => true,
                'data' => $devices,
            ]);
        } catch (Exception $e) {
            Log::error("MobileAcsProxy listDevices error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat perangkat dari ACS: ' . $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Proxy Get Device Parameters.
     */
    public function deviceDetail(Request $request, string $id): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        try {
            $cleanId = urldecode($id);
            $params = $this->driver->getDeviceParameters($cleanId);

            if (empty($params)) {
                return response()->json([
                    'success' => false,
                    'message' => "Perangkat dengan ID '{$cleanId}' tidak ditemukan di ACS.",
                ], 404);
            }

            // Extract connected devices if possible
            $connectedDevices = [];
            try {
                $connectedDevices = $this->driver->getConnectedDevices($cleanId);
            } catch (\Throwable) {}

            return response()->json([
                'success' => true,
                'data' => $params,
                'connected_devices' => $connectedDevices,
            ]);
        } catch (Exception $e) {
            Log::error("MobileAcsProxy deviceDetail error for {$id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail perangkat: ' . $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Proxy Summon Device (live refreshObject via connection request).
     */
    public function summon(Request $request, string $id): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        try {
            $cleanId = urldecode($id);
            $success = $this->driver->summonDeviceResult($cleanId);

            // Audit log
            $user = $request->user();
            Log::info("ACS Summon: User #{$user->id} ({$user->name}) summoned device {$cleanId}.");

            return response()->json([
                'success' => $success,
                'message' => $success ? "Permintaan koneksi (Summon) berhasil dikirim ke perangkat." : "Perangkat tidak merespons summon.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim summon: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Proxy Reboot Device.
     */
    public function reboot(Request $request, string $id): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        try {
            $cleanId = urldecode($id);
            $user = $request->user();

            if ($user && $user->hasRole('reseller')) {
                $allowedSns = \App\Models\ACS\ACSDevice::forReseller($user)
                    ->pluck('serial_number')
                    ->map(fn($s) => strtoupper(trim((string) $s)))
                    ->filter()
                    ->toArray();

                $cleanUpper = strtoupper($cleanId);
                $matches = false;
                foreach ($allowedSns as $sn) {
                    if ($sn !== '' && (str_contains($cleanUpper, $sn) || str_contains($sn, $cleanUpper))) {
                        $matches = true;
                        break;
                    }
                }

                if (!$matches) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Perangkat ini berada di luar cakupan izin akun Reseller Anda.',
                    ], 403);
                }
            }

            $success = $this->driver->rebootDevice($cleanId);

            Log::info("ACS Reboot: User #{$user->id} ({$user->name}) rebooted device {$cleanId}.");

            try {
                AuditLog::create([
                    'user_id' => $user?->id,
                    'action' => 'acs_reboot',
                    'description' => "Reboot device {$cleanId} via mobile ACS Proxy.",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {}

            return response()->json([
                'success' => $success,
                'message' => $success ? "Perintah restart berhasil dikirim ke perangkat." : "Gagal mengirim perintah restart.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mereboot perangkat: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Proxy Configure Wi-Fi SSID / Password.
     */
    public function setWifi(Request $request, string $id): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        $request->validate([
            'wlan_index' => 'required|integer|min:1',
            'ssid' => 'nullable|string|max:64',
            'password' => 'nullable|string|min:8|max:64',
            'security_mode' => 'nullable|string',
            'enabled' => 'nullable|boolean',
            'hide_ssid' => 'nullable|boolean',
        ]);

        $cleanId = urldecode($id);
        $wlanIndex = (int) $request->input('wlan_index', 1);
        $user = $request->user();

        if ($user && $user->hasRole('reseller')) {
            $allowedSns = \App\Models\ACS\ACSDevice::forReseller($user)
                ->pluck('serial_number')
                ->map(fn($s) => strtoupper(trim((string) $s)))
                ->filter()
                ->toArray();

            $cleanUpper = strtoupper($cleanId);
            $matches = false;
            foreach ($allowedSns as $sn) {
                if ($sn !== '' && (str_contains($cleanUpper, $sn) || str_contains($sn, $cleanUpper))) {
                    $matches = true;
                    break;
                }
            }

            if (!$matches) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perangkat ini berada di luar cakupan izin akun Reseller Anda.',
                ], 403);
            }
        }

        try {
            $kv = [];
            $prefix = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}";

            if ($request->has('ssid')) {
                $ssid = trim($request->input('ssid'));
                $kv["{$prefix}.SSID"] = $ssid;
                if ($wlanIndex === 1) {
                    $kv["VirtualParameters.WlanSsid"] = $ssid;
                }
            }

            if ($request->has('password')) {
                $pass = $request->input('password');
                $kv["{$prefix}.PreSharedKey.1.KeyPassphrase"] = $pass;
                $kv["{$prefix}.KeyPassphrase"] = $pass;
                if ($wlanIndex === 1) {
                    $kv["VirtualParameters.WlanPassword"] = $pass;
                }
            }

            if ($request->has('enabled')) {
                $kv["{$prefix}.Enable"] = $request->boolean('enabled');
            }

            if ($request->has('hide_ssid')) {
                $kv["{$prefix}.SSIDAdvertisementEnabled"] = !$request->boolean('hide_ssid');
            }

            if ($request->has('security_mode')) {
                $sec = $request->input('security_mode');
                if ($sec === 'OPEN') {
                    $kv["{$prefix}.BeaconType"] = 'None';
                } elseif ($sec === 'WPA2') {
                    $kv["{$prefix}.BeaconType"] = '11i';
                } else {
                    $kv["{$prefix}.BeaconType"] = 'WPAand11i';
                }
            }

            if (empty($kv)) {
                return response()->json(['success' => false, 'message' => 'Tidak ada parameter Wi-Fi yang diubah.'], 400);
            }

            $success = $this->driver->setParameterValues($cleanId, $kv);

            Log::info("ACS SetWifi: User #{$user->id} ({$user->name}) updated WiFi index {$wlanIndex} on {$cleanId}.");

            try {
                AuditLog::create([
                    'user_id' => $user?->id,
                    'action' => 'acs_set_wifi',
                    'description' => "Ubah konfigurasi Wi-Fi device {$cleanId} (wlan {$wlanIndex}) via Mobile ACS Proxy.",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {}

            return response()->json([
                'success' => $success,
                'message' => $success ? "Pengaturan Wi-Fi berhasil diterapkan." : "Gagal menerapkan pengaturan Wi-Fi ke perangkat.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah pengaturan Wi-Fi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Proxy Configure WAN Connection.
     */
    public function setWan(Request $request, string $id): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        $request->validate([
            'wan_index' => 'required|integer|min:1',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'vlan_id' => 'nullable|integer',
            'connection_type' => 'nullable|string',
        ]);

        $cleanId = urldecode($id);
        $wanIndex = (int) $request->input('wan_index', 1);
        $user = $request->user();

        try {
            $kv = [];
            $prefix = "InternetGatewayDevice.WANDevice.1.WANConnectionDevice.{$wanIndex}.WANPPPConnection.1";

            if ($request->has('username')) {
                $kv["{$prefix}.Username"] = $request->input('username');
            }
            if ($request->has('password')) {
                $kv["{$prefix}.Password"] = $request->input('password');
            }
            if ($request->has('vlan_id')) {
                $vlan = (int) $request->input('vlan_id');
                if ($vlan > 0) {
                    $kv["InternetGatewayDevice.WANDevice.1.WANConnectionDevice.{$wanIndex}.WANEthernetLinkConfig.X_BROADCOM_COM_VLANID"] = $vlan;
                }
            }

            if (empty($kv)) {
                return response()->json(['success' => false, 'message' => 'Tidak ada parameter WAN yang diubah.'], 400);
            }

            $success = $this->driver->setParameterValues($cleanId, $kv);

            Log::info("ACS SetWan: User #{$user->id} ({$user->name}) updated WAN on {$cleanId}.");

            return response()->json([
                'success' => $success,
                'message' => $success ? "Konfigurasi WAN berhasil diterapkan." : "Gagal menerapkan konfigurasi WAN.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengatur WAN: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Proxy Update SuperAdmin web GUI credentials on device.
     */
    public function setAdminAccount(Request $request, string $id): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $cleanId = urldecode($id);
        $adminUser = $request->input('username');
        $adminPass = $request->input('password');
        $user = $request->user();

        try {
            $kv = [
                'InternetGatewayDevice.UserInterface.X_BROADCOM_COM_LoginCfg.AdminPassword' => $adminPass,
                'VirtualParameters.AdminPassword' => $adminPass,
                'VirtualParameters.AdminUsername' => $adminUser,
            ];

            $success = $this->driver->setParameterValues($cleanId, $kv);

            Log::info("ACS SetAdminAccount: User #{$user->id} ({$user->name}) changed admin password on {$cleanId}.");

            return response()->json([
                'success' => $success,
                'message' => $success ? "Kredensial SuperAdmin perangkat berhasil diperbarui." : "Gagal memperbarui akun admin perangkat.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah akun admin: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Proxy Delete Device Tasks / Clear Task Queue.
     */
    public function clearTasks(Request $request, string $id): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        $cleanId = urldecode($id);

        try {
            // Get pending/fault tasks and delete them via driver
            $tasks = $this->driver->getTasks($cleanId);
            $deletedCount = 0;

            foreach ($tasks as $task) {
                if (!empty($task['_id'])) {
                    try {
                        \Illuminate\Support\Facades\Http::withBasicAuth(
                            config('genieacs.username', 'admin'),
                            config('genieacs.password', 'admin')
                        )->delete(rtrim(config('genieacs.base_url', 'http://localhost:7557'), '/') . "/tasks/" . $task['_id']);
                        $deletedCount++;
                    } catch (\Throwable) {}
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Antrean task dibersihkan ({$deletedCount} task dihapus).",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membersihkan task queue: ' . $e->getMessage(),
            ], 500);
        }
    }
}
