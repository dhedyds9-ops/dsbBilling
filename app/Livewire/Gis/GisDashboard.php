<?php

namespace App\Livewire\Gis;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GisDashboard extends Component
{
    public array $stats = [];
    public array $recentAlarms = [];
    public array $criticalNodes = [];
    public string $selectedArea = 'all';
    public string $timeRange = '24h';
    public bool $isLoading = true;

    protected $listeners = [
        'refreshDashboard' => 'loadDashboard',
        'nodeSelected' => 'onNodeSelected',
        'alarmReceived' => 'onAlarmReceived'
    ];

    public function mount()
    {
        $this->loadDashboard();
    }

    public function loadDashboard()
    {
        $this->isLoading = true;

        try {
            $area = $this->selectedArea;
            $range = $this->timeRange;
            $cacheKey = "gis_dashboard_{$area}_{$range}";

            $data = Cache::remember($cacheKey, 30, function () {
                return [
                    'stats' => $this->fetchDashboardStats(),
                    'alarms' => $this->fetchRecentAlarms(),
                    'criticalNodes' => $this->fetchCriticalNodes(),
                ];
            });

            $this->stats = $data['stats'];
            $this->recentAlarms = $data['alarms'];
            $this->criticalNodes = $data['criticalNodes'];
        } catch (\Throwable $e) {
            \Log::error('Error loading GIS Dashboard data: ' . $e->getMessage());
            $this->stats = $this->getDefaultStats();
            $this->recentAlarms = [];
            $this->criticalNodes = [];
        }

        $this->isLoading = false;
    }

    public function updatedSelectedArea($value)
    {
        $this->loadDashboard();
    }

    public function updatedTimeRange($value)
    {
        $this->loadDashboard();
    }

    public function onNodeSelected($nodeId)
    {
        $this->dispatch('navigateToNode', nodeId: $nodeId);
    }

    public function onAlarmReceived($alarm)
    {
        array_unshift($this->recentAlarms, $alarm);
        $this->recentAlarms = array_slice($this->recentAlarms, 0, 10);
    }

