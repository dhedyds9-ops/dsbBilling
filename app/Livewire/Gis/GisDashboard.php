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
            $this->stats = Cache::remember('gis_dashboard_stats', 60, function () {
                return $this->fetchDashboardStats();
            });

            $this->recentAlarms = Cache::remember('gis_recent_alarms', 30, function () {
                return $this->fetchRecentAlarms();
            });

            $this->criticalNodes = Cache::remember('gis_critical_nodes', 60, function () {
                return $this->fetchCriticalNodes();
            });
        } catch (\Exception $e) {
            $this->stats = $this->getDefaultStats();
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

    private function fetchDashboardStats(): array
    {
        return [
            'total_nodes' => 1250,
            'active_nodes' => 1180,
            'total_olt' => 45,
            'active_olt' => 42,
            'total_odp' => 1200,
            'active_odp' => 1150,
            'total_onu' => 3500,
            'online_onu' => 3350,
            'total_customers' => 15200,
            'active_customers' => 14800,
            'coverage_percentage' => 87.5,
            'total_capacity' => 45000,
            'used_capacity' => 32400,
            'utilization_percentage' => 72.0,
            'active_alarms' => 15,
            'critical_alarms' => 3,
            'pending_tickets' => 42,
            'avg_response_time' => 45,
        ];
    }

    private function fetchRecentAlarms(): array
    {
        return [
            [
                'id' => 'ALM-001',
                'severity' => 'critical',
                'title' => 'OLT-001 Down',
                'location' => 'Jakarta Selatan',
                'time_ago' => now()->subMinutes(5)->diffForHumans(),
                'node_id' => 'OLT-001',
                'node_type' => 'olt',
            ],
            [
                'id' => 'ALM-002',
                'severity' => 'warning',
                'title' => 'High utilization ODP-042',
                'location' => 'Bandung',
                'time_ago' => now()->subMinutes(15)->diffForHumans(),
                'node_id' => 'ODP-042',
                'node_type' => 'odp',
            ],
            [
                'id' => 'ALM-003',
                'severity' => 'info',
                'title' => 'Scheduled maintenance OLT-003',
                'location' => 'Surabaya',
                'time_ago' => now()->subMinutes(30)->diffForHumans(),
                'node_id' => 'OLT-003',
                'node_type' => 'olt',
            ],
        ];
    }

    private function fetchCriticalNodes(): array
    {
        return [
            [
                'id' => 'OLT-001',
                'type' => 'olt',
                'name' => 'OLT Central Jakarta',
                'status' => 'critical',
                'utilization' => 95.5,
                'lat' => -6.1751,
                'lon' => 106.8650,
            ],
            [
                'id' => 'ODP-042',
                'type' => 'odp',
                'name' => 'ODP Kebayoran',
                'status' => 'warning',
                'utilization' => 82.0,
                'lat' => -6.2415,
                'lon' => 106.7812,
            ],
        ];
    }

    private function getDefaultStats(): array
    {
        return [
            'total_nodes' => 0,
            'active_nodes' => 0,
            'total_customers' => 0,
            'active_customers' => 0,
            'coverage_percentage' => 0,
            'total_capacity' => 0,
            'used_capacity' => 0,
            'utilization_percentage' => 0,
            'active_alarms' => 0,
            'critical_alarms' => 0,
            'pending_tickets' => 0,
            'avg_response_time' => 0,
        ];
    }

    public function render()
    {
        return view('livewire.gis.components.dashboard')->layout('layouts.noc');
    }
}
