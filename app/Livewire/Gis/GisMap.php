<?php

namespace App\Livewire\Gis;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\CRM\Customer;
use App\Models\ISP\Olt;
use App\Models\ISP\Odp;
use App\Models\ISP\Odc;
use App\Models\ISP\JointClosure;
use App\Models\ISP\DistributionBox;
use App\Services\CRM\CustomerManagementService;
use App\Services\ISP\DistributionBoxService;
use App\Services\ISP\JointClosureService;
use App\Services\ISP\OdcService;
use App\Services\ISP\OdpService;
use App\Services\ISP\OltService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class GisMap extends Component
{
    use WithPagination;

    public string $centerLat = '-6.2088';
    public string $centerLon = '106.8456';
    public int $zoom = 12;
    public bool $isLoading = false;
    
    public array $layers = [
        'olts' => true,
        'odcs' => true,
        'odps' => true,
        'htbs' => true,
        'closures' => true,
        'customers' => true,
    ];
    
    public array $selectedNodes = [];
    public ?string $selectedNodeId = null;
    public ?string $selectedNodeType = null;
    public array $nodeDetails = [];
    
    public string $searchQuery = '';
    public array $searchResults = [];

    // Node form / modal
    public bool $showNodeForm  = false;
    public bool $isEditMode    = false;
    public ?string $editNodeId = null;
    
    public string $nodeFormType = 'odp';   // olt|odc|odp|closure|customer
    public string $nodeFormName = '';
    public string $nodeFormCode = '';
    public string $nodeFormLat  = '';
    public string $nodeFormLon  = '';
    public string $nodeFormAddress = '';
    public string $nodeFormPhone = '';
    public string $nodeFormEmail = '';
    public string $nodeFormStatus  = 'active';

    // Parent selector fields
    public ?string $nodeFormOltId       = null;
    public ?string $nodeFormOdcId       = null;
    public ?string $nodeFormOdpId       = null;
    public ?string $nodeFormParentOdpId = null;
    
    // GenieACS fields for Customer
    public string $nodeFormSn = '';
    public string $nodeFormMac = '';
    public string $nodeFormSsid = '';

    public ?string $nodeFormMessage = null;
    public bool   $nodeFormSuccess  = false;

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
        'openNodeForm' => 'openNodeForm',
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
        $this->dispatch('map-data-loaded', data: $this->mapData);
    }

    public static function cacheKey(): string
    {
        return 'gis_map_data';
    }

    public static function flushCache(): void
    {
        Cache::forget(self::cacheKey());
    }

    protected function canWriteGis(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if ($user->hasRole(\App\Enums\UserRole::Administrator->value) || $user->hasRole(\App\Enums\UserRole::Manager->value)) return true;
        if (\Gate::has('*') && \Gate::allows('*')) return true;
        return false;
    }

    public function getMapDataProperty()
    {
        return Cache::remember(self::cacheKey(), 120, function () {
            return [
                'olts' => Olt::with(['ponPorts'])->get()->map(function ($olt) {
                    $total = max(1, $olt->pon_port_count ?? 1);
                    $used = $olt->active_port_count ?? 0;
                    return [
                        'id' => $olt->id,
                        'name' => $olt->name,
                        'code' => $olt->code,
                        'lat' => (float) $olt->latitude,
                        'lon' => (float) $olt->longitude,
                        'status' => $olt->status,
                        'utilization' => (int) round(($used / $total) * 100),
                        'pon_port_count' => $olt->ponPorts->count(),
                    ];
                })->toArray(),

                'odcs' => Odc::get()->map(function ($odc) {
                    return [
                        'id' => $odc->id,
                        'olt_id' => $odc->olt_id,
                        'name' => $odc->name,
                        'code' => $odc->code,
                        'lat' => (float) $odc->latitude,
                        'lon' => (float) $odc->longitude,
                        'status' => $odc->status,
                    ];
                })->toArray(),
                
                'odps' => Odp::with(['splitters'])->get()->map(function ($odp) {
                    $total = max(1, $odp->port_count ?? 1);
                    $used = $odp->used_port_count ?? 0;
                    return [
                        'id' => $odp->id,
                        'odc_id' => $odp->odc_id,
                        'olt_id' => $odp->olt_id,
                        'parent_odp_id' => $odp->parent_odp_id,
                        'name' => $odp->name,
                        'code' => $odp->code,
                        'lat' => (float) $odp->latitude,
                        'lon' => (float) $odp->longitude,
                        'status' => $odp->status,
                        'utilization' => (int) round(($used / $total) * 100),
                        'splitter_count' => $odp->splitters->count(),
                    ];
                })->toArray(),

                'htbs' => DistributionBox::get()->map(function ($htb) {
                    return [
                        'id' => $htb->id,
                        'name' => $htb->name,
                        'code' => $htb->code,
                        'lat' => (float) $htb->latitude,
                        'lon' => (float) $htb->longitude,
                        'status' => $htb->status,
                    ];
                })->toArray(),

                'closures' => JointClosure::get()->map(function ($closure) {
                    return [
                        'id' => $closure->id,
                        'name' => $closure->name,
                        'code' => $closure->code,
                        'lat' => (float) $closure->latitude,
                        'lon' => (float) $closure->longitude,
                        'status' => $closure->status,
                    ];
                })->toArray(),
                
                'onus' => \App\Models\ISP\Onu::whereNotNull('latitude')->whereNotNull('longitude')->get()->map(function ($onu) {
                    return [
                        'id' => $onu->id,
                        'odp_id' => $onu->odp_id,
                        'name' => $onu->name,
                        'sn' => $onu->serial_number,
                        'mac' => $onu->mac_address,
                        'ssid' => $onu->wifi_ssid,
                        'lat' => (float) $onu->latitude,
                        'lon' => (float) $onu->longitude,
                        'status' => $onu->status,
                    ];
                })->toArray(),

                'customers' => Customer::with(['customerServices.onu'])
                    ->when(auth()->user()->hasRole('reseller'), function($q) {
                        $q->where(function($qq) {
                            $qq->where('reseller_id', auth()->id())
                               ->orWhere('created_by', auth()->id());
                        });
                    })
                    ->whereNotNull('latitude')->whereNotNull('longitude')->get()->map(function ($member) {
                    // Coba ambil info ONU pertama dari layanan pelanggan ini (jika ada)
                    $cs = $member->customerServices->first();
                    $onu = $cs ? $cs->onu : null;
                    return [
                        'id' => $member->id,
                        'odp_id' => $onu ? $onu->odp_id : null,
                        'name' => $member->name,
                        'code' => $member->code,
                        'lat' => (float) $member->latitude,
                        'lon' => (float) $member->longitude,
                        'status' => $member->status,
                        'sn' => $onu ? $onu->serial_number : null,
                        'mac' => $onu ? $onu->mac_address : null,
                        'ssid' => $onu ? $onu->wifi_ssid : null,
                    ];
                })->toArray(),
            ];
        });
    }

    #[On('nodeClicked')]
    public function onNodeClicked($nodeId, $nodeType = null)
    {
        if (is_array($nodeId)) {
            $nodeType = $nodeId['nodeType'] ?? null;
            $nodeId = $nodeId['nodeId'] ?? null;
        }
        $this->selectedNodeId = $nodeId;
        $this->selectedNodeType = $nodeType;
        $this->loadNodeDetails($nodeId, $nodeType);
        $this->dispatch('showNodeDetails', nodeId: $nodeId, nodeType: $nodeType);
    }

    public function loadNodeDetails($nodeId, $nodeType)
    {
        $this->nodeDetails = match($nodeType) {
            'olt' => $this->getOltDetails($nodeId),
            'odc' => $this->getOdcDetails($nodeId),
            'odp' => $this->getOdpDetails($nodeId),
            'htb' => $this->getHtbDetails($nodeId),
            'closure' => $this->getClosureDetails($nodeId),
            'customer' => $this->getCustomerDetails($nodeId),
            'onu' => $this->getOnuDetails($nodeId),
            default => [],
        };
    }

    public function editNode($nodeId, $nodeType)
    {
        $this->selectedNodeId = $nodeId;
        $this->selectedNodeType = $nodeType;
        $this->loadNodeDetails($nodeId, $nodeType);

        $this->isEditMode = true;
        $this->editNodeId = $nodeId;
        $this->nodeFormType = $nodeType;
        
        $this->nodeFormName = $this->nodeDetails['name'] ?? '';
        $this->nodeFormCode = $this->nodeDetails['code'] ?? '';
        $this->nodeFormStatus = $this->nodeDetails['status'] ?? 'active';
        $this->nodeFormAddress = $this->nodeDetails['address'] ?? '';
        $this->nodeFormPhone = $this->nodeDetails['phone'] ?? '';
        $this->nodeFormEmail = $this->nodeDetails['email'] ?? '';

        // Reset parent
        $this->nodeFormOltId = null;
        $this->nodeFormOdcId = null;
        $this->nodeFormOdpId = null;
        $this->nodeFormParentOdpId = null;

        // Ambil parent id dari original record agar tetap sinkron
        $original = match($nodeType) {
            'olt'     => Olt::find($nodeId),
            'odc'     => Odc::find($nodeId),
            'odp'     => Odp::find($nodeId),
            'htb'     => DistributionBox::find($nodeId),
            'closure' => JointClosure::find($nodeId),
            default   => null,
        };

        if ($original) {
            $this->nodeFormOltId = (string) ($original->olt_id ?? '');
            $this->nodeFormOdcId = (string) ($original->odc_id ?? '');
            if ($nodeType === 'odp') {
                $this->nodeFormParentOdpId = (string) ($original->parent_odp_id ?? '');
            }
            if ($nodeType === 'htb' || $nodeType === 'customer') {
                $this->nodeFormOdpId = (string) ($original->odp_id ?? '');
            }
        }

        // Find coordinate from original map data
        $this->nodeFormLat = '';
        $this->nodeFormLon = '';
        $cache = Cache::get(self::cacheKey(), []);
        $layerKey = $nodeType === 'customer' ? 'customers' : $nodeType . 's';
        if (isset($cache[$layerKey])) {
            foreach ($cache[$layerKey] as $n) {
                if ($n['id'] == $nodeId) {
                    $this->nodeFormLat = $n['lat'];
                    $this->nodeFormLon = $n['lon'];
                    
                    if ($nodeType === 'customer') {
                        $this->nodeFormSn = $n['sn'] ?? '';
                        $this->nodeFormMac = $n['mac'] ?? '';
                        $this->nodeFormSsid = $n['ssid'] ?? '';
                    }
                    break;
                }
            }
        }
                $this->showNodeForm = true;
        $this->resetValidation();
        $this->nodeFormMessage = null;
        $this->nodeFormSuccess = false;
    }

    public function deleteNode($nodeId, $nodeType)
    {
        if (!$this->canWriteGis()) {
            $this->clearSelection();
            return;
        }

        $user = auth()->user();
        if (!$user) return;

        $record = match($nodeType) {
            'olt'     => Olt::find($nodeId),
            'odc'     => Odc::find($nodeId),
            'odp'     => Odp::find($nodeId),
            'htb'     => DistributionBox::find($nodeId),
            'closure' => JointClosure::find($nodeId),
            'customer'=> Customer::find($nodeId),
            default   => null,
        };

        if (!$record) {
            $this->clearSelection();
            return;
        }

        try {
            match($nodeType) {
                'olt'     => app(OltService::class)->delete($record, $user),
                'odc'     => app(OdcService::class)->delete($record, $user),
                'odp'     => app(OdpService::class)->delete($record, $user),
                'htb'     => app(DistributionBoxService::class)->delete($record, $user),
                'closure' => app(JointClosureService::class)->delete($record, $user),
                'customer'=> app(CustomerManagementService::class)->delete($record, $user),
                default   => null,
            };
        } catch (\Throwable $e) {
            report($e);
            return;
        }

        self::flushCache();
        $this->clearSelection();
        $this->dispatch('nodeDeleted', id: $nodeId, type: $nodeType);
    }

    private function getOltDetails($oltId)
    {
        $olt = Olt::with(['ponPorts'])->find($oltId);
        
        if (!$olt) {
            return [];
        }

        $total = max(1, $olt->pon_port_count ?? 1);
        $used = $olt->active_port_count ?? 0;

        return [
            'type' => 'olt',
            'id' => $olt->id,
            'name' => $olt->name,
            'code' => $olt->code,
            'status' => $olt->status,
            'location' => $olt->address ?? 'Unknown',
            'capacity' => [
                'total' => $olt->pon_port_count ?? 0,
                'used' => $used,
                'utilization' => (int) round(($used / $total) * 100),
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
        $odp = Odp::with(['splitters'])->find($odpId);
        
        if (!$odp) {
            return [];
        }

        $total = max(1, $odp->port_count ?? 1);
        $used = $odp->used_port_count ?? 0;

        return [
            'type' => 'odp',
            'id' => $odp->id,
            'name' => $odp->name,
            'code' => $odp->code,
            'status' => $odp->status,
            'location' => $odp->address ?? 'Unknown',
            'capacity' => [
                'total' => $odp->port_count ?? 0,
                'used' => $used,
                'utilization' => (int) round(($used / $total) * 100),
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

    private function getOdcDetails($odcId)
    {
        $odc = Odc::find($odcId);

        if (!$odc) {
            return [];
        }

        $total = (int) ($odc->port_count ?? 0);
        $used = (int) ($odc->active_port_count ?? 0);
        $utilization = $total > 0 ? (int) round(min(100, ($used / max(1, $total)) * 100)) : 0;

        return [
            'type' => 'odc',
            'id' => $odc->id,
            'name' => $odc->name,
            'code' => $odc->code,
            'status' => $odc->status,
            'capacity' => [
                'total' => $total,
                'used' => $used,
                'utilization' => $utilization,
            ],
        ];
    }

    private function getHtbDetails($htbId)
    {
        $htb = DistributionBox::find($htbId);

        if (!$htb) {
            return [];
        }

        $total = (int) ($htb->port_count ?? 0);
        $used = (int) ($htb->active_port_count ?? 0);
        $utilization = $total > 0 ? (int) round(min(100, ($used / max(1, $total)) * 100)) : 0;

        return [
            'type' => 'htb',
            'id' => $htb->id,
            'name' => $htb->name,
            'code' => $htb->code,
            'status' => $htb->status,
            'capacity' => [
                'total' => $total,
                'used' => $used,
                'utilization' => $utilization,
            ],
        ];
    }

    private function getClosureDetails($closureId)
    {
        $closure = JointClosure::find($closureId);

        if (!$closure) {
            return [];
        }

        $total = (int) ($closure->port_count ?? 0);

        return [
            'type' => 'closure',
            'id' => $closure->id,
            'name' => $closure->name,
            'code' => $closure->code,
            'status' => $closure->status,
            'capacity' => [
                'total' => $total,
                'used' => 0,
                'utilization' => 0,
            ],
        ];
    }

    private function getCustomerDetails($customerId)
    {
        $customer = Customer::find($customerId);

        if (!$customer) {
            return [];
        }

        return [
            'type' => 'customer',
            'id' => $customer->id,
            'name' => $customer->name,
            'code' => $customer->code,
            'status' => $customer->status,
            'capacity' => [
                'total' => 1,
                'used' => 1,
                'utilization' => 100,
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

    // Auto-triggered when searchQuery changes (Livewire updated hook)
    public function updatedSearchQuery()
    {
        $this->search();
    }

    public function search()
    {
        if (strlen($this->searchQuery) < 2) {
            $this->searchResults = [];
            return;
        }

        $query = strtolower($this->searchQuery);

        $olts = Olt::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%");
            })
            ->limit(5)->get()
            ->map(fn($o) => ['id'=>$o->id,'type'=>'olt','name'=>$o->name,'code'=>$o->code,'lat'=>$o->latitude,'lon'=>$o->longitude]);

        $odps = Odp::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%");
            })
            ->limit(5)->get()
            ->map(fn($o) => ['id'=>$o->id,'type'=>'odp','name'=>$o->name,'code'=>$o->code,'lat'=>$o->latitude,'lon'=>$o->longitude]);

        $odcs = Odc::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%");
            })
            ->limit(3)->get()
            ->map(fn($o) => ['id'=>$o->id,'type'=>'odc','name'=>$o->name,'code'=>$o->code,'lat'=>$o->latitude,'lon'=>$o->longitude]);

        $htbs = DistributionBox::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%");
            })
            ->limit(3)->get()
            ->map(fn($o) => ['id'=>$o->id,'type'=>'htb','name'=>$o->name,'code'=>$o->code,'lat'=>$o->latitude,'lon'=>$o->longitude]);

        $closures = JointClosure::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%");
            })
            ->limit(3)->get()
            ->map(fn($o) => ['id'=>$o->id,'type'=>'closure','name'=>$o->name,'code'=>$o->code,'lat'=>$o->latitude,'lon'=>$o->longitude]);

        $customers = Customer::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%");
            })
            ->limit(5)->get()
            ->map(fn($o) => ['id'=>$o->id,'type'=>'customer','name'=>$o->name,'code'=>$o->code,'lat'=>$o->latitude,'lon'=>$o->longitude]);

        $this->searchResults = $olts
            ->concat($odps)
            ->concat($odcs)
            ->concat($htbs)
            ->concat($closures)
            ->concat($customers)
            ->toArray();
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

    // ---- Node Form ----
    #[On('openNodeForm')]
    public function openNodeForm($lat = '', $lon = '', $type = 'odp'): void
    {
        if (is_array($lat)) {
            $type = $lat['type'] ?? 'odp';
            $lon = $lat['lon'] ?? '';
            $lat = $lat['lat'] ?? '';
        }
        
        $this->isEditMode = false;
        $this->editNodeId = null;
        
        $this->resetValidation();
        $this->resetErrorBag();
        $this->nodeFormLat     = $lat;
        $this->nodeFormLon     = $lon;
        $this->nodeFormType    = $type;
        $this->nodeFormName    = '';
        $this->nodeFormCode    = '';
        $this->nodeFormAddress = '';
        $this->nodeFormPhone   = '';
        $this->nodeFormEmail   = '';
        $this->nodeFormStatus  = 'active';
        $this->nodeFormOltId       = null;
        $this->nodeFormOdcId       = null;
        $this->nodeFormOdpId       = null;
        $this->nodeFormParentOdpId = null;
        $this->nodeFormMessage = null;
        $this->nodeFormSuccess = false;
        $this->showNodeForm    = true;
    }

    public function closeNodeForm(): void
    {
        $this->resetValidation();
        $this->resetErrorBag();
        $this->showNodeForm    = false;
        $this->nodeFormMessage = null;
        $this->nodeFormSuccess = false;
    }

    public function saveNode(): void
    {
        if (!$this->canWriteGis()) {
            $this->addError('nodeFormName', 'Anda tidak memiliki izin untuk memodifikasi peta GIS.');
            return;
        }

        $user = auth()->user();
        if (!$user) {
            $this->addError('nodeFormName', 'Sesi login tidak ditemukan. Muat ulang halaman lalu coba lagi.');
            return;
        }

        $table = match ($this->nodeFormType) {
            'olt' => 'olts',
            'odc' => 'odcs',
            'odp' => 'odps',
            'htb' => 'distribution_boxes',
            'closure' => 'joint_closures',
            'customer' => 'members',
            default => 'olts',
        };

        $rules = [
            'nodeFormType'    => 'required|in:olt,odc,odp,htb,closure,customer',
            'nodeFormName'    => 'required|string|min:2|max:100',
            'nodeFormLat'     => ['required', 'regex:/^-?\d{1,2}(\.\d+)?$/'],
            'nodeFormLon'     => ['required', 'regex:/^-?\d{1,3}(\.\d+)?$/'],
            'nodeFormStatus'  => 'required|in:active,inactive,suspended,terminated',
            'nodeFormOltId'       => 'nullable|exists:olts,id',
            'nodeFormOdcId'       => 'nullable|exists:odcs,id',
            'nodeFormOdpId'       => 'nullable|exists:odps,id',
            'nodeFormParentOdpId' => 'nullable|exists:odps,id',
        ];

        if ($this->isEditMode) {
            $rules['nodeFormCode'] = ['required', 'string', 'max:50', Rule::unique($table, 'code')->ignore($this->editNodeId)];
        } else {
            $rules['nodeFormCode'] = ['required', 'string', 'max:50', Rule::unique($table, 'code')];
        }

        if ($this->nodeFormType === 'customer') {
            $rules['nodeFormPhone'] = ['required', 'numeric', 'digits_between:10,15', Rule::unique('members', 'phone')->ignore($this->editNodeId)];
            $rules['nodeFormEmail'] = ['nullable', 'email', 'max:255', Rule::unique('members', 'email')->ignore($this->editNodeId)];
        }

        $this->validate($rules, [
            'nodeFormName.required'  => 'Nama wajib diisi',
            'nodeFormCode.required'  => 'Kode wajib diisi',
            'nodeFormLat.required'   => 'Latitude wajib diisi',
            'nodeFormLon.required'   => 'Longitude wajib diisi',
            'nodeFormLat.regex'      => 'Format latitude tidak valid (contoh: -6.2088)',
            'nodeFormLon.regex'      => 'Format longitude tidak valid (contoh: 106.8456)',
            'nodeFormPhone.required' => 'No. HP wajib diisi untuk pelanggan',
            'nodeFormPhone.numeric'  => 'No. HP pelanggan harus berupa angka',
            'nodeFormPhone.unique'   => 'No. HP pelanggan sudah digunakan',
            'nodeFormEmail.unique'   => 'Email pelanggan sudah digunakan',
        ]);

        $common = [
            'code'      => $this->nodeFormCode,
            'name'      => $this->nodeFormName,
            'latitude'  => $this->nodeFormLat,
            'longitude' => $this->nodeFormLon,
            'address'   => $this->nodeFormAddress,
            'status'    => $this->nodeFormStatus,
        ];

        if ($this->nodeFormOltId)   $common['olt_id']   = $this->nodeFormOltId;
        if ($this->nodeFormOdcId)   $common['odc_id']   = $this->nodeFormOdcId;
        if ($this->nodeFormType === 'odp' && $this->nodeFormParentOdpId) $common['parent_odp_id'] = $this->nodeFormParentOdpId;
        if ($this->nodeFormType === 'htb' && $this->nodeFormOdpId)       $common['odp_id']         = $this->nodeFormOdpId;
        
        $record = null;

        if ($this->isEditMode) {
            // Logika Update (Edit) via Service agar audit log + updated_by tercatat
            $record = match ($this->nodeFormType) {
                'customer' => (function () use ($user) {
                    $r = Customer::find($this->editNodeId);
                    if (!$r) return null;
                    return app(CustomerManagementService::class)->updateFromMap($r, [
                        'code'      => $this->nodeFormCode,
                        'name'      => $this->nodeFormName,
                        'phone'     => $this->nodeFormPhone,
                        'email'     => $this->nodeFormEmail,
                        'address'   => $this->nodeFormAddress,
                        'latitude'  => $this->nodeFormLat,
                        'longitude' => $this->nodeFormLon,
                        'status'    => $this->nodeFormStatus,
                        'sn'        => $this->nodeFormSn,
                        'mac'       => $this->nodeFormMac,
                        'ssid'      => $this->nodeFormSsid,
                    ], $user);
                })(),
                'olt'     => ($r = Olt::find($this->editNodeId)) ? app(OltService::class)->update($r, $common, $user) : null,
                'odc'     => ($r = Odc::find($this->editNodeId)) ? app(OdcService::class)->update($r, $common, $user) : null,
                'odp'     => ($r = Odp::find($this->editNodeId)) ? app(OdpService::class)->update($r, $common, $user) : null,
                'htb'     => ($r = DistributionBox::find($this->editNodeId)) ? app(DistributionBoxService::class)->update($r, $common, $user) : null,
                'closure' => ($r = JointClosure::find($this->editNodeId)) ? app(JointClosureService::class)->update($r, $common, $user) : null,
                default   => null,
            };
        } else {
            // Logika Tambah (Create)
            $record = match ($this->nodeFormType) {
                'olt' => app(OltService::class)->create($common, $user),
                'odc' => app(OdcService::class)->create($common, $user),
                'odp' => app(OdpService::class)->create(array_merge($common, [
                    'port_count' => 16,
                    'split_ratio' => 16,
                ]), $user),
                'htb' => app(DistributionBoxService::class)->create(array_merge($common, [
                    'port_count' => 8,
                ]), $user),
                'closure' => app(JointClosureService::class)->create(array_merge($common, [
                    'type' => 'joint',
                ]), $user),
                'customer' => app(CustomerManagementService::class)->createFromMap([
                    'code' => $this->nodeFormCode,
                    'name' => $this->nodeFormName,
                    'phone' => $this->nodeFormPhone,
                    'email' => $this->nodeFormEmail,
                    'address' => $this->nodeFormAddress,
                    'latitude' => $this->nodeFormLat,
                    'longitude' => $this->nodeFormLon,
                    'status' => $this->nodeFormStatus,
                ], $user),
            };
        }

        self::flushCache();

        $this->resetValidation();
        $this->resetErrorBag();
        $this->nodeFormMessage = 'Node berhasil disimpan!';
        $this->nodeFormSuccess = true;
        
        if ($record) {
            $this->dispatch('nodeAdded',
                id:   $record->id,
                lat:  $this->nodeFormLat,
                lon:  $this->nodeFormLon,
                type: $this->nodeFormType,
                name: $this->nodeFormName,
                code: $this->nodeFormCode,
                status: $this->nodeFormStatus,
                phone: $this->nodeFormPhone,
                email: $this->nodeFormEmail,
            );
        }
    }

    public function render()
    {
        return view('livewire.gis.components.map', [
            'customers' => Customer::with(['customerServices.onu'])
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get()
                ->map(function ($c) {
                    $service = $c->customerServices->first();
                    $onu = $service?->onu;
                    
                    // Inject properties expected by map JS
                    $c->is_online = $service?->status === 'active';
                    $c->odp_id = $onu?->odp_id;
                    $c->tr069_ip = $service?->tr069_status ?? null;
                    $c->ssid_name = $onu?->wifi_ssid ?? 'N/A';
                    $c->last_inform = $onu?->last_seen_at ?? $service?->last_seen_at;
                    $c->last_reason = $service?->offline_reason ?? '-';
                    $c->has_genie_status = (bool) $c->tr069_ip;
                    
                    return $c;
                }),
            'odps' => \App\Models\ISP\Odp::all(),
            'htbs' => class_exists(\App\Models\ISP\DistributionBox::class) ? \App\Models\ISP\DistributionBox::all() : collect([]),
            'closures' => class_exists(\App\Models\ISP\JointClosure::class) ? \App\Models\ISP\JointClosure::all() : collect([]),
            'odcs' => \App\Models\ISP\Odc::all(),
            'olts' => \App\Models\ISP\Olt::all(),
            'regions' => (class_exists(\App\Models\GIS\GeoArea::class) && \Illuminate\Support\Facades\Schema::hasTable((new \App\Models\GIS\GeoArea)->getTable())) ? \App\Models\GIS\GeoArea::all() : collect([]),
            'coordinators' => class_exists(\App\Models\User::class) ? \App\Models\User::all() : collect([]),
            'parentOptions' => [
                'olts' => \App\Models\ISP\Olt::orderBy('name')->get(['id', 'name', 'code']),
                'odcs' => \App\Models\ISP\Odc::orderBy('name')->get(['id', 'name', 'code']),
                'odps' => \App\Models\ISP\Odp::orderBy('name')->get(['id', 'name', 'code']),
            ],
            'assets' => class_exists(\App\Models\Inventory\Asset::class) ? \App\Models\Inventory\Asset::whereNotNull('latitude')->whereNotNull('longitude')->get() : collect([]),
            'modemDataRecords' => collect([]),
            'coordinatorRegionId' => auth()->user()?->region_id ?? null,
            'canManageMap' => true,
            'canEditCustomer' => true,
            'isAdmin' => true,
        ])->title('Peta')->layout('layouts.noc');
    }
}
