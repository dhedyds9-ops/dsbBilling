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
        try {
            $acsService = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $query = [
                'projection' => '_id,_deviceId,_lastInform,VirtualParameters,InternetGatewayDevice.DeviceInfo,Device.DeviceInfo,InternetGatewayDevice.WANDevice,Device.WANDevice,Device.Optical,InternetGatewayDevice.ManagementServer.ConnectionRequestURL,Device.ManagementServer.ConnectionRequestURL'
            ];
            $devices = $acsService->listDevices($query, 5000);
            $count = 0;
            $syncedIds = [];
            
            \Illuminate\Support\Facades\DB::transaction(function () use ($devices, &$count, &$syncedIds) {
                foreach ($devices as $deviceData) {
                    $deviceId = $deviceData['_id'] ?? null;
                    if (!$deviceId) continue;

                // Dynamic IP & MAC Extraction
                $ip = null;
                $mac = null;
                $wanDevices = $deviceData['InternetGatewayDevice']['WANDevice'] ?? $deviceData['Device']['WANDevice'] ?? [];
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
                                        if (!empty($ppp['ExternalIPAddress']['_value'])) $ip = $ip ?: $ppp['ExternalIPAddress']['_value'];
                                        if (!empty($ppp['MACAddress']['_value'])) $mac = $mac ?: $ppp['MACAddress']['_value'];
                                    }
                                }
                                
                                $ipConns = $connNode['WANIPConnection'] ?? [];
                                if (is_array($ipConns)) {
                                    foreach ($ipConns as $iIdx => $ipc) {
                                        if ($iIdx === '_object' || !is_array($ipc)) continue;
                                        if (!empty($ipc['ExternalIPAddress']['_value'])) $ip = $ip ?: $ipc['ExternalIPAddress']['_value'];
                                        if (!empty($ipc['MACAddress']['_value'])) $mac = $mac ?: $ipc['MACAddress']['_value'];
                                    }
                                }
                            }
                        }
                    }
                }
                
                $mac = $mac ?? $deviceData['VirtualParameters']['pppoeMac']['_value'] ?? $deviceData['VirtualParameters']['PonMac']['_value'] ?? null;
                $ip = $ip ?? $deviceData['VirtualParameters']['pppoeIP']['_value'] ?? null;
                
                $model = $deviceData['_deviceId']['_ProductClass'] ?? 
                         $deviceData['InternetGatewayDevice']['DeviceInfo']['ProductClass']['_value'] ?? 
                         $deviceData['Device']['DeviceInfo']['ProductClass']['_value'] ?? 
                         $deviceData['InternetGatewayDevice']['DeviceInfo']['ModelName']['_value'] ?? 
                         $deviceData['Device']['DeviceInfo']['ModelName']['_value'] ?? null;
                
                // If model is empty string, try fallback
                if (empty(trim($model))) {
                    $model = $deviceData['InternetGatewayDevice']['DeviceInfo']['ModelName']['_value'] ?? null;
                }
                
                $connReqUrl = $deviceData['InternetGatewayDevice']['ManagementServer']['ConnectionRequestURL']['_value'] ?? 
                              $deviceData['Device']['ManagementServer']['ConnectionRequestURL']['_value'] ?? null;
                              
                if (!$ip && $connReqUrl) {
                    $parsedUrl = parse_url($connReqUrl);
                    $ip = $parsedUrl['host'] ?? null;
                }
                
                $lastInform = isset($deviceData['_lastInform']) ? \Carbon\Carbon::parse($deviceData['_lastInform']) : null;
                $status = ($lastInform && abs(now()->diffInMinutes($lastInform)) < 15) ? 'online' : 'offline';

                $serialNumber = $deviceData['_deviceId']['_SerialNumber'] ?? 
                                $deviceData['InternetGatewayDevice']['DeviceInfo']['SerialNumber']['_value'] ?? $deviceId;

                $extract = function($path) use ($deviceData) {
                    $parts = explode('.', $path);
                    $node = $deviceData;
                    foreach ($parts as $p) {
                        if (!is_array($node) || !array_key_exists($p, $node)) return null;
                        $node = $node[$p];
                    }
                    return isset($node['_value']) ? $node['_value'] : null;
                };

                $firmware = $extract('InternetGatewayDevice.DeviceInfo.SoftwareVersion') ?? $extract('Device.DeviceInfo.SoftwareVersion') ?? $extract('InternetGatewayDevice.DeviceInfo.HardwareVersion');
                $hardware = $extract('InternetGatewayDevice.DeviceInfo.HardwareVersion') ?? $extract('Device.DeviceInfo.HardwareVersion');
                
                $pppoeUsername = $extract('VirtualParameters.pppoeUsername') ?? 
                                 $extract('InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username') ?? 
                                 $extract('Device.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username');

                // Mencari RX Power lebih cerdas (Iterasi WANDevice)
                $rxPower = $extract('VirtualParameters.RXPower') ?? $extract('Device.Optical.1.Transceiver.RxPower');
                if ($rxPower === null && is_array($wanDevices)) {
                    // Cari konfigurasi PON yang ada didalam WANDevice
                    foreach ($wanDevices as $wdIdx => $wdNode) {
                        if ($wdIdx === '_object' || !is_array($wdNode)) continue;
                        
                        $possibleKeys = [
                            'X_FH_GponInterfaceConfig' => 'RXPower',
                            'X_ZTE-COM_WANPONInterfaceConfig' => 'RXPower',
                            'X_HW_PONInterfaceConfig' => 'RXPower',
                            'WANPONInterfaceConfig' => ['1', 'X_ZTE-COM_RxPower'],
                            'WANEponInterfaceConfig' => ['1', 'RxPower'],
                            'WANGPONInterfaceConfig' => ['1', 'RxPower'],
                            'X_BROADCOM_COM_PONInterfaceConfig' => 'RxPower'
                        ];
                        
                        foreach ($possibleKeys as $k => $v) {
                            if (isset($wdNode[$k])) {
                                if (is_array($v)) {
                                    if (isset($wdNode[$k][$v[0]][$v[1]]['_value'])) {
                                        $rxPower = $wdNode[$k][$v[0]][$v[1]]['_value'];
                                        break 2;
                                    }
                                } else {
                                    if (isset($wdNode[$k][$v]['_value'])) {
                                        $rxPower = $wdNode[$k][$v]['_value'];
                                        break 2;
                                    }
                                }
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
                    $rxPower = round($rxPower); // Since migration is integer, we round it
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

                // AUTO BINDING LOGIC
                if ($pppoeUsername) {
                    $customerService = \App\Models\Customer\CustomerService::where('username', $pppoeUsername)->first();
                    if ($customerService) {
                        $acsDeviceParams['customer_service_id'] = $customerService->id;
                        
                        // Cari Onu
                        $onu = \App\Models\ISP\Onu::where('serial_number', $serialNumber)->first();
                        if ($onu) {
                            $acsDeviceParams['onu_id'] = $onu->id;
                            
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

                // Penanganan SoftDeletes + Unique Constraint
                $deviceRecord = \App\Models\ACS\ACSDevice::withTrashed()->where('uuid', $deviceId)->first();
                if ($deviceRecord) {
                    // Restore jika sempat terhapus
                    if ($deviceRecord->trashed()) {
                        $deviceRecord->restore();
                    }
                    $deviceRecord->update($acsDeviceParams);
                } else {
                    \App\Models\ACS\ACSDevice::create(array_merge(['uuid' => $deviceId], $acsDeviceParams));
                }
                
                $syncedIds[] = $deviceId;
                $count++;
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

    public function render()
    {
        $query = ACSDevice::with(['customerService', 'asset', 'onu', 'olt', 'vendor']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('serial_number', 'like', '%' . $this->search . '%')
                  ->orWhere('mac_address', 'like', '%' . $this->search . '%')
                  ->orWhere('model', 'like', '%' . $this->search . '%')
                  ->orWhere('ip_address', 'like', '%' . $this->search . '%')
                  ->orWhere('connection_request_url', 'like', '%' . $this->search . '%');
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
                session()->flash('success', "Perintah summon berhasil dikirim ke perangkat ($uuid).");
            } else {
                session()->flash('error', "Gagal melakukan summon. Perangkat mungkin mati atau IP tidak terjangkau.");
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }
}