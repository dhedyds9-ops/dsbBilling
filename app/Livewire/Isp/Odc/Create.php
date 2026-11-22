<?php

namespace App\Livewire\Isp\Odc;

use App\Livewire\AdminComponent;
use App\Models\ISP\Olt;
use App\Models\ISP\Pop;
use App\Models\Master\Branch;
use App\Services\ISP\OdcService;
use Illuminate\Support\Facades\Auth;

class Create extends AdminComponent
{
    public $branch_id;
    public $olt_id;
    public $parent_odc_id;
    public $joint_closure_id;
    public $uplink_type = 'olt'; // 'olt' or 'odc'
    public $pop_id;
    public $code;
    public $name;
    public $description;
    public $address;
    public $latitude;
    public $longitude;
    public $port_count;
    public $status = 'active';

    // Integrasi Gudang & Logistik
    public $item_id;
    public $source_type = 'none'; // 'none', 'warehouse', 'custody'
    public $warehouse_id;
    public $technician_id;
    public $splitter_item_id;
    public $splitter_qty = 1;

    public function updatedUplinkType($val)
    {
        if ($val === 'olt') {
            $this->parent_odc_id = null;
        } else {
            $this->olt_id = null;
        }
    }

    public function updatedParentOdcId($parentId)
    {
        if ($parentId) {
            $parent = \App\Models\ISP\Odc::find($parentId);
            if ($parent) {
                if ($parent->branch_id && empty($this->branch_id)) {
                    $this->branch_id = $parent->branch_id;
                }
                if ($parent->pop_id && empty($this->pop_id)) {
                    $this->pop_id = $parent->pop_id;
                }
                if ($parent->olt_id && empty($this->olt_id)) {
                    $this->olt_id = $parent->olt_id;
                }
            }
        }
    }

    public function updatedOltId($oltId)
    {
        if ($oltId) {
            $olt = Olt::find($oltId);
            if ($olt && $olt->branch_id && empty($this->branch_id)) {
                $this->branch_id = $olt->branch_id;
            }
            if ($olt && $olt->pop_id && empty($this->pop_id)) {
                $this->pop_id = $olt->pop_id;
            }
        }
    }

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'odcs';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.odcs.index')],
            ['label' => 'ODC', 'url' => route('isp.odcs.index')],
            ['label' => 'Create'],
        ];
    }

    public function save()
    {
        $this->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'parent_odc_id' => 'nullable|exists:odcs,id',
            'code' => 'required|unique:odcs,code',
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $service = app(OdcService::class);
        $odc = $service->create([
            'branch_id' => $this->branch_id === '' ? null : $this->branch_id,
            'olt_id' => $this->uplink_type === 'olt' ? $this->olt_id : null,
            'parent_odc_id' => $this->uplink_type === 'odc' ? $this->parent_odc_id : null,
            'joint_closure_id' => $this->joint_closure_id ? (int) $this->joint_closure_id : null,
            'item_id' => $this->item_id ? (int) $this->item_id : null,
            'pop_id' => $this->pop_id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'port_count' => $this->port_count,
            'status' => $this->status,
        ], Auth::user());

        if ($this->source_type !== 'none' && $this->item_id) {
            $itemsToDeploy = [
                ['item_id' => (int) $this->item_id, 'quantity' => 1, 'notes' => 'Box ODC ' . $this->name]
            ];
            if ($this->splitter_item_id) {
                $itemsToDeploy[] = [
                    'item_id' => (int) $this->splitter_item_id,
                    'quantity' => (float) ($this->splitter_qty ?: 1),
                    'notes' => 'Splitter internal ODC ' . $this->name
                ];
            }
            try {
                app(\App\Services\Inventory\StockMutationService::class)->recordInfrastructureDeployment(
                    'odc',
                    $odc->id,
                    $itemsToDeploy,
                    $this->source_type,
                    $this->warehouse_id ? (int) $this->warehouse_id : null,
                    $this->technician_id ? (int) $this->technician_id : null,
                    Auth::id(),
                    "Pemasangan ODC {$this->name} ({$this->code})",
                    $odc->branch_id
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Gagal auto potong stok ODC: " . $e->getMessage());
            }
        }

        session()->flash('success', 'ODC berhasil dibuat dan sinkronisasi logistik tersimpan!');
        return redirect()->route('isp.odcs.index');
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $olts = Olt::active()->get();
        $pops = Pop::active()->get();
        $parentOdcs = \App\Models\ISP\Odc::active()->orderBy('name')->get();
        $jointClosures = \App\Models\ISP\JointClosure::active()->orderBy('name')->get();
        $odcItems = \App\Models\Inventory\Item::where(function ($q) {
            $q->where('category', 'ODC')->orWhere('name', 'like', '%ODC%');
        })->orderBy('name')->get();
        $splitterItems = \App\Models\Inventory\Item::where(function ($q) {
            $q->where('category', 'Splitter')->orWhere('name', 'like', '%Splitter%');
        })->orderBy('name')->get();
        $warehouses = \App\Models\Inventory\Warehouse::where('is_active', true)->orderBy('name')->get();
        $technicians = \App\Models\User::getEligibleTechnicians();

        return view('livewire.isp.odc.create', compact(
            'branches',
            'olts',
            'pops',
            'parentOdcs',
            'jointClosures',
            'odcItems',
            'splitterItems',
            'warehouses',
            'technicians'
        ));
    }
}
