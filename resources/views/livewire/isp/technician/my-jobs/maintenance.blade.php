<div class="p-4 pb-24">
    <div class="mb-6 flex flex-col gap-3 justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">engineering</span>
                Jadwal Maintenance
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Daftar penugasan perbaikan, relokasi, atau pengecekan jaringan pelanggan.</p>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($jobs as $job)
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 flex flex-col gap-4 items-start justify-between hover:border-amber-500 transition-colors">
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-600 flex items-center justify-center font-bold shadow-inner shrink-0">
                        <span class="material-symbols-outlined notranslate" translate="no">build</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $job->customer->name ?? 'Jaringan Pusat' }}</h3>
                            <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded dark:bg-amber-900 dark:text-amber-300">MAINTENANCE</span>
                            <span class="bg-slate-100 text-slate-800 text-[10px] font-bold px-2 py-0.5 rounded dark:bg-slate-700 dark:text-slate-300">{{ $job->priority }}</span>
                        </div>
                        <h4 class="font-semibold text-slate-800 dark:text-slate-200 text-sm">{{ $job->title }}</h4>
                        <p class="text-sm text-slate-600 dark:text-slate-400 max-w-xl line-clamp-2">{{ $job->description }}</p>
                        <div class="mt-2 text-xs text-slate-500 flex items-center gap-4">
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined notranslate text-[14px]" translate="no">location_on</span> {{ $job->customer->address ?? 'Lokasi ODC/ODP' }}</span>
                            <span class="flex items-center gap-1 text-red-500"><span class="material-symbols-outlined notranslate text-[14px]" translate="no">schedule</span> {{ $job->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-col gap-2 w-full mt-2">
                    <a href="{{ route('technician.my-jobs.show', $job->id) }}" class="flex-1 text-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                        Kerjakan Tiket
                    </a>
                </div>
            </div>
        @empty
            <div class="py-12 text-center border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl">
                <div class="w-16 h-16 mx-auto bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined notranslate text-3xl text-slate-400" translate="no">thumb_up</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Jaringan Aman</h3>
                <p class="text-slate-500 dark:text-slate-400">Tidak ada jadwal maintenance atau perbaikan saat ini.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $jobs->links() }}
    </div>
</div>


