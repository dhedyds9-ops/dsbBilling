@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">dns</span>
    <span class="text-lg">Data OLT</span>
@endsection

<div class="space-y-5 pb-10">
    @if(isset($territory) && !$territory['has_territory'])
        <div class="p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 rounded-xl text-amber-800 dark:text-amber-300 text-sm flex items-start gap-3">
            <span class="material-symbols-outlined notranslate text-amber-500 shrink-0 mt-0.5" translate="no">info</span>
            <div>
                <p class="font-semibold">Cakupan Wilayah Belum Diatur pada Akun Anda</p>
                <p class="text-xs opacity-90 mt-0.5">Saat ini hanya OLT yang melayani pelanggan Anda secara langsung yang ditampilkan. Hubungi Administrator untuk mengatur <strong>Cabang Kantor</strong> atau <strong>Wilayah Operasional</strong> pada profil akun Reseller Anda.</p>
            </div>
        </div>
    @endif

    {{-- KARTU STATISTIK (SERAGAM DENGAN PORTAL ADMIN) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total OLT</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ $stats['total_olt'] ?? 0 }}</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:24px">dns</span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">OLT Aktif</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ $stats['active_olt'] ?? 0 }}</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:24px">check_circle</span>
            </div>
        </div>

        <a href="{{ route('reseller-portal.network.onus.index', ['statusFilter' => 'online']) }}" class="group bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:border-emerald-300 dark:hover:border-emerald-600 hover:shadow-md transition-all flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">ONU Online</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['online_onu'] ?? 0 }}</span>
                    @if(($stats['total_onu'] ?? 0) > 0)
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/40 px-1.5 py-0.5 rounded">{{ $stats['online_percentage'] ?? 0 }}%</span>
                    @endif
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 group-hover:scale-105 transition-transform flex items-center justify-center">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:24px">wifi</span>
            </div>
        </a>

        <a href="{{ route('reseller-portal.network.onus.index', ['statusFilter' => 'offline']) }}" class="group bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:border-red-300 dark:hover:border-red-600 hover:shadow-md transition-all flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">ONU Offline</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-red-600 dark:text-red-400">{{ $stats['offline_onu'] ?? 0 }}</span>
                    <span class="text-xs font-medium text-red-500 dark:text-red-400">Terputus</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-red-50 dark:bg-red-900/40 text-red-600 dark:text-red-400 group-hover:scale-105 transition-transform flex items-center justify-center">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:24px">wifi_off</span>
            </div>
        </a>
    </div>

    {{-- TOOLBAR & FILTER --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 bg-white dark:bg-slate-800 p-3 sm:p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto shrink-0">
            @if(!empty($territory['label']))
                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold {{ ($territory['has_territory'] ?? false) ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/50' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                    <span class="material-symbols-outlined notranslate" style="font-size: 16px" translate="no">location_on</span>
                    {{ $territory['label'] }}
                </span>
            @endif
            <a href="{{ route('reseller-portal.network.onus.index') }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-slate-50 hover:bg-slate-100 dark:bg-slate-700/50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg shadow-sm transition-all whitespace-nowrap">
                <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:18px">router</span>
                <span>Data ONU</span>
            </a>
            <a href="{{ route('reseller-portal.network.acs-devices.index') }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all whitespace-nowrap">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">settings_remote</span>
                <span>Devices GenieACS</span>
            </a>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 w-full lg:w-auto">
            <div class="relative w-full sm:w-60">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Cari kode, nama, IP..."
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
                        <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Aksi</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">Status</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">Kode & Nama OLT</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">IP Address</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">POP & Vendor</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">Cabang / Wilayah</th>
                        <th scope="col" class="px-4 py-3 text-center whitespace-nowrap text-emerald-600 dark:text-emerald-400">ONU Online</th>
                        <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">ONU Offline</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-800">
                    @forelse($olts as $olt)
                        @php
                            $onuOnline  = $olt->onus_online_count  ?? 0;
                            $onuOffline = $olt->onus_offline_count ?? 0;
                        @endphp
                        <tr wire:key="olt-{{ $olt->id }}" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('reseller-portal.network.onus.index', ['oltFilter' => $olt->id]) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-purple-50 hover:bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:hover:bg-purple-900/50 dark:text-purple-400 transition-colors" title="Lihat ONU Terpasang">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">router</span>
                                    </a>
                                    <a href="{{ route('reseller-portal.network.olts.show', $olt->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 dark:text-blue-400 transition-colors" title="Detail OLT & Distribusi PON">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">visibility</span>
                                    </a>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 inline-flex items-center gap-1.5 text-xs font-semibold rounded-full {{ $olt->status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $olt->status === 'active' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $olt->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                                @if(!empty($olt->last_error) && $olt->status !== 'active')
                                    <div class="mt-1 flex items-center gap-1 text-[11px] text-amber-600 dark:text-amber-400 font-medium" title="{{ $olt->last_error }}">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">error</span>
                                        <span>Kendala Sync</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                                    <a href="{{ route('reseller-portal.network.olts.show', $olt->id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                        {{ $olt->name }}
                                    </a>
                                </div>
                                <div class="text-xs font-mono text-slate-500 dark:text-slate-400 mt-0.5">{{ $olt->code ?: '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-mono text-sm text-slate-700 dark:text-slate-200 font-medium">
                                    {{ $olt->ip_address ?: '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $olt->pop?->name ?? '-' }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">Vendor: {{ $olt->vendor?->name ?? ($olt->model ?: '-') }}</div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($olt->branch)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                        {{ $olt->branch->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">Global</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                @if($onuOnline > 0)
                                    <a href="{{ route('reseller-portal.network.onus.index', ['oltFilter' => $olt->id, 'statusFilter' => 'online']) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-200 dark:hover:bg-emerald-800/50 transition-colors" title="Lihat ONU Online">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                        {{ $onuOnline }}
                                    </a>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                @if($onuOffline > 0)
                                    <a href="{{ route('reseller-portal.network.onus.index', ['oltFilter' => $olt->id, 'statusFilter' => 'offline']) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 hover:bg-red-100 dark:bg-red-500/10 dark:hover:bg-red-500/20 text-red-600 dark:text-red-400 transition-colors" title="Lihat ONU Offline">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>
                                        {{ $onuOffline }}
                                    </a>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-14 h-14 bg-slate-100 dark:bg-slate-700/60 rounded-full flex items-center justify-center mb-3">
                                        <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:28px">dns</span>
                                    </div>
                                    <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-1">Belum ada data OLT</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md">Pastikan Cabang Kantor pada akun Reseller Anda sama dengan Cabang Kantor pada perangkat OLT, atau kata kunci Wilayah cocok dengan lokasi OLT/POP.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($olts, 'hasPages') && $olts->hasPages())
            <div class="bg-white dark:bg-slate-800 px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                {{ $olts->links() }}
            </div>
        @endif
    </div>
</div>
