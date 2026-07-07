<?php

namespace App\Livewire\ACS\Alarm;

use App\Livewire\ACS\BaseACSComponent;
use App\Models\ACS\ACSAlarm;

class Index extends BaseACSComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'acs';
        $this->activePage = 'alarms';
        $this->filters = ['status' => '', 'severity' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ACS', 'url' => route('acs.dashboard')],
            ['label' => 'Alarms'],
        ];
    }

    public function acknowledge($id)
    {
        $alarm = ACSAlarm::findOrFail($id);
        $alarm->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'acknowledged_by' => auth()->id(),
        ]);
        session()->flash('success', 'Alarm berhasil diakui!');
    }

    public function delete($id)
    {
        $alarm = ACSAlarm::findOrFail($id);
        $alarm->delete();
        session()->flash('success', 'Alarm berhasil dihapus!');
    }

    public function render()
    {
        $query = ACSAlarm::with('device');

        if ($this->search) {
            $query->where('message', 'like', '%' . $this->search . '%');
        }

        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        if ($this->filters['severity']) {
            $query->where('severity', $this->filters['severity']);
        }

        $alarms = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        return view('livewire.acs.alarm.index', compact('alarms'));
    }
}
