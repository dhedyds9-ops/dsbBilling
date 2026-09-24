<?php

namespace App\Livewire\Gis;

use Livewire\Component;
use App\Models\ISP\Olt;
use App\Models\ISP\Odp;
use App\Models\ISP\OltPort;
use Illuminate\Support\Facades\Cache;

class NodeDetails extends Component
{
    public ?string $nodeId = null;
    public ?string $nodeType = null;
    public array $nodeData = [];
    public array $relatedNodes = [];
    public array $performanceMetrics = [];
    public bool $isLoading = false;
    public string $activeTab = 'overview';

    protected $listeners = [
        'showNodeDetails',
        'refreshNodeDetails',
    ];

    public function showNodeDetails($nodeId, $nodeType)
    {
        $this->nodeId = $nodeId;
        $this->nodeType = $nodeType;
        $this->loadNodeData();
    }

    public function refreshNodeDetails()
    {
        if ($this->nodeId && $this->nodeType) {
            $this->loadNodeData();
        }
    }

    public function loadNodeData()
    {
        $this->isLoading = true;

        $this->nodeData = match($this->nodeType) {
            'olt' => $this->loadOltData($this->nodeId),
            'odp' => $this->loadOdpData($this->nodeId),
            'fiber' => $this->loadFiberData($this->nodeId),
            'customer' => $this->loadCustomerData($this->nodeId),
            default => [],
        };

        $this->loadPerformanceMetrics();
        
        $this->isLoading = false;
    }

    private function loadOltData($oltId)
    {
        $olt = Olt::with(['ponPorts.onus', 'capacity', 'location'])->find($oltId);

        if (!$olt) {
            return [];
        }

        return [
            'type' => 'olt',
            'id' => $olt->id,
            'name' => $olt->name,
            'code' => $olt->code,
            'ip_address' => $olt->ip_address,
            'model' => $olt->model,
            'status' => $olt->status,
            'location' => [
                'name' => $olt->location?->name ?? 'Unknown',
                'address' => $olt->location?->address ?? '',
                'lat' => $olt->latitude,
                'lon' => $olt->longitude,
            ],
            'capacity' => [
                'total_ports' => $olt->capacity?->totalPonPorts ?? 0,
                'used_ports' => $olt->capacity?->usedPonPorts ?? 0,
                'utilization' => $olt->capacity?->getUtilizationPercentage() ?? 0,
            ],
            'pon_ports' => $olt->ponPorts->map(fn($port) => [
                'id' => $port->id,
                'port_number' => $port->port_number,
                'status' => $port->status,
                'type' => $port->type,
                'onu_count' => $port->onus->count() ?? 0,
                'onu_details' => $port->onus->take(5)->map(fn($onu) => [
                    'id' => $onu->id,
                    'mac_address' => $onu->mac_address,
                    'status' => $onu->status,
                    'signal_strength' => $onu->signal_strength ?? null,
                ])->toArray(),
            ])->toArray(),
            'created_at' => $olt->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $olt->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function loadOdpData($odpId)
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
            'location' => [
                'name' => $odp->location?->name ?? 'Unknown',
                'address' => $odp->location?->address ?? '',
                'lat' => $odp->latitude,
                'lon' => $odp->longitude,
            ],
            'capacity' => [
                'total_ports' => $odp->capacity?->totalPorts ?? 0,
                'used_ports' => $odp->capacity?->usedPorts ?? 0,
                'utilization' => $odp->capacity?->getUtilizationPercentage() ?? 0,
            ],
            'splitters' => $odp->splitters->map(fn($splitter) => [
                'id' => $splitter->id,
                'name' => $splitter->name,
                'split_ratio' => $splitter->split_ratio,
                'port_count' => $splitter->port_count,
                'used_ports' => $splitter->used_ports ?? 0,
            ])->toArray(),
            'created_at' => $odp->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $odp->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function loadFiberData($fiberId)
    {
        return [
            'type' => 'fiber',
            'id' => $fiberId,
        ];
    }

    private function loadCustomerData($customerId)
    {
        return [
            'type' => 'customer',
            'id' => $customerId,
        ];
    }

    private function loadPerformanceMetrics()
    {
        $this->performanceMetrics = Cache::remember("node_metrics_{$this->nodeId}", 60, function () {
            return [
                'uptime' => '99.95%',
                'avg_latency' => '2.5ms',
                'packet_loss' => '0.01%',
                'bandwidth_usage' => '65%',
                'last_24h' => [
                    'availability' => 99.99,
                    'incidents' => 0,
                    'avg_response' => 15,
                ],
            ];
        });
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function close()
    {
        $this->dispatch('closeNodeDetails');
    }

    public function render()
    {
        return view('livewire.gis.node-details');
    }
}
