<?php

namespace App\Services\ISP;

use App\Events\ISP\InternetPackageSaved;
use App\Models\AuditLog;
use App\Models\ISP\ServiceProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ServiceProfileService
{
    public function createProfile(array $data, User $user): ServiceProfile      
    {
        Log::info('Creating package', ['data' => $data, 'user_id' => $user->id]);

        return DB::transaction(function () use ($data, $user) {
            $data = $this->autoGenerateTechnicalFields($data, $user);
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;

            // Validate unique name per tenant
            $this->validateUniqueName($data['name'], $data['tenant_id'] ?? $user->tenant_id ?? null);

            // Auto set code from name if not provided
            if (empty($data['code'])) {
                $data['code'] = $this->generateUniqueCode($data['name']);       
            }

            $profile = ServiceProfile::create($data);

            $this->logAudit($profile, 'created', null, $profile->toArray(), $user);

            event(new InternetPackageSaved($profile));

            Log::info('Package created', ['profile_id' => $profile->id, 'code' => $profile->code]);

            return $profile;
        });
    }

    public function updateProfile(ServiceProfile $profile, array $data, User $user): ServiceProfile
    {
        Log::info('Updating package', ['profile_id' => $profile->id, 'data' => $data, 'user_id' => $user->id]);

        return DB::transaction(function () use ($profile, $data, $user) {       
            $oldValues = $profile->toArray();
            $data = $this->autoGenerateTechnicalFields($data, $user);
            $data['updated_by'] = $user->id;

            // Validate unique name per tenant if name changed
            if (isset($data['name']) && $data['name'] !== $profile->name) {     
                $this->validateUniqueName($data['name'], $profile->tenant_id ?? $user->tenant_id ?? null, $profile->id);
            }

            $profile->update($data);

            $this->logAudit($profile, 'updated', $oldValues, $profile->toArray(), $user);

            event(new InternetPackageSaved($profile));

            Log::info('Package updated', ['profile_id' => $profile->id]);       

            return $profile;
        });
    }

    public function cloneProfile(ServiceProfile $profile, User $user, array $newData = []): ServiceProfile
    {
        Log::info('Cloning package', ['original_id' => $profile->id, 'new_data' => $newData, 'user_id' => $user->id]);

        return DB::transaction(function () use ($profile, $user, $newData) {    
            $cloneData = $profile->toArray();

            // Generate new unique name and code
            $cloneData['name'] = $newData['name'] ?? ($profile->name . ' (Copy)');
            $cloneData['code'] = $newData['code'] ?? $this->generateUniqueCode($profile->name . '_copy');

            // Reset audit fields
            unset($cloneData['id'], $cloneData['created_at'], $cloneData['updated_at'], $cloneData['deleted_at'], $cloneData['created_by'], $cloneData['updated_by']);

            // Override with new data
            $cloneData = array_merge($cloneData, $newData);

            $clone = $this->createProfile($cloneData, $user);

            Log::info('Package cloned', ['original_id' => $profile->id, 'clone_id' => $clone->id]);

            return $clone;
        });
    }

    public function autoGenerateTechnicalFields(array $data, User $user): array 
    {
        $serviceType = $data['service_type'] ?? 'pppoe';
        $downloadSpeed = $data['download_speed'] ?? 10;
        $uploadSpeed = $data['upload_speed'] ?? 10;
        $name = $data['name'] ?? 'Unnamed';
        $code = $data['code'] ?? 'UNKNOWN';
        $validityValue = $data['validity_value'] ?? 30;
        $validityUnit = $data['validity_unit'] ?? 'days';
        $packageType = $data['package_type'] ?? 'unlimited';

        // Set default service_profile_type_id (ambil yang pertama atau default 1)
        if (empty($data['service_profile_type_id'])) {
            $defaultType = \App\Models\ISP\ServiceProfileType::first();
            $data['service_profile_type_id'] = $defaultType->id ?? 1;
        }

        // Auto set multi tenant fields if not provided
        if (!isset($data['owner_id'])) {
            $data['owner_id'] = $user->id;
        }
        if (!isset($data['tenant_id'])) {
            $data['tenant_id'] = $user->tenant_id ?? null;
        }
        if (!isset($data['branch_id'])) {
            $data['branch_id'] = $user->branch_id ?? null;
        }
        if (!isset($data['visibility'])) {
            $data['visibility'] = 'private';
        }

        // Handle free package
        if (isset($data['is_free']) && $data['is_free']) {
            $data['base_price'] = 0;
            $data['reseller_price'] = $data['reseller_price'] ?? 0;
        }

        // Set validity based on unit
        $data['validity_unit'] = $validityUnit;
        if ($validityUnit === 'days') {
            $data['validity_days'] = $validityValue;
            $data['validity_hours'] = null;
        } elseif ($validityUnit === 'hours') {
            $data['validity_hours'] = $validityValue;
            $data['validity_days'] = null;
        } elseif ($validityUnit === 'months') {
            $data['validity_days'] = $validityValue * 30;
            $data['validity_hours'] = null;
        }

        // Auto generate technical fields
        $data['queue_type'] = 'simple_queue';
        $data['priority'] = 8;
        $data['cir_download'] = $downloadSpeed * 1000000;
        $data['cir_upload'] = $uploadSpeed * 1000000;
        $data['mir_download'] = $downloadSpeed * 1000000;
        $data['mir_upload'] = $uploadSpeed * 1000000;
        $data['burst_limit_download'] = (int) ($downloadSpeed * 1.5);
        $data['burst_limit_upload'] = (int) ($uploadSpeed * 1.5);
        $data['burst_threshold_download'] = (int) ($downloadSpeed * 0.8);       
        $data['burst_threshold_upload'] = (int) ($uploadSpeed * 0.8);
        $data['burst_time_download'] = 60;
        $data['burst_time_upload'] = 60;

        $sanitizedName = preg_replace('/[^a-zA-Z0-9]/', '', $name);
        $sanitizedCode = preg_replace('/[^a-zA-Z0-9]/', '', $code);

        $data['radius_group_name'] = 'GRP-' . strtoupper($sanitizedCode);       
        $data['radius_rate_limit'] = $downloadSpeed . 'M/' . $uploadSpeed . 'M';

        // Set session timeout based on validity
        if ($validityUnit === 'days') {
            $data['radius_session_timeout'] = $validityValue * 86400;
        } elseif ($validityUnit === 'hours') {
            $data['radius_session_timeout'] = $validityValue * 3600;
        } else { // months
            $data['radius_session_timeout'] = $validityValue * 30 * 86400;      
        }

        $data['radius_idle_timeout'] = 1800;
        $data['radius_simultaneous_use'] = $data['max_devices'] ?? 1;
        $data['radius_mac_binding'] = false;
        $data['radius_framed_pool'] = 'Pool-' . strtoupper($sanitizedCode);     
        $data['radius_address_list'] = 'Allow-' . strtoupper($sanitizedCode);   
        $data['radius_attributes'] = [
            'MikroTik-Rate-Limit' => $data['radius_rate_limit'],
            'MikroTik-Group' => $data['radius_group_name'],
        ];

        if ($serviceType === 'hotspot' || $serviceType === 'voucher') {
            $data['target_hotspot_profile'] = 'HS-PROFILE-' . strtoupper($sanitizedName);
        }

        if ($serviceType === 'pppoe') {
            $data['ppp_profile_name'] = 'PPP-PROFILE-' . strtoupper($sanitizedName);
            $data['user_manager_profile'] = 'UM-PROFILE-' . strtoupper($sanitizedName);
        }

        if ($serviceType === 'ftth') {
            $data['ppp_profile_name'] = 'FTTH-PROFILE-' . strtoupper($sanitizedName);
            $data['vlan_id'] = $data['vlan_id'] ?? null;
            $data['bridge_interface'] = $data['bridge_interface'] ?? 'br-sERVICE';
            $data['interface_name'] = $data['interface_name'] ?? 'ether-sERVICE';
        }

        $data['account_type'] = ($data['validity_days'] ?? 0) > 0 ? 'limited' : 'unlimited';
        $data['allowed_login_days'] = [0, 1, 2, 3, 4, 5, 6];
        $data['login_start_time'] = $data['login_start_time'] ?? '00:00';       
        $data['login_end_time'] = $data['login_end_time'] ?? '23:59';
        $data['idle_disconnect_policy'] = 'auto';

        $data['ip_pool_parent'] = 'pool-service';
        $data['custom_dns'] = ['8.8.8.8', '8.8.4.4'];

        $data['auto_suspend_days'] = $data['auto_suspend_days'] ?? 7;
        $data['auto_activate_after_payment'] = true;

        $data['advanced_settings'] = [
            'auto_provisioned' => true,
            'provisioned_at' => now()->toIso8601String(),
            'backend_version' => '2.0',
        ];

        return $data;
    }

    public function deleteProfile(ServiceProfile $profile, User $user): bool    
    {
        try {
            DB::transaction(function () use ($profile, $user) {
                // Check if profile has active customers
                if ($profile->customerServices()->where('status', 'active')->count() > 0) {
                    throw new \Exception('Tidak dapat menghapus paket yang masih memiliki pelanggan aktif.');
                }

                $oldValues = $profile->toArray();
                $profile->delete();
                $this->logAudit($profile, 'deleted', $oldValues, null, $user);  
            });

            Log::info('Package deleted', ['profile_id' => $profile->id]);       
            return true;
        } catch (\Exception $e) {
            Log::warning('Delete profile failed', ['profile_id' => $profile->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function restoreProfile(ServiceProfile $profile, User $user): void   
    {
        Log::info('Restoring package', ['profile_id' => $profile->id, 'user_id' => $user->id]);

        DB::transaction(function () use ($profile, $user) {
            $profile->restore();
            $this->logAudit($profile, 'restored', null, $profile->toArray(), $user);

            Log::info('Package restored', ['profile_id' => $profile->id]);      
        });
    }

    public function bulkDelete(array $ids, User $user): array
    {
        $successCount = 0;
        $failedCount = 0;

        // Process each profile individually (don't wrap all in single transaction)
        foreach ($ids as $id) {
            try {
                $profile = ServiceProfile::find($id);
                if ($profile && $this->deleteProfile($profile, $user)) {        
                    $successCount++;
                } else {
                    $failedCount++;
                }
            } catch (\Exception $e) {
                $failedCount++;
                Log::warning('Bulk delete individual failed', ['profile_id' => $id, 'error' => $e->getMessage()]);
            }
        }

        // Log bulk action
        try {
            $this->logAudit(null, 'bulk_delete', ['ids' => $ids, 'success_count' => $successCount, 'failed_count' => $failedCount], null, $user);
        } catch (\Exception $e) {
            Log::error('Bulk delete audit failed', ['error' => $e->getMessage()]);
        }

        Log::info('Bulk delete completed', ['success' => $successCount, 'failed' => $failedCount]);

        return [
            'success' => $successCount,
            'failed' => $failedCount
        ];
    }

    public function bulkActivate(array $ids, User $user): void
    {
        Log::info('Bulk activating packages', ['ids' => $ids, 'user_id' => $user->id]);

        DB::transaction(function () use ($ids, $user) {
            ServiceProfile::whereIn('id', $ids)->update(['status' => 'active', 'updated_by' => $user->id]);
            $this->logAudit(null, 'bulk_activate', ['ids' => $ids], ['status' => 'active'], $user);

            Log::info('Packages bulk activated', ['ids' => $ids]);
        });
    }

    public function bulkDeactivate(array $ids, User $user): void
    {
        Log::info('Bulk deactivating packages', ['ids' => $ids, 'user_id' => $user->id]);

        DB::transaction(function () use ($ids, $user) {
            ServiceProfile::whereIn('id', $ids)->update(['status' => 'inactive', 'updated_by' => $user->id]);
            $this->logAudit(null, 'bulk_deactivate', ['ids' => $ids], ['status' => 'inactive'], $user);

            Log::info('Packages bulk deactivated', ['ids' => $ids]);
        });
    }
    
    public function bulkEdit(array $ids, array $data, User $user): array
    {
        Log::info('Bulk editing packages', ['ids' => $ids, 'data' => $data, 'user_id' => $user->id]);
        
        $updated = 0;
        $failed = 0;
        
        DB::beginTransaction();
        
        try {
            foreach ($ids as $id) {
                try {
                    $profile = ServiceProfile::findOrFail($id);
                    
                    $updateData = array_filter($data, fn($value) => $value !== null && $value !== '');
                    
                    if (!empty($updateData)) {
                        $updateData['updated_by'] = $user->id;
                        
                        if (isset($updateData['download_speed']) || isset($updateData['upload_speed'])) {
                            $mergedData = array_merge($profile->toArray(), $updateData);
                            $autoGenerated = $this->autoGenerateTechnicalFields($mergedData, $user);
                            $updateData = array_merge($updateData, array_intersect_key($autoGenerated, array_flip([
                                'cir_download', 'cir_upload', 'mir_download', 'mir_upload',
                                'burst_limit_download', 'burst_limit_upload',
                                'burst_threshold_download', 'burst_threshold_upload',
                                'burst_time_download', 'burst_time_upload',
                                'radius_rate_limit', 'queue_type'
                            ])));
                        }
                        
                        $profile->update($updateData);
                        $this->logAudit($profile, 'bulk_updated', $profile->toArray(), array_merge($profile->toArray(), $updateData), $user);
                        $updated++;
                    }
                } catch (\Exception $e) {
                    Log::error('Bulk edit failed for package', ['id' => $id, 'error' => $e->getMessage()]);
                    $failed++;
                }
            }
            
            $this->logAudit(null, 'bulk_edit', ['ids' => $ids, 'data' => $data], null, $user);
            
            DB::commit();
            
            Log::info('Bulk edit completed', ['updated' => $updated, 'failed' => $failed]);
            
            return ['updated' => $updated, 'failed' => $failed];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function validateUniqueName(string $name, ?int $tenantId = null, ?int $excludeId = null): void
    {
        $query = ServiceProfile::where('name', $name);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        } else {
            $query->whereNull('tenant_id');
        }

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new \Exception('Nama paket sudah digunakan dalam tenant ini.');
        }
    }

    protected function generateUniqueCode(string $baseName): string
    {
        $baseCode = Str::upper(Str::slug($baseName, ''));
        $counter = 1;
        $code = $baseCode;

        while (ServiceProfile::where('code', $code)->exists()) {
            $code = $baseCode . $counter++;
        }

        return $code;
    }

    protected function logAudit($model, string $event, ?array $oldValues, ?array $newValues, User $user): void
    {
        try {
            if ($model) {
                AuditLog::create([
                    'auditable_type' => get_class($model),
                    'auditable_id' => $model->id,
                    'event' => $event,
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'user_id' => $user->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            } else {
                AuditLog::create([
                    'event' => $event,
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'user_id' => $user->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Audit log failed: ' . $e->getMessage(), [
                'model' => $model ? get_class($model) : null,
                'id' => $model->id ?? null,
            ]);
        }
    }
}
