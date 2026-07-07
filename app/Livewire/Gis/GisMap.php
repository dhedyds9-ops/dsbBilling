<?php

namespace App\Livewire\Gis;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ISP\Olt;
use App\Models\ISP\Odp;
use App\Models\ISP\FiberCore;
use Illuminate\Support\Facades\Cache;

class GisMap extends Component
{
    use WithPagination;

    public string $centerLat = '-6.2088';
    public string $centerLon = '106.8456';
    public int $zoom = 12;
    public bool $isLoading = false;
    
    public array $layers = [
        'olt' => true,
        'odp' => true,
        'onu' => false,
        'fiber' => true,
        'customer' => false,
        'heatmap' => false,
    ];
    
    public array $selectedNodes = [];
    public ?string $selectedNodeId = null;
    public ?string $selectedNodeType = null;
    public array $nodeDetails = [];
    
    public string $searchQuery = '';
    public array $searchResults = [];
    
    public bool $isDrawing = false;
    public string $drawingMode = 'none';
    public array $drawnFeatures = [];
    
    public ?string $routeFrom = null;
    public ?string $routeTo = null;
    public ?array $routePath = null;
    
    protected $listeners = [
        'nodeClicked' => 'onNodeClicked',
        'mapMoved' => 'onMapMoved',
        'layersUpdated' => 'onLayersUpdated',
        'startRouting' => 'onStartRouting',
        'cancelRouting' => 'onCancelRouting',
    ];

    protected $queryString = [
        'centerLat',
        'centerLon',
        'zoom',
        'selectedNodeId',
    ];

    public function mount()
    {
        $this->loadMapData();
    }

    public function loadMapData()
    {
        // Data will be loaded via JS from API endpoints
    }

    public function getMapDataProperty()
    {
        return Cache::remember('gis_map_data', 120, function () {
            return [
                'olts' => Olt::with(['ponPorts', 'capacity'])->get()->map(function ($olt) {
                    return [
                        'id' => $olt->id,
                        'name' => $olt->name,
                        'code' => $olt->code,
                        'lat' => $olt->latitude,
                        'lon' => $olt->longitude,
                        'status' => $olt->status,
                        'utilization' => $olt->capacity?->getUtilizationPercentage() ?? 0,
                        'pon_port_count' => $olt->ponPorts->count(),
                    ];
                })->toArray(),
                
                'odps' => Odp::with(['splitters', 'capacity'])->get()->map(function ($odp) {
                    return [
                        'id' => $odp->id,
                        'name' => $odp->name,
                        'code' => $odp->code,
                        'lat' => $odp->latitude,
                        'lon' => $odp->longitude,
                        'status' => $odp->status,
                        'utilization' => $odp->capacity?->getUtilizationPercentage() ?? 0,
                        'splitter_count' => $odp->splitters->count(),
                    ];
                })->toArray(),
                
                'fiberCables' => FiberCore::with('fiberCable')->get()->map(function ($core) {
                    return [
                        'id' => $core->id,
                        'cable_id' => $core->fiber_cable_id,
                        'core_number' => $core->core_number,
                        'status' => $core->status,
                        'utilization' => $core->getUtilizationPercentage(),
                    ];
                })->toArray(),
            ];
        });
    }

    public function onNodeClicked($nodeId, $nodeType)
    {
        $this->selectedNodeId = $nodeId;
        $this->selectedNodeType = $nodeType;
        $this->loadNodeDetails($nodeId, $nodeType);
        $this->dispatch('showNodeDetails', nodeId: $nodeId, nodeType: $nodeType);
    }

    public function loadNodeDetails($nodeId, $nodeType)
    {
        $this->nodeDetails = match($nodeType) {
            'olt' => $this->getOltDetails($nodeId),
            'odp' => $this->getOdpDetails($nodeId),
            'onu' => $this->getOnuDetails($nodeId),
            default => [],
        };
    }

