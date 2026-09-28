@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">device_hub</span>
    <span class="text-lg">Data ODP (Read-Only)</span>
@endsection

<div class="space-y-5 pb-10">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-700/50">
            <h2 class="font-bold text-slate-800 dark:text-slate-100 text-lg flex items-center gap-2">
                Daftar ODP di Wilayah Anda
            </h2>
            <div class="flex items-center gap-2 w-full md:w-auto">
                <input type="text" wire:model.live.debounce.500ms="search" placeholder="Cari ODP..." class="w-full md:w-64 px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider">
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Kode / Nama</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Sumber (ODC/OLT)</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Port (Terpakai/Total)</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Lokasi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($odps as $odp)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-5 py-4 text-slate-800 dark:text-slate-200 font-medium">
                                {{ $odp->code }}<br>
                                <span class="text-xs text-slate-500">{{ $odp->name }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $odp->odc->name ?? $odp->olt->name ?? '-' }}</td>
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
                                    <a href="https://maps.google.com/?q={{ $odp->latitude }},{{ $odp->longitude }}" target="_blank" class="text-indigo-500 hover:underline flex items-center gap-1">
                                        <i class="bi bi-geo-alt"></i> Peta
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
            <div class="p-5 border-t border-slate-100 dark:border-slate-700/50">
                {{ $odps->links() }}
            </div>
        @endif
    </div>
</div>
