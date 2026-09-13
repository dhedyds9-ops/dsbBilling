import os

os.makedirs('D:/dsBilling/app/Livewire/ISP/Technician/Payroll', exist_ok=True)
os.makedirs('D:/dsBilling/resources/views/livewire/isp/technician/payroll', exist_ok=True)

index_php = """<?php
namespace App\\Livewire\\ISP\\Technician\\Payroll;

use App\\Models\\Payroll;
use Livewire\\Component;

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
"""

show_php = """<?php
namespace App\\Livewire\\ISP\\Technician\\Payroll;

use App\\Models\\Payroll;
use Livewire\\Component;

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
"""

index_blade = """@section('header_title', 'Daftar Slip Gaji')

<div class="space-y-4 p-4">
    @if($payrolls->isEmpty())
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 text-center border border-slate-200 dark:border-slate-700">
            <span class="material-symbols-outlined text-slate-300 dark:text-slate-600 mb-2" style="font-size:48px">request_quote</span>
            <h3 class="text-slate-500 dark:text-slate-400 font-semibold text-sm">Belum ada data slip gaji</h3>
        </div>
    @else
        <div class="space-y-3">
            @foreach($payrolls as $p)
                <a href="{{ route('technician.payroll.show', $p->id) }}" class="block bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm active:scale-[0.98] transition-transform">
                    <div class="flex justify-between items-center mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 flex items-center justify-center">
                                <span class="material-symbols-outlined" style="font-size:20px">receipt_long</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ \\Carbon\\Carbon::createFromDate($p->period_year, $p->period_month, 1)->translatedFormat('F Y') }}</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider">Rp {{ number_format($p->net_salary, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-slate-400" style="font-size:20px">chevron_right</span>
                    </div>
                    <div class="flex gap-2 text-[10px] font-bold uppercase tracking-wider mt-3">
                        @if($p->status == 'paid')
                            <span class="bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 px-2 py-1 rounded-md">Dibayar</span>
                        @else
                            <span class="bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 px-2 py-1 rounded-md">Belum Dibayar</span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
"""

show_blade = """@section('header_title', 'Slip Gaji')

<div class="p-4 space-y-4">
    <div class="flex gap-2 print-hide">
        <button onclick="window.print()" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 font-bold text-sm flex justify-center items-center gap-2 transition-colors">
            <span class="material-symbols-outlined" style="font-size:20px">print</span> Cetak
        </button>
        <button onclick="history.back()" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl px-4 py-2.5 font-bold text-sm transition-colors">
            Kembali
        </button>
    </div>

    <!-- Gunakan layout persis sama dengan versi admin -->
    @include('livewire.admin.payroll.show')

</div>
"""

with open('D:/dsBilling/app/Livewire/ISP/Technician/Payroll/Index.php', 'w', encoding='utf-8') as f:
    f.write(index_php)
with open('D:/dsBilling/app/Livewire/ISP/Technician/Payroll/Show.php', 'w', encoding='utf-8') as f:
    f.write(show_php)
with open('D:/dsBilling/resources/views/livewire/isp/technician/payroll/index.blade.php', 'w', encoding='utf-8') as f:
    f.write(index_blade)
with open('D:/dsBilling/resources/views/livewire/isp/technician/payroll/show.blade.php', 'w', encoding='utf-8') as f:
    f.write(show_blade)

print("Technician Payroll views generated")
