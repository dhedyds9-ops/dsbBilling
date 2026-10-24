<?php

namespace App\Livewire\Admin\Payroll;

use App\Livewire\AdminComponent;
use App\Models\Payroll;
use App\Models\Setting;
use App\Services\WhatsApp\WhatsAppService;
use Carbon\Carbon;

class Show extends AdminComponent
{
    public Payroll $payroll;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage   = 'payroll';
        $this->breadcrumbs  = [
            ['label' => 'Dashboard',     'url' => route('dashboard')],
            ['label' => 'Kepegawaian',   'url' => '#'],
            ['label' => 'Rekap Gaji',    'url' => route('admin.payroll.index')],
            ['label' => 'Detail Slip'],
        ];

        $this->payroll = Payroll::with('employee')->findOrFail($id);
    }

    public function sendWhatsApp()
    {
        if (!$this->payroll->employee || !$this->payroll->employee->phone) {
            session()->flash('error', 'Karyawan tidak memiliki nomor WhatsApp.');
            return;
        }

        try {
            $waService = app(WhatsAppService::class);

            $period = ($this->payroll->period_start && $this->payroll->period_end)
                ? Carbon::parse($this->payroll->period_start)->translatedFormat('d M Y')
                  . ' s/d '
                  . Carbon::parse($this->payroll->period_end)->translatedFormat('d M Y')
                : Carbon::createFromDate($this->payroll->period_year, $this->payroll->period_month, 1)
                        ->translatedFormat('F Y');

            $latePenalty   = (float) Setting::getValue('payroll.late_penalty', 25000);
            $absentPenalty = (float) Setting::getValue('payroll.absent_penalty', 100000);

            $net     = number_format($this->payroll->net_salary,   0, ',', '.');
            $base    = number_format($this->payroll->base_salary,  0, ',', '.');
            $allow   = number_format($this->payroll->allowances,   0, ',', '.');
            $deduct  = number_format($this->payroll->deductions,   0, ',', '.');
            $company = Setting::getValue('company.name', 'dsBilling');

            $message  = "*SLIP GAJI - {$company}*\n\n";
            $message .= "Halo {$this->payroll->employee->name},\n";
            $message .= "Berikut rincian gaji Anda periode *{$period}*:\n\n";

            // Rekap Absensi
            if ($this->payroll->work_days > 0) {
                $message .= "📋 *Rekap Kehadiran*\n";
                $message .= "- Hari Kerja  : {$this->payroll->work_days} hari\n";
                $message .= "- Hadir       : {$this->payroll->present_days} hari\n";
                $message .= "- Terlambat   : {$this->payroll->late_days} hari\n";
                $message .= "- Sakit/Izin  : " . (($this->payroll->sick_days ?? 0) + ($this->payroll->permission_days ?? 0)) . " hari\n";
                $message .= "- Alpa        : {$this->payroll->absent_days} hari\n\n";
            }

            $message .= "A. *Pendapatan*\n";
            $message .= "   Gaji Pokok  : Rp {$base}\n";
            $message .= "   Tunjangan   : Rp {$allow}\n\n";

            $message .= "B. *Potongan*\n";
            if ($this->payroll->late_days > 0) {
                $cutLate = number_format($this->payroll->late_days * $latePenalty, 0, ',', '.');
                $message .= "   Terlambat {$this->payroll->late_days}x : -Rp {$cutLate}\n";
            }
            if ($this->payroll->absent_days > 0) {
                $cutAbsent = number_format($this->payroll->absent_days * $absentPenalty, 0, ',', '.');
                $message .= "   Alpa {$this->payroll->absent_days}x      : -Rp {$cutAbsent}\n";
            }
            $message .= "   Total Potongan : -Rp {$deduct}\n\n";

            $message .= "====================\n";
            $message .= "*TOTAL DITERIMA: Rp {$net}*\n";
            $message .= "====================\n\n";

            $message .= ($this->payroll->status === 'paid')
                ? "✅ Status: *SUDAH DIBAYAR*\n"
                : "⏳ Status: *DRAFT / PENDING*\n";

            $message .= "\nTerima kasih.";

            $waService->sendMessage($this->payroll->employee->phone, $message);
            session()->flash('success', 'Slip gaji berhasil dikirim ke WhatsApp karyawan.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal mengirim WA: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $latePenalty   = (float) Setting::getValue('payroll.late_penalty', 25000);
        $absentPenalty = (float) Setting::getValue('payroll.absent_penalty', 100000);

        return view('livewire.admin.payroll.show', compact('latePenalty', 'absentPenalty'))
            ->layout('layouts.enterprise');
    }
}
