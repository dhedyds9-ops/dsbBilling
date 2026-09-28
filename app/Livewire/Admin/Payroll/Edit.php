<?php

namespace App\Livewire\Admin\Payroll;

use App\Livewire\AdminComponent;
use App\Models\Payroll;

class Edit extends AdminComponent
{
    public Payroll $payroll;
    public $base_salary;
    public $allowances;
    public $deductions;
    public $net_salary;
    public $notes;
    public $status;

    public function mount($id)
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'payroll';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Administration', 'url' => route('admin.users.index')],
            ['label' => 'Payroll', 'url' => route('admin.payroll.index')],
            ['label' => 'Edit'],
        ];

        $this->payroll = Payroll::with('employee')->findOrFail($id);
        $this->base_salary = $this->payroll->base_salary;
        $this->allowances = $this->payroll->allowances;
        $this->deductions = $this->payroll->deductions;
        $this->net_salary = $this->payroll->net_salary;
        $this->notes = $this->payroll->notes;
        $this->status = $this->payroll->status;
    }

    public function calculateNet()
    {
        $base = (float) $this->base_salary;
        $allow = (float) $this->allowances;
        $deduct = (float) $this->deductions;
        $this->net_salary = $base + $allow - $deduct;
    }

    public function updatedBaseSalary() { $this->calculateNet(); }
    public function updatedAllowances() { $this->calculateNet(); }
    public function updatedDeductions() { $this->calculateNet(); }

    public function save()
    {
        $this->validate([
            'base_salary' => 'required|numeric|min:0',
            'allowances' => 'required|numeric|min:0',
            'deductions' => 'required|numeric|min:0',
            'status' => 'required|in:draft,paid',
        ]);

        $this->calculateNet();

        $this->payroll->update([
            'base_salary' => $this->base_salary,
            'allowances' => $this->allowances,
            'deductions' => $this->deductions,
            'net_salary' => $this->net_salary,
            'status' => $this->status,
            'notes' => $this->notes,
        ]);

        session()->flash('success', 'Data slip gaji berhasil diperbarui.');
        return redirect()->route('admin.payroll.index');
    }

    public function render()
    {
        return view('livewire.admin.payroll.edit')->layout('layouts.enterprise');
    }
}
