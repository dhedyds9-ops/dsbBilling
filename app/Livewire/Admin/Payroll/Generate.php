<?php

namespace App\Livewire\Admin\Payroll;

use App\Livewire\AdminComponent;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Workforce\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Src\Domain\Workforce\Enums\AttendanceStatus;

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

        $this->month        = date('m');
        $this->year         = date('Y');
        // Default cutoff: 26 bulan lalu s/d 25 bulan ini
        $this->period_start = date('Y-m-26', strtotime('-1 month'));
        $this->period_end   = date('Y-m-25');
    }

    /**
     * Hitung total hari kerja (Senin–Sabtu) dalam rentang periode.
     * Minggu dianggap libur. Bisa dikembangkan dengan tabel hari libur nasional.
     */
    private function countWorkDays(string $start, string $end): int
    {
        $period  = CarbonPeriod::create($start, $end);
        $count   = 0;
        foreach ($period as $day) {
            // 0 = Minggu
            if ($day->dayOfWeek !== Carbon::SUNDAY) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Ambil rekap absensi satu karyawan dalam periode.
     *
     * @return array{present:int, late:int, sick:int, permission:int, absent:int}
     */
    private function getAttendanceSummary(int $userId, string $start, string $end): array
    {
        $rows = Attendance::where('technician_id', $userId)
            ->whereBetween('date', [$start, $end])
            ->get(['status']);

        $summary = [
            'present'    => 0,  // checked_in + checked_out
            'late'       => 0,
            'sick'       => 0,
            'permission' => 0,
            'absent'     => 0,
        ];

        foreach ($rows as $row) {
            $val = $row->status instanceof AttendanceStatus
                ? $row->status->value
                : (string) $row->status;

            match ($val) {
                'checked_in', 'checked_out' => $summary['present']++,
                'late'                       => $summary['late']++,
                'sick'                       => $summary['sick']++,
                'permission', 'leave'        => $summary['permission']++,
                'absent'                     => $summary['absent']++,
                default                      => null,
            };
        }

        return $summary;
    }

    public function generate()
    {
        $this->validate([
            'month'        => 'required|numeric|min:1|max:12',
            'year'         => 'required|numeric|min:2000|max:2100',
            'period_start' => 'nullable|date',
            'period_end'   => 'nullable|date|after_or_equal:period_start',
        ]);

        $employees     = Employee::where('status', 'active')->get();
        $generatedCount = 0;

        $latePenalty   = 0; // Keterlambatan tidak memotong gaji
        $absentPenalty = (float) \App\Models\Setting::getValue('payroll.absent_penalty', 100000);

        $workDays = ($this->period_start && $this->period_end)
            ? $this->countWorkDays($this->period_start, $this->period_end)
            : 0;

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                $monthInt = (int) $this->month;
                $yearInt  = (int) $this->year;

                // === Rekap Absensi Otomatis ===
                $attSummary  = ['present' => 0, 'late' => 0, 'sick' => 0, 'permission' => 0, 'absent' => 0];
                $deductions  = 0;
                $attNotes    = [];

                if ($employee->user_id && $this->period_start && $this->period_end) {
                    $attSummary = $this->getAttendanceSummary(
                        $employee->user_id,
                        $this->period_start,
                        $this->period_end
                    );

                    // Rekap kehadiran dicatat untuk monitoring absensi
                    // Sesuai kebijakan perusahaan: TIDAK ADA POTONGAN DARI ABSENSI (potongan hanya jika ada kasbon)
                }

                $fullNotes = trim($this->notes ?? '');
                $netSalary = $employee->base_salary;

                $payloadBase = [
                    'period_start'    => $this->period_start ?: null,
                    'period_end'      => $this->period_end   ?: null,
                    'base_salary'     => $employee->base_salary,
                    'allowances'      => 0,
                    'deductions'      => $deductions,
                    'net_salary'      => $netSalary,
                    'status'          => 'draft',
                    'notes'           => $fullNotes ?: null,
                    // Kolom rekap absensi
                    'work_days'       => $workDays,
                    'present_days'    => $attSummary['present'],
                    'late_days'       => $attSummary['late'],
                    'sick_days'       => $attSummary['sick'],
                    'permission_days' => $attSummary['permission'],
                    'absent_days'     => $attSummary['absent'],
                ];

                $payroll = Payroll::withTrashed()
                    ->where('employee_id', $employee->id)
                    ->where('period_month', $monthInt)
                    ->where('period_year',  $yearInt)
                    ->first();

                if ($payroll) {
                    if ($payroll->trashed()) {
                        $payroll->restore();
                    }
                    $payroll->update($payloadBase);
                    $generatedCount++;
                } else {
                    Payroll::create(array_merge($payloadBase, [
                        'employee_id'  => $employee->id,
                        'period_month' => $monthInt,
                        'period_year'  => $yearInt,
                    ]));
                    $generatedCount++;
                }
            }

            DB::commit();

            session()->flash('success', "{$generatedCount} slip gaji berhasil digenerate untuk periode {$this->month}/{$this->year}.");
            return redirect()->route('admin.payroll.index');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal generate gaji: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.payroll.generate');
    }
}
