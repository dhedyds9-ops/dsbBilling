<div class="space-y-3.5 pb-6">
    @section('header_title', 'Tugas Pasang Baru (PSB)')
    <div class="bg-emerald-600 text-white p-3.5 sm:p-4 rounded-2xl shadow-md">
        <div class="flex items-center gap-2 mb-1">
            <span class="material-symbols-outlined text-lg sm:text-xl">person_add</span>
            <h2 class="text-base sm:text-lg font-black">Tugas Pemasangan Baru (PSB)</h2>
        </div>
        <p class="text-xs text-emerald-100">Daftar pelanggan baru yang menunggu penarikan kabel & instalasi perangkat.</p>
    </div>

    <div class="space-y-3">
        @forelse($jobs as $job)
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 flex flex-col gap-4 items-start justify-between hover:border-indigo-500 transition-colors">
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 flex items-center justify-center font-bold shadow-inner shrink-0">
                        <span class="material-symbols-outlined notranslate" translate="no">person_add</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $job->customer_name }}</h3>
                            <span class="{{ $job->badge_class }} text-[10px] font-bold px-2 py-0.5 rounded">{{ $job->type_label }}</span>
                            <span class="bg-slate-100 text-slate-800 text-[10px] font-bold px-2 py-0.5 rounded dark:bg-slate-700 dark:text-slate-300">{{ $job->uuid }}</span>
                            @if(!empty($job->reseller_name))
                                <span class="bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800 text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[12px] notranslate" translate="no">storefront</span>
                                    Mitra: {{ $job->reseller_name }}
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 max-w-xl">{{ $job->description }}</p>
                        <div class="mt-2 text-xs text-slate-500 flex items-center gap-4 flex-wrap">
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined notranslate text-[14px]" translate="no">call</span> {{ $job->customer_phone }}</span>
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined notranslate text-[14px]" translate="no">location_on</span> {{ $job->customer_address }}</span>
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined notranslate text-[14px]" translate="no">calendar_today</span> {{ $job->created_at ? \Carbon\Carbon::parse($job->created_at)->format('d M Y, H:i') : '-' }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-col gap-2 w-full mt-2">
                    <a href="{{ $job->wizard_url }}" class="flex-1 text-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm flex items-center justify-center gap-1.5 active:scale-95">
                        <span class="material-symbols-outlined text-base">rocket_launch</span>
                        <span>Mulai Instalasi (PSB)</span>
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
