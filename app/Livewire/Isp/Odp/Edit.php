<?php

namespace App\Livewire\Isp\Odp;

use App\Livewire\AdminComponent;
use App\Models\ISP\Odp as OdpModel;
use App\Models\ISP\Odc;
use App\Models\Master\Branch;
use App\Services\ISP\OdpService;
use App\Services\ISP\NodePhotoService;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;

class Edit extends AdminComponent
{
    use WithFileUploads;

    public $odpId;
    public $odp;
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
    public $status;
    public $photo;
    public $croppedPhotoData = null;
    public $existingPhotos = [];

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
            $parent = OdpModel::find($parentId);
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

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'odps';
        $this->odpId = $id;
        $this->odp = OdpModel::findOrFail($id);

        $this->branch_id = $this->odp->branch_id;
        $this->odc_id = $this->odp->odc_id;
        $this->joint_closure_id = $this->odp->joint_closure_id;
        $this->odc_port = $this->odp->odc_port;
        $this->parent_odp_id = $this->odp->parent_odp_id;
        $this->uplink_type = $this->odp->parent_odp_id ? 'odp' : 'odc';
        $this->code = $this->odp->code;
        $this->name = $this->odp->name;
        $this->description = $this->odp->description;
        $this->address = $this->odp->address;
        $this->province = $this->odp->province;
        $this->regency = $this->odp->regency;
        $this->district = $this->odp->district;
        $this->village = $this->odp->village;
        $this->postal_code = $this->odp->postal_code;
        $this->latitude = $this->odp->latitude;
        $this->longitude = $this->odp->longitude;
        $this->port_count = $this->odp->port_count ?: 8;
        $this->status = $this->odp->status ?: 'active';
        $this->existingPhotos = is_array($this->odp->photos) ? $this->odp->photos : [];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.odps.index')],
            ['label' => 'ODP', 'url' => route('isp.odps.index')],
            ['label' => $this->odp->name, 'url' => route('isp.odps.show', $this->odpId)],
            ['label' => 'Edit'],
        ];
    }

    public function save()
    {
        $this->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'parent_odp_id' => 'nullable|exists:odps,id',
            'code' => 'required|unique:odps,code,' . $this->odpId,
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'photo' => 'nullable|image|max:12288',
            'district' => 'nullable|string|max:100',
            'village' => 'nullable|string|max:100',
            'regency' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);

        $photos = $this->existingPhotos;
        if (!empty($this->croppedPhotoData)) {
            $res = NodePhotoService::attachPhoto($this->odp, $this->croppedPhotoData, 'odp', true);
            $photos = $res['photos'] ?? [];
        } elseif ($this->photo) {
            $res = NodePhotoService::attachPhoto($this->odp, $this->photo, 'odp', true);
            $photos = $res['photos'] ?? [];
        }

        $service = app(OdpService::class);
        $service->update($this->odp, [
            'branch_id' => $this->branch_id === '' ? null : $this->branch_id,
            'odc_id' => $this->uplink_type === 'odc' ? $this->odc_id : null,
            'joint_closure_id' => $this->joint_closure_id ? (int) $this->joint_closure_id : null,
            'parent_odp_id' => $this->uplink_type === 'odp' ? $this->parent_odp_id : null,
            'odc_port' => $this->uplink_type === 'odc' && $this->odc_port ? (int) $this->odc_port : null,
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
            'port_count' => (int) ($this->port_count ?: 8),
            'status' => $this->status,
            'photos' => array_values(array_unique($photos)),
        ], Auth::user());

        \App\Livewire\Gis\GisMap::flushCache();

        session()->flash('success', 'ODP dan foto fisik tiang berhasil diperbarui!');
        return redirect()->route('isp.odps.show', $this->odpId);
    }

    public function deletePhoto()
    {
        NodePhotoService::deletePhotos($this->odp);
        $this->existingPhotos = [];
        $this->photo = null;
        $this->croppedPhotoData = null;
        \App\Livewire\Gis\GisMap::flushCache();
        session()->flash('success', 'Foto dokumentasi tiang ODP berhasil dihapus!');
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $odcs = Odc::active()->with('parentOdc')->orderBy('name')->get();
        $jointClosures = \App\Models\ISP\JointClosure::active()->orderBy('name')->get();
        $parentOdps = OdpModel::active()->where('id', '!=', $this->odpId)->orderBy('name')->get();
        return view('livewire.isp.odp.edit', compact('branches', 'odcs', 'jointClosures', 'parentOdps'));
    }
}
