<?php

namespace App\Livewire\CustomerPortal\SelfService;

use App\Models\ACS\ACSDevice;
use App\Models\Customer\CustomerService;
use App\Models\ISP\HotspotActiveSession;
use App\Models\ISP\Onu;
use App\Models\ISP\PppActiveSession;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use App\Services\ISP\Session\SessionKickService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer-app')]
class ActiveSessions extends Component
{
    public string $search = '';
    public string $message = '';
    public string $messageType = 'success';

    protected $queryString = ['search' => ['except' => '']];

    public function kickSession(string $type, int $sessionId, SessionKickService $kickService): void
    {
        $user = Auth::user();
        $customer = $user->customer;
        
        if (!$customer) {
            $this->showMessage('Akses ditolak: Data pelanggan tidak ditemukan.', 'error');
            return;
        }

        $validUsernames = CustomerService::where('customer_id', $customer->id)
            ->pluck('username')
            ->toArray();

        try {
            if ($type === 'pppoe') {
                $session = PppActiveSession::find($sessionId);
                if (!$session || !in_array($session->name, $validUsernames)) {
                    $this->showMessage('Sesi PPPoE tidak ditemukan atau Anda tidak memiliki akses.', 'error');
                    return;
                }
                $result = $kickService->kickPppoe($session, $user);
            } else {
                $session = HotspotActiveSession::find($sessionId);
                if (!$session || !in_array($session->user, $validUsernames)) {
                    $this->showMessage('Sesi Hotspot tidak ditemukan atau Anda tidak memiliki akses.', 'error');
                    return;
                }
                $result = $kickService->kickHotspot($session, $user);
            }

            if (isset($result['success']) && $result['success']) {
                $this->showMessage('Perangkat/sesi berhasil diputus dari jaringan. Modem akan otomatis tersambung kembali.', 'success');
            } else {
                $errorMsg = $result['error'] ?? 'Terjadi kesalahan sistem saat memutus koneksi.';
                $this->showMessage('Gagal memutus koneksi: ' . $errorMsg, 'error');
            }

        } catch (\Throwable $e) {
            Log::error('Customer kick session error: ' . $e->getMessage());
            $this->showMessage('Gagal memutus koneksi karena gangguan teknis.', 'error');
        }
    }

    public function refreshConnectedDevices(): void
    {
        $user = Auth::user();
        $customer = $user->customer;
        if ($customer) {
            $customerServices = CustomerService::where('customer_id', $customer->id)->get();
            foreach ($customerServices as $cs) {
                $targetId = $this->resolveGenieAcsDeviceId($cs);
                if ($targetId) {
                    Cache::forget("customer_acs_hosts_{$targetId}");
                    Cache::forget("customer_acs_signal_{$targetId}");
                }
            }
        }
        $this->showMessage('Daftar perangkat terhubung berhasil diperbarui dari GenieACS.', 'success');
    }

    public function blockDevice(string $macAddress, string $deviceId): void
    {
        try {
            $driver = app(GenieACSDriver::class);
            $driver->blockMacAddress($deviceId, $macAddress);
            Cache::forget("customer_acs_hosts_{$deviceId}");
            $this->showMessage("Perangkat {$macAddress} berhasil diblokir dari WiFi.", 'success');
        } catch (\Throwable $e) {
            $this->showMessage("Gagal memblokir perangkat: " . $e->getMessage(), 'error');
        }
    }

    public function unblockDevice(string $macAddress, string $deviceId): void
    {
        try {
            $driver = app(GenieACSDriver::class);
            $driver->unblockMacAddress($deviceId, $macAddress);
            Cache::forget("customer_acs_hosts_{$deviceId}");
            $this->showMessage("Blokir perangkat {$macAddress} berhasil dibuka.", 'success');
        } catch (\Throwable $e) {
            $this->showMessage("Gagal membuka blokir perangkat: " . $e->getMessage(), 'error');
        }
    }

    private function resolveGenieAcsDeviceId(CustomerService $service): ?string
    {
        if ($service->relationLoaded('acsDevice') && $service->acsDevice) {
            return $service->acsDevice->uuid ?: $service->acsDevice->serial_number;
        }

        $acs = ACSDevice::where('customer_service_id', $service->id)->first();
        if ($acs) {
            return $acs->uuid ?: $acs->serial_number;
        }

        if ($service->onu_id) {
            $onu = $service->relationLoaded('onu') && $service->onu
                ? $service->onu
                : Onu::find($service->onu_id);
            if ($onu) {
                $acsByOnu = ACSDevice::where('onu_id', $onu->id)
                    ->orWhere('serial_number', $onu->serial_number)
                    ->first();
                if ($acsByOnu) {
                    return $acsByOnu->uuid ?: $acsByOnu->serial_number;
                }
                return $onu->serial_number ?: ($onu->mac_address ?: null);
            }
        }

        if (!empty($service->username)) {
            $onu = Onu::where('serial_number', $service->username)
                ->orWhere('name', $service->username)
                ->first();
            if ($onu) {
                return $onu->serial_number ?: ($onu->mac_address ?: null);
            }
        }

        return $service->attributes['genieacs_device_id'] ?? null;
    }

