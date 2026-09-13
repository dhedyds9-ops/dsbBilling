<?php

namespace App\Services\ISP;

use App\Models\ISP\NetworkIncident;
use App\Services\Telegram\TelegramService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RootCauseAnalysisEngine
{
    public function __construct(protected TelegramService $telegram)
    {
    }

    public function runAnalysis(): void
    {
        $this->analyzePonPorts();
        $this->analyzeOdpGroups();
        $this->analyzeNasRouters();
        $this->analyzeAcsMassDegradation();
    }

    // ------------------------------------------------------------------
    // 1. PON Port Down Detection
    //    Trigger: >80% ONU on the same PON port are inactive
    // ------------------------------------------------------------------
    protected function analyzePonPorts(): void
    {
        $ponStats = DB::table('onus')
            ->select(
                'olt_id',
                'pon_port',
                DB::raw('count(*) as total_onus'),
                DB::raw('sum(case when status = "inactive" then 1 else 0 end) as offline_onus')
            )
            ->groupBy('olt_id', 'pon_port')
            ->havingRaw('total_onus > 5')
            ->get();

        foreach ($ponStats as $stat) {
            $offlineRatio = $stat->offline_onus / $stat->total_onus;
            $referenceId  = "OLT-{$stat->olt_id}-PON-{$stat->pon_port}";

            if ($offlineRatio >= 0.8) {
                $existing = NetworkIncident::where('type', 'pon_down')
                    ->where('reference_id', $referenceId)
                    ->where('status', 'active')
                    ->first();

                if (!$existing) {
                    $description = "🚨 MASS OUTAGE: OLT-{$stat->olt_id} PORT PON {$stat->pon_port} DOWN. {$stat->offline_onus}/{$stat->total_onus} Pelanggan Terdampak.";
                    NetworkIncident::create([
                        'type'                     => 'pon_down',
                        'reference_id'             => $referenceId,
                        'impacted_customers_count' => $stat->offline_onus,
                        'status'                   => 'active',
                        'description'              => $description,
                        'started_at'               => now(),
                    ]);

                    Log::error("RCA: [PON_DOWN] {$referenceId}");
                    $this->telegram->sendAlarmNotification($referenceId, 'MASS_OUTAGE', $description);
                } else {
                    // Update impacted count if changed
                    if ($existing->impacted_customers_count !== (int)$stat->offline_onus) {
                        $existing->update(['impacted_customers_count' => $stat->offline_onus]);
                    }
                }
            } else {
                $this->resolveIncident('pon_down', $referenceId);
            }
        }
    }

    // ------------------------------------------------------------------
    // 2. ODP Group Down Detection
    //    Trigger: >80% customer services under the same ODP are FIBER_LOSS
    // ------------------------------------------------------------------
    protected function analyzeOdpGroups(): void
    {
        // Join customer_services -> onus -> odp_id to detect ODP-level failures
        $odpStats = DB::table('customer_services')
            ->join('onus', 'customer_services.onu_id', '=', 'onus.id')
            ->whereNotNull('onus.odp_id')
            ->select(
                'onus.odp_id',
                DB::raw('count(*) as total'),
                DB::raw('sum(case when customer_services.optical_status in ("offline","los") then 1 else 0 end) as fiber_loss')
            )
            ->groupBy('onus.odp_id')
            ->havingRaw('total >= 3')
            ->get();

        foreach ($odpStats as $stat) {
            $ratio       = $stat->fiber_loss / $stat->total;
            $referenceId = "ODP-{$stat->odp_id}";

            if ($ratio >= 0.8) {
                $existing = NetworkIncident::where('type', 'odp_down')
                    ->where('reference_id', $referenceId)
                    ->where('status', 'active')
                    ->first();

                if (!$existing) {
                    $description = "🚨 ODP DOWN: ODP-{$stat->odp_id} — {$stat->fiber_loss}/{$stat->total} Pelanggan FIBER_LOSS.";
                    NetworkIncident::create([
                        'type'                     => 'odp_down',
                        'reference_id'             => $referenceId,
                        'impacted_customers_count' => $stat->fiber_loss,
                        'status'                   => 'active',
                        'description'              => $description,
                        'started_at'               => now(),
                    ]);

                    Log::error("RCA: [ODP_DOWN] {$referenceId}");
                    $this->telegram->sendAlarmNotification($referenceId, 'ODP_DOWN', $description);
                }
            } else {
                $this->resolveIncident('odp_down', $referenceId);
            }
        }
    }

    // ------------------------------------------------------------------
    // 3. NAS/Router Down Detection
    //    Trigger: >80% sessions under same router dropped while optical is fine
    // ------------------------------------------------------------------
    protected function analyzeNasRouters(): void
    {
        // Find routers that recently lost most of their sessions
        $routerStats = DB::table('customer_services')
            ->join('onus', 'customer_services.onu_id', '=', 'onus.id')
            ->join('pppoe_users', 'customer_services.id', '=', 'pppoe_users.customer_service_id')
            ->select(
                'pppoe_users.router_id',
                DB::raw('count(*) as total'),
                DB::raw('sum(case when customer_services.service_status = "offline" AND customer_services.optical_status = "online" then 1 else 0 end) as router_offline')
            )
            ->groupBy('pppoe_users.router_id')
            ->havingRaw('total >= 5')
            ->get();

        foreach ($routerStats as $stat) {
            $ratio       = $stat->router_offline / $stat->total;
            $referenceId = "ROUTER-{$stat->router_id}";

            if ($ratio >= 0.8) {
                $existing = NetworkIncident::where('type', 'router_down')
                    ->where('reference_id', $referenceId)
                    ->where('status', 'active')
                    ->first();

                if (!$existing) {
                    $description = "🚨 ROUTER/NAS DOWN: Router-{$stat->router_id} — {$stat->router_offline}/{$stat->total} sesi offline (optik normal).";
                    NetworkIncident::create([
                        'type'                     => 'router_down',
                        'reference_id'             => $referenceId,
                        'impacted_customers_count' => $stat->router_offline,
                        'status'                   => 'active',
                        'description'              => $description,
                        'started_at'               => now(),
                    ]);

                    Log::error("RCA: [ROUTER_DOWN] {$referenceId}");
                    $this->telegram->sendAlarmNotification($referenceId, 'NAS_DOWN', $description);
                }
            } else {
                $this->resolveIncident('router_down', $referenceId);
            }
        }
    }

    // ------------------------------------------------------------------
    // 4. ACS Mass Degradation Detection
    //    Trigger: Many devices optical+service online but tr069 offline/stale
    // ------------------------------------------------------------------
    protected function analyzeAcsMassDegradation(): void
    {
        $total = DB::table('customer_services')
            ->where('optical_status', 'online')
            ->where('service_status', 'online')
            ->count();

        if ($total < 10) return;

        $stale = DB::table('customer_services')
            ->where('optical_status', 'online')
            ->where('service_status', 'online')
            ->whereIn('tr069_status', ['offline', 'stale'])
            ->count();

        $ratio = $total > 0 ? $stale / $total : 0;

        if ($ratio >= 0.5) {
            $existing = NetworkIncident::where('type', 'acs_degraded')
                ->where('status', 'active')
                ->first();

            if (!$existing) {
                $description = "⚠️ ACS MASS DEGRADATION: {$stale}/{$total} perangkat online tapi TR-069 tidak merespons. Kemungkinan koneksi ke GenieACS (port 7547) bermasalah.";
                NetworkIncident::create([
                    'type'                     => 'acs_degraded',
                    'reference_id'             => 'GENIEACS-GLOBAL',
                    'impacted_customers_count' => $stale,
                    'status'                   => 'active',
                    'description'              => $description,
                    'started_at'               => now(),
                ]);

                Log::error("RCA: [ACS_DEGRADED]");
                $this->telegram->sendAlarmNotification('GENIEACS', 'ACS_DEGRADED', $description);
            }
        } else {
            $this->resolveIncident('acs_degraded', 'GENIEACS-GLOBAL');
        }
    }

    // ------------------------------------------------------------------
    // Helper: Resolve an active incident
    // ------------------------------------------------------------------
    protected function resolveIncident(string $type, string $referenceId): void
    {
        NetworkIncident::where('type', $type)
            ->where('reference_id', $referenceId)
            ->where('status', 'active')
            ->update([
                'status'      => 'resolved',
                'resolved_at' => now(),
            ]);
    }
}
