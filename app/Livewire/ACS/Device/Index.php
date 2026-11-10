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

        // Otomatis sinkronkan status live saat halaman dimuat
        $this->pollLiveStatus();
    }

    /**
     * Polling ringan untuk memperbarui status online/offline langsung dari GenieACS tanpa reload halaman
     */
    public function pollLiveStatus(): void
    {
        try {
            $acsService = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $devices = $acsService->listDevices(['projection' => '_id,_lastInform,_ip'], 5000);
            if (!empty($devices)) {
                $now = now();
                $activeIps = \App\Models\ISP\OnlineSession::pluck('address')->filter()->all();
                $activeIpFlip = array_flip($activeIps);

                foreach ($devices as $d) {
                    $uuid = $d['_id'] ?? null;
                    if (!$uuid) continue;

                    try {
                        $isOnline = false;

                        // 1. Cek last inform (toleransi 10 menit untuk interval standar inform 300 detik)
                        if (!empty($d['_lastInform'])) {
                            $lastInform = \Carbon\Carbon::parse($d['_lastInform']);
                            if (abs($now->diffInMinutes($lastInform)) <= 10) {
                                $isOnline = true;
                            }
                        }

                        // 2. Cek IP aktif di MikroTik session (harus ada kontak inform dalam 15 menit)
                        $ip = $d['_ip'] ?? null;
                        if (!$isOnline && !empty($ip) && isset($activeIpFlip[$ip])) {
                            if (isset($lastInform) && abs($now->diffInMinutes($lastInform)) <= 15) {
                                $isOnline = true;
                            }
                        }

                        $updateData = [];
                        if (isset($lastInform)) {
                            $updateData['last_inform'] = $lastInform;
                        }

                        if ($isOnline) {
                            $updateData['status'] = 'online';
                            $updateData['last_contact'] = $lastInform ?? $now;
                        } else {
                            // Cek apakah database mencatat link optik ONU aktif di OLT dalam 10 menit terakhir DAN inform masih wajar (<= 30 menit)
                            $rec = \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                                ->where('uuid', $uuid)
                                ->first(['id', 'ip_address', 'serial_number', 'status']);
                            if ($rec) {
                                $hasActiveOnu = !empty($rec->serial_number) && \App\Models\ISP\Onu::withoutGlobalScopes()
                                    ->where('serial_number', $rec->serial_number)
                                    ->where('status', 'active')
                                    ->whereNotNull('last_seen_at')
                                    ->where('last_seen_at', '>=', now()->subMinutes(10))
                                    ->exists();

                                if ($hasActiveOnu && isset($lastInform) && abs($now->diffInMinutes($lastInform)) <= 30) {
                                    $updateData['status'] = 'online';
                                    $updateData['last_contact'] = $now;
                                } else {
                                    $updateData['status'] = 'offline';
                                }
                            }
                        }

                        \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                            ->where('uuid', $uuid)
                            ->update($updateData);
                    } catch (\Throwable) {}
                }
            }
        } catch (\Throwable $e) {
            // Abaikan error jika GenieACS tidak dapat dihubungi
        }
    }

    public function delete($id)
    {
        $device = ACSDevice::findOrFail($id);
        $uuid = $device->uuid;
        $device->delete();

        if ($uuid) {
            try {
                $acsService = new \App\Services\Adapters\Monitoring\GenieACSDriver();
                $acsService->deleteDevice($uuid);
            } catch (\Throwable) {}
        }

        session()->flash('success', 'Device berhasil dihapus dari dsBilling dan server GenieACS!');
    }

    public function deleteAllOfflineDevices()
    {
        try {
            $offlineDevices = ACSDevice::where('status', 'offline')->get();
            $count = $offlineDevices->count();
            if ($count > 0) {
                $acsService = new \App\Services\Adapters\Monitoring\GenieACSDriver();
                foreach ($offlineDevices as $dev) {
                    if ($dev->uuid) {
                        try {
                            $acsService->deleteDevice($dev->uuid);
                        } catch (\Throwable) {}
                    }
                    $dev->delete();
                }
                session()->flash('success', "Berhasil menghapus {$count} device offline dari dsBilling dan server GenieACS!");
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
                'projection' => '_id,_deviceId,_ip,_lastInform,VirtualParameters,VirtualParameters.PonMac,VirtualParameters.ponMac,VirtualParameters.pppoeIP,VirtualParameters.IPTR069,VirtualParameters.pppoeMac,VirtualParameters.getSerialNumber,InternetGatewayDevice.DeviceInfo,Device.DeviceInfo,InternetGatewayDevice.WANDevice,Device.WANDevice,Device.Optical,InternetGatewayDevice.ManagementServer.ConnectionRequestURL,Device.ManagementServer.ConnectionRequestURL'
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

        // Helper validasi dan pembersih MAC Address
        $cleanMac = function ($val) {
            if (empty($val) || !is_string($val)) return null;
            $val = trim($val);
            if ($val === '' || $val === '00:00:00:00:00:00' || $val === 'ff:ff:ff:ff:ff:ff' || strtoupper($val) === 'FF:FF:FF:FF:FF:FF') {
                return null;
            }
            $hexOnly = preg_replace('/[^0-9A-Fa-f]/', '', $val);
            if (strlen($hexOnly) === 12) {
                return strtoupper(implode(':', str_split($hexOnly, 2)));
            }
            if (preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $val)) {
                return strtoupper(str_replace('-', ':', $val));
            }
            return null;
        };

        // Ekstraksi MAC Address (Prioritas: VirtualParameters.PonMac untuk ZTE ONU -> pppoeMac -> ZTE Vendor Extension -> WAN Connection -> LAN)
        $vpPonMac = $cleanMac($extract('VirtualParameters.PonMac'))
                 ?? $cleanMac($extract('VirtualParameters.ponMac'))
                 ?? $cleanMac($extract('VirtualParameters.PonMAC'))
                 ?? $cleanMac($extract('VirtualParameters.PONMAC'))
                 ?? $cleanMac($extract('VirtualParameters.pon_mac'))
                 ?? $cleanMac($deviceData['VirtualParameters']['PonMac']['_value'] ?? null)
                 ?? $cleanMac($deviceData['VirtualParameters']['ponMac']['_value'] ?? null);

        $vpPppoeMac = $cleanMac($extract('VirtualParameters.pppoeMac'))
                   ?? $cleanMac($extract('VirtualParameters.pppoeMAC'))
                   ?? $cleanMac($deviceData['VirtualParameters']['pppoeMac']['_value'] ?? null);

        $zteMac = $cleanMac($extract('InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.PonMac'))
               ?? $cleanMac($extract('Device.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.PonMac'))
               ?? $cleanMac($extract('InternetGatewayDevice.DeviceInfo.X_ZTE-COM_MacAddress'))
               ?? $cleanMac($extract('Device.DeviceInfo.X_ZTE-COM_MacAddress'));

        $wanMac = null;
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
                                $cand = $cleanMac($ppp['MACAddress']['_value'] ?? null);
                                if ($cand) {
                                    $wanMac = $cand;
                                    break 3;
                                }
                            }
                        }
                        $ipConns = $connNode['WANIPConnection'] ?? [];
                        if (is_array($ipConns)) {
                            foreach ($ipConns as $iIdx => $ipc) {
                                if ($iIdx === '_object' || !is_array($ipc)) continue;
                                $cand = $cleanMac($ipc['MACAddress']['_value'] ?? null);
                                if ($cand) {
                                    $wanMac = $cand;
                                    break 3;
                                }
                            }
                        }
                    }
                }
            }
        }

        $lanMac = $cleanMac($extract('InternetGatewayDevice.LANDevice.1.LANEthernetInterfaceConfig.1.MACAddress'))
               ?? $cleanMac($extract('Device.Ethernet.Interface.1.MACAddress'));

        $mac = $vpPonMac ?: ($vpPppoeMac ?: ($zteMac ?: ($wanMac ?: ($lanMac ?: null))));
        
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
        $status = ($lastInform && abs(now()->diffInMinutes($lastInform)) <= 10) ? 'online' : 'offline';

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
        
        $rawPppoeUser = $extract('VirtualParameters.pppoeUsername') 
                     ?? $extract('VirtualParameters.pppoeUsername2')
                     ?? $extract('VirtualParameters.pppUsername');
        $pppoeUsername = (is_scalar($rawPppoeUser) && trim((string)$rawPppoeUser) !== '') ? trim((string)$rawPppoeUser) : null;

        if (!$pppoeUsername && is_array($wanDevices)) {
            foreach ($wanDevices as $wdIdx => $wdNode) {
                if ($wdIdx === '_object' || !is_array($wdNode)) continue;
                $connDevs = $wdNode['WANConnectionDevice'] ?? [];
                if (is_array($connDevs)) {
                    foreach ($connDevs as $cdIdx => $cdNode) {
                        if ($cdIdx === '_object' || !is_array($cdNode)) continue;
                        $pppConns = $cdNode['WANPPPConnection'] ?? [];
                        if (is_array($pppConns)) {
                            foreach ($pppConns as $pppIdx => $pppNode) {
                                if ($pppIdx === '_object' || !is_array($pppNode)) continue;
                                $candUser = $pppNode['Username']['_value'] ?? null;
                                if (is_scalar($candUser) && trim((string)$candUser) !== '') {
                                    $pppoeUsername = trim((string)$candUser);
                                    break 3;
                                }
                            }
                        }
                    }
                }
            }
        }

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
            'last_contact' => now(),
            'firmware_version' => $firmware,
            'software_version' => $firmware,
            'hardware_version' => $hardware,
            'manufacturer' => $deviceData['_deviceId']['_Manufacturer'] ?? null,
            'oui' => $deviceData['_deviceId']['_OUI'] ?? null,
        ];

        // Helper pencarian ONU dengan toleransi format SN (ASCII vs HEX) dan Fallback MAC Address
        $findOnu = function ($sn, $macAddr = null) {
            if (!empty($sn)) {
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

                // Fiberhome HEX (46485454) <-> ASCII (FHTT)
                if (str_starts_with(strtoupper($snClean), '46485454')) {
                    $alt = 'FHTT' . substr($snClean, 8);
                    $onu = \App\Models\ISP\Onu::withoutGlobalScopes()->where('serial_number', $alt)->first();
                    if ($onu) return $onu;
                } elseif (str_starts_with(strtoupper($snClean), 'FHTT')) {
                    $alt = '46485454' . substr($snClean, 4);
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
            }

            // Fallback via MAC Address (sangat berguna untuk ONU ZTE / EPON)
            if (!empty($macAddr)) {
                $cleanMac = trim((string)$macAddr);
                $hexMac = preg_replace('/[^0-9A-Fa-f]/', '', $cleanMac);
                $onu = \App\Models\ISP\Onu::withoutGlobalScopes()
                    ->where('mac_address', $cleanMac)
                    ->orWhere('mac_address', strtoupper($cleanMac))
                    ->orWhere('mac_address', $hexMac)
                    ->first();
                if ($onu) return $onu;
            }

            return null;
        };

        // AUTO BINDING LOGIC
        if ($pppoeUsername) {
            $customerService = \App\Models\Customer\CustomerService::where('username', $pppoeUsername)->first();
            if ($customerService) {
                $acsDeviceParams['customer_service_id'] = $customerService->id;
                if (!empty($customerService->olt_id)) {
                    $acsDeviceParams['olt_id'] = $customerService->olt_id;
                }
                
                // Cari Onu
                $onu = $findOnu($serialNumber, $mac);

                // Auto-create Onu jika belum terdaftar di tabel onus namun customer dan OLT diketahui
                if (!$onu && !empty($serialNumber) && !empty($acsDeviceParams['olt_id'])) {
                    try {
                        $onu = \App\Models\ISP\Onu::create([
                            'olt_id'           => $acsDeviceParams['olt_id'],
                            'branch_id'        => $customerService->branch_id ?? null,
                            'customer_id'      => $customerService->customer_id ?? null,
                            'serial_number'    => strtoupper(trim($serialNumber)),
                            'mac_address'      => $mac,
                            'name'             => $customerService->customer?->name ?? strtoupper(trim($serialNumber)),
                            'model'            => $acsDeviceParams['model'] ?? null,
                            'firmware_version' => $acsDeviceParams['software_version'] ?? null,
                            'rx_power_dbm'     => $acsDeviceParams['signal'] ?? null,
                            'status'           => 'active',
                            'provision_status' => 'provisioned',
                        ]);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("Auto-create Onu from ACS Inform failed: " . $e->getMessage());
                    }
                }

                if ($onu) {
                    $acsDeviceParams['onu_id'] = $onu->id; $acsDeviceParams['olt_id'] = $onu->olt_id; $acsDeviceParams['odp_id'] = $onu->odp_id;
                    if (empty($onu->mac_address) && $mac) {
                        $onu->update(['mac_address' => $mac]);
                    }
                    
                    // Bind Onu ke CustomerService jika berbeda (Modem Baru)
                    // HANYA jika modem ini aktif/online atau CustomerService belum punya ONU aktif
                    if ($customerService->onu_id !== $onu->id) {
                        $currentOnu = $customerService->onu;
                        $isCurrentOnuActive = $currentOnu && $currentOnu->status === 'active';
                        if (!$isCurrentOnuActive || $status === 'online') {
                            if ($customerService->onu_id) {
                                \App\Models\ISP\Onu::where('id', $customerService->onu_id)->update(['status' => 'inactive']);
                            }
                            $customerService->update(['onu_id' => $onu->id]);
                        }
                    }
                }
            }
        }

        // Fallback Binding jika pppoeUsername tidak terbaca TR-069
        if (empty($acsDeviceParams['customer_service_id'])) {
            $onu = $findOnu($serialNumber, $mac);
            if ($onu) {
                $acsDeviceParams['onu_id'] = $onu->id; $acsDeviceParams['olt_id'] = $onu->olt_id; $acsDeviceParams['odp_id'] = $onu->odp_id;
                if (empty($onu->mac_address) && $mac) {
                    $onu->update(['mac_address' => $mac]);
                }
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

        // Validasi status: utamakan lastInform TR-069.
        // JANGAN blind override jika PPPoE online (karena pelanggan bisa jadi sedang pakai modem baru, sedangkan modem ini modem lama!)
        if ($status !== 'online') {
            $hasActiveOnu = false;
            if (!empty($acsDeviceParams['onu_id'])) {
                $linkedOnu = \App\Models\ISP\Onu::find($acsDeviceParams['onu_id']);
                if ($linkedOnu && $linkedOnu->status === 'active' && $linkedOnu->last_seen_at && abs(now()->diffInMinutes($linkedOnu->last_seen_at)) <= 10) {
                    $hasActiveOnu = true;
                }
            } elseif (!empty($serialNumber)) {
                $matchedOnu = $findOnu($serialNumber, $mac);
                if ($matchedOnu && $matchedOnu->status === 'active' && $matchedOnu->last_seen_at && abs(now()->diffInMinutes($matchedOnu->last_seen_at)) <= 10) {
                    $hasActiveOnu = true;
                }
            }

            if ($hasActiveOnu && $lastInform && abs(now()->diffInMinutes($lastInform)) <= 30) {
                $acsDeviceParams['status'] = 'online';
            } else {
                $acsDeviceParams['status'] = 'offline';
            }
        } else {
            $acsDeviceParams['status'] = 'online';
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
            'onu.olt',
            'onu.ponPort',
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
                  ->orWhereHas('olt', function($oq) use ($term) {
                      $oq->where('name', 'like', '%' . $term . '%');
                  })
                  ->orWhereHas('onu.olt', function($oq) use ($term) {
                      $oq->where('name', 'like', '%' . $term . '%');
                  })
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
            $result = $driver->summonDeviceResult($uuid);

            if ($result['connection_request_success'] || $result['task_queued']) {
                // Tunggu jeda toleransi handshake Inform agar GenieACS memperbarui parameter
                usleep(1500000); // 1.5 detik
                try {
                    $latest = $driver->getDeviceParameters($uuid);
                    if (!empty($latest)) {
                        $this->saveDeviceData($latest);
                    }
                } catch (\Throwable $te) {}

                $device = ACSDevice::where('uuid', $uuid)->first();
                $isNowOnline = $device && $device->status === 'online';

                if ($isNowOnline) {
                    session()->flash('success', "Perangkat ($uuid) berhasil disummon dan langsung ONLINE! Data parameter terbaru telah diperbarui.");
                } elseif ($result['connection_request_success']) {
                    session()->flash('success', "Perintah summon terkirim ke perangkat ($uuid). Menunggu proses Inform lengkap dari modem.");
                } else {
                    session()->flash('info', "Perintah summon telah diantrikan di server GenieACS untuk ($uuid). Data akan otomatis diperbarui saat modem terhubung.");
                }
            } else {
                session()->flash('error', "Gagal melakukan summon. " . ($result['error'] ?? 'Perangkat tidak ditemukan di GenieACS atau server ACS tidak merespons.'));
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error saat summon: ' . $e->getMessage());
        }
    }

    public function reboot($uuid)
    {
        try {
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $success = $driver->rebootDevice($uuid);
            
            $device = ACSDevice::where('uuid', $uuid)->orWhere('serial_number', $uuid)->first();
            if ($device) {
                \App\Models\ACS\DeviceTask::create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'acs_device_id' => $device->id,
                    'type' => 'reboot',
                    'status' => $success ? 'completed' : 'pending',
                    'created_by' => auth()->id()
                ]);
            }

            if ($success) {
                session()->flash('success', "Perintah restart modem ({$uuid}) berhasil dikirim ke perangkat.");
            } else {
                session()->flash('info', "Perintah restart modem ({$uuid}) berhasil diantrikan ke GenieACS.");
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal restart modem: ' . $e->getMessage());
        }
    }
}



