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
        $service->create([
            'branch_id' => $this->branch_id === '' ? null : $this->branch_id,
            'olt_id' => $this->uplink_type === 'olt' ? $this->olt_id : null,
            'parent_odc_id' => $this->uplink_type === 'odc' ? $this->parent_odc_id : null,
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

        session()->flash('success', 'ODC berhasil dibuat!');
        return redirect()->route('isp.odcs.index');
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $olts = Olt::active()->get();
        $pops = Pop::active()->get();
        $parentOdcs = \App\Models\ISP\Odc::active()->orderBy('name')->get();
        return view('livewire.isp.odc.create', compact('branches', 'olts', 'pops', 'parentOdcs'));
    }
}
