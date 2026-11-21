<?php

namespace App\Livewire\Isp\Odc;

use App\Livewire\AdminComponent;
use App\Models\ISP\Odc as OdcModel;
use App\Models\ISP\Olt;
use App\Models\ISP\Pop;
use App\Models\Master\Branch;
use App\Services\ISP\OdcService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class Edit extends AdminComponent
{
    use WithFileUploads;

    public $odcId;
    public $odc;
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
    public $status;
    public $photo;
    public $croppedPhotoData = null;
    public array $existingPhotos = [];

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
            $parent = OdcModel::find($parentId);
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

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'odcs';
        $this->odcId = $id;
        $this->odc = OdcModel::findOrFail($id);

        $this->existingPhotos = is_array($this->odc->photos) ? $this->odc->photos : [];
        $this->branch_id = $this->odc->branch_id;
        $this->olt_id = $this->odc->olt_id;
        $this->parent_odc_id = $this->odc->parent_odc_id;
        $this->uplink_type = $this->odc->parent_odc_id ? 'odc' : 'olt';
        $this->pop_id = $this->odc->pop_id;
        $this->code = $this->odc->code;
        $this->name = $this->odc->name;
        $this->description = $this->odc->description;
        $this->address = $this->odc->address;
        $this->latitude = $this->odc->latitude;
        $this->longitude = $this->odc->longitude;
        $this->port_count = $this->odc->port_count ?: 16;
        $this->status = $this->odc->status ?: 'active';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.odcs.index')],
            ['label' => 'ODC', 'url' => route('isp.odcs.index')],
            ['label' => $this->odc->name, 'url' => route('isp.odcs.show', $this->odcId)],
            ['label' => 'Edit'],
        ];
    }

    public function save()
    {
        $this->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'code' => 'required|unique:odcs,code,' . $this->odcId,
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'photo' => 'nullable|image|max:12288',
        ]);

        $photos = $this->existingPhotos;
        if ($this->croppedPhotoData) {
            $res = \App\Services\ISP\NodePhotoService::attachPhoto($this->odc, $this->croppedPhotoData, 'odc', true);
            $photos = $res['photos'];
        } elseif ($this->photo) {
            $res = \App\Services\ISP\NodePhotoService::attachPhoto($this->odc, $this->photo, 'odc', true);
            $photos = $res['photos'];
        }

        $service = app(OdcService::class);
        $service->update($this->odc, [
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
            'photos' => array_values(array_unique($photos)),
        ], Auth::user());

        \App\Livewire\Gis\GisMap::flushCache();

        session()->flash('success', 'ODC dan foto tiang berhasil diperbarui!');
        return redirect()->route('isp.odcs.show', $this->odcId);
    }

    public function deletePhoto()
    {
        \App\Services\ISP\NodePhotoService::deletePhotos($this->odc);
        $this->existingPhotos = [];
        $this->photo = null;
        $this->croppedPhotoData = null;
        \App\Livewire\Gis\GisMap::flushCache();
        session()->flash('success', 'Foto dokumentasi tiang ODC berhasil dihapus!');
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $olts = Olt::active()->get();
        $pops = Pop::active()->get();
        $parentOdcs = OdcModel::active()->where('id', '!=', $this->odcId)->orderBy('name')->get();
        return view('livewire.isp.odc.edit', compact('branches', 'olts', 'pops', 'parentOdcs'));
    }
}
