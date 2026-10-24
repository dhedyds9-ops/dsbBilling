<?php

namespace App\Livewire\Admin\Payroll;

use App\Livewire\Admin\BaseAdminComponent;
use App\Models\Payroll;

class Index extends BaseAdminComponent
{
    public $month;
    public $year;
    public $filterStatus = '';
    public $showDeleteModal = false;
    public $deleteId = null;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'payroll';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Kepegawaian', 'url' => '#'],
            ['label' => 'Rekap Gaji', 'url' => route('admin.payroll.index')],
        ];
        
        $this->month = (int)date('m');
        $this->year = (int)date('Y');
    }

    public function updatingMonth() { $this->resetPage(); }
    public function updatingYear() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingSearch() { $this->resetPage(); }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteSingle()
    {
        if ($this->deleteId) {
            Payroll::withTrashed()->find($this->deleteId)?->forceDelete();
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Data slip gaji berhasil dihapus.']);
        }
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function markAsPaid($id)
    {
        $payroll = Payroll::findOrFail($id);
        $payroll->update([
            'status' => 'paid',
            'payment_date' => now()
        ]);
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Slip gaji ditandai sudah dibayar.']);
    }

    public function render()
    {
        $baseQuery = Payroll::with(['employee.user'])
            ->when($this->month, fn($q) => $q->where('period_month', (int) $this->month))
            ->when($this->year, fn($q) => $q->where('period_year', (int) $this->year));

        $stats = [
            'total_slip' => (clone $baseQuery)->count(),
            'total_net' => (clone $baseQuery)->sum('net_salary'),
            'paid_net' => (clone $baseQuery)->where('status', 'paid')->sum('net_salary'),
            'unpaid_net' => (clone $baseQuery)->where('status', '!=', 'paid')->sum('net_salary'),
        ];

        $payrolls = (clone $baseQuery)
            ->when($this->search, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('nik', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus !== '', fn($q) => $q->where('status', $this->filterStatus))
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.payroll.index', [
            'payrolls' => $payrolls,
            'stats' => $stats,
        ]);
    }
}
