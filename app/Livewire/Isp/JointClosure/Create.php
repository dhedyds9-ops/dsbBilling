<?php

namespace App\Livewire\Isp\JointClosure;

use App\Livewire\AdminComponent;
use App\Models\ISP\JointClosure;
use App\Models\ISP\Odc;
use App\Models\ISP\Olt;
use App\Models\ISP\Vendor;
use App\Models\Master\Branch;
use App\Services\ISP\JointClosureService;
use App\Services\ISP\NodePhotoService;
use Livewire\WithFileUploads;

class Create extends AdminComponent
{
    use WithFileUploads;

    public $branch_id;
    public $vendor_id;
    public $code;
    public $name;
    public $type = 'Dome'; // Dome, Inline, Wall Mount
    public $port_count = 24;
    public $custom_port_count = '';
    public $uplink_type = 'olt'; // 'olt', 'closure', 'odc'
    public $olt_id;
    public $pon_port;
    public $parent_closure_id;
    public $odc_id;
    public $description;
    public $address;
    public $latitude;
    public $longitude;
    public $status = 'active';
    public $photo;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'joint-closures';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Infrastruktur Fiber', 'url' => route('isp.joint-closures.index')],
            ['label' => 'Joint Closure', 'url' => route('isp.joint-closures.index')],
            ['label' => 'Tambah Baru'],
        ];

        // Suggest unique code
        $nextId = (JointClosure::max('id') ?? 0) + 1;
        $this->code = 'JC-' . str_pad((string)$nextId, 3, '0', STR_PAD_LEFT);
    }

    public function updatedUplinkType($val)
    {
        if ($val === 'olt') {
            $this->parent_closure_id = null;
            $this->odc_id = null;
        } elseif ($val === 'closure') {
            $this->olt_id = null;
            $this->pon_port = null;
            $this->odc_id = null;
        } elseif ($val === 'odc') {
            $this->olt_id = null;
            $this->pon_port = null;
            $this->parent_closure_id = null;
        }
    }

    public function updatedPortCount($val)
    {
        if ($val !== 'custom') {
            $this->custom_port_count = '';
        }
    }

    public function save()
    {
        $effectivePortCount = $this->port_count === 'custom'
            ? (int) $this->custom_port_count
            : (int) $this->port_count;

        $this->validate([
            'code' => 'required|string|max:50|unique:joint_closures,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:Dome,Inline,Wall Mount',
            'port_count' => 'required',
            'custom_port_count' => $this->port_count === 'custom' ? 'required|integer|min:1|max:512' : 'nullable',
            'status' => 'required|in:active,inactive',
            'branch_id' => 'nullable|exists:branches,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'olt_id' => 'nullable|exists:olts,id',
            'parent_closure_id' => 'nullable|exists:joint_closures,id',
            'odc_id' => 'nullable|exists:odcs,id',
            'photo' => 'nullable|image|max:10240',
        ]);

        $photos = [];
        if ($this->photo) {
            $path = $this->photo->store('nodes/closures', 'public');
            $photos[] = '/storage/' . $path;
        }

        $service = app(JointClosureService::class);
        $closure = $service->create([
            'branch_id' => $this->branch_id ?: null,
            'vendor_id' => $this->vendor_id ?: null,
            'olt_id' => $this->uplink_type === 'olt' ? ($this->olt_id ?: null) : null,
            'pon_port' => $this->uplink_type === 'olt' ? ($this->pon_port ?: null) : null,
            'parent_closure_id' => $this->uplink_type === 'closure' ? ($this->parent_closure_id ?: null) : null,
            'odc_id' => $this->uplink_type === 'odc' ? ($this->odc_id ?: null) : null,
            'code' => strtoupper(trim($this->code)),
            'name' => trim($this->name),
            'type' => $this->type,
            'port_count' => $effectivePortCount,
            'address' => $this->address ?: null,
            'latitude' => $this->latitude ?: null,
            'longitude' => $this->longitude ?: null,
            'description' => $this->description ?: null,
            'status' => $this->status,
            'photos' => $photos,
        ], auth()->user());

        session()->flash('success', 'Joint Closure ' . $closure->name . ' berhasil ditambahkan!');
        return redirect()->route('isp.joint-closures.index');
    }

    public function render()
    {
        return view('livewire.isp.joint-closure.create', [
            'branches' => Branch::orderBy('name')->get(),
            'vendors' => Vendor::orderBy('name')->get(),
            'olts' => Olt::orderBy('name')->get(),
            'parentClosures' => JointClosure::orderBy('name')->get(),
            'odcs' => Odc::orderBy('name')->get(),
        ])->layout('layouts.admin');
    }
}
