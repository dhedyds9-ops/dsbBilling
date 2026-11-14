<?php

namespace App\Services\ISP;

use App\Models\ISP\Olt;
use App\Models\ISP\OltMetric;
use App\Models\ISP\Onu;
use App\Models\ISP\OnuSignal;
use App\Models\ISP\PonPort;
use App\Services\Adapters\Provisioning\OltRegistry;
use App\Services\ISP\RootCauseAnalysisEngine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class OltPollingService
{
    public function __construct(protected OltRegistry $registry)
    {
    }

    public function pollAll(): array
    {
        $olts = Olt::reachable()->with(['vendor', 'ponPorts'])->cursor();
        $results = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'onus_online' => 0,
            'onus_offline' => 0,
            'alerts' => [],
        ];
        foreach ($olts as $olt) {
            $results['total']++;
            try {
                $pollResult = $this->pollOlt($olt);
                if ($pollResult['success']) {
                    $results['success']++;
                    $results['onus_online'] += $pollResult['onu_online'] ?? 0;
                    $results['onus_offline'] += $pollResult['onu_offline'] ?? 0;
                    $results['alerts'] = array_merge($results['alerts'], $pollResult['alerts'] ?? []);
                } else {
                    $results['failed']++;
                }
            } catch (Throwable $e) {
                Log::error('OLT Poll exception for ' . $olt->name, ['exception' => $e]);
                $results['failed']++;
            }
        }

        // After all OLTs are polled, run Root Cause Analysis to detect mass outages
        try {
            app(RootCauseAnalysisEngine::class)->runAnalysis();
        } catch (Throwable $e) {
            Log::error('RootCauseAnalysisEngine failed', ['msg' => $e->getMessage()]);
        }

        return $results;
    }

    public function pollOlt(Olt $olt): array
    {
        try {
            $driver = $this->registry->forOlt($olt);
            $systemInfo = $driver->getSystemInfo();
            if (($systemInfo['status'] ?? 'offline') === 'offline') {
                throw new \Exception("SNMP Timeout atau koneksi ditolak (Uptime: N/A).");
            }
            $ponPorts = $driver->getPonPortsStatus();
            if (empty($ponPorts)) { throw new \Exception("0 Port PON terdeteksi. Kemungkinan SNMP Community tidak memiliki hak akses (View) ke Enterprise MIB OLT ini, atau tipe OLT tidak cocok."); }

            DB::transaction(function () use ($olt, $systemInfo, $ponPorts, $driver) {
                $updateData = [
                    'status' => 'active',
                    'last_polled_at' => now(),
                    'uptime_text' => $systemInfo['uptime'] ?? null,
                    'temperature' => $systemInfo['temperature'] ?? null,
                    'firmware_version' => $systemInfo['firmware'] ?? $olt->firmware_version,
                    'active_port_count' => collect($ponPorts)->where('status', 'up')->count(),
                    'pon_port_count' => count($ponPorts),
                    'status' => ($systemInfo['status'] ?? 'offline') === 'online' ? 'active' : 'inactive',
                    'last_polled_at' => now(),
                ];

                if (\Illuminate\Support\Facades\Schema::hasColumn('olts', 'last_error')) {
                    $updateData['last_error'] = null;
                }
                
                if (!empty($systemInfo['model']) && $systemInfo['model'] !== '-') {
                    $updateData['model'] = $systemInfo['model'];
                }
                if (!empty($systemInfo['serial_number']) && $systemInfo['serial_number'] !== '-') {
                    $updateData['serial_number'] = $systemInfo['serial_number'];
                }
                
                $olt->update($updateData);

                $this->recordMetric($olt, 'temperature', (float)($systemInfo['temperature'] ?? 0), 'C');

                foreach ($ponPorts as $port) {
                    $idx = (int)($port['port_index'] ?? 0);
                    $portName = $port['port_name'];
                    if (preg_match('/\/(\d+)\/(\d+)$/', $portName, $sp)) {
                        $slotNum = (int)$sp[1];
                        $rawPortNum = (int)$sp[2];
                        $portNumber = ($slotNum > 0) ? (($slotNum - 1) * 16 + $rawPortNum) : $rawPortNum;
                    } elseif (preg_match_all('/\d+/', $portName, $m)) {
                        $portNumber = (int)end($m[0]);
                    } else {
                        $portNumber = $idx;
                    }
                    $portStatus = $port['status'] === 'up' ? 'active' : 'inactive';

                    $dbPort = PonPort::where('olt_id', $olt->id)
                        ->where(function ($q) use ($portName, $portNumber, $idx) {
                            $q->where('name', $portName)
                              ->orWhere('port_number', $portNumber)
                              ->orWhere('port_index', $idx);
                        })
                        ->first();

                    $portCode = $olt->code . '-PON-' . $portNumber;
                    if (!$dbPort) {
                        if (PonPort::where('code', $portCode)->exists()) {
                            $portCode = $olt->code . '-PON-' . $olt->id . '-' . $portNumber;
                        }
                        $dbPort = PonPort::create([
                            'olt_id' => $olt->id,
                            'code' => $portCode,
                            'name' => $portName,
                            'port_number' => $portNumber,
                            'port_index' => $idx,
                            'type' => str_contains(strtolower($portName), 'gpon') ? 'gpon' : (str_contains(strtolower($portName), 'epon') ? 'epon' : (str_contains(strtolower($olt->model ?? ''), 'gpon') ? 'gpon' : 'epon')),
                            'status' => $portStatus,
                        ]);
                    } else {
                        $dbPort->update([
                            'status' => $portStatus,
                            'name' => $portName,
                            'port_number' => $portNumber,
                            'port_index' => $idx,
                            'type' => str_contains(strtolower($portName), 'gpon') ? 'gpon' : (str_contains(strtolower($portName), 'epon') ? 'epon' : (str_contains(strtolower($olt->model ?? ''), 'gpon') ? 'gpon' : 'epon')),
                        ]);
                    }

                    $this->recordMetric(
                        $olt,
                        'pon_port_status',
                        (int)($port['status'] === 'up'),
                        null,
                        $idx
                    );
                }
            });

            $alerts = [];
            $thresholds = config('olt-drivers.thresholds');
            if (($systemInfo['temperature'] ?? 0) >= $thresholds['olt_temperature_critical']) {
                $alerts[] = ['level' => 'critical', 'scope' => 'olt', 'olt' => $olt->name, 'msg' => "OLT temperature terlalu tinggi: {$systemInfo['temperature']}C"];
            }

            $onuOnline = 0;
            $onuOffline = 0;
            $updatedOnu = 0;
            $portErrors = [];
            $activePortCount = 0;

            // Hapus ONU dummy akibat respon error SNMP
            Onu::where("olt_id", $olt->id)->where(function($q) { $q->where("serial_number", "like", "%NOSUCH%")->orWhere("serial_number", "like", "%ERROR%")->orWhereRaw("LENGTH(serial_number) > 25"); })->delete();

            foreach ($ponPorts as $port) {
                if (($port['status'] ?? '') === 'up') {
                    $activePortCount++;
                }
                $idx = (int)($port['port_index'] ?? 0);
                if ($idx <= 0) {
                    continue;
                }

                $portName = $port['port_name'];
                if (preg_match('/\/(\d+)\/(\d+)$/', $portName, $sp)) {
                    $slotNum = (int)$sp[1];
                    $rawPortNum = (int)$sp[2];
                    $portNumber = ($slotNum > 0) ? (($slotNum - 1) * 16 + $rawPortNum) : $rawPortNum;
                } elseif (preg_match_all('/\d+/', $portName, $m)) {
                    $portNumber = (int)end($m[0]);
                } else {
                    $portNumber = $idx;
                }

                $dbPort = PonPort::where('olt_id', $olt->id)
                    ->where('name', $portName)
                    ->first();

                if (!$dbPort) {
                    $dbPort = PonPort::where('olt_id', $olt->id)
                        ->where('port_number', $portNumber)
                        ->first();
                }

                try {
                    $onus = $driver->getOnuRxPower($idx);
                    \Illuminate\Support\Facades\Log::info("OLT Polling {$olt->name} Port {$port['port_name']} (idx {$idx}): found " . count($onus) . " ONUs");
                } catch (\Throwable $e) { \Illuminate\Support\Facades\Log::error("ONU RxPower Failed for Port $idx", ["error" => $e->getMessage()]); $portErrors[] = $e->getMessage(); continue; }

                DB::transaction(function () use ($olt, $idx, $dbPort, $onus, &$onuOnline, &$onuOffline, &$updatedOnu, $thresholds, &$alerts) {
                    $rxWarn = $thresholds['onu_rx_power_warning_low'] ?? -25.0;
                    $rxCrit = $thresholds['onu_rx_power_critical_low'] ?? -28.0;
                    $polledSns = [];

                    foreach ($onus as $item) {
                        $sn = $item['serial_number'] ?? null;
                        if (!$sn) {
                            continue;
                        }
                        $snNormalized = strtoupper(trim($sn));
                        $polledSns[] = $snNormalized;
                        $macAddress = null;
                        if (!empty($item['mac_address'])) {
                            $macCleaned = strtoupper(preg_replace('/[^A-F0-9]/i', '', trim($item['mac_address'])));
                            $macAddress = strlen($macCleaned) === 12 ? implode(':', str_split($macCleaned, 2)) : null;
                        }
                        // Candidate serial numbers (mendukung format ASCII dan Hex Vendor seperti ZTEG <-> 5A544547, HWTC, FHTT)
                        $candidates = [$snNormalized];
                        if (str_starts_with($snNormalized, 'ZTEG')) {
                            $candidates[] = '5A544547' . substr($snNormalized, 4);
                        } elseif (str_starts_with($snNormalized, '5A544547')) {
                            $candidates[] = 'ZTEG' . substr($snNormalized, 8);
                        } elseif (str_starts_with($snNormalized, 'HWTC')) {
                            $candidates[] = '48575443' . substr($snNormalized, 4);
                        } elseif (str_starts_with($snNormalized, '48575443')) {
                            $candidates[] = 'HWTC' . substr($snNormalized, 8);
                        } elseif (str_starts_with($snNormalized, 'FHTT')) {
                            $candidates[] = '46485454' . substr($snNormalized, 4);
                        } elseif (str_starts_with($snNormalized, '46485454')) {
                            $candidates[] = 'FHTT' . substr($snNormalized, 8);
                        }
                        $candidates = array_values(array_unique(array_filter($candidates)));

                        // 1. Prioritas Utama: Cari secara global tanpa batasan branch/reseller scope
                        // (karena serial_number adalah identitas fisik unik pada database)
                        $onu = Onu::withoutGlobalScopes()->withTrashed()
                            ->whereIn('serial_number', $candidates)
                            ->first();

                        // 2. Prioritas Kedua: Cari secara global berdasarkan mac_address jika ada
                        if (!$onu && $macAddress) {
                            $onu = Onu::withoutGlobalScopes()->withTrashed()
                                ->where('mac_address', $macAddress)
                                ->first();
                        }

                        // 3. Prioritas Ketiga: Fallback cari berdasarkan port & index pada OLT ini
                        if (!$onu) {
                            $onu = Onu::withoutGlobalScopes()->withTrashed()
                                ->where('olt_id', $olt->id)
                                ->where('pon_port', $idx)
                                ->where('onu_id_on_olt', (int)($item['onu_index'] ?? 0))
                                ->first();
                        }

                        $status = $item['status'] ?? 'unknown';

                        // Golden rule: jika redaman optik RX valid (-38 s.d -6 dBm), laser modem aktif -> PASTI ONLINE!
                        if (!empty($item['rx_power_dbm']) && (float)$item['rx_power_dbm'] >= -38.0 && (float)$item['rx_power_dbm'] <= -6.0) {
                            $status = 'online';
                        }

                        if ($onu && $onu->trashed()) {
                            // Hanya restore jika ONU benar-benar ONLINE & aktif di lapangan
                            // Jangan bangkitkan ONU offline / orphan yang sengaja sudah dihapus admin
                            if ($status === 'online') {
                                $onu->restore();
                            } else {
                                continue;
                            }
                        }

                        if (!$onu) {
                            // Cek sekali lagi sebelum create untuk mencegah race condition duplicate serial_number
                            $existingWithSn = Onu::withoutGlobalScopes()->withTrashed()
                                ->whereIn('serial_number', $candidates)
                                ->first();
                            if ($existingWithSn) {
                                $onu = $existingWithSn;
                                if ($onu->trashed()) {
                                    if ($status === 'online') {
                                        $onu->restore();
                                    } else {
                                        continue;
                                    }
                                }
                            } else {
                                try {
                                    $resolvedPortNum = ($dbPort && $dbPort->port_number) ? $dbPort->port_number : ($idx > 1000000 ? ($idx & 0xFF ?: (($idx >> 8) & 0xFF)) : $idx);
                                    $newStatusEnum = $status === 'online' ? 'active' : 'inactive';
                                    $onu = Onu::create([
                                        'olt_id' => $olt->id,
                                        'pon_port' => $resolvedPortNum,
                                        'pon_port_id' => $dbPort ? $dbPort->id : null,
                                        'onu_id_on_olt' => (int)($item['onu_index'] ?? 0),
                                        'serial_number' => $snNormalized,
                                        'mac_address' => $macAddress,
                                        'vendor_id' => $olt->vendor_id,
                                        'name' => !empty($item['name']) ? $item['name'] : ($snNormalized ?: ("ONU-{$idx}-" . ($item['onu_index'] ?? 0))),
                                        'model' => $item['model'] ?? null,
                                        'firmware_version' => $item['firmware_version'] ?? null,
                                        'status' => $newStatusEnum,
                                        'provision_status' => 'provisioned',
                                        'branch_id' => $olt->branch_id,
                                        'last_seen_at' => $status === 'online' ? now() : null,
                                    ]);
                                    
                                    try {
                                        app(\App\Services\ISP\CorrelationService::class)->evaluateOnu($onu);
                                    } catch (\Throwable $e) {
                                        Log::error('CorrelationService evaluateOnu error', ['msg' => $e->getMessage()]);
                                    }
                                } catch (\Illuminate\Database\QueryException $qe) {
                                    // Self-healing jika terjadi race condition duplicate serial number (Error 1062 / 23000)
                                    if ($qe->getCode() == 23000 || str_contains($qe->getMessage(), '1062')) {
                                        $onu = Onu::withoutGlobalScopes()->withTrashed()
                                            ->where(function ($q) use ($candidates, $macAddress) {
                                                $q->whereIn('serial_number', $candidates);
                                                if ($macAddress) {
                                                    $q->orWhere('mac_address', $macAddress);
                                                }
                                            })->first();

                                        if ($onu) {
                                            if ($onu->trashed() && $status === 'online') {
                                                $onu->restore();
                                            }
                                        } else {
                                            throw $qe;
                                        }
                                    } else {
                                        throw $qe;
                                    }
                                }
                            }
                        }

                        if ($status === 'online') {
                            $onuOnline++;
                        } else {
                            $onuOffline++;
                        }
                        $rxDbm = $item['rx_power_dbm'] ?? null;
                        $txDbm = $item['tx_power_dbm'] ?? null;
                        $snrDb = $item['snr_db'] ?? null;
                        $temperature = $item['temperature'] ?? null;
                        $firmware = $item['firmware_version'] ?? null;
                        $hardware = $item['hardware_version'] ?? null;
                        $model = $item['model'] ?? null;

                        if ($rxDbm !== null && $rxDbm <= $rxCrit) {
                            $alerts[] = ['level' => 'critical', 'scope' => 'onu', 'onu' => $onu->name, 'msg' => "RX terlalu rendah: {$rxDbm}dBm"];
                        } elseif ($rxDbm !== null && $rxDbm <= $rxWarn) {
                            $alerts[] = ['level' => 'warning', 'scope' => 'onu', 'onu' => $onu->name, 'msg' => "RX rendah: {$rxDbm}dBm"];
                        }

                        $newStatusEnum = $status === 'online' ? 'active' : 'inactive';
                        $oldStatusEnum = $onu->status;
                        
                        $resolvedPortNum = ($dbPort && $dbPort->port_number) ? $dbPort->port_number : ($idx > 1000000 ? ($idx & 0xFF ?: (($idx >> 8) & 0xFF)) : $idx);

                        $onuUpdate = [
                            'olt_id' => $olt->id,
                            'pon_port' => $resolvedPortNum,
                            'pon_port_id' => $dbPort ? $dbPort->id : null,
                            'onu_id_on_olt' => (int)($item['onu_index'] ?? $onu->onu_id_on_olt),
                            'rx_power_dbm' => $rxDbm,
                            'tx_power_dbm' => $txDbm,
                            'snr_db' => $snrDb,
                            'temperature' => $temperature,
                            'last_seen_at' => $status === 'online' ? now() : $onu->last_seen_at,
                            'status' => $newStatusEnum,
                        ];
                        if (!empty($olt->branch_id) && empty($onu->branch_id)) {
                            $onuUpdate['branch_id'] = $olt->branch_id;
                        }
                        if (!empty($macAddress)) {
                            $onuUpdate['mac_address'] = $macAddress;
                        }
                        if (!empty($firmware)) {
                            $onuUpdate['firmware_version'] = $firmware;
                        }
                        if (!empty($hardware)) {
                            $onuUpdate['hardware_version'] = $hardware;
                        }
                        if (!empty($model) && empty($onu->model)) {
                            $onuUpdate['model'] = $model;
                        }
                        if (!empty($item['name']) && trim((string) $item['name']) !== $snNormalized) {
                            $currentName = trim((string) ($onu->name ?? ''));
                            if ($currentName === '' || strtoupper($currentName) === $snNormalized || str_starts_with($currentName, 'ONU-')) {
                                $onuUpdate['name'] = trim((string) $item['name']);
                            }
                        }

                        // Cegah benturan duplicate unique serial_number
                        if (!empty($snNormalized) && $onu->serial_number !== $snNormalized) {
                            $conflict = Onu::withoutGlobalScopes()->withTrashed()
                                ->whereIn('serial_number', $candidates)
                                ->where('id', '!=', $onu->id)
                                ->first();
                            if ($conflict) {
                                // Record lain sudah memiliki serial_number ini.
                                // Alihkan pembaruan ke record $conflict tersebut sebagai representasi fisik modem yang sebenarnya.
                                $staleOnu = $onu;
                                $onu = $conflict;
                                if ($onu->trashed()) {
                                    $onu->restore();
                                }
                                $onuUpdate['serial_number'] = $snNormalized;
                                if ($staleOnu->id !== $onu->id && empty($staleOnu->customer_service_id)) {
                                    $staleOnu->update(['status' => 'inactive', 'onu_id_on_olt' => null]);
                                }
                            } else {
                                $onuUpdate['serial_number'] = $snNormalized;
                            }
                        }

                        try {
                            $onu->update($onuUpdate);
                        } catch (\Illuminate\Database\QueryException $qe) {
                            // Self-healing jika terjadi benturan 1062 duplicate key serial number
                            if ($qe->getCode() == 23000 || str_contains($qe->getMessage(), '1062')) {
                                $dup = Onu::withoutGlobalScopes()->withTrashed()
                                    ->whereIn('serial_number', $candidates)
                                    ->where('id', '!=', $onu->id)
                                    ->first();
                                if ($dup) {
                                    unset($onuUpdate['serial_number']);
                                    $onu->update($onuUpdate);
                                    if ($dup->trashed()) {
                                        $dup->restore();
                                    }
                                    $dup->update([
                                        'olt_id' => $olt->id,
                                        'pon_port' => $idx,
                                        'pon_port_id' => $dbPort ? $dbPort->id : null,
                                        'onu_id_on_olt' => (int)($item['onu_index'] ?? $dup->onu_id_on_olt),
                                        'status' => $newStatusEnum,
                                        'rx_power_dbm' => $rxDbm,
                                        'tx_power_dbm' => $txDbm,
                                        'last_seen_at' => $status === 'online' ? now() : $dup->last_seen_at,
                                    ]);
                                    $onu = $dup;
                                } else {
                                    throw $qe;
                                }
                            } else {
                                throw $qe;
                            }
                        }

                        if ($oldStatusEnum !== $newStatusEnum) {
                            $oldSt = $oldStatusEnum === 'active' ? 'online' : 'offline';
                            $newSt = $newStatusEnum === 'active' ? 'online' : 'offline';
                            
                            if ($newSt === 'offline' && $rxDbm !== null && $rxDbm <= $rxCrit) {
                                $newSt = 'los';
                            }
                            event(new \App\Events\ISP\OnuStatusChanged($onu->id, $oldSt, $newSt, $rxDbm));
                        }

                        try {
                            OnuSignal::create([
                                'onu_id' => $onu->id,
                                'olt_id' => $olt->id,
                                'pon_port' => $idx,
                                'rx_power_dbm' => $rxDbm,
                                'tx_power_dbm' => $txDbm,
                                'snr_db' => $snrDb,
                                'temperature' => $temperature,
                                'status' => $status,
                                'measured_at' => now(),
                            ]);
                        } catch (\Throwable $e) { \Illuminate\Support\Facades\Log::error("Swallowed exception caught: " . $e->getMessage());
        }
                        $updatedOnu++;
                    }
                    
                    // Mark ONUs as offline if they exist in DB for this port but were missing from OLT output
                    if ($dbPort || $idx) {
                        $missingOnus = \App\Models\ISP\Onu::withoutGlobalScopes()->where('olt_id', $olt->id)
                            ->where(function ($q) use ($dbPort, $idx) {
                                if ($dbPort) {
                                    $q->where('pon_port_id', $dbPort->id);
                                }
                                if ($idx) {
                                    $q->orWhere('pon_port', $idx);
                                }
                            })
                            ->whereNotIn('serial_number', $polledSns)
                            ->get();
                            
                        foreach ($missingOnus as $mOnu) {
                            $onuOffline++;
                            if ($mOnu->status !== 'inactive') { // 'inactive' is Eloquent enum for offline
                                $oldSt = 'online';
                                $mOnu->update(['status' => 'inactive', 'rx_power_dbm' => null, 'tx_power_dbm' => null]);
                                event(new \App\Events\ISP\OnuStatusChanged($mOnu->id, $oldSt, 'offline', null));
                            }
                        }
                    }
                    
                });
            }

            if (count($portErrors) > 0) { throw new \Exception("Gagal membaca " . count($portErrors) . " port PON. Kemungkinan Telnet/SNMP timeout. Detail: " . implode(', ', array_unique($portErrors))); }

            // Hitung jumlah riil ONU aktif & offline yang terdaftar pada OLT di database (single source of truth)
            $actualOnline = $olt->onus()->withoutGlobalScopes()->where('status', 'active')->count();
            $actualOffline = $olt->onus()->withoutGlobalScopes()->where('status', '!=', 'active')->count();

            $olt->update([
                'onu_active_count' => $actualOnline,
            ]);

            return [
                'success' => true,
                'olt_id' => $olt->id,
                'onu_online' => $actualOnline,
                'onu_offline' => $actualOffline,
                'alerts' => $alerts,
            ];
        } catch (Throwable $e) {
            $errorMsg = $e->getMessage();
            Log::warning('OLT Poll failed', ['olt_id' => $olt->id, 'msg' => $errorMsg]);
            try {
                $payload = [
                    'status' => 'inactive',
                    'last_polled_at' => now(),
                ];
                if (\Illuminate\Support\Facades\Schema::hasColumn('olts', 'last_error')) {
                    $payload['last_error'] = $errorMsg;
                }
                $olt->update($payload);
            } catch (Throwable $dbErr) {
                Log::error("Failed updating OLT status on poll failure: " . $dbErr->getMessage());
            }
            return [
                'success' => false,
                'olt_id' => $olt->id,
                'error' => $errorMsg,
                'message' => $errorMsg,
            ];
        }
    }

    protected function recordMetric(Olt $olt, string $key, float $value, ?string $unit = null, ?int $ponPort = null): void
    {
        try {
            OltMetric::create([
                'olt_id' => $olt->id,
                'pon_port' => $ponPort,
                'metric_key' => $key,
                'metric_value' => $value,
                'unit' => $unit,
                'measured_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::debug('metric insert failed', ['msg' => $e->getMessage()]);
        }
    }
}











