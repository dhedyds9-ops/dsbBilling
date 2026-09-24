<?php

namespace App\Livewire\Gis;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RoutePlanner extends Component
{
    public ?string $sourceNodeId = null;
    public ?string $sourceNodeType = null;
    public ?string $targetNodeId = null;
    public ?string $targetNodeType = null;
    
    public ?string $sourceNodeName = null;
    public ?string $targetNodeName = null;
    
    public ?array $calculatedRoute = null;
    public array $alternativeRoutes = [];
    public ?string $selectedRouteId = null;
    
    public string $optimizationType = 'distance';
    public array $routeStats = [];
    
    public bool $isCalculating = false;
    public bool $showAlternatives = false;
    
    protected $listeners = [
        'calculateRoute',
        'selectNode',
    ];

    public function selectNode($nodeId, $nodeType, $nodeName)
    {
        if (!$this->sourceNodeId) {
            $this->sourceNodeId = $nodeId;
            $this->sourceNodeType = $nodeType;
            $this->sourceNodeName = $nodeName;
        } elseif (!$this->targetNodeId) {
            $this->targetNodeId = $nodeId;
            $this->targetNodeType = $nodeType;
            $this->targetNodeName = $nodeName;
        } else {
            $this->resetRoute();
            $this->sourceNodeId = $nodeId;
            $this->sourceNodeType = $nodeType;
            $this->sourceNodeName = $nodeName;
        }
    }

    public function calculateRoute()
    {
        if (!$this->sourceNodeId || !$this->targetNodeId) {
            return;
        }

        $this->isCalculating = true;

        try {
            $routeData = Cache::remember("route_{$this->sourceNodeId}_{$this->targetNodeId}", 300, function () {
                return $this->fetchRouteCalculation();
            });

            $this->calculatedRoute = $routeData['primary'];
            $this->alternativeRoutes = $routeData['alternatives'] ?? [];
            $this->routeStats = $routeData['stats'];
            $this->selectedRouteId = $this->calculatedRoute['id'] ?? null;

            $this->dispatch('displayRoute', route: $this->calculatedRoute);
        } catch (\Exception $e) {
            $this->dispatch('showError', message: 'Failed to calculate route');
        }

        $this->isCalculating = false;
    }

    private function fetchRouteCalculation(): array
    {
        return [
            'primary' => [
                'id' => 'route-001',
                'source' => [
                    'id' => $this->sourceNodeId,
                    'type' => $this->sourceNodeType,
                    'name' => $this->sourceNodeName,
                ],
                'target' => [
                    'id' => $this->targetNodeId,
                    'type' => $this->targetNodeType,
                    'name' => $this->targetNodeName,
                ],
                'path' => [
                    ['id' => $this->sourceNodeId, 'type' => $this->sourceNodeType, 'name' => $this->sourceNodeName],
                    ['id' => 'ODC-001', 'type' => 'odc', 'name' => 'ODC Central'],
                    ['id' => 'ODP-042', 'type' => 'odp', 'name' => 'ODP Kebayoran'],
                    ['id' => 'ODP-043', 'type' => 'odp', 'name' => 'ODP Senayan'],
                    ['id' => $this->targetNodeId, 'type' => $this->targetNodeType, 'name' => $this->targetNodeName],
                ],
                'distance' => 12.5,
                'fiber_length' => 15.2,
                'hop_count' => 5,
                'estimated_time' => 45,
                'latency' => 5.2,
                'cost' => 150000,
            ],
            'alternatives' => [
                [
                    'id' => 'route-002',
                    'rank' => 2,
                    'distance' => 15.8,
                    'hop_count' => 7,
                    'difference_from_primary' => 26,
                ],
                [
                    'id' => 'route-003',
                    'rank' => 3,
                    'distance' => 18.2,
                    'hop_count' => 8,
                    'difference_from_primary' => 46,
                ],
            ],
            'stats' => [
                'total_distance' => 12.5,
                'total_fiber' => 15.2,
                'hop_count' => 5,
                'avg_utilization' => 45,
            ],
        ];
    }

    public function selectAlternative($routeId)
    {
        $alternative = array_filter($this->alternativeRoutes, fn($r) => $r['id'] === $routeId);
        
        if ($alternative) {
            $this->selectedRouteId = $routeId;
            $this->calculatedRoute = array_values($alternative)[0];
            $this->dispatch('displayRoute', route: $this->calculatedRoute);
        }
    }

    public function resetRoute()
    {
        $this->sourceNodeId = null;
        $this->sourceNodeType = null;
        $this->sourceNodeName = null;
        $this->targetNodeId = null;
        $this->targetNodeType = null;
        $this->targetNodeName = null;
        $this->calculatedRoute = null;
        $this->alternativeRoutes = [];
        $this->selectedRouteId = null;
        $this->routeStats = [];
        
        $this->dispatch('clearRoute');
    }

    public function swapNodes()
    {
        $tempId = $this->sourceNodeId;
        $tempType = $this->sourceNodeType;
        $tempName = $this->sourceNodeName;

        $this->sourceNodeId = $this->targetNodeId;
        $this->sourceNodeType = $this->targetNodeType;
        $this->sourceNodeName = $this->targetNodeName;

        $this->targetNodeId = $tempId;
        $this->targetNodeType = $tempType;
        $this->targetNodeName = $tempName;

        if ($this->calculatedRoute) {
            $this->calculateRoute();
        }
    }

    public function toggleAlternatives()
    {
        $this->showAlternatives = !$this->showAlternatives;
    }

    public function exportRoute()
    {
        if ($this->calculatedRoute) {
            $this->dispatch('exportRouteData', route: $this->calculatedRoute);
        }
    }

    public function render()
    {
        return view('livewire.gis.route-planner');
    }
}
