<?php

namespace App\Livewire\Admin\Payroll;

use App\Livewire\Admin\BaseAdminComponent;
use App\Models\Payroll;

class Index extends BaseAdminComponent
{
    public $month;
    public $year;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'payroll';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Administration', 'url' => route('admin.users.index')],
            ['label' => 'Payroll'],
        ];
        
        $this->month = date('m');
        $this->year = date('Y');
    }

    public function delete($id)
    {
        Payroll::withTrashed()->findOrFail($id)->forceDelete();
        session()->flash('success', 'Data slip gaji berhasil dihapus. Anda dapat men-generate ulangnya.');
    }

    public function markAsPaid($id)
    {
        $payroll = Payroll::findOrFail($id);
        $payroll->update([
            'status' => 'paid',
            'payment_date' => now()
        ]);
        session()->flash('message', 'Payroll marked as paid.');
    }

    public function render()
    {
        $payrolls = Payroll::with('employee')
            ->when($this->month, fn($q) => $q->where('period_month', (int) $this->month))
            ->when($this->year, fn($q) => $q->where('period_year', (int) $this->year))
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.payroll.index', compact('payrolls'));
    }
}
