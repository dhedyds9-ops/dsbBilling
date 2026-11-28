<?php

namespace App\Services\Provisioning;

use App\Models\Customer\CustomerService;
use App\Models\ISP\PPPoEUser;
use App\Services\Adapters\Provisioning\OltRegistry;
use App\Services\ISP\GenieAcsProvisioningService;
use App\Services\ISP\OdpOccupancyService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Src\Domain\Customer\Events\ServiceActivatedEvent;
use Throwable;

/**
 * ProvisioningOrchestrator — pintu tunggal (facade) seluruh aksi provisioning perangkat.
 *
 * Semua entry point provisioning (registrasi, event-driven, REST/engine, pipeline step)
 * didelegasikan ke sini agar logika OLT-QoS, ACS/TR-069, dan aktivasi tidak terduplikasi.
 */
class ProvisioningOrchestrator
{
    public function __construct(
        protected OltRegistry $oltRegistry,
        protected GenieAcsProvisioningService $acsProvisioning,
        protected OdpOccupancyService $odpOccupancy,
    ) {
    }

    /**
     * Provisioning penuh satu ONU: OLT QoS + ACS/TR-069 + rekalkulasi ODP.
     */
    public function provisionOnu(CustomerService $customerService): array
    {
        $onu = $customerService->onu;
        if (!$onu) {
            return ['status' => 'skipped', 'reason' => 'CustomerService tidak memiliki ONU.'];
        }

        $olt = $this->provisionOltQos($customerService);
        $acs = $this->provisionAcs($customerService);

        if ($olt['status'] === 'failed') {
            $onu->provision_status = 'olt_failed';
            $onu->saveQuietly();
        }

        if ($onu->odp_id) {
            try {
                $this->odpOccupancy->recalculateOne($onu->odp);
            } catch (Throwable $e) {
                Log::error('ProvisioningOrchestrator: rekalkulasi ODP gagal', [
                    'onu_id' => $onu->id,
                    'msg' => $e->getMessage(),
                ]);
            }
        }

        Log::info('ProvisioningOrchestrator: provisioning ONU selesai', [
            'onu_id' => $onu->id,
            'olt' => $olt['status'],
            'acs' => $acs['status'] ?? null,
        ]);

        return ['status' => 'done', 'olt' => $olt, 'acs' => $acs];
    }

    /**
     * SSOT konfigurasi OLT: admin status + limitasi bandwidth dari ServiceProfile (satuan Mbps).
     */
    public function provisionOltQos(CustomerService $customerService): array
    {
        $onu = $customerService->onu;
        if (!$onu || !$onu->olt || !$onu->serial_number || !$onu->pon_port) {
            return ['status' => 'skipped', 'reason' => 'ONU belum terhubung ke OLT/PON port.'];
        }

        $profile = $customerService->serviceProfile;
        $download = (int) ($profile->download_speed ?? 0);
        $upload = (int) ($profile->upload_speed ?? 0);

        if ($download <= 0 || $upload <= 0) {
            return ['status' => 'skipped', 'reason' => 'ServiceProfile tidak memiliki download_speed/upload_speed valid.'];
        }

        try {
            $driver = $this->oltRegistry->forOlt($onu->olt);
            $driver->setOnuAdminStatus($onu, 'enable');

            if (!$driver->setOnuBandwidthLimit($onu, $download, $upload)) {
                throw new \RuntimeException('OLT menolak konfigurasi limitasi bandwidth.');
            }

            return ['status' => 'ok', 'download_mbps' => $download, 'upload_mbps' => $upload];
        } catch (Throwable $e) {
            Log::error('ProvisioningOrchestrator: OLT QoS gagal', [
                'onu_id' => $onu->id,
                'msg' => $e->getMessage(),
            ]);

            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    /**
     * SSOT provisioning ACS/TR-069 (WAN PPPoE + Wi-Fi) via GenieAcsProvisioningService.
     */
    public function provisionAcs(CustomerService $customerService): array
    {
        $onu = $customerService->onu;
        if (!$onu) {
            return ['status' => 'skipped', 'reason' => 'CustomerService tidak memiliki ONU.'];
        }

        try {
            return $this->acsProvisioning->provisionOnt($onu, [
                'customer_service' => $customerService,
            ]);
        } catch (Throwable $e) {
            Log::error('ProvisioningOrchestrator: ACS provisioning gagal', [
                'onu_id' => $onu->id,
                'msg' => $e->getMessage(),
            ]);

            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    /**
     * SSOT aktivasi layanan: set status aktif + PPPoE aktif + publikasikan ServiceActivatedEvent.
     * Provisioning perangkat dijalankan listener (FiberServiceProvisioningListener) dari event tersebut.
     */
    public function activate(CustomerService $customerService, ?int $userId = null): array
    {
        $customerService->update([
            'status' => 'active',
            'service_status' => 'online',
            'activated_at' => $customerService->activated_at ?? now(),
        ]);

        $pppoeUser = PPPoEUser::withoutGlobalScopes()
            ->where('customer_service_id', $customerService->id)
            ->first();
        if ($pppoeUser) {
            $pppoeUser->update(['status' => 'active']);
        }

        Event::dispatch(new ServiceActivatedEvent($customerService->uuid, null, $userId));

        return ['status' => 'activated'];
    }
}
