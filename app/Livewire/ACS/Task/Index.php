<?php

namespace App\Livewire\ACS\Task;

use App\Livewire\ACS\BaseACSComponent;
use App\Models\ACS\DeviceTask;

class Index extends BaseACSComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'acs';
        $this->activePage = 'tasks';
        $this->filters = ['status' => '', 'type' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ACS', 'url' => route('acs.dashboard')],
            ['label' => 'Tasks'],
        ];
    }

    public function delete($id)
    {
        $task = DeviceTask::findOrFail($id);
        $task->delete();
        session()->flash('success', 'Task berhasil dihapus!');
    }

    public function render()
    {
        $query = DeviceTask::with('device');

        if ($this->search) {
            $query->whereHas('device', function($q) {
                $q->where('serial_number', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        if ($this->filters['type']) {
            $query->where('type', $this->filters['type']);
        }

        $tasks = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        return view('livewire.acs.task.index', compact('tasks'));
    }
}
