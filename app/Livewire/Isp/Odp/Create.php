<?php

namespace App\Livewire\Isp\Odp;

use App\Livewire\AdminComponent;
use App\Models\ISP\Odc;
use App\Models\Master\Branch;
use App\Services\ISP\OdpService;
use Illuminate\Support\Facades\Auth;

class Create extends AdminComponent
{
    public $branch_id;
    public $odc_id;
    public $joint_closure_id;
    public $odc_port;
    public $parent_odp_id;
    public $uplink_type = 'odc'; // 'odc' or 'odp'
    public $code;
    public $name;
    public $description;
    public $address;
    public $province;
    public $regency;
    public $district;
    public $village;
    public $postal_code;
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
        if ($val === 'odc') {
            $this->parent_odp_id = null;
        } else {
            $this->odc_id = null;
            $this->odc_port = null;
        }
    }

    public function updatedParentOdpId($parentId)
    {
        if ($parentId) {
            $parent = \App\Models\ISP\Odp::find($parentId);
            if ($parent) {
                if ($parent->branch_id && empty($this->branch_id)) {
                    $this->branch_id = $parent->branch_id;
                }
                if ($parent->odc_id && empty($this->odc_id)) {
                    $this->odc_id = $parent->odc_id;
                }
            }
        }
    }

    public function updatedOdcId($odcId)
    {
        if ($odcId) {
            $odc = Odc::find($odcId);
            if ($odc && $odc->branch_id && empty($this->branch_id)) {
                $this->branch_id = $odc->branch_id;
            }
        }
    }

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'odps';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.odps.index')],
            ['label' => 'ODP', 'url' => route('isp.odps.index')],
            ['label' => 'Create'],
        ];
    }

    public function save()
    {
        $this->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'parent_odp_id' => 'nullable|exists:odps,id',
            'code' => 'required|unique:odps,code',
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'district' => 'nullable|string|max:100',
            'village' => 'nullable|string|max:100',
            'regency' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);

        $service = app(OdpService::class);
        $odp = $service->create([
            'branch_id' => $this->branch_id === '' ? null : $this->branch_id,
            'odc_id' => $this->uplink_type === 'odc' ? $this->odc_id : null,
            'joint_closure_id' => $this->joint_closure_id ? (int) $this->joint_closure_id : null,
            'parent_odp_id' => $this->uplink_type === 'odp' ? $this->parent_odp_id : null,
            'odc_port' => $this->uplink_type === 'odc' && $this->odc_port ? (int) $this->odc_port : null,
            'item_id' => $this->item_id ? (int) $this->item_id : null,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'province' => $this->province,
            'regency' => $this->regency,
            'district' => $this->district,
            'village' => $this->village,
            'postal_code' => $this->postal_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'port_count' => $this->port_count,
            'status' => $this->status,
        ], Auth::user());

        if ($this->source_type !== 'none' && $this->item_id) {
            $itemsToDeploy = [
                ['item_id' => (int) $this->item_id, 'quantity' => 1, 'notes' => 'Box ODP ' . $this->name]
            ];
            if ($this->splitter_item_id) {
                $itemsToDeploy[] = [
                    'item_id' => (int) $this->splitter_item_id,
                    'quantity' => (float) ($this->splitter_qty ?: 1),
                    'notes' => 'Splitter internal ODP ' . $this->name
                ];
            }
            try {
                app(\App\Services\Inventory\StockMutationService::class)->recordInfrastructureDeployment(
                    'odp',
                    $odp->id,
                    $itemsToDeploy,
                    $this->source_type,
                    $this->warehouse_id ? (int) $this->warehouse_id : null,
                    $this->technician_id ? (int) $this->technician_id : null,
                    Auth::id(),
                    "Pemasangan ODP {$this->name} ({$this->code})",
                    $odp->branch_id
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Gagal auto potong stok ODP: " . $e->getMessage());
            }
        }

        session()->flash('success', 'ODP berhasil dibuat dan sinkronisasi logistik tersimpan!');
        return redirect()->route('isp.odps.index');
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $odcs = Odc::active()->with('parentOdc')->orderBy('name')->get();
        $parentOdps = \App\Models\ISP\Odp::active()->orderBy('name')->get();
        $jointClosures = \App\Models\ISP\JointClosure::active()->orderBy('name')->get();
        $odpItems = \App\Models\Inventory\Item::where(function ($q) {
            $q->where('category', 'ODP')->orWhere('name', 'like', '%ODP%');
        })->orderBy('name')->get();
        $splitterItems = \App\Models\Inventory\Item::where(function ($q) {
            $q->where('category', 'Splitter')->orWhere('name', 'like', '%Splitter%');
        })->orderBy('name')->get();
        $warehouses = \App\Models\Inventory\Warehouse::where('is_active', true)->orderBy('name')->get();
        $technicians = \App\Models\User::getEligibleTechnicians();

        return view('livewire.isp.odp.create', compact(
            'branches',
            'odcs',
            'parentOdps',
            'jointClosures',
            'odpItems',
            'splitterItems',
            'warehouses',
            'technicians'
        ));
    }
}
