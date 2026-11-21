<?php

namespace App\Services\ISP;

use App\Models\CRM\Customer;
use App\Models\ISP\Odc;
use App\Models\ISP\Odp;
use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\ISP\PonPort;
use App\Models\ISP\Splitter;
use Illuminate\Support\Facades\Log;

class OpticalLinkBudgetService
{
    // Standar ITU-T G.652.D / G.671
    public const FIBER_LOSS_PER_KM = 0.25; // dB/km @ 1490nm downstream
    public const CONNECTOR_PAIR_LOSS = 0.30; // dB per pair (SC/UPC atau SC/APC)
    public const SPLICE_LOSS = 0.05; // dB per fusion splice

    public function __construct(
        protected GenieAcsDiagnosticService $acsDiagnosticService
    ) {
    }

    /**
     * Hitung Optical Link Budget untuk pelanggan tertentu
     */
    public function calculateCustomerBudget(Customer $customer, bool $forceFresh = false): array
    {
        // Cari ONU milik pelanggan
        $onu = null;
        if (method_exists($customer, 'onu') && $customer->onu) {
            $onu = $customer->onu;
        }

        if (!$onu && method_exists($customer, 'customerServices')) {
            $cs = $customer->customerServices()->with('onu')->first();
            $onu = $cs?->onu;
        }

        if (!$onu) {
            return [
                'success' => false,
                'message' => 'Perangkat ONU tidak ditemukan untuk pelanggan ini.',
                'customer' => [
                    'id' => $customer->id,
                    'code' => $customer->code,
                    'name' => $customer->name,
                ],
            ];
        }

        return $this->calculateOnuBudget($onu, $customer, $forceFresh);
    }

