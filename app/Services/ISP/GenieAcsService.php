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
        return $this->provisioningService->refreshAndSyncSignal(
            $this->resolveCustomerOnu($customerId, $onuId)
        );
    }

    public function getDeviceStatus(int $customerId, int $onuId): array
    {
        return $this->provisioningService->refreshAndSyncSignal(
            $this->resolveCustomerOnu($customerId, $onuId)
        );
    }

    public function getDeviceSignal(int $customerId, int $onuId): array
    {
        return $this->provisioningService->refreshAndSyncSignal(
            $this->resolveCustomerOnu($customerId, $onuId)
        );
    }

    public function updateWifiPassword(int $customerId, int $onuId, string $newPassword): array
    {
        $onu = $this->resolveCustomerOnu($customerId, $onuId);
        $ssid = $onu->wifi_ssid ?: ('ISP-' . strtoupper(substr(md5((string)$onu->id), 0, 6)));
        return $this->provisioningService->updateSsidAndPassword($onu, $ssid, $newPassword);
    }

    public function updateWifiSsidAndPassword(int $customerId, int $onuId, string $ssid, string $password): array
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
        $cs = CustomerService::where('customer_id', $customerId)->where('onu_id', $onuId)->first();
        if (!$cs) {
            throw new Exception('Perangkat ONU tidak ditemukan atau bukan milik customer');
        }
        return Onu::with(['olt', 'vendor', 'customerService'])->findOrFail($onuId);
    }
}
