<?php

namespace App\Livewire\Isp\JointClosure;

use App\Livewire\AdminComponent;
use App\Models\ISP\JointClosure;
use App\Models\ISP\Odc;
use App\Models\ISP\Olt;
use App\Models\ISP\Vendor;
use App\Models\Master\Branch;
use App\Services\ISP\JointClosureService;
use Livewire\WithFileUploads;

class Edit extends AdminComponent
{
    use WithFileUploads;

    public $closureId;
    public $closure;
    public $branch_id;
    public $vendor_id;
    public $code;
    public $name;
    public $type;
    public $port_count;
    public $custom_port_count = '';
    public $uplink_type = 'olt';
    public $olt_id;
    public $pon_port;
    public $parent_closure_id;
    public $odc_id;
    public $description;
    public $address;
    public $latitude;
    public $longitude;
    public $status;
    public $photo;
    public $existingPhotos = [];

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'joint-closures';
        $this->closureId = $id;
        $this->closure = JointClosure::findOrFail($id);

        $this->branch_id = $this->closure->branch_id;
        $this->vendor_id = $this->closure->vendor_id;
        $this->code = $this->closure->code;
        $this->name = $this->closure->name;
        $this->type = $this->closure->type ?: 'Dome';
        $this->description = $this->closure->description;
        $this->address = $this->closure->address;
        $this->latitude = $this->closure->latitude;
        $this->longitude = $this->closure->longitude;
        $this->status = $this->closure->status ?: 'active';
        $this->existingPhotos = is_array($this->closure->photos) ? $this->closure->photos : [];

        // Port count logic
        $standardCores = [4, 6, 8, 12, 24, 48, 96, 144];
        if (in_array((int)$this->closure->port_count, $standardCores, true)) {
            $this->port_count = (int)$this->closure->port_count;
        } else {
            $this->port_count = 'custom';
            $this->custom_port_count = (int)$this->closure->port_count;
        }

        // Uplink logic
        if (!empty($this->closure->parent_closure_id)) {
            $this->uplink_type = 'closure';
            $this->parent_closure_id = $this->closure->parent_closure_id;
        } elseif (!empty($this->closure->odc_id)) {
            $this->uplink_type = 'odc';
            $this->odc_id = $this->closure->odc_id;
        } else {
            $this->uplink_type = 'olt';
            $this->olt_id = $this->closure->olt_id;
            $this->pon_port = $this->closure->pon_port;
        }

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Infrastruktur Fiber', 'url' => route('isp.joint-closures.index')],
            ['label' => 'Joint Closure', 'url' => route('isp.joint-closures.index')],
            ['label' => $this->closure->name],
            ['label' => 'Edit'],
        ];
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

    public function deleteExistingPhoto($index)
    {
        if (isset($this->existingPhotos[$index])) {
            unset($this->existingPhotos[$index]);
            $this->existingPhotos = array_values($this->existingPhotos);
            $this->closure->update(['photos' => $this->existingPhotos]);
            session()->flash('success', 'Foto berhasil dihapus.');
        }
    }

    public function update()
    {
        $effectivePortCount = $this->port_count === 'custom'
            ? (int) $this->custom_port_count
            : (int) $this->port_count;

        $this->validate([
            'code' => 'required|string|max:50|unique:joint_closures,code,' . $this->closureId,
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

        $photos = $this->existingPhotos;
        if ($this->photo) {
            $path = $this->photo->store('nodes/closures', 'public');
            $photos[] = '/storage/' . $path;
        }

        $service = app(JointClosureService::class);
        $service->update($this->closure, [
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

        session()->flash('success', 'Joint Closure ' . $this->closure->name . ' berhasil diperbarui!');
        return redirect()->route('isp.joint-closures.show', $this->closureId);
    }

    public function render()
    {
        return view('livewire.isp.joint-closure.edit', [
            'branches' => Branch::orderBy('name')->get(),
            'vendors' => Vendor::orderBy('name')->get(),
            'olts' => Olt::orderBy('name')->get(),
            'parentClosures' => JointClosure::where('id', '!=', $this->closureId)->orderBy('name')->get(),
            'odcs' => Odc::orderBy('name')->get(),
        ])->layout('layouts.admin');
    }
}
