<?php

namespace App\Services\ISP;

use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Odp;
use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\ISP\OnuSignal;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class FiberLinkStatusService
{
    public function getChainForCustomer(int $customerId): array
    {
        $customer = Customer::findOrFail($customerId);
        $services = CustomerService::where('customer_id', $customerId)
            ->with(['onu:id,olt_id,odp_id,pon_port,serial_number,mac_address,status,rx_power_dbm,tx_power_dbm,snr_db,last_seen_at,provision_status,wifi_ssid,name,model,vendor_id',
                     'onu.olt:id,name,ip_address,status,temperature,last_polled_at,vendor_id,uptime_text',
                     'onu.olt.vendor:id,name',
                     'onu.vendor:id,name',
                     'onu.odp:id,name,latitude,longitude,address,port_count,used_port_count,status,code,split_ratio',
                     'service:id,name',
                     'contract:id,contract_no,start_date,end_date',
            ])
            ->whereNotNull('onu_id')
            ->orderByDesc('activated_at')
            ->limit(5)
            ->get();

        $chains = [];
        foreach ($services as $svc) {
            if (!$svc->onu) {
                continue;
            }
            $onu = $svc->onu;
            $olt = $onu->olt;
            $odp = $onu->odp;

            $onuStatus = $onu->is_online ? 'online' : 'offline';
            $oltStatus = $olt && $olt->is_online ? 'online' : ($olt ? 'offline' : 'missing');
            $odpStatus = $odp?->status === 'active' ? 'active' : 'inactive';

            $link = [];
            $link[] = $this->node('customer', (string)$customer->id, $customer->name, $svc->status === 'active' ? 'active' : $svc->status, [
                'customer_service_id' => $svc->id,
                'contract_no' => $svc->contract?->contract_no,
                'service_name' => $svc->service?->name,
                'activated_at' => (string)$svc->activated_at,
            ]);

            if ($odp) {
                $link[] = $this->node('odp', (string)$odp->id, $odp->name ?? $odp->code, $odpStatus, [
                    'port_count' => (int)$odp->port_count,
                    'used_port_count' => (int)$odp->used_port_count,
                    'occupancy' => $odp->occupancy_percent . '%',
                    'split_ratio' => (int)$odp->split_ratio,
                    'gps' => $odp->gps_available ? ['lat' => (float)$odp->latitude, 'lng' => (float)$odp->longitude] : null,
                ]);
            }

            if ($onu) {
                $signals = [];
                if ($onu->rx_power_dbm !== null) {
                    $signals[] = ['rx_dbm' => (float)$onu->rx_power_dbm];
                }
                if ($onu->tx_power_dbm !== null) {
                    $signals[] = ['tx_dbm' => (float)$onu->tx_power_dbm];
                }
                if ($onu->snr_db !== null) {
                    $signals[] = ['snr_db' => (float)$onu->snr_db];
                }
                $link[] = $this->node('onu', (string)$onu->id, $onu->name ?: ($onu->model ?: ($onu->serial_number ?: 'ONU-' . $onu->id)), $onuStatus, [
                    'serial' => $onu->serial_number,
                    'mac' => $onu->mac_address,
                    'pon_port' => $onu->pon_port,
                    'vendor' => $onu->vendor?->name,
                    'signal' => $signals,
                    'signal_quality' => $onu->signal_quality,
                    'last_seen' => (string)$onu->last_seen_at,
                    'provision_status' => $onu->provision_status,
                    'wifi_ssid' => $onu->wifi_ssid,
                ]);
            }

            if ($olt) {
                $link[] = $this->node('olt', (string)$olt->id, $olt->name, $oltStatus, [
                    'ip' => $olt->ip_address,
                    'vendor' => $olt->vendor?->name,
                    'temperature' => $olt->temperature ? (float)$olt->temperature : null,
                    'uptime' => $olt->uptime_text,
                    'last_polled' => (string)$olt->last_polled_at,
                ]);
            }

            $healthy = collect($link)->every(fn ($n) => in_array($n['status'], ['active', 'online', 'good', 'excellent']));
            $signalBad = isset($onu) && in_array($onu->signal_quality, ['warning', 'critical']);

            $chains[] = [
                'customer_service_id' => $svc->id,
                'healthy' => $healthy && !$signalBad,
                'signal_warning' => $signalBad,
                'hops' => $link,
            ];
        }

        return [
            'customer_id' => $customerId,
            'customer_name' => $customer->name,
            'chains' => $chains,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    protected function node(string $type, string $id, string $label, string $status, array $meta = []): array
    {
        return [
            'type' => $type,
            'id' => $id,
            'label' => $label,
            'status' => $status,
            'meta' => $meta,
        ];
    }

    public function diagnoseChain(int $customerServiceId): array
    {
        $svc = CustomerService::with(['onu', 'onu.olt', 'onu.odp', 'customer'])->findOrFail($customerServiceId);
        $result = [
            'customer_service_id' => $customerServiceId,
            'customer_name' => $svc->customer?->name,
            'overall_status' => 'unknown',
            'checks' => [],
            'recommendations' => [],
        ];

        if (!$svc->onu) {
            $result['checks'][] = ['name' => 'onu_bound', 'status' => 'fail', 'detail' => 'Customer service tidak terikat dengan ONU'];
            $result['recommendations'][] = 'Ikat customer service dengan ONU di menu Pelanggan → Instalasi';
            $result['overall_status'] = 'fail';
            return $result;
        }

        $onu = $svc->onu;
        $olt = $onu->olt;
        $odp = $onu->odp;

        if (!$olt) {
            $result['checks'][] = ['name' => 'olt_present', 'status' => 'fail', 'detail' => 'ONU tidak terikat dengan OLT'];
            $result['overall_status'] = 'fail';
            return $result;
        }

        $oName = $olt->name . '@' . $olt->ip_address;
        if ($olt->is_online) {
            $result['checks'][] = ['name' => 'olt_online', 'status' => 'pass', 'detail' => "$oName online"];
        } else {
            $result['checks'][] = ['name' => 'olt_online', 'status' => 'fail', 'detail' => "$oName OFFLINE. Cek power ONU OLT, koneksi management, SNMP community"];
            $result['recommendations'][] = "Restart OLT $oName atau periksa kabel FO backbone";
        }

        if ($odp) {
            $occ = $odp->occupancy_percent;
            if ($occ >= 90) {
                $result['checks'][] = ['name' => 'odp_occupancy', 'status' => 'warn', 'detail' => "ODP $odp->name okupansi $occ%, harus tambah splitter"];
                $result['recommendations'][] = "Tambah ODP / splitter baru di area $odp->name";
            } else {
                $result['checks'][] = ['name' => 'odp_occupancy', 'status' => 'pass', 'detail' => "ODP $odp->name ok ($occ%)"];
            }
        }

        if ($onu->is_online) {
            $result['checks'][] = ['name' => 'onu_online', 'status' => 'pass', 'detail' => "ONU $onu->serial_number online"];
        } else {
            $result['checks'][] = ['name' => 'onu_online', 'status' => 'fail', 'detail' => "ONU $onu->serial_number OFFLINE sejak $onu->last_seen_at"];
            $result['recommendations'][] = 'Cek power adaptor ONU, kabel FO dari ODP ke ONU, clean core konektor';
        }

        if ($onu->rx_power_dbm !== null) {
            $rx = (float)$onu->rx_power_dbm;
            if ($rx < -28) {
                $result['checks'][] = ['name' => 'onu_rx_power', 'status' => 'fail', 'detail' => "RX $rx dBm di bawah -28 dBm. FO kotor/putus/connector kotor"];
                $result['recommendations'][] = 'Bersihkan konektor FO ONU, ODP dan OLT core, cek kabel FO dari tikus / terjepit';
            } elseif ($rx < -25) {
                $result['checks'][] = ['name' => 'onu_rx_power', 'status' => 'warn', 'detail' => "RX $rx dBm mendekati ambang batas"];
                $result['recommendations'][] = 'Jadwalkan pengecekan konektor FO';
            } else {
                $result['checks'][] = ['name' => 'onu_rx_power', 'status' => 'pass', 'detail' => "RX $rx dBm normal"];
            }
        }

        if ($svc->status !== 'active') {
            $result['checks'][] = ['name' => 'service_status', 'status' => 'warn', 'detail' => "Status service $svc->status"];
        } else {
            $result['checks'][] = ['name' => 'service_status', 'status' => 'pass', 'detail' => 'Service aktif'];
        }

        $fail = collect($result['checks'])->where('status', 'fail')->count();
        $warn = collect($result['checks'])->where('status', 'warn')->count();
        $result['overall_status'] = match (true) {
            $fail > 0 => 'fail',
            $warn > 0 => 'warn',
            default => 'pass',
        };
        return $result;
    }

    public function getProblematicOdps(int $limit = 50): array
    {
        $odps = Odp::active()
            ->withCount(['onus as onus_total', 'onus as onus_active' => function ($q) {
                $q->where('status', 'active');
            }])
            ->withSum([
                'onus as rx_critical_count' => function ($q) {
                    $q->whereNotNull('rx_power_dbm')->where('rx_power_dbm', '<=', config('olt-drivers.thresholds.onu_rx_power_critical_low', -28.0));
                }
            ], 'id')
            ->has('onus', '>=', 1)
            ->limit($limit)
            ->get();

        $problems = [];
        foreach ($odps as $odp) {
            $total = max(1, (int)$odp->onus_total);
            $badRatio = (int)($odp->rx_critical_count ?? 0) / $total;
            if ($badRatio >= 0.4 || $odp->occupancy_percent >= 90) {
                $problems[] = [
                    'odp_id' => $odp->id,
                    'name' => $odp->name ?: $odp->code,
                    'occupancy_percent' => $odp->occupancy_percent,
                    'rx_critical_count' => (int)($odp->rx_critical_count ?? 0),
                    'onus_total' => $total,
                    'bad_ratio_percent' => round($badRatio * 100, 1),
                    'reason' => $badRatio >= 0.4 ? 'signal_mass_drop' : 'high_occupancy',
                    'gps' => $odp->gps_available ? [(float)$odp->longitude, (float)$odp->latitude] : null,
                ];
            }
        }
        return $problems;
    }
}
