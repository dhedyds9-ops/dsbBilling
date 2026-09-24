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

            DB::transaction(function () use ($olt, $systemInfo, $ponPorts, $driver) {
                $updateData = [
                    'uptime_text' => $systemInfo['uptime'] ?? null,
                    'temperature' => $systemInfo['temperature'] ?? null,
                    'firmware_version' => $systemInfo['firmware'] ?? $olt->firmware_version,
                    'active_port_count' => collect($ponPorts)->where('status', 'up')->count(),
                    'pon_port_count' => count($ponPorts),
                    'status' => ($systemInfo['status'] ?? 'offline') === 'online' ? 'active' : 'inactive',
                    'last_polled_at' => now(),
                ];
                
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
                    $portNumber = preg_match_all('/\d+/', $port['port_name'], $m) ? (int)end($m[0]) : $idx;
                    $portName = $port['port_name'];
                    $portStatus = $port['status'] === 'up' ? 'active' : 'inactive';

                    $dbPort = PonPort::where('olt_id', $olt->id)
                        ->where('port_number', $portNumber)
                        ->first();

                    if (!$dbPort) {
                        $dbPort = PonPort::create([
                            'olt_id' => $olt->id,
                            'code' => $olt->code . '-PON-' . $portNumber,
                            'name' => $portName,
                            'port_number' => $portNumber,
                            'type' => str_contains(strtolower($olt->vendor->name ?? ''), 'cdata') ? 'gpon' : 'epon',
                            'status' => $portStatus,
                        ]);
                    } else {
                        $dbPort->update([
                            'status' => $portStatus,
                            'name' => $portName,
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

            foreach ($ponPorts as $port) {
                if ($port['status'] !== 'up') {
                    continue;
                }
                $idx = (int)($port['port_index'] ?? 0);
                if ($idx <= 0) {
                    continue;
                }

                $portNumber = preg_match_all('/\d+/', $port['port_name'], $m) ? (int)end($m[0]) : $idx;
                $dbPort = PonPort::where('olt_id', $olt->id)
                    ->where('port_number', $portNumber)
                    ->first();

                try {
                    $onus = $driver->getOnuRxPower($idx);
                } catch (Throwable) {
                    continue;
                }

                DB::transaction(function () use ($olt, $idx, $dbPort, $onus, &$onuOnline, &$onuOffline, &$updatedOnu, $thresholds, &$alerts) {
                    $rxWarn = $thresholds['onu_rx_power_warning_low'] ?? -25.0;
                    $rxCrit = $thresholds['onu_rx_power_critical_low'] ?? -28.0;

                    foreach ($onus as $item) {
                        $sn = $item['serial_number'] ?? null;
                        if (!$sn) {
                            continue;
                        }
                        $snNormalized = strtoupper(trim($sn));
                        $macAddress = null;
                        if (!empty($item['mac_address'])) {
                            $macCleaned = strtoupper(preg_replace('/[^A-F0-9]/i', '', trim($item['mac_address'])));
                            $macAddress = strlen($macCleaned) === 12 ? implode(':', str_split($macCleaned, 2)) : null;
                        }
                        $onu = Onu::where('olt_id', $olt->id)
                            ->where(fn ($q) => $q->where('serial_number', $snNormalized))
                            ->first();
                        if (!$onu && $macAddress) {
                            $onu = Onu::where('olt_id', $olt->id)
                                ->where(fn ($q) => $q->where('mac_address', $macAddress))
                                ->first();
                        }
                        if (!$onu) {
                            $onu = Onu::where('olt_id', $olt->id)
                                ->where('pon_port', $idx)
                                ->where('onu_id_on_olt', (int)($item['onu_index'] ?? 0))
                                ->first();
                        }

                        $status = $item['status'] ?? 'unknown';

                        if (!$onu) {
                            $onu = Onu::create([
                                'olt_id' => $olt->id,
                                'pon_port' => $idx,
                                'pon_port_id' => $dbPort ? $dbPort->id : null,
                                'onu_id_on_olt' => (int)($item['onu_index'] ?? 0),
                                'serial_number' => $snNormalized,
                                'mac_address' => $macAddress,
                                'vendor_id' => $olt->vendor_id,
                                'name' => !empty($item['name']) ? $item['name'] : ($snNormalized ?: ("ONU-{$idx}-" . ($item['onu_index'] ?? 0))),
                                'model' => $item['model'] ?? null,
                                'firmware_version' => $item['firmware_version'] ?? null,
                                'status' => $status === 'online' ? 'active' : 'inactive',
                                'provision_status' => 'provisioned',
                            ]);
                            
                            try {
                                app(\App\Services\ISP\CorrelationService::class)->evaluateOnu($onu);
                            } catch (\Throwable $e) {
                                Log::error('CorrelationService evaluateOnu error', ['msg' => $e->getMessage()]);
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
                        
                        $onuUpdate = [
                            'pon_port' => $idx,
                            'pon_port_id' => $dbPort ? $dbPort->id : null,
                            'onu_id_on_olt' => (int)($item['onu_index'] ?? $onu->onu_id_on_olt),
                            'rx_power_dbm' => $rxDbm,
                            'tx_power_dbm' => $txDbm,
                            'snr_db' => $snrDb,
                            'temperature' => $temperature,
                            'last_seen_at' => $status === 'online' ? now() : $onu->last_seen_at,
                            'status' => $newStatusEnum,
                        ];
                        if (!empty($macAddress) && empty($onu->mac_address)) {
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
                        if (empty($onu->serial_number) || str_starts_with($onu->serial_number, 'CDATA-GPON-')) {
                            $onuUpdate['serial_number'] = $snNormalized;
                        }
                        $onu->update($onuUpdate);

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
                        } catch (Throwable) {
                        }
                        $updatedOnu++;
                    }
                });
            }

            if ($updatedOnu > 0) {
                $olt->update([
                    'onu_active_count' => $olt->onus()->where('status', 'active')->count(),
                ]);
            }

            return [
                'success' => true,
                'olt_id' => $olt->id,
                'onu_online' => $onuOnline,
                'onu_offline' => $onuOffline,
                'alerts' => $alerts,
            ];
        } catch (Throwable $e) {
            Log::warning('OLT Poll failed', ['olt_id' => $olt->id, 'msg' => $e->getMessage()]);
            try {
                $olt->update(['status' => 'inactive', 'last_polled_at' => now()]);
            } catch (Throwable) {
            }
            return [
                'success' => false,
                'olt_id' => $olt->id,
                'error' => $e->getMessage(),
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

