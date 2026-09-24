<?php

namespace App\Livewire\ACS\Log;

use App\Livewire\ACS\BaseACSComponent;
use App\Models\ACS\ACSLog;

class Index extends BaseACSComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'acs';
        $this->activePage = 'logs';
        $this->filters = ['type' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ACS', 'url' => route('acs.dashboard')],
            ['label' => 'Logs'],
        ];
    }

    public function delete($id)
    {
        $log = ACSLog::findOrFail($id);
        $log->delete();
        session()->flash('success', 'Log berhasil dihapus!');
    }

    public function render()
    {
        $query = ACSLog::with('device');

        if ($this->search) {
            $query->where('message', 'like', '%' . $this->search . '%');
        }

        if ($this->filters['type']) {
            $query->where('type', $this->filters['type']);
        }

        $logs = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        return view('livewire.acs.log.index', compact('logs'));
    }
}
