<?php

namespace App\Livewire\ACS\Firmware;

use App\Livewire\ACS\BaseACSComponent;
use App\Models\ACS\Firmware;
use App\Models\ISP\Vendor;

class Edit extends BaseACSComponent
{
    public $firmwareId;
    public $firmware;
    public $vendor_id;
    public $model;
    public $version;
    public $release_date;
    public $checksum;
    public $download_url;
    public $file_path;
    public $notes;
    public $status;

    public function mount($id = null)
    {
        parent::mount();
        $this->firmwareId = $id;
        $this->firmware = Firmware::findOrFail($id);
        $this->fill($this->firmware->toArray());
        $this->activeModule = 'acs';
        $this->activePage = 'firmware';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ACS', 'url' => route('acs.dashboard')],
            ['label' => 'Firmware', 'url' => route('acs.firmware.index')],
            ['label' => 'Edit'],
        ];
    }

    public function save()
    {
        $validated = $this->validate([
            'version' => 'required|string|max:255',
            'vendor_id' => 'nullable|exists:vendors,id',
            'status' => 'required|string|in:active,inactive',
        ]);

        $validated['updated_by'] = auth()->id();
        $this->firmware->update($validated);

        session()->flash('success', 'Firmware berhasil diperbarui!');
        return redirect()->route('acs.firmware.index');
    }

    public function render()
    {
        $vendors = Vendor::all();
        return view('livewire.acs.firmware.edit', compact('vendors'));
    }
}
