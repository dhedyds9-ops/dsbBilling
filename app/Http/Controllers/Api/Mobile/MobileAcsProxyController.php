<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
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

        // Warehouse staff has zero access to network CPE/ONT operations
        if ($user->hasRole('admin_gudang') || str_contains(strtolower($user->job_title ?? ''), 'gudang')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Staf gudang tidak memiliki izin untuk mengelola jaringan TR-069.',
            ], 403);
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
     * Validate device ownership and role authorization for ACS operations.
     * Incorporates 3 Layers:
     * A. Role: Administrator/Superadmin bypasses.
     * B. Permission: Checks if user has permission for the requested operation.
     * C. Ownership & Scope: Enforces valid relationship Reseller -> CustomerService -> ONU -> ACSDevice,
     *    and branch/tenant boundary checks.
     */
    protected function validateDeviceAccess(User $user, string $cleanId, string $operation = 'read'): ?JsonResponse
    {
        // 1. SuperAdmin & Administrator backoffice have full access
        if ($user->isSuperAdmin() || $user->hasRole('administrator')) {
            return null;
        }

        // 2. Permission Check (Layer B)
        $requiredPermissions = match ($operation) {
            'wan'          => ['onu.configure.wan', 'onu.manage', 'noc.onu.manage', 'technician.portal'],
            'wifi'         => ['onu.configure.wifi', 'onu.manage', 'noc.onu.manage', 'technician.portal'],
            'admin'        => ['onu.configure.admin', 'onu.manage', 'noc.onu.manage'],
            'tasks.delete' => ['onu.task.delete', 'onu.manage', 'noc.onu.manage'],
            'tasks.retry'  => ['onu.task.retry', 'onu.manage', 'noc.onu.manage'],
            'tasks.view'   => ['onu.task.view', 'onu.view', 'onu.manage', 'noc.onu.manage', 'technician.portal'],
            'reboot'       => ['onu.reboot', 'onu.manage', 'noc.onu.manage', 'technician.portal'],
            'ping'         => ['onu.diagnose', 'onu.manage', 'noc.onu.manage', 'technician.portal'],
            'summon'       => ['onu.diagnose', 'onu.view', 'onu.reboot', 'onu.manage', 'noc.onu.manage', 'technician.portal'],
            'tags'         => ['onu.view', 'onu.manage', 'noc.onu.manage'],
            'read'         => ['onu.view', 'noc.onu.view', 'onu.manage', 'noc.onu.manage', 'technician.portal'],
            default        => ['onu.view', 'onu.manage', 'noc.onu.manage', 'technician.portal'],
        };

        $hasPerm = false;
        foreach ($requiredPermissions as $perm) {
            if ($user->hasPermission($perm) || $user->can($perm)) {
                $hasPerm = true;
                break;
            }
        }

        if (!$hasPerm) {
            return response()->json([
                'success' => false,
                'message' => "Akses ditolak: Anda tidak memiliki permission untuk operasi '{$operation}'.",
            ], 403);
        }

        $cleanUpper = strtoupper(trim(urldecode($cleanId)));

        // Reject wildcard/bulk requests for non-admin users
        if ($cleanUpper === 'ALL' || $cleanUpper === '*' || $cleanUpper === '') {
            return response()->json([
                'success' => false,
                'message' => 'ID perangkat spesifik wajib disertakan.',
            ], 403);
        }

        // 3. Strict Device Resolution (preventing device ID manipulation / loose substring spoofing)
        $dbDevice = \App\Models\ACS\ACSDevice::withoutGlobalScopes()->where(function ($q) use ($cleanUpper) {
            $q->where('serial_number', $cleanUpper)
              ->orWhere('uuid', $cleanUpper)
              ->orWhere('mac_address', $cleanUpper);
        })->first();

        $extractedSn = null;
        if (!$dbDevice && str_contains($cleanUpper, '-')) {
            $parts = explode('-', $cleanUpper);
            $candidateSn = end($parts);
            if (!empty($candidateSn) && strlen($candidateSn) >= 4) {
                $dbDevice = \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                    ->where('serial_number', $candidateSn)
                    ->first();
                if ($dbDevice) {
                    $extractedSn = $candidateSn;
                }
            }
        }

        $dbOnu = null;
        if ($dbDevice && $dbDevice->onu_id) {
            $dbOnu = \App\Models\ISP\Onu::withoutGlobalScopes()->find($dbDevice->onu_id);
        }
        if (!$dbOnu) {
            $dbOnu = \App\Models\ISP\Onu::withoutGlobalScopes()->where(function ($q) use ($cleanUpper, $extractedSn) {
                $q->where('serial_number', $cleanUpper)
                  ->orWhere('code', $cleanUpper)
                  ->orWhere('mac_address', $cleanUpper);
                if ($extractedSn) {
                    $q->orWhere('serial_number', $extractedSn);
                }
            })->first();
        }

        // Must exist in registered database records
        if (!$dbDevice && !$dbOnu) {
            return response()->json([
                'success' => false,
                'message' => 'Perangkat ini tidak terdaftar atau berada di luar cakupan izin akun Anda.',
            ], 403);
        }

        // Resolve CustomerService relationship
        $cs = null;
        if ($dbDevice && $dbDevice->customer_service_id) {
            $cs = \App\Models\Customer\CustomerService::withoutGlobalScopes()->find($dbDevice->customer_service_id);
        }
        if (!$cs && $dbOnu) {
            $cs = \App\Models\Customer\CustomerService::withoutGlobalScopes()->where('onu_id', $dbOnu->id)->first();
        }

        // 4. Reseller Scope & Ownership Enforcement (Layer C)
        // Validates chain: Reseller -> CustomerService -> ONU -> ACSDevice
        if ($user->hasRole('reseller')) {
            $resellerId = $user->getEffectiveResellerId() ?: $user->id;

            // Reject if explicitly registered to another reseller
            if ($cs) {
                $csResellerId = $cs->reseller_id;
                $custResellerId = $cs->customer_id 
                    ? \App\Models\CRM\Customer::withoutGlobalScopes()->where('id', $cs->customer_id)->value('reseller_id')
                    : null;

                if (!empty($csResellerId) && (int) $csResellerId !== (int) $resellerId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Akses ditolak: Perangkat ini terdaftar pada pelanggan Mitra Reseller lain.',
                    ], 403);
                }

                if (!empty($custResellerId) && (int) $custResellerId !== (int) $resellerId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Akses ditolak: Perangkat ini terdaftar pada pelanggan Mitra Reseller lain.',
                    ], 403);
                }
            }

            // Assert positive ownership
            $isOwned = false;

            if ($cs) {
                if ((int) $cs->reseller_id === (int) $resellerId || (int) $cs->created_by === (int) $resellerId) {
                    $isOwned = true;
                } elseif ($cs->customer_id) {
                    $customer = \App\Models\CRM\Customer::withoutGlobalScopes()->find($cs->customer_id);
                    if ($customer && ((int) $customer->reseller_id === (int) $resellerId || (int) $customer->created_by === (int) $resellerId)) {
                        $isOwned = true;
                    }
                }
            }

            if (!$isOwned && $dbOnu) {
                if ((int) $dbOnu->created_by === (int) $resellerId) {
                    $isOwned = true;
                }
            }

            if (!$isOwned && $dbDevice) {
                if ((int) $dbDevice->created_by === (int) $resellerId) {
                    $isOwned = true;
                }
            }

            if (!$isOwned && $dbDevice) {
                $isOwned = \App\Models\ACS\ACSDevice::forReseller($user)->where('id', $dbDevice->id)->exists();
            }

            if (!$isOwned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Perangkat ini berada di luar cakupan kepemilikan akun Reseller Anda.',
                ], 403);
            }
        }

        // 5. Branch Scope Isolation (for staff/technicians with branch_id assignment)
        if ($user->branch_id) {
            $deviceBranchId = null;
            if ($cs) {
                $deviceBranchId = $cs->branch_id ?? ($cs->customer_id ? \App\Models\CRM\Customer::withoutGlobalScopes()->where('id', $cs->customer_id)->value('branch_id') : null);
            }
            if (!$deviceBranchId && $dbOnu) {
                $deviceBranchId = $dbOnu->branch_id;
            }

            if ($deviceBranchId && (int) $deviceBranchId !== (int) $user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Perangkat ini terdaftar pada cabang/tenant lain.',
                ], 403);
            }
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
                    $snToMatch = $devSn;
                    if (str_contains($devSn, '-')) {
                        $parts = explode('-', $devSn);
                        $snToMatch = end($parts);
                    }
                    return in_array($devSn, $allowedSns, true) || in_array($snToMatch, $allowedSns, true);
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

        $cleanId = urldecode($id);
        if ($devCheck = $this->validateDeviceAccess($request->user(), $cleanId, 'read')) {
            return $devCheck;
        }

        try {
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

        $cleanId = urldecode($id);
        if ($devCheck = $this->validateDeviceAccess($request->user(), $cleanId, 'summon')) {
            return $devCheck;
        }

        try {
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
     * Proxy Ping Diagnostics to Device in GenieACS.
     */
    public function ping(Request $request, string $id): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        $cleanId = urldecode($id);
        if ($devCheck = $this->validateDeviceAccess($request->user(), $cleanId, 'ping')) {
            return $devCheck;
        }

        $host = trim((string) $request->input('target_host', '8.8.8.8'));
        if ($host === '') {
            $host = '8.8.8.8';
        }

        try {
            $kv = [
                'InternetGatewayDevice.IPPingDiagnostics.Host' => $host,
                'InternetGatewayDevice.IPPingDiagnostics.NumberOfRepetitions' => 4,
                'InternetGatewayDevice.IPPingDiagnostics.Timeout' => 2000,
                'InternetGatewayDevice.IPPingDiagnostics.DiagnosticsState' => 'Requested',
            ];
            $this->driver->setParameterValues($cleanId, $kv);

            return response()->json([
                'success' => true,
                'message' => "Tes Ping ke {$host} sedang dijalankan oleh modem...",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menjalankan tes ping: ' . $e->getMessage(),
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

        $cleanId = urldecode($id);
        if ($devCheck = $this->validateDeviceAccess($request->user(), $cleanId, 'reboot')) {
            return $devCheck;
        }

        try {
            $user = $request->user();
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

        $cleanId = urldecode($id);
        if ($devCheck = $this->validateDeviceAccess($request->user(), $cleanId, 'wifi')) {
            return $devCheck;
        }

        $request->validate([
            'wlan_index' => 'nullable|integer|min:1|max:8',
            'ssid' => 'nullable|string|max:64',
            'password' => 'nullable|string|max:64',
            'security_mode' => 'nullable|string',
            'enabled' => 'nullable|boolean',
            'hide_ssid' => 'nullable|boolean',
        ]);

        $wlanIndex = (int) $request->input('wlan_index', 1);
        $user = $request->user();

        try {
            $kv = [];
            $prefix = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}";

            if ($request->has('ssid')) {
                $ssid = trim($request->input('ssid'));
                $kv["{$prefix}.SSID"] = $ssid;
                if ($wlanIndex === 1) {
                    $kv["VirtualParameters.WlanSsid"] = $ssid;
                    $kv["VirtualParameters.WlanSSID"] = $ssid;
                }
            }

            $secUpper = strtoupper((string) $request->input('security_mode', ''));

            if ($request->has('password') && $secUpper !== 'OPEN') {
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
                $sec = strtoupper((string) $request->input('security_mode'));
                if ($sec === 'OPEN') {
                    $openBeacon = $this->driver->resolveOpenBeaconValue('default', $cleanId);
                    $kv["{$prefix}.BeaconType"] = $openBeacon;
                    if ($openBeacon === 'Basic') {
                        // Huawei/FiberHome: Open = Basic tanpa enkripsi WEP.
                        $kv["{$prefix}.BasicEncryptionModes"] = 'None';
                    }
                } elseif ($sec === 'WPA2') {
                    $kv["{$prefix}.BeaconType"] = '11i';
                    $kv["{$prefix}.IEEE80211iAuthenticationMode"] = 'PSKAuthentication';
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

        $cleanId = urldecode($id);
        if ($devCheck = $this->validateDeviceAccess($request->user(), $cleanId, 'wan')) {
            return $devCheck;
        }

        $request->validate([
            'wan_index' => 'nullable|integer|min:1|max:8',
            'connection_type' => 'nullable|string|max:32',
            'username' => 'nullable|string|max:128',
            'password' => 'nullable|string|max:128',
            'vlan_id' => 'nullable|integer|min:0|max:4094',
            'vlan_enabled' => 'nullable|boolean',
            'service_type' => 'nullable|string|max:32',
            'service_list' => 'nullable|string|max:32',
            'bind_lan1' => 'nullable|boolean',
            'bind_lan2' => 'nullable|boolean',
            'bind_lan3' => 'nullable|boolean',
            'bind_lan4' => 'nullable|boolean',
            'bind_wlan1' => 'nullable|boolean',
            'bind_wlan2' => 'nullable|boolean',
            'bind_wlan3' => 'nullable|boolean',
            'bind_wlan4' => 'nullable|boolean',
            'is_new_instance' => 'nullable|boolean',
            'manufacturer' => 'nullable|string|max:64',
        ]);

        $wanIndex = (int) $request->input('wan_index', 1);
        $user = $request->user();
        $connType = $request->input('connection_type', 'PPPoE_Routed');
        $isBridge = str_contains(strtolower($connType), 'bridge');
        $isPppoe = str_contains(strtolower($connType), 'pppoe') || !str_contains(strtolower($connType), 'ip');

        try {
            $kv = [];
            $slotName = ($isPppoe) ? "WANPPPConnection" : "WANIPConnection";
            $prefix = "InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.{$slotName}.{$wanIndex}";

            // Enable connection
            $kv["{$prefix}.Enable"] = true;
            $kv["{$prefix}.ConnectionType"] = $connType;

            // Name
            $vlanId = (int) $request->input('vlan_id', 0);
            $vlanEnabled = $request->boolean('vlan_enabled', $vlanId > 0);
            $typeCode = $isBridge ? 'B' : 'R';
            $connName = ($vlanEnabled && $vlanId > 0)
                ? "{$wanIndex}_INTERNET_{$typeCode}_VID_{$vlanId}"
                : "{$wanIndex}_INTERNET_{$typeCode}_VID_";
            $kv["{$prefix}.Name"] = $connName;

            // PPPoE Credentials
            if ($isPppoe) {
                if ($request->has('username')) {
                    $kv["{$prefix}.Username"] = trim((string) $request->input('username'));
                }
                if ($request->has('password') && $request->input('password') !== null) {
                    $kv["{$prefix}.Password"] = (string) $request->input('password');
                }
                $kv["{$prefix}.NATEnabled"] = true;
            }

            // VLAN Tagging
            if ($vlanEnabled && $vlanId > 0) {
                $linkPrefix = "InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANEthernetLinkConfig";
                $kv["{$linkPrefix}.X_BROADCOM_COM_VLANID"] = $vlanId;
                $kv["{$linkPrefix}.X_CT-COM_VLAN"] = $vlanId;
                $kv["{$prefix}.X_BROADCOM_COM_VLANID"] = $vlanId;
            }

            if (empty($kv)) {
                return response()->json(['success' => false, 'message' => 'Tidak ada parameter WAN yang diubah.'], 400);
            }

            $success = $this->driver->setParameterValues($cleanId, $kv);

            Log::info("ACS SetWan: User #{$user->id} ({$user->name}) updated WAN on {$cleanId}.");

            try {
                AuditLog::create([
                    'user_id' => $user?->id,
                    'action' => 'acs_set_wan',
                    'description' => "Ubah konfigurasi WAN device {$cleanId} (index {$wanIndex}, type {$connType}) via Mobile ACS Proxy.",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {}

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

        $cleanId = urldecode($id);
        if ($devCheck = $this->validateDeviceAccess($request->user(), $cleanId, 'admin')) {
            return $devCheck;
        }

        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

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

            try {
                AuditLog::create([
                    'user_id' => $user?->id,
                    'action' => 'acs_set_admin_account',
                    'description' => "Ubah akun admin device {$cleanId} via Mobile ACS Proxy.",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {}

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
        if ($devCheck = $this->validateDeviceAccess($request->user(), $cleanId, 'tasks.delete')) {
            return $devCheck;
        }

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

    /**
     * Proxy Get Tasks for Device from GenieACS.
     */
    public function getTasks(Request $request, string $id): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        $cleanId = urldecode($id);
        if ($devCheck = $this->validateDeviceAccess($request->user(), $cleanId, 'tasks.view')) {
            return $devCheck;
        }

        try {
            $tasks = $this->driver->getTasks($cleanId);
            return response()->json([
                'success' => true,
                'data' => $tasks,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil antrean task: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Proxy Delete Single Task from GenieACS.
     */
    public function deleteSingleTask(Request $request, string $id, string $taskId): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        $cleanId = urldecode($id);
        if ($devCheck = $this->validateDeviceAccess($request->user(), $cleanId, 'tasks.delete')) {
            return $devCheck;
        }

        try {
            $baseUrl = rtrim(config('genieacs.base_url', 'http://localhost:7557'), '/');
            $resp = \Illuminate\Support\Facades\Http::withBasicAuth(
                config('genieacs.username', 'admin'),
                config('genieacs.password', 'admin')
            )->delete("{$baseUrl}/tasks/" . urlencode($taskId));

            if ($resp->successful() || $resp->status() === 404) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tugas berhasil dihapus.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tugas dari GenieACS.',
            ], $resp->status() ?: 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tugas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Proxy Retry Task in GenieACS.
     */
    public function retryTask(Request $request, string $id, string $taskId): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        $cleanId = urldecode($id);
        if ($devCheck = $this->validateDeviceAccess($request->user(), $cleanId, 'tasks.retry')) {
            return $devCheck;
        }

        try {
            $baseUrl = rtrim(config('genieacs.base_url', 'http://localhost:7557'), '/');
            $resp = \Illuminate\Support\Facades\Http::withBasicAuth(
                config('genieacs.username', 'admin'),
                config('genieacs.password', 'admin')
            )->post("{$baseUrl}/tasks/" . urlencode($taskId) . '/retry');

            if ($resp->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tugas berhasil dijadwalkan ulang.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengulang tugas di GenieACS.',
            ], $resp->status() ?: 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengulang tugas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Proxy Add Tag to Device in GenieACS.
     */
    public function addTag(Request $request, string $id): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        $cleanId = urldecode($id);
        if ($devCheck = $this->validateDeviceAccess($request->user(), $cleanId, 'tag')) {
            return $devCheck;
        }

        $tag = trim((string) $request->input('tag'));
        if ($tag === '') {
            return response()->json(['success' => false, 'message' => 'Tag tidak boleh kosong.'], 422);
        }

        try {
            $baseUrl = rtrim(config('genieacs.base_url', 'http://localhost:7557'), '/');
            $resp = \Illuminate\Support\Facades\Http::withBasicAuth(
                config('genieacs.username', 'admin'),
                config('genieacs.password', 'admin')
            )->post("{$baseUrl}/devices/" . urlencode($cleanId) . '/tags/' . urlencode($tag));

            if ($resp->successful() || $resp->status() === 200 || $resp->status() === 201) {
                return response()->json([
                    'success' => true,
                    'message' => "Tag '{$tag}' berhasil ditambahkan.",
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan tag di GenieACS.',
            ], $resp->status() ?: 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan tag: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Proxy Remove Tag from Device in GenieACS.
     */
    public function removeTag(Request $request, string $id, string $tag): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        $cleanId = urldecode($id);
        if ($devCheck = $this->validateDeviceAccess($request->user(), $cleanId, 'tag')) {
            return $devCheck;
        }

        $cleanTag = trim(urldecode($tag));
        try {
            $baseUrl = rtrim(config('genieacs.base_url', 'http://localhost:7557'), '/');
            $resp = \Illuminate\Support\Facades\Http::withBasicAuth(
                config('genieacs.username', 'admin'),
                config('genieacs.password', 'admin')
            )->delete("{$baseUrl}/devices/" . urlencode($cleanId) . '/tags/' . urlencode($cleanTag));

            if ($resp->successful() || $resp->status() === 404) {
                return response()->json([
                    'success' => true,
                    'message' => "Tag '{$cleanTag}' berhasil dihapus.",
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tag dari GenieACS.',
            ], $resp->status() ?: 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tag: ' . $e->getMessage(),
            ], 500);
        }
    }
}
