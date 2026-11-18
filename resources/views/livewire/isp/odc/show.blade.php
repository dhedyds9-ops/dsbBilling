<div class="space-y-5 pb-16" x-data="odcTopologyManager(@js($this->topologyData))">
    {{-- ============================================================
         FLASH ALERTS
         ============================================================ --}}
    @if(session()->has('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-500">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif
    @if(session()->has('topology_warning'))
        <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-sm flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-500">warning</span>
                <span>{{ session('topology_warning') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-amber-500 hover:text-amber-700">&times;</button>
        </div>
    @endif

    {{-- ============================================================
         1. HEADER ODC DETAIL
         ============================================================ --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs">
        <div class="flex items-center gap-4">
            <a href="{{ route('isp.odcs.index') }}" class="p-2.5 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100 bg-slate-100 dark:bg-slate-800 rounded-xl transition-colors">
                <span class="material-symbols-outlined notranslate" translate="no">arrow_back</span>
            </a>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-500 flex items-center justify-center text-white font-bold text-xl shadow-md shadow-indigo-500/20">
                {{ substr($odc->name ?? 'O', 0, 1) }}
            </div>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight">{{ $odc->name }}</h1>
                    <span class="px-2.5 py-0.5 font-mono text-xs font-semibold rounded-md bg-indigo-50 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                        {{ $odc->code }}
                    </span>
                    @php
                        $statusBadge = match($odc->status) {
                            'active' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
                            'warning' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border-amber-200 dark:border-amber-800',
                            default => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400 border-rose-200 dark:border-rose-800'
                        };
                    @endphp
                    <span class="px-2.5 py-0.5 font-semibold text-xs rounded-full border {{ $statusBadge }} flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full {{ $odc->status === 'active' ? 'bg-emerald-500 animate-pulse' : 'bg-current' }}"></span>
                        {{ ucfirst($odc->status) }}
                    </span>
                    @if($odc->branch)
                        <span class="px-2.5 py-0.5 text-xs rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                            Cabang: {{ $odc->branch->name }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-2">
                    <span>{{ $odc->address ?: 'Alamat belum diisi' }}</span>
                    @if($odc->olt)
                        <span class="text-slate-400">·</span>
                        <span class="text-cyan-600 dark:text-cyan-400 font-medium">Uplink: {{ $odc->olt->name }}</span>
                    @endif
                </p>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" wire:click="setTab('topology')"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-cyan-600 to-cyan-500 hover:from-cyan-500 hover:to-cyan-400 text-white rounded-xl text-xs font-bold shadow-md shadow-cyan-500/20 transition-all cursor-pointer">
                <span class="material-symbols-outlined text-sm">hub</span>
                Lihat Topologi
            </button>
            <a href="{{ route('isp.odcs.edit', $odc->id) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-all shadow-2xs">
                <span class="material-symbols-outlined text-sm">edit</span>
                Edit
            </a>
            <button type="button" wire:click="delete" wire:confirm="Yakin ingin menghapus ODC ini beserta datanya?"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/60 text-rose-600 dark:text-rose-400 rounded-xl text-xs font-semibold hover:bg-rose-100 dark:hover:bg-rose-900/60 transition-all">
                <span class="material-symbols-outlined text-sm">delete</span>
                Hapus
            </button>
        </div>
    </div>

    {{-- ============================================================
         2. RINGKASAN KPI (6 METRIC CARDS)
         ============================================================ --}}
    @php $sum = $this->summary; @endphp
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
        {{-- Total Kapasitas --}}
        <div class="p-3.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs relative overflow-hidden group">
            <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Kapasitas</div>
            <div class="text-2xl font-black text-slate-900 dark:text-slate-100 mt-1 font-mono">{{ $sum['total_ports'] }}</div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Port Distribusi</div>
            <div class="w-1.5 h-full bg-indigo-500 absolute left-0 top-0"></div>
        </div>

        {{-- Port Terpakai --}}
        <div class="p-3.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs relative overflow-hidden group">
            <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                <span>Port Terpakai</span>
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ $sum['occupancy_percent'] }}%</span>
            </div>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 font-mono">{{ $sum['used_ports'] }}</div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full mt-2 overflow-hidden">
                <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $sum['occupancy_percent'] }}%"></div>
            </div>
            <div class="w-1.5 h-full bg-emerald-500 absolute left-0 top-0"></div>
        </div>

        {{-- Port Kosong --}}
        <div class="p-3.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs relative overflow-hidden group">
            <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Port Kosong</div>
            <div class="text-2xl font-black text-cyan-600 dark:text-cyan-400 mt-1 font-mono">{{ $sum['available_ports'] }}</div>
            <div class="text-[11px] text-cyan-600/80 dark:text-cyan-400/80 mt-0.5">Siap Dialokasikan</div>
            <div class="w-1.5 h-full bg-cyan-500 absolute left-0 top-0"></div>
        </div>

        {{-- Port Gangguan --}}
        <div class="p-3.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs relative overflow-hidden group">
            <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Port Gangguan</div>
            <div class="text-2xl font-black {{ $sum['fault_ports'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-700 dark:text-slate-300' }} mt-1 font-mono">
                {{ $sum['fault_ports'] }}
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                {{ $sum['fault_ports'] > 0 ? 'Perlu Pengecekan' : 'Normal / Aman' }}
            </div>
            <div class="w-1.5 h-full {{ $sum['fault_ports'] > 0 ? 'bg-rose-500' : 'bg-slate-300 dark:bg-slate-700' }} absolute left-0 top-0"></div>
        </div>

        {{-- ODP Terhubung --}}
        <div class="p-3.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs relative overflow-hidden group">
            <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">ODP Terhubung</div>
            <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1 font-mono">{{ $sum['odps_count'] }}</div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Distribution Points</div>
            <div class="w-1.5 h-full bg-indigo-500 absolute left-0 top-0"></div>
        </div>

        {{-- Pelanggan Terhubung --}}
        <div class="p-3.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs relative overflow-hidden group">
            <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                <span>Pelanggan</span>
                <span class="text-[10px] text-emerald-500">{{ $sum['online_onus_count'] }} Online</span>
            </div>
            <div class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-1 font-mono">{{ $sum['customers_count'] }}</div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $sum['onus_count'] }} Unit ONT/ONU</div>
            <div class="w-1.5 h-full bg-purple-500 absolute left-0 top-0"></div>
        </div>
    </div>

    {{-- ============================================================
         3. TAB NAVIGATION BAR (6 TABS)
         ============================================================ --}}
    <div class="border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-2xl px-2 shadow-xs overflow-x-auto">
        <nav class="flex space-x-1 sm:space-x-3 p-1.5" aria-label="Tabs">
            {{-- Tab A: Overview --}}
            <button type="button" wire:click="setTab('overview')"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center gap-2 whitespace-nowrap {{ $activeTab === 'overview' ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-300 shadow-2xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                <span class="material-symbols-outlined text-base">dashboard</span>
                <span>Overview</span>
            </button>

            {{-- Tab B: Topologi Jaringan --}}
            <button type="button" wire:click="setTab('topology')"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center gap-2 whitespace-nowrap {{ $activeTab === 'topology' ? 'bg-cyan-50 dark:bg-cyan-950/70 text-cyan-600 dark:text-cyan-300 shadow-2xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                <span class="material-symbols-outlined text-base">account_tree</span>
                <span>Topologi Jaringan</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded-full font-mono {{ $activeTab === 'topology' ? 'bg-cyan-200/60 dark:bg-cyan-900 text-cyan-800 dark:text-cyan-200' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                    {{ $this->topologyData['counts']['total_nodes'] ?? 0 }}
                </span>
            </button>

            {{-- Tab C: Port Management --}}
            <button type="button" wire:click="setTab('ports')"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center gap-2 whitespace-nowrap {{ $activeTab === 'ports' ? 'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-300 shadow-2xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                <span class="material-symbols-outlined text-base">settings_input_composite</span>
                <span>Port Management</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded-full font-mono {{ $activeTab === 'ports' ? 'bg-emerald-200/60 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                    {{ $sum['total_ports'] }}
                </span>
            </button>

            {{-- Tab D: ODP Terhubung --}}
            <button type="button" wire:click="setTab('odps')"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center gap-2 whitespace-nowrap {{ $activeTab === 'odps' ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-300 shadow-2xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                <span class="material-symbols-outlined text-base">router</span>
                <span>ODP Terhubung</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded-full font-mono {{ $activeTab === 'odps' ? 'bg-indigo-200/60 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                    {{ $sum['odps_count'] }}
                </span>
            </button>

            {{-- Tab E: Pelanggan --}}
            <button type="button" wire:click="setTab('customers')"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center gap-2 whitespace-nowrap {{ $activeTab === 'customers' ? 'bg-purple-50 dark:bg-purple-950/70 text-purple-600 dark:text-purple-300 shadow-2xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                <span class="material-symbols-outlined text-base">group</span>
                <span>Pelanggan</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded-full font-mono {{ $activeTab === 'customers' ? 'bg-purple-200/60 dark:bg-purple-900 text-purple-800 dark:text-purple-200' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                    {{ $sum['customers_count'] }}
                </span>
            </button>

            {{-- Tab F: Riwayat --}}
            <button type="button" wire:click="setTab('history')"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center gap-2 whitespace-nowrap {{ $activeTab === 'history' ? 'bg-amber-50 dark:bg-amber-950/70 text-amber-600 dark:text-amber-300 shadow-2xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                <span class="material-symbols-outlined text-base">manage_history</span>
                <span>Riwayat</span>
            </button>
        </nav>
    </div>

    {{-- ============================================================
         4. TAB CONTENT
         ============================================================ --}}

    {{-- TAB A: OVERVIEW --}}
    @if($activeTab === 'overview')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            {{-- Col 1 & 2: Main Info --}}
            <div class="lg:col-span-2 space-y-5">
                {{-- ODC Specifications --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-indigo-500 text-lg">info</span>
                        Informasi Spesifikasi ODC
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <span class="text-slate-400 block mb-0.5">Kode ODC</span>
                            <span class="text-slate-900 dark:text-slate-100 font-mono font-bold text-sm">{{ $odc->code }}</span>
                        </div>
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <span class="text-slate-400 block mb-0.5">Nama Perangkat</span>
                            <span class="text-slate-900 dark:text-slate-100 font-bold text-sm">{{ $odc->name }}</span>
                        </div>
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <span class="text-slate-400 block mb-0.5">Total Kapasitas Port</span>
                            <span class="text-indigo-600 dark:text-indigo-400 font-bold text-sm font-mono">{{ $odc->port_count ?: 48 }} Port</span>
                        </div>
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <span class="text-slate-400 block mb-0.5">Status Operasional</span>
                            <span class="font-bold text-sm capitalize text-emerald-600 dark:text-emerald-400">{{ $odc->status }}</span>
                        </div>
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl sm:col-span-2">
                            <span class="text-slate-400 block mb-0.5">Alamat Lokasi Fisik</span>
                            <span class="text-slate-900 dark:text-slate-100 font-medium">{{ $odc->address ?: 'Belum diisi' }}</span>
                        </div>
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl sm:col-span-2">
                            <span class="text-slate-400 block mb-0.5">Deskripsi / Catatan Lapangan</span>
                            <p class="text-slate-700 dark:text-slate-300 leading-relaxed">{{ $odc->description ?: 'Tidak ada deskripsi tambahan.' }}</p>
                        </div>
                    </div>
                </div>

                {{-- OLT Sumber & POP --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-cyan-500 text-lg">lan</span>
                        Uplink Sumber (OLT & POP)
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        {{-- OLT Card --}}
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700/60">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-blue-500 text-base">dns</span>
                                    OLT Sumber
                                </span>
                                @if($odc->olt)
                                    <a href="{{ route('isp.olts.show', $odc->olt->id) }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">
                                        Detail OLT &rarr;
                                    </a>
                                @endif
                            </div>
                            @if($odc->olt)
                                <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $odc->olt->name }}</p>
                                <p class="text-slate-500 font-mono mt-0.5">{{ $odc->olt->code }} · IP: {{ $odc->olt->ip_address ?: '—' }}</p>
                                <div class="mt-2.5 pt-2 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between text-[11px]">
                                    <span class="text-slate-500">Model: {{ $odc->olt->model ?: 'OLT GPON' }}</span>
                                    <span class="px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-semibold">Active</span>
                                </div>
                            @else
                                <p class="text-slate-400 italic">Belum terhubung ke OLT manapun.</p>
                            @endif
                        </div>

                        {{-- POP Card --}}
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700/60">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-cyan-500 text-base">location_city</span>
                                    POP (Point of Presence)
                                </span>
                            </div>
                            @if($odc->pop)
                                <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $odc->pop->name }}</p>
                                <p class="text-slate-500 font-mono mt-0.5">{{ $odc->pop->code }}</p>
                                <p class="text-[11px] text-slate-500 mt-1 line-clamp-1">{{ $odc->pop->address ?: '—' }}</p>
                            @else
                                <p class="text-slate-400 italic">Belum terikat ke POP.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Col 3: GPS Coordinates & Documentation Photos --}}
            <div class="space-y-5">
                {{-- GPS Coordinates --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-rose-500 text-lg">pin_drop</span>
                        Koordinat Geografis (GIS)
                    </h3>
                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between items-center p-2.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl font-mono">
                            <span class="text-slate-500">Latitude:</span>
                            <span class="font-bold text-slate-900 dark:text-slate-100">{{ $odc->latitude ?: '—' }}</span>
                        </div>
                        <div class="flex justify-between items-center p-2.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl font-mono">
                            <span class="text-slate-500">Longitude:</span>
                            <span class="font-bold text-slate-900 dark:text-slate-100">{{ $odc->longitude ?: '—' }}</span>
                        </div>
                        @if($odc->latitude && $odc->longitude)
                            <a href="https://www.google.com/maps?q={{ $odc->latitude }},{{ $odc->longitude }}"
                               target="_blank" rel="noopener noreferrer"
                               class="w-full mt-2 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 rounded-xl font-semibold transition-colors border border-indigo-200 dark:border-indigo-800">
                                <span class="material-symbols-outlined text-sm">map</span>
                                Buka di Google Maps
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Documentation Photos --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-indigo-500 text-lg">photo_camera</span>
                        Foto Dokumentasi Tiang/Box ODC
                    </h3>
                    @php $photos = is_array($odc->photos) ? $odc->photos : []; @endphp
                    @if(count($photos) > 0)
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($photos as $pIdx => $photoUrl)
                                <div class="relative group rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 aspect-video bg-slate-100 dark:bg-slate-800">
                                    <img src="{{ $photoUrl }}" alt="Dokumentasi ODC" class="w-full h-full object-cover">
                                    <button type="button" wire:click="deletePhoto({{ $pIdx }})" wire:confirm="Hapus foto ini?"
                                            class="absolute top-1.5 right-1.5 p-1 bg-rose-600/90 text-white rounded-lg opacity-0 group-hover:opacity-100 transition-opacity">
                                        <span class="material-symbols-outlined text-xs">delete</span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-center border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl">
                            <span class="material-symbols-outlined text-slate-400 text-3xl mb-1">image_not_supported</span>
                            <p class="text-xs text-slate-500">Belum ada foto dokumentasi untuk ODC ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- TAB B: TOPOLOGI JARINGAN (INTERACTIVE FIBER TOPOLOGY) --}}
    @if($activeTab === 'topology')
        <div class="space-y-4">
            {{-- Toolbar: Tracing & Interactive Actions --}}
            <div class="p-4 bg-slate-900 border border-slate-800 rounded-2xl text-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-md">
                {{-- Search / Path Tracing Bar --}}
                <div class="flex items-center gap-2 flex-1 max-w-xl">
                    <div class="relative flex-1">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-500 text-sm">travel_explore</span>
                        <input type="text" wire:model.live.debounce.300ms="traceQuery"
                               placeholder="Cari ODP, ONU, Serial Number, atau Nama Pelanggan untuk tracing jalur..."
                               class="w-full bg-slate-950 border border-slate-700 focus:border-cyan-400 rounded-xl pl-9 pr-3 py-2 text-xs font-mono text-slate-100 placeholder-slate-500 focus:outline-hidden focus:ring-1 focus:ring-cyan-400">
                    </div>
                    <button type="button" wire:click="tracePath"
                            class="px-3.5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-sm">
                        <span class="material-symbols-outlined text-sm">search</span>
                        Trace
                    </button>
                    @if($tracedNodeId || $traceQuery)
                        <button type="button" wire:click="clearTrace"
                                class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs transition">
                            Reset
                        </button>
                    @endif
                </div>

                {{-- Interactive Canvas Controls --}}
                <div class="flex items-center gap-1.5 flex-wrap">
                    <button type="button" @click="zoomIn()" title="Perbesar"
                            class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition border border-slate-700/60">
                        <span class="material-symbols-outlined text-sm">zoom_in</span>
                    </button>
                    <button type="button" @click="zoomOut()" title="Perkecil"
                            class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition border border-slate-700/60">
                        <span class="material-symbols-outlined text-sm">zoom_out</span>
                    </button>
                    <button type="button" @click="resetScale()" title="Skala Normal 1:1"
                            class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition border border-slate-700/60 font-mono text-xs font-bold">
                        1:1
                    </button>
                    <button type="button" @click="fitViewSmart()" title="Paskan Layar (Fit to Screen)"
                            class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition border border-slate-700/60 text-xs font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">fit_screen</span>
                        Fit
                    </button>
                    <button type="button" @click="toggleFullscreen()" title="Layar Penuh"
                            class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition border border-slate-700/60">
                        <span class="material-symbols-outlined text-sm" x-text="isFullscreen ? 'fullscreen_exit' : 'fullscreen'">fullscreen</span>
                    </button>
                </div>
            </div>

            {{-- Topology Canvas Container --}}
            <div id="odc-topology-wrapper" class="relative w-full rounded-2xl overflow-hidden border border-slate-800 bg-[#070b14] shadow-xl">
                {{-- Canvas Area (Min-Height 620px) --}}
                <div id="odc-vis-network-container"
                     wire:ignore
                     x-ref="visContainer"
                     class="w-full h-[620px] cursor-grab active:cursor-grabbing">
                </div>

                {{-- Status Legend (Bottom Left) --}}
                <div class="absolute bottom-3 left-3 z-10 bg-slate-900/90 backdrop-blur-md border border-slate-800 rounded-xl p-2.5 text-[11px] text-slate-300 flex flex-wrap items-center gap-3 shadow-lg pointer-events-none sm:pointer-events-auto">
                    <span class="font-bold text-slate-400 uppercase text-[10px] tracking-wider">Status:</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Online / Normal</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Warning / Marginal</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Offline / Gangguan</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span> Jalur Tracing</span>
                </div>

                {{-- Tracing Active Banner --}}
                @if($tracedNodeId)
                    <div class="absolute top-3 left-3 z-10 bg-cyan-950/90 border border-cyan-800 text-cyan-200 px-3.5 py-1.5 rounded-xl text-xs flex items-center gap-2 shadow-lg">
                        <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                        <span class="font-mono">Active Tracing: {{ $traceQuery }}</span>
                        <button type="button" wire:click="clearTrace" class="ml-1 text-cyan-400 hover:text-white font-bold">&times;</button>
                    </div>
                @endif

                {{-- Empty state if no nodes --}}
                @if(empty($this->topologyData['nodes']))
                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-[#070b14]/90 text-slate-400">
                        <span class="material-symbols-outlined text-slate-600 text-5xl mb-2">account_tree</span>
                        <p class="text-sm font-semibold text-slate-200">Belum ada topologi jalur untuk ODC ini.</p>
                        <p class="text-xs text-slate-500 mt-0.5">Hubungkan OLT sumber dan minimal 1 ODP pada ODC ini.</p>
                    </div>
                @endif
            </div>

            {{-- Node Inspector Slide-Over / Detail Modal --}}
            @if($showNodeDrawer && $selectedNode)
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-lg relative animate-in fade-in slide-in-from-top-4 duration-200">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-500">device_hub</span>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                                Detail Node: {{ $selectedNode['metadata']['name'] ?? $selectedNode['id'] }}
                            </h3>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $selectedNode['group'] ?? 'Device' }}
                            </span>
                        </div>
                        <button type="button" wire:click="closeNodeDrawer" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-4 text-xs">
                        @foreach($selectedNode['metadata'] as $key => $val)
                            @if(!in_array($key, ['detail_url', 'acs_url']) && !is_null($val))
                                <div class="p-2.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                                    <span class="text-slate-400 block capitalize text-[10px]">{{ str_replace('_', ' ', $key) }}</span>
                                    <span class="text-slate-900 dark:text-slate-100 font-mono font-semibold">{{ $val }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    {{-- Optical Link Budget Analysis (for ONUs) --}}
                    @if($this->linkBudget)
                        <div class="mt-4 p-3.5 bg-slate-900 border border-slate-800 rounded-xl text-xs space-y-2">
                            <div class="flex items-center justify-between text-[11px] font-bold text-cyan-400 uppercase tracking-wider">
                                <span class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-sm">timeline</span>
                                    Optical Link Budget & Attenuation Breakdown
                                </span>
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] {{ $this->linkBudget['status'] === 'ideal' ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-amber-950 text-amber-400 border border-amber-800' }}">
                                    {{ strtoupper($this->linkBudget['status']) }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-[11px] font-mono">
                                <div class="p-2 bg-slate-950 rounded-lg">
                                    <span class="text-slate-500 block text-[10px]">OLT Tx Power:</span>
                                    <span class="text-slate-200 font-bold">+{{ $this->linkBudget['tx_olt'] }} dBm</span>
                                </div>
                                <div class="p-2 bg-slate-950 rounded-lg">
                                    <span class="text-slate-500 block text-[10px]">Total Redaman:</span>
                                    <span class="text-amber-400 font-bold">-{{ $this->linkBudget['total_attenuation'] }} dB</span>
                                </div>
                                <div class="p-2 bg-slate-950 rounded-lg">
                                    <span class="text-slate-500 block text-[10px]">Estimasi Rx:</span>
                                    <span class="text-cyan-400 font-bold">{{ $this->linkBudget['calculated_rx'] }} dBm</span>
                                </div>
                                <div class="p-2 bg-slate-950 rounded-lg">
                                    <span class="text-slate-500 block text-[10px]">Actual Rx:</span>
                                    <span class="text-emerald-400 font-bold">{{ $this->linkBudget['actual_rx'] }} dBm</span>
                                </div>
                            </div>
                            <div class="text-[10px] text-slate-400 flex flex-wrap items-center justify-between pt-1 gap-2">
                                <span>Feeder: -{{ $this->linkBudget['feeder_loss'] }}dB | Splitter ODC: -{{ $this->linkBudget['odc_splitter_loss'] }}dB | Splitter ODP: -{{ $this->linkBudget['odp_splitter_loss'] }}dB | Dropcore: -{{ $this->linkBudget['drop_loss'] }}dB</span>
                                <span class="font-mono text-slate-300">Deviasi: {{ $this->linkBudget['deviation'] > 0 ? '+' : '' }}{{ $this->linkBudget['deviation'] }} dB</span>
                            </div>
                        </div>
                    @endif

                    @if(!empty($selectedNode['metadata']['detail_url']) || !empty($selectedNode['metadata']['acs_url']))
                        <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center gap-2 justify-end">
                            @if(!empty($selectedNode['metadata']['acs_url']))
                                <a href="{{ $selectedNode['metadata']['acs_url'] }}"
                                   class="px-3 py-1.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-semibold flex items-center gap-1.5 transition">
                                    <span class="material-symbols-outlined text-sm">settings_remote</span>
                                    Buka Detail GenieACS
                                </a>
                            @endif
                            @if(!empty($selectedNode['metadata']['detail_url']))
                                <a href="{{ $selectedNode['metadata']['detail_url'] }}"
                                   class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold flex items-center gap-1.5 transition">
                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                    Buka Detail Halaman
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif

    {{-- TAB C: PORT MANAGEMENT --}}
    @if($activeTab === 'ports')
        <div class="space-y-4">
            {{-- Filter & Search Controls --}}
            <div class="p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status Port:</span>
                    @foreach(['all' => 'Semua Port', 'used' => 'Used', 'available' => 'Available', 'fault' => 'Fault'] as $sKey => $sLabel)
                        <button type="button" wire:click="$set('portStatusFilter', '{{ $sKey }}')"
                                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all {{ $portStatusFilter === $sKey ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                            {{ $sLabel }}
                        </button>
                    @endforeach
                </div>
                <div class="relative w-full md:w-72">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-sm">search</span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nomor port atau nama ODP..."
                           class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl pl-9 pr-3 py-2 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-indigo-500">
                </div>
            </div>

            {{-- Port Matrix Grid --}}
            @php $ports = $this->ports; @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5">
                @forelse($ports as $port)
                    @php
                        $portColor = match($port['status']) {
                            'used' => 'border-emerald-200 dark:border-emerald-800/80 bg-emerald-50/40 dark:bg-emerald-950/20 text-emerald-800 dark:text-emerald-300',
                            'fault' => 'border-rose-200 dark:border-rose-800/80 bg-rose-50/40 dark:bg-rose-950/20 text-rose-800 dark:text-rose-300',
                            default => 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300'
                        };
                        $statusPill = match($port['status']) {
                            'used' => 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300',
                            'fault' => 'bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300',
                            default => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'
                        };
                    @endphp
                    <div class="p-4 rounded-2xl border {{ $portColor }} shadow-xs transition hover:shadow-md flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-mono font-bold text-sm flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $port['status'] === 'used' ? 'bg-emerald-500' : ($port['status'] === 'fault' ? 'bg-rose-500' : 'bg-slate-400') }}"></span>
                                    Port #{{ $port['port_number'] }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $statusPill }}">
                                    {{ $port['status'] }}
                                </span>
                            </div>
                            <div class="space-y-1.5 text-xs">
                                <div class="flex justify-between text-slate-500 dark:text-slate-400">
                                    <span>ODP Tujuan:</span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $port['odp_code'] ?: '—' }}</span>
                                </div>
                                <div class="flex justify-between text-slate-500 dark:text-slate-400">
                                    <span>Splitter:</span>
                                    <span class="font-mono text-slate-700 dark:text-slate-300">{{ $port['splitter_type'] }}</span>
                                </div>
                                <div class="flex justify-between text-slate-500 dark:text-slate-400">
                                    <span>Downstream Pelanggan:</span>
                                    <span class="font-bold text-purple-600 dark:text-purple-400 font-mono">{{ $port['downstream_customers'] }} Pelanggan</span>
                                </div>
                            </div>
                        </div>

                        @if($port['odp_id'])
                            <div class="mt-3 pt-2.5 border-t border-slate-200/80 dark:border-slate-800 flex justify-between items-center gap-2">
                                <span class="text-[11px] text-slate-500 truncate" title="{{ $port['odp_name'] }}">{{ $port['odp_name'] }}</span>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <a href="{{ route('isp.odps.show', $port['odp_id']) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                        Detail &rarr;
                                    </a>
                                    <button type="button" wire:click="disconnectPort({{ $port['port_number'] }})" wire:confirm="Yakin ingin melepas ODP dari Port #{{ $port['port_number'] }}?"
                                            class="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 rounded transition" title="Lepas Koneksi ODP">
                                        <span class="material-symbols-outlined text-xs">link_off</span>
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="mt-3 pt-2.5 border-t border-slate-200/80 dark:border-slate-800 flex justify-between items-center gap-2">
                                <span class="text-[11px] text-slate-400 italic">Port kosong</span>
                                <button type="button" wire:click="openPortModal({{ $port['port_number'] }})"
                                        class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 rounded-lg text-xs font-semibold inline-flex items-center gap-1 transition">
                                    <span class="material-symbols-outlined text-xs">add_link</span>
                                    Alokasikan
                                </button>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full p-8 text-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-400">
                        <span class="material-symbols-outlined text-4xl mb-1 text-slate-500">filter_alt_off</span>
                        <p class="text-sm font-semibold">Tidak ada port yang sesuai dengan filter.</p>
                    </div>
                @endforelse
            </div>

            {{-- Modal Alokasi ODP ke Port --}}
            @if($showPortModal)
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-xs p-4 animate-in fade-in duration-150">
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 max-w-md w-full shadow-2xl space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <span class="material-symbols-outlined text-indigo-500">settings_input_composite</span>
                                Hubungkan ODP ke Port #{{ $targetPortNumber }}
                            </h3>
                            <button type="button" wire:click="closePortModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Pilih ODP yang akan dialokasikan:
                            </label>
                            <select wire:model="selectedOdpIdForPort"
                                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-indigo-500">
                                <option value="">— Pilih ODP —</option>
                                @foreach($this->availableOdps as $availOdp)
                                    <option value="{{ $availOdp->id }}">{{ $availOdp->code }} — {{ $availOdp->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-500 mt-1.5">ODP yang dipilih akan dipetakan langsung ke port ini pada ODC {{ $odc->name }}.</p>
                        </div>
                        <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                            <button type="button" wire:click="closePortModal" class="px-3.5 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">
                                Batal
                            </button>
                            <button type="button" wire:click="assignPort"
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-xs transition"
                                    {{ !$selectedOdpIdForPort ? 'disabled' : '' }}>
                                Simpan Alokasi Port
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- TAB D: ODP TERHUBUNG --}}
    @if($activeTab === 'odps')
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    Daftar ODP Berasal Dari ODC Ini
                </h3>
                <span class="text-xs text-slate-500 font-mono">Total {{ $odc->odps->count() }} ODP</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase font-semibold">
                        <tr>
                            <th class="p-3.5">Kode ODP</th>
                            <th class="p-3.5">Nama ODP</th>
                            <th class="p-3.5">Port ODC Asal</th>
                            <th class="p-3.5">Kapasitas Splitter</th>
                            <th class="p-3.5">Port Tersedia</th>
                            <th class="p-3.5">Pelanggan Downstream</th>
                            <th class="p-3.5">Status</th>
                            <th class="p-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($odc->odps as $odp)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="p-3.5 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $odp->code }}</td>
                                <td class="p-3.5 font-semibold text-slate-900 dark:text-slate-100">{{ $odp->name }}</td>
                                <td class="p-3.5 font-mono">Port #{{ $odp->odc_port ?: '—' }}</td>
                                <td class="p-3.5 font-mono">1:{{ $odp->split_ratio ?: 8 }}</td>
                                <td class="p-3.5 font-mono text-emerald-600 dark:text-emerald-400 font-semibold">
                                    {{ max(0, ($odp->port_count ?: 8) - ($odp->used_port_count ?? 0)) }} Port
                                </td>
                                <td class="p-3.5 font-mono">{{ $odp->used_port_count ?? 0 }} Pelanggan</td>
                                <td class="p-3.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $odp->status === 'active' ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                                        {{ ucfirst($odp->status) }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-right">
                                    <a href="{{ route('isp.odps.show', $odp->id) }}"
                                       class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900 text-indigo-600 dark:text-indigo-400 rounded-lg font-semibold inline-flex items-center gap-1 transition">
                                        Detail ODP
                                        <span class="material-symbols-outlined text-xs">arrow_forward</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-3xl mb-1 text-slate-500">router</span>
                                    <p class="text-xs">Belum ada ODP yang terhubung ke ODC ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- TAB E: PELANGGAN DOWNSTREAM --}}
    @if($activeTab === 'customers')
        <div class="space-y-4">
            {{-- Filter & Search --}}
            <div class="p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Layanan:</span>
                    @foreach(['all' => 'Semua', 'pppoe' => 'PPPoE', 'hotspot' => 'Hotspot'] as $cKey => $cLabel)
                        <button type="button" wire:click="$set('customerServiceFilter', '{{ $cKey }}')"
                                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all {{ $customerServiceFilter === $cKey ? 'bg-purple-600 text-white shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                            {{ $cLabel }}
                        </button>
                    @endforeach
                </div>
                <div class="relative w-full md:w-80">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-sm">search</span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kode, nama, username, atau SN..."
                           class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl pl-9 pr-3 py-2 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-purple-500">
                </div>
            </div>

            {{-- Customers Data Table --}}
            @php $custRows = $this->downstreamCustomers; @endphp
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase font-semibold">
                            <tr>
                                <th class="p-3.5">Kode Pelanggan</th>
                                <th class="p-3.5">Nama Pelanggan</th>
                                <th class="p-3.5">Layanan</th>
                                <th class="p-3.5">ODP & Port</th>
                                <th class="p-3.5">Modem ONU / ONT</th>
                                <th class="p-3.5">Status Online</th>
                                <th class="p-3.5">Sinyal RX</th>
                                <th class="p-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($custRows as $c)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="p-3.5 font-mono font-bold text-purple-600 dark:text-purple-400">{{ $c['customer_code'] }}</td>
                                    <td class="p-3.5">
                                        <div class="font-bold text-slate-900 dark:text-slate-100">{{ $c['customer_name'] }}</div>
                                        <div class="text-[11px] text-slate-500 font-mono">{{ $c['username'] }}</div>
                                    </td>
                                    <td class="p-3.5">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $c['service_type'] === 'PPPoE' ? 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300' : 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300' }}">
                                            {{ $c['service_type'] }}
                                        </span>
                                    </td>
                                    <td class="p-3.5">
                                        <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $c['odp_code'] }}</div>
                                        <div class="text-[11px] text-slate-500">Drop Port #{{ $c['odp_port'] }}</div>
                                    </td>
                                    <td class="p-3.5">
                                        <div class="font-mono text-slate-800 dark:text-slate-200">{{ $c['onu_sn'] }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $c['onu_model'] ?: 'ONT GPON' }}</div>
                                    </td>
                                    <td class="p-3.5">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold flex items-center gap-1 w-max {{ $c['is_online'] ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300' : 'bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $c['is_online'] ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                            {{ $c['is_online'] ? 'Online' : 'Offline' }}
                                        </span>
                                    </td>
                                    <td class="p-3.5 font-mono">
                                        @if($c['rx_power'])
                                            <span class="{{ (float) $c['rx_power'] < -26.0 ? 'text-rose-500 font-bold' : 'text-emerald-600 dark:text-emerald-400' }}">
                                                {{ $c['rx_power'] }} dBm
                                            </span>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="p-3.5 text-right space-x-1 whitespace-nowrap">
                                        @if($c['acs_url'])
                                            <a href="{{ $c['acs_url'] }}" target="_blank"
                                               class="p-1.5 bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 rounded-lg hover:bg-cyan-100 transition inline-block" title="GenieACS Detail">
                                                <span class="material-symbols-outlined text-xs">settings_remote</span>
                                            </a>
                                        @endif
                                        <a href="{{ $c['detail_url'] }}"
                                           class="px-2.5 py-1 bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/60 dark:hover:bg-purple-900 text-purple-600 dark:text-purple-400 rounded-lg font-semibold inline-flex items-center gap-1 transition">
                                            Profil
                                            <span class="material-symbols-outlined text-xs">arrow_forward</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-slate-400">
                                        <span class="material-symbols-outlined text-3xl mb-1 text-slate-500">person_off</span>
                                        <p class="text-xs">Tidak ada pelanggan downstream yang ditemukan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- TAB F: RIWAYAT & AKTIVITAS --}}
    @if($activeTab === 'history')
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined text-amber-500 text-lg">history</span>
                Log Riwayat Koneksi, Port & Aktivitas Teknisi
            </h3>
            @php $events = $this->history; @endphp
            @if(count($events) > 0)
                <div class="relative pl-6 space-y-4 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200 dark:before:bg-slate-800">
                    @foreach($events as $ev)
                        <div class="relative flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center -ml-6 text-[10px] shadow-sm">
                                <span class="material-symbols-outlined text-xs">{{ $ev['icon'] }}</span>
                            </span>
                            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl flex-1 text-xs">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-bold text-slate-900 dark:text-slate-100">{{ $ev['title'] }}</span>
                                    <span class="text-slate-400 font-mono text-[10px]">{{ $ev['date']?->format('d M Y H:i') }}</span>
                                </div>
                                <p class="text-slate-600 dark:text-slate-300">{{ $ev['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center text-slate-400 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl">
                    <span class="material-symbols-outlined text-3xl mb-1 text-slate-500">history_toggle_off</span>
                    <p class="text-xs">Belum ada catatan riwayat gangguan atau perubahan koneksi ODC.</p>
                </div>
            @endif
        </div>
    @endif
</div>

{{-- ============================================================
     VIS-NETWORK TOPOLOGY CONTROLLER (ALPINE.JS SCRIPT)
     ============================================================ --}}
<script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
<script>
function odcTopologyManager(initialTopology) {
    return {
        network: null,
        isFullscreen: false,
        currentTopology: initialTopology,

        init() {
            this.$nextTick(() => {
                this.loadTopology(this.currentTopology);
            });

            // Listen for Livewire focus event
            window.addEventListener('topology-focus-node', (event) => {
                if (event.detail && event.detail.nodeId && this.network) {
                    this.network.selectNodes([event.detail.nodeId]);
                    this.network.focus(event.detail.nodeId, {
                        scale: 1.25,
                        animation: { duration: 400, easingFunction: 'easeInOutQuad' }
                    });
                }
            });

            window.addEventListener('topology-reset-focus', () => {
                if (this.network) {
                    this.network.unselectAll();
                    this.fitViewSmart();
                }
            });
        },

        loadTopology(topology) {
            this.currentTopology = topology;
            const container = this.$refs.visContainer;
            if (!container || typeof vis === 'undefined') return;

            if (!topology || !topology.nodes || topology.nodes.length === 0) {
                if (this.network) {
                    this.network.destroy();
                    this.network = null;
                }
                return;
            }

            const nodes = new vis.DataSet(topology.nodes);
            const edges = new vis.DataSet(topology.edges);

            const options = {
                layout: {
                    hierarchical: {
                        enabled: true,
                        direction: 'LR', // Left-to-Right: OLT -> ODF -> ODC -> ODP -> ONU -> Cust
                        sortMethod: 'directed',
                        levelSeparation: 320,
                        nodeSpacing: 80,
                        treeSpacing: 100,
                        blockShifting: true,
                        edgeMinimization: true,
                        parentCentralization: true
                    }
                },
                physics: false,
                nodes: {
                    borderWidth: 2,
                    borderWidthSelected: 4,
                    shadow: { enabled: true, color: 'rgba(0,0,0,0.5)', size: 6, x: 2, y: 2 },
                    margin: { top: 10, right: 16, bottom: 10, left: 16 },
                    font: { size: 13, face: 'JetBrains Mono, monospace', color: '#f8fafc' },
                    shapeProperties: { borderRadius: 10 }
                },
                edges: {
                    width: 2.5,
                    selectionWidth: 4,
                    hoverWidth: 3.5,
                    smooth: { type: 'cubicBezier', forceDirection: 'horizontal', roundness: 0.35 },
                    font: { size: 10, align: 'top', color: '#94a3b8' }
                },
                interaction: {
                    hover: true,
                    tooltipDelay: 150,
                    zoomView: true,
                    dragView: true,
                    selectable: true,
                    multiselect: false
                }
            };

            if (this.network) {
                this.network.destroy();
            }

            this.network = new vis.Network(container, { nodes, edges }, options);

            // Click node event -> sync to Livewire
            this.network.on('click', (params) => {
                if (params.nodes && params.nodes.length > 0) {
                    const nodeId = params.nodes[0];
                    if (window.Livewire) {
                        const lwEl = container.closest('[wire\\:id]');
                        if (lwEl && window.Livewire.find(lwEl.getAttribute('wire:id'))) {
                            window.Livewire.find(lwEl.getAttribute('wire:id')).selectNode(nodeId);
                        }
                    }
                }
            });

            setTimeout(() => {
                this.fitViewSmart();
            }, 100);
        },

        zoomIn() {
            if (!this.network) return;
            const current = this.network.getScale();
            this.network.moveTo({ scale: current * 1.25, animation: { duration: 200 } });
        },

        zoomOut() {
            if (!this.network) return;
            const current = this.network.getScale();
            this.network.moveTo({ scale: current * 0.8, animation: { duration: 200 } });
        },

        resetScale() {
            if (!this.network) return;
            this.network.moveTo({ scale: 1.0, animation: { duration: 200 } });
        },

        fitViewSmart() {
            if (!this.network) return;
            this.network.fit({
                padding: 40,
                animation: { duration: 350, easingFunction: 'easeInOutQuad' }
            });
        },

        toggleFullscreen() {
            const el = document.getElementById('odc-topology-wrapper');
            if (!el) return;

            if (!document.fullscreenElement) {
                el.requestFullscreen().then(() => {
                    this.isFullscreen = true;
                    setTimeout(() => this.fitViewSmart(), 300);
                }).catch(() => {});
            } else {
                document.exitFullscreen().then(() => {
                    this.isFullscreen = false;
                    setTimeout(() => this.fitViewSmart(), 300);
                }).catch(() => {});
            }
        }
    };
}
</script>
