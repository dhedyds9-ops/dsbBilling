<?php

namespace App\Livewire\NOC\Topology;

use App\Livewire\AdminComponent;
use App\Services\NOC\ImpactAnalysisService;
use App\Services\NOC\NocHealthService;
use App\Models\ISP\Olt;
use App\Models\ISP\PonPort;
use App\Models\ISP\Router;
use App\Models\Alarm;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class Index extends AdminComponent
{
    use WithPagination;

    public string $activeTab = 'olt';
    public ?int $selectedOltId    = null;
    public ?int $selectedPonId    = null;
    public ?int $selectedRouterId = null;
    public int $perPage = 20;

    // Filters for customer table
    public string $customerSearch = '';
    public string $customerServiceFilter = 'all';

    // Interactive Side Panel state
    public bool $showSidePanel = false;
    public ?array $selectedNodeDetail = null;

    protected ImpactAnalysisService $impact;

    public function boot(ImpactAnalysisService $impact): void
    {
        $this->impact = $impact;
    }

    public function configure(): void
    {
    }

    public function mount(): void
    {
        $this->activeModule = 'noc';
        $this->activePage   = 'topology';

        // Auto-select first OLT if available
        $firstOlt = Olt::withoutTrashed()->first();
        if ($firstOlt) {
            $this->selectedOltId = $firstOlt->id;
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['olt', 'pon', 'router'], true)) {
            $this->activeTab = $tab;
            $this->resetPage();
            $this->showSidePanel = false;
            $this->selectedNodeDetail = null;

            // Auto-select first available device in tab if not set
            if ($tab === 'olt' && !$this->selectedOltId) {
                $firstOlt = Olt::withoutTrashed()->first();
                if ($firstOlt) {
                    $this->selectedOltId = $firstOlt->id;
                }
            } elseif ($tab === 'pon' && !$this->selectedPonId) {
                $firstPon = PonPort::withoutTrashed()->first();
                if ($firstPon) {
                    $this->selectedPonId = $firstPon->id;
                    $this->selectedOltId = $firstPon->olt_id;
                }
            } elseif ($tab === 'router' && !$this->selectedRouterId) {
                $firstRouter = Router::withoutTrashed()->first();
                if ($firstRouter) {
                    $this->selectedRouterId = $firstRouter->id;
                }
            }
        }
    }

    public function updatedCustomerSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCustomerServiceFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedOltId(): void
    {
        $this->resetPage();
        $this->selectedNodeDetail = null;
        $this->showSidePanel = false;
        if ($this->activeTab === 'pon') {
            $firstPon = PonPort::withoutTrashed()->where('olt_id', $this->selectedOltId)->first();
            $this->selectedPonId = $firstPon?->id;
        }
    }

    public function updatedSelectedPonId(): void
    {
        $this->resetPage();
        $this->selectedNodeDetail = null;
        $this->showSidePanel = false;
    }

    public function updatedSelectedRouterId(): void
    {
        $this->resetPage();
        $this->selectedNodeDetail = null;
        $this->showSidePanel = false;
    }

    public function selectNode(string $nodeId): void
    {
        $topology = $this->topologyData;
        $nodes = $topology['nodes'] ?? [];

        foreach ($nodes as $node) {
            if ($node['id'] === $nodeId) {
                $this->selectedNodeDetail = $node;
                $this->showSidePanel = true;
                return;
            }
        }
    }

    public function closeSidePanel(): void
    {
        $this->showSidePanel = false;
        $this->selectedNodeDetail = null;
    }

    #[Computed]
    public function networkSummary(): array
    {
        $health = app(NocHealthService::class)->getNetworkHealthSummary();
        $totalDevices = ($health['olt']['total'] ?? 0) + ($health['router']['total'] ?? 0) + ($health['onu']['total'] ?? 0);
        $onlineDevices = ($health['olt']['online'] ?? 0) + ($health['router']['online'] ?? 0) + ($health['onu']['online'] ?? 0);
        $activeAlarms = $health['alarms']['open'] ?? Alarm::where('status', 'open')->count();

        $currentImpact = $this->impactResult;
        $affectedCust = $currentImpact['customer_count'] > 0
            ? $currentImpact['customer_count']
            : ($health['onu']['offline'] ?? 0);

        return [
            'total_devices'      => $totalDevices,
            'online_devices'     => $onlineDevices,
            'online_percentage'  => $totalDevices > 0 ? round(($onlineDevices / $totalDevices) * 100, 1) : 100,
            'active_alarms'      => $activeAlarms,
            'critical_alarms'    => $health['alarms']['critical'] ?? 0,
            'affected_customers' => $affectedCust,
        ];
    }

    #[Computed]
    public function olts(): \Illuminate\Database\Eloquent\Collection
    {
        return Olt::withoutTrashed()
            ->withCount(['ponPorts', 'onus'])
            ->select('id', 'name', 'code', 'status', 'ip_address', 'model')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function ponPorts(): \Illuminate\Database\Eloquent\Collection
    {
        $query = PonPort::withoutTrashed()
            ->with('olt:id,name')
            ->withCount('onus')
            ->select('id', 'name', 'port_number', 'olt_id');

        if ($this->selectedOltId) {
            $query->where('olt_id', $this->selectedOltId);
        }

        return $query->orderBy('olt_id')->orderBy('port_number')->get();
    }

    #[Computed]
    public function routers(): \Illuminate\Database\Eloquent\Collection
    {
        return Router::withoutTrashed()
            ->select('id', 'name', 'code', 'status', 'ip_address', 'model')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function impactResult(): array
    {
        return match ($this->activeTab) {
            'olt'    => $this->selectedOltId    ? $this->impact->analyzeOltImpact($this->selectedOltId)    : $this->emptyImpact('OLT'),
            'pon'    => $this->selectedPonId    ? $this->impact->analyzePonImpact($this->selectedPonId)    : $this->emptyImpact('PON Port'),
            'router' => $this->selectedRouterId ? $this->impact->analyzeRouterImpact($this->selectedRouterId) : $this->emptyImpact('Router'),
            default  => $this->emptyImpact('Device'),
        };
    }

    #[Computed]
    public function topologyData(): array
    {
        $deviceType = $this->activeTab;
        $deviceId = match ($this->activeTab) {
            'olt'    => $this->selectedOltId,
            'pon'    => $this->selectedPonId,
            'router' => $this->selectedRouterId,
            default  => null,
        };

        if (!$deviceId) {
            return ['nodes' => [], 'edges' => [], 'rca' => null];
        }

        return $this->impact->getTopologyData($deviceType, $deviceId);
    }

    #[Computed]
    public function affectedCustomers(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $deviceType = match ($this->activeTab) {
            'olt'    => $this->selectedOltId    ? 'olt'    : null,
            'pon'    => $this->selectedPonId    ? 'pon'    : null,
            'router' => $this->selectedRouterId ? 'router' : null,
            default  => null,
        };

        $deviceId = match ($this->activeTab) {
            'olt'    => $this->selectedOltId,
            'pon'    => $this->selectedPonId,
            'router' => $this->selectedRouterId,
            default  => null,
        };

        if (!$deviceType || !$deviceId) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        }

        return $this->impact->getAffectedCustomers(
            $deviceType,
            $deviceId,
            $this->perPage,
            $this->customerSearch,
            $this->customerServiceFilter
        );
    }

    private function emptyImpact(string $label): array
    {
        return [
            'device'                   => 'Select a ' . $label,
            'device_type'              => $label,
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

    #[\Livewire\Attributes\Layout('layouts.noc')]
    public function render()
    {
        return view('livewire.noc.topology.index', [
            'activeTab'             => $this->activeTab,
            'selectedOltId'         => $this->selectedOltId,
            'selectedPonId'         => $this->selectedPonId,
            'selectedRouterId'      => $this->selectedRouterId,
            'customerSearch'        => $this->customerSearch,
            'customerServiceFilter' => $this->customerServiceFilter,
            'showSidePanel'         => $this->showSidePanel,
            'selectedNodeDetail'    => $this->selectedNodeDetail,
            'networkSummary'        => $this->networkSummary,
            'olts'                  => $this->olts,
            'ponPorts'              => $this->ponPorts,
            'routers'               => $this->routers,
            'impactResult'          => $this->impactResult,
            'topologyData'          => $this->topologyData,
            'affectedCustomers'     => $this->affectedCustomers,
        ]);
    }
}
