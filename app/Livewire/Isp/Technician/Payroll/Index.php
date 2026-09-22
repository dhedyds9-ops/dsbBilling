<?php
namespace App\Livewire\ISP\Technician\Payroll;

use App\Models\Payroll;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $employeeId = auth()->user()->employee->id ?? null;
        $payrolls = $employeeId ? Payroll::where('employee_id', $employeeId)->orderBy('period_year', 'desc')->orderBy('period_month', 'desc')->get() : collect();
        
        return view('livewire.isp.technician.payroll.index', [
            'payrolls' => $payrolls
        ])->layout('layouts.technician-app');
    }
}