    private function fetchConnectedDevicesFromGenieAcs(string $targetId): array
    {
        return Cache::remember("customer_acs_hosts_{$targetId}", 25, function () use ($targetId) {
            try {
                $driver = app(GenieACSDriver::class);
                $raw = $driver->getConnectedDevices($targetId);
                
                // Cek daftar MAC yang diblokir jika ada
                $blockedMacs = [];
                try {
                    $params = $driver->getDeviceParameters($targetId);
                    $listPath = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.MACAddressControlList";
                    $parts = explode('.', $listPath);
                    $node = $params;
                    foreach ($parts as $p) {
                        if (!is_array($node) || !array_key_exists($p, $node)) { $node = null; break; }
                        $node = $node[$p];
                    }
                    $blockedVal = is_array($node) && isset($node['_value']) ? $node['_value'] : ($node ?? '');
                    $blockedMacs = array_filter(array_map('trim', explode(',', (string) $blockedVal)));
                } catch (\Throwable) {}

                $devices = [];
                foreach ($raw as $dev) {
                    $mac = $dev['MACAddress'] ?? 'Unknown';
                    $devices[] = [
                        'hostname' => $dev['HostName'] ?? 'Unknown Device',
                        'ip' => $dev['IPAddress'] ?? '-',
                        'mac' => $mac,
                        'interface' => $dev['Layer1Interface'] ?? ($dev['InterfaceType'] ?? 'WiFi'),
                        'active' => !empty($dev['Active']),
                        'is_blocked' => in_array($mac, $blockedMacs),
                    ];
                }

                return $devices;
            } catch (\Throwable $e) {
                Log::warning("GenieACS fetchConnectedDevices for {$targetId}: " . $e->getMessage());
                return [];
            }
        });
    }

    private function fetchSignalFromGenieAcs(string $targetId): array
    {
        return Cache::remember("customer_acs_signal_{$targetId}", 60, function () use ($targetId) {
            try {
                $driver = app(GenieACSDriver::class);
                return $driver->getDeviceSignal($targetId);
            } catch (\Throwable) {
                return [];
            }
        });
    }

    private function showMessage(string $message, string $type): void
    {
        $this->message = $message;
        $this->messageType = $type;
        $this->dispatch('alert', type: $type, message: $message);
    }

