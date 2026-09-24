<?php

namespace App\Livewire\CustomerPortal\SelfService;

use App\Models\Customer\CustomerService;
use App\Models\ISP\Onu;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.customer-app')]
class ConnectedDevices extends Component
{
    public $devices = [];
    public $isLoading = true;
    public $error = null;
    public $onuId = null;
    public $genieacsDeviceId = null;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->isLoading = true;
        $this->error = null;
        $this->devices = [];

        try {
            $customer = Auth::user()->customer;
            if (!$customer) {
                throw new \Exception("Data pelanggan tidak ditemukan.");
            }

            // Cari CustomerService yang memiliki ONU ID atau GenieACS Device ID
            $customerService = CustomerService::where('customer_id', $customer->id)
                ->where(function($q) {
                    $q->whereNotNull('onu_id')
                      ->orWhereNotNull('attributes->genieacs_device_id');
                })
                ->first();

            if (!$customerService) {
                throw new \Exception("Tidak ada perangkat (ONU) yang terhubung dengan akun Anda. Silakan hubungi admin.");
            }

            $deviceId = null;

            if ($customerService->onu_id) {
                $onu = Onu::find($customerService->onu_id);
                if ($onu) {
                    $deviceId = $onu->genieacsDeviceId;
                }
            }

            if (!$deviceId && isset($customerService->attributes['genieacs_device_id'])) {
                $deviceId = $customerService->attributes['genieacs_device_id'];
            }

            if (!$deviceId) {
                throw new \Exception("Informasi perangkat (Serial Number/MAC) tidak lengkap.");
            }

            $this->genieacsDeviceId = $deviceId;

            $driver = app(GenieACSDriver::class);
            
            // Periksa apakah ONU sedang online
            $deviceStatus = $driver->getDeviceSignal($deviceId);
            $isOnline = $deviceStatus['online'] ?? false;
            
            if ($isOnline) {
                $rawDevices = $driver->getConnectedDevices($deviceId);
                // Hanya tampilkan perangkat yang benar-benar aktif (online)
                $this->devices = array_filter($rawDevices, function($dev) {
                    return !empty($dev['Active']);
                });
            } else {
                $this->devices = [];
            }
            
            // Periksa daftar blokir
            try {
                $params = $driver->getDeviceParameters($deviceId);
                $listPath = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.MACAddressControlList";
                
                $extractPath = function($path) use ($params) {
                    $parts = explode('.', $path);
                    $node = $params;
                    foreach ($parts as $p) {
                        if (!is_array($node) || !array_key_exists($p, $node)) return null;
                        $node = $node[$p];
                    }
                    return $node;
                };

                $blockedList = $extractPath($listPath);
                $blockedValue = is_array($blockedList) && isset($blockedList['_value']) ? $blockedList['_value'] : ($blockedList ?? '');
                $blockedMacs = array_filter(array_map('trim', explode(',', $blockedValue)));

                // Tandai mana yang terblokir
                foreach ($this->devices as &$dev) {
                    $dev['IsBlocked'] = in_array($dev['MACAddress'] ?? '', $blockedMacs);
                }
                
                // Tambahkan perangkat yang diblokir yang mungkin sedang offline
                foreach ($blockedMacs as $bMac) {
                    $found = false;
                    foreach ($this->devices as $dev) {
                        if (($dev['MACAddress'] ?? '') === $bMac) {
                            $found = true; break;
                        }
                    }
                    if (!$found) {
                        $this->devices[] = [
                            'MACAddress' => $bMac,
                            'IPAddress' => '-',
                            'HostName' => 'Perangkat Diblokir (Offline)',
                            'Active' => false,
                            'IsBlocked' => true
                        ];
                    }
                }
                
            } catch (\Exception $e) {
                // Ignore jika tidak bisa ambil daftar blokir
                Log::warning("Gagal ambil daftar blokir: " . $e->getMessage());
            }

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Log::error("ConnectedDevices error: " . $e->getMessage());
        }

        $this->isLoading = false;
    }

    public function blockMac(string $macAddress)
    {
        if (!$this->genieacsDeviceId) return;

        try {
            $driver = app(GenieACSDriver::class);
            $driver->blockMacAddress($this->genieacsDeviceId, $macAddress);
            
            $this->dispatch('alert', type: 'success', message: "Perangkat $macAddress berhasil diblokir.");
            $this->loadData();
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: "Gagal memblokir: " . $e->getMessage());
        }
    }

    public function unblockMac(string $macAddress)
    {
        if (!$this->genieacsDeviceId) return;

        try {
            $driver = app(GenieACSDriver::class);
            $driver->unblockMacAddress($this->genieacsDeviceId, $macAddress);
            
            $this->dispatch('alert', type: 'success', message: "Blokir perangkat $macAddress berhasil dibuka.");
            $this->loadData();
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: "Gagal membuka blokir: " . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.customer-portal.self-service.connected-devices');
    }
}
