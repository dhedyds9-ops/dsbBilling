<?php

namespace App\Livewire\ACS\Firmware;

use App\Livewire\ACS\BaseACSComponent;
use App\Models\ACS\Firmware;

class Index extends BaseACSComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'acs';
        $this->activePage = 'firmware';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ACS', 'url' => route('acs.dashboard')],
            ['label' => 'Firmware'],
        ];
    }

    public function delete($id)
    {
        $firmware = Firmware::findOrFail($id);
        $firmware->delete();
        session()->flash('success', 'Firmware berhasil dihapus!');
    }

    public function render()
    {
        $query = Firmware::with('vendor');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('version', 'like', '%' . $this->search . '%')
                  ->orWhere('model', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        $firmwares = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        return view('livewire.acs.firmware.index', compact('firmwares'));
    }
}
