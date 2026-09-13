<?php

namespace App\Livewire\Admin\Attendance;

use App\Livewire\AdminComponent;
use App\Models\Workforce\Attendance;
use Carbon\Carbon;

class Index extends AdminComponent
{
    public $dateFilter;
    public $search = '';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'admin-attendance';
        $this->dateFilter = Carbon::today()->format('Y-m-d');
        
        $this->breadcrumbs = [
            ['label' => 'Administration', 'url' => '#'],
            ['label' => 'Rekap Absensi', 'url' => '#'],
        ];
    }

    public function updateStatus($id, $status, $notes = null)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->status = \Src\Domain\Workforce\Enums\AttendanceStatus::from($status);
        if ($notes) {
            $attendance->notes = $attendance->notes ? $attendance->notes . ' | ' . $notes : $notes;
        }
        $attendance->save();
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Status absensi berhasil diubah.']);
    }

    public function render()
    {
        $query = Attendance::with('technician')
            ->whereDate('date', $this->dateFilter);

        if (!empty($this->search)) {
            $query->whereHas('technician', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        $attendances = $query->orderBy('checked_in_at', 'asc')->get();

        return view('livewire.admin.attendance.index', [
            'attendances' => $attendances
        ])->layout('layouts.enterprise');
    }
}
