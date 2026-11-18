<?php

namespace App\Livewire\Isp\Odc;

use App\Livewire\AdminComponent;
use App\Models\ISP\Odc as OdcModel;
use App\Services\ISP\OdcTopologyService;
use Livewire\Attributes\Computed;

class Show extends AdminComponent
{
    public $odcId;
    public $odc;
    public string $activeTab = 'overview';

    // Filters & Search
    public string $search = '';
    public string $portStatusFilter = 'all';
    public string $customerServiceFilter = 'all';

    // Interactive Topology State
    public ?string $selectedNodeId = null;
    public ?array $selectedNode = null;
    public bool $showNodeDrawer = false;
    public string $traceQuery = '';
    public ?string $tracedNodeId = null;

    // Port Connection Action Modal State
    public bool $showPortModal = false;
    public ?int $targetPortNumber = null;
    public ?int $selectedOdpIdForPort = null;

    protected OdcTopologyService $topologyService;

    public function boot(OdcTopologyService $topologyService): void
    {
        $this->topologyService = $topologyService;
    }

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'odcs';
        $this->odcId = $id;

        $this->odc = OdcModel::withoutGlobalScopes()
            ->with(['olt.pop', 'pop', 'odps', 'splitters', 'branch', 'createdBy'])
            ->findOrFail($id);

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.odcs.index')],
            ['label' => 'ODC', 'url' => route('isp.odcs.index')],
            ['label' => $this->odc->name],
        ];

        // Read query parameter for initial tab if provided
        $tabParam = request()->query('tab');
        if ($tabParam && in_array($tabParam, ['overview', 'topology', 'ports', 'odps', 'customers', 'history'], true)) {
            $this->activeTab = $tabParam;
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['overview', 'topology', 'ports', 'odps', 'customers', 'history'], true)) {
            $this->activeTab = $tab;
            $this->search = '';
            $this->showNodeDrawer = false;
            $this->selectedNode = null;
        }
    }

    public function selectNode(string $nodeId): void
    {
        $this->selectedNodeId = $nodeId;
        $topo = $this->topologyData;
        
        foreach ($topo['nodes'] as $n) {
            if ($n['id'] === $nodeId) {
                $this->selectedNode = $n;
                $this->showNodeDrawer = true;
                return;
            }
        }
    }

    public function closeNodeDrawer(): void
    {
        $this->showNodeDrawer = false;
        $this->selectedNode = null;
        $this->selectedNodeId = null;
    }

    public function tracePath(): void
    {
        $query = trim(strtolower($this->traceQuery));
        if (empty($query)) {
            $this->clearTrace();
            return;
        }

        $topo = $this->topologyData;
        foreach ($topo['nodes'] as $n) {
            $label = strtolower($n['label'] ?? '');
            $code = strtolower($n['metadata']['code'] ?? '');
            $sn = strtolower($n['metadata']['serial_number'] ?? '');
            $name = strtolower($n['metadata']['name'] ?? '');

            if (str_contains($label, $query) || str_contains($code, $query) || str_contains($sn, $query) || str_contains($name, $query)) {
                $this->tracedNodeId = $n['id'];
                $this->selectNode($n['id']);
                $this->dispatch('topology-focus-node', nodeId: $n['id']);
                return;
            }
        }

        session()->flash('topology_warning', "Perangkat atau pelanggan dengan kata kunci \"{$this->traceQuery}\" tidak ditemukan pada ODC ini.");
    }

    public function clearTrace(): void
    {
        $this->traceQuery = '';
        $this->tracedNodeId = null;
        $this->dispatch('topology-reset-focus');
    }

    public function delete()
    {
        $this->odc->delete();
        session()->flash('success', 'ODC berhasil dihapus.');
        return redirect()->route('isp.odcs.index');
    }

    public function deletePhoto(int $index)
    {
        $photos = is_array($this->odc->photos) ? $this->odc->photos : [];
        if (isset($photos[$index])) {
            unset($photos[$index]);
            $this->odc->photos = array_values($photos);
            $this->odc->save();
            session()->flash('success', 'Foto dokumentasi ODC berhasil dihapus.');
        }
    }

    public function openPortModal(int $portNumber): void
    {
        $this->targetPortNumber = $portNumber;
        $this->selectedOdpIdForPort = null;
        $this->showPortModal = true;
    }

    public function closePortModal(): void
    {
        $this->showPortModal = false;
        $this->targetPortNumber = null;
        $this->selectedOdpIdForPort = null;
    }

    public function assignPort(): void
    {
        if (!$this->selectedOdpIdForPort || !$this->targetPortNumber) {
            return;
        }

        $odp = \App\Models\ISP\Odp::withoutGlobalScopes()->find($this->selectedOdpIdForPort);
        if ($odp) {
            $odp->update([
                'odc_id' => $this->odc->id,
                'odc_port' => $this->targetPortNumber,
            ]);
            $this->odc->refresh();
            $this->closePortModal();
            session()->flash('success', "ODP {$odp->name} berhasil dihubungkan ke Port #{$this->targetPortNumber}.");
        }
    }

    public function disconnectPort(int $portNumber): void
    {
        $odp = \App\Models\ISP\Odp::withoutGlobalScopes()
            ->where('odc_id', $this->odc->id)
            ->where('odc_port', $portNumber)
            ->first();

        if ($odp) {
            $odp->update(['odc_port' => null]);
            $this->odc->refresh();
            session()->flash('success', "Koneksi ODP {$odp->name} dari Port #{$portNumber} berhasil dilepas.");
        }
    }

    #[Computed]
    public function availableOdps()
    {
        return \App\Models\ISP\Odp::withoutGlobalScopes()
            ->where(function($q) {
                $q->whereNull('odc_id')
                  ->orWhere('odc_id', $this->odc->id);
            })
            ->where(function($q) {
                $q->whereNull('odc_port')
                  ->orWhere('odc_port', 0);
            })
            ->orderBy('name')
            ->get(['id', 'code', 'name']);
    }

    #[Computed]
    public function linkBudget(): ?array
    {
        if ($this->selectedNode && ($this->selectedNode['group'] ?? '') === 'onu') {
            $onuId = $this->selectedNode['metadata']['id'] ?? null;
            if ($onuId) {
                $onu = \App\Models\ISP\Onu::withoutGlobalScopes()->find($onuId);
                if ($onu) {
                    return $this->topologyService->calculateLinkBudget($onu);
                }
            }
        }
        return null;
    }

    #[Computed]
    public function summary(): array
    {
        return $this->topologyService->getSummary($this->odc);
    }

    #[Computed]
    public function topologyData(): array
    {
        return $this->topologyService->getTopologyData($this->odc);
    }

    #[Computed]
    public function ports(): array
    {
        return $this->topologyService->getPortManagement(
            $this->odc,
            $this->portStatusFilter,
            $this->search
        );
    }

    #[Computed]
    public function downstreamCustomers()
    {
        return $this->topologyService->getDownstreamCustomers(
            $this->odc,
            $this->search,
            $this->customerServiceFilter
        );
    }

    #[Computed]
    public function history(): array
    {
        return $this->topologyService->getHistory($this->odc);
    }

    public function render()
    {
        return view('livewire.isp.odc.show');
    }
}
