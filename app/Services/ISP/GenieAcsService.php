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

    public function getWifiCredentials(int $customerId, int $onuId): array
    {
        $onu = $this->resolveCustomerOnu($customerId, $onuId);
        
        $deviceId = null;
        try {
            $deviceId = \App\Models\ISP\DeviceCorrelation::where('onu_id', $onu->id)->value('genieacs_device_id');
        } catch (\Throwable) {}

        if (empty($deviceId)) {
            try {
                $deviceId = \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                    ->where('onu_id', $onu->id)
                    ->orWhere('serial_number', $onu->serial_number)
                    ->value('uuid');
            } catch (\Throwable) {}
        }

        if (empty($deviceId)) {
            $deviceId = $onu->customerService?->attributes['genieacs_device_id'] 
                ?? $onu->attributes['genieacs_device_id'] 
                ?? $onu->genieacs_device_id;
        }

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
            $deviceId = \App\Models\ISP\DeviceCorrelation::where('onu_id', $onu->id)->value('genieacs_device_id')
                ?? $onu->genieacs_device_id;
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
