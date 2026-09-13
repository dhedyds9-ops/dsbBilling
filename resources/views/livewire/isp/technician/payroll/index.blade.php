@section('header_title', 'Daftar Slip Gaji')

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
                                <h3 class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ \Carbon\Carbon::createFromDate($p->period_year, $p->period_month, 1)->translatedFormat('F Y') }}</h3>
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
