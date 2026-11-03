<?php

namespace App\Livewire\ACS\Device;

use App\Livewire\ACS\BaseACSComponent;
use App\Models\ACS\ACSDevice;

class Index extends BaseACSComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'acs';
        $this->activePage = 'devices';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ACS', 'url' => route('acs.dashboard')],
            ['label' => 'Devices'],
        ];
    }

    public function delete($id)
    {
        $device = ACSDevice::findOrFail($id);
        $device->delete();
        session()->flash('success', 'Device berhasil dihapus!');
    }

    public function deleteAllOfflineDevices()
    {
        try {
            $count = ACSDevice::where('status', 'offline')->count();
            if ($count > 0) {
                ACSDevice::where('status', 'offline')->delete();
                session()->flash('success', "Berhasil menghapus {$count} device offline!");
            } else {
                session()->flash('info', 'Tidak ada device offline yang ditemukan.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus device: ' . $e->getMessage());
        }
    }

    public function export()
    {
        session()->flash('info', 'Export feature will be implemented later!');
    }

    public function syncDevices()
    {
        $this->syncDevicesBackground();
        
    }

    public function syncDevicesBackground()
    {
        try {
            $acsService = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $query = [
                'projection' => '_id,_deviceId,_ip,_lastInform,VirtualParameters,InternetGatewayDevice.DeviceInfo,Device.DeviceInfo,InternetGatewayDevice.WANDevice,Device.WANDevice,Device.Optical,InternetGatewayDevice.ManagementServer.ConnectionRequestURL,Device.ManagementServer.ConnectionRequestURL'
            ];
            $devices = $acsService->listDevices($query, 5000);
            $count = 0;
            $syncedIds = [];
            
            \Illuminate\Support\Facades\DB::transaction(function () use ($devices, &$count, &$syncedIds) {
                foreach ($devices as $deviceData) {
                    $savedId = $this->saveDeviceData($deviceData);
                    if ($savedId) {
                        $syncedIds[] = $savedId;
                        $count++;
                    }
                }
            
                // Tandai perangkat yang tidak ada di ACS sebagai offline
                if (count($syncedIds) > 0) {
                    \App\Models\ACS\ACSDevice::whereNotIn('uuid', $syncedIds)->update(['status' => 'offline']);
                }
            });

            session()->flash('success', "Berhasil mensinkronisasi {$count} perangkat dari GenieACS!");
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal sinkronisasi: ' . $e->getMessage());
        }
    }

    /**
     * Memproses dan menyimpan/mengupdate data satu perangkat dari GenieACS ke database dsBilling
     */
    public function saveDeviceData(array $deviceData): ?string
    {
        $deviceId = $deviceData['_id'] ?? null;
        if (!$deviceId) return null;

        $extract = function($path) use ($deviceData) {
            $parts = explode('.', $path);
            $node = $deviceData;
            foreach ($parts as $p) {
                if (!is_array($node) || !array_key_exists($p, $node)) return null;
                $node = $node[$p];
            }
            return isset($node['_value']) ? $node['_value'] : null;
        };

        // Helper validasi IP Address yang bukan 0.0.0.0 atau loopback
        $isValidIp = function ($val) {
            if (empty($val) || !is_string($val)) return false;
            $val = trim($val);
            if ($val === '' || $val === '0.0.0.0' || $val === '::' || $val === '127.0.0.1') return false;
            return filter_var($val, FILTER_VALIDATE_IP) !== false;
        };

        $wanDevices = $deviceData['InternetGatewayDevice']['WANDevice'] ?? $deviceData['Device']['WANDevice'] ?? [];
        $mac = null;

        // 1. Ekstraksi IP PPPoE (Prioritas: VirtualParameters.pppoeIP -> WANPPPConnection)
        $pppoeIp = null;
        $vpPppoe = $extract('VirtualParameters.pppoeIP') 
                ?? $extract('VirtualParameters.pppoeIp')
                ?? $extract('VirtualParameters.PPPOEIP');
        if ($isValidIp($vpPppoe)) {
            $pppoeIp = trim($vpPppoe);
        }

        if (!$pppoeIp && is_array($wanDevices)) {
            foreach ($wanDevices as $wdIdx => $wdNode) {
                if ($wdIdx === '_object' || !is_array($wdNode)) continue;
                $connDevices = $wdNode['WANConnectionDevice'] ?? [];
                if (is_array($connDevices)) {
                    foreach ($connDevices as $cdIdx => $connNode) {
                        if ($cdIdx === '_object' || !is_array($connNode)) continue;
                        $pppConns = $connNode['WANPPPConnection'] ?? [];
                        if (is_array($pppConns)) {
                            foreach ($pppConns as $pIdx => $ppp) {
                                if ($pIdx === '_object' || !is_array($ppp)) continue;
                                $candidate = $ppp['ExternalIPAddress']['_value'] ?? null;
                                if ($isValidIp($candidate)) {
                                    $pppoeIp = trim($candidate);
                                    break 3;
                                }
                            }
                        }
                    }
                }
            }
        }

        // 2. Ekstraksi IP TR-069 (Prioritas: VirtualParameters.IPTR069 -> ConnectionRequestURL -> WANIPConnection -> _ip)
        $tr069Ip = null;
        $vpTr069 = $extract('VirtualParameters.IPTR069')
                ?? $extract('VirtualParameters.iptr069')
                ?? $extract('VirtualParameters.ipTR069')
                ?? $extract('VirtualParameters.Iptr069')
                ?? $extract('VirtualParameters.TR069IP');
        if ($isValidIp($vpTr069)) {
            $tr069Ip = trim($vpTr069);
        }

        $connReqUrl = $deviceData['InternetGatewayDevice']['ManagementServer']['ConnectionRequestURL']['_value'] ?? 
                      $deviceData['Device']['ManagementServer']['ConnectionRequestURL']['_value'] ?? null;
        if (!empty($connReqUrl)) {
            $parsedHost = parse_url($connReqUrl, PHP_URL_HOST);
            if ($isValidIp($parsedHost)) {
                $tr069Ip = $tr069Ip ?: $parsedHost;
            }
        }

        if (!$tr069Ip && is_array($wanDevices)) {
            foreach ($wanDevices as $wdIdx => $wdNode) {
                if ($wdIdx === '_object' || !is_array($wdNode)) continue;
                $connDevices = $wdNode['WANConnectionDevice'] ?? [];
                if (is_array($connDevices)) {
                    foreach ($connDevices as $cdIdx => $connNode) {
                        if ($cdIdx === '_object' || !is_array($connNode)) continue;
                        $ipConns = $connNode['WANIPConnection'] ?? [];
                        if (is_array($ipConns)) {
                            foreach ($ipConns as $iIdx => $ipc) {
                                if ($iIdx === '_object' || !is_array($ipc)) continue;
                                $candidate = $ipc['ExternalIPAddress']['_value'] ?? null;
                                if ($isValidIp($candidate)) {
                                    $tr069Ip = trim($candidate);
                                    break 3;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!$tr069Ip && !empty($deviceData['_ip']) && $isValidIp($deviceData['_ip'])) {
            $tr069Ip = trim($deviceData['_ip']);
        }

        // Pastikan connection_request_url menyimpan TR-069 IP yang benar untuk pencarian dan pemanggilan
        if ($tr069Ip) {
            if (empty($connReqUrl) || str_contains($connReqUrl, '0.0.0.0')) {
                $connReqUrl = "http://{$tr069Ip}:7547/";
            }
        }

        // IP utama: prioritaskan IP PPPoE, jika belum ada/offline gunakan IP TR-069
        $ip = $pppoeIp ?: ($tr069Ip ?: null);

        // Ekstraksi MAC Address
        if (is_array($wanDevices)) {
            foreach ($wanDevices as $wdIdx => $wdNode) {
                if ($wdIdx === '_object' || !is_array($wdNode)) continue;
                $connDevices = $wdNode['WANConnectionDevice'] ?? [];
                if (is_array($connDevices)) {
                    foreach ($connDevices as $cdIdx => $connNode) {
                        if ($cdIdx === '_object' || !is_array($connNode)) continue;
                        $pppConns = $connNode['WANPPPConnection'] ?? [];
                        if (is_array($pppConns)) {
                            foreach ($pppConns as $pIdx => $ppp) {
                                if ($pIdx === '_object' || !is_array($ppp)) continue;
                                if (!empty($ppp['MACAddress']['_value'])) $mac = $mac ?: $ppp['MACAddress']['_value'];
                            }
                        }
                        $ipConns = $connNode['WANIPConnection'] ?? [];
                        if (is_array($ipConns)) {
                            foreach ($ipConns as $iIdx => $ipc) {
                                if ($iIdx === '_object' || !is_array($ipc)) continue;
                                if (!empty($ipc['MACAddress']['_value'])) $mac = $mac ?: $ipc['MACAddress']['_value'];
                            }
                        }
                    }
                }
            }
        }
        $mac = $mac ?? $deviceData['VirtualParameters']['pppoeMac']['_value'] ?? $deviceData['VirtualParameters']['PonMac']['_value'] ?? null;
        
        $model = $deviceData['_deviceId']['_ProductClass'] ?? 
                 $deviceData['InternetGatewayDevice']['DeviceInfo']['ProductClass']['_value'] ?? 
                 $deviceData['Device']['DeviceInfo']['ProductClass']['_value'] ?? 
                 $deviceData['InternetGatewayDevice']['DeviceInfo']['ModelName']['_value'] ?? 
                 $deviceData['Device']['DeviceInfo']['ModelName']['_value'] ?? null;
        
        // If model is empty string, try fallback
        if (empty(trim((string)$model))) {
            $model = $deviceData['InternetGatewayDevice']['DeviceInfo']['ModelName']['_value'] ?? null;
        }
        
        $lastInform = isset($deviceData['_lastInform']) ? \Carbon\Carbon::parse($deviceData['_lastInform']) : null;
        $status = ($lastInform && abs(now()->diffInMinutes($lastInform)) < 15) ? 'online' : 'offline';

        // Helper pembersih karakter rusak/non-printable (sering terjadi pada VSOL)
        $cleanSn = function ($val) {
            if (!$val) return null;
            $clean = preg_replace('/[^\x20-\x7E]/', '', (string)$val);
            $clean = preg_replace('/[^A-Za-z0-9_\-:]/', '', $clean);
            return trim($clean);
        };

        // Deteksi Serial Number Khusus:
        // 1. VirtualParameters (getSerialNumber / gponSN) dari GenieACS untuk VSOL & Huawei
        // 2. Vendor Extension (X_HW_SerialNumber)
        // 3. Fallback standar TR-069
        $detectedSn = $cleanSn($extract('VirtualParameters.getSerialNumber'))
                   ?? $cleanSn($extract('VirtualParameters.serialNumber'))
                   ?? $cleanSn($extract('VirtualParameters.SerialNumber'))
                   ?? $cleanSn($extract('VirtualParameters.gponSN'))
                   ?? $cleanSn($extract('VirtualParameters.GPONSN'))
                   ?? $cleanSn($extract('InternetGatewayDevice.DeviceInfo.X_HW_SerialNumber'))
                   ?? $cleanSn($extract('Device.DeviceInfo.X_HW_SerialNumber'))
                   ?? $cleanSn($extract('InternetGatewayDevice.DeviceInfo.X_HW_GponSN'))
                   ?? $cleanSn($extract('InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.SerialNumber'));

        $serialNumber = !empty($detectedSn) ? $detectedSn : (
            $cleanSn($deviceData['_deviceId']['_SerialNumber'] ?? null) ?? 
            $cleanSn($extract('InternetGatewayDevice.DeviceInfo.SerialNumber')) ?? 
            $cleanSn($extract('Device.DeviceInfo.SerialNumber')) ?? 
            $deviceId
        );

        $firmware = $extract('InternetGatewayDevice.DeviceInfo.SoftwareVersion') ?? $extract('Device.DeviceInfo.SoftwareVersion') ?? $extract('InternetGatewayDevice.DeviceInfo.HardwareVersion');
        $hardware = $extract('InternetGatewayDevice.DeviceInfo.HardwareVersion') ?? $extract('Device.DeviceInfo.HardwareVersion');
        
        $pppoeUsername = $extract('VirtualParameters.pppoeUsername') ?? 
                         $extract('InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username') ?? 
                         $extract('Device.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username');

        // Mencari RX Power lebih cerdas (Iterasi WANDevice)
        $rxPower = $extract('InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.RXPower')
                ?? $extract('InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.RXPower')
                ?? $extract('InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.RXPower')
                ?? $extract('VirtualParameters.RXPower') 
                ?? $extract('Device.Optical.1.Transceiver.RxPower');
        if ($rxPower === null && is_array($wanDevices)) {
            // Cari konfigurasi PON yang ada didalam WANDevice
            foreach ($wanDevices as $wdIdx => $wdNode) {
                if ($wdIdx === '_object' || !is_array($wdNode)) continue;
                $ponConfig = $wdNode['WANPONInterfaceConfig'] ?? $wdNode['WANEponInterfaceConfig'] ?? [];
                if (is_array($ponConfig)) {
                    foreach ($ponConfig as $pcIndex => $pcNode) {
                        if (!is_numeric($pcIndex) || !is_array($pcNode)) continue;
                        $rxPower = $pcNode['X_ZTE-COM_RxPower']['_value'] ?? $pcNode['X_HW_RxPower']['_value'] ?? $pcNode['RxPower']['_value'] ?? null;
                        if ($rxPower !== null) break 2;
                    }
                }
            }
        }
        
        if ($rxPower !== null && is_numeric($rxPower)) {
            $rxPower = (float)$rxPower;
            if ($rxPower < -500 || $rxPower > 500) {
                $rxPower /= 1000;
            } elseif ($rxPower < -50 || $rxPower > 50) {
                $rxPower /= 100;
            }
        }

        $acsDeviceParams = [
            'signal' => $rxPower,
            'serial_number' => $serialNumber,
            'mac_address' => $mac,
            'model' => $model,
            'ip_address' => $ip,
            'connection_request_url' => $connReqUrl,
            'status' => $status,
            'last_inform' => $lastInform,
            'firmware_version' => $firmware,
            'software_version' => $firmware,
            'hardware_version' => $hardware,
            'manufacturer' => $deviceData['_deviceId']['_Manufacturer'] ?? null,
            'oui' => $deviceData['_deviceId']['_OUI'] ?? null,
        ];

        // Helper pencarian ONU dengan toleransi format SN (ASCII vs HEX)
        $findOnu = function ($sn) {
            if (empty($sn)) return null;
            $snClean = trim((string)$sn);
            $onu = \App\Models\ISP\Onu::withoutGlobalScopes()->where('serial_number', $snClean)->first();
            if ($onu) return $onu;

            // Huawei HEX (48575443) <-> ASCII (HWTC)
            if (str_starts_with(strtoupper($snClean), '48575443')) {
                $alt = 'HWTC' . substr($snClean, 8);
                $onu = \App\Models\ISP\Onu::withoutGlobalScopes()->where('serial_number', $alt)->first();
                if ($onu) return $onu;
            } elseif (str_starts_with(strtoupper($snClean), 'HWTC')) {
                $alt = '48575443' . substr($snClean, 4);
                $onu = \App\Models\ISP\Onu::withoutGlobalScopes()->where('serial_number', $alt)->first();
                if ($onu) return $onu;
            }

            // VSOL HEX (56534F4C) <-> ASCII (VSOL)
            if (str_starts_with(strtoupper($snClean), '56534F4C')) {
                $alt = 'VSOL' . substr($snClean, 8);
                $onu = \App\Models\ISP\Onu::withoutGlobalScopes()->where('serial_number', $alt)->first();
                if ($onu) return $onu;
            } elseif (str_starts_with(strtoupper($snClean), 'VSOL')) {
                $alt = '56534F4C' . substr($snClean, 4);
                $onu = \App\Models\ISP\Onu::withoutGlobalScopes()->where('serial_number', $alt)->first();
                if ($onu) return $onu;
            }

            // VSOL awalan 12345 fallback (misal 12345B4641533EDA0)
            if (str_starts_with($snClean, '12345') && strlen($snClean) >= 12) {
                $last8 = substr($snClean, -8);
                $alt = 'VSOL' . $last8;
                $onu = \App\Models\ISP\Onu::withoutGlobalScopes()->where('serial_number', $alt)->first();
                if ($onu) return $onu;
            }

            // ZTE HEX (5A544547) <-> ASCII (ZTEG)
            if (str_starts_with(strtoupper($snClean), '5A544547')) {
                $alt = 'ZTEG' . substr($snClean, 8);
                $onu = \App\Models\ISP\Onu::withoutGlobalScopes()->where('serial_number', $alt)->first();
                if ($onu) return $onu;
            } elseif (str_starts_with(strtoupper($snClean), 'ZTEG')) {
                $alt = '5A544547' . substr($snClean, 4);
                $onu = \App\Models\ISP\Onu::withoutGlobalScopes()->where('serial_number', $alt)->first();
                if ($onu) return $onu;
            }

            return null;
        };

        // AUTO BINDING LOGIC
        if ($pppoeUsername) {
            $customerService = \App\Models\Customer\CustomerService::where('username', $pppoeUsername)->first();
            if ($customerService) {
                $acsDeviceParams['customer_service_id'] = $customerService->id;
                
                // Cari Onu
                $onu = $findOnu($serialNumber);
                if ($onu) {
                    $acsDeviceParams['onu_id'] = $onu->id; $acsDeviceParams['olt_id'] = $onu->olt_id; $acsDeviceParams['odp_id'] = $onu->odp_id;
                    
                    // Bind Onu ke CustomerService jika berbeda (Modem Baru)
                    if ($customerService->onu_id !== $onu->id) {
                        // Putuskan Onu lama
                        if ($customerService->onu_id) {
                            \App\Models\ISP\Onu::where('id', $customerService->onu_id)->update(['status' => 'inactive']); // Opsional menandai ONU lama inactive
                        }
                        $customerService->update(['onu_id' => $onu->id]);
                    }
                }
            }
        }

        // Fallback Binding jika pppoeUsername tidak terbaca TR-069
        if (empty($acsDeviceParams['customer_service_id'])) {
            $onu = $findOnu($serialNumber);
            if ($onu) {
                $acsDeviceParams['onu_id'] = $onu->id; $acsDeviceParams['olt_id'] = $onu->olt_id; $acsDeviceParams['odp_id'] = $onu->odp_id;
                $cs = \App\Models\Customer\CustomerService::where('onu_id', $onu->id)->first();
                if ($cs) {
                    $acsDeviceParams['customer_service_id'] = $cs->id;
                }
            }

            if (empty($acsDeviceParams['customer_service_id']) && $mac) {
                $pppoe = \App\Models\ISP\PPPoEUser::where('mac_address', $mac)->first();
                if ($pppoe && $pppoe->customer_service_id) {
                    $acsDeviceParams['customer_service_id'] = $pppoe->customer_service_id;
                }
            }
        }

        // Penanganan SoftDeletes + Unique Constraint
        $deviceRecord = \App\Models\ACS\ACSDevice::forUserArea(auth()->user())->withTrashed()->where('uuid', $deviceId)->first();
        if ($deviceRecord) {
            // Restore jika sempat terhapus
            if ($deviceRecord->trashed()) {
                $deviceRecord->restore();
            }
            $deviceRecord->update($acsDeviceParams);
        } else {
            \App\Models\ACS\ACSDevice::create(array_merge(['uuid' => $deviceId], $acsDeviceParams));
        }

        return $deviceId;
    }

    public function render()
    {
        $query = ACSDevice::forUserArea(auth()->user())->with([
            'customerService.customer',
            'asset',
            'onu.customer',
            'onu.customerService.customer',
            'olt',
            'vendor'
        ]);

        if ($this->search) {
            $term = trim($this->search);
            $query->where(function($q) use ($term) {
                $q->where('serial_number', 'like', '%' . $term . '%')
                  ->orWhere('mac_address', 'like', '%' . $term . '%')
                  ->orWhere('model', 'like', '%' . $term . '%')
                  ->orWhere('ip_address', 'like', '%' . $term . '%')
                  ->orWhere('connection_request_url', 'like', '%' . $term . '%')
                  ->orWhereHas('customerService', function($csq) use ($term) {
                      $csq->where('username', 'like', '%' . $term . '%')
                          ->orWhere('ip_address', 'like', '%' . $term . '%');
                  })
                  ->orWhereHas('customerService.customer', function($cq) use ($term) {
                      $cq->where('name', 'like', '%' . $term . '%')
                        ->orWhere('code', 'like', '%' . $term . '%')
                        ->orWhere('phone', 'like', '%' . $term . '%');
                  })
                  ->orWhereHas('onu.customer', function($cq) use ($term) {
                      $cq->where('name', 'like', '%' . $term . '%')
                        ->orWhere('code', 'like', '%' . $term . '%');
                  });
            });
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        $devices = $query->orderBy($this->sortField, $this->sortDirection)
                        ->paginate($this->perPage);

        return view('livewire.acs.device.index', compact('devices'));
    }

    public function summon($uuid)
    {
        try {
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $success = $driver->summonDevice($uuid);
            if ($success) {
                // Tunggu sejenak agar Inform selesai diproses oleh GenieACS, lalu perbarui data lokal
                try {
                    usleep(1200000); // 1.2 detik
                    $latest = $driver->getDeviceParameters($uuid);
                    if (!empty($latest)) {
                        $this->saveDeviceData($latest);
                    }
                } catch (\Throwable $te) {
                    // Abaikan jika proses fetch parameters gagal di background
                }

                session()->flash('success', "Perintah summon berhasil dikirim ke perangkat ($uuid). Data perangkat dan status telah diperbarui.");
            } else {
                session()->flash('error', "Gagal melakukan summon. Perangkat mungkin mati atau IP tidak terjangkau.");
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }
}



