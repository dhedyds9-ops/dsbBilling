<div>
    <div class="mb-6 flex flex-col gap-3 justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">add_circle</span>
                Tugas Pemasangan Baru (PSB)
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Daftar pelanggan baru yang menunggu penarikan kabel dan instalasi perangkat.</p>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($jobs as $job)
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 flex flex-col gap-4 items-start justify-between hover:border-indigo-500 transition-colors">
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 flex items-center justify-center font-bold shadow-inner shrink-0">
                        <span class="material-symbols-outlined notranslate" translate="no">person_add</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $job->customer->name ?? 'Pelanggan Tidak Diketahui' }}</h3>
                            <span class="bg-blue-100 text-blue-800 text-[10px] font-bold px-2 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">PSB</span>
                            <span class="bg-slate-100 text-slate-800 text-[10px] font-bold px-2 py-0.5 rounded dark:bg-slate-700 dark:text-slate-300">{{ $job->uuid }}</span>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 max-w-xl">{{ $job->description }}</p>
                        <div class="mt-2 text-xs text-slate-500 flex items-center gap-4">
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined notranslate text-[14px]" translate="no">location_on</span> {{ $job->customer->address ?? 'Alamat tidak tersedia' }}</span>
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined notranslate text-[14px]" translate="no">calendar_today</span> {{ $job->created_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-col gap-2 w-full mt-2">
                    <a href="{{ route('technician.installation.wizard') }}?ticket={{ $job->id }}" class="flex-1 text-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                        Mulai Instalasi
                    </a>
                </div>
            </div>
        @empty
            <div class="py-12 text-center border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl">
                <div class="w-16 h-16 mx-auto bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined notranslate text-3xl text-slate-400" translate="no">check_circle</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Tidak Ada Tugas PSB</h3>
                <p class="text-slate-500 dark:text-slate-400">Anda tidak memiliki jadwal Pemasangan Baru saat ini.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $jobs->links() }}
    </div>
</div>

