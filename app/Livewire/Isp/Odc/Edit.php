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
    public array $existingPhotos = [];

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
        $this->pop_id = $this->odc->pop_id;
        $this->code = $this->odc->code;
        $this->name = $this->odc->name;
        $this->description = $this->odc->description;
        $this->address = $this->odc->address;
        $this->latitude = $this->odc->latitude;
        $this->longitude = $this->odc->longitude;
        $this->port_count = $this->odc->port_count;
        $this->status = $this->odc->status;
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
        if ($this->photo) {
            $folder = 'odcs';
            $filename = Str::random(40) . '.' . ($this->photo->getClientOriginalExtension() ?: 'jpg');
            $relPath = "network_photos/{$folder}/{$filename}";
            $fullPath = storage_path("app/public/{$relPath}");
            @mkdir(dirname($fullPath), 0775, true);
            @copy($this->photo->getRealPath(), $fullPath);
            @chmod($fullPath, 0664);

            $mirrorTargets = [
                public_path("storage/{$relPath}"),
                "/var/www/shared_storage/{$relPath}",
                "/var/www/winets/storage/app/public/{$relPath}",
                "/var/www/winets/public/storage/{$relPath}",
                "/var/www/mstore/storage/app/public/{$relPath}",
                "/var/www/mstore/public/storage/{$relPath}",
            ];
            foreach ($mirrorTargets as $mt) {
                $mtDir = dirname($mt);
                if (!@is_dir($mtDir)) @mkdir($mtDir, 0775, true);
                if (!@file_exists($mt)) {
                    @copy($fullPath, $mt);
                    @chmod($mt, 0664);
                }
            }

            $cleanUrl = "/storage/{$relPath}";
            array_unshift($photos, $cleanUrl);
        }

        $service = app(OdcService::class);
        $service->update($this->odc, [
            'branch_id' => $this->branch_id === '' ? null : $this->branch_id,
            'olt_id' => $this->olt_id,
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

    public function render()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $olts = Olt::active()->get();
        $pops = Pop::active()->get();
        return view('livewire.isp.odc.edit', compact('branches', 'olts', 'pops'));
    }
}
