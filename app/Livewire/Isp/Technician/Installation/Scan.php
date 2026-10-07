<?php

namespace App\Livewire\Isp\Technician\Installation;

use App\Models\ISP\Onu;
use Livewire\Component;

class Scan extends Component
{
    public $serialNumber = '';
    public $scannedOnu = null;
    public $scanResult = null; // 'found', 'unregistered', 'not_found'

    public function scanBarcode()
    {
        $this->validate([
            'serialNumber' => 'required|string|min:4'
        ]);

        $this->serialNumber = strtoupper(trim($this->serialNumber));
        
        // Cek database lokal
        $onu = Onu::with(['odp', 'olt', 'customerService.customer'])->where('serial_number', $this->serialNumber)->orWhere('mac_address', $this->serialNumber)->first();

        if ($onu) {
            $this->scannedOnu = $onu;
            $this->scanResult = 'found';
        } else {
            // Simulasi hasil scan dari OLT (ONU yang belum diregistrasi)
            // Dalam implementasi asli, di sini akan memanggil OltPollingService atau RouterOSService
            // Untuk demo/scaffolding, jika formatnya SN valid kita anggap unregistered
            if (preg_match('/^[A-Z0-9]{8,16}$/', $this->serialNumber)) {
                $this->scanResult = 'unregistered';
                $this->scannedOnu = [
                    'serial_number' => $this->serialNumber,
                    'rx_power' => -rand(150, 250) / 10, // -15.0 to -25.0
                    'olt_detected' => 'OLT-ZTE-CORE',
                    'pon_port' => '0/1/'.rand(1,8),
                ];
            } else {
                $this->scanResult = 'not_found';
                $this->scannedOnu = null;
            }
        }
    }

    public function resetScan()
    {
        $this->serialNumber = '';
        $this->scannedOnu = null;
        $this->scanResult = null;
    }

    public function render()
    {
        return view('livewire.isp.technician.installation.scan')->layout('layouts.noc', ['slot' => '']);
    }
}
