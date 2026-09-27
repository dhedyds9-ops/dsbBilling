<?php

namespace App\Jobs\Provisioning\Steps;

use App\Services\Adapters\Provisioning\OltRegistry;
use App\Models\ISP\Onu;
use Exception;

class OltOnuRegistrationJob extends BasePipelineStepJob
{
    protected function executeStep(): ?array
    {
        $cs = $this->step->provisionPipeline->serviceInstance->customerService;
        
        $onuId = $cs->onu_id;
        if (!$onuId) {
            return ['status' => 'skipped', 'reason' => 'No ONU attached. Pemasangan bukan tipe FTTH murni.'];
        }

        $onu = Onu::with('olt')->find($onuId);
        if (!$onu || !$onu->olt) {
            throw new Exception("ONU atau OLT tidak ditemukan di database.");
        }

        $registry = app(OltRegistry::class);
        $driver = $registry->forOlt($onu->olt);

        // 1. Eksekusi registrasi fisik (T-CONT, GEM, SN Auth)
        $success = $driver->provisionOnu(
            $onu,
            $onu->serial_number,
            $onu->pon_port,
            'default'
        );

        if (!$success) {
            throw new Exception("Gagal meregistrasi ONU ke OLT. OLT Driver mengembalikan false.");
        }

        // 2. Injeksi Otomatis OMCI WAN (PPPoE / DHCP) jika data tersedia
        if (!empty($cs->username) && !empty($cs->password)) {
            $vlanId = $cs->networkProfile->vlan_id ?? 10; // Ambil VLAN dari profil jaringan, default 10
            
            $omciConfig = [
                'mode'     => 'pppoe',
                'username' => $cs->username,
                'password' => $cs->password,
                'vlan_id'  => $vlanId,
                'ip_index' => 1,
                'priority' => 0
            ];
            
            try {
                $driver->pushOnuWanConfig($onu, $omciConfig);
            } catch (Exception $e) {
                // Log error tapi jangan gagalkan pipeline, karena TR069 mungkin masih bisa mengambil alih
                \Illuminate\Support\Facades\Log::warning("Gagal injeksi OMCI otomatis untuk ONU {$onu->id}: " . $e->getMessage());
            }
        }

        // Simpan konfigurasi ke NVRAM OLT agar persisten (opsional tapi best practice)
        $driver->saveConfig();

        $onu->update(['status' => 'online']); // Asumsi register sukses

        return [
            'registered_at' => now()->toDateTimeString(),
            'olt_id' => $onu->olt_id,
            'pon_port' => $onu->pon_port,
            'serial_number' => $onu->serial_number,
            'driver_status' => 'Provisioning Success'
        ];
    }
}
