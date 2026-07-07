<?php

namespace App\Livewire\Gis;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;

class AlarmPanel extends Component
{
    use WithPagination;

    public string $filterSeverity = 'all';
    public string $filterStatus = 'all';
    public string $searchQuery = '';
    public string $timeRange = '24h';
    public bool $isAutoRefresh = true;
    public int $refreshInterval = 30;
    
    protected $listeners = [
        'alarmReceived',
        'refreshAlarms',
    ];

    public function mount()
    {
        $this->startAutoRefresh();
    }

    public function startAutoRefresh()
    {
        if ($this->isAutoRefresh) {
            $this->dispatch('startRefreshTimer', interval: $this->refreshInterval);
        }
    }

    public function updatedIsAutoRefresh($value)
    {
        if ($value) {
            $this->startAutoRefresh();
        } else {
            $this->dispatch('stopRefreshTimer');
        }
    }

    public function getAlarmsProperty()
    {
        $alarms = Cache::remember("alarms_{$this->timeRange}", 30, function () {
            return $this->fetchAlarms();
        });

        if ($this->filterSeverity !== 'all') {
            $alarms = array_filter($alarms, fn($a) => $a['severity'] === $this->filterSeverity);
        }

        if ($this->filterStatus !== 'all') {
            $alarms = array_filter($alarms, fn($a) => $a['status'] === $this->filterStatus);
        }

        if ($this->searchQuery) {
            $query = strtolower($this->searchQuery);
            $alarms = array_filter($alarms, fn($a) => 
                str_contains(strtolower($a['message']), $query) ||
                str_contains(strtolower($a['id']), $query)
            );
        }

        return array_values($alarms);
    }

    public function getCriticalCountProperty()
    {
        return count(array_filter($this->alarms, fn($a) => $a['severity'] === 'critical'));
    }

    public function getWarningCountProperty()
    {
        return count(array_filter($this->alarms, fn($a) => $a['severity'] === 'warning'));
    }

    public function getActiveAlarmsProperty()
    {
        return array_filter($this->alarms, fn($a) => $a['status'] === 'active');
    }

    private function fetchAlarms(): array
    {
        return [
            [
                'id' => 'ALM-2024-001',
                'severity' => 'critical',
                'status' => 'active',
                'type' => 'node_down',
                'message' => 'OLT-001 is not responding',
                'node_id' => 'OLT-001',
                'node_type' => 'olt',
                'location' => 'Jakarta Selatan',
                'timestamp' => now()->subMinutes(5)->toIso8601String(),
                'acknowledged_by' => null,
                'resolved_at' => null,
            ],
            [
                'id' => 'ALM-2024-002',
                'severity' => 'warning',
                'status' => 'active',
                'type' => 'capacity_warning',
                'message' => 'ODP-042 utilization above 80%',
                'node_id' => 'ODP-042',
                'node_type' => 'odp',
                'location' => 'Bandung',
                'timestamp' => now()->subMinutes(15)->toIso8601String(),
                'acknowledged_by' => 'Tech-101',
                'resolved_at' => null,
            ],
            [
                'id' => 'ALM-2024-003',
                'severity' => 'info',
                'status' => 'active',
                'type' => 'maintenance',
                'message' => 'Scheduled maintenance OLT-003',
                'node_id' => 'OLT-003',
                'node_type' => 'olt',
                'location' => 'Surabaya',
                'timestamp' => now()->subMinutes(30)->toIso8601String(),
                'acknowledged_by' => null,
                'resolved_at' => null,
            ],
            [
                'id' => 'ALM-2024-004',
                'severity' => 'critical',
                'status' => 'acknowledged',
                'type' => 'fiber_cut',
                'message' => 'Fiber cable damaged at segment FK-042',
                'node_id' => 'FK-042',
                'node_type' => 'fiber',
                'location' => 'Bekasi',
                'timestamp' => now()->subHours(2)->toIso8601String(),
                'acknowledged_by' => 'Tech-205',
                'resolved_at' => null,
            ],
            [
                'id' => 'ALM-2024-005',
                'severity' => 'warning',
                'status' => 'resolved',
                'type' => 'signal_degradation',
                'message' => 'Signal degradation detected on ODP-015',
                'node_id' => 'ODP-015',
                'node_type' => 'odp',
                'location' => 'Tangerang',
                'timestamp' => now()->subHours(5)->toIso8601String(),
                'acknowledged_by' => 'Tech-103',
                'resolved_at' => now()->subHours(4)->toIso8601String(),
            ],
        ];
    }

    public function acknowledgeAlarm($alarmId)
    {
        $this->dispatch('acknowledgeAlarm', alarmId: $alarmId);
    }

    public function viewOnMap($nodeId, $nodeType)
    {
        $this->dispatch('navigateToNode', nodeId: $nodeId, nodeType: $nodeType);
    }

    public function refreshAlarms()
    {
        Cache::forget("alarms_{$this->timeRange}");
        $this->dispatch('refreshDashboard');
    }

    public function alarmReceived($alarm)
    {
        $this->dispatch('playAlarmSound', severity: $alarm['severity']);
        $this->dispatch('showNotification', [
            'title' => "New {$alarm['severity']} Alarm",
            'message' => $alarm['message'],
        ]);
    }

    public function render()
    {
        return view('livewire.gis.components.alarm-panel', [
            'alarms' => $this->alarms,
            'criticalCount' => $this->criticalCount,
            'warningCount' => $this->warningCount,
            'activeCount' => count($this->activeAlarms),
        ]);
    }
}
