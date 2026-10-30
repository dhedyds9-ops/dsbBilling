@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">device_hub</span>
    <span class="text-lg">Data ODP (Read-Only)</span>
@endsection

<div class="space-y-5 pb-10">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="p-3 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 border-b border-slate-100 dark:border-slate-700/50">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2.5">
                <h2 class="font-bold text-slate-800 dark:text-slate-100 text-base sm:text-lg flex items-center gap-2">
                    Daftar ODP di Wilayah Anda
                </h2>
                @if(!empty($territory['label']))
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium {{ ($territory['has_territory'] ?? false) ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                        <span class="material-symbols-outlined notranslate" style="font-size: 14px" translate="no">location_on</span>
                        {{ $territory['label'] }}
                    </span>
                @endif
            </div>
            <div class="relative w-full sm:w-64">
                <span class="material-symbols-outlined notranslate absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none" translate="no">search</span>
                <input type="text" wire:model.live.debounce.500ms="search" placeholder="Cari ODP..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider">
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Kode / Nama</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Sumber (ODC/OLT) & Cabang</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Port (Terpakai/Total)</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Lokasi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($odps as $odp)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-5 py-4 text-slate-800 dark:text-slate-200 font-medium">
                                <span class="font-mono text-xs font-bold">{{ $odp->code }}</span><br>
                                <span class="text-xs text-slate-500">{{ $odp->name }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400">
                                <div class="font-medium text-slate-700 dark:text-slate-300 text-xs">{{ $odp->odc?->name ?? $odp->olt?->name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $odp->branch?->name ?? ($odp->district ?: ($odp->village ?: '-')) }}</div>
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400">
                                @php
                                    $used = $odp->used_port_count ?? 0;
                                    $total = $odp->port_count ?? 0;
                                    $percent = $total > 0 ? ($used / $total) * 100 : 0;
                                    $color = $percent > 80 ? 'text-red-500' : 'text-emerald-500';
                                @endphp
                                <span class="{{ $color }} font-bold">{{ $used }}</span> / {{ $total }}
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400">
                                @if($odp->latitude && $odp->longitude)
                                    <a href="https://maps.google.com/?q={{ $odp->latitude }},{{ $odp->longitude }}" target="_blank" class="text-indigo-500 hover:underline inline-flex items-center gap-1 text-xs font-medium">
                                        <span class="material-symbols-outlined notranslate" style="font-size: 16px" translate="no">location_on</span>
                                        Peta
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-slate-500">Tidak ada data ODP di wilayah ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($odps->hasPages())
            <div class="p-4 sm:p-5 border-t border-slate-100 dark:border-slate-700/50 overflow-x-auto">
                {{ $odps->links() }}
            </div>
        @endif
    </div>
</div>
