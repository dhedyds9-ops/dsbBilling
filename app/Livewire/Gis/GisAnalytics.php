<?php

namespace App\Livewire\Gis;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;

class GisAnalytics extends Component
{
    use WithPagination;

    public string $timeRange = '7d';
    public string $areaFilter = 'all';
    public string $selectedMetric = 'coverage';
    
    public array $chartData = [];
    public array $summaryMetrics = [];
    public array $topPerformers = [];
    public array $bottomPerformers = [];

    protected $listeners = [
        'refreshAnalytics',
        'updateTimeRange',
    ];

    public function mount()
    {
        $this->loadAnalytics();
    }

    public function updatedTimeRange($value)
    {
        $this->loadAnalytics();
    }

    public function updatedSelectedMetric($value)
    {
        $this->loadAnalytics();
    }

    public function loadAnalytics()
    {
        $cacheKey = "gis_analytics_{$this->timeRange}_{$this->selectedMetric}";

        $data = Cache::remember($cacheKey, 300, function () {
            return [
                'chart' => $this->generateChartData(),
                'summary' => $this->generateSummaryMetrics(),
                'top' => $this->getTopPerformers(),
                'bottom' => $this->getBottomPerformers(),
            ];
        });

        $this->chartData = $data['chart'];
        $this->summaryMetrics = $data['summary'];
        $this->topPerformers = $data['top'];
        $this->bottomPerformers = $data['bottom'];
    }

    private function generateChartData(): array
    {
        $labels = [];
        $values = [];

        switch ($this->timeRange) {
            case '24h':
                for ($i = 0; $i < 24; $i++) {
                    $labels[] = date('H:i', strtotime("-{$i} hours"));
                    $values[] = rand(70, 95);
                }
                break;
            case '7d':
                for ($i = 6; $i >= 0; $i--) {
                    $labels[] = date('D', strtotime("-{$i} days"));
                    $values[] = rand(75, 90);
                }
                break;
            case '30d':
                for ($i = 29; $i >= 0; $i--) {
                    $labels[] = date('d', strtotime("-{$i} days"));
                    $values[] = rand(72, 88);
                }
                break;
            default:
                $labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
                $values = [82, 85, 78, 88];
        }

        return [
            'labels' => array_reverse($labels),
            'values' => array_reverse($values),
            'metric' => $this->selectedMetric,
        ];
    }

    private function generateSummaryMetrics(): array
    {
        return [
            'current' => [
                'value' => 87.5,
                'change' => 2.3,
                'trend' => 'up',
            ],
            'average' => [
                'value' => 85.2,
                'change' => 1.5,
                'trend' => 'up',
            ],
            'peak' => [
                'value' => 95.0,
                'timestamp' => now()->subDays(2)->toIso8601String(),
            ],
            'lowest' => [
                'value' => 72.0,
                'timestamp' => now()->subDays(5)->toIso8601String(),
            ],
        ];
    }

    private function getTopPerformers(): array
    {
        return [
            ['id' => 'OLT-001', 'name' => 'OLT Central Jakarta', 'value' => 98.5, 'type' => 'olt'],
            ['id' => 'ODP-015', 'name' => 'ODP Kebayoran Baru', 'value' => 96.2, 'type' => 'odp'],
            ['id' => 'OLT-005', 'name' => 'OLT Bandung Utara', 'value' => 95.8, 'type' => 'olt'],
        ];
    }

    private function getBottomPerformers(): array
    {
        return [
            ['id' => 'ODP-042', 'name' => 'ODP Cibitung', 'value' => 45.2, 'type' => 'odp'],
            ['id' => 'OLT-008', 'name' => 'OLT Bekasi Timur', 'value' => 52.3, 'type' => 'olt'],
            ['id' => 'ODP-089', 'name' => 'ODP Tangsel', 'value' => 58.7, 'type' => 'odp'],
        ];
    }

    public function refreshAnalytics()
    {
        Cache::forget("gis_analytics_{$this->timeRange}_{$this->selectedMetric}");
        $this->loadAnalytics();
    }

    public function updateTimeRange($range)
    {
        $this->timeRange = $range;
    }

    public function exportReport()
    {
        $this->dispatch('exportAnalyticsReport', [
            'timeRange' => $this->timeRange,
            'metric' => $this->selectedMetric,
            'data' => $this->chartData,
        ]);
    }

    public function render()
    {
        return view('livewire.gis.gis-analytics', [
            'chartData' => $this->chartData,
            'summaryMetrics' => $this->summaryMetrics,
            'topPerformers' => $this->topPerformers,
            'bottomPerformers' => $this->bottomPerformers,
        ]);
    }
}
