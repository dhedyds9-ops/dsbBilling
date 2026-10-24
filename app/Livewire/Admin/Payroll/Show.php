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

        // Jika slip draft masih menyimpan catatan denda lama (seperti "Terlambat 3x (-Rp 75.000)"), bersihkan otomatis
        if ($this->payroll->status === 'draft' && !empty($this->payroll->notes)) {
            $notesLower = strtolower($this->payroll->notes);
            if ((str_contains($notesLower, 'terlambat') || str_contains($notesLower, 'alpa')) && !str_contains($notesLower, 'kasbon')) {
                $cleaned = preg_replace('/Terlambat\s+\d+x(\s*\(-Rp\s*[\d\.,]+\))?/i', '', $this->payroll->notes);
                $cleaned = preg_replace('/Alpa\s+\d+x(\s*\(-Rp\s*[\d\.,]+\))?/i', '', $cleaned);
                $cleaned = trim(trim($cleaned, " \t\n\r\0\x0B|,-"));

                $this->payroll->notes = $cleaned ?: null;
                if ((float)$this->payroll->deductions == 75000 || (float)$this->payroll->deductions == 100000) {
                    $this->payroll->deductions = 0;
                    $this->payroll->net_salary = max(0, (float)$this->payroll->base_salary + (float)$this->payroll->allowances);
                }
                $this->payroll->save();
            }
        }
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

            $hasOldPenaltyNotes = !empty($this->payroll->notes) && (str_contains($this->payroll->notes, 'Terlambat') || str_contains($this->payroll->notes, 'Alpa')) && !str_contains(strtolower($this->payroll->notes), 'kasbon');
            $kasbonDeduct = $hasOldPenaltyNotes ? 0 : (float)$this->payroll->deductions;
            $netVal = max(0, (float)$this->payroll->base_salary + (float)$this->payroll->allowances - $kasbonDeduct);
            $net = number_format($netVal, 0, ',', '.');
            $deduct = number_format($kasbonDeduct, 0, ',', '.');

            $message .= "B. *Potongan*\n";
            if ($kasbonDeduct > 0) {
                $message .= "   Kasbon / Pinjaman : -Rp {$deduct}\n";
                if ($this->payroll->notes) {
                    $message .= "   Keterangan : {$this->payroll->notes}\n";
                }
            } else {
                $message .= "   Tidak ada potongan kasbon (Rp 0)\n";
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
