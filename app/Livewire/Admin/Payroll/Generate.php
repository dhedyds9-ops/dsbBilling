<?php

namespace App\Livewire\Admin\Payroll;

use App\Livewire\AdminComponent;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Support\Facades\DB;

class Generate extends AdminComponent
{
    public $month;
    public $year;
    public $period_start;
    public $period_end;
    public $notes;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'payroll';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Kepegawaian', 'url' => '#'],
            ['label' => 'Rekap Gaji', 'url' => route('admin.payroll.index')],
            ['label' => 'Generate Gaji'],
        ];

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

        $latePenalty = (float) \App\Models\Setting::getValue('payroll.late_penalty', 25000);
        $absentPenalty = (float) \App\Models\Setting::getValue('payroll.absent_penalty', 100000);

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                $monthInt = (int) $this->month;
                $yearInt = (int) $this->year;

                $deductions = 0;
                $attendanceNotes = [];

                if ($employee->user_id && $this->period_start && $this->period_end) {
                    $lateCount = \App\Models\Workforce\Attendance::where('technician_id', $employee->user_id)
                        ->whereBetween('date', [$this->period_start, $this->period_end])
                        ->where('status', \Src\Domain\Workforce\Enums\AttendanceStatus::LATE)
                        ->count();
                    $absentCount = \App\Models\Workforce\Attendance::where('technician_id', $employee->user_id)
                        ->whereBetween('date', [$this->period_start, $this->period_end])
                        ->where('status', \Src\Domain\Workforce\Enums\AttendanceStatus::ABSENT)
                        ->count();

                    if ($lateCount > 0) {
                        $deductions += ($lateCount * $latePenalty);
                        $attendanceNotes[] = "Terlambat {$lateCount}x (-Rp " . number_format($lateCount * $latePenalty, 0, ',', '.') . ")";
                    }
                    if ($absentCount > 0) {
                        $deductions += ($absentCount * $absentPenalty);
                        $attendanceNotes[] = "Alpa {$absentCount}x (-Rp " . number_format($absentCount * $absentPenalty, 0, ',', '.') . ")";
                    }
                }

                $fullNotes = trim(($this->notes ?? '') . (empty($attendanceNotes) ? '' : ' | ' . implode(', ', $attendanceNotes)), ' |');
                $netSalary = max(0, $employee->base_salary - $deductions);
                
                // Gunakan withTrashed() agar record yang sudah di soft-delete bisa terdeteksi dan di-restore
                $payroll = Payroll::withTrashed()
                    ->where('employee_id', $employee->id)
                    ->where('period_month', $monthInt)
                    ->where('period_year', $yearInt)
                    ->first();

                if ($payroll) {
                    if ($payroll->trashed()) {
                        $payroll->restore();
                        $payroll->update([
                            'period_start' => $this->period_start ?: null,
                            'period_end' => $this->period_end ?: null,
                            'base_salary' => $employee->base_salary,
                            'allowances' => 0,
                            'deductions' => $deductions,
                            'net_salary' => $netSalary,
                            'status' => 'draft',
                            'notes' => $fullNotes ?: null
                        ]);
                        $generatedCount++;
                    }
                } else {
                    Payroll::create([
                        'employee_id' => $employee->id,
                        'period_month' => $monthInt,
                        'period_year' => $yearInt,
                        'period_start' => $this->period_start ?: null,
                        'period_end' => $this->period_end ?: null,
                        'base_salary' => $employee->base_salary,
                        'allowances' => 0,
                        'deductions' => $deductions,
                        'net_salary' => $netSalary,
                        'status' => 'draft',
                        'notes' => $fullNotes ?: null
                    ]);
                    $generatedCount++;
                }
            }
            DB::commit();
            
            session()->flash('success', "{$generatedCount} slip gaji berhasil digenerate untuk periode {$this->month}/{$this->year}.");
            return redirect()->route('admin.payroll.index');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', "Gagal generate gaji: " . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.payroll.generate');
    }
}
