<?php

namespace App\Livewire\ACS\Firmware;

use App\Livewire\ACS\BaseACSComponent;
use App\Models\ACS\Firmware;
use App\Models\ISP\Vendor;

class Create extends BaseACSComponent
{
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
            'status' => 'required|string|in:active,inactive',
        ]);

        $validated['uuid'] = (string) \Illuminate\Support\Str::uuid();
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        Firmware::create($validated);

        session()->flash('success', 'Firmware berhasil dibuat!');
        return redirect()->route('acs.firmware.index');
    }

    public function render()
    {
        $vendors = Vendor::all();
        return view('livewire.acs.firmware.create', compact('vendors'));
    }
}
