<?php

namespace App\Services\NOC;

use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\ISP\PonPort;
use App\Models\ISP\Router;
use App\Models\ISP\Pop;
use App\Models\Alarm;
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
                'pon_count'   => $olt->pon_ports_count ?? 0,
                'onu_count'   => $olt->onus_count ?? 0,
                'device'      => $olt->name,
                'device_type' => 'OLT',
                'ip_address'  => $olt->ip_address,
                'status'      => $olt->status,
                'model'       => $olt->model,
            ]);
        });
    }

    /**
     * Calculate impact of a PON port going down.
     */
    public function analyzePonImpact(int $ponPortId): array
    {
        return Cache::remember("noc.impact.pon.{$ponPortId}", self::CACHE_TTL, function () use ($ponPortId) {
            $ponPort = PonPort::with('olt:id,name,ip_address,status')->find($ponPortId);
            if (!$ponPort) {
                return $this->emptyImpact();
            }

            $onuIds = Onu::where('pon_port_id', $ponPortId)->pluck('id');

            return $this->buildImpactFromOnuIds($onuIds, [
                'pon_count'   => 1,
                'onu_count'   => $onuIds->count(),
                'device'      => ($ponPort->olt->name ?? 'OLT') . ' PON ' . $ponPort->port_number . ' (' . $ponPort->name . ')',
                'device_type' => 'PON',
                'ip_address'  => $ponPort->olt->ip_address ?? '—',
                'status'      => 'active',
                'model'       => 'GPON SFP Port',
            ]);
        });
    }

    /**
     * Calculate impact of a Router going offline.
     */
    public function analyzeRouterImpact(int $routerId): array
    {
        return Cache::remember("noc.impact.router.{$routerId}", self::CACHE_TTL, function () use ($routerId) {
            $router = Router::select('id', 'name', 'code', 'ip_address', 'status', 'model', 'pop_id')->find($routerId);
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

            // Find downstream OLTs (same POP or connected via network profiles / ONUs)
            $downstreamOlts = Olt::where('pop_id', $router->pop_id)->count();

            return [
                'device'                   => $router->name,
                'device_type'              => 'Router',
                'ip_address'               => $router->ip_address ?? '—',
                'status'                   => $router->status,
                'model'                    => $router->model ?? 'MikroTik Router',
                'pon_count'                => 0,
                'onu_count'                => 0,
                'customer_count'           => $customerCount,
                'service_count'            => $serviceCounts->total ?? 0,
                'downstream_devices_count' => $downstreamOlts,
                'network_links_count'      => $downstreamOlts + 1,
                'breakdown'                => [
                    'pppoe'   => (int) ($serviceCounts->pppoe ?? 0),
                    'hotspot' => (int) ($serviceCounts->hotspot ?? 0),
                    'other'   => (int) ($serviceCounts->other ?? 0),
                ],
            ];
        });
    }

    /**
     * Get list of affected customers for a given device.
     * Includes search and service filtering.
     */
    public function getAffectedCustomers(
        string $deviceType,
        int $deviceId,
        int $perPage = 20,
        ?string $search = null,
        ?string $serviceType = null
    ): \Illuminate\Pagination\LengthAwarePaginator {
        $query = CustomerService::with([
            'customer:id,name,code',
            'onu:id,serial_number,status,olt_id,pon_port_id,name',
            'onu.olt:id,name',
            'onu.ponPort:id,name,port_number',
            'serviceProfile:id,name,service_type',
            'networkProfile:id,name,router_id',
            'networkProfile.router:id,name',
        ])->where('status', 'active');

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

        // Search filter: customer name, customer code, or ONU serial number
        if (!empty($search)) {
            $search = trim($search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%")
                       ->orWhere('code', 'like', "%{$search}%");
                })->orWhereHas('onu', function ($oq) use ($search) {
                    $oq->where('serial_number', 'like', "%{$search}%")
                       ->orWhere('name', 'like', "%{$search}%");
                })->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Service type filter
        if (!empty($serviceType) && $serviceType !== 'all') {
            $query->whereHas('serviceProfile', function ($sq) use ($serviceType) {
                $sq->where('service_type', $serviceType);
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Build topology nodes, edges, and Root Cause Analysis for interactive canvas.
     */
    public function getTopologyData(string $deviceType, int $deviceId): array
    {
        if (!$deviceId) {
            return [
                'nodes' => [],
                'edges' => [],
                'rca'   => null,
            ];
        }

        return Cache::remember("noc.topology.v2.{$deviceType}.{$deviceId}", self::CACHE_TTL, function () use ($deviceType, $deviceId) {
            if ($deviceType === 'olt') {
                return $this->buildOltTopology($deviceId);
            } elseif ($deviceType === 'pon') {
                return $this->buildPonTopology($deviceId);
            } elseif ($deviceType === 'router') {
                return $this->buildRouterTopology($deviceId);
            }

            return ['nodes' => [], 'edges' => [], 'rca' => null];
        });
    }

    /**
     * Clear impact cache when device status changes.
     */
    public function clearCache(string $deviceType, int $deviceId): void
    {
        Cache::forget("noc.impact.{$deviceType}.{$deviceId}");
        Cache::forget("noc.topology.v2.{$deviceType}.{$deviceId}");
    }

    // -------------------------------------------------------------------------
    // Private topology builders
    // -------------------------------------------------------------------------

    private function buildOltTopology(int $oltId): array
    {
        $olt = Olt::with(['ponPorts', 'pop', 'vendor'])->find($oltId);
        if (!$olt) {
            return ['nodes' => [], 'edges' => [], 'rca' => null];
        }

        $nodes = [];
        $edges = [];

        $isOnline = $olt->status === 'active' && $olt->last_polled_at && $olt->last_polled_at >= now()->subMinutes(15);
        $oltStatus = $isOnline ? 'online' : ($olt->status === 'active' ? 'warning' : 'critical');
        $oltColors = $this->getStatusColor($oltStatus);

        $onuIds = Onu::where('olt_id', $oltId)->pluck('id');
        $customerCount = CustomerService::whereIn('onu_id', $onuIds)->where('status', 'active')->count();

        // 1. Upstream Router (if POP has router)
        $parentRouter = $olt->pop_id ? Router::where('pop_id', $olt->pop_id)->first() : null;
        if ($parentRouter) {
            $rOnline = in_array($parentRouter->status, ['online', 'active']);
            $rStatus = $rOnline ? 'online' : 'critical';
            $rColors = $this->getStatusColor($rStatus);

            $nodes[] = [
                'id'                 => 'router_' . $parentRouter->id,
                'label'              => "Router: {$parentRouter->name}\n" . ($parentRouter->ip_address ?: 'Gateway'),
                'type'               => 'Router (Core/Agg)',
                'name'               => $parentRouter->name,
                'code'               => $parentRouter->code ?? '-',
                'ip'                 => $parentRouter->ip_address ?? '—',
                'model'              => $parentRouter->model ?? 'MikroTik Router',
                'vendor'             => 'MikroTik',
                'status'             => $rStatus,
                'status_label'       => ucfirst($rStatus),
                'level'              => 0,
                'shape'              => 'box',
                'color'              => $rColors,
                'font'               => ['color' => '#f8fafc', 'face' => 'JetBrains Mono, monospace', 'size' => 14, 'bold' => true],
                'parent'             => 'Upstream / Internet Gateway',
                'alarms_count'       => 0,
                'children_count'     => 1,
                'affected_customers' => 0,
                'is_direct_fault'    => false,
            ];

            $edges[] = [
                'from'   => 'router_' . $parentRouter->id,
                'to'     => 'olt_' . $olt->id,
                'arrows' => 'to',
                'color'  => ['color' => '#00e5ff', 'highlight' => '#38bdf8'],
                'width'  => 2,
            ];
        }

        // 2. Selected OLT (Direct Target)
        $oltAlarms = Alarm::where('status', 'open')->where(function ($q) use ($olt) {
            $q->where('source_type', 'olt')->where('source_id', $olt->id)
              ->orWhere('source_name', $olt->name);
        })->get();

        $nodes[] = [
            'id'                 => 'olt_' . $olt->id,
            'label'              => "★ OLT: {$olt->name}\nIP: " . ($olt->ip_address ?: 'No IP'),
            'type'               => 'OLT (Selected Root)',
            'name'               => $olt->name,
            'code'               => $olt->code ?? '-',
            'ip'                 => $olt->ip_address ?? '—',
            'model'              => $olt->model ?? 'Chassis OLT',
            'vendor'             => $olt->vendor->name ?? 'ZTE / Huawei / V-SOL',
            'status'             => $oltStatus,
            'status_label'       => ucfirst($oltStatus),
            'level'              => 1,
            'shape'              => 'box',
            'color'              => $oltColors,
            'font'               => ['color' => '#f8fafc', 'face' => 'JetBrains Mono, monospace', 'size' => 15, 'bold' => true],
            'parent'             => $parentRouter ? "Router {$parentRouter->name}" : ($olt->pop ? "POP {$olt->pop->name}" : 'Core Network'),
            'alarms_count'       => $oltAlarms->count(),
            'children_count'     => $olt->ponPorts->count(),
            'affected_customers' => $customerCount,
            'is_direct_fault'    => true,
        ];

        // 3. PON Ports
        $ponPorts = PonPort::where('olt_id', $olt->id)->orderBy('port_number')->get();
        foreach ($ponPorts as $pon) {
            $ponOnus = Onu::where('pon_port_id', $pon->id)->get();
            $onuTotal = $ponOnus->count();
            $onuOnline = $ponOnus->where('last_seen_at', '>=', now()->subMinutes(5))->count();
            $ponStatus = $onuTotal === 0 ? 'unknown' : ($onuOnline === $onuTotal ? 'online' : ($onuOnline > 0 ? 'warning' : 'critical'));
            $ponColors = $this->getStatusColor($ponStatus);
            $ponCustomerCount = CustomerService::whereIn('onu_id', $ponOnus->pluck('id'))->where('status', 'active')->count();

            $nodes[] = [
                'id'                 => 'pon_' . $pon->id,
                'label'              => "PON {$pon->port_number}\n({$onuOnline}/{$onuTotal} ONUs)",
                'type'               => 'PON Port',
                'name'               => "PON Port {$pon->port_number} ({$pon->name})",
                'code'               => "PON-{$pon->port_number}",
                'ip'                 => '—',
                'model'              => 'GPON SFP+ Class C+',
                'vendor'             => 'Optical Transceiver',
                'status'             => $ponStatus,
                'status_label'       => ucfirst($ponStatus),
                'level'              => 2,
                'shape'              => 'ellipse',
                'color'              => $ponColors,
                'font'               => ['color' => '#f8fafc', 'face' => 'JetBrains Mono, monospace', 'size' => 13, 'bold' => true],
                'parent'             => "OLT {$olt->name}",
                'alarms_count'       => 0,
                'children_count'     => $onuTotal,
                'affected_customers' => $ponCustomerCount,
                'is_direct_fault'    => false,
            ];

            $edges[] = [
                'from'   => 'olt_' . $olt->id,
                'to'     => 'pon_' . $pon->id,
                'arrows' => 'to',
                'color'  => ['color' => $ponStatus === 'critical' ? '#ef4444' : '#00e5ff'],
                'width'  => 2.5,
            ];

            // 4. ONUs under PON
            if ($onuTotal > 0 && $onuTotal <= 6) {
                foreach ($ponOnus as $onu) {
                    $onuOnlineFlag = $onu->last_seen_at && $onu->last_seen_at >= now()->subMinutes(5);
                    $oStatus = $onu->status === 'los' ? 'critical' : ($onuOnlineFlag ? 'online' : 'critical');
                    $oColors = $this->getStatusColor($oStatus);

                    $nodes[] = [
                        'id'                 => 'onu_' . $onu->id,
                        'label'              => "ONU " . substr($onu->serial_number, -6) . "\n" . ($onu->name ?: 'ONT'),
                        'type'               => 'ONU / ONT',
                        'name'               => $onu->name ?: $onu->serial_number,
                        'code'               => $onu->serial_number,
                        'ip'                 => $onu->ip_address ?? '—',
                        'model'              => $onu->model ?? 'ONU Device',
                        'vendor'             => $onu->vendor->name ?? 'ZTE / Fiberhome / Huawei',
                        'status'             => $oStatus,
                        'status_label'       => ucfirst($oStatus),
                        'level'              => 3,
                        'shape'              => 'box',
                        'color'              => $oColors,
                        'font'               => ['color' => '#f8fafc', 'face' => 'JetBrains Mono, monospace', 'size' => 12],
                        'parent'             => "PON {$pon->port_number}",
                        'alarms_count'       => 0,
                        'children_count'     => 1,
                        'affected_customers' => CustomerService::where('onu_id', $onu->id)->where('status', 'active')->count(),
                        'is_direct_fault'    => false,
                    ];

                    $edges[] = [
                        'from'   => 'pon_' . $pon->id,
                        'to'     => 'onu_' . $onu->id,
                        'arrows' => 'to',
                        'color'  => ['color' => $oStatus === 'critical' ? '#ef4444' : '#64748b'],
                        'width'  => 1.5,
                    ];
                }
            } elseif ($onuTotal > 6) {
                // Aggregated ONU cluster node
                $nodes[] = [
                    'id'                 => 'onu_cluster_' . $pon->id,
                    'label'              => "Cluster ONUs\n({$onuOnline}/{$onuTotal} Online)",
                    'type'               => 'ONU Cluster',
                    'name'               => "Cluster {$onuTotal} ONUs (PON {$pon->port_number})",
                    'code'               => "CLUSTER-PON{$pon->port_number}",
                    'ip'                 => '—',
                    'model'              => "Aggregate of {$onuTotal} ONUs",
                    'vendor'             => 'Optical Distribution',
                    'status'             => $ponStatus,
                    'status_label'       => ucfirst($ponStatus),
                    'level'              => 3,
                    'shape'              => 'box',
                    'color'              => $ponColors,
                    'font'               => ['color' => '#f8fafc', 'face' => 'JetBrains Mono, monospace', 'size' => 12, 'bold' => true],
                    'parent'             => "PON {$pon->port_number}",
                    'alarms_count'       => 0,
                    'children_count'     => $onuTotal,
                    'affected_customers' => $ponCustomerCount,
                    'is_direct_fault'    => false,
                ];

                $edges[] = [
                    'from'   => 'pon_' . $pon->id,
                    'to'     => 'onu_cluster_' . $pon->id,
                    'arrows' => 'to',
                    'color'  => ['color' => $ponStatus === 'critical' ? '#ef4444' : '#64748b'],
                    'width'  => 1.5,
                ];
            }
        }

        // RCA data
        $firstAlarm = $oltAlarms->sortByDesc('level')->first();
        $suspectedCause = $firstAlarm
            ? "Alarm: {$firstAlarm->title}"
            : (!$isOnline ? 'OLT Unreachable / Power Outage or Fiber Uplink Loss' : 'Normal Operation / Impact Simulation (No direct alarm detected)');

        $severity = $customerCount > 100 ? 'CRITICAL (P1)' : ($customerCount > 25 ? 'HIGH (P2)' : ($customerCount > 0 ? 'MEDIUM (P3)' : 'LOW (P4)'));

        $rca = [
            'failed_device'      => "OLT {$olt->name} (" . ($olt->ip_address ?: 'No IP') . ")",
            'device_type'        => 'OLT Optical Chassis',
            'suspected_cause'    => $suspectedCause,
            'affected_path'      => "Router → OLT [{$olt->name}] → " . $ponPorts->count() . " PON Ports → " . $onuIds->count() . " ONUs → {$customerCount} Pelanggan Aktif",
            'severity'           => $severity,
            'detection_time'     => $firstAlarm?->started_at ? $firstAlarm->started_at->format('Y-m-d H:i:s') : ($olt->last_polled_at ? $olt->last_polled_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s')),
            'last_status_update' => $olt->updated_at ? $olt->updated_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
            'is_direct_fault'    => true,
            'impact_count'       => $customerCount,
        ];

        return [
            'nodes' => $nodes,
            'edges' => $edges,
            'rca'   => $rca,
        ];
    }

    private function buildPonTopology(int $ponPortId): array
    {
        $pon = PonPort::with(['olt.pop', 'olt.vendor'])->find($ponPortId);
        if (!$pon) {
            return ['nodes' => [], 'edges' => [], 'rca' => null];
        }

        $nodes = [];
        $edges = [];

        // 1. Parent OLT Node
        $olt = $pon->olt;
        $oltOnline = $olt && $olt->status === 'active' && $olt->last_polled_at && $olt->last_polled_at >= now()->subMinutes(15);
        $oltStatus = $oltOnline ? 'online' : ($olt?->status === 'active' ? 'warning' : 'critical');

        if ($olt) {
            $nodes[] = [
                'id'                 => 'olt_' . $olt->id,
                'label'              => "Parent OLT: {$olt->name}\nIP: " . ($olt->ip_address ?: 'No IP'),
                'type'               => 'Parent OLT',
                'name'               => $olt->name,
                'code'               => $olt->code ?? '-',
                'ip'                 => $olt->ip_address ?? '—',
                'model'              => $olt->model ?? 'Chassis OLT',
                'vendor'             => $olt->vendor->name ?? 'ZTE / Huawei',
                'status'             => $oltStatus,
                'status_label'       => ucfirst($oltStatus),
                'level'              => 0,
                'shape'              => 'box',
                'color'              => $this->getStatusColor($oltStatus),
                'font'               => ['color' => '#f8fafc', 'face' => 'JetBrains Mono, monospace', 'size' => 14, 'bold' => true],
                'parent'             => $olt->pop ? "POP {$olt->pop->name}" : 'Core Network',
                'alarms_count'       => 0,
                'children_count'     => 1,
                'affected_customers' => 0,
                'is_direct_fault'    => false,
            ];
        }

        // 2. Selected PON Port (Target)
        $ponOnus = Onu::where('pon_port_id', $pon->id)->get();
        $onuTotal = $ponOnus->count();
        $onuOnline = $ponOnus->where('last_seen_at', '>=', now()->subMinutes(5))->count();
        $ponStatus = $onuTotal === 0 ? 'unknown' : ($onuOnline === $onuTotal ? 'online' : ($onuOnline > 0 ? 'warning' : 'critical'));
        $customerCount = CustomerService::whereIn('onu_id', $ponOnus->pluck('id'))->where('status', 'active')->count();

        $nodes[] = [
            'id'                 => 'pon_' . $pon->id,
            'label'              => "★ PON {$pon->port_number}: {$pon->name}\n({$onuOnline}/{$onuTotal} ONUs Online)",
            'type'               => 'PON Port (Selected)',
            'name'               => "PON {$pon->port_number} ({$pon->name})",
            'code'               => "PON-{$pon->port_number}",
            'ip'                 => '—',
            'model'              => 'GPON Port Transceiver',
            'vendor'             => 'Optical Transceiver',
            'status'             => $ponStatus,
            'status_label'       => ucfirst($ponStatus),
            'level'              => 1,
            'shape'              => 'ellipse',
            'color'              => $this->getStatusColor($ponStatus),
            'font'               => ['color' => '#f8fafc', 'face' => 'JetBrains Mono, monospace', 'size' => 14, 'bold' => true],
            'parent'             => $olt ? "OLT {$olt->name}" : 'OLT Chassis',
            'alarms_count'       => 0,
            'children_count'     => $onuTotal,
            'affected_customers' => $customerCount,
            'is_direct_fault'    => true,
        ];

        if ($olt) {
            $edges[] = [
                'from'   => 'olt_' . $olt->id,
                'to'     => 'pon_' . $pon->id,
                'arrows' => 'to',
                'color'  => ['color' => '#00e5ff'],
                'width'  => 2.5,
            ];
        }

        // 3. Child ONUs
        foreach ($ponOnus->take(15) as $onu) {
            $onuOnlineFlag = $onu->last_seen_at && $onu->last_seen_at >= now()->subMinutes(5);
            $oStatus = $onu->status === 'los' ? 'critical' : ($onuOnlineFlag ? 'online' : 'critical');
            $onuCust = CustomerService::where('onu_id', $onu->id)->where('status', 'active')->first();

            $nodes[] = [
                'id'                 => 'onu_' . $onu->id,
                'label'              => "ONU: " . substr($onu->serial_number, -6) . "\n" . ($onuCust ? $onuCust->customer?->name : ($onu->name ?: 'ONT')),
                'type'               => 'ONU / ONT',
                'name'               => $onu->name ?: $onu->serial_number,
                'code'               => $onu->serial_number,
                'ip'                 => $onu->ip_address ?? '—',
                'model'              => $onu->model ?? 'ONU Device',
                'vendor'             => $onu->vendor->name ?? 'ZTE / Huawei',
                'status'             => $oStatus,
                'status_label'       => ucfirst($oStatus),
                'level'              => 2,
                'shape'              => 'box',
                'color'              => $this->getStatusColor($oStatus),
                'font'               => ['color' => '#f8fafc', 'face' => 'JetBrains Mono, monospace', 'size' => 12],
                'parent'             => "PON {$pon->port_number}",
                'alarms_count'       => 0,
                'children_count'     => 1,
                'affected_customers' => $onuCust ? 1 : 0,
                'is_direct_fault'    => false,
            ];

            $edges[] = [
                'from'   => 'pon_' . $pon->id,
                'to'     => 'onu_' . $onu->id,
                'arrows' => 'to',
                'color'  => ['color' => $oStatus === 'critical' ? '#ef4444' : '#64748b'],
                'width'  => 1.5,
            ];
        }

        // RCA
        $severity = $customerCount > 50 ? 'CRITICAL (P1)' : ($customerCount > 10 ? 'HIGH (P2)' : ($customerCount > 0 ? 'MEDIUM (P3)' : 'LOW (P4)'));
        $rca = [
            'failed_device'      => "PON Port {$pon->port_number} ({$pon->name}) on OLT " . ($olt->name ?? 'OLT'),
            'device_type'        => 'GPON Optical Port',
            'suspected_cause'    => $ponStatus === 'critical' ? 'Fiber Distribution Cut / SFP Transceiver Loss of Signal (LOS)' : 'PON Port Degradation / Operational Impact Analysis',
            'affected_path'      => "OLT [{$olt?->name}] → PON Port [{$pon->port_number}] → {$onuTotal} ONUs → {$customerCount} Pelanggan",
            'severity'           => $severity,
            'detection_time'     => now()->format('Y-m-d H:i:s'),
            'last_status_update' => $pon->updated_at ? $pon->updated_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
            'is_direct_fault'    => true,
            'impact_count'       => $customerCount,
        ];

        return [
            'nodes' => $nodes,
            'edges' => $edges,
            'rca'   => $rca,
        ];
    }

    private function buildRouterTopology(int $routerId): array
    {
        $router = Router::with('pop')->find($routerId);
        if (!$router) {
            return ['nodes' => [], 'edges' => [], 'rca' => null];
        }

        $nodes = [];
        $edges = [];

        $rOnline = in_array($router->status, ['online', 'active']);
        $rStatus = $rOnline ? 'online' : 'critical';

        // Count routed customers
        $customerCount = CustomerService::whereHas('networkProfile', function ($q) use ($routerId) {
            $q->where('router_id', $routerId);
        })->where('status', 'active')->distinct('customer_id')->count('customer_id');

        // Root Node (Router)
        $routerAlarms = Alarm::where('status', 'open')->where(function ($q) use ($router) {
            $q->where('source_type', 'router')->where('source_id', $router->id)
              ->orWhere('source_name', $router->name);
        })->get();

        $nodes[] = [
            'id'                 => 'router_' . $router->id,
            'label'              => "★ Router: {$router->name}\nIP: " . ($router->ip_address ?: 'No IP'),
            'type'               => 'Core / Distribution Router',
            'name'               => $router->name,
            'code'               => $router->code ?? '-',
            'ip'                 => $router->ip_address ?? '—',
            'model'              => $router->model ?? 'MikroTik RouterOS',
            'vendor'             => 'MikroTik',
            'status'             => $rStatus,
            'status_label'       => ucfirst($rStatus),
            'level'              => 0,
            'shape'              => 'box',
            'color'              => $this->getStatusColor($rStatus),
            'font'               => ['color' => '#f8fafc', 'face' => 'JetBrains Mono, monospace', 'size' => 15, 'bold' => true],
            'parent'             => $router->pop ? "POP {$router->pop->name}" : 'Core Backbone',
            'alarms_count'       => $routerAlarms->count(),
            'children_count'     => 0,
            'affected_customers' => $customerCount,
            'is_direct_fault'    => true,
        ];

        // Downstream OLTs in the same POP
        $olts = $router->pop_id ? Olt::where('pop_id', $router->pop_id)->get() : Olt::take(4)->get();
        foreach ($olts as $olt) {
            $isOltOnline = $olt->status === 'active' && $olt->last_polled_at && $olt->last_polled_at >= now()->subMinutes(15);
            $oStatus = $isOltOnline ? 'online' : 'warning';
            $oltCustCount = CustomerService::whereIn('onu_id', Onu::where('olt_id', $olt->id)->pluck('id'))->where('status', 'active')->count();

            $nodes[] = [
                'id'                 => 'olt_' . $olt->id,
                'label'              => "OLT: {$olt->name}\nIP: " . ($olt->ip_address ?: '—'),
                'type'               => 'Downstream OLT',
                'name'               => $olt->name,
                'code'               => $olt->code ?? '-',
                'ip'                 => $olt->ip_address ?? '—',
                'model'              => $olt->model ?? 'OLT Chassis',
                'vendor'             => $olt->vendor->name ?? 'ZTE / Huawei',
                'status'             => $oStatus,
                'status_label'       => ucfirst($oStatus),
                'level'              => 1,
                'shape'              => 'box',
                'color'              => $this->getStatusColor($oStatus),
                'font'               => ['color' => '#f8fafc', 'face' => 'JetBrains Mono, monospace', 'size' => 13, 'bold' => true],
                'parent'             => "Router {$router->name}",
                'alarms_count'       => 0,
                'children_count'     => $olt->ponPorts()->count(),
                'affected_customers' => $oltCustCount,
                'is_direct_fault'    => false,
            ];

            $edges[] = [
                'from'   => 'router_' . $router->id,
                'to'     => 'olt_' . $olt->id,
                'arrows' => 'to',
                'color'  => ['color' => '#00e5ff'],
                'width'  => 2.5,
            ];
        }

        // RCA
        $firstAlarm = $routerAlarms->sortByDesc('level')->first();
        $suspectedCause = $firstAlarm
            ? "Alarm: {$firstAlarm->title}"
            : (!$rOnline ? 'Router Offline / BGP-OSPF Session Down / Power Failure' : 'Router Operational / Downstream Session Impact Simulation');

        $severity = $customerCount > 100 ? 'CRITICAL (P1)' : ($customerCount > 30 ? 'HIGH (P2)' : ($customerCount > 0 ? 'MEDIUM (P3)' : 'LOW (P4)'));

        $rca = [
            'failed_device'      => "Router {$router->name} (" . ($router->ip_address ?: 'No IP') . ")",
            'device_type'        => 'Core / Distribution Router',
            'suspected_cause'    => $suspectedCause,
            'affected_path'      => "Core Backbone → Router [{$router->name}] → " . $olts->count() . " Downstream OLTs → {$customerCount} Pelanggan PPPoE/Hotspot",
            'severity'           => $severity,
            'detection_time'     => $firstAlarm?->started_at ? $firstAlarm->started_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
            'last_status_update' => $router->updated_at ? $router->updated_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
            'is_direct_fault'    => true,
            'impact_count'       => $customerCount,
        ];

        return [
            'nodes' => $nodes,
            'edges' => $edges,
            'rca'   => $rca,
        ];
    }

    private function getStatusColor(string $status): array
    {
        return match ($status) {
            'online' => [
                'background' => '#064e3b',
                'border'     => '#10b981',
                'highlight'  => ['background' => '#065f46', 'border' => '#34d399'],
            ],
            'warning' => [
                'background' => '#451a03',
                'border'     => '#f59e0b',
                'highlight'  => ['background' => '#78350f', 'border' => '#fbbf24'],
            ],
            'critical' => [
                'background' => '#450a0a',
                'border'     => '#ef4444',
                'highlight'  => ['background' => '#7f1d1d', 'border' => '#f87171'],
            ],
            default => [
                'background' => '#1e293b',
                'border'     => '#64748b',
                'highlight'  => ['background' => '#334155', 'border' => '#94a3b8'],
            ],
        };
    }

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
            'device'                   => '',
            'device_type'              => '',
            'ip_address'               => '—',
            'status'                   => 'unknown',
            'model'                    => '—',
            'pon_count'                => 0,
            'onu_count'                => 0,
            'customer_count'           => 0,
            'service_count'            => 0,
            'downstream_devices_count' => 0,
            'network_links_count'      => 0,
            'breakdown'                => ['pppoe' => 0, 'hotspot' => 0, 'other' => 0],
        ];
    }
}