    private function getOltDetails($oltId)
    {
        $olt = Olt::with(['ponPorts', 'capacity', 'location'])->find($oltId);
        
        if (!$olt) {
            return [];
        }

        return [
            'type' => 'olt',
            'id' => $olt->id,
            'name' => $olt->name,
            'code' => $olt->code,
            'status' => $olt->status,
            'location' => $olt->location?->name ?? 'Unknown',
            'capacity' => [
                'total' => $olt->capacity?->totalPonPorts ?? 0,
                'used' => $olt->capacity?->usedPonPorts ?? 0,
                'utilization' => $olt->capacity?->getUtilizationPercentage() ?? 0,
            ],
            'pon_ports' => $olt->ponPorts->map(fn($port) => [
                'id' => $port->id,
                'port_number' => $port->port_number,
                'status' => $port->status,
                'onu_count' => $port->onus->count() ?? 0,
            ])->toArray(),
            'actions' => [
                ['label' => 'View Details', 'action' => 'viewDetails'],
                ['label' => 'Manage Ports', 'action' => 'managePorts'],
                ['label' => 'View Customers', 'action' => 'viewCustomers'],
            ],
        ];
    }

    private function getOdpDetails($odpId)
    {
        $odp = Odp::with(['splitters', 'capacity', 'location'])->find($odpId);
        
        if (!$odp) {
            return [];
        }

        return [
            'type' => 'odp',
            'id' => $odp->id,
            'name' => $odp->name,
            'code' => $odp->code,
            'status' => $odp->status,
            'location' => $odp->location?->name ?? 'Unknown',
            'capacity' => [
                'total' => $odp->capacity?->totalPorts ?? 0,
                'used' => $odp->capacity?->usedPorts ?? 0,
                'utilization' => $odp->capacity?->getUtilizationPercentage() ?? 0,
            ],
            'splitters' => $odp->splitters->map(fn($splitter) => [
                'id' => $splitter->id,
                'name' => $splitter->name,
                'split_ratio' => $splitter->split_ratio,
                'port_count' => $splitter->port_count,
            ])->toArray(),
            'actions' => [
                ['label' => 'View Details', 'action' => 'viewDetails'],
                ['label' => 'Manage Splitters', 'action' => 'manageSplitters'],
            ],
        ];
    }

    private function getOnuDetails($onuId)
    {
        return [
            'type' => 'onu',
            'id' => $onuId,
            'actions' => [
                ['label' => 'View Details', 'action' => 'viewDetails'],
                ['label' => 'Diagnostics', 'action' => 'runDiagnostics'],
            ],
        ];
    }

    public function onMapMoved($center, $zoom)
    {
        $this->centerLat = $center['lat'];
        $this->centerLon = $center['lon'];
        $this->zoom = $zoom;
    }

    public function onLayersUpdated($layers)
    {
        $this->layers = $layers;
    }

    public function toggleLayer($layerName)
    {
        $this->layers[$layerName] = !$this->layers[$layerName];
    }

    public function search()
    {
        if (strlen($this->searchQuery) < 2) {
            $this->searchResults = [];
            return;
        }

        $query = strtolower($this->searchQuery);

        $olts = Olt::where('name', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'type' => 'olt',
                'name' => $o->name,
                'code' => $o->code,
                'lat' => $o->latitude,
                'lon' => $o->longitude,
            ]);

        $odps = Odp::where('name', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'type' => 'odp',
                'name' => $o->name,
                'code' => $o->code,
                'lat' => $o->latitude,
                'lon' => $o->longitude,
            ]);

        $this->searchResults = $olts->concat($odps)->toArray();
    }

    public function selectSearchResult($nodeId, $nodeType)
    {
        $this->selectedNodeId = $nodeId;
        $this->selectedNodeType = $nodeType;
        $this->loadNodeDetails($nodeId, $nodeType);
        $this->searchQuery = '';
        $this->searchResults = [];
        
        $this->dispatch('navigateToNode', nodeId: $nodeId, nodeType: $nodeType);
    }

    public function onStartRouting($fromNodeId, $toNodeId)
    {
        $this->routeFrom = $fromNodeId;
        $this->routeTo = $toNodeId;
        $this->dispatch('calculateRoute', from: $fromNodeId, to: $toNodeId);
    }

    public function onCancelRouting()
    {
        $this->routeFrom = null;
        $this->routeTo = null;
        $this->routePath = null;
    }

    public function clearSelection()
    {
        $this->selectedNodeId = null;
        $this->selectedNodeType = null;
        $this->nodeDetails = [];
    }

    public function render()
    {
        return view('livewire.gis.components.map');
    }
}
