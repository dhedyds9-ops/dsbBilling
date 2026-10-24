<?php

namespace App\Livewire\Admin\Attendance;

use App\Livewire\AdminComponent;
use App\Models\Employee;
use App\Models\Workforce\Attendance;
use Carbon\Carbon;
use Livewire\WithPagination;

class Index extends AdminComponent
{
    use WithPagination;

    public $filterMode = 'today'; // today | month | period | all
    public $periodStart;
    public $periodEnd;
    public $employeeFilter = '';
    public $search = '';
    public $filterStatus = '';
    public $perPage = 15;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'attendance';
        $this->periodStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->periodEnd   = Carbon::today()->format('Y-m-d');

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Kepegawaian', 'url' => '#'],
            ['label' => 'Rekap Absensi', 'url' => route('admin.attendance.index')],
        ];
    }

    public function updatingFilterMode()     { $this->resetPage(); }
    public function updatingPeriodStart()    { $this->resetPage(); }
    public function updatingPeriodEnd()      { $this->resetPage(); }
    public function updatingEmployeeFilter() { $this->resetPage(); }
    public function updatingSearch()         { $this->resetPage(); }
    public function updatingFilterStatus()   { $this->resetPage(); }

    public function updateStatus($id, $status, $notes = null)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->status = \Src\Domain\Workforce\Enums\AttendanceStatus::from($status);
        if ($notes) {
            $attendance->notes = $attendance->notes
                ? $attendance->notes . ' | ' . $notes
                : $notes;
        }
        $attendance->save();
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Status absensi berhasil diubah.']);
    }

    public function render()
    {
        $baseQuery = Attendance::with(['technician.employee'])
            ->when($this->filterMode === 'today',
                fn($q) => $q->whereDate('date', Carbon::today()))
            ->when($this->filterMode === 'month',
                fn($q) => $q->whereMonth('date', now()->month)
                            ->whereYear('date', now()->year))
            ->when($this->filterMode === 'period' && $this->periodStart && $this->periodEnd,
                fn($q) => $q->whereBetween('date', [$this->periodStart, $this->periodEnd]))
            ->when($this->employeeFilter,
                fn($q) => $q->where('technician_id', $this->employeeFilter));

        $stats = [
            'total'      => (clone $baseQuery)->count(),
            'working'    => (clone $baseQuery)->where('status', 'checked_in')->count(),
            'done'       => (clone $baseQuery)->where('status', 'checked_out')->count(),
            'late'       => (clone $baseQuery)->where('status', 'late')->count(),
            'sick_leave' => (clone $baseQuery)->whereIn('status', ['sick', 'permission', 'absent'])->count(),
        ];

        $query = (clone $baseQuery)
            ->when(!empty($this->search), function ($q) {
                $q->whereHas('technician', function ($tq) {
                    $tq->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when(!empty($this->filterStatus),
                fn($q) => $q->where('status', $this->filterStatus))
            ->orderBy('date', 'desc')
            ->orderBy('checked_in_at', 'asc');

        $attendances = $query->paginate($this->perPage);

        $employees = Employee::where('status', 'active')
            ->with('user')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.attendance.index', [
            'attendances' => $attendances,
            'stats'       => $stats,
            'employees'   => $employees,
        ]);
    }
}
