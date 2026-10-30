@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">device_hub</span>
    <span class="text-lg">Data ODP</span>
@endsection

<div class="space-y-5 pb-10">
    @if(isset($territory) && !$territory['has_territory'])
        <div class="p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 rounded-xl text-amber-800 dark:text-amber-300 text-sm flex items-start gap-3">
            <span class="material-symbols-outlined notranslate text-amber-500 shrink-0 mt-0.5" translate="no">info</span>
            <div>
                <p class="font-semibold">Cakupan Wilayah Belum Diatur pada Akun Anda</p>
                <p class="text-xs opacity-90 mt-0.5">Saat ini hanya ODP yang melayani pelanggan Anda secara langsung yang ditampilkan. Hubungi Administrator untuk mengatur <strong>Cabang Kantor</strong> atau <strong>Wilayah Operasional</strong> pada profil akun Reseller Anda.</p>
            </div>
        </div>
    @endif

    {{-- TOOLBAR & FILTER (SERAGAM DENGAN PORTAL ADMIN) --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 bg-white dark:bg-slate-800 p-3 sm:p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto shrink-0">
            @if(!empty($territory['label']))
                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold {{ ($territory['has_territory'] ?? false) ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/50' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                    <span class="material-symbols-outlined notranslate" style="font-size: 16px" translate="no">location_on</span>
                    {{ $territory['label'] }}
                </span>
            @endif
            <a href="{{ route('reseller-portal.network.map') }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-slate-50 hover:bg-slate-100 dark:bg-slate-700/50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg shadow-sm transition-all whitespace-nowrap">
                <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:18px">map</span>
                <span>Peta Jaringan</span>
            </a>
            <a href="{{ route('reseller-portal.network.odcs.index') }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-slate-50 hover:bg-slate-100 dark:bg-slate-700/50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg shadow-sm transition-all whitespace-nowrap">
                <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:18px">hub</span>
                <span>Data ODC</span>
            </a>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 w-full lg:w-auto">
            <div class="relative w-full sm:w-60">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Cari kode, nama, alamat..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            </div>
            <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 w-full sm:w-auto">
                <select wire:model.live="statusFilter"
                        class="w-full sm:w-auto pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
                <select wire:model.live="perPage"
                        class="w-full sm:w-auto pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="10">10 / hal</option>
                    <option value="25">25 / hal</option>
                    <option value="50">50 / hal</option>
                    <option value="100">100 / hal</option>
                </select>
                @if($search !== '' || $statusFilter !== '')
                    <button type="button" wire:click="resetFilters"
                            title="Reset Filter"
                            class="col-span-2 sm:col-auto inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-lg text-sm font-medium transition-colors cursor-pointer whitespace-nowrap">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">filter_alt_off</span>
                        <span>Reset</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- DATA TABLE (SERAGAM DENGAN PORTAL ADMIN) --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50/80 dark:bg-slate-800/80">
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">Status</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">Kode & Nama ODP</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">ODC Induk</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">Cabang / Wilayah</th>
                        <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Port</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">Alamat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-800">
                    @forelse($odps as $odp)
                        <tr wire:key="odp-{{ $odp->id }}" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 inline-flex items-center gap-1.5 text-xs font-semibold rounded-full {{ $odp->status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $odp->status === 'active' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $odp->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                                    {{ $odp->name }}
                                </div>
                                <div class="text-xs font-mono text-slate-500 dark:text-slate-400 mt-0.5">{{ $odp->code ?: '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $odp->odc?->name ?? '-' }}</div>
                                @if($odp->odc?->code)
                                    <div class="text-xs font-mono text-slate-400">{{ $odp->odc->code }}</div>
                                @elseif($odp->olt)
                                    <div class="text-xs text-slate-400">OLT: {{ $odp->olt->name }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($odp->branch)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                        {{ $odp->branch->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">Global</span>
                                @endif
                                @if($odp->district || $odp->village)
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ collect([$odp->village, $odp->district])->filter()->implode(', ') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                <span class="text-sm font-mono text-slate-700 dark:text-slate-300">
                                    @if($odp->used_port_count !== null && $odp->port_count !== null)
                                        {{ $odp->used_port_count }} / {{ $odp->port_count }}
                                    @else
                                        {{ $odp->port_count ?? '-' }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="text-sm text-slate-600 dark:text-slate-400 truncate max-w-[240px]" title="{{ $odp->address }}">{{ $odp->address ?: '-' }}</div>
                                @if($odp->latitude && $odp->longitude)
                                    <a href="https://www.google.com/maps?q={{ $odp->latitude }},{{ $odp->longitude }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-medium text-indigo-600 dark:text-indigo-400 hover:underline mt-0.5">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:12px">location_on</span>
                                        {{ $odp->latitude }}, {{ $odp->longitude }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-14 h-14 bg-slate-100 dark:bg-slate-700/60 rounded-full flex items-center justify-center mb-3">
                                        <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:28px">device_hub</span>
                                    </div>
                                    <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-1">Belum ada data ODP</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md">Tidak ditemukan perangkat ODP pada cakupan cabang atau wilayah akun Reseller Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($odps, 'hasPages') && $odps->hasPages())
            <div class="bg-white dark:bg-slate-800 px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                {{ $odps->links() }}
            </div>
        @endif
    </div>
</div>
