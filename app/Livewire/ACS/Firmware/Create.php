<?php

namespace App\Livewire\ACS\Firmware;

use App\Livewire\ACS\BaseACSComponent;
use App\Models\ACS\Firmware;
use App\Models\ISP\Vendor;

class Create extends BaseACSComponent
{
    use \Livewire\WithFileUploads;

    public $file;
    public $vendor_id;
    public $model;
    public $version;
    public $release_date;
    public $checksum;
    public $download_url;
    public $file_path;
    public $notes;
    public $status = 'active';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'acs';
        $this->activePage = 'firmware';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ACS', 'url' => route('acs.dashboard')],
            ['label' => 'Firmware', 'url' => route('acs.firmware.index')],
            ['label' => 'Create'],
        ];
    }

    public function save()
    {
        $validated = $this->validate([
            'version' => 'required|string|max:255',
            'vendor_id' => 'nullable|exists:vendors,id',
            'model' => 'nullable|string|max:255',
            'release_date' => 'nullable|date',
            'status' => 'required|string|in:active,inactive',
            'file' => 'required|file|max:102400', // max 100MB
        ]);

        $path = $this->file->store('firmwares', 'public');

        Firmware::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'vendor_id' => $this->vendor_id,
            'model' => $this->model,
            'version' => $this->version,
            'release_date' => $this->release_date,
            'status' => $this->status,
            'file_path' => $path,
            'download_url' => \Illuminate\Support\Facades\Storage::url($path),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        session()->flash('success', 'Firmware berhasil dibuat!');
        return redirect()->route('acs.firmware.index');
    }

    public function render()
    {
        $vendors = Vendor::all();
        return view('livewire.acs.firmware.create', compact('vendors'));
    }
}
