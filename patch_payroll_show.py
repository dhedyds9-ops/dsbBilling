file_php = 'D:/dsBilling/app/Livewire/Admin/Payroll/Show.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

import re

# Add layout, sendWhatsApp method, etc.
replacement = """<?php

namespace App\\Livewire\\Admin\\Payroll;

use App\\Models\\Payroll;
use Livewire\\Component;
use App\\Services\\WhatsApp\\WhatsAppService;
use App\\Models\\Setting;
use Carbon\\Carbon;

class Show extends Component
{
    public Payroll $payroll;

    public function mount(Payroll $payroll)
    {
        $this->payroll = $payroll;
        $this->payroll->load('employee');
        $this->dispatch('set-active-menu', module: 'kepegawaian', page: 'payroll');
    }

    public function sendWhatsApp()
    {
        if (!$this->payroll->employee || !$this->payroll->employee->phone) {
            session()->flash('error', 'Karyawan tidak memiliki nomor WhatsApp.');
            return;
        }

        try {
            $waService = app(WhatsAppService::class);
            
            $month = Carbon::createFromDate($this->payroll->period_year, $this->payroll->period_month, 1)->translatedFormat('F Y');
            $net = number_format($this->payroll->net_salary, 0, ',', '.');
            $base = number_format($this->payroll->base_salary, 0, ',', '.');
            $allow = number_format($this->payroll->allowances, 0, ',', '.');
            $deduct = number_format($this->payroll->deductions, 0, ',', '.');
            $company = Setting::getValue('company.name', 'dsBilling');
            
            // Format pesan slip gaji
            $message = "*SLIP GAJI - {$company}*\\n\\n";
            $message .= "Halo {$this->payroll->employee->name},\\n";
            $message .= "Berikut adalah rincian gaji Anda untuk periode *{$month}*:\\n\\n";
            $message .= "A. Pendapatan\\n";
            $message .= "- Gaji Pokok: Rp {$base}\\n";
            $message .= "- Tunjangan: Rp {$allow}\\n\\n";
            $message .= "B. Potongan\\n";
            $message .= "- Potongan: Rp {$deduct}\\n\\n";
            $message .= "====================\\n";
            $message .= "*TOTAL DITERIMA: Rp {$net}*\\n";
            $message .= "====================\\n\\n";
            if ($this->payroll->status == 'paid') {
                $message .= "? Status: *SUDAH DIBAYAR*\\n";
                if ($this->payroll->payment_date) {
                    $date = Carbon::parse($this->payroll->payment_date)->translatedFormat('d F Y');
                    $message .= "?? Tanggal: {$date}\\n";
                }
            } else {
                $message .= "? Status: *{$this->payroll->status}*\\n";
            }
            $message .= "\\nAnda dapat mengunduh dan mencetak dokumen slip gaji secara lengkap melalui portal teknisi / karyawan.\\n\\n";
            $message .= "Terima kasih.";

            $waService->sendMessage($this->payroll->employee->phone, $message);

            session()->flash('success', 'Slip gaji berhasil dikirim ke WhatsApp karyawan.');
        } catch (\\Throwable $e) {
            session()->flash('error', 'Gagal mengirim WA: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.payroll.show')
            ->layout('layouts.app');
    }
}
"""

with open(file_php, 'w', encoding='utf-8') as f:
    f.write(replacement)
print("Admin Payroll Show updated")
