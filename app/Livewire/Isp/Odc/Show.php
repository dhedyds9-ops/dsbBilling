<?php

namespace App\Livewire\Isp\Odc;

use App\Livewire\AdminComponent;
use App\Models\ISP\JointClosure;
use App\Models\ISP\Odc as OdcModel;
use App\Models\ISP\Odp;
use App\Models\ISP\Splitter;
use App\Models\ISP\SplitterPort;
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

    // Cabinet Port Connection Action Modal State
    public bool $showPortModal = false;
    public ?int $targetPortNumber = null;
    public ?int $selectedOdpIdForPort = null;

    // Splitter Management State
    public bool $showSplitterModal = false;
    public string $splitterName = '';
    public string $splitterCode = '';
    public string $splitterType = 'plc'; // 'plc', 'fbt'
    public string $splitterRatio = '1:8';
    public string $splitterPackage = 'tray'; // 'tray', 'cassette', 'mini_tube', 'abs_box', 'rack_mount'
    public int $splitterInputPorts = 1;
    public int $splitterPortCount = 8;
    public ?float $splitterLossDb = 10.50;
    public string $splitterDescription = '';

    // Splitter Port Modal State
    public bool $showSplitterPortModal = false;
    public ?int $selectedSplitterPortId = null;
    public ?SplitterPort $selectedSplitterPort = null;
    public string $portFormTargetType = 'odp'; // 'odp', 'closure'
    public ?int $portFormTargetId = null;
    public string $portFormStatus = 'available';
    public ?float $portFormAttenuation = null;
    public string $portFormCoreColor = '';
    public string $portFormNotes = '';

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
            ->with([
                'olt.pop',
                'parentOdc.olt',
                'parentOdc.splitters',
                'childOdcs.odps.onus.customerService.customer',
                'pop',
                'odps.onus.customerService.customer',
                'odps.splitters.ports',
                'splitters.ports.target',
                'branch',
                'createdBy',
            ])
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

    // -------------------------------------------------------------
    // Cabinet Port Allocation
    // -------------------------------------------------------------
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

        $odp = Odp::withoutGlobalScopes()->find($this->selectedOdpIdForPort);
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
        $odp = Odp::withoutGlobalScopes()
            ->where('odc_id', $this->odc->id)
            ->where('odc_port', $portNumber)
            ->first();

        if ($odp) {
            $odp->update(['odc_port' => null]);
            $this->odc->refresh();
            session()->flash('success', "Koneksi ODP {$odp->name} dari Port #{$portNumber} berhasil dilepas.");
        }
    }

    // -------------------------------------------------------------
    // Splitter Management (PLC & FBT)
    // -------------------------------------------------------------
    public function openAddSplitterModal(): void
    {
        $count = Splitter::withoutGlobalScopes()->where('odc_id', $this->odc->id)->count() + 1;
        $this->splitterCode = 'SPL-' . ($this->odc->code ?: 'ODC') . '-T' . $count;
        $this->splitterName = 'Splitter Tray #' . $count;
        $this->splitterType = 'plc';
        $this->splitterRatio = '1:8';
        $this->splitterPackage = 'tray';
        $this->splitterInputPorts = 1;
        $this->splitterPortCount = 8;
        $this->splitterLossDb = 10.50;
        $this->splitterDescription = '';
        $this->showSplitterModal = true;
    }

    public function closeAddSplitterModal(): void
    {
        $this->showSplitterModal = false;
    }

    public function updatedSplitterRatio($ratio): void
    {
        $this->splitterRatio = $ratio;
        $losses = Splitter::STANDARD_LOSSES;
        $this->splitterLossDb = $losses[$ratio] ?? 10.50;

        if (preg_match('/^[12]:(\d+)$/', $ratio, $m)) {
            $this->splitterPortCount = (int) $m[1];
        } elseif (in_array($ratio, ['50:50', '60:40', '70:30', '80:20', '85:15', '90:10'])) {
            $this->splitterPortCount = 2;
            $this->splitterType = 'fbt';
        }
    }

    public function updatedSplitterType($type): void
    {
        $this->splitterType = $type;
        if ($type === 'fbt' && !str_contains($this->splitterRatio, ':') || str_starts_with($this->splitterRatio, '1:')) {
            $this->splitterRatio = '70:30';
            $this->splitterPortCount = 2;
            $this->splitterLossDb = 5.60;
        } elseif ($type === 'plc' && !str_starts_with($this->splitterRatio, '1:')) {
            $this->splitterRatio = '1:8';
            $this->splitterPortCount = 8;
            $this->splitterLossDb = 10.50;
        }
    }

    public function createSplitter(): void
    {
        $this->validate([
            'splitterCode' => 'required|string|max:50',
            'splitterName' => 'required|string|max:100',
            'splitterType' => 'required|in:plc,fbt',
            'splitterRatio' => 'required|string|max:20',
            'splitterPortCount' => 'required|integer|min:2|max:64',
            'splitterInputPorts' => 'required|integer|min:1|max:4',
        ]);

        $splitter = Splitter::create([
            'odc_id' => $this->odc->id,
            'code' => $this->splitterCode,
            'name' => $this->splitterName,
            'type' => $this->splitterType,
            'ratio_label' => $this->splitterRatio,
            'package_type' => $this->splitterPackage,
            'input_port_count' => $this->splitterInputPorts,
            'port_count' => $this->splitterPortCount,
            'insertion_loss_db' => $this->splitterLossDb,
            'description' => $this->splitterDescription,
            'status' => 'active',
            'branch_id' => $this->odc->branch_id,
            'created_by' => auth()->id(),
        ]);

        $splitter->syncPorts();
        $this->odc->refresh();
        $this->closeAddSplitterModal();

        session()->flash('success', "Splitter {$splitter->name} ({$splitter->ratio_label}) berhasil dipasang pada ODC.");
    }

    public function deleteSplitter(int $splitterId): void
    {
        $splitter = Splitter::withoutGlobalScopes()
            ->where('odc_id', $this->odc->id)
            ->find($splitterId);

        if ($splitter) {
            $name = $splitter->name;
            $splitter->ports()->delete();
            $splitter->delete();
            $this->odc->refresh();
            session()->flash('success', "Splitter {$name} berhasil dilepas dari ODC.");
        }
    }

    // -------------------------------------------------------------
    // Splitter Port Connection Modal
    // -------------------------------------------------------------
    public function openSplitterPortModal(int $portId): void
    {
        $port = SplitterPort::withoutGlobalScopes()->with(['splitter', 'target'])->find($portId);
        if (!$port) {
            return;
        }

        $this->selectedSplitterPortId = $port->id;
        $this->selectedSplitterPort = $port;
        $this->portFormTargetType = ($port->target_type && str_contains($port->target_type, 'JointClosure')) ? 'closure' : 'odp';
        $this->portFormTargetId = $port->target_id;
        $this->portFormStatus = $port->status ?: 'available';
        $this->portFormAttenuation = $port->attenuation_db;
        $this->portFormCoreColor = $port->core_color ?: '';
        $this->portFormNotes = $port->notes ?: '';

        $this->showSplitterPortModal = true;
    }

    public function closeSplitterPortModal(): void
    {
        $this->showSplitterPortModal = false;
        $this->selectedSplitterPortId = null;
        $this->selectedSplitterPort = null;
    }

    public function saveSplitterPort(): void
    {
        if (!$this->selectedSplitterPortId) {
            return;
        }

        $port = SplitterPort::withoutGlobalScopes()->find($this->selectedSplitterPortId);
        if (!$port) {
            return;
        }

        $port->status = $this->portFormStatus;
        $port->attenuation_db = $this->portFormAttenuation;
        $port->core_color = $this->portFormCoreColor;
        $port->notes = $this->portFormNotes;

        if ($this->portFormStatus === 'used' && $this->portFormTargetId) {
            if ($this->portFormTargetType === 'odp') {
                $odp = Odp::withoutGlobalScopes()->find($this->portFormTargetId);
                if ($odp) {
                    $port->assignTarget($odp, 1, "{$odp->name} [{$odp->code}]");
                    $odp->update([
                        'odc_id' => $this->odc->id,
                        'odc_port' => $port->port_number,
                    ]);
                }
            } elseif ($this->portFormTargetType === 'closure') {
                $closure = JointClosure::withoutGlobalScopes()->find($this->portFormTargetId);
                if ($closure) {
                    $port->assignTarget($closure, 1, "{$closure->name} [{$closure->code}]");
                }
            }
        } elseif ($this->portFormStatus === 'available') {
            $port->disconnect();
        } else {
            $port->save();
        }

        $this->odc->refresh();
        $this->closeSplitterPortModal();
        session()->flash('success', "Konfigurasi Port #{$port->port_number} berhasil diperbarui.");
    }

    public function disconnectSplitterPort(int $portId): void
    {
        $port = SplitterPort::withoutGlobalScopes()->find($portId);
        if ($port) {
            $port->disconnect();
            $this->odc->refresh();
            session()->flash('success', "Koneksi Port #{$port->port_number} berhasil diputus.");
        }
    }

    // -------------------------------------------------------------
    // Computed Properties
    // -------------------------------------------------------------
    #[Computed]
    public function availableOdps()
    {
        return Odp::withoutGlobalScopes()
            ->where(function($q) {
                $q->whereNull('odc_id')
                  ->orWhere('odc_id', $this->odc->id);
            })
            ->orderBy('name')
            ->get(['id', 'code', 'name']);
    }

    #[Computed]
    public function availableClosures()
    {
        return JointClosure::withoutGlobalScopes()
            ->orderBy('name')
            ->get(['id', 'code', 'name']);
    }

    #[Computed]
    public function odcSplitters()
    {
        return $this->topologyService->getOdcSplitters($this->odc);
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
    public function connectedOdps()
    {
        return \App\Models\ISP\Odp::withoutGlobalScopes()
            ->where('odc_id', $this->odcId)
            ->with([
                'splitters.ports.target',
                'onus.customerService.customer',
            ])
            ->orderByRaw('COALESCE(odc_port, 999) asc')
            ->get();
    }

    #[Computed]
    public function childOdcs()
    {
        return OdcModel::withoutGlobalScopes()
            ->where('parent_odc_id', $this->odcId)
            ->with(['odps.onus.customerService.customer'])
            ->get();
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