    /**
     * Hitung Optical Link Budget untuk ONU tertentu
     */
    public function calculateOnuBudget(Onu $onu, ?Customer $customer = null, bool $forceFresh = false): array
    {
        // 1. Resolve Customer jika belum disediakan
        if (!$customer && $onu->customerService && $onu->customerService->customer) {
            $customer = $onu->customerService->customer;
        }

        // 2. Resolve Relasi Topologi: ODP, ODC, OLT, PonPort
        $odp = $onu->odp ?: ($onu->odp_id ? Odp::withoutGlobalScopes()->with(['splitters.ports', 'odc'])->find($onu->odp_id) : null);
        $odc = $odp?->odc ?: ($odp?->odc_id ? Odc::withoutGlobalScopes()->with('splitters.ports')->find($odp->odc_id) : null);
        
        $olt = $onu->olt ?: ($odc?->olt ?: ($odp?->olt ?: ($onu->olt_id ? Olt::withoutGlobalScopes()->with('ponPorts')->find($onu->olt_id) : null)));
        $ponPort = $onu->ponPort ?: ($onu->pon_port_id ? PonPort::find($onu->pon_port_id) : null);
        if (!$ponPort && $olt && $onu->pon_port) {
            $ponPort = PonPort::where('olt_id', $olt->id)
                ->where(fn($q) => $q->where('port_number', $onu->pon_port)->orWhere('port_index', $onu->pon_port))
                ->first();
        }

        // 3. Daya Pancar OLT SFP (Tx Power)
        $txOlt = 4.5; // Default GPON Class C+
        $sfpClass = 'Class C+';
        if ($ponPort) {
            $txOlt = $ponPort->getEffectiveTxPower();
            $sfpClass = $ponPort->sfp_class ?: 'Class C+';
        }

        // 4. Hitung Jarak & Loss Tiap Segmen

        // Cek apakah ODC terhubung ke Parent ODC (Cascade Tier-1 -> Tier-2)
        $parentOdc = null;
        if ($odc && $odc->parent_odc_id) {
            $parentOdc = $odc->parentOdc ?: Odc::withoutGlobalScopes()->find($odc->parent_odc_id);
        }

        // Segmen A: Feeder Cable (OLT/POP ➔ ODC Utama / Tier-1)
        $feederTargetOdc = $parentOdc ?: $odc;
        $feederDistMeters = 1200; // default 1.2 km
        if ($olt && $feederTargetOdc && $olt->latitude && $olt->longitude && $feederTargetOdc->latitude && $feederTargetOdc->longitude) {
            $geoDist = self::calculateHaversineDistance(
                (float) $olt->latitude,
                (float) $olt->longitude,
                (float) $feederTargetOdc->latitude,
                (float) $feederTargetOdc->longitude
            );
            if ($geoDist && $geoDist > 10) {
                $feederDistMeters = round($geoDist * 1.25); // Tortuosity factor 1.25x jalan
            }
        }
        $feederLoss = round(($feederDistMeters / 1000) * self::FIBER_LOSS_PER_KM + (2 * self::CONNECTOR_PAIR_LOSS), 2);

        // Segmen B1: Splitter Parent ODC (Tier-1) & Sub-Feeder Cable (jika ada cascade)
        $parentOdcSplitterLoss = 0.0;
        $parentOdcSplitterRatio = '1:4';
        $subFeederDistMeters = 0;
        $subFeederLoss = 0.0;

        if ($parentOdc) {
            $parentOdcSplitterLoss = 7.2;
            if ($parentOdc->splitters && $parentOdc->splitters->isNotEmpty()) {
                $spParent = $parentOdc->splitters->first();
                $parentOdcSplitterLoss = (float) ($spParent->insertion_loss_db ?: 7.2);
                $parentOdcSplitterRatio = $spParent->ratio_label ?: ("1:" . ($spParent->ratio ?: 4));
            }

            // Sub-Feeder Cable (Parent ODC ➔ Sub-ODC)
            $subFeederDistMeters = 600; // default 600m
            if ($parentOdc->latitude && $parentOdc->longitude && $odc->latitude && $odc->longitude) {
                $geoDist = self::calculateHaversineDistance(
                    (float) $parentOdc->latitude,
                    (float) $parentOdc->longitude,
                    (float) $odc->latitude,
                    (float) $odc->longitude
                );
                if ($geoDist && $geoDist > 5) {
                    $subFeederDistMeters = round($geoDist * 1.25);
                }
            }
            $subFeederLoss = round(($subFeederDistMeters / 1000) * self::FIBER_LOSS_PER_KM + self::CONNECTOR_PAIR_LOSS, 2);
        }

        // Segmen B2: Splitter ODC (Tier-2 jika cascade, atau Tier-1 jika direct)
        $odcSplitterLoss = 7.2; // default 1:4 (7.2 dB)
        $odcSplitterRatio = '1:4';
        if ($odc && $odc->splitters && $odc->splitters->isNotEmpty()) {
            // Cari splitter yang tersambung ke ODP ini, atau splitter pertama
            $sp = null;
            if ($odp) {
                foreach ($odc->splitters as $candidateSp) {
                    $hasPortToOdp = $candidateSp->ports()->where('target_id', $odp->id)->exists();
                    if ($hasPortToOdp) {
                        $sp = $candidateSp;
                        break;
                    }
                }
            }
            $sp = $sp ?: $odc->splitters->first();
            if ($sp) {
                $odcSplitterLoss = (float) ($sp->insertion_loss_db ?: 7.2);
                $odcSplitterRatio = $sp->ratio_label ?: ("1:" . ($sp->ratio ?: 4));
            }
        }

        // Segmen C: Distribution Cable (ODC ➔ ODP)
        $distDistMeters = 800; // default 800m
        if ($odc && $odp && $odc->latitude && $odc->longitude && $odp->latitude && $odp->longitude) {
            $geoDist = self::calculateHaversineDistance(
                (float) $odc->latitude,
                (float) $odc->longitude,
                (float) $odp->latitude,
                (float) $odp->longitude
            );
            if ($geoDist && $geoDist > 5) {
                $distDistMeters = round($geoDist * 1.25);
            }
        }
        $distLoss = round(($distDistMeters / 1000) * self::FIBER_LOSS_PER_KM + self::CONNECTOR_PAIR_LOSS, 2);

        // Segmen D: Splitter ODP (Tier-2 jika direct, atau Tier-3 jika cascade)
        $odpSplitterLoss = 10.5; // default 1:8 (10.5 dB)
        $odpSplitterRatio = '1:8';
        if ($odp && $odp->splitters && $odp->splitters->isNotEmpty()) {
            $spOdp = $odp->splitters->first();
            $odpSplitterLoss = (float) ($spOdp->insertion_loss_db ?: 10.5);
            $odpSplitterRatio = $spOdp->ratio_label ?: ("1:" . ($spOdp->ratio ?: 8));
        }

        // Segmen E: Drop Cable & Roset Pelanggan (ODP ➔ Customer)
        $dropDistMeters = 85; // default 85 meter
        if ($odp && $customer && $odp->latitude && $odp->longitude && $customer->latitude && $customer->longitude) {
            $geoDist = self::calculateHaversineDistance(
                (float) $odp->latitude,
                (float) $odp->longitude,
                (float) $customer->latitude,
                (float) $customer->longitude
            );
            if ($geoDist && $geoDist > 2) {
                $dropDistMeters = round($geoDist * 1.15);
            }
        }
        $dropLoss = round(($dropDistMeters / 1000) * self::FIBER_LOSS_PER_KM + (2 * self::CONNECTOR_PAIR_LOSS), 2);

        // 5. Total Estimasi Redaman Pasif & Nilai Teoretis
        $totalEstimatedLoss = round($feederLoss + $parentOdcSplitterLoss + $subFeederLoss + $odcSplitterLoss + $distLoss + $odpSplitterLoss + $dropLoss, 2);
        $theoreticalRx = round($txOlt - $totalEstimatedLoss, 2);

        // 6. Ambil Pengukuran Aktual (Ground Truth)
        $actualRx = null;
        $actualTxOnu = $onu->tx_power_dbm;
        $actualSource = 'olt_snmp';

        if ($forceFresh) {
            try {
                $liveDiag = $this->acsDiagnosticService->getLiveDiagnostics($onu, true);
                if (!empty($liveDiag['optical']['rx_power']) && is_numeric($liveDiag['optical']['rx_power'])) {
                    $actualRx = (float) $liveDiag['optical']['rx_power'];
                    $actualSource = 'genieacs_live';
                    if (!empty($liveDiag['optical']['tx_power'])) {
                        $actualTxOnu = (float) $liveDiag['optical']['tx_power'];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("LinkBudget: GenieACS Live diagnostics failed for ONU {$onu->id}: " . $e->getMessage());
            }
        }

        // Fallback jika tidak force fresh atau ACS gagal
        if ($actualRx === null) {
            if ($onu->rx_power_dbm !== null) {
                $actualRx = (float) $onu->rx_power_dbm;
                $actualSource = 'olt_snmp';
            } elseif ($onu->acsDevice && !empty($onu->acsDevice->rx_power)) {
                $actualRx = (float) $onu->acsDevice->rx_power;
                $actualSource = 'acs_cached';
            } else {
                $actualRx = $theoreticalRx; // fallback jika belum ada data sama sekali
                $actualSource = 'estimated_baseline';
            }
        }

        // 7. Hitung Deviasi (Delta)
        $deviation = round($actualRx - $theoreticalRx, 2);

        // Jarak Kumulatif GIS vs OLT RTT
        $totalGisDistMeters = $feederDistMeters + $subFeederDistMeters + $distDistMeters + $dropDistMeters;
        $rttDistMeters = $onu->distance_meters;
        $distanceDeltaMeters = $rttDistMeters ? ($rttDistMeters - $totalGisDistMeters) : null;

        // 8. Korelasi dengan Neighbor ONUs untuk Root Cause Pinpointing
        $diagnosis = $this->diagnoseFaultLocation(
            onu: $onu,
            odp: $odp,
            odc: $odc,
            actualRx: $actualRx,
            deviation: $deviation,
            distanceDeltaMeters: $distanceDeltaMeters
        );

        $segments = [
            [
                'key' => 'olt_tx',
                'name' => 'OLT Transceiver SFP',
                'node_type' => 'active_source',
                'value' => $txOlt,
                'unit' => 'dBm',
                'loss_db' => 0.0,
                'details' => "Port {$ponPort?->name} ({$sfpClass})",
                'device_name' => $olt?->name,
            ],
            [
                'key' => 'feeder',
                'name' => $parentOdc ? 'Kabel Feeder (OLT ➔ Induk ODC)' : 'Kabel Feeder (OLT ➔ ODC)',
                'node_type' => 'cable',
                'distance_meters' => $feederDistMeters,
                'loss_db' => $feederLoss,
                'unit' => 'dB',
                'details' => "{$feederDistMeters}m @ 0.25dB/km + konektor patch panel",
                'device_name' => $parentOdc ? "Kabel Feeder {$parentOdc->name}" : "Kabel Feeder {$odc?->name}",
            ],
        ];

        if ($parentOdc) {
            $segments[] = [
                'key' => 'parent_odc_splitter',
                'name' => "Splitter Induk ODC Tier-1 ({$parentOdcSplitterRatio})",
                'node_type' => 'splitter',
                'ratio' => $parentOdcSplitterRatio,
                'loss_db' => $parentOdcSplitterLoss,
                'unit' => 'dB',
                'details' => "Induk ODC: {$parentOdc->name} ({$parentOdc->code})",
                'device_name' => $parentOdc->name,
            ];
            $segments[] = [
                'key' => 'sub_feeder',
                'name' => 'Kabel Sub-Feeder (Induk ODC ➔ Sub-ODC)',
                'node_type' => 'cable',
                'distance_meters' => $subFeederDistMeters,
                'loss_db' => $subFeederLoss,
                'unit' => 'dB',
                'details' => "{$subFeederDistMeters}m @ 0.25dB/km + adapter",
                'device_name' => "Kabel {$parentOdc->name} ➔ {$odc?->name}",
            ];
            $segments[] = [
                'key' => 'odc_splitter',
                'name' => "Splitter Sub-ODC Tier-2 ({$odcSplitterRatio})",
                'node_type' => 'splitter',
                'ratio' => $odcSplitterRatio,
                'loss_db' => $odcSplitterLoss,
                'unit' => 'dB',
                'details' => "Sub-ODC: {$odc?->name} ({$odc?->code})",
                'device_name' => $odc?->name,
            ];
        } else {
            $segments[] = [
                'key' => 'odc_splitter',
                'name' => "Splitter ODC Tier-1 ({$odcSplitterRatio})",
                'node_type' => 'splitter',
                'ratio' => $odcSplitterRatio,
                'loss_db' => $odcSplitterLoss,
                'unit' => 'dB',
                'details' => "ODC: {$odc?->name} ({$odc?->code})",
                'device_name' => $odc?->name,
            ];
        }

        $segments[] = [
            'key' => 'distribution',
            'name' => $parentOdc ? 'Kabel Distribusi (Sub-ODC ➔ ODP)' : 'Kabel Distribusi (ODC ➔ ODP)',
            'node_type' => 'cable',
            'distance_meters' => $distDistMeters,
            'loss_db' => $distLoss,
            'unit' => 'dB',
            'details' => "{$distDistMeters}m @ 0.25dB/km + adapter input ODP",
            'device_name' => "Kabel Distribusi ke {$odp?->name}",
        ];
        $segments[] = [
            'key' => 'odp_splitter',
            'name' => $parentOdc ? "Splitter ODP Tier-3 ({$odpSplitterRatio})" : "Splitter ODP Tier-2 ({$odpSplitterRatio})",
            'node_type' => 'splitter',
            'ratio' => $odpSplitterRatio,
            'loss_db' => $odpSplitterLoss,
            'unit' => 'dB',
            'details' => "ODP: {$odp?->name} ({$odp?->code})",
            'device_name' => $odp?->name,
        ];
        $segments[] = [
            'key' => 'drop_cable',
            'name' => 'Kabel Drop & Roset (ODP ➔ Rumah)',
            'node_type' => 'drop',
            'distance_meters' => $dropDistMeters,
            'loss_db' => $dropLoss,
            'unit' => 'dB',
            'details' => "{$dropDistMeters}m + 2x Fast Connector SC/UPC",
            'device_name' => 'Drop Core Rumah Pelanggan',
        ];

        return [
            'success' => true,
            'customer' => $customer ? [
                'id' => $customer->id,
                'code' => $customer->code,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'address' => $customer->address,
            ] : null,
            'onu' => [
                'id' => $onu->id,
                'code' => $onu->code,
                'serial_number' => $onu->serial_number,
                'model' => $onu->model,
                'status' => $onu->status,
                'operational_status' => $onu->operational_status,
                'rtt_distance_meters' => $rttDistMeters,
            ],
            'olt' => [
                'id' => $olt?->id,
                'name' => $olt?->name,
                'code' => $olt?->code,
                'pon_port_name' => $ponPort?->name ?: "Port {$onu->pon_port}",
                'sfp_class' => $sfpClass,
                'tx_power_dbm' => $txOlt,
            ],
            'odc' => $odc ? [
                'id' => $odc->id,
                'name' => $odc->name,
                'code' => $odc->code,
            ] : null,
            'parent_odc' => $parentOdc ? [
                'id' => $parentOdc->id,
                'name' => $parentOdc->name,
                'code' => $parentOdc->code,
            ] : null,
            'odp' => $odp ? [
                'id' => $odp->id,
                'name' => $odp->name,
                'code' => $odp->code,
            ] : null,
            'segments' => $segments,
            'metrics' => [
                'total_estimated_loss_db' => $totalEstimatedLoss,
                'theoretical_rx_dbm' => $theoreticalRx,
                'actual_rx_dbm' => $actualRx,
                'actual_tx_dbm' => $actualTxOnu,
                'deviation_db' => $deviation,
                'total_gis_distance_meters' => $totalGisDistMeters,
                'rtt_distance_meters' => $rttDistMeters,
                'distance_delta_meters' => $distanceDeltaMeters,
                'actual_source' => $actualSource,
            ],
            'diagnosis' => $diagnosis,
        ];
    }

    /**
     * Mendiagnosis kemungkinan titik gangguan berdasarkan deviasi dan korelasi tetangga
     */
    protected function diagnoseFaultLocation(
        Onu $onu,
        ?Odp $odp,
        ?Odc $odc,
        float $actualRx,
        float $deviation,
        ?int $distanceDeltaMeters
    ): array {
        // Kasus 1: Kabel Putus / Loss of Signal (LOS)
        if ($actualRx <= -38.0 || $onu->status === 'inactive' && $actualRx <= -35.0) {
            return [
                'status' => 'los',
                'badge_color' => 'rose',
                'title' => '🚨 LOSS OF SIGNAL (FIBER CUT / DISCONNECTED)',
                'fault_segment' => 'drop_cable',
                'confidence' => 'high',
                'recommendation' => 'Koneksi optik terputus total (LOS). Periksa apakah kabel drop putus, konektor terlepas dari roset/ONU, atau ada pemadaman adaptor listrik ONT.',
                'neighbor_stats' => ['odp_degraded_pct' => 0],
            ];
        }

        // Kasus 2: Deviasi Normal (|Delta| <= 1.8 dB dan Rx >= -24.0 dBm)
        if (abs($deviation) <= 1.8 && $actualRx >= -24.0) {
            return [
                'status' => 'ideal',
                'badge_color' => 'emerald',
                'title' => '✅ OPTIK SEHAT (NORMAL & IDEAL)',
                'fault_segment' => 'none',
                'confidence' => 'high',
                'recommendation' => 'Redaman dan daya optik berada dalam batas toleransi standar pabrikasi dan instalasi FTTH. Tidak diperlukan tindakan.',
                'neighbor_stats' => ['odp_degraded_pct' => 0],
            ];
        }

        // Kasus 3: Deviasi Ringan (-2.5 dB <= Delta < -1.8 dB atau Rx antara -24.0 s.d -27.0 dBm)
        if ($deviation >= -2.5 && $actualRx >= -27.0) {
            return [
                'status' => 'marginal',
                'badge_color' => 'amber',
                'title' => '⚠️ PERINGATAN: REDAMAN MARGINAL (TOLERANSI BATAS)',
                'fault_segment' => 'drop_cable',
                'confidence' => 'medium',
                'recommendation' => 'Daya optik masih dalam batas kerja ONT namun mendekati batas sensitivitas receiver (-27 dBm). Disarankan membersihkan konektor SC/UPC pada kunjungan teknisi berikutnya.',
                'neighbor_stats' => ['odp_degraded_pct' => 0],
            ];
        }

        // KASUS GANGGUAN SERIUS (Deviation < -2.5 dB atau Rx < -27.0 dBm):
        // Periksa korelasi tetangga (Tree Correlation)
        $neighborOnus = collect();
        if ($odp) {
            $neighborOnus = Onu::withoutGlobalScopes()
                ->where('odp_id', $odp->id)
                ->where('id', '!=', $onu->id)
                ->whereNotNull('rx_power_dbm')
                ->get();
        }

        $odpTotalNeighbors = $neighborOnus->count();
        $odpDegradedNeighbors = $neighborOnus->filter(fn($n) => (float)$n->rx_power_dbm <= -26.0)->count();
        $odpDegradedRatio = $odpTotalNeighbors > 0 ? ($odpDegradedNeighbors / $odpTotalNeighbors) : 0;

        // Cek jika SELURUH ODP di ODC ini juga bermasalah
        $odcOdpsDegradedRatio = 0;
        if ($odc && $odpDegradedRatio >= 0.6) {
            $otherOdps = Odp::withoutGlobalScopes()
                ->where('odc_id', $odc->id)
                ->where('id', '!=', $odp->id)
                ->with('onus')
                ->get();

            $totalOtherOnus = 0;
            $degradedOtherOnus = 0;
            foreach ($otherOdps as $o) {
                foreach ($o->onus as $n) {
                    if ($n->rx_power_dbm !== null) {
                        $totalOtherOnus++;
                        if ((float)$n->rx_power_dbm <= -26.0) {
                            $degradedOtherOnus++;
                        }
                    }
                }
            }
            $odcOdpsDegradedRatio = $totalOtherOnus > 0 ? ($degradedOtherOnus / $totalOtherOnus) : 0;
        }

        // Deteksi Segmen Gangguan:

        // A. Jika ODC massal degraded (>50% pelanggan di ODC bermasalah)
        if ($odcOdpsDegradedRatio >= 0.5) {
            return [
                'status' => 'critical',
                'badge_color' => 'rose',
                'title' => '🚨 GANGGUAN MASSAL: JALUR FEEDER UTAMA / SFP OLT',
                'fault_segment' => 'feeder_cable',
                'confidence' => 'very_high',
                'recommendation' => "Hampir semua ODP di bawah ODC {$odc?->name} mengalami redaman tinggi. Titik masalah berada di Kabel Feeder Utama (OLT ke ODC) atau modul optik SFP OLT.",
                'neighbor_stats' => [
                    'odp_degraded_pct' => round($odpDegradedRatio * 100),
                    'odc_degraded_pct' => round($odcOdpsDegradedRatio * 100),
                ],
            ];
        }

        // B. Jika seluruh pelanggan di ODP ini degraded (tapi ODP lain normal)
        if ($odpDegradedRatio >= 0.6 && $odpTotalNeighbors >= 2) {
            return [
                'status' => 'critical',
                'badge_color' => 'rose',
                'title' => "⚠️ GANGGUAN DISTRIBUSI: KABEL DISTRIBUSI / SPLITTER {$odp?->name}",
                'fault_segment' => 'distribution_cable',
                'confidence' => 'high',
                'recommendation' => "{$odpDegradedNeighbors} dari {$odpTotalNeighbors} pelanggan di {$odp?->name} mengalami redaman tinggi. Masalah terlokalisasi pada kabel distribusi ODC ➔ ODP atau input splitter ODP kendor/rusak.",
                'neighbor_stats' => [
                    'odp_degraded_pct' => round($odpDegradedRatio * 100),
                ],
            ];
        }

        // C. Jika HANYA pelanggan ini yang degraded (tetangga di ODP normal)
        return [
            'status' => 'critical',
            'badge_color' => 'amber',
            'title' => '⚠️ GANGGUAN LOKAL: KABEL DROP / FAST CONNECTOR PELANGGAN',
            'fault_segment' => 'drop_cable',
            'confidence' => 'high',
            'recommendation' => "Pelanggan lain di {$odp?->name} memiliki sinyal normal. Gangguan terlokalisasi di kabel drop pelanggan, Fast Connector di port ODP, atau patchcord/roset rumah pelanggan yang tertekuk (macrobending).",
            'neighbor_stats' => [
                'odp_degraded_pct' => round($odpDegradedRatio * 100),
            ],
        ];
    }

    /**
     * Hitung jarak Haversine antar koordinat GPS (dalam meter)
     */
    public static function calculateHaversineDistance(?float $lat1, ?float $lon1, ?float $lat2, ?float $lon2): ?float
    {
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) {
            return null;
        }

        // Validasi rentang koordinat bumi
        if ($lat1 < -90 || $lat1 > 90 || $lat2 < -90 || $lat2 > 90) return null;
        if ($lon1 < -180 || $lon1 > 180 || $lon2 < -180 || $lon2 > 180) return null;

        $earthRadius = 6371000; // Radius bumi dalam meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 1);
    }
}
