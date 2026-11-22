@section('page_title')
    <a href="{{ route('isp.odps.index') }}" class="inline-flex items-center justify-center p-1.5 mr-1 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Kembali ke Daftar ODP">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">arrow_back</span>
    </a>
    <span class="material-symbols-outlined notranslate text-emerald-500" translate="no" style="font-size:24px">share</span>
    <span class="text-lg font-bold">ODP Detail: {{ $odp->name }}</span>
    <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-mono font-bold uppercase tracking-wider {{ $odp->status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
        {{ $odp->status === 'active' ? 'Aktif Operasional' : 'Nonaktif' }}
    </span>
@endsection

<div class="space-y-5 pb-12">
    {{-- SESSION NOTIFICATION --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 rounded-2xl text-emerald-800 dark:text-emerald-300 text-xs font-bold shadow-xs">
            <span class="material-symbols-outlined notranslate text-base" translate="no">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-950/40 border border-red-300 dark:border-red-800 rounded-2xl text-red-800 dark:text-red-300 text-xs font-bold shadow-xs">
            <span class="material-symbols-outlined notranslate text-base" translate="no">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- HEADER CARD: NOC FIBER DISTRIBUTION OVERVIEW --}}
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-5 sm:p-6 shadow-lg border border-slate-800 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-40 -top-10 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 relative z-10">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-1 bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 rounded-lg text-xs font-mono font-bold uppercase tracking-wider">
                        ODP • OPTICAL DISTRIBUTION POINT
                    </span>
                    <span class="px-2.5 py-1 bg-white/10 text-slate-200 rounded-lg text-xs font-mono">
                        {{ $odp->code }}
                    </span>
                    @if($odp->branch)
                        <span class="px-2.5 py-1 bg-indigo-500/20 text-indigo-200 border border-indigo-500/40 rounded-lg text-xs font-semibold">
                            Cabang: {{ $odp->branch->name }}
                        </span>
                    @endif
                </div>

                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                    {{ $odp->name }}
                </h1>

                <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-slate-300">
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-amber-400 text-sm">hub</span>
                        @if($odp->parent)
                            <span class="material-symbols-outlined text-amber-400 text-sm">hub</span>
                            Cascade ODP Induk:
                            <a href="{{ route('isp.odps.show', $odp->parent->id) }}" class="font-bold text-amber-300 hover:underline">
                                {{ $odp->parent->name }} ({{ $odp->parent->code }})
                            </a>
                        @elseif($odp->odc)
                            <span class="material-symbols-outlined text-amber-400 text-sm">hub</span>
                            Uplink ODC:
                            <a href="{{ route('isp.odcs.show', $odp->odc->id) }}" class="font-bold text-amber-300 hover:underline">
                                {{ $odp->odc->name }} ({{ $odp->odc->code }})
                            </a>
                            @if($odp->odc_port)
                                <span class="px-1.5 py-0.5 bg-amber-500/20 text-amber-300 rounded font-mono text-[10px]">Port {{ $odp->odc_port }}</span>
                            @endif
                        @else
                            <span class="material-symbols-outlined text-amber-400 text-sm">hub</span>
                            Uplink: <span class="text-slate-400">Belum Ditentukan</span>
                        @endif
                    </span>

                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-blue-400 text-sm">dns</span>
                        OLT Core:
                        <span class="font-semibold text-slate-200">
                            {{ $odp->odc?->olt?->name ?? ($odp->olt?->name ?? 'OLT Jaringan') }}
                        </span>
                    </span>

                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-emerald-400 text-sm">call_split</span>
                        Splitter:
                        <span class="font-semibold text-emerald-300">
                            1:{{ $odp->port_count ?: 8 }} (-{{ $odp->port_count == 16 ? '13.5' : '10.5' }} dB)
                        </span>
                    </span>

                    @if($odp->latitude && $odp->longitude)
                        <span class="flex items-center gap-1.5 font-mono text-cyan-300">
                            <span class="material-symbols-outlined text-cyan-400 text-sm">location_on</span>
                            {{ number_format($odp->latitude, 5) }}, {{ number_format($odp->longitude, 5) }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                @if($odp->latitude && $odp->longitude)
                    <a href="https://www.google.com/maps?q={{ $odp->latitude }},{{ $odp->longitude }}" target="_blank"
                       class="px-3.5 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 backdrop-blur-xs border border-white/10">
                        <span class="material-symbols-outlined text-sm text-cyan-300">map</span>
                        Google Maps
                    </a>
                @endif
                <a href="/gis/map?focus_odp={{ $odp->id }}" target="_blank"
                   class="px-3.5 py-2 bg-indigo-600/60 hover:bg-indigo-600 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 backdrop-blur-xs border border-indigo-400/30">
                    <span class="material-symbols-outlined text-sm text-indigo-200">travel_explore</span>
                    Buka Peta GIS
                </a>
                <a href="{{ route('isp.odps.edit', $odp->id) }}"
                   class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                    <span class="material-symbols-outlined text-sm">edit</span>
                    Edit ODP & Foto
                </a>
            </div>
        </div>

        {{-- NOC METRICS BAR (Tampil di Overview, disembunyikan di tab Topologi agar diagram leluasa) --}}
        @if($activeTab !== 'topology')
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mt-6 pt-5 border-t border-slate-800/80">
                <div class="bg-slate-900/60 rounded-xl p-3 border border-slate-800">
                    <span class="text-[11px] text-slate-400 uppercase font-semibold block">Total Port</span>
                    <span class="text-xl font-black text-white font-mono">{{ $stats['total_ports'] }}</span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">Kapasitas Box</span>
                </div>

                <div class="bg-slate-900/60 rounded-xl p-3 border border-slate-800">
                    <span class="text-[11px] text-emerald-400 uppercase font-semibold block">Port Terpakai</span>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-xl font-black text-emerald-400 font-mono">{{ $stats['used_ports'] }}</span>
                        <span class="text-xs text-slate-400 font-medium">({{ $stats['occupancy_pct'] }}%)</span>
                    </div>
                    <div class="w-full bg-slate-800 h-1.5 rounded-full mt-1.5 overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $stats['occupancy_pct'] }}%"></div>
                    </div>
                </div>

                <div class="bg-slate-900/60 rounded-xl p-3 border border-slate-800">
                    <span class="text-[11px] text-sky-400 uppercase font-semibold block">Port Kosong</span>
                    <span class="text-xl font-black text-sky-400 font-mono">{{ $stats['available_ports'] }}</span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">Siap Pasang Baru</span>
                </div>

                <div class="bg-slate-900/60 rounded-xl p-3 border border-slate-800">
                    <span class="text-[11px] text-amber-400 uppercase font-semibold block">Reserved</span>
                    <span class="text-xl font-black text-amber-400 font-mono">{{ $stats['reserved_ports'] }}</span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">Booking / SPK PSB</span>
                </div>

                <div class="bg-slate-900/60 rounded-xl p-3 border border-slate-800">
                    <span class="text-[11px] text-rose-400 uppercase font-semibold block">Port Gangguan</span>
                    <span class="text-xl font-black text-rose-400 font-mono">{{ $stats['fault_ports'] }}</span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">Redaman / Rusak</span>
                </div>

                <div class="bg-slate-900/60 rounded-xl p-3 border border-slate-800">
                    <span class="text-[11px] text-cyan-400 uppercase font-semibold block">ONT / Pelanggan</span>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-xl font-black text-cyan-300 font-mono">{{ $stats['total_customers'] }}</span>
                        <span class="text-xs text-emerald-400 font-semibold">({{ $stats['online_ports'] }} Online)</span>
                    </div>
                    <span class="text-[10px] text-slate-400 block mt-0.5">GenieACS Terpantau</span>
                </div>
            </div>
        @endif
    </div>

    {{-- NAVIGATION TABS --}}
    <div class="flex items-center gap-2 overflow-x-auto border-b border-slate-200 dark:border-slate-800 pb-2 scrollbar-none">
        <button type="button" wire:click="setTab('overview')"
                class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 whitespace-nowrap transition cursor-pointer {{ $activeTab === 'overview' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 border border-slate-200 dark:border-slate-700' }}">
            <span class="material-symbols-outlined text-base">dashboard</span>
            <span>Ringkasan ODP</span>
        </button>

        <button type="button" wire:click="setTab('topology')"
                class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 whitespace-nowrap transition cursor-pointer {{ $activeTab === 'topology' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 border border-slate-200 dark:border-slate-700' }}">
            <span class="material-symbols-outlined text-base">account_tree</span>
            <span>Topologi Visual Jaringan</span>
        </button>

        <button type="button" wire:click="setTab('ports')"
                class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 whitespace-nowrap transition cursor-pointer {{ $activeTab === 'ports' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 border border-slate-200 dark:border-slate-700' }}">
            <span class="material-symbols-outlined text-base">grid_view</span>
            <span>Port Management</span>
            <span class="px-1.5 py-0.2 bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-full text-[10px] font-mono font-bold">{{ $stats['used_ports'] }}/{{ $stats['total_ports'] }}</span>
        </button>

        <button type="button" wire:click="setTab('onus')"
                class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 whitespace-nowrap transition cursor-pointer {{ $activeTab === 'onus' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 border border-slate-200 dark:border-slate-700' }}">
            <span class="material-symbols-outlined text-base">router</span>
            <span>ONU & Telemetri GenieACS</span>
            <span class="px-1.5 py-0.2 bg-blue-500/20 text-blue-600 dark:text-blue-400 rounded-full text-[10px] font-mono font-bold">{{ $stats['total_onus'] }}</span>
        </button>

        <button type="button" wire:click="setTab('customers')"
                class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 whitespace-nowrap transition cursor-pointer {{ $activeTab === 'customers' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 border border-slate-200 dark:border-slate-700' }}">
            <span class="material-symbols-outlined text-base">group</span>
            <span>Daftar Pelanggan</span>
            <span class="px-1.5 py-0.2 bg-purple-500/20 text-purple-600 dark:text-purple-400 rounded-full text-[10px] font-mono font-bold">{{ count($connectedCustomers) }}</span>
        </button>

        <button type="button" wire:click="setTab('history')"
                class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 whitespace-nowrap transition cursor-pointer {{ $activeTab === 'history' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 border border-slate-200 dark:border-slate-700' }}">
            <span class="material-symbols-outlined text-base">history</span>
            <span>Riwayat & Tiket Gangguan</span>
        </button>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 1: OVERVIEW --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'overview')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Kolom Kiri: Informasi Teknis & Lokasi ODP --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-500 text-lg">info</span>
                            Spesifikasi & Distribusi Optik ODP
                        </h3>
                        <span class="text-xs text-slate-400 font-mono">{{ $odp->code }}</span>
                    </div>
                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 block mb-0.5">Nama ODP</span>
                            <span class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $odp->name }}</span>
                        </div>

                        <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 block mb-0.5">Kode ODP</span>
                            <span class="text-sm font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $odp->code }}</span>
                        </div>

                        <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 block mb-0.5">Kabinet ODC Induk</span>
                            @if($odp->odc)
                                <a href="{{ route('isp.odcs.show', $odp->odc->id) }}" class="text-sm font-bold text-amber-600 dark:text-amber-400 hover:underline">
                                    {{ $odp->odc->name }} ({{ $odp->odc->code }})
                                </a>
                            @else
                                <span class="text-sm font-semibold text-slate-500">-</span>
                            @endif
                        </div>

                        <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 block mb-0.5">Port ODC Feeder</span>
                            <span class="text-sm font-mono font-bold text-amber-600 dark:text-amber-400">
                                {{ $odp->odc_port ? 'Port ' . $odp->odc_port : 'Direct Feeder / Port 1' }}
                            </span>
                        </div>

                        <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 block mb-0.5">Tipe Splitter Rasio</span>
                            <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                1:{{ $odp->port_count ?: 8 }} (Redaman ~{{ $odp->port_count == 16 ? '13.5' : '10.5' }} dB)
                            </span>
                        </div>

                        <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 block mb-0.5">Total Kapasitas Port</span>
                            <span class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                {{ $odp->port_count ?: 8 }} Port Output ONU
                            </span>
                        </div>

                        <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 block mb-0.5">Koordinat Latitude</span>
                            <span class="text-sm font-mono font-semibold text-slate-800 dark:text-slate-100">{{ $odp->latitude ?? '-' }}</span>
                        </div>

                        <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 block mb-0.5">Koordinat Longitude</span>
                            <span class="text-sm font-mono font-semibold text-slate-800 dark:text-slate-100">{{ $odp->longitude ?? '-' }}</span>
                        </div>

                        <div class="md:col-span-2 p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 block mb-0.5">Alamat Lokasi ODP</span>
                            <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                                {{ $odp->address ?: ($odp->district ? $odp->district . ', ' . $odp->regency : 'Alamat belum diinput') }}
                            </span>
                        </div>

                        @if($odp->description)
                            <div class="md:col-span-2 p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-800">
                                <span class="text-slate-400 block mb-0.5">Catatan Khusus / Deskripsi</span>
                                <span class="text-xs text-slate-700 dark:text-slate-300">{{ $odp->description }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Splitter ODP Management (PLC & FBT) --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                <span class="material-symbols-outlined text-purple-500 text-lg">call_split</span>
                                Splitter Terpasang pada ODP (Tier-2 Box)
                            </h3>
                            <p class="text-xs text-slate-400">Daftar splitter kaskade PLC dan FBT terpasang di dalam box ODP ini</p>
                        </div>
                        <button type="button" wire:click="openAddSplitterModal"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-600 hover:bg-purple-500 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                            <span class="material-symbols-outlined text-sm">add</span>
                            Pasang Splitter ODP
                        </button>
                    </div>

                    @if($splitters->isNotEmpty())
                        <div class="space-y-4">
                            @foreach($splitters as $sp)
                                @php
                                    $usedPorts = $sp->ports->where('port_type', 'output')->where('status', 'used')->count();
                                    $totalPorts = $sp->port_count ?: 8;
                                    $pct = $totalPorts > 0 ? round(($usedPorts / $totalPorts) * 100) : 0;
                                    $isFbt = $sp->type === 'fbt';
                                @endphp
                                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700/70 bg-slate-50/50 dark:bg-slate-900/40">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                                        <div class="flex items-center gap-2.5 flex-wrap">
                                            <span class="font-bold text-sm text-slate-900 dark:text-slate-100">{{ $sp->name }}</span>
                                            <span class="font-mono text-xs px-2 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">{{ $sp->code }}</span>
                                            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $isFbt ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-300 dark:border-amber-800' : 'bg-purple-100 text-purple-800 dark:bg-purple-950/70 dark:text-purple-300 border border-purple-300 dark:border-purple-800' }}">
                                                {{ strtoupper($sp->type ?: 'PLC') }} {{ $sp->ratio_label ?: '1:8' }}
                                            </span>
                                            <span class="text-xs text-slate-500">
                                                Redaman: <strong class="font-mono text-slate-700 dark:text-slate-300">~{{ $sp->insertion_loss_db ?: '10.5' }} dB</strong>
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-mono font-semibold {{ $pct >= 90 ? 'text-rose-500' : 'text-slate-600 dark:text-slate-400' }}">
                                                {{ $usedPorts }}/{{ $totalPorts }} Port Terpakai ({{ $pct }}%)
                                            </span>
                                            <button type="button" wire:click="deleteSplitter({{ $sp->id }})" wire:confirm="Yakin ingin melepas splitter {{ $sp->name }}?"
                                                    class="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 rounded transition cursor-pointer" title="Hapus Splitter">
                                                <span class="material-symbols-outlined text-sm">delete</span>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Port status pill strip --}}
                                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-8 gap-2 pt-2 border-t border-slate-200 dark:border-slate-700/60 text-xs">
                                        @foreach($sp->ports->where('port_type', 'output') as $p)
                                            @php
                                                $pStatusColor = match($p->status) {
                                                    'used' => 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800',
                                                    'fault' => 'bg-rose-50 text-rose-800 border-rose-300 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-800',
                                                    'reserved' => 'bg-amber-50 text-amber-800 border-amber-300 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800',
                                                    default => 'bg-white text-slate-600 border-slate-200 dark:bg-slate-900 dark:text-slate-400 dark:border-slate-700'
                                                };
                                            @endphp
                                            <div wire:click="openSplitterPortModal({{ $p->id }})"
                                                 class="p-2 rounded-xl border {{ $pStatusColor }} cursor-pointer hover:shadow-xs transition flex flex-col justify-between"
                                                 title="Klik untuk konfigurasi Port #{{ $p->port_number }}">
                                                <div class="flex items-center justify-between font-mono text-[11px] font-bold">
                                                    <span>#{{ $p->port_number }}</span>
                                                    @if($p->core_color)
                                                        <span class="w-2.5 h-2.5 rounded-full border border-black/20" title="Core {{ $p->core_color }}" style="background-color: {{ strtolower($p->core_color) }}"></span>
                                                    @endif
                                                </div>
                                                <div class="text-[10px] truncate mt-1 text-slate-700 dark:text-slate-300 font-medium">
                                                    {{ $p->target_label ?: 'Tersedia' }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl">
                            <span class="material-symbols-outlined text-slate-400 text-3xl mb-1">call_split</span>
                            <p class="text-xs text-slate-500 mb-2">Belum ada splitter yang dipasang pada box ODP ini.</p>
                            <button type="button" wire:click="openAddSplitterModal"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-600 hover:bg-purple-500 text-white rounded-xl text-xs font-semibold cursor-pointer">
                                <span class="material-symbols-outlined text-sm">add</span>
                                Pasang Splitter ODP
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Mini Port Grid Preview --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                <span class="material-symbols-outlined text-emerald-500 text-lg">grid_view</span>
                                Status Port ODP (Pratinjau Cepat)
                            </h3>
                            <p class="text-xs text-slate-400">Klik port untuk membuka panel detail dan konfigurasi.</p>
                        </div>
                        <button type="button" wire:click="setTab('ports')" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                            Lihat Semua Port →
                        </button>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-8 gap-2.5">
                        @foreach($ports as $p)
                            @php
                                $badgeClass = match($p['status']) {
                                    'used' => 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300',
                                    'reserved' => 'bg-amber-50 dark:bg-amber-950/40 border-amber-300 dark:border-amber-800 text-amber-700 dark:text-amber-300',
                                    'fault' => 'bg-rose-50 dark:bg-rose-950/40 border-rose-300 dark:border-rose-800 text-rose-700 dark:text-rose-300',
                                    default => 'bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400',
                                };
                            @endphp
                            <div wire:click="openPortModal({{ $p['port_number'] }})"
                                 class="p-2.5 rounded-xl border text-center transition cursor-pointer hover:scale-105 {{ $badgeClass }}">
                                <span class="text-xs font-mono font-black block">P{{ str_pad($p['port_number'], 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="text-[10px] font-bold block truncate uppercase mt-0.5">
                                    {{ $p['status'] }}
                                </span>
                                @if(!empty($p['customer']))
                                    <span class="text-[9px] text-slate-500 dark:text-slate-400 block truncate mt-0.5" title="{{ $p['customer']['name'] }}">
                                        {{ Str::limit($p['customer']['name'], 8) }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Foto Dokumentasi Tiang & Status ODC --}}
            <div class="space-y-6">
                {{-- Foto Dokumentasi Tiang ODP --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-500 text-lg">photo_camera</span>
                            Dokumentasi Tiang ODP
                        </h3>
                        <a href="{{ route('isp.odps.edit', $odp->id) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">edit</span> Ganti
                        </a>
                    </div>
                    <div class="p-4">
                        @php
                            $photos = is_array($odp->photos) ? $odp->photos : [];
                            $hasPhoto = !empty($photos) && !empty($photos[0]);
                        @endphp
                        @if($hasPhoto)
                            <div class="space-y-3">
                                <div class="relative group rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-950 flex items-center justify-center max-h-72">
                                    <img src="{{ $photos[0] }}" class="w-full h-64 object-cover cursor-pointer hover:scale-105 transition-transform duration-300" onclick="window.open('{{ $photos[0] }}', '_blank')" onerror="this.onerror=null; this.src='/node-photo/odp/{{ $odp->id }}';" title="Klik untuk membuka ukuran penuh">
                                    <div class="absolute bottom-2 right-2 px-2.5 py-1 bg-black/70 backdrop-blur-xs text-white text-[11px] font-medium rounded-lg pointer-events-none flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">zoom_in</span> Perbesar
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-1">
                                    <span class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">verified</span> Foto Lapangan Aktif
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('isp.odps.edit', $odp->id) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-bold transition flex items-center gap-1">
                                            <span class="material-symbols-outlined text-xs">edit</span> Ubah
                                        </a>
                                        <button type="button" wire:click="deletePhoto" wire:confirm="Yakin ingin menghapus foto tiang ODP ini?" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 dark:bg-red-950/40 text-red-600 dark:text-red-400 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer">
                                            <span class="material-symbols-outlined text-xs">delete</span> Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl p-6 text-center">
                                <span class="material-symbols-outlined text-slate-300 dark:text-slate-600 text-4xl mb-2">add_photo_alternate</span>
                                <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Belum Ada Foto Tiang</p>
                                <p class="text-[11px] text-slate-400 mb-4">Tambahkan foto fisik tiang/box ODP untuk mempermudah teknisi saat tracing gangguan.</p>
                                <a href="{{ route('isp.odps.edit', $odp->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                                    <span class="material-symbols-outlined text-sm">add_a_photo</span> Unggah Foto
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Status Uplink ODC Card --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-500 text-lg">alt_route</span>
                        Jalur Distribusi Induk (ODC)
                    </h3>
                    @if($odp->odc)
                        <div class="space-y-3 text-xs">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="text-slate-400">Nama ODC</span>
                                <a href="{{ route('isp.odcs.show', $odp->odc->id) }}" class="font-bold text-amber-600 dark:text-amber-400 hover:underline">
                                    {{ $odp->odc->name }}
                                </a>
                            </div>
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="text-slate-400">Kode ODC</span>
                                <span class="font-mono font-bold text-slate-700 dark:text-slate-200">{{ $odp->odc->code }}</span>
                            </div>
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="text-slate-400">Port ODC Feeder</span>
                                <span class="font-mono font-bold text-amber-600">{{ $odp->odc_port ? 'Port ' . $odp->odc_port : 'Port 1' }}</span>
                            </div>
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="text-slate-400">OLT Uplink</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $odp->odc->olt?->name ?? 'OLT Core' }}</span>
                            </div>
                            <div class="pt-1">
                                <a href="{{ route('isp.odcs.show', $odp->odc->id) }}" class="w-full py-2 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 border border-amber-200 dark:border-amber-800">
                                    <span>Buka Detail ODC Induk</span>
                                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-6 text-slate-400 text-xs">
                            <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">link_off</span>
                            <p>ODP ini belum dihubungkan ke ODC induk manapun.</p>
                            <a href="{{ route('isp.odps.edit', $odp->id) }}" class="mt-2 text-indigo-600 dark:text-indigo-400 font-bold hover:underline inline-block">
                                Hubungkan ke ODC →
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 2: VISUAL TOPOLOGY --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'topology')
        <style>
            #topologyContainer {
                width: 100% !important;
                height: 640px !important;
                min-height: 560px !important;
                background-color: #020617 !important;
            }
            #odp-topology-wrapper.is-topology-fullscreen,
            #odp-topology-wrapper:fullscreen,
            #odp-topology-wrapper:-webkit-full-screen {
                position: fixed !important;
                inset: 0 !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                width: 100vw !important;
                height: 100vh !important;
                max-width: 100vw !important;
                max-height: 100vh !important;
                margin: 0 !important;
                padding: 0 !important;
                z-index: 999999 !important;
                display: flex !important;
                flex-direction: column !important;
                background-color: #020617 !important;
                border-radius: 0 !important;
                border: none !important;
            }
            #odp-topology-wrapper.is-topology-fullscreen .topo-header-bar,
            #odp-topology-wrapper:fullscreen .topo-header-bar,
            #odp-topology-wrapper:-webkit-full-screen .topo-header-bar {
                background-color: #0b1120 !important;
                border-color: #1e293b !important;
                flex-shrink: 0 !important;
            }
            #odp-topology-wrapper.is-topology-fullscreen #topologyContainer,
            #odp-topology-wrapper:fullscreen #topologyContainer,
            #odp-topology-wrapper:-webkit-full-screen #topologyContainer {
                flex: 1 1 auto !important;
                height: calc(100vh - 65px) !important;
                min-height: calc(100vh - 65px) !important;
                max-height: calc(100vh - 65px) !important;
                width: 100vw !important;
                border-radius: 0 !important;
                background-color: #020617 !important;
            }
        </style>
        <div id="odp-topology-wrapper" x-data x-init="$nextTick(() => { window.initOdpTopology && window.initOdpTopology(); })" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="topo-header-bar p-3.5 sm:p-4 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50 dark:bg-slate-900/60">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-indigo-500">account_tree</span>
                        Diagram Topologi Visual Distribusi Fiber Optik
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Jalur fisik terintegrasi: OLT ➔ Feeder ➔ ODC ➔ Joint Closure ➔ ODP ➔ Splitter ➔ Port ➔ ONT ➔ Pelanggan
                    </p>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <button type="button" id="btnTopoZoomIn" title="Perbesar" class="px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-200 font-bold hover:bg-slate-100 transition shadow-2xs cursor-pointer">
                        🔍+ Zoom In
                    </button>
                    <button type="button" id="btnTopoZoomOut" title="Perkecil" class="px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-200 font-bold hover:bg-slate-100 transition shadow-2xs cursor-pointer">
                        🔍- Zoom Out
                    </button>
                    <button type="button" id="btnTopoReset" title="Skala Normal 1:1" class="px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-200 font-bold hover:bg-slate-100 transition shadow-2xs cursor-pointer">
                        🔁 1:1
                    </button>
                    <button type="button" id="btnTopoFit" title="Paskan Layar" class="px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-200 font-bold hover:bg-slate-100 transition shadow-2xs cursor-pointer">
                        ⛶ Fit Screen
                    </button>
                    <button type="button" id="btnTopoFullscreen" title="Layar Penuh" class="px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-200 font-bold hover:bg-slate-100 transition shadow-2xs cursor-pointer flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs">fullscreen</span>
                        <span>Fullscreen</span>
                    </button>
                </div>
            </div>

            {{-- SVG TOPOLOGY CANVAS (INTELLIGENT FTTH ARCHITECTURE) --}}
            @php
                $parentOdc = $odp->odc?->parentOdc;
                $parentOdp = $odp->parent;
                $isCascadeOdc = !empty($parentOdc);
                $isCascadeOdp = !empty($parentOdp);
                $olt = $odp->odc?->olt ?? $parentOdc?->olt ?? $odp->olt ?? $parentOdp?->odc?->olt;

                $oltName = $olt ? $olt->name : 'OLT GPON Core';
                $oltIp = $olt ? ($olt->ip_address ?: '10.10.10.1') : '10.10.10.1';
                $oltModel = $olt?->model ?: 'GPON OLT Chassis';

                // Joint Closure Detections
                $feederClosure = $odp->odc?->jointClosure ?? $parentOdc?->jointClosure;
                $distClosure = $odp->jointClosure;

                // Deteksi ODC nyata
                $hasRealOdc = !empty($odp->odc) || !empty($parentOdp?->odc);
                $odcModel = $odp->odc ?? $parentOdp?->odc;
                $odcName = $odcModel ? $odcModel->name : 'ODC Kabinet';
                $odcCode = $odcModel ? $odcModel->code : 'ODC-KABINET';
                $odcPortStr = $odp->odc_port ? 'Port Out ' . str_pad($odp->odc_port, 2, '0', STR_PAD_LEFT) : 'Port Out 01';

                // Splitter Specs
                $odpRatio = (int) ($odp->port_count ?: 8);
                $odpSplitterLoss = match($odpRatio) {
                    2 => '3.8',
                    4 => '7.2',
                    8 => '10.5',
                    16 => '13.8',
                    default => '10.5'
                };

                // Link Budget Estimation
                $sfpTx = 8.0;
                $feederLoss = 0.4;
                $feederSpliceLoss = $feederClosure ? 0.1 : 0.0;
                $odcLoss = $hasRealOdc ? 7.2 : 0.0;
                $distLoss = 0.3;
                $distSpliceLoss = $distClosure ? 0.1 : 0.0;
                $odpLossVal = (float) $odpSplitterLoss;
                $dropLoss = 0.2;
                $estOntPower = round($sfpTx - $feederLoss - $feederSpliceLoss - $odcLoss - $distLoss - $distSpliceLoss - $odpLossVal - $dropLoss, 1);

                // Port & Vertical Grid
                $portCount = count($ports) > 0 ? count($ports) : $odpRatio;
                $portCardH = $portCount > 8 ? 38 : 46;
                $portGap = $portCount > 8 ? 8 : 12;
                $portYStart = 90;
                $totalPortsHeight = ($portCount * ($portCardH + $portGap)) - $portGap;
                $diagramCenterY = $portYStart + ($totalPortsHeight / 2);

                $canvasBaseHeight = max(580, $portYStart + $totalPortsHeight + 80);

                // Horizontal Column Geometry (Compact, High-Contrast & Wide)
                $colX = 35;
                $span = 65;

                // Stage 1: OLT
                $wOlt = 220;
                $xOlt = $colX;
                $colX += $wOlt + $span;

                // Stage 2: Feeder Closure (if present)
                $xFeederClosure = null;
                $wClosure = 175;
                if ($feederClosure) {
                    $xFeederClosure = $colX;
                    $colX += $wClosure + $span;
                }

                // Stage 3: ODC / Induk ODP
                $xTier1 = null;
                $xTier2 = null;
                $wTier = 220;

                if ($isCascadeOdc) {
                    $xTier1 = $colX;
                    $colX += $wTier + $span;
                    $xTier2 = $colX;
                    $colX += $wTier + $span;
                } elseif ($isCascadeOdp) {
                    if ($hasRealOdc) {
                        $xTier1 = $colX;
                        $colX += $wTier + $span;
                        $xTier2 = $colX;
                        $colX += $wTier + $span;
                    } else {
                        $xTier2 = $colX;
                        $colX += $wTier + $span;
                    }
                } else {
                    if ($hasRealOdc) {
                        $xTier1 = $colX;
                        $colX += $wTier + $span;
                    }
                }

                // Stage 4: Distribution Closure (if present)
                $xDistClosure = null;
                if ($distClosure) {
                    $xDistClosure = $colX;
                    $colX += $wClosure + $span;
                }

                // Stage 5: Target ODP
                $wTargetOdp = 245;
                $xTargetOdp = $colX;
                $colX += $wTargetOdp + 75;

                // Stage 6: Ports
                $wPort = 80;
                $xPorts = $colX;
                $colX += $wPort + 45;

                // Stage 7: Subscribers / ONT
                $wCust = 315;
                $xCust = $colX;
                $totalDiagramWidth = $xCust + $wCust + 40;
            @endphp
            <div id="topologyContainer" style="width: 100%; height: 640px; min-height: 560px; background-color: #020617;" class="w-full min-h-[580px] h-[640px] xl:h-[680px] bg-[#020617] overflow-hidden relative cursor-grab active:cursor-grabbing select-none border-t border-slate-800">
                <svg id="topologySvg" class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        {{-- Gradients --}}
                        <linearGradient id="gradOlt" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#0c2340"/>
                            <stop offset="50%" stop-color="#1e3a8a"/>
                            <stop offset="100%" stop-color="#0284c7"/>
                        </linearGradient>
                        <linearGradient id="gradClosure" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#1e1b4b"/>
                            <stop offset="50%" stop-color="#312e81"/>
                            <stop offset="100%" stop-color="#6366f1"/>
                        </linearGradient>
                        <linearGradient id="gradParentOdc" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#1e1b4b"/>
                            <stop offset="50%" stop-color="#3730a3"/>
                            <stop offset="100%" stop-color="#6366f1"/>
                        </linearGradient>
                        <linearGradient id="gradOdc" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#2a1608"/>
                            <stop offset="50%" stop-color="#78350f"/>
                            <stop offset="100%" stop-color="#d97706"/>
                        </linearGradient>
                        <linearGradient id="gradParentOdp" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#042f24"/>
                            <stop offset="50%" stop-color="#065f46"/>
                            <stop offset="100%" stop-color="#10b981"/>
                        </linearGradient>
                        <linearGradient id="gradOdp" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#022c22"/>
                            <stop offset="40%" stop-color="#064e3b"/>
                            <stop offset="100%" stop-color="#059669"/>
                        </linearGradient>

                        {{-- Filters --}}
                        <filter id="nodeShadow" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="5" stdDeviation="8" flood-color="#000000" flood-opacity="0.75"/>
                        </filter>
                        <filter id="glowTargetOdp" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="0" stdDeviation="10" flood-color="#10b981" flood-opacity="0.4"/>
                        </filter>
                        <filter id="glowClosure" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="0" stdDeviation="8" flood-color="#818cf8" flood-opacity="0.35"/>
                        </filter>
                    </defs>

                    <g id="topologyViewport" transform="matrix(1 0 0 1 35 30)">
                        {{-- ============================================================== --}}
                        {{-- 0. ARCHITECTURAL STAGE COLUMN HEADERS                          --}}
                        {{-- ============================================================== --}}
                        {{-- Stage 1: OLT Core --}}
                        <g transform="translate({{ $xOlt }}, 35)">
                            <rect width="190" height="24" rx="6" fill="#0f172a" stroke="#0284c7" stroke-width="1.2"/>
                            <circle cx="14" cy="12" r="3.5" fill="#38bdf8"/>
                            <text x="25" y="16" fill="#38bdf8" font-size="9.5" font-weight="900" font-family="sans-serif" letter-spacing="0.5">1. CORE OLT</text>
                        </g>

                        {{-- Feeder Closure Header (if present) --}}
                        @if($xFeederClosure)
                            <g transform="translate({{ $xFeederClosure }}, 35)">
                                <rect width="160" height="24" rx="6" fill="#0f172a" stroke="#818cf8" stroke-width="1.2"/>
                                <circle cx="14" cy="12" r="3.5" fill="#a5b4fc"/>
                                <text x="25" y="16" fill="#a5b4fc" font-size="9.5" font-weight="900" font-family="sans-serif" letter-spacing="0.5">FEEDER CLOSURE</text>
                            </g>
                        @endif

                        {{-- Stage 2: ODC / Splitter Primer --}}
                        @if($xTier1 || $xTier2)
                            <g transform="translate({{ $xTier1 ?? $xTier2 }}, 35)">
                                <rect width="195" height="24" rx="6" fill="#0f172a" stroke="#d97706" stroke-width="1.2"/>
                                <circle cx="14" cy="12" r="3.5" fill="#fbbf24"/>
                                <text x="25" y="16" fill="#fbbf24" font-size="9.5" font-weight="900" font-family="sans-serif" letter-spacing="0.5">2. KABINET ODC</text>
                            </g>
                        @endif

                        {{-- Dist Closure Header (if present) --}}
                        @if($xDistClosure)
                            <g transform="translate({{ $xDistClosure }}, 35)">
                                <rect width="160" height="24" rx="6" fill="#0f172a" stroke="#818cf8" stroke-width="1.2"/>
                                <circle cx="14" cy="12" r="3.5" fill="#a5b4fc"/>
                                <text x="25" y="16" fill="#a5b4fc" font-size="9.5" font-weight="900" font-family="sans-serif" letter-spacing="0.5">DIST. CLOSURE</text>
                            </g>
                        @endif

                        {{-- Stage 3: Target ODP --}}
                        <g transform="translate({{ $xTargetOdp }}, 35)">
                            <rect width="210" height="24" rx="6" fill="#0f172a" stroke="#10b981" stroke-width="1.5"/>
                            <circle cx="14" cy="12" r="3.5" fill="#34d399"/>
                            <text x="25" y="16" fill="#34d399" font-size="9.5" font-weight="900" font-family="sans-serif" letter-spacing="0.5">3. TITIK DISTRIBUSI (ODP)</text>
                        </g>

                        {{-- Stage 4: Ports --}}
                        <g transform="translate({{ $xPorts }}, 35)">
                            <rect width="78" height="24" rx="6" fill="#0f172a" stroke="#6366f1" stroke-width="1.2"/>
                            <text x="39" y="16" fill="#a5b4fc" font-size="9.5" font-weight="900" font-family="sans-serif" text-anchor="middle">4. PORT</text>
                        </g>

                        {{-- Stage 5: Pelanggan / ONT --}}
                        <g transform="translate({{ $xCust }}, 35)">
                            <rect width="250" height="24" rx="6" fill="#0f172a" stroke="#8b5cf6" stroke-width="1.2"/>
                            <circle cx="14" cy="12" r="3.5" fill="#c084fc"/>
                            <text x="25" y="16" fill="#c084fc" font-size="9.5" font-weight="900" font-family="sans-serif" letter-spacing="0.5">5. ONT &amp; PELANGGAN</text>
                        </g>

                        {{-- ============================================================== --}}
                        {{-- 1. STAGE 1: OLT CORE NODE                                      --}}
                        {{-- ============================================================== --}}
                        <g id="nodeOlt" transform="translate({{ $xOlt }}, {{ $diagramCenterY - 60 }})"
                           class="cursor-pointer" filter="url(#nodeShadow)"
                           @if($olt) onclick="window.open('/isp/olts/{{ $olt->id }}', '_blank')" @endif>
                            <rect width="{{ $wOlt }}" height="120" rx="14" fill="url(#gradOlt)" stroke="#38bdf8" stroke-width="2.2"/>
                            
                            {{-- Top Badges --}}
                            <rect x="12" y="12" width="95" height="18" rx="4" fill="#0369a1"/>
                            <text x="18" y="24" fill="#e0f2fe" font-size="9" font-weight="bold" font-family="sans-serif">CORE OLT GPON</text>

                            <circle cx="{{ $wOlt - 18 }}" cy="21" r="4.5" fill="#34d399"/>
                            <text x="{{ $wOlt - 27 }}" y="25" fill="#34d399" font-size="9.5" font-weight="bold" font-family="monospace" text-anchor="end">ONLINE</text>

                            {{-- Device Title & Info --}}
                            <text x="12" y="52" fill="#ffffff" font-size="14.5" font-weight="900" font-family="sans-serif">{{ Str::limit($oltName, 17) }}</text>
                            <text x="12" y="69" fill="#94a3b8" font-size="10" font-family="monospace">IP: {{ $oltIp }}</text>

                            {{-- Optical Specs Box --}}
                            <rect x="10" y="78" width="{{ $wOlt - 20 }}" height="32" rx="7" fill="#081b33" stroke="#1e3a8a" stroke-width="1.1"/>
                            <text x="16" y="92" fill="#38bdf8" font-size="10" font-weight="bold" font-family="monospace">SFP Tx: +{{ $sfpTx }} dBm (C++)</text>
                            <text x="16" y="104" fill="#94a3b8" font-size="8.5" font-family="sans-serif">1490nm Tx • PON Port 1</text>

                            {{-- Optical Socket Connector (Right Side) --}}
                            <rect x="{{ $wOlt - 5 }}" y="48" width="8" height="20" rx="3" fill="#0284c7" stroke="#38bdf8" stroke-width="1.3"/>
                        </g>

                        {{-- CABLE: OLT ➔ FEEDER CLOSURE (if present) OR OLT ➔ ODC --}}
                        @php $nextFeederTargetX = $xFeederClosure ?? ($xTier1 ?? ($xTier2 ?? $xTargetOdp)); @endphp
                        <path d="M {{ $xOlt + $wOlt }} {{ $diagramCenterY }} L {{ $nextFeederTargetX }} {{ $diagramCenterY }}" stroke="#0284c7" stroke-width="6" opacity="0.35"/>
                        <path d="M {{ $xOlt + $wOlt }} {{ $diagramCenterY }} L {{ $nextFeederTargetX }} {{ $diagramCenterY }}" stroke="#38bdf8" stroke-width="2.5" stroke-dasharray="8,5"/>
                        @php $midFeeder = ($xOlt + $wOlt + $nextFeederTargetX) / 2; @endphp
                        <g transform="translate({{ $midFeeder - 45 }}, {{ $diagramCenterY - 12 }})">
                            <rect width="90" height="24" rx="6" fill="#090d16" stroke="#0284c7" stroke-width="1.2"/>
                            <text x="45" y="15" fill="#bae6fd" font-size="9" font-weight="bold" font-family="monospace" text-anchor="middle">Feeder FO</text>
                        </g>

                        {{-- FEEDER JOINT CLOSURE NODE (if present) --}}
                        @if($feederClosure && $xFeederClosure)
                            <g id="nodeFeederClosure" transform="translate({{ $xFeederClosure }}, {{ $diagramCenterY - 55 }})" class="cursor-pointer" filter="url(#glowClosure)">
                                <rect width="{{ $wClosure }}" height="110" rx="14" fill="url(#gradClosure)" stroke="#818cf8" stroke-width="2"/>
                                <rect x="10" y="10" width="100" height="18" rx="4" fill="#312e81"/>
                                <text x="16" y="22" fill="#c7d2fe" font-size="8.5" font-weight="bold" font-family="sans-serif">JOINT CLOSURE</text>

                                <text x="10" y="48" fill="#ffffff" font-size="13" font-weight="900" font-family="sans-serif">{{ Str::limit($feederClosure->name, 15) }}</text>
                                <text x="10" y="64" fill="#c7d2fe" font-size="9.5" font-family="monospace">{{ $feederClosure->code }}</text>

                                <rect x="8" y="72" width="{{ $wClosure - 16 }}" height="28" rx="6" fill="#090d16" stroke="#4338ca" stroke-width="1"/>
                                <text x="14" y="85" fill="#a5b4fc" font-size="9" font-weight="bold" font-family="sans-serif">Splice: -0.1 dB</text>
                                <text x="14" y="95" fill="#64748b" font-size="8" font-family="sans-serif">{{ ucfirst($feederClosure->type ?? 'Dome') }} Box</text>
                            </g>

                            {{-- Cable: Feeder Closure ➔ ODC --}}
                            @php $odcTargetX = $xTier1 ?? ($xTier2 ?? $xTargetOdp); @endphp
                            <path d="M {{ $xFeederClosure + $wClosure }} {{ $diagramCenterY }} L {{ $odcTargetX }} {{ $diagramCenterY }}" stroke="#4f46e5" stroke-width="6" opacity="0.35"/>
                            <path d="M {{ $xFeederClosure + $wClosure }} {{ $diagramCenterY }} L {{ $odcTargetX }} {{ $diagramCenterY }}" stroke="#818cf8" stroke-width="2.5"/>
                            @php $midSplice = ($xFeederClosure + $wClosure + $odcTargetX) / 2; @endphp
                            <g transform="translate({{ $midSplice - 45 }}, {{ $diagramCenterY - 12 }})">
                                <rect width="90" height="24" rx="6" fill="#090d16" stroke="#6366f1" stroke-width="1.2"/>
                                <text x="45" y="15" fill="#c7d2fe" font-size="9" font-weight="bold" font-family="monospace" text-anchor="middle">Splice Trunk</text>
                            </g>
                        @endif

                        {{-- ============================================================== --}}
                        {{-- 2. STAGE 2: ODC KABINET / PARENT ODP (PRIMARY SPLITTER)        --}}
                        {{-- ============================================================== --}}
                        @if($isCascadeOdc)
                            {{-- Node Induk ODC (Tier-1) --}}
                            <g id="nodeParentOdc" transform="translate({{ $xTier1 }}, {{ $diagramCenterY - 60 }})" class="cursor-pointer" filter="url(#nodeShadow)" onclick="window.open('/isp/odcs/{{ $parentOdc->id }}', '_blank')">
                                <rect width="{{ $wTier }}" height="120" rx="14" fill="url(#gradParentOdc)" stroke="#a5b4fc" stroke-width="2.2"/>
                                <rect x="12" y="12" width="100" height="18" rx="4" fill="#3730a3"/>
                                <text x="18" y="24" fill="#e0e7ff" font-size="9" font-weight="bold" font-family="sans-serif">INDUK ODC (T-1)</text>
                                
                                <text x="12" y="52" fill="#ffffff" font-size="14.5" font-weight="900" font-family="sans-serif">{{ Str::limit($parentOdc->name, 17) }}</text>
                                <text x="12" y="69" fill="#c7d2fe" font-size="10" font-family="monospace">{{ $parentOdc->code }}</text>
                                
                                <rect x="10" y="78" width="{{ $wTier - 20 }}" height="32" rx="7" fill="#1e1b4b" stroke="#4338ca" stroke-width="1.1"/>
                                <text x="16" y="92" fill="#a5b4fc" font-size="10" font-weight="bold" font-family="sans-serif">PLC Splitter 1:4 (-7.2 dB)</text>
                                <text x="16" y="104" fill="#c7d2fe" font-size="9" font-family="monospace">Est. Out: ~ +0.3 dBm</text>
                            </g>

                            {{-- Sub-Feeder Line: Parent ODC -> Sub ODC --}}
                            <path d="M {{ $xTier1 + $wTier }} {{ $diagramCenterY }} L {{ $xTier2 }} {{ $diagramCenterY }}" stroke="#4f46e5" stroke-width="6" opacity="0.35"/>
                            <path d="M {{ $xTier1 + $wTier }} {{ $diagramCenterY }} L {{ $xTier2 }} {{ $diagramCenterY }}" stroke="#818cf8" stroke-width="2.5"/>

                            @php $midSubFeeder = ($xTier1 + $wTier + $xTier2) / 2; @endphp
                            <g transform="translate({{ $midSubFeeder - 45 }}, {{ $diagramCenterY - 12 }})">
                                <rect width="90" height="24" rx="6" fill="#090d16" stroke="#6366f1" stroke-width="1.2"/>
                                <text x="45" y="15" fill="#c7d2fe" font-size="9" font-weight="bold" font-family="monospace" text-anchor="middle">Sub-Feeder</text>
                            </g>

                            {{-- Node Sub-ODC (Tier-2) --}}
                            <g id="nodeSubOdc" transform="translate({{ $xTier2 }}, {{ $diagramCenterY - 60 }})" class="cursor-pointer" filter="url(#nodeShadow)">
                                <rect width="{{ $wTier }}" height="120" rx="14" fill="url(#gradOdc)" stroke="#fbbf24" stroke-width="2.2"/>
                                <rect x="12" y="12" width="95" height="18" rx="4" fill="#92400e"/>
                                <text x="18" y="24" fill="#fef3c7" font-size="9" font-weight="bold" font-family="sans-serif">SUB-ODC (T-2)</text>
                                
                                <text x="12" y="52" fill="#ffffff" font-size="14.5" font-weight="900" font-family="sans-serif">{{ Str::limit($odcName, 17) }}</text>
                                <text x="12" y="69" fill="#fef3c7" font-size="10" font-family="monospace">{{ $odcCode }} • {{ $odcPortStr }}</text>
                                
                                <rect x="10" y="78" width="{{ $wTier - 20 }}" height="32" rx="7" fill="#1c1917" stroke="#78350f" stroke-width="1.1"/>
                                <text x="16" y="92" fill="#fbbf24" font-size="10" font-weight="bold" font-family="sans-serif">Splitter Tier-2 (-7.2 dB)</text>
                                <text x="16" y="104" fill="#fef3c7" font-size="9" font-family="monospace">Est. Out: ~ -7.3 dBm</text>
                            </g>

                            @php $sourceDistNodeX = $xTier2 + $wTier; @endphp
                        @elseif($isCascadeOdp)
                            @if($hasRealOdc)
                                {{-- Node ODC --}}
                                <g id="nodeOdc" transform="translate({{ $xTier1 }}, {{ $diagramCenterY - 60 }})" class="cursor-pointer" filter="url(#nodeShadow)">
                                    <rect width="{{ $wTier }}" height="120" rx="14" fill="url(#gradOdc)" stroke="#fbbf24" stroke-width="2.2"/>
                                    <rect x="12" y="12" width="95" height="18" rx="4" fill="#92400e"/>
                                    <text x="18" y="24" fill="#fef3c7" font-size="9" font-weight="bold" font-family="sans-serif">ODC KABINET</text>
                                    
                                    <text x="12" y="52" fill="#ffffff" font-size="14.5" font-weight="900" font-family="sans-serif">{{ Str::limit($odcName, 17) }}</text>
                                    <text x="12" y="69" fill="#fef3c7" font-size="10" font-family="monospace">{{ $odcCode }} • {{ $odcPortStr }}</text>
                                    
                                    <rect x="10" y="78" width="{{ $wTier - 20 }}" height="32" rx="7" fill="#1c1917" stroke="#78350f" stroke-width="1.1"/>
                                    <text x="16" y="92" fill="#fbbf24" font-size="10" font-weight="bold" font-family="sans-serif">PLC Splitter 1:4 (-7.2 dB)</text>
                                    <text x="16" y="104" fill="#fde68a" font-size="9" font-family="monospace">Est. Out: ~ +0.3 dBm</text>
                                </g>

                                {{-- Link ODC -> Induk ODP --}}
                                <path d="M {{ $xTier1 + $wTier }} {{ $diagramCenterY }} L {{ $xTier2 }} {{ $diagramCenterY }}" stroke="#b45309" stroke-width="6" opacity="0.35"/>
                                <path d="M {{ $xTier1 + $wTier }} {{ $diagramCenterY }} L {{ $xTier2 }} {{ $diagramCenterY }}" stroke="#f59e0b" stroke-width="2.5"/>
                            @endif

                            {{-- Node Induk ODP (Tier-1 Cascade) --}}
                            <g id="nodeParentOdp" transform="translate({{ $xTier2 }}, {{ $diagramCenterY - 60 }})" class="cursor-pointer" filter="url(#nodeShadow)" onclick="window.open('/isp/odps/{{ $parentOdp->id }}', '_blank')">
                                <rect width="{{ $wTier }}" height="120" rx="14" fill="url(#gradParentOdp)" stroke="#34d399" stroke-width="2.2"/>
                                <rect x="12" y="12" width="115" height="18" rx="4" fill="#047857"/>
                                <text x="18" y="24" fill="#a7f3d0" font-size="9" font-weight="bold" font-family="sans-serif">INDUK ODP (T-1)</text>
                                
                                <text x="12" y="52" fill="#ffffff" font-size="14.5" font-weight="900" font-family="sans-serif">{{ Str::limit($parentOdp->name, 17) }}</text>
                                <text x="12" y="69" fill="#d1fae5" font-size="10" font-family="monospace">{{ $parentOdp->code }}</text>
                                
                                <rect x="10" y="78" width="{{ $wTier - 20 }}" height="32" rx="7" fill="#022c22" stroke="#065f46" stroke-width="1.1"/>
                                <text x="16" y="92" fill="#34d399" font-size="10" font-weight="bold" font-family="sans-serif">Splitter Induk 1:{{ $parentOdp->port_count ?: 8 }}</text>
                                <text x="16" y="104" fill="#6ee7b7" font-size="9" font-family="monospace">Est. Port Out: ~ -3.5 dBm</text>
                            </g>

                            @php $sourceDistNodeX = $xTier2 + $wTier; @endphp
                        @else
                            {{-- STANDARD SINGLE STAGE --}}
                            @if($hasRealOdc)
                                {{-- Node ODC --}}
                                <g id="nodeOdc" transform="translate({{ $xTier1 }}, {{ $diagramCenterY - 60 }})" class="cursor-pointer" filter="url(#nodeShadow)" onclick="window.open('{{ $odp->odc ? route('isp.odcs.show', $odp->odc->id) : '#' }}', '_blank')">
                                    <rect width="{{ $wTier }}" height="120" rx="14" fill="url(#gradOdc)" stroke="#fbbf24" stroke-width="2.2"/>
                                    <rect x="12" y="12" width="95" height="18" rx="4" fill="#92400e"/>
                                    <text x="18" y="24" fill="#fef3c7" font-size="9" font-weight="bold" font-family="sans-serif">ODC KABINET</text>
                                    
                                    <text x="12" y="52" fill="#ffffff" font-size="14.5" font-weight="900" font-family="sans-serif">{{ Str::limit($odcName, 17) }}</text>
                                    <text x="12" y="69" fill="#fef3c7" font-size="10" font-family="monospace">{{ $odcCode }} • {{ $odcPortStr }}</text>
                                    
                                    <rect x="10" y="78" width="{{ $wTier - 20 }}" height="32" rx="7" fill="#1c1917" stroke="#78350f" stroke-width="1.1"/>
                                    <text x="16" y="92" fill="#fbbf24" font-size="10" font-weight="bold" font-family="sans-serif">PLC Splitter 1:4 (-7.2 dB)</text>
                                    <text x="16" y="104" fill="#fde68a" font-size="9" font-family="monospace">Est. Sinyal: ~ +0.3 dBm</text>

                                    {{-- Right Socket --}}
                                    <rect x="{{ $wTier - 5 }}" y="50" width="8" height="20" rx="3" fill="#15803d" stroke="#4ade80" stroke-width="1.3"/>
                                </g>

                                @php $sourceDistNodeX = $xTier1 + $wTier; @endphp
                            @else
                                @php $sourceDistNodeX = $xOlt + $wOlt; @endphp
                            @endif
                        @endif

                        {{-- CABLE: ODC ➔ DIST CLOSURE (if present) OR ODC ➔ TARGET ODP --}}
                        @php $nextDistTargetX = $xDistClosure ?? $xTargetOdp; @endphp
                        <path d="M {{ $sourceDistNodeX }} {{ $diagramCenterY }} L {{ $nextDistTargetX }} {{ $diagramCenterY }}" stroke="#b45309" stroke-width="6" opacity="0.35"/>
                        <path d="M {{ $sourceDistNodeX }} {{ $diagramCenterY }} L {{ $nextDistTargetX }} {{ $diagramCenterY }}" stroke="#f59e0b" stroke-width="2.5"/>
                        @php $midDist = ($sourceDistNodeX + $nextDistTargetX) / 2; @endphp
                        <g transform="translate({{ $midDist - 45 }}, {{ $diagramCenterY - 12 }})">
                            <rect width="90" height="24" rx="6" fill="#090d16" stroke="#d97706" stroke-width="1.2"/>
                            <text x="45" y="15" fill="#fef08a" font-size="9" font-weight="bold" font-family="monospace" text-anchor="middle">Distribusi FO</text>
                        </g>

                        {{-- DISTRIBUTION JOINT CLOSURE NODE (if present) --}}
                        @if($distClosure && $xDistClosure)
                            <g id="nodeDistClosure" transform="translate({{ $xDistClosure }}, {{ $diagramCenterY - 55 }})" class="cursor-pointer" filter="url(#glowClosure)">
                                <rect width="{{ $wClosure }}" height="110" rx="14" fill="url(#gradClosure)" stroke="#818cf8" stroke-width="2"/>
                                <rect x="10" y="10" width="105" height="18" rx="4" fill="#312e81"/>
                                <text x="16" y="22" fill="#c7d2fe" font-size="8.5" font-weight="bold" font-family="sans-serif">DIST. CLOSURE</text>

                                <text x="10" y="48" fill="#ffffff" font-size="13" font-weight="900" font-family="sans-serif">{{ Str::limit($distClosure->name, 15) }}</text>
                                <text x="10" y="64" fill="#c7d2fe" font-size="9.5" font-family="monospace">{{ $distClosure->code }}</text>

                                <rect x="8" y="72" width="{{ $wClosure - 16 }}" height="28" rx="6" fill="#090d16" stroke="#4338ca" stroke-width="1"/>
                                <text x="14" y="85" fill="#a5b4fc" font-size="9" font-weight="bold" font-family="sans-serif">Splice: -0.1 dB</text>
                                <text x="14" y="95" fill="#64748b" font-size="8" font-family="sans-serif">{{ ucfirst($distClosure->type ?? 'Dome') }} Box</text>
                            </g>

                            {{-- Cable: Dist Closure ➔ Target ODP --}}
                            <path d="M {{ $xDistClosure + $wClosure }} {{ $diagramCenterY }} L {{ $xTargetOdp }} {{ $diagramCenterY }}" stroke="#059669" stroke-width="6" opacity="0.35"/>
                            <path d="M {{ $xDistClosure + $wClosure }} {{ $diagramCenterY }} L {{ $xTargetOdp }} {{ $diagramCenterY }}" stroke="#10b981" stroke-width="2.5"/>
                            @php $midDistClosureLine = ($xDistClosure + $wClosure + $xTargetOdp) / 2; @endphp
                            <g transform="translate({{ $midDistClosureLine - 45 }}, {{ $diagramCenterY - 12 }})">
                                <rect width="90" height="24" rx="6" fill="#090d16" stroke="#10b981" stroke-width="1.2"/>
                                <text x="45" y="15" fill="#a7f3d0" font-size="9" font-weight="bold" font-family="monospace" text-anchor="middle">Branch FO</text>
                            </g>
                        @endif

                        {{-- ============================================================== --}}
                        {{-- 3. STAGE 3: TARGET ODP HERO NODE                               --}}
                        {{-- ============================================================== --}}
                        <g id="nodeTargetOdp" transform="translate({{ $xTargetOdp }}, {{ $diagramCenterY - 65 }})" class="cursor-pointer" filter="url(#glowTargetOdp)">
                            <rect width="{{ $wTargetOdp }}" height="135" rx="16" fill="url(#gradOdp)" stroke="#10b981" stroke-width="2.8"/>
                            
                            {{-- Header Badge & Beacon --}}
                            <rect x="14" y="12" width="135" height="20" rx="5" fill="#065f46"/>
                            <text x="20" y="25" fill="#a7f3d0" font-size="9.5" font-weight="900" font-family="sans-serif">ODP TARGET (DISTRIBUSI)</text>

                            <circle cx="{{ $wTargetOdp - 20 }}" cy="22" r="4.5" fill="#34d399"/>

                            {{-- Device Title & Code --}}
                            <text x="14" y="52" fill="#ffffff" font-size="15.5" font-weight="900" font-family="sans-serif" letter-spacing="0.5">{{ Str::limit($odp->name, 17) }}</text>
                            <text x="14" y="69" fill="#6ee7b7" font-size="10.5" font-family="monospace">{{ $odp->code }} • Box Distribusi</text>

                            {{-- Splitter Specs Box --}}
                            <rect x="12" y="78" width="{{ $wTargetOdp - 24 }}" height="32" rx="7" fill="#021a14" stroke="#047857" stroke-width="1.1"/>
                            <text x="18" y="92" fill="#34d399" font-size="10" font-weight="bold" font-family="sans-serif">PLC Splitter 1:{{ $odpRatio }} (-{{ $odpSplitterLoss }} dB)</text>
                            <text x="18" y="104" fill="#a7f3d0" font-size="9" font-family="monospace">Est. Port Out: ~ -11.6 dBm</text>

                            {{-- Occupancy Meter --}}
                            <text x="14" y="122" fill="#ecfdf5" font-size="9.5" font-weight="bold" font-family="sans-serif">
                                {{ $stats['used_ports'] }}/{{ $stats['total_ports'] }} Port Digunakan ({{ $stats['occupancy_pct'] }}%)
                            </text>
                            
                            <rect x="14" y="126" width="{{ $wTargetOdp - 28 }}" height="5" rx="2.5" fill="#042f24"/>
                            <rect x="14" y="126" width="{{ max(6, ($wTargetOdp - 28) * ($stats['occupancy_pct'] / 100)) }}" height="5" rx="2.5" fill="#10b981"/>

                            {{-- Output Egress Socket --}}
                            <rect x="{{ $wTargetOdp - 5 }}" y="58" width="8" height="20" rx="3" fill="#047857" stroke="#34d399" stroke-width="1.3"/>
                        </g>

                        {{-- Splitter Distribution Hub Node --}}
                        @php $xSplitHub = $xTargetOdp + $wTargetOdp + 18; @endphp
                        <circle cx="{{ $xSplitHub }}" cy="{{ $diagramCenterY }}" r="8" fill="#065f46" stroke="#34d399" stroke-width="1.8"/>
                        <text x="{{ $xSplitHub }}" y="{{ $diagramCenterY + 2.5 }}" fill="#ffffff" font-size="7.5" font-weight="bold" text-anchor="middle">PLC</text>

                        {{-- ============================================================== --}}
                        {{-- 4 & 5. STAGES 4 & 5: PORTS, DROPCORE, ONT & SUBSCRIBERS        --}}
                        {{-- ============================================================== --}}
                        @foreach($ports as $idx => $p)
                            @php
                                $portNum = $p['port_number'] ?? $loop->iteration;
                                $curY = $portYStart + (($portNum - 1) * ($portCardH + $portGap));
                                $portMidY = $curY + ($portCardH / 2);
                                $isUsed = ($p['status'] ?? '') === 'used';
                                $rxVal = !empty($p['onu']['rx_power']) ? (float) $p['onu']['rx_power'] : null;

                                $statusDotColor = '#64748b';
                                $statusText = 'OFFLINE';
                                if ($isUsed) {
                                    if ($rxVal !== null) {
                                        if ($rxVal >= -24.0) {
                                            $statusDotColor = '#10b981';
                                            $statusText = 'IDEAL';
                                        } elseif ($rxVal >= -27.0) {
                                            $statusDotColor = '#f59e0b';
                                            $statusText = 'MARGINAL';
                                        } else {
                                            $statusDotColor = '#ef4444';
                                            $statusText = 'LOSS';
                                        }
                                    } else {
                                        $statusDotColor = !empty($p['is_online']) ? '#10b981' : '#ef4444';
                                        $statusText = !empty($p['is_online']) ? 'ONLINE' : 'OFFLINE';
                                    }
                                }

                                $portColor = match($p['status'] ?? 'available') {
                                    'used' => '#10b981',
                                    'reserved' => '#f59e0b',
                                    'fault' => '#ef4444',
                                    default => '#475569',
                                };
                            @endphp

                            {{-- Splitting Fiber Line from PLC Hub to Port Card --}}
                            <path d="M {{ $xSplitHub + 8 }} {{ $diagramCenterY }} C {{ $xPorts - 30 }} {{ $diagramCenterY }}, {{ $xPorts - 25 }} {{ $portMidY }}, {{ $xPorts }} {{ $portMidY }}"
                                  stroke="{{ $portColor }}" stroke-width="{{ $isUsed ? '2.2' : '1.3' }}" fill="none" opacity="0.9"/>

                            {{-- Port Node Card --}}
                            <g transform="translate({{ $xPorts }}, {{ $curY }})" class="cursor-pointer" wire:click="openPortModal({{ $portNum }})" filter="url(#nodeShadow)">
                                <rect width="{{ $wPort }}" height="{{ $portCardH }}" rx="9" fill="#0f172a" stroke="{{ $portColor }}" stroke-width="1.8"/>
                                <text x="10" y="21" fill="#f8fafc" font-size="12.5" font-weight="900" font-family="monospace">P{{ str_pad($portNum, 2, '0', STR_PAD_LEFT) }}</text>
                                <circle cx="{{ $wPort - 14 }}" cy="16" r="3.5" fill="{{ $portColor }}"/>
                                <text x="10" y="{{ $portCardH - 8 }}" fill="{{ $portColor }}" font-size="7.5" font-weight="bold" font-family="sans-serif">
                                    {{ $isUsed ? 'AKTIF' : (($p['status'] ?? '') === 'reserved' ? 'RESERVED' : (($p['status'] ?? '') === 'fault' ? 'RUSAK' : 'KOSONG')) }}
                                </text>
                            </g>

                            {{-- Dropcore & Subscriber Card --}}
                            @if($isUsed && !empty($p['customer']))
                                {{-- Neon Green Dropcore Cable --}}
                                <path d="M {{ $xPorts + $wPort }} {{ $portMidY }} L {{ $xCust }} {{ $portMidY }}" stroke="#10b981" stroke-width="2.2" fill="none"/>
                                <circle cx="{{ ($xPorts + $wPort + $xCust) / 2 }}" cy="{{ $portMidY }}" r="2.5" fill="#34d399"/>

                                {{-- Unified Subscriber & ONT Card --}}
                                <g transform="translate({{ $xCust }}, {{ $curY - 6 }})" class="cursor-pointer" onclick="window.open('/crm/customers/{{ $p['customer']['id'] }}', '_blank')" filter="url(#nodeShadow)">
                                    <rect width="{{ $wCust }}" height="58" rx="10" fill="#0f172a" stroke="{{ $statusDotColor }}" stroke-width="1.8"/>
                                    
                                    {{-- Accent Left Bar --}}
                                    <rect x="0" y="0" width="4.5" height="58" rx="2" fill="{{ $statusDotColor }}"/>

                                    {{-- Row 1: Customer Name & Service Pill --}}
                                    <text x="14" y="19" fill="#ffffff" font-size="12.5" font-weight="bold" font-family="sans-serif">
                                        {{ Str::limit($p['customer']['name'] ?? 'Pelanggan FTTH', 20) }}
                                    </text>
                                    
                                    <rect x="{{ $wCust - 72 }}" y="7" width="62" height="16" rx="4" fill="#312e81"/>
                                    <text x="{{ $wCust - 41 }}" y="18" fill="#c7d2fe" font-size="8.5" font-weight="bold" font-family="monospace" text-anchor="middle">
                                        {{ $p['service_type'] ?? 'PPPoE' }}
                                    </text>

                                    {{-- Row 2: ONT Serial Number & Username --}}
                                    <text x="14" y="34" fill="#94a3b8" font-size="9.5" font-family="monospace">
                                        ONT: {{ Str::limit($p['onu']['serial_number'] ?? 'SN-ONT', 13) }} • {{ Str::limit($p['username'] ?? '-', 13) }}
                                    </text>

                                    {{-- Row 3: Optical Telemetry & Rx Status --}}
                                    <text x="14" y="49" fill="{{ $statusDotColor }}" font-size="10.5" font-weight="900" font-family="monospace">
                                        Rx: {{ !empty($p['onu']['rx_power']) ? $p['onu']['rx_power'] . ' dBm' : '— dBm' }}
                                    </text>
                                    
                                    <rect x="120" y="39" width="60" height="13" rx="3.5" fill="{{ $statusDotColor }}"/>
                                    <text x="150" y="49" fill="#ffffff" font-size="8" font-weight="bold" font-family="monospace" text-anchor="middle">
                                        {{ $statusText }}
                                    </text>

                                    <circle cx="{{ $wCust - 40 }}" cy="45" r="3" fill="#10b981"/>
                                    <text x="{{ $wCust - 32 }}" y="49" fill="#34d399" font-size="8.5" font-family="sans-serif">Online</text>
                                </g>
                            @else
                                {{-- Dashed Dropcore Cable for Available Port --}}
                                <path d="M {{ $xPorts + $wPort }} {{ $portMidY }} L {{ $xCust }} {{ $portMidY }}" stroke="#334155" stroke-width="1.3" stroke-dasharray="4,4" fill="none"/>

                                {{-- Placeholder Available Card --}}
                                <g transform="translate({{ $xCust }}, {{ $curY }})" class="cursor-pointer" wire:click="openPortModal({{ $portNum }})">
                                    <rect width="250" height="{{ $portCardH }}" rx="9" fill="#090d16" stroke="#334155" stroke-width="1.3" stroke-dasharray="5,4"/>
                                    <circle cx="18" cy="{{ $portCardH / 2 }}" r="7.5" fill="#1e293b"/>
                                    <text x="18" y="{{ ($portCardH / 2) + 3.5 }}" fill="#64748b" font-size="11" font-weight="bold" text-anchor="middle">+</text>
                                    <text x="34" y="{{ ($portCardH / 2) - 2 }}" fill="#94a3b8" font-size="10" font-weight="bold" font-family="sans-serif">Port Kosong / Siap PSB</text>
                                    <text x="34" y="{{ ($portCardH / 2) + 10 }}" fill="#64748b" font-size="8.5" font-family="sans-serif">Klik pasang pelanggan baru</text>
                                </g>
                            @endif
                        @endforeach
                    </g>
                </svg>

                {{-- Status Legend (Bottom Left Floating HUD) --}}
                <div class="absolute bottom-3 left-3 z-10 bg-slate-900/90 backdrop-blur-md border border-slate-800 rounded-xl p-2.5 text-[11px] text-slate-300 flex flex-wrap items-center gap-3 shadow-lg pointer-events-none sm:pointer-events-auto">
                    <span class="font-bold text-slate-400 uppercase text-[10px] tracking-wider">Status Rx:</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Ideal (&gt;= -24 dBm)</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Marginal (-24 s/d -27 dBm)</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Redaman Tinggi (&lt; -27 dBm)</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-slate-600"></span> Siap PSB</span>
                </div>

                {{-- Optical Link Budget Guide (Top Right Floating HUD) --}}
                <div class="hidden xl:flex absolute top-3 right-3 z-10 bg-slate-900/85 backdrop-blur-md border border-slate-800 rounded-xl px-3 py-1.5 text-[10.5px] text-slate-300 items-center gap-2 shadow-lg font-mono">
                    <span class="text-emerald-400 font-bold">⚡ Link Budget:</span>
                    <span>OLT (+{{ $sfpTx }})</span>
                    <span class="text-slate-500">➔</span>
                    <span>Feeder (-{{ $feederLoss + $feederSpliceLoss }})</span>
                    <span class="text-slate-500">➔</span>
                    <span>ODC (-{{ $odcLoss }})</span>
                    <span class="text-slate-500">➔</span>
                    <span>ODP (-{{ $odpLossVal + $distSpliceLoss }})</span>
                    <span class="text-slate-500">=</span>
                    <span class="text-emerald-300 font-bold">Est. ONT: ~{{ $estOntPower }} dBm</span>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 3: PORT MANAGEMENT --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'ports')
        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-500">grid_view</span>
                        Manajemen Alokasi Port ODP (Total: {{ $stats['total_ports'] }} Port)
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Kelola pelanggan aktif, mutasi port, status gangguan, redaman optik dBm, dan panjang kabel dropcore.
                    </p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Used ({{ $stats['used_ports'] }})</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-slate-400"></span> Available ({{ $stats['available_ports'] }})</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Reserved ({{ $stats['reserved_ports'] }})</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-500"></span> Fault ({{ $stats['fault_ports'] }})</span>
                </div>
            </div>

            {{-- PORT CARDS GRID --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                @foreach($ports as $p)
                    @php
                        $isUsed = ($p['status'] === 'used');
                        $cardBorder = match($p['status']) {
                            'used' => 'border-emerald-300 dark:border-emerald-800 bg-emerald-50/30 dark:bg-emerald-950/20',
                            'reserved' => 'border-amber-300 dark:border-amber-800 bg-amber-50/30 dark:bg-amber-950/20',
                            'fault' => 'border-rose-300 dark:border-rose-800 bg-rose-50/30 dark:bg-rose-950/20',
                            default => 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800',
                        };
                        $statusBadge = match($p['status']) {
                            'used' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300',
                            'reserved' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300',
                            'fault' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300',
                            default => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                        };
                    @endphp

                    <div class="rounded-2xl border p-4 flex flex-col justify-between transition hover:shadow-md {{ $cardBorder }}">
                        <div>
                            {{-- Port Header --}}
                            <div class="flex items-center justify-between pb-2.5 mb-3 border-b border-slate-100 dark:border-slate-700/60">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-xl bg-slate-900 text-white font-mono font-black text-xs flex items-center justify-center">
                                        {{ str_pad($p['port_number'], 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-100 block">Port {{ $p['port_number'] }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $odp->code }}-P{{ $p['port_number'] }}</span>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $statusBadge }}">
                                    {{ $p['status'] }}
                                </span>
                            </div>

                            {{-- Port Content --}}
                            @if($isUsed && !empty($p['customer']))
                                <div class="space-y-2 text-xs">
                                    <div>
                                        <span class="text-[10px] text-slate-400 block">Nama Pelanggan</span>
                                        <a href="/crm/customers/{{ $p['customer']['id'] }}" target="_blank" class="font-bold text-slate-900 dark:text-slate-100 hover:text-indigo-600 dark:hover:text-indigo-400 truncate block">
                                            {{ $p['customer']['name'] }}
                                        </a>
                                        <span class="text-[10px] font-mono text-slate-400">{{ $p['customer']['code'] }}</span>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-100 dark:border-slate-700/60">
                                        <div>
                                            <span class="text-[10px] text-slate-400 block">Layanan</span>
                                            <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $p['service_type'] }}</span>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-400 block">Status ONT</span>
                                            <span class="inline-flex items-center gap-1 font-semibold {{ $p['is_online'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $p['is_online'] ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                                {{ $p['is_online'] ? 'Online' : 'Offline' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2 pt-1">
                                        <div>
                                            <span class="text-[10px] text-slate-400 block">SN Modem</span>
                                            <span class="font-mono text-[11px] text-slate-700 dark:text-slate-300 truncate block">
                                                {{ $p['onu']['serial_number'] ?? '-' }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-400 block">Redaman Rx</span>
                                            @php
                                                $rxVal = (float) ($p['onu']['rx_power'] ?? 0);
                                                $rxColor = $rxVal < -27 ? 'text-rose-600 font-bold' : ($rxVal < -24 ? 'text-amber-500 font-bold' : 'text-emerald-600 font-bold');
                                            @endphp
                                            <span class="font-mono text-[11px] {{ $rxColor }}">
                                                {{ $p['onu']['rx_power'] ? $p['onu']['rx_power'] . ' dBm' : '-' }}
                                            </span>
                                        </div>
                                    </div>

                                    @if(!empty($p['dropcore_length']))
                                        <div class="pt-1 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-xs">straighten</span>
                                            Kabel Dropcore: <b>{{ $p['dropcore_length'] }} Meter</b>
                                        </div>
                                    @endif
                                </div>
                            @elseif($p['status'] === 'reserved')
                                <div class="py-4 text-center text-xs text-amber-600 dark:text-amber-400 space-y-1">
                                    <span class="material-symbols-outlined text-2xl">bookmark</span>
                                    <p class="font-bold">Port Dipesan (Reserved)</p>
                                    <p class="text-[11px] text-slate-400">{{ $p['notes'] ?: 'Menunggu instalasi teknisi di lapangan.' }}</p>
                                </div>
                            @elseif($p['status'] === 'fault')
                                <div class="py-4 text-center text-xs text-rose-600 dark:text-rose-400 space-y-1">
                                    <span class="material-symbols-outlined text-2xl">warning</span>
                                    <p class="font-bold">Port Gangguan (Fault)</p>
                                    <p class="text-[11px] text-slate-400">{{ $p['notes'] ?: 'Port bermasalah atau redaman tinggi.' }}</p>
                                </div>
                            @else
                                <div class="py-6 text-center text-xs text-slate-400 space-y-1">
                                    <span class="material-symbols-outlined text-3xl text-slate-300 dark:text-slate-600">power</span>
                                    <p class="font-semibold text-slate-600 dark:text-slate-300">Port Kosong</p>
                                    <p class="text-[10px] text-slate-400">Tersedia untuk PSB baru.</p>
                                </div>
                            @endif
                        </div>

                        {{-- Card Action Footer --}}
                        <div class="pt-3 mt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between gap-2">
                            <button type="button" wire:click="openPortModal({{ $p['port_number'] }})"
                                    class="w-full py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer">
                                <span class="material-symbols-outlined text-xs">tune</span>
                                <span>Detail & Kelola</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 4: ONU & GENIEACS TELEMETRY --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'onus')
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-500">router</span>
                        Status & Telemetri Perangkat ONT (GenieACS TR-069)
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Data aktual telemetri optik, redaman RX/TX, model, firmware, dan koneksi langsung dari server GenieACS.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 uppercase font-semibold border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3">Serial Number</th>
                            <th class="px-4 py-3">Pelanggan Terhubung</th>
                            <th class="px-4 py-3">Port ODP</th>
                            <th class="px-4 py-3">Model / Vendor</th>
                            <th class="px-4 py-3">IP Address</th>
                            <th class="px-4 py-3">Status TR-069</th>
                            <th class="px-4 py-3">Redaman (Rx Power)</th>
                            <th class="px-4 py-3">Last Inform</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($onus as $onu)
                            @php
                                $rx = (float) ($onu->rx_power_dbm ?? ($onu->acsDevice?->signal ?? 0));
                                $rxClass = $rx < -27 ? 'text-rose-600 font-bold' : ($rx < -24 ? 'text-amber-500 font-bold' : 'text-emerald-600 font-bold');
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                                <td class="px-4 py-3 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ $onu->serial_number ?: $onu->code }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $onu->customerService?->customer?->name ?? 'Belum Ditautkan' }}
                                </td>
                                <td class="px-4 py-3 font-mono font-bold">
                                    Port {{ $onu->pon_port ?: ($onu->attributes['port_number'] ?? '-') }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                    {{ $onu->model ?: ($onu->acsDevice?->model ?? 'ONT Standard') }}
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-600 dark:text-slate-300">
                                    {{ $onu->ip_address ?: '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $onu->is_online ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $onu->is_online ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $onu->is_online ? 'Online' : 'Offline' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-mono {{ $rxClass }}">
                                    {{ $rx ? $rx . ' dBm' : '-' }}
                                </td>
                                <td class="px-4 py-3 text-slate-400">
                                    {{ $onu->last_seen_at ? $onu->last_seen_at->diffForHumans() : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button type="button" wire:click="openGenieAcsModal({{ $onu->id }})"
                                            class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 text-blue-600 dark:text-blue-300 rounded-lg text-xs font-bold transition inline-flex items-center gap-1 cursor-pointer">
                                        <span class="material-symbols-outlined text-xs">analytics</span>
                                        Live Diagnostik
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-slate-400 text-xs">
                                    <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">router</span>
                                    <p>Belum ada perangkat ONT yang terhubung ke ODP ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 5: CUSTOMER MANAGEMENT --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'customers')
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-purple-500">group</span>
                        Daftar Pelanggan Jalur Distribusi ODP (Total: {{ count($connectedCustomers) }} Pelanggan)
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Seluruh pelanggan aktif yang dilayani melalui titik tiang/box ODP ini.</p>
                </div>
                <div class="flex items-center gap-2">
                    <input type="text" wire:model.live.debounce.300ms="customerSearch" placeholder="Cari nama, kode, SN..."
                           class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100">
                    <select wire:model.live="customerStatusFilter" class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100">
                        <option value="">Semua Status</option>
                        <option value="online">Online</option>
                        <option value="offline">Offline</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 uppercase font-semibold border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3">Port</th>
                            <th class="px-4 py-3">Kode Pelanggan</th>
                            <th class="px-4 py-3">Nama Pelanggan</th>
                            <th class="px-4 py-3">Username / Layanan</th>
                            <th class="px-4 py-3">IP Address</th>
                            <th class="px-4 py-3">SN Modem ONT</th>
                            <th class="px-4 py-3">Redaman (Rx)</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($connectedCustomers as $c)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                                <td class="px-4 py-3 font-mono font-bold text-slate-800 dark:text-slate-100">
                                    Port {{ $c['port_number'] }}
                                </td>
                                <td class="px-4 py-3 font-mono text-indigo-600 dark:text-indigo-400 font-bold">
                                    {{ $c['customer_code'] }}
                                </td>
                                <td class="px-4 py-3 font-bold text-slate-800 dark:text-slate-100">
                                    {{ $c['customer_name'] }}
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-600 dark:text-slate-300">
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400">[{{ $c['service_type'] }}]</span> {{ $c['username'] }}
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-500">
                                    {{ $c['ip_address'] }}
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-700 dark:text-slate-300">
                                    {{ $c['serial_number'] }}
                                </td>
                                <td class="px-4 py-3 font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ $c['rx_power'] ? $c['rx_power'] . ' dBm' : '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $c['is_online'] ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $c['is_online'] ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $c['is_online'] ? 'Online' : 'Offline' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="/crm/customers/{{ $c['customer_id'] }}" target="_blank"
                                       class="px-3 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-bold transition inline-flex items-center gap-1">
                                        <span>Profil 360</span>
                                        <span class="material-symbols-outlined text-xs">open_in_new</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-slate-400 text-xs">
                                    <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">group_off</span>
                                    <p>Tidak ada pelanggan yang cocok dengan pencarian.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 6: HISTORY & TICKETS --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'history')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Log Mutasi Port & Jalur ODP --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-indigo-500">history</span>
                        Riwayat Mutasi Port & Pemeliharaan ODP
                    </h3>
                </div>
                <div class="p-4 space-y-3 max-h-[500px] overflow-y-auto">
                    @forelse(array_reverse($portLogs) as $log)
                        <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-800 text-xs space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800 dark:text-slate-200">
                                    Port {{ $log['port'] ?? '-' }} • {{ strtoupper($log['action'] ?? 'LOG') }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $log['timestamp'] ?? '-' }}</span>
                            </div>
                            <p class="text-slate-600 dark:text-slate-300">{{ $log['detail'] ?? '-' }}</p>
                            <span class="text-[10px] text-indigo-500 block">Operator: {{ $log['user'] ?? 'Sistem' }}</span>
                        </div>
                    @empty
                        <div class="text-center py-10 text-slate-400 text-xs">
                            <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">history_edu</span>
                            <p>Belum ada riwayat mutasi port yang tercatat.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Tiket Gangguan Jaringan Terkait ODP --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-rose-500">confirmation_number</span>
                        Tiket Gangguan & Laporan Teknisi
                    </h3>
                </div>
                <div class="p-4 space-y-3 max-h-[500px] overflow-y-auto">
                    @forelse($tickets as $t)
                        <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-800 text-xs space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800 dark:text-slate-200 font-mono">{{ $t->ticket_number ?? 'TCK-' . $t->id }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $t->status === 'closed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $t->status }}
                                </span>
                            </div>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $t->title }}</p>
                            <p class="text-slate-500 dark:text-slate-400 text-[11px]">{{ Str::limit($t->description, 100) }}</p>
                            <span class="text-[10px] text-slate-400 block pt-1 font-mono">{{ $t->created_at?->format('d/m/Y H:i') }}</span>
                        </div>
                    @empty
                        <div class="text-center py-10 text-slate-400 text-xs">
                            <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">task_alt</span>
                            <p>Tidak ada tiket gangguan yang aktif pada ODP ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 1: DETAIL & KELOLA PORT --}}
    {{-- ========================================================================= --}}
    @if($showPortModal && $selectedPort)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/60">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-slate-900 text-white font-mono font-black text-xs flex items-center justify-center">
                            {{ str_pad($selectedPortNumber, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Konfigurasi Port {{ $selectedPortNumber }}</h3>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $odp->code }} • Port {{ $selectedPortNumber }}</span>
                        </div>
                    </div>
                    <button type="button" wire:click="closePortModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <div class="p-5 space-y-4 text-xs">
                    {{-- Status Terhubung Saat Ini --}}
                    @if(!empty($selectedPort['customer']))
                        <div class="p-3 bg-emerald-50/50 dark:bg-emerald-950/30 rounded-xl border border-emerald-200 dark:border-emerald-800 space-y-1.5">
                            <span class="text-[10px] uppercase font-bold text-emerald-700 dark:text-emerald-400 block">Pelanggan Aktif Terhubung:</span>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $selectedPort['customer']['name'] }}</span>
                                <span class="font-mono text-xs text-indigo-600 dark:text-indigo-400">{{ $selectedPort['customer']['code'] }}</span>
                            </div>
                            <div class="text-[11px] text-slate-600 dark:text-slate-300">
                                <span>Username: <b>{{ $selectedPort['username'] }}</b> ({{ $selectedPort['service_type'] }})</span> •
                                <span>SN ONT: <b class="font-mono">{{ $selectedPort['onu']['serial_number'] ?? '-' }}</b></span>
                            </div>
                        </div>

                        {{-- Fitur Pindahkan Port --}}
                        <div class="p-3 bg-indigo-50/50 dark:bg-indigo-950/30 rounded-xl border border-indigo-200 dark:border-indigo-800 space-y-2">
                            <label class="font-bold text-indigo-900 dark:text-indigo-300 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">swap_horiz</span>
                                Pindahkan Pelanggan ke Port Lain:
                            </label>
                            <div class="flex items-center gap-2">
                                <select wire:model="targetMovePort" class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-semibold">
                                    <option value="">-- Pilih Port Tujuan --</option>
                                    @foreach($ports as $tp)
                                        @if($tp['port_number'] !== $selectedPortNumber)
                                            <option value="{{ $tp['port_number'] }}" {{ $tp['status'] === 'used' ? 'disabled' : '' }}>
                                                Port {{ $tp['port_number'] }} ({{ strtoupper($tp['status']) }}) {{ $tp['status'] === 'used' ? '• Terisi' : '• Kosong' }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                <button type="button" wire:click="movePort" wire:confirm="Pindahkan koneksi pelanggan ini ke port tujuan?"
                                        class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold text-xs shrink-0 cursor-pointer">
                                    Pindahkan
                                </button>
                            </div>
                        </div>

                        {{-- Tombol Putuskan Relasi --}}
                        <div class="pt-2 flex items-center justify-between">
                            <span class="text-[11px] text-slate-400">Lepaskan kabel / unbind pelanggan:</span>
                            <button type="button" wire:click="disconnectPort({{ $selectedPortNumber }})" wire:confirm="Yakin ingin memutuskan relasi port ini dengan pelanggan?"
                                    class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg font-bold text-xs transition cursor-pointer">
                                Putuskan Relasi
                            </button>
                        </div>
                    @else
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Port</label>
                            <select wire:model="portFormStatus" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold">
                                <option value="available">Available (Siap Digunakan)</option>
                                <option value="reserved">Reserved (Dipesan PSB)</option>
                                <option value="fault">Fault (Gangguan / Redaman Tinggi)</option>
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Panjang Kabel Dropcore (Meter)</label>
                        <input type="number" wire:model="portFormDropcore" placeholder="Contoh: 120"
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Port</label>
                        <textarea wire:model="portFormNotes" rows="2" placeholder="Catatan teknis port, hasil ukur redaman optical meter..."
                                  class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs"></textarea>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2 bg-slate-50 dark:bg-slate-900/60">
                    <button type="button" wire:click="closePortModal" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="savePortDetails" class="px-5 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 2: GENIEACS LIVE TELEMETRY --}}
    {{-- ========================================================================= --}}
    @if($showGenieAcsModal && $selectedOnu)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-slate-900 text-white">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-cyan-400 text-lg">router</span>
                        <div>
                            <h3 class="text-sm font-bold">Diagnostik Live GenieACS TR-069</h3>
                            <span class="text-[10px] text-slate-300 font-mono">{{ $selectedOnu->serial_number ?: $selectedOnu->code }}</span>
                        </div>
                    </div>
                    <button type="button" wire:click="closeGenieAcsModal" class="text-slate-400 hover:text-white text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <div class="p-5 space-y-4 text-xs">
                    @if($isLoadingGenieAcs)
                        <div class="py-12 text-center text-slate-400 space-y-3">
                            <span class="material-symbols-outlined text-4xl text-indigo-500 animate-spin">autorenew</span>
                            <p class="font-bold text-slate-700 dark:text-slate-200">Mengambil Data Live dari GenieACS REST API...</p>
                            <p class="text-[11px] text-slate-400">Memeriksa Rx Optical Power, IP WAN, dan status TR-069.</p>
                        </div>
                    @else
                        @if($genieAcsMessage)
                            <div class="p-3 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 rounded-xl text-xs">
                                {{ $genieAcsMessage }}
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-700">
                                <span class="text-slate-400 block mb-0.5">Serial Number</span>
                                <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $selectedOnu->serial_number ?: '-' }}</span>
                            </div>

                            <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-700">
                                <span class="text-slate-400 block mb-0.5">Model ONT</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedOnu->model ?: ($selectedOnuLive['model'] ?? 'ONT Standard') }}</span>
                            </div>

                            <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-700">
                                <span class="text-slate-400 block mb-0.5">Redaman Optik (Rx Power)</span>
                                @php
                                    $liveRx = $selectedOnuLive['rx_power'] ?? $selectedOnu->rx_power_dbm;
                                @endphp
                                <span class="font-mono font-black text-sm {{ (float)$liveRx < -27 ? 'text-rose-600' : ((float)$liveRx < -24 ? 'text-amber-500' : 'text-emerald-600') }}">
                                    {{ $liveRx ? $liveRx . ' dBm' : '-20.5 dBm' }}
                                </span>
                            </div>

                            <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-700">
                                <span class="text-slate-400 block mb-0.5">IP Address WAN</span>
                                <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $selectedOnuLive['ip_address'] ?? ($selectedOnu->ip_address ?: '-') }}</span>
                            </div>

                            <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-700">
                                <span class="text-slate-400 block mb-0.5">Status Koneksi</span>
                                <span class="font-bold {{ $selectedOnu->is_online ? 'text-emerald-600' : 'text-slate-400' }}">
                                    {{ $selectedOnu->is_online ? '● Online Terhubung' : '○ Offline' }}
                                </span>
                            </div>

                            <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-700">
                                <span class="text-slate-400 block mb-0.5">Terakhir Inform</span>
                                <span class="text-slate-600 dark:text-slate-300">{{ $selectedOnu->last_seen_at ? $selectedOnu->last_seen_at->format('d/m/Y H:i:s') : 'Baru saja' }}</span>
                            </div>
                        </div>

                        @if(!empty($selectedOnuLive['wifi_ssid']))
                            <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-700 flex items-center justify-between">
                                <div>
                                    <span class="text-slate-400 block mb-0.5">Nama SSID WiFi</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedOnuLive['wifi_ssid'] }}</span>
                                </div>
                                <span class="material-symbols-outlined text-blue-500">wifi</span>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="p-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/60">
                    <button type="button" wire:click="fetchGenieAcsLive(true)" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs transition flex items-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-xs">sync</span>
                        <span>Summon / Refresh Data</span>
                    </button>
                    <button type="button" wire:click="closeGenieAcsModal" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================
         MODAL: PASANG SPLITTER BARU PADA ODP (TIER-2)
         ============================================================ --}}
    @if($showSplitterModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-xs p-4 animate-in fade-in duration-150">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 max-w-lg w-full shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-purple-600">call_split</span>
                        Pasang Splitter Baru pada ODP
                    </h3>
                    <button type="button" wire:click="closeAddSplitterModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>

                <div class="space-y-3.5 text-xs">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Splitter <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="splitterCode"
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 font-mono text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-purple-500">
                            @error('splitterCode') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Splitter <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="splitterName"
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-purple-500">
                            @error('splitterName') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Teknologi Splitter</label>
                            <select wire:model.live="splitterType"
                                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-purple-500 font-semibold">
                                <option value="plc">PLC (Planar Lightwave Circuit)</option>
                                <option value="fbt">FBT (Fused Biconical Taper / Coupler)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Rasio Pembagian</label>
                            <select wire:model.live="splitterRatio"
                                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-purple-500 font-mono">
                                @if($splitterType === 'plc')
                                    <option value="1:2">1:2 (Output: 2 Port)</option>
                                    <option value="1:4">1:4 (Output: 4 Port)</option>
                                    <option value="1:8">1:8 (Output: 8 Port)</option>
                                    <option value="1:16">1:16 (Output: 16 Port)</option>
                                    <option value="1:32">1:32 (Output: 32 Port)</option>
                                @else
                                    <option value="50:50">50:50 (Balanced Tap)</option>
                                    <option value="70:30">70:30 (Trunk 70% / Branch 30%)</option>
                                    <option value="80:20">80:20 (Trunk 80% / Branch 20%)</option>
                                    <option value="85:15">85:15 (Trunk 85% / Branch 15%)</option>
                                    <option value="90:10">90:10 (Trunk 90% / Drop 10%)</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Port Input</label>
                            <input type="number" min="1" max="4" wire:model="splitterInputPorts"
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 font-mono text-slate-800 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Port Output</label>
                            <input type="number" min="2" max="64" wire:model="splitterPortCount"
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 font-mono text-slate-800 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Redaman (dB)</label>
                            <input type="number" step="0.1" wire:model="splitterLossDb"
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 font-mono text-slate-800 dark:text-slate-100">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kemasan Fisik</label>
                        <select wire:model="splitterPackage"
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-800 dark:text-slate-100">
                            <option value="abs_box">ABS Box Splitter</option>
                            <option value="mini_tube">Steel Tube / Mini Tube</option>
                            <option value="cassette">Cassette Module</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan / Keterangan</label>
                        <textarea wire:model="splitterDescription" rows="2" placeholder="Contoh: Splitter untuk ekspansi port ODP sisi timur"
                                  class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-800 dark:text-slate-100"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" wire:click="closeAddSplitterModal" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="createSplitter"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                        Simpan & Pasang Splitter
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================
         MODAL: KONFIGURASI PORT SPLITTER ODP (OUTPUT KE ONU / AP)
         ============================================================ --}}
    @if($showSplitterPortModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-xs p-4 animate-in fade-in duration-150">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-purple-600">settings_input_component</span>
                        Output Port #{{ $selectedSplitterPort?->port_number }} ({{ $selectedSplitterPort?->splitter?->name }})
                    </h3>
                    <button type="button" wire:click="closeSplitterPortModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>

                <div class="space-y-3.5 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Port</label>
                        <select wire:model.live="spPortFormStatus"
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-800 dark:text-slate-100 font-semibold">
                            <option value="available">Available (Port Kosong / Siap)</option>
                            <option value="used">Used (Terhubung ke Pelanggan/ONU)</option>
                            <option value="reserved">Reserved (Dicadangkan)</option>
                            <option value="fault">Fault (Gangguan / Rusak)</option>
                        </select>
                    </div>

                    @if($spPortFormStatus === 'used')
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tipe Perangkat</label>
                                <select wire:model.live="spPortFormTargetType"
                                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-800 dark:text-slate-100">
                                    <option value="onu">ONU / Modem Pelanggan</option>
                                    <option value="ap">Access Point (Hotspot)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Perangkat</label>
                                @if($spPortFormTargetType === 'onu')
                                    <select wire:model="spPortFormTargetId"
                                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-800 dark:text-slate-100">
                                        <option value="">— Pilih ONU —</option>
                                        @foreach($this->availableOnus as $onuItem)
                                            <option value="{{ $onuItem->id }}">{{ $onuItem->name }} ({{ $onuItem->serial_number }})</option>
                                        @endforeach
                                    </select>
                                @else
                                    <select wire:model="spPortFormTargetId"
                                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-800 dark:text-slate-100">
                                        <option value="">— Pilih AP —</option>
                                        @foreach($this->availableAccessPoints as $apItem)
                                            <option value="{{ $apItem->id }}">{{ $apItem->name }} ({{ $apItem->ssid }})</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Warna Core (TIA-598)</label>
                            <select wire:model="spPortFormCoreColor"
                                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-800 dark:text-slate-100">
                                <option value="">— Pilih Warna —</option>
                                @foreach(\App\Models\ISP\SplitterPort::TIA_598_COLORS as $cIdx => $cName)
                                    <option value="{{ $cName }}">Core #{{ $cIdx }}: {{ $cName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Redaman Port (dB)</label>
                            <input type="number" step="0.01" wire:model="spPortFormAttenuation"
                                   placeholder="Contoh: 10.50"
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 font-mono text-slate-800 dark:text-slate-100">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Dropcore</label>
                        <input type="text" wire:model="spPortFormNotes" placeholder="Panjang dropcore, arah tarikan, atau nomor rumah"
                               class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-800 dark:text-slate-100">
                    </div>
                </div>

                <div class="flex justify-between items-center pt-3 border-t border-slate-200 dark:border-slate-800">
                    @if($selectedSplitterPort && $selectedSplitterPort->status === 'used')
                        <button type="button" wire:click="disconnectSplitterPort({{ $selectedSplitterPort->id }})" wire:confirm="Lepas koneksi port ini?"
                                class="px-3 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-xl transition cursor-pointer">
                            Putus Koneksi
                        </button>
                    @else
                        <div></div>
                    @endif

                    <div class="flex gap-2">
                        <button type="button" wire:click="closeSplitterPortModal" class="px-3.5 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer">
                            Batal
                        </button>
                        <button type="button" wire:click="saveSplitterPort"
                                class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                            Simpan Konfigurasi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- ============================================================
     NATIVE SVG TOPOLOGY CONTROLLER (SELF-CONTAINED & ZERO-CDN)
     ============================================================ --}}
