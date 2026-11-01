<?php

namespace App\Services\ISP;

use App\Models\Customer\CustomerService;
use App\Models\ISP\Onu;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use App\Services\Adapters\Provisioning\OltRegistry;
use Exception;
use Illuminate\Support\Facades\Log;

class GenieAcsService
{
    public function __construct(
        protected GenieACSDriver $genieACSDriver,
        protected GenieAcsProvisioningService $provisioningService,
        protected OltRegistry $oltRegistry,
    ) {
    }

    public function getDeviceParameters(int $customerId, int $onuId): array
    {
        $onu = $this->resolveCustomerOnu($customerId, $onuId);
        return $this->genieACSDriver->getDeviceParameters($onu->genieacs_device_id);
    }

    public function getDeviceStatus(int $customerId, int $onuId): array
    {
        $onu = $this->resolveCustomerOnu($customerId, $onuId);
        $isOnline = $this->genieACSDriver->isDeviceOnline($onu->genieacs_device_id);
        return ['online' => $isOnline];
    }

    public function getDeviceSignal(int $customerId, int $onuId): array
    {
        $onu = $this->resolveCustomerOnu($customerId, $onuId);
        return $this->genieACSDriver->getDeviceSignal($onu->genieacs_device_id);
    }

    public function resolveGenieAcsDeviceId(Onu $onu, ?int $customerId = null): ?string
    {
        // 1. Direct device correlation
        try {
            $deviceId = \App\Models\ISP\DeviceCorrelation::where('onu_id', $onu->id)->value('genieacs_device_id');
            if (!empty($deviceId)) {
                return $deviceId;
            }
        } catch (\Throwable) {}

        // 2. Direct genieacs_device_id on ONU or CustomerService
        if (!empty($onu->genieacs_device_id)) {
            return $onu->genieacs_device_id;
        }
        $csDeviceId = $onu->customerService?->attributes['genieacs_device_id'] ?? null;
        if (!empty($csDeviceId)) {
            return $csDeviceId;
        }

        // 3. Search ACSDevice model
        try {
            $acsDevice = \App\Models\ACS\ACSDevice::withoutGlobalScopes()->where('onu_id', $onu->id)->first();

            if (!$acsDevice && !empty($onu->serial_number)) {
                $acsDevice = \App\Models\ACS\ACSDevice::withoutGlobalScopes()->where('serial_number', $onu->serial_number)->first();
            }

            $csId = $onu->customer_service_id ?? $onu->customerService?->id;
            if (!$acsDevice && !empty($csId)) {
                $acsDevice = \App\Models\ACS\ACSDevice::withoutGlobalScopes()->where('customer_service_id', $csId)->first();
            }

            if (!$acsDevice && !empty($onu->mac_address)) {
                $cleanMac = strtoupper(str_replace([':', '-', '.'], '', $onu->mac_address));
                $acsDevice = \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                    ->where(function ($q) use ($onu, $cleanMac) {
                        $q->where('mac_address', $onu->mac_address)
                          ->orWhere('mac_address', $cleanMac)
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(mac_address, ':', ''), '-', ''), '.', '') = ?", [$cleanMac]);
                    })->first();
            }

            // Suffix matching (e.g. VSOL0033EDA0 vs 12345B4641533EDA0, both ending with 33EDA0)
            if (!$acsDevice && !empty($onu->serial_number) && strlen($onu->serial_number) >= 6) {
                $suffix = substr($onu->serial_number, -6);
                $acsDevice = \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                    ->where('serial_number', 'LIKE', "%{$suffix}%")
                    ->first();
            }

            if (!$acsDevice && !empty($customerId)) {
                $acsDevice = \App\Models\ACS\ACSDevice::withoutGlobalScopes()->where('customer_id', $customerId)->first();
            }

