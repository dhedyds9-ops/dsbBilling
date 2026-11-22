@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">archive</span>
    <span class="text-lg">Data Joint Closure (FO Splice Box)</span>
@endsection

<div class="space-y-5 pb-10">
    {{-- SESSION FLASH --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl text-red-700 dark:text-red-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">error</span>
            {{ session('error') }}
        </div>
    @endif

    {{-- METRICS HUD SUMMARY --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="p-3.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
            <span class="text-xs text-slate-400 block font-medium">Total Closure</span>
            <span class="text-xl font-black text-slate-800 dark:text-slate-100 mt-1 block">{{ $stats['total'] }}</span>
            <span class="text-[11px] text-slate-400">Titik Sambungan FO</span>
        </div>
        <div class="p-3.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
            <span class="text-xs text-slate-400 block font-medium">Total Kapasitas</span>
            <span class="text-xl font-black text-indigo-600 dark:text-indigo-400 mt-1 block">{{ number_format($stats['total_cores']) }}</span>
            <span class="text-[11px] text-indigo-500 font-semibold">Total Core Optik</span>
        </div>
        <div class="p-3.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
            <span class="text-xs text-slate-400 block font-medium">Status Aktif</span>
            <span class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1 block">{{ $stats['active'] }}</span>
            <span class="text-[11px] text-emerald-500 font-semibold">Beroperasi Normal</span>
        </div>
        <div class="p-3.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
            <span class="text-xs text-slate-400 block font-medium">Model Dome</span>
            <span class="text-xl font-black text-purple-600 dark:text-purple-400 mt-1 block">{{ $stats['dome'] }}</span>
            <span class="text-[11px] text-purple-500">Kubah Vertikal Tiang</span>
        </div>
        <div class="p-3.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
            <span class="text-xs text-slate-400 block font-medium">Model Inline</span>
            <span class="text-xl font-black text-cyan-600 dark:text-cyan-400 mt-1 block">{{ $stats['inline'] }}</span>
            <span class="text-[11px] text-cyan-500">Horizontal Jalur</span>
        </div>
        <div class="p-3.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
            <span class="text-xs text-slate-400 block font-medium">Model Wall</span>
            <span class="text-xl font-black text-amber-600 dark:text-amber-400 mt-1 block">{{ $stats['wall'] }}</span>
            <span class="text-[11px] text-amber-500">Dinding / Handhole</span>
        </div>
    </div>

    {{-- TOOLBAR & FILTER --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 bg-white dark:bg-slate-800 p-3 sm:p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto shrink-0">
            <a href="{{ route('isp.joint-closures.create') }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm shadow-indigo-200 dark:shadow-none transition-all whitespace-nowrap">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">add</span>
                <span>Tambah Joint Closure</span>
            </a>
            <a href="{{ route('gis.map') }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-sm font-medium rounded-lg transition-all whitespace-nowrap">
                <span class="material-symbols-outlined notranslate text-emerald-500" translate="no" style="font-size:18px">map</span>
                <span>Lihat di Peta GIS</span>
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
                <select wire:model.live="filters.type"
                        class="w-full sm:w-auto pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="">Semua Model</option>
                    <option value="Dome">Dome</option>
                    <option value="Inline">Inline</option>
                    <option value="Wall Mount">Wall Mount</option>
                </select>

                <select wire:model.live="filters.port_count"
                        class="w-full sm:w-auto pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="">Semua Core</option>
                    <option value="4">4 Core</option>
                    <option value="6">6 Core</option>
                    <option value="8">8 Core</option>
                    <option value="12">12 Core</option>
                    <option value="24">24 Core</option>
                    <option value="48">48 Core</option>
                    <option value="96">96 Core</option>
                    <option value="144">144 Core</option>
                </select>

                <select wire:model.live="filters.status"
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

                <label class="col-span-2 sm:col-auto inline-flex items-center justify-center gap-2 px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 cursor-pointer whitespace-nowrap">
                    <input type="checkbox" wire:model.live="showTrashed" class="w-4 h-4 text-indigo-600 border-slate-300 dark:border-slate-600 rounded focus:ring-indigo-500 dark:bg-slate-800" />
                    <span>Sampah</span>
                </label>

                @if($search || ($filters['status'] ?? '') || ($filters['type'] ?? '') || ($filters['port_count'] ?? '') || $showTrashed)
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

    {{-- BULK ACTIONS BANNER --}}
    @if(!empty($selectedClosures))
        <div class="flex flex-wrap items-center justify-between gap-2 p-3 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 rounded-xl">
            <span class="text-sm text-indigo-800 dark:text-indigo-300 font-semibold">{{ count($selectedClosures) }} Joint Closure dipilih</span>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="bulkActivate" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold">Aktifkan Terpilih</button>
                <button type="button" wire:click="bulkDeactivate" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-semibold">Nonaktifkan</button>
                <button type="button" wire:click="bulkDelete" wire:confirm="Yakin ingin menghapus seluruh closure terpilih?" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">Hapus Terpilih</button>
            </div>
        </div>
    @endif

    {{-- DATA TABLE --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="p-4 w-10">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        </th>
                        <th class="p-4">Identitas Closure</th>
                        <th class="p-4">Model & Tipe</th>
                        <th class="p-4">Kapasitas Core</th>
                        <th class="p-4">Uplink (Jalur Induk)</th>
                        <th class="p-4">Downlink Cabang</th>
                        <th class="p-4">Lokasi & Tiang</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                    @forelse($closures as $c)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="p-4">
                                <input type="checkbox" wire:model.live="selectedClosures" value="{{ $c->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            </td>
                            <td class="p-4">
                                <a href="{{ route('isp.joint-closures.show', $c->id) }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline block">
                                    {{ $c->name }}
                                </a>
                                <span class="text-xs font-mono text-slate-400 block">{{ $c->code }}</span>
                            </td>
                            <td class="p-4">
                                @php
                                    $typeBadge = match(strtolower((string)$c->type)) {
                                        'dome' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800',
                                        'inline' => 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-950/40 dark:text-cyan-300 dark:border-cyan-800',
                                        'wall mount', 'wall' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                                        default => 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-700',
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $typeBadge }}">
                                    <span class="material-symbols-outlined text-xs">inventory_2</span>
                                    <span>{{ $c->type ?: 'Dome' }}</span>
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-100">
                                    <span class="material-symbols-outlined text-xs text-indigo-500">cable</span>
                                    <span>{{ $c->port_count ?: 24 }} Core</span>
                                </span>
                            </td>
                            <td class="p-4 text-xs">
                                @if($c->parentClosure)
                                    <span class="text-indigo-600 dark:text-indigo-400 font-semibold block">Cascade Closure</span>
                                    <a href="{{ route('isp.joint-closures.show', $c->parentClosure->id) }}" class="hover:underline text-slate-600 dark:text-slate-300 font-mono">
                                        {{ $c->parentClosure->name }}
                                    </a>
                                @elseif($c->odc)
                                    <span class="text-amber-600 dark:text-amber-400 font-semibold block">ODC Distribusi</span>
                                    <a href="{{ route('isp.odcs.show', $c->odc->id) }}" class="hover:underline text-slate-600 dark:text-slate-300 font-mono">
                                        {{ $c->odc->name }}
                                    </a>
                                @elseif($c->olt)
                                    <span class="text-sky-600 dark:text-sky-400 font-semibold block">OLT Feeder</span>
                                    <span class="text-slate-600 dark:text-slate-300 font-mono">{{ $c->olt->name }} {{ $c->pon_port ? '('.$c->pon_port.')' : '' }}</span>
                                @else
                                    <span class="text-slate-400 italic">Belum dihubungkan</span>
                                @endif
                            </td>
                            <td class="p-4 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold" title="ODP Terhubung">
                                        {{ $c->odps_count ?? 0 }} ODP
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 text-[11px] font-bold" title="Sub-ODC Terhubung">
                                        {{ $c->odcs_count ?? 0 }} ODC
                                    </span>
                                    @if(!empty($c->child_closures_count))
                                        <span class="px-2 py-0.5 rounded-md bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 text-[11px] font-bold" title="Child Closure">
                                            {{ $c->child_closures_count }} Sub-JC
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4 text-xs max-w-xs">
                                <span class="truncate block text-slate-700 dark:text-slate-300" title="{{ $c->address }}">{{ $c->address ?: '-' }}</span>
                                @if($c->latitude && $c->longitude)
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $c->latitude }},{{ $c->longitude }}" target="_blank" class="text-indigo-500 hover:underline font-mono text-[11px] flex items-center gap-0.5 mt-0.5">
                                        <span class="material-symbols-outlined text-xs">pin_drop</span>
                                        <span>{{ round($c->latitude, 4) }}, {{ round($c->longitude, 4) }}</span>
                                    </a>
                                @endif
                            </td>
                            <td class="p-4">
                                @if($c->status === 'active')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Aktif</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        <span>Nonaktif</span>
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('isp.joint-closures.show', $c->id) }}" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 hover:text-indigo-600 rounded-lg transition" title="Lihat Detail">
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                    </a>
                                    <a href="{{ route('isp.joint-closures.edit', $c->id) }}" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 hover:text-amber-600 rounded-lg transition" title="Edit">
                                        <span class="material-symbols-outlined text-sm">edit</span>
                                    </a>
                                    <button type="button" wire:click="duplicate({{ $c->id }})" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 hover:text-cyan-600 rounded-lg transition cursor-pointer" title="Duplikasi">
                                        <span class="material-symbols-outlined text-sm">content_copy</span>
                                    </button>
                                    @if($c->trashed())
                                        <button type="button" wire:click="restore({{ $c->id }})" class="p-1.5 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-emerald-600 rounded-lg transition cursor-pointer" title="Restore">
                                            <span class="material-symbols-outlined text-sm">restore</span>
                                        </button>
                                    @else
                                        <button type="button" wire:click="delete({{ $c->id }})" wire:confirm="Yakin ingin menghapus Joint Closure ini?" class="p-1.5 hover:bg-red-50 dark:hover:bg-red-950/40 text-red-600 rounded-lg transition cursor-pointer" title="Hapus">
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-400">
                                <span class="material-symbols-outlined text-4xl mb-2 text-slate-300 block">archive</span>
                                <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">Belum ada data Joint Closure</p>
                                <p class="text-xs text-slate-400 mt-0.5">Tambahkan Joint Closure sambungan kabel optik pertama Anda.</p>
                                <a href="{{ route('isp.joint-closures.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 mt-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition">
                                    <span class="material-symbols-outlined text-sm">add</span> Tambah Joint Closure
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($closures->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700">
                {{ $closures->links() }}
            </div>
        @endif
    </div>
</div>
