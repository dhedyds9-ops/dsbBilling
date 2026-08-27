<?php

namespace App\Services\ISP;

use App\Models\ISP\Olt;
use App\Models\ISP\OltMetric;
use App\Models\ISP\Onu;
use App\Models\ISP\OnuSignal;
use App\Services\Adapters\Provisioning\OltRegistry;
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
        return $results;
    }

    public function pollOlt(Olt $olt): array
    {
        try {
            $driver = $this->registry->forOlt($olt);
            $systemInfo = $driver->getSystemInfo();
            $ponPorts = $driver->getPonPortsStatus();

            DB::transaction(function () use ($olt, $systemInfo, $ponPorts, $driver) {
                $olt->update([
                    'uptime_text' => $systemInfo['uptime'] ?? null,
                    'temperature' => $systemInfo['temperature'] ?? null,
                    'firmware_version' => $systemInfo['firmware'] ?? $olt->firmware_version,
                    'active_port_count' => collect($ponPorts)->where('status', 'up')->count(),
                    'pon_port_count' => count($ponPorts),
                    'status' => ($systemInfo['status'] ?? 'offline') === 'online' ? 'active' : 'inactive',
                    'last_polled_at' => now(),
                ]);

                $this->recordMetric($olt, 'temperature', (float)($systemInfo['temperature'] ?? 0), 'C');

                foreach ($ponPorts as $port) {
                    $this->recordMetric(
                        $olt,
                        'pon_port_status',
                        (int)($port['status'] === 'up'),
                        null,
                        (int)($port['port_index'] ?? 0)
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
                try {
                    $onus = $driver->getOnuRxPower($idx);
                } catch (Throwable) {
                    continue;
                }

                DB::transaction(function () use ($olt, $idx, $onus, &$onuOnline, &$onuOffline, &$updatedOnu, $thresholds, &$alerts) {
                    $rxWarn = $thresholds['onu_rx_power_warning_low'] ?? -25.0;
                    $rxCrit = $thresholds['onu_rx_power_critical_low'] ?? -28.0;

                    foreach ($onus as $item) {
                        $sn = $item['serial_number'] ?? null;
                        if (!$sn) {
                            continue;
                        }
                        $onu = Onu::where('olt_id', $olt->id)
                            ->where(fn ($q) => $q->where('serial_number', $sn))
                            ->first();
                        if (!$onu) {
                            $onu = Onu::where('olt_id', $olt->id)
                                ->where('pon_port', $idx)
                                ->where('onu_id_on_olt', (int)($item['onu_index'] ?? 0))
                                ->first();
                        }
                        if (!$onu) {
                            continue;
                        }

                        $status = $item['status'] ?? 'unknown';
                        if ($status === 'online') {
                            $onuOnline++;
                        } else {
                            $onuOffline++;
                        }
                        $rxDbm = $item['rx_power_dbm'] ?? null;
                        if ($rxDbm !== null && $rxDbm <= $rxCrit) {
                            $alerts[] = ['level' => 'critical', 'scope' => 'onu', 'onu' => $onu->name, 'msg' => "RX terlalu rendah: {$rxDbm}dBm"];
                        } elseif ($rxDbm !== null && $rxDbm <= $rxWarn) {
                            $alerts[] = ['level' => 'warning', 'scope' => 'onu', 'onu' => $onu->name, 'msg' => "RX rendah: {$rxDbm}dBm"];
                        }

                        $onu->update([
                            'pon_port' => $idx,
                            'onu_id_on_olt' => (int)($item['onu_index'] ?? $onu->onu_id_on_olt),
                            'rx_power_dbm' => $rxDbm,
                            'last_seen_at' => $status === 'online' ? now() : $onu->last_seen_at,
                            'status' => $status === 'online' ? 'active' : 'inactive',
                        ]);

                        try {
                            OnuSignal::create([
                                'onu_id' => $onu->id,
                                'olt_id' => $olt->id,
                                'pon_port' => $idx,
                                'rx_power_dbm' => $rxDbm,
                                'tx_power_dbm' => $item['tx_power_dbm'] ?? null,
                                'snr_db' => $item['snr_db'] ?? null,
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
