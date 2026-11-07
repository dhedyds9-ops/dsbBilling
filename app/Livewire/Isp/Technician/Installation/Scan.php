<?php

namespace App\Livewire\Isp\Technician\Installation;

use App\Models\ACS\ACSDevice;
use App\Models\ISP\Onu;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.technician-app')]
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

        $input = strtoupper(trim($this->serialNumber));
        $this->serialNumber = $input;

        // Siapkan kandidat pencarian (termasuk konversi HEX <-> ASCII vendor GPON ZTE, HWTC, FHTT)
        $candidates = [$input];
        if (str_starts_with($input, 'ZTEG')) {
            $candidates[] = '5A544547' . substr($input, 4);
        } elseif (str_starts_with($input, '5A544547')) {
            $candidates[] = 'ZTEG' . substr($input, 8);
        }
        if (str_starts_with($input, 'HWTC')) {
            $candidates[] = '48575443' . substr($input, 4);
        } elseif (str_starts_with($input, '48575443')) {
            $candidates[] = 'HWTC' . substr($input, 8);
        }
        if (str_starts_with($input, 'FHTT')) {
            $candidates[] = '46485454' . substr($input, 4);
        } elseif (str_starts_with($input, '46485454')) {
            $candidates[] = 'FHTT' . substr($input, 8);
        }

        $candidates = array_unique(array_filter($candidates));

        // 1. Cek database lokal Onu (withoutGlobalScopes agar tidak terhalang branch scope/reseller)
        $onu = Onu::withoutGlobalScopes()
            ->with([
                'odp:id,name,code',
                'olt:id,name,model',
                'ponPort:id,name,port_number,port_index',
                'customerService' => fn($q) => $q->withoutGlobalScopes(),
                'customerService.customer' => fn($q) => $q->withoutGlobalScopes(),
                'customerService.serviceProfile' => fn($q) => $q->withoutGlobalScopes(),
                'customerService.pppoeUser' => fn($q) => $q->withoutGlobalScopes(),
                'acsDevice' => fn($q) => $q->withoutGlobalScopes(),
            ])
            ->where(function ($q) use ($candidates) {
                foreach ($candidates as $cand) {
                    $q->orWhere('serial_number', 'like', "%{$cand}%")
                      ->orWhere('mac_address', 'like', "%{$cand}%")
                      ->orWhere('code', 'like', "%{$cand}%");
                }
            })
            ->first();

        // 2. Cek database GenieACS (ACSDevice)
        $acsDevice = ACSDevice::withoutGlobalScopes()
            ->with([
                'olt:id,name',
                'customerService' => fn($q) => $q->withoutGlobalScopes(),
                'customerService.customer' => fn($q) => $q->withoutGlobalScopes(),
                'customerService.serviceProfile' => fn($q) => $q->withoutGlobalScopes(),
                'customerService.pppoeUser' => fn($q) => $q->withoutGlobalScopes(),
            ])
            ->where(function ($q) use ($candidates, $onu) {
                if ($onu && !empty($onu->serial_number)) {
                    $q->orWhere('serial_number', $onu->serial_number);
                }
                foreach ($candidates as $cand) {
                    $q->orWhere('serial_number', 'like', "%{$cand}%")
                      ->orWhere('mac_address', 'like', "%{$cand}%");
                }
            })
            ->first();

        if ($onu || $acsDevice) {
            // Resolusi CustomerService lintas model tanpa terhalang isolasi branch/reseller
            $cs = null;
            if ($onu) {
                $cs = $onu->customerService;
                if (!$cs) {
                    $cs = CustomerService::withoutGlobalScopes()
                        ->with([
                            'customer' => fn($q) => $q->withoutGlobalScopes(),
                            'serviceProfile' => fn($q) => $q->withoutGlobalScopes(),
                            'pppoeUser' => fn($q) => $q->withoutGlobalScopes(),
                        ])
                        ->where('onu_id', $onu->id)
                        ->latest('id')
                        ->first();
                }
            }
            if (!$cs && $acsDevice) {
                $cs = $acsDevice->customerService;
                if (!$cs && $acsDevice->customer_service_id) {
                    $cs = CustomerService::withoutGlobalScopes()
                        ->with([
                            'customer' => fn($q) => $q->withoutGlobalScopes(),
                            'serviceProfile' => fn($q) => $q->withoutGlobalScopes(),
                            'pppoeUser' => fn($q) => $q->withoutGlobalScopes(),
                        ])
                        ->find($acsDevice->customer_service_id);
                }
            }

            // Fallback Customer jika tidak ada di CustomerService
            $cust = $cs?->customer 
                ?? ($onu?->customer_id ? Customer::withoutGlobalScopes()->find($onu->customer_id) : null)
                ?? ($acsDevice?->resolved_customer ?? null);

            $olt = $onu?->olt ?? ($acsDevice?->olt ?? null);

            // Format PON Port yang user-friendly (resolusi dari nama port, bukan integer mentah)
            $ponPortDisplay = $onu?->ponPort?->name;
            if (!$ponPortDisplay && !empty($onu?->pon_port)) {
                $rawPort = (string) $onu->pon_port;
                if (is_numeric($rawPort) && (int) $rawPort > 1000) {
                    $pRec = \App\Models\ISP\PonPort::where('olt_id', $onu->olt_id)
                        ->where(function($q) use ($onu) {
                            $q->where('port_index', $onu->pon_port)
                              ->orWhere('id', $onu->pon_port_id);
                        })->first();
                    $ponPortDisplay = $pRec?->name ?? 'GPON (Port Aktif)';
                } else {
                    $ponPortDisplay = 'PON ' . $rawPort;
                }
            }
            if (!$ponPortDisplay) {
                $ponPortDisplay = $acsDevice?->pon_port ?? '-';
            }

            $this->scannedOnu = [
                'id' => $onu?->id,
                'is_registered' => true,
                'source' => $onu ? 'onu' : 'acs',
                'serial_number' => $onu?->serial_number ?: ($acsDevice?->serial_number ?: $input),
                'mac_address' => $onu?->mac_address ?: ($acsDevice?->mac_address ?: '-'),
                'model' => $onu?->model ?: ($acsDevice?->model ?: 'ONT'),
                'customer_name' => $cust?->name ?? 'Belum Terikat Pelanggan',
                'customer_code' => $cust?->code ?? ($cust?->customer_code ?? ($cust?->id ? ('ID #' . $cust->id) : '-')),
                'customer_phone' => $cust?->phone ?? '-',
                'package_name' => $cs?->serviceProfile?->name ?? 'Layanan Aktif',
                'pppoe_username' => $cs?->pppoeUser?->username ?? '-',
                'olt_name' => $olt?->name ?? ($acsDevice?->olt_name ?? 'OLT-Pusat'),
                'pon_port' => $ponPortDisplay,
                'rx_power' => $onu?->rx_power_dbm ?? ($acsDevice?->signal ?? null),
                'status' => $onu?->status ?? ($acsDevice?->status ?? 'active'),
            ];

            $this->scanResult = 'found';
        } else {
            // Perangkat belum terdaftar di database manapun
            if (preg_match('/^[A-Z0-9_\-]{6,24}$/', $input)) {
                $this->scanResult = 'unregistered';
                $this->scannedOnu = [
                    'id' => null,
                    'is_registered' => false,
                    'serial_number' => $input,
                    'mac_address' => '-',
                    'model' => 'ONT Baru',
                    'customer_name' => 'Belum Terdaftar',
                    'customer_code' => '-',
                    'customer_phone' => '-',
                    'package_name' => '-',
                    'pppoe_username' => '-',
                    'olt_name' => 'Terdeteksi Sinyal Optik',
                    'pon_port' => 'Port Terkoneksi',
                    'rx_power' => null,
                    'status' => 'unregistered',
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
        return view('livewire.isp.technician.installation.scan');
    }
}