    private function getSinceDateTime(): \Carbon\Carbon
    {
        return match ($this->timeRange) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => now()->subHours(24),
        };
    }

    private function fetchDashboardStats(): array
    {
        $area = $this->selectedArea;

        // OLT queries
        $oltQuery = \App\Models\ISP\Olt::query();
        if ($area !== 'all' && is_numeric($area)) {
            $oltQuery->where('branch_id', $area);
        }
        $totalOlt = (clone $oltQuery)->count();
        $activeOlt = (clone $oltQuery)->where(function ($q) {
            $q->where('status', 'online')
              ->orWhere('status', 'active')
              ->orWhere('status', 'up');
        })->count();

        // ODP queries
        $odpQuery = \App\Models\ISP\Odp::query();
        if ($area !== 'all') {
            if (is_numeric($area)) {
                $odpQuery->where('branch_id', $area);
            } else {
                $odpQuery->where(function ($q) use ($area) {
                    $q->where('regency', 'like', "%{$area}%")
                      ->orWhere('district', 'like', "%{$area}%")
                      ->orWhere('address', 'like', "%{$area}%");
                });
            }
        }
        $totalOdp = (clone $odpQuery)->count();
        $activeOdp = (clone $odpQuery)->where(function ($q) {
            $q->where('status', 'active')
              ->orWhere('status', 'online')
              ->orWhere('status', 'normal');
        })->count();

        // ONU queries
        $onuQuery = \App\Models\ISP\Onu::query();
        if ($area !== 'all' && is_numeric($area)) {
            $onuQuery->where('branch_id', $area);
        }
        $totalOnu = (clone $onuQuery)->count();
        $onlineOnu = (clone $onuQuery)->where(function ($q) {
            $q->where('status', 'online')
              ->orWhere('status', 'up')
              ->orWhere('status', 'active');
        })->count();

        // Alarm queries
        $since = $this->getSinceDateTime();
        $alarmQuery = \App\Models\Alarm::query()->where('created_at', '>=', $since);
        $activeAlarms = (clone $alarmQuery)->where('status', 'active')->count();
        $criticalAlarms = (clone $alarmQuery)->where('status', 'active')
            ->where(function ($q) {
                $q->where('level', 'critical')->orWhere('level', 'danger');
            })->count();

        return [
            'total_olt' => $totalOlt,
            'active_olt' => $activeOlt,
            'total_odp' => $totalOdp,
            'active_odp' => $activeOdp,
            'total_onu' => $totalOnu,
            'online_onu' => $onlineOnu,
            'active_alarms' => $activeAlarms,
            'critical_alarms' => $criticalAlarms,
        ];
    }

    private function fetchRecentAlarms(): array
    {
        $since = $this->getSinceDateTime();

        return \App\Models\Alarm::query()
            ->where('created_at', '>=', $since)
            ->latest()
            ->take(15)
            ->get()
            ->map(function ($alarm) {
                $severity = match (strtolower($alarm->level ?? '')) {
                    'critical', 'danger' => 'critical',
                    'warning' => 'warning',
                    default => 'info',
                };

                return [
                    'id' => $alarm->uuid ?? (string) $alarm->id,
                    'severity' => $severity,
                    'title' => $alarm->title ?? 'Sistem Alarm',
                    'location' => $alarm->source_name ?? ($alarm->description ?? 'NOC Monitor'),
                    'time_ago' => $alarm->created_at ? $alarm->created_at->diffForHumans() : '-',
                    'node_id' => $alarm->source_id ?? '',
                    'node_type' => $alarm->source_type ?? 'node',
                ];
            })
            ->toArray();
    }

    private function fetchCriticalNodes(): array
    {
        $criticalNodes = [];

        // 1. OLTs that are down or critical
        $olts = \App\Models\ISP\Olt::query()
            ->where(function ($q) {
                $q->whereIn('status', ['offline', 'down', 'critical', 'error'])
                  ->orWhere('active_port_count', '>=', \DB::raw('COALESCE(pon_port_count, 1)'));
            })
            ->take(5)
            ->get();

        foreach ($olts as $olt) {
            $total = max(1, $olt->pon_port_count ?? 1);
            $used = $olt->active_port_count ?? 0;
            $utilization = min(100, (float) round(($used / $total) * 100, 1));
            $isDown = in_array(strtolower($olt->status ?? ''), ['offline', 'down', 'critical', 'error']);

            $criticalNodes[] = [
                'id' => 'OLT-' . $olt->id,
                'type' => 'olt',
                'name' => $olt->name ?? $olt->code ?? "OLT #{$olt->id}",
                'status' => $isDown ? 'critical' : 'warning',
                'utilization' => $utilization,
                'location' => $olt->address ?? ($olt->ip_address ? "IP: {$olt->ip_address}" : 'Server POP'),
                'lat' => (float) ($olt->latitude ?? 0),
                'lon' => (float) ($olt->longitude ?? 0),
            ];
        }

        // 2. ODPs that are full or in critical condition
        $odps = \App\Models\ISP\Odp::query()
            ->where(function ($q) {
                $q->whereIn('status', ['damaged', 'full', 'critical', 'maintenance'])
                  ->orWhere('used_port_count', '>=', \DB::raw('COALESCE(port_count, 1)'));
            })
            ->take(5)
            ->get();

        foreach ($odps as $odp) {
            $total = max(1, $odp->port_count ?? 1);
            $used = $odp->used_port_count ?? 0;
            $utilization = min(100, (float) round(($used / $total) * 100, 1));

            $criticalNodes[] = [
                'id' => 'ODP-' . $odp->id,
                'type' => 'odp',
                'name' => $odp->name ?? $odp->code ?? "ODP #{$odp->id}",
                'status' => $utilization >= 100 ? 'critical' : 'warning',
                'utilization' => $utilization,
                'location' => $odp->address ?? ($odp->regency ?? 'Distribusi ODP'),
                'lat' => (float) ($odp->latitude ?? 0),
                'lon' => (float) ($odp->longitude ?? 0),
            ];
        }

        return $criticalNodes;
    }

    private function getDefaultStats(): array
    {
        return [
            'total_olt' => 0,
            'active_olt' => 0,
            'total_odp' => 0,
            'active_odp' => 0,
            'total_onu' => 0,
            'online_onu' => 0,
            'active_alarms' => 0,
            'critical_alarms' => 0,
        ];
    }

    public function render()
    {
        $branches = \App\Models\Master\Branch::where('is_active', true)->get(['id', 'name']);

        return view('livewire.gis.components.dashboard', [
            'branches' => $branches,
        ])->layout('layouts.noc');
    }
}
