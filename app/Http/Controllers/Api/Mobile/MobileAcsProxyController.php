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
        protected GenieACSDriver $driver,
        protected \App\Services\ACS\AcsWanManagerService $wanService,
        protected \App\Services\ACS\AcsVerificationEngine $verificationEngine
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

        // Option A: Akun customer diblokir total dari antarmuka GenieACS TR-069
        if ($user->hasRole('customer') || (method_exists($user, 'isCustomer') && $user->isCustomer())) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Akun pelanggan tidak memiliki izin untuk mengelola jaringan TR-069.',
            ], 403);
        }

        // Warehouse staff has zero access to network CPE/ONT operations
        if ($user->hasRole('admin_gudang') || str_contains(strtolower($user->job_title ?? ''), 'gudang')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Staf gudang tidak memiliki izin untuk mengelola jaringan TR-069.',
            ], 403);
        }

        $isAuthorized = $user->isSuperAdmin()
            || $user->hasRole(['noc', 'teknisi', 'technician', 'administrator', 'manager', 'reseller'])
            || $user->isTechnician()
            || in_array(strtoupper($user->job_function ?? ''), ['TECHNICIAN', 'TEKNISI', 'NOC', 'ADMINISTRATOR', 'MANAGER', 'RESELLER'])
            || str_contains(strtoupper($user->job_title ?? ''), 'NOC')
            || str_contains(strtoupper($user->job_title ?? ''), 'TEKNISI')
            || str_contains(strtoupper($user->job_title ?? ''), 'TECHNICIAN')
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
            'delete'       => ['onu.delete', 'onu.manage', 'noc.onu.manage', 'technician.portal'],
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

        if (!$hasPerm && ($user->isTechnician() || $user->hasRole(['admin', 'administrator', 'manager', 'noc', 'teknisi', 'technician']))) {
            $hasPerm = true;
        }

        if (!$hasPerm && ($user->isReseller() || $user->hasRole('reseller'))) {
            if (in_array($operation, ['summon', 'read', 'ping', 'tasks.view', 'reboot', 'delete'])) {
                $hasPerm = true;
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
        $dbDevice = \App\Models\ACS\ACSDevice::withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->where(function ($q) use ($cleanUpper) {
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
                    ->whereNull('deleted_at')
                    ->where('serial_number', $candidateSn)
                    ->first();
                if ($dbDevice) {
                    $extractedSn = $candidateSn;
                }
            }
        }

        $dbOnu = null;
        if ($dbDevice && $dbDevice->onu_id) {
            $dbOnu = \App\Models\ISP\Onu::withoutGlobalScopes()->whereNull('deleted_at')->find($dbDevice->onu_id);
        }
        if (!$dbOnu) {
            $dbOnu = \App\Models\ISP\Onu::withoutGlobalScopes()
                ->whereNull('deleted_at')
                ->where(function ($q) use ($cleanUpper, $extractedSn) {
                    $q->where('serial_number', $cleanUpper)
                      ->orWhere('code', $cleanUpper)
                      ->orWhere('mac_address', $cleanUpper);
                    if ($extractedSn) {
                        $q->orWhere('serial_number', $extractedSn);
                    }
                })->first();
        }

        // Must exist in registered database records, or exist in GenieACS for diagnostic/provisioning operations
        if (!$dbDevice && !$dbOnu) {
            $isDiagnosticOp = in_array($operation, ['summon', 'read', 'ping', 'tasks.view', 'wifi.update', 'wan.update']);
            if (!$isDiagnosticOp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perangkat ini tidak terdaftar atau berada di luar cakupan izin akun Anda.',
                ], 403);
            }
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
        if ($user->hasRole('reseller') || $user->isReseller()) {
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

            if ($dbOnu && !empty($dbOnu->reseller_id) && (int) $dbOnu->reseller_id !== (int) $resellerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Perangkat ini terdaftar pada pelanggan Mitra Reseller lain.',
                ], 403);
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
                if ((int) $dbOnu->reseller_id === (int) $resellerId || (int) $dbOnu->created_by === (int) $resellerId) {
                    $isOwned = true;
                } elseif (\App\Models\ISP\Onu::forReseller($user)->where('id', $dbOnu->id)->exists()) {
                    $isOwned = true;
                }
            }

            if (!$isOwned && $dbDevice) {
                if ((int) $dbDevice->created_by === (int) $resellerId) {
                    $isOwned = true;
                } elseif (\App\Models\ACS\ACSDevice::forReseller($user)->where('id', $dbDevice->id)->exists()) {
                    $isOwned = true;
                }
            }

            // Cek kepemilikan oleh reseller lain
            $isOwnedByOtherReseller = false;
            if ($cs) {
                $csResellerId = $cs->reseller_id;
                $custResellerId = $cs->customer_id 
                    ? \App\Models\CRM\Customer::withoutGlobalScopes()->where('id', $cs->customer_id)->value('reseller_id')
                    : null;
                if ((!empty($csResellerId) && (int)$csResellerId !== (int)$resellerId) || (!empty($custResellerId) && (int)$custResellerId !== (int)$resellerId)) {
                    $isOwnedByOtherReseller = true;
                }
            }
            if ($dbOnu && !empty($dbOnu->reseller_id) && (int)$dbOnu->reseller_id !== (int)$resellerId) {
                $isOwnedByOtherReseller = true;
            }

            if ($isOwnedByOtherReseller) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Perangkat ini terdaftar pada pelanggan Mitra Reseller lain.',
                ], 403);
            }

            $isDiagnosticOp = in_array($operation, ['summon', 'read', 'ping', 'tasks.view']);

            // Resolusi Branch Device untuk Reseller
            $resellerDevBranchId = null;
            if ($cs) {
                $resellerDevBranchId = $cs->branch_id ?? ($cs->customer_id ? \App\Models\CRM\Customer::withoutGlobalScopes()->where('id', $cs->customer_id)->value('branch_id') : null);
            }
            if (!$resellerDevBranchId && $dbOnu) {
                $resellerDevBranchId = $dbOnu->branch_id;
            }

            // Reseller ditolak jika perangkat terdaftar di cabang lain
            if ($resellerDevBranchId && $user->branch_id && (int) $resellerDevBranchId !== (int) $user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Perangkat ini berada di luar cakupan kepemilikan akun Reseller Anda.',
                ], 403);
            }

            // Jika bukan pemilik positif dan bukan operasi diagnostik (misal: wifi, wan, reboot, delete, admin)
            if (!$isOwned && !$isDiagnosticOp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Perangkat ini berada di luar cakupan kepemilikan akun Reseller Anda.',
                ], 403);
            }

            // Paritas dengan Web Portal Reseller (ACSDevice::scopeForReseller):
            // Perangkat unassigned (belum terikat pelanggan manapun / baru colok di OLT) diizinkan diakses
            // untuk keperluan pembacaan detail, diagnosa, dan pengaturan aktivasi PSB.
            if (!$isOwned && $operation === 'delete') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Anda hanya dapat menghapus perangkat milik akun Reseller Anda.',
                ], 403);
            }
        }

        // 5. Cross-Branch & Tenant Boundary Check (Directive: Cross-Branch Allowed with Audit Trail)
        if ($user->branch_id) {
            $deviceBranchId = null;
            $deviceCompanyId = null;
            if ($cs) {
                $deviceBranchId = $cs->branch_id ?? ($cs->customer_id ? \App\Models\CRM\Customer::withoutGlobalScopes()->where('id', $cs->customer_id)->value('branch_id') : null);
                $deviceCompanyId = $cs->customer_id ? \App\Models\CRM\Customer::withoutGlobalScopes()->where('id', $cs->customer_id)->value('company_id') : null;
            }
            if (!$deviceBranchId && $dbOnu) {
                $deviceBranchId = $dbOnu->branch_id;
                $deviceCompanyId = $dbOnu->company_id;
            }

            // Strictly enforce multi-tenant company isolation
            if ($user->company_id && $deviceCompanyId && (int) $user->company_id !== (int) $deviceCompanyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Perangkat ini terdaftar pada tenant/perusahaan yang berbeda.',
                ], 403);
            }

            // Cross-branch operation is permitted for checked-in staff with valid assignment or manager oversight
            // Reseller examining unassigned devices from upstream OLT is also exempted from cross-branch rejection
            $isResellerUser = ($user->hasRole('reseller') || $user->isReseller());
            $isUnassignedForReseller = $isResellerUser && (!$cs || empty($cs->customer_id));

            if ($deviceBranchId && (int) $deviceBranchId !== (int) $user->branch_id && !$isUnassignedForReseller) {
                $hasAssignedWo = false;
                if ($cs && $cs->customer_id) {
                    $hasAssignedWo = \App\Models\Workforce\WorkOrder::where('customer_id', $cs->customer_id)
                        ->where('assigned_to', $user->id)
                        ->whereIn('status', ['assigned', 'in_progress'])
                        ->exists();
                }
                if (!$hasAssignedWo && $dbOnu) {
                    $hasAssignedWo = \App\Models\Workforce\WorkOrder::where('assigned_to', $user->id)
                        ->whereIn('status', ['assigned', 'in_progress'])
                        ->where(function ($q) use ($dbOnu) {
                            $q->where('description', 'like', "%{$dbOnu->serial_number}%")
                              ->orWhere('notes', 'like', "%{$dbOnu->serial_number}%");
                        })->exists();
                }

                // Strictly reject unassigned cross-branch device operations (exempt diagnostic/summon operations for staff)
                $isDiagOp = in_array($operation, ['summon', 'read', 'ping', 'tasks.view']);
                if (!$hasAssignedWo && !$isDiagOp && !$user->isSuperAdmin() && !$user->roles->contains(fn ($r) => in_array($r->name, ['manager', 'administrator', 'superadmin', 'noc']))) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Akses ditolak: Perangkat ini terdaftar pada cabang/tenant lain.',
                    ], 403);
                }

                \Illuminate\Support\Facades\Log::info("[CROSS-BRANCH: Tech Cabang #{$user->branch_id} -> Device Cabang #{$deviceBranchId}] Staff: {$user->name} ({$user->id}) performed ACS operation '{$operation}' on device '{$cleanUpper}'.");
            }
        }

        return null;
    }

    /**
     * Proxy List Devices from GenieACS with resilient dsBilling database fallback.
     */
    public function listDevices(Request $request): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        $user = $request->user();
        $limit = min(5000, (int) $request->input('limit', 1000));
        $devices = [];

        try {
            $devices = $this->driver->listDevices([], $limit);
        } catch (\Throwable $e) {
            Log::warning("MobileAcsProxy listDevices: GenieACS driver unreachable or error ({$e->getMessage()}), utilizing dsBilling local database fallback.");
            $devices = [];
        }

        // SSOT Parity with Web: Filter out all soft-deleted (trashed) and dismantled/replaced devices
        // This prevents zombie devices whose physical ONT is still sending Informs from appearing online in Android
        $trashedSns = \App\Models\ACS\ACSDevice::onlyTrashed()->pluck('serial_number')
            ->merge(\App\Models\ACS\ACSDevice::onlyTrashed()->pluck('uuid'))
            ->merge(\App\Models\ISP\Onu::onlyTrashed()->pluck('serial_number'))
            ->merge(\App\Models\ISP\Onu::whereIn('status', ['dismantled', 'replaced'])->pluck('serial_number'))
            ->filter()
            ->map(fn($s) => strtoupper(trim((string) $s)))
            ->unique()
            ->toArray();

        if (!empty($devices) && !empty($trashedSns)) {
            $devices = array_values(array_filter($devices, function ($dev) use ($trashedSns) {
                $devSn = strtoupper(trim((string) ($dev['serial_number'] ?? '')));
                $devId = strtoupper(trim((string) ($dev['_id'] ?? '')));
                $prodSn = strtoupper(trim((string) ($dev['_deviceId']['_SerialNumber'] ?? '')));

                $candidates = array_filter([$devSn, $devId, $prodSn]);
                foreach ($candidates as $cand) {
                    if (in_array($cand, $trashedSns, true)) {
                        return false;
                    }
                    if (str_contains($cand, '-')) {
                        $parts = explode('-', $cand);
                        if (in_array(end($parts), $trashedSns, true)) {
                            return false;
                        }
                    }
                }
                return true;
            }));
        }

        // Reseller filtering on driver devices:
        // Option A: Reseller dapat melihat semua perangkat GenieACS (termasuk unassigned/baru colok),
        // kecuali perangkat yang sudah terdaftar pada pelanggan Mitra Reseller lain.
        if ($user && $user->hasRole('reseller')) {
            $effectiveResellerId = $user->getEffectiveResellerId() ?: $user->id;

            $otherResellersSns = \App\Models\Customer\CustomerService::withoutGlobalScopes()
                ->whereNotNull('reseller_id')
                ->where('reseller_id', '!=', $effectiveResellerId)
                ->pluck('onu_serial')
                ->filter()
                ->map(fn($s) => strtoupper(trim((string) $s)))
                ->unique()
                ->toArray();

            if (!empty($devices)) {
                $devices = array_values(array_filter($devices, function ($dev) use ($otherResellersSns) {
                    $devSn = strtoupper(trim((string) ($dev['serial_number'] ?? ($dev['_id'] ?? ''))));
                    $snToMatch = $devSn;
                    if (str_contains($devSn, '-')) {
                        $parts = explode('-', $devSn);
                        $snToMatch = end($parts);
                    }
                    return !in_array($devSn, $otherResellersSns, true) && !in_array($snToMatch, $otherResellersSns, true);
                }));
            }
        }

        // Fallback or merge with dsBilling database records
        $dbOnus = collect();
        $dbAcs = collect();

        if ($user && $user->hasRole('reseller')) {
            $dbOnus = \App\Models\ISP\Onu::withoutGlobalScopes()
                ->whereNull('deleted_at')
                ->forReseller($user)
                ->with(['customerService.customer', 'customerService.pppoeUser', 'odp', 'olt', 'vendor'])
                ->get();
            $dbAcs = \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                ->whereNull('deleted_at')
                ->forReseller($user)
                ->with(['customerService.customer', 'onu', 'odp', 'olt'])
                ->get();
        } else {
            $onusQuery = \App\Models\ISP\Onu::withoutGlobalScopes()
                ->whereNull('deleted_at')
                ->with(['customerService.customer', 'customerService.pppoeUser', 'odp', 'olt', 'vendor']);
            $acsQuery = \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                ->whereNull('deleted_at')
                ->with(['customerService.customer', 'onu', 'odp', 'olt']);

            if ($user && !empty($user->branch_id) && !$user->isSuperAdmin() && !$user->hasRole('administrator')) {
                $onusQuery->where(function ($q) use ($user) {
                    $q->where('branch_id', $user->branch_id)->orWhereNull('branch_id');
                });
            }

            $dbOnus = $onusQuery->limit(200)->get();
            $dbAcs = $acsQuery->limit(200)->get();
        }

        // Index existing devices by normalized serial number to prevent duplicates
        $existingSns = [];
        foreach ($devices as $d) {
            $devSn = strtoupper(trim((string) ($d['serial_number'] ?? ($d['_id'] ?? ''))));
            if ($devSn !== '') {
                $existingSns[$devSn] = true;
                if (str_contains($devSn, '-')) {
                    $snParts = explode('-', $devSn);
                    $existingSns[end($snParts)] = true;
                }
            }
        }

        // Convert and merge database records
        foreach ($dbOnus as $onu) {
            $sn = strtoupper(trim((string) $onu->serial_number));
            if (empty($sn) || isset($existingSns[$sn])) {
                continue;
            }
            $existingSns[$sn] = true;

            $isOnline = in_array(strtolower((string) $onu->status), ['online', 'active'], true)
                || ($onu->last_seen_at && \Carbon\Carbon::parse($onu->last_seen_at)->diffInMinutes(now()) <= 5);

            $customerName = $onu->customerService?->customer?->name ?? $onu->customer?->name ?? $onu->name;
            $pppoeUser = $onu->customerService?->pppoeUser?->username ?? $onu->pppoe_username ?? '-';
            $rxPower = $onu->rx_power_dbm !== null ? -abs((float) $onu->rx_power_dbm) : null;
            $mfg = $onu->vendor?->name ?? (str_starts_with($sn, 'HWTC') ? 'Huawei' : (str_starts_with($sn, 'ZTEG') ? 'ZTE' : 'Modem'));
            $model = $onu->model ?: ($onu->name ?: 'ONT');

            $devices[] = [
                '_id' => "{$mfg}-{$sn}",
                '_deviceId' => [
                    '_Manufacturer' => $mfg,
                    '_ProductClass' => $model,
                    '_SerialNumber' => $sn,
                ],
                '_lastInform' => $onu->last_seen_at ? \Carbon\Carbon::parse($onu->last_seen_at)->toISOString() : ($isOnline ? now()->toISOString() : now()->subHours(2)->toISOString()),
                'is_online' => $isOnline,
                'customer_name' => $customerName,
                'pppoe_username' => $pppoeUser,
                'ssid' => $onu->wifi_ssid ?: 'Wi-Fi',
                'rx_power' => $rxPower,
                'odp_name' => $onu->odp?->name ?? $onu->odp?->code,
                'VirtualParameters' => [
                    'RXPower' => (string) ($rxPower ?? -19.5),
                    'redaman' => (string) ($rxPower ?? -19.5),
                    'SSID' => (string) ($onu->wifi_ssid ?: 'Wi-Fi'),
                    'pppoeUsername' => (string) $pppoeUser,
                    'customerName' => (string) ($customerName ?: ''),
                ],
                'tags' => array_values(array_filter([$onu->odp?->name, $onu->olt?->name])),
            ];
        }

        foreach ($dbAcs as $ad) {
            $sn = strtoupper(trim((string) $ad->serial_number));
            if (empty($sn) || isset($existingSns[$sn])) {
                continue;
            }
            $existingSns[$sn] = true;

            $isOnline = strtolower((string) $ad->status) === 'online'
                || ($ad->last_inform && \Carbon\Carbon::parse($ad->last_inform)->diffInMinutes(now()) <= 5);

            $customerName = $ad->customerService?->customer?->name;
            $pppoeUser = $ad->pppoe_username ?? '-';
            $rxPower = $ad->signal !== null ? -abs((float) $ad->signal) : null;
            $mfg = $ad->manufacturer ?: (str_starts_with($sn, 'HWTC') ? 'Huawei' : (str_starts_with($sn, 'ZTEG') ? 'ZTE' : 'Modem'));
            $model = $ad->model ?: ($ad->product_class ?: 'ONT');

            $devices[] = [
                '_id' => $ad->uuid ?: "{$mfg}-{$sn}",
                '_deviceId' => [
                    '_Manufacturer' => $mfg,
                    '_ProductClass' => $model,
                    '_SerialNumber' => $sn,
                ],
                '_lastInform' => $ad->last_inform ? \Carbon\Carbon::parse($ad->last_inform)->toISOString() : ($isOnline ? now()->toISOString() : now()->subHours(2)->toISOString()),
                'is_online' => $isOnline,
                'customer_name' => $customerName,
                'pppoe_username' => $pppoeUser,
                'ssid' => $ad->wifi_ssid ?: 'Wi-Fi',
                'rx_power' => $rxPower,
                'odp_name' => $ad->odp?->name ?? $ad->odp?->code,
                'VirtualParameters' => [
                    'RXPower' => (string) ($rxPower ?? -19.5),
                    'redaman' => (string) ($rxPower ?? -19.5),
                    'SSID' => (string) ($ad->wifi_ssid ?: 'Wi-Fi'),
                    'pppoeUsername' => (string) $pppoeUser,
                    'customerName' => (string) ($customerName ?: ''),
                ],
                'tags' => array_values(array_filter([$ad->odp?->name, $ad->olt?->name])),
            ];
        }

        // Enrich any remaining devices with customer names from dsBilling database
        if (!empty($devices)) {
            $candidateSns = [];
            foreach ($devices as $d) {
                if (!empty($d['customer_name'])) continue;
                $devSn = strtoupper(trim((string) ($d['serial_number'] ?? ($d['_id'] ?? ''))));
                if ($devSn !== '') {
                    $candidateSns[] = $devSn;
                    if (str_contains($devSn, '-')) {
                        $parts = explode('-', $devSn);
                        $candidateSns[] = end($parts);
                    }
                }
            }
            $candidateSns = array_values(array_unique(array_filter($candidateSns)));

            if (!empty($candidateSns)) {
                $custServices = \App\Models\Customer\CustomerService::withoutGlobalScopes()
                    ->with([
                        'customer' => fn($q) => $q->withoutGlobalScopes(),
                        'onu' => fn($q) => $q->withoutGlobalScopes()->with(['odp' => fn($o) => $o->withoutGlobalScopes()]),
                    ])
                    ->where(function ($query) use ($candidateSns) {
                        $query->whereHas('onu', fn($q) => $q->whereIn('serial_number', $candidateSns))
                              ->orWhereIn('onu_serial', $candidateSns);
                    })
                    ->get();

                $dbOnus = \App\Models\ISP\Onu::withoutGlobalScopes()
                    ->whereIn('serial_number', $candidateSns)
                    ->with(['odp' => fn($q) => $q->withoutGlobalScopes(), 'customer' => fn($q) => $q->withoutGlobalScopes()])
                    ->get()
                    ->keyBy(fn($o) => strtoupper($o->serial_number));

                $dbAcsList = \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                    ->whereIn('serial_number', $candidateSns)
                    ->orWhereIn('uuid', $candidateSns)
                    ->with(['odp' => fn($q) => $q->withoutGlobalScopes(), 'customerService.customer' => fn($q) => $q->withoutGlobalScopes()])
                    ->get()
                    ->keyBy(fn($a) => strtoupper($a->serial_number ?: $a->uuid));

                $metaMap = [];
                foreach ($custServices as $cs) {
                    $keys = array_filter([
                        $cs->onu_serial ? strtoupper($cs->onu_serial) : null,
                        $cs->onu?->serial_number ? strtoupper($cs->onu->serial_number) : null
                    ]);
                    $meta = [
                        'customer_name' => $cs->customer?->name,
                        'pppoe_username' => $cs->username,
                        'lat' => $cs->customer?->latitude ? (float)$cs->customer->latitude : null,
                        'lng' => $cs->customer?->longitude ? (float)$cs->customer->longitude : null,
                        'odp_name' => $cs->onu?->odp?->name ?? $cs->onu?->odp?->code,
                        'rx_power' => $cs->onu?->rx_power_dbm !== null ? (float)$cs->onu->rx_power_dbm : null,
                    ];
                    foreach ($keys as $k) {
                        $metaMap[$k] = $meta;
                    }
                }

                foreach ($devices as &$d) {
                    $devSn = strtoupper(trim((string) ($d['serial_number'] ?? ($d['_id'] ?? ''))));
                    $snParts = explode('-', $devSn);
                    $snShort = end($snParts);
                    
                    $matched = $metaMap[$devSn] ?? ($metaMap[$snShort] ?? null);
                    $dbOnu = $dbOnus->get($devSn) ?? $dbOnus->get($snShort);
                    $dbAcs = $dbAcsList->get($devSn) ?? $dbAcsList->get($snShort);

                    if (empty($d['customer_name'])) {
                        $d['customer_name'] = $matched['customer_name'] 
                            ?? $dbOnu?->customer?->name 
                            ?? $dbAcs?->customerService?->customer?->name 
                            ?? null;
                    }

                    if (empty($d['pppoe_username']) || $d['pppoe_username'] === '-') {
                        $d['pppoe_username'] = $matched['pppoe_username'] 
                            ?? $dbOnu?->pppoe_username 
                            ?? $dbAcs?->pppoe_username 
                            ?? '-';
                    }

                    $resolvedLat = $matched['lat'] 
                        ?? ($dbOnu?->latitude ? (float)$dbOnu->latitude : null)
                        ?? ($dbAcs?->latitude ? (float)$dbAcs->latitude : null)
                        ?? ($dbOnu?->customer?->latitude ? (float)$dbOnu->customer->latitude : null);

                    $resolvedLng = $matched['lng'] 
                        ?? ($dbOnu?->longitude ? (float)$dbOnu->longitude : null)
                        ?? ($dbAcs?->longitude ? (float)$dbAcs->longitude : null)
                        ?? ($dbOnu?->customer?->longitude ? (float)$dbOnu->customer->longitude : null);

                    $resolvedOdp = $matched['odp_name'] 
                        ?? $dbOnu?->odp?->name 
                        ?? $dbAcs?->odp?->name 
                        ?? null;

                    if ($resolvedLat !== null && $resolvedLng !== null) {
                        $d['latitude'] = $resolvedLat;
                        $d['longitude'] = $resolvedLng;
                        if (!isset($d['tags']) || !is_array($d['tags'])) {
                            $d['tags'] = [];
                        }
                        $hasGeoTag = false;
                        foreach ($d['tags'] as $t) {
                            if (is_string($t) && str_starts_with($t, 'geo:')) {
                                $hasGeoTag = true;
                                break;
                            }
                        }
                        if (!$hasGeoTag) {
                            $d['tags'][] = "geo:{$resolvedLat},{$resolvedLng}";
                        }
                    }

                    if ($resolvedOdp) {
                        $d['odp_name'] = $resolvedOdp;
                        if (!isset($d['tags']) || !is_array($d['tags'])) {
                            $d['tags'] = [];
                        }
                        $hasOdpTag = false;
                        foreach ($d['tags'] as $t) {
                            if (is_string($t) && str_starts_with($t, 'odp:')) {
                                $hasOdpTag = true;
                                break;
                            }
                        }
                        if (!$hasOdpTag) {
                            $d['tags'][] = "odp:{$resolvedOdp}";
                        }
                    }

                    if (!isset($d['rx_power']) && !empty($matched['rx_power'])) {
                        $d['rx_power'] = $matched['rx_power'];
                    }
                }
                unset($d);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $devices,
        ]);
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
            $realDeviceId = $this->driver->resolveDeviceId($cleanId);
            $params = [];
            try {
                $params = $this->driver->getDeviceParameters($realDeviceId);
                if (empty($params) && $realDeviceId !== $cleanId) {
                    $params = $this->driver->getDeviceParameters($cleanId);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("MobileAcsProxy getDeviceParameters error for {$cleanId}: " . $e->getMessage());
            }

            // Enrich params with dsBilling database resolution (parity with Web dsBilling)
            $cleanUpper = strtoupper(trim($cleanId));
            $cuParts = explode('-', $cleanUpper);
            $candidateSn = end($cuParts);

            $ad = \App\Models\ACS\ACSDevice::withoutGlobalScopes()->where(function ($q) use ($cleanUpper, $candidateSn) {
                $q->where('uuid', $cleanUpper)
                  ->orWhere('serial_number', $cleanUpper)
                  ->orWhere('serial_number', $candidateSn)
                  ->orWhere('mac_address', $cleanUpper);
            })->first();

            $onu = $ad && $ad->onu_id ? \App\Models\ISP\Onu::withoutGlobalScopes()->find($ad->onu_id) : null;
            if (!$onu) {
                $onu = \App\Models\ISP\Onu::withoutGlobalScopes()->where(function ($q) use ($cleanUpper, $candidateSn) {
                    $q->where('serial_number', $cleanUpper)
                      ->orWhere('serial_number', $candidateSn)
                      ->orWhere('mac_address', $cleanUpper);
                })->first();
            }

            if (empty($params)) {
                if ($ad || $onu) {
                    $params = [
                        '_id' => $cleanId,
                        '_deviceId' => [
                            '_Manufacturer' => $onu?->vendor?->name ?? ($ad?->vendor ?? 'ZTE'),
                            '_ProductClass' => $onu?->model_name ?? ($ad?->model_name ?? 'Modem'),
                            '_SerialNumber' => $candidateSn,
                        ],
                        '_lastInform' => $ad?->last_inform?->toIso8601String() ?? $onu?->updated_at?->toIso8601String(),
                        'VirtualParameters' => array_filter([
                            'RXPower' => $onu?->rx_power_dbm !== null ? (string)$onu->rx_power_dbm : null,
                            'TXPower' => $onu?->tx_power_dbm !== null ? (string)$onu->tx_power_dbm : null,
                            'SSID' => $onu?->wifi_ssid,
                            'wlan_ssid' => $onu?->wifi_ssid,
                        ]),
                    ];
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => "Perangkat dengan ID '{$cleanId}' tidak ditemukan di ACS maupun database.",
                    ], 404);
                }
            }

            $cs = null;
            if ($ad && $ad->customer_service_id) {
                $cs = \App\Models\Customer\CustomerService::withoutGlobalScopes()
                    ->with(['customer' => fn($q) => $q->withoutGlobalScopes()])
                    ->find($ad->customer_service_id);
            }
            if (!$cs && $onu) {
                $cs = \App\Models\Customer\CustomerService::withoutGlobalScopes()
                    ->where('onu_id', $onu->id)
                    ->with(['customer' => fn($q) => $q->withoutGlobalScopes()])
                    ->first();
            }

            $cust = $cs?->customer;
            if (!$cust && $onu?->customer_id) {
                $cust = \App\Models\CRM\Customer::withoutGlobalScopes()->find($onu->customer_id);
            }

            $rawTr069 = $ad?->connection_request_url ? parse_url($ad->connection_request_url, PHP_URL_HOST) : null;
            $tr069Ip = ($rawTr069 && $rawTr069 !== '0.0.0.0' && filter_var($rawTr069, FILTER_VALIDATE_IP)) ? $rawTr069 : null;
            $rawPpp = ($ad?->ip_address && $ad->ip_address !== '0.0.0.0') ? $ad->ip_address : ($cs?->ip_address ?? null);
            $pppoeIp = ($rawPpp && $rawPpp !== $tr069Ip) ? $rawPpp : null;

            $ponPortName = $onu?->ponPort?->name 
                ?? ($onu?->ponPort?->port_number ? "GPON " . $onu->ponPort->port_number : null)
                ?? ($onu?->pon_port ? (str_starts_with($onu->pon_port, 'GPON') ? $onu->pon_port : "GPON " . $onu->pon_port) : null);

            $params['customer_name'] = $cust?->name;
            $params['customer_code'] = $cust?->code;
            $params['customer_phone'] = $cust?->phone;
            $params['customer_address'] = $cust?->address;
            $params['pppoe_username'] = $cs?->username ?: ($ad?->customerService?->username);
            $params['pppoe_ip'] = $pppoeIp;
            $params['tr069_ip'] = $tr069Ip;
            $params['wan_ip'] = $pppoeIp ?: ($tr069Ip ?: ($ad?->ip_address));
            $params['mac_address'] = $ad?->mac_address ?: ($onu?->mac_address);
            $params['olt_name'] = $onu?->olt?->name ?: ($ad?->olt?->name);
            $params['pon_port_name'] = $ponPortName;
            $params['odp_name'] = $onu?->odp?->name ?: ($onu?->odp?->code);

            // Heuristic extraction for CPU, RAM, and Temperature metrics
            $flat = [];
            $scalarVal = function ($node) {
                if ($node === null) return null;
                if (is_scalar($node)) return trim((string) $node);
                if (is_array($node)) {
                    if (isset($node['_value']) && is_scalar($node['_value'])) {
                        return trim((string) $node['_value']);
                    }
                    if (isset($node[0]) && is_scalar($node[0])) {
                        return trim((string) $node[0]);
                    }
                }
                return null;
            };
            $flatten = function ($data, $prefix = '') use (&$flatten, &$flat, $scalarVal) {
                if (!is_array($data)) return;
                foreach ($data as $k => $v) {
                    if ($k === '_timestamp' || $k === '_object' || $k === '_writable') continue;
                    $key = $prefix ? "$prefix.$k" : (string) $k;
                    if (is_array($v)) {
                        $s = $scalarVal($v);
                        if ($s !== null) {
                            $flat[$key] = $s;
                        }
                        $flatten($v, $key);
                    } elseif (is_scalar($v)) {
                        $flat[$key] = (string) $v;
                    }
                }
            };
            $flatten($params);

            // Use unified AcsTelemetryParser for standardized multi-vendor hardware metrics
            $hw = \App\Services\ACS\AcsTelemetryParser::parseHardwareMetrics($flat, $ad);
            $cpuUsage = $hw['cpu_usage_display'] !== 'Tidak Didukung' ? $hw['cpu_usage_display'] : null;
            $ramUsage = $hw['ram_usage_display'] !== 'Tidak Didukung' ? $hw['ram_usage_display'] : null;
            $temperature = $hw['temperature_display'] !== 'Tidak Didukung' ? $hw['temperature_display'] : null;

            // Standardized Optical Diagnostics (Phase 2 SSOT)
            $optical = \App\Services\ACS\AcsTelemetryParser::parseOptical($params, $onu, $ad);
            $rxPower = $optical['rx_power'];
            $txPower = $optical['tx_power'];

            if ($rxPower !== null && (float)$rxPower < 0) {
                try {
                    $ad?->update(['signal' => $rxPower]);
                    $onu?->update(['rx_power_dbm' => $rxPower]);
                } catch (\Throwable) {}
            }
            if ($txPower !== null && (float)$txPower > 0) {
                try {
                    $onu?->update(['tx_power_dbm' => $txPower]);
                } catch (\Throwable) {}
            }

            // Extract connected devices directly in-memory from $params
            $connectedDevices = $this->driver->extractConnectedDevicesFromParams($params);
            if (empty($connectedDevices)) {
                try {
                    $connectedDevices = $this->driver->getConnectedDevices($realDeviceId);
                } catch (\Throwable) {}
            }

            // Accurate Multi-Vendor Multi-Band Wi-Fi Engine (Phase 2 SSOT)
            $wifiDetailed = \App\Services\ACS\AcsTelemetryParser::parseWifiDetailed($params, $onu);
            $wlans = !empty($wifiDetailed['all_ssids'])
                ? $wifiDetailed['all_ssids']
                : $this->driver->extractAllWlansFromParams($params, $onu);

            // Populate normalized values into params payload
            $params['cpu_usage'] = $cpuUsage;
            $params['cpu_usage_raw'] = $hw['cpu_usage'];
            $params['cpu_status'] = $hw['cpu_status'];
            $params['ram_usage'] = $ramUsage;
            $params['ram_usage_raw'] = $hw['ram_usage'];
            $params['ram_total'] = $hw['ram_total_display'];
            $params['ram_free'] = $hw['ram_free_display'];
            $params['ram_status'] = $hw['ram_status'];
            $params['temperature'] = $temperature;
            $params['temperature_status'] = $hw['temperature_status'];
            $params['flash_total'] = $hw['flash_total_display'];
            $params['flash_free'] = $hw['flash_free_display'];
            $params['flash_status'] = $hw['flash_status'];
            $params['uptime'] = $hw['uptime_display'];
            $params['uptime_seconds'] = $hw['uptime_seconds'];
            $params['uptime_status'] = $hw['uptime_status'];
            $params['hardware_metrics'] = $hw;

            // WAN Connections with Desired vs Actual Mismatch Tracking
            $detectedVendor = $hw['vendor'] ?? 'generic';
            $desiredState = null;
            if ($cs || $onu) {
                $desiredState = [
                    'username' => $cs?->username ?: ($ad?->customerService?->username),
                    'vlan_id' => $onu?->vlan_id ?: ($cs?->vlan_id ?? null),
                    'connection_type' => 'PPPoE_Routed',
                    'service_list' => 'INTERNET',
                ];
            }
            $wanConnections = \App\Services\ACS\AcsTelemetryParser::parseWanTree($params, $detectedVendor, $desiredState);
            $portBindings = \App\Services\ACS\AcsPortBindingEngine::discover($params, $wanConnections);

            $params['wan_connections'] = $wanConnections;
            $params['port_bindings'] = $portBindings;
            $params['optical_diagnostics'] = $optical;
            $params['wifi_details'] = $wifiDetailed;
            $params['rx_power'] = $rxPower;
            $params['tx_power'] = $txPower;
            if (!isset($params['VirtualParameters']) || !is_array($params['VirtualParameters'])) {
                $params['VirtualParameters'] = [];
            }
            if ($rxPower !== null) {
                $params['VirtualParameters']['RXPower'] = (string)$rxPower;
                $params['VirtualParameters']['rxPower'] = (string)$rxPower;
            }
            if ($txPower !== null) {
                $params['VirtualParameters']['TXPower'] = (string)$txPower;
                $params['VirtualParameters']['txPower'] = (string)$txPower;
            }
            $params['connected_devices'] = $connectedDevices;
            $params['connected_clients'] = $connectedDevices;
            $params['connected_devices_count'] = count($connectedDevices);
            $params['wlans'] = $wlans;

            return response()->json([
                'success' => true,
                'data' => $params,
                'optical_diagnostics' => $optical,
                'wifi_details' => $wifiDetailed,
                'port_bindings' => $portBindings,
                'wan_connections' => $wanConnections,
                'connected_devices' => $connectedDevices,
                'connected_clients' => $connectedDevices,
            ]);
        } catch (\Throwable $e) {
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
            $res = $this->driver->summonDeviceResult($cleanId);
            $success = ($res['connection_request_success'] ?? false) || ($res['task_queued'] ?? false);

            // Audit log
            $user = $request->user();
            Log::info("ACS Summon: User #{$user->id} ({$user->name}) summoned device {$cleanId}. Result: " . json_encode($res));

            $message = !empty($res['connection_request_success'])
                ? "Permintaan koneksi (Summon Live) berhasil dikirim ke perangkat."
                : (!empty($res['task_queued'])
                    ? "Tugas refresh parameter live berhasil diantrikan ke antrean TR-069."
                    : ("Perangkat tidak merespons summon: " . ($res['error'] ?? "Modem offline atau tidak terjangkau.")));

            return response()->json([
                'success' => $success,
                'message' => $message,
                'details' => $res,
            ], $success ? 200 : 200);
        } catch (\Throwable $e) {
            Log::error("ACS Summon exception for {$cleanId}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim summon: ' . $e->getMessage(),
            ], 200);
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
            $realDeviceId = $this->driver->resolveDeviceId($cleanId);
            $params = $this->driver->getDeviceParameters($realDeviceId);

            $ad = \App\Models\ACS\ACSDevice::withoutGlobalScopes()->where('uuid', $realDeviceId)->orWhere('serial_number', $realDeviceId)->first();
            $onu = $ad && $ad->onu_id ? \App\Models\ISP\Onu::withoutGlobalScopes()->find($ad->onu_id) : null;
            if (!$onu) {
                $onu = \App\Models\ISP\Onu::withoutGlobalScopes()->where('serial_number', $cleanId)->first();
            }

            // Discover actual WLANs using AcsTelemetryParser
            $wifiDetailed = \App\Services\ACS\AcsTelemetryParser::parseWifiDetailed($params, $onu);
            $targetWlan = null;

            // Search by instance number, or by display_index
            foreach ($wifiDetailed['all_ssids'] as $slot) {
                if ($slot['instance'] === $wlanIndex || $slot['display_index'] === $wlanIndex) {
                    $targetWlan = $slot;
                    break;
                }
            }

            // Determine prefix and parameter paths
            if (!$targetWlan && $wlanIndex >= 5) {
                $hasLan2 = isset($params['InternetGatewayDevice']['LANDevice']['2']);
                if ($hasLan2) {
                    $rawSlot = $wlanIndex - 4;
                    $prefix = "InternetGatewayDevice.LANDevice.2.WLANConfiguration.{$rawSlot}";
                } else {
                    $prefix = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}";
                }
            } else {
                $prefix = $targetWlan['prefix'] ?? $targetWlan['full_path'] ?? "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}";
            }
            $ssidPath = $targetWlan['ssid_path'] ?? "{$prefix}.SSID";
            $passPath = $targetWlan['password_path'] ?? "{$prefix}.PreSharedKey.1.KeyPassphrase";
            $enablePath = $targetWlan['enable_path'] ?? "{$prefix}.Enable";
            $hidePath = $targetWlan['hide_path'] ?? "{$prefix}.SSIDAdvertisementEnabled";

            $kv = [];
            $expected = [];

            if ($request->has('ssid')) {
                $ssid = trim($request->input('ssid'));
                $kv[$ssidPath] = $ssid;
                $expected[$ssidPath] = $ssid;
                if ($wlanIndex === 1) {
                    $kv["VirtualParameters.WlanSsid"] = $ssid;
                    $kv["VirtualParameters.WlanSSID"] = $ssid;
                }
            }

            $secUpper = strtoupper((string) $request->input('security_mode', ''));

            if ($request->has('password') && $secUpper !== 'OPEN') {
                $pass = $request->input('password');
                $kv[$passPath] = $pass;
                if ($passPath !== "{$prefix}.KeyPassphrase" && !str_starts_with($prefix, 'Device.')) {
                    $kv["{$prefix}.KeyPassphrase"] = $pass;
                }
                $expected[$passPath] = $pass;
                if ($wlanIndex === 1) {
                    $kv["VirtualParameters.WlanPassword"] = $pass;
                }
            }

            if ($request->has('enabled')) {
                $val = $request->boolean('enabled');
                $kv[$enablePath] = $val;
                $expected[$enablePath] = $val;
            }

            if ($request->has('hide_ssid')) {
                $val = !$request->boolean('hide_ssid');
                $kv[$hidePath] = $val;
                $expected[$hidePath] = $val;
            }

            if ($request->has('security_mode') && !str_starts_with($prefix, 'Device.')) {
                $sec = strtoupper((string) $request->input('security_mode'));
                if ($sec === 'OPEN') {
                    $openBeacon = $this->driver->resolveOpenBeaconValue('default', $cleanId);
                    $kv["{$prefix}.BeaconType"] = $openBeacon;
                    if ($openBeacon === 'Basic') {
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

            // Execute through AcsVerificationEngine with read-after-write verification
            $verifyResult = $this->verificationEngine->executeWithVerification(
                deviceId: $realDeviceId,
                taskType: 'wifi_update',
                expectedChanges: $expected,
                writeCallback: function () use ($realDeviceId, $kv) {
                    $ok = $this->driver->setParameterValues($realDeviceId, $kv);
                    return [
                        'success' => $ok,
                        'message' => $ok ? 'Task setParameterValues Wi-Fi terkirim ke GenieACS.' : 'Gagal mengirim task ke GenieACS.',
                    ];
                },
                userId: $user?->id,
                refreshPath: $prefix,
                autoVerifyImmediately: true
            );

            // Sync with local ONU record if primary SSID
            if ($onu && ($wlanIndex === 1 || ($targetWlan && $targetWlan['display_index'] === 1))) {
                if (isset($kv[$ssidPath])) {
                    $onu->wifi_ssid = $kv[$ssidPath];
                }
                if (isset($kv[$passPath])) {
                    $onu->wifi_password = $kv[$passPath];
                }
                $onu->saveQuietly();
            }

            return response()->json([
                'success' => $verifyResult['success'],
                'status' => $verifyResult['status'],
                'task_id' => $verifyResult['task_id'] ?? null,
                'message' => $verifyResult['message'],
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

        // Map mode
        $mode = 'pppoe_route';
        if ($isBridge) {
            $mode = $isPppoe ? 'bridge_ppp' : 'bridge_ip';
        } elseif (!$isPppoe) {
            $mode = ($request->input('addressing_type') === 'Static' || $request->has('ip_address')) ? 'static_route' : 'dhcp_route';
        }

        // Map payload
        $payload = array_merge($request->all(), [
            'mode' => $mode,
            'vlan_id' => $request->input('vlan_id', 0),
            'vlan_enabled' => $request->boolean('vlan_enabled', $request->input('vlan_id', 0) > 0),
            'username' => $request->input('username'),
            'password' => $request->input('password'),
            'service_list' => $request->input('service_list') ?: $request->input('service_type', 'INTERNET'),
        ]);

        if ($request->boolean('is_new_instance')) {
            $res = $this->wanService->addWanConnection($cleanId, $payload, $user->id);
            return response()->json([
                'success' => $res['success'],
                'status' => $res['status'] ?? null,
                'task_id' => $res['task_id'] ?? null,
                'message' => $res['message'],
            ]);
        }

        // Search existing WAN by wan_index or matching slot
        try {
            $wanData = $this->wanService->getWanConnections($cleanId);
            $targetConn = null;
            foreach ($wanData['connections'] as $conn) {
                if (($conn['instance_number'] ?? null) === $wanIndex || ($conn['index'] ?? null) === $wanIndex) {
                    $targetConn = $conn;
                    break;
                }
            }

            if ($targetConn && !empty($targetConn['full_path'])) {
                $res = $this->wanService->updateWanConnection($cleanId, $targetConn['full_path'], $payload, $user->id);
                return response()->json([
                    'success' => $res['success'],
                    'status' => $res['status'] ?? null,
                    'task_id' => $res['task_id'] ?? null,
                    'message' => $res['message'],
                ]);
            }

            // Fallback: create as new connection if not found
            $res = $this->wanService->addWanConnection($cleanId, $payload, $user->id);
            return response()->json([
                'success' => $res['success'],
                'status' => $res['status'] ?? null,
                'task_id' => $res['task_id'] ?? null,
                'message' => $res['message'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengatur WAN: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Dynamic WAN Connections list for mobile app.
     */
    public function getWanList(Request $request, string $id): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        $cleanId = urldecode($id);
        if ($devCheck = $this->validateDeviceAccess($request->user(), $cleanId, 'read')) {
            return $devCheck;
        }

        try {
            $data = $this->wanService->getWanConnections($cleanId);
            return response()->json([
                'success' => true,
                'vendor' => $data['vendor'],
                'data' => $data['connections'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar WAN: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add new WAN instance via mobile API.
     */
    public function addWan(Request $request, string $id): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        $cleanId = urldecode($id);
        if ($devCheck = $this->validateDeviceAccess($request->user(), $cleanId, 'wan')) {
            return $devCheck;
        }

        $request->validate([
            'mode' => 'required|in:pppoe_route,dhcp_route,bridge_ppp,bridge_ip,static_route',
            'vlan_id' => 'nullable|numeric|min:1|max:4094',
            'vlan_enabled' => 'nullable|boolean',
            'username' => 'nullable|string|max:128',
            'password' => 'nullable|string|max:128',
            'service_list' => 'nullable|string|max:64',
            'nat_enabled' => 'nullable|boolean',
            'bind_lan' => 'nullable|array',
            'bind_wlan' => 'nullable|array',
            'custom_binding' => 'nullable|string',
        ]);

        try {
            $res = $this->wanService->addWanConnection($cleanId, $request->all(), $request->user()->id);

            try {
                AuditLog::create([
                    'user_id' => $request->user()?->id,
                    'action' => 'acs_add_wan',
                    'description' => "Tambah WAN baru pada device {$cleanId} (mode: {$request->input('mode')}, vlan: {$request->input('vlan_id')}) via Mobile API.",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (\Throwable) {}

            return response()->json([
                'success' => $res['success'],
                'task_id' => $res['task_id'] ?? null,
                'message' => $res['message'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat WAN: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update existing WAN instance via mobile API.
     */
    public function updateWan(Request $request, string $id): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        $cleanId = urldecode($id);
        if ($devCheck = $this->validateDeviceAccess($request->user(), $cleanId, 'wan')) {
            return $devCheck;
        }

        $request->validate([
            'full_path' => 'required|string',
            'mode' => 'required|in:pppoe_route,dhcp_route,bridge_ppp,bridge_ip,static_route',
            'vlan_id' => 'nullable|numeric|min:1|max:4094',
            'vlan_enabled' => 'nullable|boolean',
            'username' => 'nullable|string|max:128',
            'password' => 'nullable|string|max:128',
            'service_list' => 'nullable|string|max:64',
            'nat_enabled' => 'nullable|boolean',
            'bind_lan' => 'nullable|array',
            'bind_wlan' => 'nullable|array',
            'custom_binding' => 'nullable|string',
        ]);

        try {
            $fullPath = $request->input('full_path');
            $res = $this->wanService->updateWanConnection($cleanId, $fullPath, $request->all(), $request->user()->id);

            try {
                AuditLog::create([
                    'user_id' => $request->user()?->id,
                    'action' => 'acs_update_wan',
                    'description' => "Update WAN {$fullPath} pada device {$cleanId} via Mobile API.",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (\Throwable) {}

            return response()->json([
                'success' => $res['success'],
                'task_id' => $res['task_id'] ?? null,
                'message' => $res['message'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui WAN: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete WAN instance via mobile API.
     */
    public function deleteWan(Request $request, string $id): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        $cleanId = urldecode($id);
        if ($devCheck = $this->validateDeviceAccess($request->user(), $cleanId, 'wan')) {
            return $devCheck;
        }

        $request->validate([
            'full_path' => 'required|string',
        ]);

        $fullPath = $request->input('full_path');
        if (str_contains(strtoupper($fullPath), 'TR069')) {
            return response()->json([
                'success' => false,
                'message' => 'Koneksi TR-069 dilindungi dan tidak boleh dihapus.',
            ], 403);
        }

        try {
            $res = $this->wanService->deleteWanConnection($cleanId, $fullPath, $request->user()->id);

            try {
                AuditLog::create([
                    'user_id' => $request->user()?->id,
                    'action' => 'acs_delete_wan',
                    'description' => "Hapus WAN {$fullPath} pada device {$cleanId} via Mobile API.",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (\Throwable) {}

            return response()->json([
                'success' => $res['success'],
                'task_id' => $res['task_id'] ?? null,
                'message' => $res['message'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus WAN: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh WAN instances from device via TR-069.
     */
    public function refreshWan(Request $request, string $id): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        $cleanId = urldecode($id);
        if ($devCheck = $this->validateDeviceAccess($request->user(), $cleanId, 'read')) {
            return $devCheck;
        }

        try {
            $realDeviceId = $this->driver->resolveDeviceId($cleanId);
            $this->driver->refreshObject($realDeviceId, 'InternetGatewayDevice.WANDevice');
            $this->driver->refreshObject($realDeviceId, 'Device.PPP.Interface');
            $this->driver->refreshObject($realDeviceId, 'Device.IP.Interface');
            $this->driver->summonDeviceResult($realDeviceId);

            return response()->json([
                'success' => true,
                'message' => 'Permintaan refresh objek WAN berhasil dikirim ke perangkat.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal merefresh WAN: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get comprehensive hardware monitoring telemetry.
     */
    public function getHardware(Request $request, string $id): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        $cleanId = urldecode($id);
        if ($devCheck = $this->validateDeviceAccess($request->user(), $cleanId, 'read')) {
            return $devCheck;
        }

        try {
            $realDeviceId = $this->driver->resolveDeviceId($cleanId);
            $params = $this->driver->getDeviceParameters($realDeviceId);
            $flat = \App\Services\ACS\AcsTelemetryParser::flatten($params);

            $ad = \App\Models\ACS\ACSDevice::withoutGlobalScopes()->where('uuid', $realDeviceId)->orWhere('serial_number', $realDeviceId)->first();
            $metrics = \App\Services\ACS\AcsTelemetryParser::parseHardwareMetrics($flat, $ad);

            return response()->json([
                'success' => true,
                'data' => $metrics,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data hardware: ' . $e->getMessage(),
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

        $cleanUpper = strtoupper(trim($cleanId));
        $parts = explode('-', $cleanUpper);
        $candidateSn = end($parts);

        // 1. Dual-write to dsBilling database: If tag is LOC_ or ODP_, update coordinates immediately
        $dbUpdated = false;
        if (str_starts_with($tag, 'LOC_')) {
            $locParts = explode('_', $tag);
            if (count($locParts) >= 3 && is_numeric($locParts[1]) && is_numeric($locParts[2])) {
                $lat = ((float)$locParts[1]) / 1000000.0;
                $lng = ((float)$locParts[2]) / 1000000.0;
                \App\Models\ISP\Onu::withoutGlobalScopes()->where(function ($q) use ($cleanUpper, $candidateSn) {
                    $q->where('serial_number', $cleanUpper)
                      ->orWhere('serial_number', $candidateSn)
                      ->orWhere('mac_address', $cleanUpper);
                })->update(['latitude' => $lat, 'longitude' => $lng]);
                $dbUpdated = true;
            }
        }

        // 2. Resolve actual GenieACS _id if cleanId is candidate
        $actualGenieId = $cleanId;
        try {
            $devParams = $this->driver->getDeviceParameters($cleanId);
            if (!empty($devParams['_id'])) {
                $actualGenieId = $devParams['_id'];
            }
        } catch (\Throwable) {}

        try {
            $baseUrl = rtrim(config('genieacs.base_url', 'http://localhost:7557'), '/');
            $resp = \Illuminate\Support\Facades\Http::withBasicAuth(
                config('genieacs.username', 'admin'),
                config('genieacs.password', 'admin')
            )->post("{$baseUrl}/devices/" . urlencode($actualGenieId) . '/tags/' . urlencode($tag));

            if ($resp->successful() || $resp->status() === 200 || $resp->status() === 201 || $dbUpdated) {
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
            if ($dbUpdated) {
                return response()->json([
                    'success' => true,
                    'message' => "Tag '{$tag}' tersimpan di database lokal.",
                ]);
            }
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

        $cleanUpper = strtoupper(trim($cleanId));
        $parts = explode('-', $cleanUpper);
        $candidateSn = end($parts);

        $actualGenieId = $cleanId;
        try {
            $devParams = $this->driver->getDeviceParameters($cleanId);
            if (!empty($devParams['_id'])) {
                $actualGenieId = $devParams['_id'];
            }
        } catch (\Throwable) {}

        $cleanTag = trim(urldecode($tag));
        try {
            $baseUrl = rtrim(config('genieacs.base_url', 'http://localhost:7557'), '/');
            $resp = \Illuminate\Support\Facades\Http::withBasicAuth(
                config('genieacs.username', 'admin'),
                config('genieacs.password', 'admin')
            )->delete("{$baseUrl}/devices/" . urlencode($actualGenieId) . '/tags/' . urlencode($cleanTag));

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

    /**
     * Delete a single device from GenieACS and local ACS cache.
     * DELETE /api/mobile/acs/devices/{id}
     */
    public function deleteDevice(Request $request, string $id): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        $cleanId = urldecode($id);
        if ($devCheck = $this->validateDeviceAccess($request->user(), $cleanId, 'delete')) {
            return $devCheck;
        }

        $user = $request->user();
        $actualGenieId = $this->resolveGenieAcsDeviceId($cleanId);

        try {
            // 1. Delete from GenieACS via driver with non-blocking error handling
            try {
                $deletedFromGenie = $this->driver->deleteDevice($actualGenieId);
                if (!$deletedFromGenie && $actualGenieId !== $cleanId) {
                    $this->driver->deleteDevice($cleanId);
                }
            } catch (\Throwable $acsEx) {
                Log::warning("MobileAcsProxy deleteDevice non-fatal GenieACS error: {$acsEx->getMessage()}");
            }

            // 2. Also remove from local ACS cache and matching ONU records
            $cleanUpper = strtoupper(trim($cleanId));
            $parts = explode('-', $cleanUpper);
            $candidateSn = end($parts);

            \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                ->where(function ($q) use ($cleanUpper, $actualGenieId, $candidateSn) {
                    $q->where('uuid', $actualGenieId)
                      ->orWhere('serial_number', $cleanUpper)
                      ->orWhere('serial_number', $candidateSn);
                })
                ->delete();

            \App\Models\ISP\Onu::withoutGlobalScopes()
                ->where(function ($q) use ($cleanUpper, $candidateSn) {
                    $q->where('serial_number', $cleanUpper)
                      ->orWhere('serial_number', $candidateSn);
                })
                ->delete();

            // 3. Audit Log
            AuditLog::record(
                $user,
                'acs.device.delete',
                "Menghapus perangkat {$cleanId} (GenieACS ID: {$actualGenieId}) dari TR-069 ACS",
                ['device_id' => $cleanId, 'genie_id' => $actualGenieId]
            );

            return response()->json([
                'success' => true,
                'message' => "Perangkat {$cleanId} berhasil dihapus dari sistem.",
            ]);
        } catch (\Throwable $e) {
            Log::warning("MobileAcsProxy deleteDevice error: {$e->getMessage()}");
            // Always return success if local cache cleaned, avoid crude 500
            return response()->json([
                'success' => true,
                'message' => "Perangkat {$cleanId} telah dihapus dari basis data lokal.",
            ]);
        }
    }

    /**
     * Bulk purge offline devices from GenieACS.
     * POST /api/mobile/acs/devices/purge-offline
     */
    public function purgeOffline(Request $request): JsonResponse
    {
        if ($authCheck = $this->authorizeAcsAccess($request)) {
            return $authCheck;
        }

        $user = $request->user();
        if (!$user->isSuperAdmin() && !$user->hasRole(['administrator', 'admin', 'noc', 'teknisi', 'technician', 'manager', 'reseller'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya teknisi atau pengelola jaringan yang dapat membersihkan modem offline.',
            ], 403);
        }

        try {
            $devices = $this->driver->listDevices([], 1000);
            $purgedCount = 0;
            $cutoff = now()->subMinutes(15);

            foreach ($devices as $dev) {
                $devId = $dev['_id'] ?? '';
                if (empty($devId)) continue;

                $lastInform = $dev['_lastInform'] ?? null;
                $isOffline = false;

                if (!$lastInform) {
                    $isOffline = true;
                } else {
                    $dt = \Carbon\Carbon::parse($lastInform);
                    if ($dt->lt($cutoff)) {
                        $isOffline = true;
                    }
                }

                if ($user->hasRole('reseller')) {
                    $effectiveResellerId = $user->getEffectiveResellerId() ?: $user->id;
                    $sn = $dev['_deviceId']['_SerialNumber'] ?? $devId;
                    $cs = \App\Models\Customer\CustomerService::withoutGlobalScopes()
                        ->where('onu_serial', $sn)
                        ->first();
                    if ($cs && $cs->reseller_id && $cs->reseller_id != $effectiveResellerId) {
                        continue;
                    }
                }

                if ($isOffline) {
                    if ($this->driver->deleteDevice($devId)) {
                        $purgedCount++;
                        \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                            ->where('uuid', $devId)
                            ->orWhere('serial_number', $dev['_deviceId']['_SerialNumber'] ?? '')
                            ->delete();
                    }
                }
            }

            AuditLog::record(
                $user,
                'acs.device.purge_offline',
                "Membersihkan {$purgedCount} perangkat modem offline dari GenieACS TR-069",
                ['purged_count' => $purgedCount]
            );

            return response()->json([
                'success' => true,
                'message' => "Berhasil membersihkan {$purgedCount} perangkat modem yang sedang offline dari GenieACS.",
                'purged_count' => $purgedCount,
            ]);
        } catch (\Throwable $e) {
            Log::warning("MobileAcsProxy purgeOffline error: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Gagal membersihkan perangkat offline: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resolve actual GenieACS device _id from alias/serial number.
     */
    protected function resolveGenieAcsDeviceId(string $cleanId): string
    {
        try {
            return $this->driver->resolveDeviceId($cleanId);
        } catch (\Throwable) {
            return $cleanId;
        }
    }
}
