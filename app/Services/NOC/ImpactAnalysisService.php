<?php

namespace App\Services\NOC;

use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\ISP\PonPort;
use App\Models\ISP\Router;
use App\Models\ISP\Pop;
use App\Models\Customer\CustomerService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ImpactAnalysisService
{
    const CACHE_TTL = 60;

    /**
     * Calculate impact of an OLT going offline.
     * Returns affected PON, ONU, CustomerService counts + breakdown by type.
     */
    public function analyzeOltImpact(int $oltId): array
    {
        return Cache::remember("noc.impact.olt.{$oltId}", self::CACHE_TTL, function () use ($oltId) {
            $olt = Olt::withCount([
                'ponPorts',
                'onus',
                'onus as active_onu_count' => fn ($q) => $q->where('status', '!=', 'inactive'),
            ])->find($oltId);

            if (!$olt) {
                return $this->emptyImpact();
            }

            // Get ONU IDs on this OLT
            $onuIds = Onu::where('olt_id', $oltId)->pluck('id');

            return $this->buildImpactFromOnuIds($onuIds, [
                'pon_count'  => $olt->pon_ports_count ?? 0,
                'onu_count'  => $olt->onus_count ?? 0,
                'device'     => $olt->name,
                'device_type' => 'OLT',
            ]);
        });
    }

    /**
     * Calculate impact of a PON port going down.
     */
    public function analyzePonImpact(int $ponPortId): array
    {
        return Cache::remember("noc.impact.pon.{$ponPortId}", self::CACHE_TTL, function () use ($ponPortId) {
            $ponPort = PonPort::with('olt:id,name')->find($ponPortId);
            if (!$ponPort) {
                return $this->emptyImpact();
            }

            $onuIds = Onu::where('pon_port_id', $ponPortId)->pluck('id');

            return $this->buildImpactFromOnuIds($onuIds, [
                'pon_count'   => 1,
                'onu_count'   => $onuIds->count(),
                'device'      => ($ponPort->olt->name ?? 'OLT') . ' PON ' . $ponPort->name,
                'device_type' => 'PON',
            ]);
        });
    }

    /**
     * Calculate impact of a Router going offline.
     */
    public function analyzeRouterImpact(int $routerId): array
    {
        return Cache::remember("noc.impact.router.{$routerId}", self::CACHE_TTL, function () use ($routerId) {
            $router = Router::select('id', 'name')->find($routerId);
            if (!$router) {
                return $this->emptyImpact();
            }

            // Count services routed through this router via networkProfile relation
            $baseQuery = CustomerService::whereHas('networkProfile', function ($q) use ($routerId) {
                $q->where('router_id', $routerId);
            })->where('customer_services.status', 'active');

            $serviceCounts = (clone $baseQuery)
                ->join('service_profiles', 'customer_services.service_profile_id', '=', 'service_profiles.id')
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN service_profiles.service_type = 'pppoe' THEN 1 ELSE 0 END) as pppoe,
                    SUM(CASE WHEN service_profiles.service_type = 'hotspot' THEN 1 ELSE 0 END) as hotspot,
                    SUM(CASE WHEN service_profiles.service_type NOT IN ('pppoe','hotspot') THEN 1 ELSE 0 END) as other
                ")
                ->first();

            $customerCount = (clone $baseQuery)
                ->distinct('customer_services.customer_id')
                ->count('customer_services.customer_id');

            return [
                'device'          => $router->name,
                'device_type'     => 'Router',
                'pon_count'       => 0,
                'onu_count'       => 0,
                'customer_count'  => $customerCount,
                'service_count'   => $serviceCounts->total ?? 0,
                'breakdown'       => [
                    'pppoe'   => (int) ($serviceCounts->pppoe ?? 0),
                    'hotspot' => (int) ($serviceCounts->hotspot ?? 0),
                    'other'   => (int) ($serviceCounts->other ?? 0),
                ],
            ];
        });
    }

    /**
     * Get list of affected customers for a given OLT.
     * Paginated — never load all at once.
     */
    public function getAffectedCustomers(string $deviceType, int $deviceId, int $perPage = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = CustomerService::with(['customer:id,name,code', 'onu:id,serial_number,status'])
            ->where('status', 'active');

        if ($deviceType === 'olt') {
            $onuIds = Onu::where('olt_id', $deviceId)->pluck('id');
            $query->whereIn('onu_id', $onuIds);
        } elseif ($deviceType === 'pon') {
            $onuIds = Onu::where('pon_port_id', $deviceId)->pluck('id');
            $query->whereIn('onu_id', $onuIds);
        } elseif ($deviceType === 'router') {
            $query->whereHas('networkProfile', function ($q) use ($deviceId) {
                $q->where('router_id', $deviceId);
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Clear impact cache when device status changes.
     */
    public function clearCache(string $deviceType, int $deviceId): void
    {
        Cache::forget("noc.impact.{$deviceType}.{$deviceId}");
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function buildImpactFromOnuIds(Collection $onuIds, array $base): array
    {
        if ($onuIds->isEmpty()) {
            return array_merge($this->emptyImpact(), $base);
        }

        $serviceCounts = CustomerService::whereIn('customer_services.onu_id', $onuIds)
            ->join('service_profiles', 'customer_services.service_profile_id', '=', 'service_profiles.id')
            ->where('customer_services.status', 'active')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN service_profiles.service_type = 'pppoe' THEN 1 ELSE 0 END) as pppoe,
                SUM(CASE WHEN service_profiles.service_type = 'hotspot' THEN 1 ELSE 0 END) as hotspot,
                SUM(CASE WHEN service_profiles.service_type NOT IN ('pppoe','hotspot') THEN 1 ELSE 0 END) as other
            ")
            ->first();

        $customerCount = CustomerService::whereIn('onu_id', $onuIds)
            ->where('status', 'active')
            ->distinct('customer_id')
            ->count('customer_id');

        return array_merge($base, [
            'customer_count' => $customerCount,
            'service_count'  => $serviceCounts->total ?? 0,
            'breakdown'      => [
                'pppoe'   => (int) ($serviceCounts->pppoe ?? 0),
                'hotspot' => (int) ($serviceCounts->hotspot ?? 0),
                'other'   => (int) ($serviceCounts->other ?? 0),
            ],
        ]);
    }

    private function emptyImpact(): array
    {
        return [
            'device'         => '',
            'device_type'    => '',
            'pon_count'      => 0,
            'onu_count'      => 0,
            'customer_count' => 0,
            'service_count'  => 0,
            'breakdown'      => ['pppoe' => 0, 'hotspot' => 0, 'other' => 0],
        ];
    }
}