            if ($acsDevice) {
                // Auto-link to ONU for instant future lookups
                if (!$acsDevice->onu_id) {
                    try {
                        $acsDevice->update(['onu_id' => $onu->id]);
                    } catch (\Throwable) {}
                }
                // Save correlation
                try {
                    \App\Models\ISP\DeviceCorrelation::updateOrCreate(
                        ['onu_id' => $onu->id],
                        ['genieacs_device_id' => $acsDevice->uuid, 'matched_by' => 'auto', 'status' => 'auto_bind']
                    );
                } catch (\Throwable) {}

                return $acsDevice->uuid;
            }
        } catch (\Throwable $e) {
            Log::warning('resolveGenieAcsDeviceId ACSDevice lookup error: ' . $e->getMessage());
        }

        // 4. Fallback to using ONU serial number
        return $onu->serial_number ?: null;
    }

    public function getWifiCredentials(int $customerId, int $onuId): array
    {
        $onu = $this->resolveCustomerOnu($customerId, $onuId);
        $deviceId = $this->resolveGenieAcsDeviceId($onu, $customerId);

        $vendor = $onu->vendor?->name ?? 'default';

        $ssid = null;
        $password = null;
        $live = [];

        if (!empty($deviceId)) {
            try {
                $live = $this->genieACSDriver->getWifiCredentials($deviceId, $vendor);
                if (!empty($live['ssid'])) {
                    $ssid = $live['ssid'];
                }
                if (!empty($live['password']) && $live['password'] !== '******') {
                    $password = $live['password'];
                }

                if (!empty($live['real_device_id'])) {
                    try {
                        \App\Models\ISP\DeviceCorrelation::updateOrCreate(
                            ['onu_id' => $onu->id],
                            ['genieacs_device_id' => $live['real_device_id'], 'matched_by' => 'auto', 'status' => 'auto_bind']
                        );
                    } catch (\Throwable) {}
                }
            } catch (\Throwable $e) {
                Log::warning("GenieACS getWifiCredentials fallback to DB for ONU {$onuId}: " . $e->getMessage());
            }
        }

        // Fallback to database values stored in onus or customer_services
        if (empty($ssid)) {
            $ssid = $onu->wifi_ssid ?: ($onu->customerService?->attributes['wifi_ssid'] ?? null);
        }
        if (empty($password)) {
            $password = $onu->wifi_password ?: ($onu->customerService?->attributes['wifi_password'] ?? null);
        }

        // If live credentials successfully read from device, update DB to keep fresh
        if (!empty($live['ssid']) || (!empty($live['password']) && $live['password'] !== '******')) {
            try {
                $updateData = [];
                if (!empty($live['ssid']) && $live['ssid'] !== $onu->wifi_ssid) {
                    $updateData['wifi_ssid'] = $live['ssid'];
                }
                if (!empty($live['password']) && $live['password'] !== '******' && $live['password'] !== $onu->wifi_password) {
                    $updateData['wifi_password'] = $live['password'];
                }
                if (!empty($updateData)) {
                    $onu->update($updateData);
                }
            } catch (\Throwable) {}
        }

        return [
            'ssid' => $ssid,
            'password' => $password,
        ];
    }

    public function getConnectedDevices(int $customerId, int $onuId): array
    {
        try {
            $onu = $this->resolveCustomerOnu($customerId, $onuId);
            $deviceId = $this->resolveGenieAcsDeviceId($onu, $customerId);
            if (empty($deviceId)) {
                return [];
            }
            return $this->genieACSDriver->getConnectedDevices($deviceId);
        } catch (\Throwable $e) {
            Log::warning("GenieACS getConnectedDevices failed for ONU {$onuId}: " . $e->getMessage());
            return [];
        }
    }

    public function updateWifiPassword(int $customerId, int $onuId, string $newPassword): array
    {
        $onu = $this->resolveCustomerOnu($customerId, $onuId);
        $ssid = $onu->wifi_ssid ?: ('ISP-' . strtoupper(substr(md5((string)$onu->id), 0, 6)));
        return $this->provisioningService->updateSsidAndPassword($onu, $ssid, $newPassword);
    }

    public function updateWifiSsidAndPassword(int $customerId, int $onuId, string $ssid, ?string $password = null): array
    {
        $onu = $this->resolveCustomerOnu($customerId, $onuId);
        return $this->provisioningService->updateSsidAndPassword($onu, $ssid, $password);
    }

    public function rebootOnu(int $customerId, int $onuId): array
    {
        $onu = $this->resolveCustomerOnu($customerId, $onuId);
        if ($onu->olt) {
            try {
                $drv = $this->oltRegistry->forOlt($onu->olt);
                $drv->rebootOnu($onu);
            } catch (Exception $e) {
                Log::warning('OLT reboot ONU failed (fallback ke GenieACS)', ['msg' => $e->getMessage(), 'onu_id' => $onuId]);
            }
        }
        return $this->provisioningService->rebootOnu($onu);
    }

    public function factoryResetOnu(int $customerId, int $onuId): array
    {
        $onu = $this->resolveCustomerOnu($customerId, $onuId);
        return $this->provisioningService->factoryResetOnu($onu);
    }

    public function getTasks(int $customerId, int $onuId, ?string $status = null): array
    {
        $onu = $this->resolveCustomerOnu($customerId, $onuId);
        return $this->genieACSDriver->getTasks($onu->genieacs_device_id, $status);
    }

    public function getOnuTasks(Onu $onu, ?string $status = null): array
    {
        return $this->genieACSDriver->getTasks($onu->genieacs_device_id, $status);
    }

    public function listDevices(array $query = [], int $limit = 100): array
    {
        return $this->genieACSDriver->listDevices($query, $limit);
    }

    public function publishDefaultProvisions(): array
    {
        return $this->provisioningService->publishDefaultProvisions();
    }

    protected function resolveCustomerOnu(int $customerId, int $onuId): Onu
    {
        $cs = CustomerService::withoutGlobalScopes()
            ->where('customer_id', $customerId)
            ->where('onu_id', $onuId)
            ->first();

        if (!$cs) {
            throw new Exception('Perangkat ONU tidak ditemukan atau bukan milik customer');
        }

        return Onu::withoutGlobalScopes()->with(['olt', 'vendor', 'customerService'])->findOrFail($onuId);
    }
}
