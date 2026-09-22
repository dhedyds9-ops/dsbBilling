<?php
namespace App\Livewire\Isp\Technician\Payroll;

use App\Models\Payroll;
use Livewire\Component;

class Show extends Component
{
    public Payroll $payroll;

    public function mount($id)
    {
        $this->payroll = Payroll::with('employee')->findOrFail($id);
        
        // Ensure they can only view their own
        if (auth()->user()->employee && auth()->user()->employee->id !== $this->payroll->employee_id) {
            abort(403, 'Unauthorized access to this payslip.');
        }
    }

    public function render()
    {
        return view('livewire.isp.technician.payroll.show')
            ->layout('layouts.technician-app');
    }
}
