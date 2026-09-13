<?php

namespace App\Livewire\Admin\Payroll;

use App\Models\Employee;
use App\Models\Payroll;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Generate extends Component
{
    public $month;
    public $year;
    public $period_start;
    public $period_end;
    public $notes;

    public function mount()
    {
        $this->month = date('m');
        $this->year = date('Y');
        // Default cutoff: 26 prev month to 25 current month
        $this->period_start = date('Y-m-26', strtotime('-1 month'));
        $this->period_end = date('Y-m-25');
    }

    public function generate()
    {
        $this->validate([
            'month' => 'required|numeric|min:1|max:12',
            'year' => 'required|numeric|min:2000|max:2100',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date',
        ]);

        $employees = Employee::where('status', 'active')->get();
        $generatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                // Check if payroll already exists for this period
                $monthInt = (int) $this->month;
                $yearInt = (int) $this->year;
                
                // Gunakan withTrashed() agar record yang sudah di soft-delete bisa terdeteksi dan di-restore
                $payroll = Payroll::withTrashed()
                    ->where('employee_id', $employee->id)
                    ->where('period_month', $monthInt)
                    ->where('period_year', $yearInt)
                    ->first();

                if ($payroll) {
                    // Jika ada dan sudah dihapus (soft delete), kita kembalikan (restore) dan perbarui datanya
                    if ($payroll->trashed()) {
                        $payroll->restore();
                        $payroll->update([
                            'period_start' => $this->period_start ?: null,
                            'period_end' => $this->period_end ?: null,
                            'base_salary' => $employee->base_salary,
                            'allowances' => 0,
                            'deductions' => 0,
                            'net_salary' => $employee->base_salary,
                            'status' => 'draft',
                            'notes' => $this->notes
                        ]);
                        $generatedCount++;
                    }
                    // Jika belum dihapus, biarkan saja (skip) agar tidak menimpa data yang sedang aktif
                } else {
                    Payroll::create([
                        'employee_id' => $employee->id,
                        'period_month' => $monthInt,
                        'period_year' => $yearInt,
                        'period_start' => $this->period_start ?: null,
                        'period_end' => $this->period_end ?: null,
                        'base_salary' => $employee->base_salary,
                        'allowances' => 0,
                        'deductions' => 0,
                        'net_salary' => $employee->base_salary,
                        'status' => 'draft',
                        'notes' => $this->notes
                    ]);
                    $generatedCount++;
                }
            }
            DB::commit();
            
            session()->flash('success', "{$generatedCount} payroll records generated successfully for {$this->month}/{$this->year}.");
            return redirect()->route('admin.payroll.index');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', "Error generating payroll: " . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.payroll.generate');
    }
}
