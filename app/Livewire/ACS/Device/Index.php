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

                        // 1. Cek last inform (toleransi 15 menit untuk interval standar inform 300-600 detik)
                        if (!empty($d['_lastInform'])) {
                            $lastInform = \Carbon\Carbon::parse($d['_lastInform']);
                            if (abs($now->diffInMinutes($lastInform)) <= 15) {
                                $isOnline = true;
                            }
                        }

                        // 2. Cek IP aktif di MikroTik session (toleransi hingga 45 menit jika PPPoE session aktif)
                        $ip = $d['_ip'] ?? null;
                        if (!$isOnline && !empty($ip) && isset($activeIpFlip[$ip])) {
                            if (isset($lastInform) && abs($now->diffInMinutes($lastInform)) <= 45) {
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
                            // Cek apakah database mencatat link optik ONU aktif di OLT DAN inform masih dalam 60 menit
                            $rec = \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                                ->where('uuid', $uuid)
                                ->first(['id', 'ip_address', 'serial_number', 'status']);
                            if ($rec) {
                                $hasActiveOnu = !empty($rec->serial_number) && \App\Models\ISP\Onu::withoutGlobalScopes()
                                    ->where('serial_number', $rec->serial_number)
                                    ->where('status', 'active')
                                    ->whereNotNull('rx_power_dbm')
                                    ->where('rx_power_dbm', '>=', -35.0)
                                    ->exists();

                                if ($hasActiveOnu && isset($lastInform) && abs($now->diffInMinutes($lastInform)) <= 60) {
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

    public function unlinkCustomer($id)
    {
        try {
            $device = ACSDevice::withoutGlobalScopes()->findOrFail($id);
            $device->update(['customer_service_id' => null]);

            if ($device->onu_id) {
                \App\Models\Customer\CustomerService::withoutGlobalScopes()
                    ->where('onu_id', $device->onu_id)
                    ->update(['onu_id' => null]);

                $onu = \App\Models\ISP\Onu::withoutGlobalScopes()->find($device->onu_id);
                if ($onu && \Illuminate\Support\Facades\Schema::hasColumn('onus', 'customer_id')) {
                    $onu->customer_id = null;
                    $onu->save();
                }
            }

            session()->flash('success', "Kaitan pelanggan pada perangkat {$device->serial_number} berhasil dilepas.");
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal melepas kaitan: ' . $e->getMessage());
        }
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

        // Telemetri Multi-Vendor Terpadu (AcsTelemetryParser)
        $flat = \App\Services\ACS\AcsTelemetryParser::flatten($deviceData);
        $serialNumber = \App\Services\ACS\AcsTelemetryParser::parseSerialNumber($flat, $deviceId, $deviceData);
        $mac = \App\Services\ACS\AcsTelemetryParser::parseMacAddress($flat);
        $matchedOnu = $findOnu($serialNumber, $mac);

        $connReqUrl = $deviceData['InternetGatewayDevice']['ManagementServer']['ConnectionRequestURL']['_value'] ?? 
                      $deviceData['Device']['ManagementServer']['ConnectionRequestURL']['_value'] ?? null;

        $pppoeAndIp = \App\Services\ACS\AcsTelemetryParser::parsePppoeAndIp($flat, $connReqUrl);
        $tr069Ip = $pppoeAndIp['tr069_ip'];
        $pppoeIp = $pppoeAndIp['pppoe_ip'];
        $pppoeUsername = $pppoeAndIp['pppoe_username'];

        if ($tr069Ip && (empty($connReqUrl) || str_contains($connReqUrl, '0.0.0.0'))) {
            $connReqUrl = "http://{$tr069Ip}:7547/";
        }
        $ip = $pppoeIp ?: ($tr069Ip ?: null);

        // Penguraian Optik Cerdas: Mengatasi Anomali ZTE Swap & Fallback DDM OLT
        $optical = \App\Services\ACS\AcsTelemetryParser::parseOptical($flat, $matchedOnu);
        $rxPower = $optical['rx_power'];

        $identity = \App\Services\ACS\AcsTelemetryParser::parseDeviceIdentity($flat, $deviceData, $serialNumber);

        $lastInform = isset($deviceData['_lastInform']) ? \Carbon\Carbon::parse($deviceData['_lastInform']) : null;
        $status = ($lastInform && abs(now()->diffInMinutes($lastInform)) <= 20) ? 'online' : 'offline';

        $acsDeviceParams = [
            'signal' => ($rxPower !== null && $rxPower < 0) ? $rxPower : ($matchedOnu?->rx_power_dbm ?? null),
            'serial_number' => $serialNumber,
            'mac_address' => $mac,
            'model' => $identity['model'],
            'ip_address' => $ip,
            'connection_request_url' => $connReqUrl,
            'status' => $status,
            'last_inform' => $lastInform,
            'last_contact' => now(),
            'firmware_version' => $identity['software_version'],
            'software_version' => $identity['software_version'],
            'hardware_version' => $identity['hardware_version'],
            'manufacturer' => $identity['manufacturer'],
            'oui' => $identity['oui'],
            'onu_id' => $matchedOnu?->id,
            'olt_id' => $matchedOnu?->olt_id,
            'odp_id' => $matchedOnu?->odp_id,
        ];

        // AUTO BINDING LOGIC
        if ($pppoeUsername) {
            $customerService = \App\Models\Customer\CustomerService::where('username', $pppoeUsername)->first();
            if ($customerService) {
                // Cari Onu
                $onu = $findOnu($serialNumber, $mac);

                // Jika ONU ini sudah ada di database dan secara eksplisit kaitan pelanggan telah dilepas oleh admin,
                // jangan otomatis mengaitkan kembali ke pelanggan lama hanya karena sisa konfigurasi WAN di modem.
                $isExplicitlyUnlinked = $onu && empty($onu->customer_id) && (!$onu->relationLoaded('customerService') || !$onu->customerService);

                if (!$isExplicitlyUnlinked) {
                    $acsDeviceParams['customer_service_id'] = $customerService->id;
                    if (!empty($customerService->olt_id)) {
                        $acsDeviceParams['olt_id'] = $customerService->olt_id;
                    }
                }

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
                            'rx_power_dbm'     => ($rxPower !== null && $rxPower < 0) ? $rxPower : null,
                            'tx_power_dbm'     => (!empty($optical['tx_power']) && $optical['tx_power'] > 0) ? $optical['tx_power'] : null,
                            'status'           => 'active',
                            'provision_status' => 'provisioned',
                        ]);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("Auto-create Onu from ACS Inform failed: " . $e->getMessage());
                    }
                }

                if ($onu) {
                    $acsDeviceParams['onu_id'] = $onu->id; $acsDeviceParams['olt_id'] = $onu->olt_id; $acsDeviceParams['odp_id'] = $onu->odp_id;
                    $onuUpdates = [];
                    if (empty($onu->mac_address) && $mac) {
                        $onuUpdates['mac_address'] = $mac;
                    }
                    if ($rxPower !== null && $rxPower < 0) {
                        $onuUpdates['rx_power_dbm'] = $rxPower;
                    }
                    if (!empty($optical['tx_power']) && $optical['tx_power'] > 0) {
                        $onuUpdates['tx_power_dbm'] = $optical['tx_power'];
                    }
                    if (!empty($onuUpdates)) {
                        $onu->update($onuUpdates);
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
                $onuUpdates = [];
                if (empty($onu->mac_address) && $mac) {
                    $onuUpdates['mac_address'] = $mac;
                }
                if ($rxPower !== null && $rxPower < 0) {
                    $onuUpdates['rx_power_dbm'] = $rxPower;
                }
                if (!empty($optical['tx_power']) && $optical['tx_power'] > 0) {
                    $onuUpdates['tx_power_dbm'] = $optical['tx_power'];
                }
                if (!empty($onuUpdates)) {
                    $onu->update($onuUpdates);
                }
                $cs = \App\Models\Customer\CustomerService::withoutGlobalScopes()->where('onu_id', $onu->id)->first();
                if ($cs) {
                    $acsDeviceParams['customer_service_id'] = $cs->id;
                }
            }

            if (empty($acsDeviceParams['customer_service_id']) && $mac) {
                $pppoe = \App\Models\ISP\PPPoEUser::withoutGlobalScopes()->where('mac_address', $mac)->first();
                if ($pppoe && $pppoe->customer_service_id) {
                    $acsDeviceParams['customer_service_id'] = $pppoe->customer_service_id;
                }
            }
        }

        // Pastikan jika tidak ada customer yang terikat, eksplisit set null agar DB di-update
        if (!isset($acsDeviceParams['customer_service_id']) || empty($acsDeviceParams['customer_service_id'])) {
            $acsDeviceParams['customer_service_id'] = null;
        }

        // Validasi status: utamakan lastInform TR-069.
        // JANGAN blind override jika PPPoE online (karena pelanggan bisa jadi sedang pakai modem baru, sedangkan modem ini modem lama!)
        if ($status !== 'online') {
            $hasActiveOnu = false;
            if (!empty($acsDeviceParams['onu_id'])) {
                $linkedOnu = \App\Models\ISP\Onu::find($acsDeviceParams['onu_id']);
                if ($linkedOnu && $linkedOnu->status === 'active' && (!empty($linkedOnu->rx_power_dbm) || ($linkedOnu->last_seen_at && abs(now()->diffInMinutes($linkedOnu->last_seen_at)) <= 60))) {
                    $hasActiveOnu = true;
                }
            } elseif (!empty($serialNumber)) {
                $matchedOnu = $findOnu($serialNumber, $mac);
                if ($matchedOnu && $matchedOnu->status === 'active' && (!empty($matchedOnu->rx_power_dbm) || ($matchedOnu->last_seen_at && abs(now()->diffInMinutes($matchedOnu->last_seen_at)) <= 60))) {
                    $hasActiveOnu = true;
                }
            }

            if ($hasActiveOnu && $lastInform && abs(now()->diffInMinutes($lastInform)) <= 60) {
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