    public function render()
    {
        $customer = Auth::user()?->customer;
        $pppoeItems = collect();
        $hotspotSessions = collect();
        $usernames = [];

        if ($customer) {
            // Ambil semua CustomerService pelanggan
            $customerServices = CustomerService::where('customer_id', $customer->id)
                ->with(['onu.vendor', 'onu.olt', 'onu.odp', 'acsDevice', 'pppoeUser', 'hotspotUser'])
                ->get();

            $usernames = $customerServices->pluck('username')->filter()->toArray();

            // 1. Kumpulkan PPPoE Services (ambil data dari ONU dan GenieACS)
            foreach ($customerServices as $cs) {
                $isPppoe = $cs->pppoeUser !== null 
                    || $cs->onu_id !== null 
                    || ($cs->serviceProfile && $cs->serviceProfile->type === 'pppoe')
                    || PppActiveSession::where('name', $cs->username)->exists();

                if ($isPppoe) {
                    // Cari ONU
                    $onu = $cs->onu;
                    if (!$onu && $cs->onu_id) {
                        $onu = Onu::with(['vendor', 'olt', 'odp'])->find($cs->onu_id);
                    }
                    if (!$onu && !empty($cs->username)) {
                        $onu = Onu::with(['vendor', 'olt', 'odp'])
                            ->where('serial_number', $cs->username)
                            ->orWhere('name', $cs->username)
                            ->first();
                    }

                    // Cari ACS Device / Target ID GenieACS
                    $acsDevice = $cs->acsDevice;
                    if (!$acsDevice && $onu) {
                        $acsDevice = ACSDevice::where('onu_id', $onu->id)
                            ->orWhere('serial_number', $onu->serial_number)
                            ->first();
                    }
                    if (!$acsDevice) {
                        $acsDevice = ACSDevice::where('customer_service_id', $cs->id)->first();
                    }

                    $targetAcsId = $this->resolveGenieAcsDeviceId($cs);

                    // Ambil Sesi PPP dari MikroTik jika ada
                    $pppSession = PppActiveSession::with('router')->where('name', $cs->username)->first();

                    // Ambil Sinyal & Perangkat Terhubung dari GenieACS
                    $connectedDevices = [];
                    $liveSignal = [];
                    if ($targetAcsId) {
                        $connectedDevices = $this->fetchConnectedDevicesFromGenieAcs($targetAcsId);
                        $liveSignal = $this->fetchSignalFromGenieAcs($targetAcsId);
                    }

                    // Tentukan rx_power
                    $rxPower = null;
                    if (isset($liveSignal['rx_power_dbm']) && $liveSignal['rx_power_dbm'] !== null) {
                        $rxPower = round((float) $liveSignal['rx_power_dbm'], 2) . ' dBm';
                    } elseif ($onu && $onu->rx_power_dbm !== null) {
                        $rxPower = round((float) $onu->rx_power_dbm, 2) . ' dBm';
                    }

                    // Tentukan tx_power
                    $txPower = null;
                    if (isset($liveSignal['tx_power_dbm']) && $liveSignal['tx_power_dbm'] !== null) {
                        $txPower = round((float) $liveSignal['tx_power_dbm'], 2) . ' dBm';
                    } elseif ($onu && $onu->tx_power_dbm !== null) {
                        $txPower = round((float) $onu->tx_power_dbm, 2) . ' dBm';
                    }

                    // Tentukan WiFi SSID
                    $wifiSsid = $onu?->wifi_ssid ?: ($liveSignal['ssid'] ?? null);

                    // Filter pencarian jika diisi
                    $searchLower = strtolower(trim($this->search));
                    $filteredDevices = $connectedDevices;
                    if (!empty($searchLower)) {
                        $filteredDevices = array_filter($connectedDevices, function ($dev) use ($searchLower) {
                            return str_contains(strtolower($dev['hostname']), $searchLower)
                                || str_contains(strtolower($dev['ip']), $searchLower)
                                || str_contains(strtolower($dev['mac']), $searchLower);
                        });
                    }

                    $isOnline = ($pppSession !== null) 
                        || ($liveSignal['online'] ?? false) 
                        || ($onu?->status === 'active' || $onu?->status === 'online')
                        || ($acsDevice?->status === 'online');

                    $pppoeItems->push((object)[
                        'type' => 'pppoe',
                        'customer_service_id' => $cs->id,
                        'username' => $cs->username ?: ($onu?->name ?? 'PPPoE Service'),
                        'status' => $isOnline ? 'online' : 'offline',
                        'is_online' => $isOnline,
                        'serial_number' => $onu?->serial_number ?: ($acsDevice?->serial_number ?: ($targetAcsId ?: '-')),
                        'model' => ($onu?->vendor?->name ? $onu->vendor->name . ' ' : '') . ($onu?->model ?: ($acsDevice?->model ?: 'Modem ONU')),
                        'mac_address' => $onu?->mac_address ?: ($pppSession?->caller_id ?: ($acsDevice?->mac_address ?: '-')),
                        'ip_address' => $pppSession?->address ?: ($acsDevice?->ip_address ?: '-'),
                        'rx_power' => $rxPower,
                        'tx_power' => $txPower,
                        'wifi_ssid' => $wifiSsid,
                        'uptime' => $pppSession?->uptime ?: (isset($liveSignal['last_inform']) && $liveSignal['last_inform'] ? Carbon::parse($liveSignal['last_inform'])->diffForHumans() : '-'),
                        'router' => $pppSession?->router ? $pppSession->router->name : 'N/A',
                        'ppp_session_id' => $pppSession?->id,
                        'genieacs_device_id' => $targetAcsId,
                        'connected_devices' => $filteredDevices,
                        'total_connected' => count($connectedDevices),
                    ]);
                }
            }

            // 2. Hotspot Sessions (jika pelanggan memiliki sesi hotspot aktif)
            if (!empty($usernames)) {
                $queryStr = '%' . $this->search . '%';
                $hotspotSessions = HotspotActiveSession::with('router')
                    ->whereIn('user', $usernames)
                    ->when($this->search, function ($query) use ($queryStr) {
                        $query->where(function ($q) use ($queryStr) {
                            $q->where('user', 'like', $queryStr)
                              ->orWhere('address', 'like', $queryStr)
                              ->orWhere('mac_address', 'like', $queryStr);
                        });
                    })
                    ->get()
                    ->map(function ($item) {
                        return (object)[
                            'id' => $item->id,
                            'type' => 'hotspot',
                            'username' => $item->user,
                            'ip_address' => $item->address,
                            'mac_address' => $item->mac_address,
                            'uptime' => $item->uptime,
                            'router' => $item->router ? $item->router->name : 'N/A',
                        ];
                    });
            }
        }

        return view('livewire.customer-portal.self-service.active-sessions', [
            'pppoeItems' => $pppoeItems,
            'hotspotSessions' => $hotspotSessions,
        ]);
    }
}
