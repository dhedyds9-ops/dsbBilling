<?php

namespace App\Livewire\NOC\Topology;

use App\Livewire\AdminComponent;
use App\Services\NOC\ImpactAnalysisService;
use App\Models\ISP\Olt;
use App\Models\ISP\PonPort;
use App\Models\ISP\Router;
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
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['olt', 'pon', 'router'], true)) {
            $this->activeTab = $tab;
            $this->resetPage();
        }
    }

    public function updatedSelectedOltId(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedPonId(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedRouterId(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function olts(): \Illuminate\Database\Eloquent\Collection
    {
        return Olt::withoutTrashed()->select('id', 'name', 'code')->orderBy('name')->get();
    }

    #[Computed]
    public function ponPorts(): \Illuminate\Database\Eloquent\Collection
    {
        if (!$this->selectedOltId) {
            return PonPort::withoutTrashed()
                ->with('olt:id,name')
                ->select('id', 'name', 'port_number', 'olt_id')
                ->orderBy('olt_id')
                ->orderBy('port_number')
                ->get();
        }

        return PonPort::withoutTrashed()
            ->where('olt_id', $this->selectedOltId)
            ->select('id', 'name', 'port_number', 'olt_id')
            ->orderBy('port_number')
            ->get();
    }

    #[Computed]
    public function routers(): \Illuminate\Database\Eloquent\Collection
    {
        return Router::withoutTrashed()->select('id', 'name', 'code')->orderBy('name')->get();
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

        return $this->impact->getAffectedCustomers($deviceType, $deviceId, $this->perPage);
    }

    private function emptyImpact(string $label): array
    {
        return [
            'device'         => 'Select a ' . $label,
            'device_type'    => $label,
            'pon_count'      => 0,
            'onu_count'      => 0,
            'customer_count' => 0,
            'service_count'  => 0,
            'breakdown'      => ['pppoe' => 0, 'hotspot' => 0, 'other' => 0],
        ];
    }

    #[\Livewire\Attributes\Layout('layouts.noc')]
    public function render()
    {
        return view('livewire.noc.topology.index', [
            'olts'              => $this->olts,
            'ponPorts'          => $this->ponPorts,
            'routers'           => $this->routers,
            'impactResult'      => $this->impactResult,
            'affectedCustomers' => $this->affectedCustomers,
        ]);
    }
}



