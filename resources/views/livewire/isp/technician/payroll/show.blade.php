@section('header_title', 'Slip Gaji')

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