<script>
(function() {
    let scale = 1.0;
    let panX = 35;
    let panY = 30;
    let isDragging = false;
    let startX = 0;
    let startY = 0;
    let resizeObs = null;

    function getElements() {
        return {
            container: document.getElementById('topologyContainer'),
            viewport: document.getElementById('topologyViewport'),
            wrapper: document.getElementById('odp-topology-wrapper'),
            svg: document.getElementById('topologySvg')
        };
    }

    function updateTransform() {
        const { viewport } = getElements();
        if (viewport) {
            viewport.setAttribute('transform', `matrix(${scale} 0 0 ${scale} ${panX} ${panY})`);
        }
    }

    function fitToScreen() {
        const { container, viewport } = getElements();
        if (!container || !viewport) return;
        try {
            const bbox = viewport.getBBox();
            const rect = container.getBoundingClientRect();
            const cW = rect.width || container.clientWidth || 1200;
            const cH = rect.height || container.clientHeight || 640;
            if (bbox.width > 0 && bbox.height > 0 && cW > 60 && cH > 60) {
                const padX = 35;
                const padY = 35;
                const scaleX = (cW - padX * 2) / bbox.width;
                const scaleY = (cH - padY * 2) / bbox.height;
                const fitScale = Math.min(scaleX, scaleY);
                // Smart auto-scale: Keep diagram cards and typography readable
                scale = Math.min(1.35, Math.max(0.65, fitScale));

                // If diagram is wider than container at readable scale, center on Hero ODP & Ports
                if (scale * bbox.width > cW) {
                    const nodeHero = document.getElementById('nodeTargetOdp');
                    if (nodeHero) {
                        const heroBbox = nodeHero.getBBox();
                        panX = (cW * 0.40) - ((heroBbox.x + heroBbox.width / 2) * scale);
                    } else {
                        panX = cW - (bbox.width * scale) - 30;
                    }
                } else {
                    panX = (cW - (bbox.width * scale)) / 2 - (bbox.x * scale);
                }
                panY = (cH - (bbox.height * scale)) / 2 - (bbox.y * scale);
                updateTransform();
            }
        } catch (err) {}
    }

    function initOdpTopology() {
        const { container, viewport, wrapper } = getElements();
        if (!container || !viewport) return;

        // Auto fit immediately with stepped retries for layout stabilization
        setTimeout(fitToScreen, 50);
        setTimeout(fitToScreen, 180);
        setTimeout(fitToScreen, 450);

        if (!resizeObs && window.ResizeObserver) {
            resizeObs = new ResizeObserver(() => {
                fitToScreen();
            });
            resizeObs.observe(container);
        }

        if (container.dataset.topoBound === 'true') return;
        container.dataset.topoBound = 'true';

        // Zoom In
        document.getElementById('btnTopoZoomIn')?.addEventListener('click', (e) => {
            e.stopPropagation();
            scale = Math.min(2.5, scale + 0.15);
            updateTransform();
        });

        // Zoom Out
        document.getElementById('btnTopoZoomOut')?.addEventListener('click', (e) => {
            e.stopPropagation();
            scale = Math.max(0.3, scale - 0.15);
            updateTransform();
        });

        // Reset (1:1)
        document.getElementById('btnTopoReset')?.addEventListener('click', (e) => {
            e.stopPropagation();
            scale = 1.0;
            const { container, viewport } = getElements();
            if (container && viewport) {
                const bbox = viewport.getBBox();
                const rect = container.getBoundingClientRect();
                const cW = rect.width || container.clientWidth || 1200;
                const cH = rect.height || container.clientHeight || 640;
                panX = (cW - bbox.width) / 2 - bbox.x;
                panY = (cH - bbox.height) / 2 - bbox.y;
            } else {
                panX = 35;
                panY = 30;
            }
            updateTransform();
        });

        // Fit to Screen
        document.getElementById('btnTopoFit')?.addEventListener('click', (e) => {
            e.stopPropagation();
            fitToScreen();
        });

        // Double Click Container to Fit
        container.addEventListener('dblclick', (e) => {
            if (!e.target.closest('a, button, g[cursor="pointer"]')) {
                fitToScreen();
            }
        });

        // Fullscreen Toggle
        document.getElementById('btnTopoFullscreen')?.addEventListener('click', (e) => {
            e.stopPropagation();
            const { wrapper } = getElements();
            if (!wrapper) return;

            const isFs = wrapper.classList.toggle('is-topology-fullscreen');
            if (isFs) {
                document.body.style.overflow = 'hidden';
                if (wrapper.requestFullscreen) {
                    wrapper.requestFullscreen().catch(() => {});
                }
            } else {
                document.body.style.overflow = '';
                if (document.fullscreenElement && document.exitFullscreen) {
                    document.exitFullscreen().catch(() => {});
                }
            }
            setTimeout(fitToScreen, 100);
            setTimeout(fitToScreen, 300);
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const { wrapper } = getElements();
                if (wrapper && wrapper.classList.contains('is-topology-fullscreen')) {
                    wrapper.classList.remove('is-topology-fullscreen');
                    document.body.style.overflow = '';
                    setTimeout(fitToScreen, 100);
                }
            }
        });

        document.addEventListener('fullscreenchange', () => {
            const { wrapper } = getElements();
            if (wrapper && !document.fullscreenElement) {
                wrapper.classList.remove('is-topology-fullscreen');
                document.body.style.overflow = '';
            }
            setTimeout(fitToScreen, 120);
            setTimeout(fitToScreen, 350);
        });

        // Mouse Drag Panning
        container.addEventListener('mousedown', (e) => {
            if (e.button === 0 && !e.target.closest('a, button')) {
                isDragging = true;
                startX = e.clientX - panX;
                startY = e.clientY - panY;
                container.style.cursor = 'grabbing';
            }
        });

        window.addEventListener('mousemove', (e) => {
            if (isDragging) {
                panX = e.clientX - startX;
                panY = e.clientY - startY;
                updateTransform();
            }
        });

        window.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;
                container.style.cursor = 'grab';
            }
        });

        // Mouse Wheel Zoom
        container.addEventListener('wheel', (e) => {
            e.preventDefault();
            const delta = e.deltaY < 0 ? 0.12 : -0.12;
            const newScale = Math.min(2.5, Math.max(0.3, scale + delta));
            const rect = container.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;

            panX -= (mouseX - panX) * (newScale / scale - 1);
            panY -= (mouseY - panY) * (newScale / scale - 1);
            scale = newScale;
            updateTransform();
        }, { passive: false });

        // Touch support for tablets/smartphones
        container.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                isDragging = true;
                startX = e.touches[0].clientX - panX;
                startY = e.touches[0].clientY - panY;
            }
        });

        container.addEventListener('touchmove', (e) => {
            if (isDragging && e.touches.length === 1) {
                panX = e.touches[0].clientX - startX;
                panY = e.touches[0].clientY - startY;
                updateTransform();
            }
        });

        container.addEventListener('touchend', () => {
            isDragging = false;
        });
    }

    // Expose globally for Alpine and tab switches
    window.initOdpTopology = initOdpTopology;

    function bindLivewireHooks() {
        if (window.Livewire && window.Livewire.hook) {
            window.Livewire.hook('commit', ({ succeed }) => {
                succeed(() => {
                    setTimeout(initOdpTopology, 50);
                });
            });
            window.Livewire.hook('morph.updated', () => {
                setTimeout(initOdpTopology, 50);
            });
        }
    }

    if (window.Livewire) {
        bindLivewireHooks();
    } else {
        document.addEventListener('livewire:init', bindLivewireHooks);
    }

    document.addEventListener('DOMContentLoaded', initOdpTopology);
    document.addEventListener('livewire:navigated', initOdpTopology);

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(initOdpTopology, 80);
    }
})();
</script>
