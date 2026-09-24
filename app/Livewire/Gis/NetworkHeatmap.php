<?php

namespace App\Livewire\Gis;

use Livewire\Component;
use App\Models\ISP\Olt;
use App\Models\ISP\Odp;
use Illuminate\Support\Facades\Cache;

class NetworkHeatmap extends Component
{
    public string $activeLayer = 'capacity';
    public string $areaFilter = 'all';
    public float $radiusKm = 10.0;
    public float $gridSize = 0.5;
    
    public array $heatmapData = [];
    public array $bounds = [];
    public bool $isLoading = true;
    
    protected $listeners = [
        'updateHeatmapLayer',
        'updateAreaFilter',
        'refreshHeatmap',
    ];

    public function mount()
    {
        $this->loadHeatmapData();
    }

    public function updatedActiveLayer()
    {
        $this->loadHeatmapData();
    }

    public function updatedAreaFilter()
    {
        $this->loadHeatmapData();
    }

    public function updateHeatmapLayer($layer)
    {
        $this->activeLayer = $layer;
        $this->loadHeatmapData();
    }

    public function updateAreaFilter($area)
    {
        $this->areaFilter = $area;
        $this->loadHeatmapData();
    }

    public function refreshHeatmap()
    {
        $this->loadHeatmapData();
    }

    public function loadHeatmapData()
    {
        $this->isLoading = true;

        $cacheKey = "heatmap_{$this->activeLayer}_{$this->areaFilter}";
        
        $this->heatmapData = Cache::remember($cacheKey, 300, function () {
            return $this->generateHeatmapData();
        });

        $this->bounds = $this->calculateBounds();
        
        $this->isLoading = false;
    }

    private function generateHeatmapData(): array
    {
        $points = match($this->activeLayer) {
            'capacity' => $this->getCapacityHeatmapData(),
            'coverage' => $this->getCoverageHeatmapData(),
            'customers' => $this->getCustomerHeatmapData(),
            'growth' => $this->getGrowthHeatmapData(),
            'alarms' => $this->getAlarmHeatmapData(),
            default => [],
        };

        return $points;
    }

    private function getCapacityHeatmapData(): array
    {
        $olts = Olt::with('capacity')->get();
        $odps = Odp::with('capacity')->get();

        $points = [];

        foreach ($olts as $olt) {
            if ($olt->latitude && $olt->longitude) {
                $utilization = $olt->capacity?->getUtilizationPercentage() ?? 0;
                $points[] = [
                    'lat' => $olt->latitude,
                    'lon' => $olt->longitude,
                    'value' => $utilization,
                    'type' => 'olt',
                    'id' => $olt->id,
                    'name' => $olt->name,
                ];
            }
        }

        foreach ($odps as $odp) {
            if ($odp->latitude && $odp->longitude) {
                $utilization = $odp->capacity?->getUtilizationPercentage() ?? 0;
                $points[] = [
                    'lat' => $odp->latitude,
                    'lon' => $odp->longitude,
                    'value' => $utilization,
                    'type' => 'odp',
                    'id' => $odp->id,
                    'name' => $odp->name,
                ];
            }
        }

        return $points;
    }

    private function getCoverageHeatmapData(): array
    {
        return [
            ['lat' => -6.2088, 'lon' => 106.8456, 'value' => 95, 'type' => 'coverage'],
            ['lat' => -6.2415, 'lon' => 106.7812, 'value' => 87, 'type' => 'coverage'],
            ['lat' => -6.1751, 'lon' => 106.8650, 'value' => 72, 'type' => 'coverage'],
        ];
    }

    private function getCustomerHeatmapData(): array
    {
        return [
            ['lat' => -6.2088, 'lon' => 106.8456, 'value' => 150, 'type' => 'customer'],
            ['lat' => -6.2415, 'lon' => 106.7812, 'value' => 200, 'type' => 'customer'],
            ['lat' => -6.1751, 'lon' => 106.8650, 'value' => 120, 'type' => 'customer'],
        ];
    }

    private function getGrowthHeatmapData(): array
    {
        return [
            ['lat' => -6.2088, 'lon' => 106.8456, 'value' => 15, 'type' => 'growth'],
            ['lat' => -6.2415, 'lon' => 106.7812, 'value' => 25, 'type' => 'growth'],
            ['lat' => -6.1751, 'lon' => 106.8650, 'value' => 8, 'type' => 'growth'],
        ];
    }

    private function getAlarmHeatmapData(): array
    {
        return [
            ['lat' => -6.2088, 'lon' => 106.8456, 'value' => 80, 'type' => 'alarm'],
            ['lat' => -6.2415, 'lon' => 106.7812, 'value' => 60, 'type' => 'alarm'],
        ];
    }

    private function calculateBounds(): array
    {
        if (empty($this->heatmapData)) {
            return [
                'min_lat' => -6.5,
                'max_lat' => -6.0,
                'min_lon' => 106.5,
                'max_lon' => 107.2,
            ];
        }

        $lats = array_column($this->heatmapData, 'lat');
        $lons = array_column($this->heatmapData, 'lon');

        return [
            'min_lat' => min($lats) - 0.1,
            'max_lat' => max($lats) + 0.1,
            'min_lon' => min($lons) - 0.1,
            'max_lon' => max($lons) + 0.1,
        ];
    }

    public function getLayerConfigProperty()
    {
        return [
            'capacity' => [
                'label' => 'Capacity',
                'colorScale' => ['#10B981', '#F59E0B', '#EF4444'],
                'unit' => '%',
                'max' => 100,
            ],
            'coverage' => [
                'label' => 'Coverage',
                'colorScale' => ['#E5E7EB', '#3B82F6'],
                'unit' => '%',
                'max' => 100,
            ],
            'customers' => [
                'label' => 'Customer Density',
                'colorScale' => ['#FEF3C7', '#F59E0B', '#DC2626'],
                'unit' => 'per km²',
                'max' => 500,
            ],
            'growth' => [
                'label' => 'Growth Rate',
                'colorScale' => ['#F3F4F6', '#10B981'],
                'unit' => '%',
                'max' => 50,
            ],
            'alarms' => [
                'label' => 'Active Alarms',
                'colorScale' => ['#D1FAE5', '#F59E0B', '#EF4444'],
                'unit' => 'count',
                'max' => 100,
            ],
        ];
    }

    public function render()
    {
        return view('livewire.gis.network-heatmap');
    }
}
