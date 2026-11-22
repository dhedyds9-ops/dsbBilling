<div class="space-y-5 pb-16">
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
                    @if($odc->parentOdc)
                        <span class="text-slate-400">·</span>
                        <a href="{{ route('isp.odcs.show', $odc->parent_odc_id) }}" class="text-cyan-600 dark:text-cyan-400 font-medium hover:underline inline-flex items-center gap-1">
                            <span class="material-symbols-outlined notranslate" style="font-size:14px">subdirectory_arrow_right</span>
                            Induk ODC: {{ $odc->parentOdc->name }}
                        </a>
                    @elseif($odc->olt)
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
         2. RINGKASAN KPI (6 METRIC CARDS - Disembunyikan di tab topologi agar diagram luas)
         ============================================================ --}}
    @php $sum = $this->summary; @endphp
    @if($activeTab !== 'topology')
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
    @endif

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

                {{-- Splitter ODC Management (PLC & FBT) --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                                <span class="material-symbols-outlined text-purple-500 text-lg">call_split</span>
                                Splitter Terpasang pada ODC (Tier-1 Tray)
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Daftar splitter kaskade PLC dan FBT terpasang di dalam ODC ini</p>
                        </div>
                        <button type="button" wire:click="openAddSplitterModal"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-600 hover:bg-purple-500 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                            <span class="material-symbols-outlined text-sm">add</span>
                            Pasang Splitter Baru
                        </button>
                    </div>

                    @php $splitters = $this->odcSplitters; @endphp
                    @if($splitters->isNotEmpty())
                        <div class="space-y-4">
                            @foreach($splitters as $sp)
                                @php
                                    $usedPorts = $sp->ports->where('port_type', 'output')->where('status', 'used')->count();
                                    $totalPorts = $sp->port_count ?: 8;
                                    $pct = $totalPorts > 0 ? round(($usedPorts / $totalPorts) * 100) : 0;
                                    $isFbt = $sp->type === 'fbt';
                                @endphp
                                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700/70 bg-slate-50/50 dark:bg-slate-800/40">
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
                        <div class="p-8 text-center border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl">
                            <span class="material-symbols-outlined text-slate-400 text-3xl mb-1">call_split</span>
                            <p class="text-xs text-slate-500 mb-2">Belum ada splitter yang dipasang pada tray ODC ini.</p>
                            <button type="button" wire:click="openAddSplitterModal"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-600 hover:bg-purple-500 text-white rounded-xl text-xs font-semibold cursor-pointer">
                                <span class="material-symbols-outlined text-sm">add</span>
                                Pasang Splitter Pertama
                            </button>
                        </div>
                    @endif
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
        <style>
            #odcTopologyContainer {
                width: 100% !important;
                height: 640px !important;
                min-height: 560px !important;
                background-color: #020617 !important;
            }
            #odc-topology-wrapper.is-topology-fullscreen,
            #odc-topology-wrapper:fullscreen,
            #odc-topology-wrapper:-webkit-full-screen {
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
            #odc-topology-wrapper.is-topology-fullscreen .odc-topo-toolbar,
            #odc-topology-wrapper:fullscreen .odc-topo-toolbar,
            #odc-topology-wrapper:-webkit-full-screen .odc-topo-toolbar {
                border-radius: 0 !important;
                background-color: #0b1120 !important;
                border-color: #1e293b !important;
                flex-shrink: 0 !important;
            }
            #odc-topology-wrapper.is-topology-fullscreen #odcTopologyContainer,
            #odc-topology-wrapper:fullscreen #odcTopologyContainer,
            #odc-topology-wrapper:-webkit-full-screen #odcTopologyContainer {
                flex: 1 1 auto !important;
                height: calc(100vh - 65px) !important;
                min-height: calc(100vh - 65px) !important;
                max-height: calc(100vh - 65px) !important;
                width: 100vw !important;
                border-radius: 0 !important;
                background-color: #020617 !important;
            }
        </style>
        <div class="space-y-4">
            <div id="odc-topology-wrapper" x-data x-init="$nextTick(() => { window.initOdcTopology && window.initOdcTopology(); })" class="relative w-full rounded-2xl overflow-hidden border border-slate-800 bg-[#020617] shadow-xl">
                {{-- Toolbar: Tracing & Interactive Actions --}}
                <div class="odc-topo-toolbar p-4 bg-slate-900 border-b border-slate-800 text-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-3">
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
                        <button type="button" id="btnOdcZoomIn" title="Perbesar"
                                class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition border border-slate-700/60 cursor-pointer">
                            <span class="material-symbols-outlined text-sm">zoom_in</span>
                        </button>
                        <button type="button" id="btnOdcZoomOut" title="Perkecil"
                                class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition border border-slate-700/60 cursor-pointer">
                            <span class="material-symbols-outlined text-sm">zoom_out</span>
                        </button>
                        <button type="button" id="btnOdcReset" title="Skala Normal 1:1"
                                class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition border border-slate-700/60 font-mono text-xs font-bold cursor-pointer">
                            1:1
                        </button>
                        <button type="button" id="btnOdcFit" title="Paskan Layar (Fit to Screen)"
                                class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition border border-slate-700/60 text-xs font-semibold flex items-center gap-1 cursor-pointer">
                            <span class="material-symbols-outlined text-sm">fit_screen</span>
                            Fit
                        </button>
                        <button type="button" id="btnOdcFullscreen" title="Layar Penuh"
                                class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition border border-slate-700/60 cursor-pointer">
                            <span class="material-symbols-outlined text-sm">fullscreen</span>
                        </button>
                    </div>
                </div>

                {{-- SVG TOPOLOGY CANVAS (INTELLIGENT FIBER TREE) --}}
                @php
                $parentOdc = $odc->parentOdc;
                $isCascadeOdc = !empty($parentOdc);
                $olt = $odc->olt ?? $parentOdc?->olt;
                $oltName = $olt ? $olt->name : 'OLT GPON Core';
                $oltIp = $olt ? ($olt->ip_address ?: '10.10.10.1') : '10.10.10.1';
                $oltModel = $olt?->model ?: 'GPON OLT Chassis';

                // Joint Closure Detections
                $feederClosure = $odc->jointClosure ?? $parentOdc?->jointClosure;

                $odpList = $this->connectedOdps;
                $childOdcList = $this->childOdcs;

                // Redaman ODC Splitter
                $odcCap = $odc->port_count ?: 48;
                $odcSplitterRatio = $odcCap > 16 ? '1:8' : ($odcCap > 8 ? '1:4' : '1:2');
                $odcLoss = $odcCap > 16 ? '10.5' : ($odcCap > 8 ? '7.2' : '3.8');

                // Link Budget Estimation
                $sfpTx = 8.0;
                $feederLoss = 0.4;
                $feederSpliceLoss = $feederClosure ? 0.1 : 0.0;
                $odcLossVal = (float) $odcLoss;
                $distLoss = 0.3;
                $odpLossVal = 10.5;
                $dropLoss = 0.2;
                $estOntPower = round($sfpTx - $feederLoss - $feederSpliceLoss - $odcLossVal - $distLoss - $odpLossVal - $dropLoss, 1);

                // Hitung posisi vertikal secara dinamis
                $odpLayouts = [];
                $headerY = 35;
                $currentY = 90;
                $rowSpacing = 24;

                foreach ($odpList as $odpItem) {
                    $onuList = $odpItem->onus;
                    $onuCount = $onuList->count();
                    $boxHeight = max(112, $onuCount > 0 ? ($onuCount * 70 + 14) : 112);
                    $odpCenterY = $currentY + ($boxHeight / 2);

                    $odpLayouts[] = [
                        'type' => 'odp',
                        'model' => $odpItem,
                        'topY' => $currentY,
                        'height' => $boxHeight,
                        'centerY' => $odpCenterY,
                        'onus' => $onuList,
                    ];

                    $currentY += $boxHeight + $rowSpacing;
                }

                foreach ($childOdcList as $childOdcItem) {
                    $boxHeight = 112;
                    $centerY = $currentY + ($boxHeight / 2);
                    $odpLayouts[] = [
                        'type' => 'sub_odc',
                        'model' => $childOdcItem,
                        'topY' => $currentY,
                        'height' => $boxHeight,
                        'centerY' => $centerY,
                        'onus' => collect(),
                    ];
                    $currentY += $boxHeight + $rowSpacing;
                }

                $totalCanvasHeight = max(580, $currentY + 50);

                if (!empty($odpLayouts)) {
                    $firstY = $odpLayouts[0]['centerY'];
                    $lastY = $odpLayouts[count($odpLayouts) - 1]['centerY'];
                    $targetOdcCenterY = ($firstY + $lastY) / 2;
                } else {
                    $targetOdcCenterY = 280;
                }

                // Horizontal Column Geometry (Compact, High-Contrast & Wide)
                $wOlt = 220;
                $wClosure = 170;
                $wTier = 220;
                $wTargetOdc = 250;
                $wOdp = 240;
                $wOnu = 300;
                $span = 60;

                $colX = 35;
                $xOlt = $colX;
                $colX += $wOlt + $span;

                $xFeederClosure = null;
                if ($feederClosure) {
                    $xFeederClosure = $colX;
                    $colX += $wClosure + $span;
                }

                $xTier1 = null;
                if ($isCascadeOdc) {
                    $xTier1 = $colX;
                    $colX += $wTier + $span;
                }

                $xTargetOdc = $colX;
                $colX += $wTargetOdc + $span;

                $xOdp = $colX;
                $colX += $wOdp + 45;

                $xOnu = $colX;
                $totalCanvasWidth = $xOnu + $wOnu + 40;

                $qSearch = trim(strtolower($traceQuery));
            @endphp

                <div id="odcTopologyContainer"
                     style="width: 100%; height: 640px; min-height: 560px; background-color: #020617;"
                     class="w-full min-h-[580px] h-[640px] xl:h-[680px] bg-[#020617] cursor-grab active:cursor-grabbing select-none overflow-hidden relative border-t border-slate-800">
                    <svg id="odcTopologySvg" class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="gradOdcOlt" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#0c2340"/>
                                <stop offset="50%" stop-color="#1e3a8a"/>
                                <stop offset="100%" stop-color="#0284c7"/>
                            </linearGradient>
                            <linearGradient id="gradOdcClosure" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#1e1b4b"/>
                                <stop offset="50%" stop-color="#312e81"/>
                                <stop offset="100%" stop-color="#6366f1"/>
                            </linearGradient>
                            <linearGradient id="gradOdcParent" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#1e1b4b"/>
                                <stop offset="50%" stop-color="#3730a3"/>
                                <stop offset="100%" stop-color="#6366f1"/>
                            </linearGradient>
                            <linearGradient id="gradOdcTarget" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#2a1608"/>
                                <stop offset="50%" stop-color="#78350f"/>
                                <stop offset="100%" stop-color="#d97706"/>
                            </linearGradient>
                            <linearGradient id="gradOdcOdp" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#022c22"/>
                                <stop offset="50%" stop-color="#064e3b"/>
                                <stop offset="100%" stop-color="#10b981"/>
                            </linearGradient>
                            <linearGradient id="gradOdcSub" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#2e1065"/>
                                <stop offset="50%" stop-color="#4c1d95"/>
                                <stop offset="100%" stop-color="#8b5cf6"/>
                            </linearGradient>
                            <filter id="odcNodeShadow" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="5" stdDeviation="8" flood-color="#000" flood-opacity="0.75"/>
                            </filter>
                            <filter id="glowTargetOdc" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="0" stdDeviation="10" flood-color="#f59e0b" flood-opacity="0.4"/>
                            </filter>
                            <filter id="glowClosureOdc" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="0" stdDeviation="8" flood-color="#818cf8" flood-opacity="0.35"/>
                            </filter>
                        </defs>

                        <g id="odcTopologyViewport" transform="matrix(1 0 0 1 35 30)">
                            {{-- ============================================================== --}}
                            {{-- 0. ARCHITECTURAL STAGE COLUMN HEADERS                          --}}
                            {{-- ============================================================== --}}
                            {{-- Stage 1 Header --}}
                            <g transform="translate({{ $xOlt }}, 35)">
                                <rect width="190" height="24" rx="6" fill="#0f172a" stroke="#0284c7" stroke-width="1.2"/>
                                <circle cx="14" cy="12" r="3.5" fill="#38bdf8"/>
                                <text x="25" y="16" fill="#38bdf8" font-size="9.5" font-weight="900" font-family="sans-serif" letter-spacing="0.5">1. CORE OLT</text>
                            </g>

                            {{-- Feeder Closure Header (If present) --}}
                            @if($xFeederClosure)
                                <g transform="translate({{ $xFeederClosure }}, 35)">
                                    <rect width="160" height="24" rx="6" fill="#0f172a" stroke="#818cf8" stroke-width="1.2"/>
                                    <circle cx="14" cy="12" r="3.5" fill="#a5b4fc"/>
                                    <text x="25" y="16" fill="#a5b4fc" font-size="9.5" font-weight="900" font-family="sans-serif" letter-spacing="0.5">FEEDER CLOSURE</text>
                                </g>
                            @endif

                            {{-- Stage 2 Header (If cascade) --}}
                            @if($xTier1)
                                <g transform="translate({{ $xTier1 }}, 35)">
                                    <rect width="195" height="24" rx="6" fill="#0f172a" stroke="#6366f1" stroke-width="1.2"/>
                                    <circle cx="14" cy="12" r="3.5" fill="#818cf8"/>
                                    <text x="25" y="16" fill="#818cf8" font-size="9.5" font-weight="900" font-family="sans-serif" letter-spacing="0.5">2. INDUK ODC (T-1)</text>
                                </g>
                            @endif

                            {{-- Stage 3 Header --}}
                            <g transform="translate({{ $xTargetOdc }}, 35)">
                                <rect width="210" height="24" rx="6" fill="#0f172a" stroke="#f59e0b" stroke-width="1.5"/>
                                <circle cx="14" cy="12" r="3.5" fill="#fbbf24"/>
                                <text x="25" y="16" fill="#fbbf24" font-size="9.5" font-weight="900" font-family="sans-serif" letter-spacing="0.5">3. KABINET ODC (TARGET)</text>
                            </g>

                            {{-- Stage 4 Header --}}
                            <g transform="translate({{ $xOdp }}, 35)">
                                <rect width="200" height="24" rx="6" fill="#0f172a" stroke="#10b981" stroke-width="1.2"/>
                                <circle cx="14" cy="12" r="3.5" fill="#34d399"/>
                                <text x="25" y="16" fill="#34d399" font-size="9.5" font-weight="900" font-family="sans-serif" letter-spacing="0.5">4. TITIK DISTRIBUSI (ODP)</text>
                            </g>

                            {{-- Stage 5 Header --}}
                            <g transform="translate({{ $xOnu }}, 35)">
                                <rect width="240" height="24" rx="6" fill="#0f172a" stroke="#8b5cf6" stroke-width="1.2"/>
                                <circle cx="14" cy="12" r="3.5" fill="#c084fc"/>
                                <text x="25" y="16" fill="#c084fc" font-size="9.5" font-weight="900" font-family="sans-serif" letter-spacing="0.5">5. ONT &amp; PELANGGAN</text>
                            </g>

                            {{-- ============================================================== --}}
                            {{-- 1. STAGE 1: OLT CORE NODE                                      --}}
                            {{-- ============================================================== --}}
                            <g id="nodeOlt" transform="translate({{ $xOlt }}, {{ $targetOdcCenterY - 60 }})"
                               class="cursor-pointer" filter="url(#odcNodeShadow)"
                               @if($olt) onclick="window.open('/isp/olts/{{ $olt->id }}', '_blank')" @endif>
                                <rect width="{{ $wOlt }}" height="120" rx="14" fill="url(#gradOdcOlt)" stroke="#38bdf8" stroke-width="2.2"/>
                                <rect x="12" y="12" width="95" height="18" rx="4" fill="#0369a1"/>
                                <text x="18" y="24" fill="#e0f2fe" font-size="9" font-weight="bold" font-family="sans-serif">CORE OLT GPON</text>

                                <circle cx="{{ $wOlt - 18 }}" cy="21" r="4.5" fill="#34d399"/>
                                <text x="{{ $wOlt - 27 }}" y="25" fill="#34d399" font-size="9.5" font-weight="bold" font-family="monospace" text-anchor="end">ONLINE</text>

                                <text x="12" y="52" fill="#ffffff" font-size="14.5" font-weight="900" font-family="sans-serif">{{ Str::limit($oltName, 17) }}</text>
                                <text x="12" y="69" fill="#94a3b8" font-size="10" font-family="monospace">IP: {{ $oltIp }}</text>

                                <rect x="10" y="78" width="{{ $wOlt - 20 }}" height="32" rx="7" fill="#081b33" stroke="#1e3a8a" stroke-width="1.1"/>
                                <text x="16" y="92" fill="#38bdf8" font-size="10" font-weight="bold" font-family="monospace">SFP Tx: +{{ $sfpTx }} dBm (C++)</text>
                                <text x="16" y="104" fill="#94a3b8" font-size="8.5" font-family="sans-serif">1490nm Tx • PON Port 1</text>

                                {{-- Connector socket right side --}}
                                <rect x="{{ $wOlt - 5 }}" y="48" width="8" height="20" rx="3" fill="#0284c7" stroke="#38bdf8" stroke-width="1.3"/>
                            </g>

                            {{-- FEEDER CABLE: OLT ➔ FEEDER CLOSURE (if present) OR OLT ➔ ODC --}}
                            @php $nextFeederTargetX = $xFeederClosure ?? ($xTier1 ?? $xTargetOdc); @endphp
                            <path d="M {{ $xOlt + $wOlt }} {{ $targetOdcCenterY }} L {{ $nextFeederTargetX }} {{ $targetOdcCenterY }}" stroke="#0284c7" stroke-width="6" opacity="0.35"/>
                            <path d="M {{ $xOlt + $wOlt }} {{ $targetOdcCenterY }} L {{ $nextFeederTargetX }} {{ $targetOdcCenterY }}" stroke="#38bdf8" stroke-width="2.5" stroke-dasharray="8,5"/>

                            @php $midFeeder = ($xOlt + $wOlt + $nextFeederTargetX) / 2; @endphp
                            <g transform="translate({{ $midFeeder - 45 }}, {{ $targetOdcCenterY - 12 }})">
                                <rect width="90" height="24" rx="6" fill="#090d16" stroke="#0284c7" stroke-width="1.2"/>
                                <text x="45" y="15" fill="#bae6fd" font-size="9" font-weight="bold" font-family="monospace" text-anchor="middle">Feeder FO</text>
                            </g>

                            {{-- FEEDER JOINT CLOSURE NODE (if present) --}}
                            @if($feederClosure && $xFeederClosure)
                                <g id="nodeFeederClosure" transform="translate({{ $xFeederClosure }}, {{ $targetOdcCenterY - 55 }})" class="cursor-pointer" filter="url(#glowClosureOdc)">
                                    <rect width="{{ $wClosure }}" height="110" rx="14" fill="url(#gradOdcClosure)" stroke="#818cf8" stroke-width="2"/>
                                    <rect x="10" y="10" width="100" height="18" rx="4" fill="#312e81"/>
                                    <text x="16" y="22" fill="#c7d2fe" font-size="8.5" font-weight="bold" font-family="sans-serif">JOINT CLOSURE</text>

                                    <text x="10" y="48" fill="#ffffff" font-size="13" font-weight="900" font-family="sans-serif">{{ Str::limit($feederClosure->name, 15) }}</text>
                                    <text x="10" y="64" fill="#c7d2fe" font-size="9.5" font-family="monospace">{{ $feederClosure->code }}</text>

                                    <rect x="8" y="72" width="{{ $wClosure - 16 }}" height="28" rx="6" fill="#090d16" stroke="#4338ca" stroke-width="1"/>
                                    <text x="14" y="85" fill="#a5b4fc" font-size="9" font-weight="bold" font-family="sans-serif">Splice: -0.1 dB</text>
                                    <text x="14" y="95" fill="#64748b" font-size="8" font-family="sans-serif">{{ ucfirst($feederClosure->type ?? 'Dome') }} Box</text>
                                </g>

                                {{-- Cable: Feeder Closure ➔ ODC --}}
                                @php $odcTargetX = $xTier1 ?? $xTargetOdc; @endphp
                                <path d="M {{ $xFeederClosure + $wClosure }} {{ $targetOdcCenterY }} L {{ $odcTargetX }} {{ $targetOdcCenterY }}" stroke="#4f46e5" stroke-width="6" opacity="0.35"/>
                                <path d="M {{ $xFeederClosure + $wClosure }} {{ $targetOdcCenterY }} L {{ $odcTargetX }} {{ $targetOdcCenterY }}" stroke="#818cf8" stroke-width="2.5"/>
                                @php $midSplice = ($xFeederClosure + $wClosure + $odcTargetX) / 2; @endphp
                                <g transform="translate({{ $midSplice - 45 }}, {{ $targetOdcCenterY - 12 }})">
                                    <rect width="90" height="24" rx="6" fill="#090d16" stroke="#6366f1" stroke-width="1.2"/>
                                    <text x="45" y="15" fill="#c7d2fe" font-size="9" font-weight="bold" font-family="monospace" text-anchor="middle">Splice Trunk</text>
                                </g>
                            @endif

                            @if($isCascadeOdc)
                                {{-- Node Induk ODC (Tier-1) --}}
                                <g id="nodeParentOdc" transform="translate({{ $xTier1 }}, {{ $targetOdcCenterY - 60 }})"
                                   class="cursor-pointer" filter="url(#odcNodeShadow)"
                                   onclick="window.open('/isp/odcs/{{ $parentOdc->id }}', '_blank')">
                                    <rect width="{{ $wTier }}" height="120" rx="14" fill="url(#gradOdcParent)" stroke="#a5b4fc" stroke-width="2.2"/>
                                    <rect x="12" y="12" width="100" height="18" rx="4" fill="#3730a3"/>
                                    <text x="18" y="24" fill="#e0e7ff" font-size="9" font-weight="bold" font-family="sans-serif">INDUK ODC (T-1)</text>

                                    <text x="12" y="52" fill="#ffffff" font-size="14.5" font-weight="900" font-family="sans-serif">{{ Str::limit($parentOdc->name, 17) }}</text>
                                    <text x="12" y="69" fill="#c7d2fe" font-size="10" font-family="monospace">{{ $parentOdc->code }}</text>

                                    <rect x="10" y="78" width="{{ $wTier - 20 }}" height="32" rx="7" fill="#1e1b4b" stroke="#4338ca" stroke-width="1.1"/>
                                    <text x="16" y="92" fill="#a5b4fc" font-size="10" font-weight="bold" font-family="sans-serif">PLC Splitter 1:4 (-7.2 dB)</text>
                                    <text x="16" y="104" fill="#c7d2fe" font-size="9" font-family="monospace">Est. Out: ~ +0.4 dBm</text>
                                </g>

                                {{-- Sub-Feeder Line: Induk ODC -> Sub ODC Target --}}
                                <path d="M {{ $xTier1 + $wTier }} {{ $targetOdcCenterY }} L {{ $xTargetOdc }} {{ $targetOdcCenterY }}" stroke="#4f46e5" stroke-width="6" opacity="0.35"/>
                                <path d="M {{ $xTier1 + $wTier }} {{ $targetOdcCenterY }} L {{ $xTargetOdc }} {{ $targetOdcCenterY }}" stroke="#818cf8" stroke-width="2.5"/>

                                @php $midSubFeeder = ($xTier1 + $wTier + $xTargetOdc) / 2; @endphp
                                <g transform="translate({{ $midSubFeeder - 45 }}, {{ $targetOdcCenterY - 12 }})">
                                    <rect width="90" height="24" rx="6" fill="#090d16" stroke="#6366f1" stroke-width="1.2"/>
                                    <text x="45" y="15" fill="#c7d2fe" font-size="9" font-weight="bold" font-family="monospace" text-anchor="middle">Sub-Feeder</text>
                                </g>
                            @endif

                            {{-- ============================================================== --}}
                            {{-- 2. STAGE 3: TARGET ODC HERO NODE                               --}}
                            {{-- ============================================================== --}}
                            <g id="nodeOdcTarget" transform="translate({{ $xTargetOdc }}, {{ $targetOdcCenterY - 65 }})"
                               class="cursor-pointer" filter="url(#glowTargetOdc)" wire:click="selectNode('odc-{{ $odc->id }}')">
                                <rect width="{{ $wTargetOdc }}" height="135" rx="16" fill="url(#gradOdcTarget)" stroke="#fbbf24" stroke-width="2.8"/>
                                
                                <rect x="14" y="12" width="{{ $isCascadeOdc ? '145' : '135' }}" height="20" rx="5" fill="#92400e"/>
                                <text x="20" y="25" fill="#fef3c7" font-size="9.5" font-weight="900" font-family="sans-serif">
                                    {{ $isCascadeOdc ? 'SUB-ODC T-2 (TARGET)' : 'ODC KABINET (TARGET)' }}
                                </text>

                                <circle cx="{{ $wTargetOdc - 20 }}" cy="22" r="4.5" fill="#fbbf24"/>

                                <text x="14" y="52" fill="#ffffff" font-size="15.5" font-weight="900" font-family="sans-serif" letter-spacing="0.5">{{ Str::limit($odc->name, 17) }}</text>
                                <text x="14" y="69" fill="#fde68a" font-size="10.5" font-family="monospace">{{ $odc->code }} • {{ $odcCap }} Port Kapasitas</text>

                                <rect x="12" y="78" width="{{ $wTargetOdc - 24 }}" height="32" rx="7" fill="#1c1917" stroke="#78350f" stroke-width="1.1"/>
                                <text x="18" y="92" fill="#fbbf24" font-size="10" font-weight="bold" font-family="sans-serif">Splitter Tray {{ $odcSplitterRatio }} (-{{ $odcLoss }} dB)</text>
                                <text x="18" y="104" fill="#fef3c7" font-size="9" font-family="monospace">Est. Port Out: ~ {{ $isCascadeOdc ? '-7.3' : '+0.4' }} dBm</text>

                                <text x="14" y="122" fill="#fef08a" font-size="9.5" font-weight="bold" font-family="sans-serif">
                                    {{ $this->summary['used_ports'] }}/{{ $this->summary['total_ports'] }} Port Digunakan ({{ round(($this->summary['used_ports'] / max(1, $this->summary['total_ports'])) * 100) }}%)
                                </text>

                                <rect x="14" y="126" width="{{ $wTargetOdc - 28 }}" height="5" rx="2.5" fill="#451a03"/>
                                <rect x="14" y="126" width="{{ max(6, ($wTargetOdc - 28) * ($this->summary['used_ports'] / max(1, $this->summary['total_ports']))) }}" height="5" rx="2.5" fill="#f59e0b"/>

                                {{-- Connector socket right side --}}
                                <rect x="{{ $wTargetOdc - 5 }}" y="58" width="8" height="20" rx="3" fill="#b45309" stroke="#fbbf24" stroke-width="1.3"/>
                            </g>

                            {{-- Stage 4 Header --}}
                            <g transform="translate({{ $xOdp }}, 40)">
                                <rect width="215" height="26" rx="6" fill="#0f172a" stroke="#10b981" stroke-width="1.2"/>
                                <circle cx="16" cy="13" r="4" fill="#34d399"/>
                                <text x="28" y="17" fill="#34d399" font-size="10" font-weight="900" font-family="sans-serif" letter-spacing="0.5">4. TITIK DISTRIBUSI (ODP)</text>
                            </g>

                            {{-- Stage 5 Header --}}
                            <g transform="translate({{ $xOnu }}, 40)">
                                <rect width="260" height="26" rx="6" fill="#0f172a" stroke="#8b5cf6" stroke-width="1.2"/>
                                <circle cx="16" cy="13" r="4" fill="#c084fc"/>
                                <text x="28" y="17" fill="#c084fc" font-size="10" font-weight="900" font-family="sans-serif" letter-spacing="0.5">5. TERMINASI PELANGGAN (ONT/CPE)</text>
                            </g>

                            {{-- ============================================================== --}}
                            {{-- 1. STAGE 1: OLT CORE NODE                                      --}}
                            {{-- ============================================================== --}}
                            <g id="nodeOlt" transform="translate({{ $xOlt }}, {{ $targetOdcCenterY - 70 }})"
                               class="cursor-pointer" filter="url(#odcNodeShadow)"
                               @if($olt) onclick="window.open('/isp/olts/{{ $olt->id }}', '_blank')" @endif>
                                <rect width="{{ $wOlt }}" height="140" rx="16" fill="url(#gradOdcOlt)" stroke="#38bdf8" stroke-width="2.5"/>
                                <rect x="14" y="14" width="105" height="20" rx="5" fill="#0369a1"/>
                                <text x="22" y="28" fill="#e0f2fe" font-size="10" font-weight="bold" font-family="sans-serif">CORE OLT GPON</text>

                                <circle cx="{{ $wOlt - 22 }}" cy="24" r="5" fill="#34d399"/>
                                <text x="{{ $wOlt - 32 }}" y="28" fill="#34d399" font-size="10" font-weight="bold" font-family="monospace" text-anchor="end">ONLINE</text>

                                <text x="14" y="58" fill="#ffffff" font-size="16" font-weight="900" font-family="sans-serif">{{ Str::limit($oltName, 17) }}</text>
                                <text x="14" y="78" fill="#94a3b8" font-size="11" font-family="monospace">IP: {{ $oltIp }} • {{ Str::limit($oltModel, 14) }}</text>

                                <rect x="12" y="90" width="{{ $wOlt - 24 }}" height="38" rx="8" fill="#081b33" stroke="#1e3a8a" stroke-width="1.2"/>
                                <text x="20" y="106" fill="#38bdf8" font-size="11" font-weight="bold" font-family="monospace">SFP Tx: +{{ $sfpTx }} dBm (Class C++)</text>
                                <text x="20" y="120" fill="#94a3b8" font-size="9.5" font-family="sans-serif">1490nm Tx / 1310nm Rx • PON Port 1</text>

                                {{-- Connector socket right side --}}
                                <rect x="{{ $wOlt - 6 }}" y="58" width="10" height="24" rx="3" fill="#0284c7" stroke="#38bdf8" stroke-width="1.5"/>
                            </g>

                            @if($isCascadeOdc)
                                {{-- Feeder Line: OLT -> Parent ODC --}}
                                <path d="M {{ $xOlt + $wOlt }} {{ $targetOdcCenterY }} L {{ $xTier1 }} {{ $targetOdcCenterY }}" stroke="#0284c7" stroke-width="7" opacity="0.35"/>
                                <path d="M {{ $xOlt + $wOlt }} {{ $targetOdcCenterY }} L {{ $xTier1 }} {{ $targetOdcCenterY }}" stroke="#38bdf8" stroke-width="3" stroke-dasharray="8,5"/>

                                @php $midFeeder = ($xOlt + $wOlt + $xTier1) / 2; @endphp
                                <g transform="translate({{ $midFeeder - 55 }}, {{ $targetOdcCenterY - 14 }})">
                                    <rect width="110" height="28" rx="7" fill="#090d16" stroke="#0284c7" stroke-width="1.5"/>
                                    <text x="55" y="18" fill="#bae6fd" font-size="10" font-weight="bold" font-family="monospace" text-anchor="middle">Feeder FO</text>
                                </g>

                                {{-- Node Induk ODC (Tier-1) --}}
                                <g id="nodeParentOdc" transform="translate({{ $xTier1 }}, {{ $targetOdcCenterY - 75 }})"
                                   class="cursor-pointer" filter="url(#odcNodeShadow)"
                                   onclick="window.open('/isp/odcs/{{ $parentOdc->id }}', '_blank')">
                                    <rect width="{{ $wTier }}" height="150" rx="16" fill="url(#gradOdcParent)" stroke="#a5b4fc" stroke-width="2.5"/>
                                    <rect x="14" y="14" width="120" height="20" rx="5" fill="#3730a3"/>
                                    <text x="22" y="28" fill="#e0e7ff" font-size="10" font-weight="bold" font-family="sans-serif">INDUK ODC (T-1)</text>

                                    <text x="14" y="58" fill="#ffffff" font-size="16" font-weight="900" font-family="sans-serif">{{ Str::limit($parentOdc->name, 17) }}</text>
                                    <text x="14" y="78" fill="#c7d2fe" font-size="11" font-family="monospace">{{ $parentOdc->code }}</text>

                                    <rect x="12" y="90" width="{{ $wTier - 24 }}" height="46" rx="8" fill="#1e1b4b" stroke="#4338ca" stroke-width="1.2"/>
                                    <text x="20" y="108" fill="#a5b4fc" font-size="11" font-weight="bold" font-family="sans-serif">PLC Splitter 1:4 (-7.2 dB)</text>
                                    <text x="20" y="125" fill="#c7d2fe" font-size="10" font-family="monospace">Est. Out: ~ +0.4 dBm</text>
                                </g>

                                {{-- Sub-Feeder Line: Induk ODC -> Sub ODC Target --}}
                                <path d="M {{ $xTier1 + $wTier }} {{ $targetOdcCenterY }} L {{ $xTargetOdc }} {{ $targetOdcCenterY }}" stroke="#4f46e5" stroke-width="7" opacity="0.35"/>
                                <path d="M {{ $xTier1 + $wTier }} {{ $targetOdcCenterY }} L {{ $xTargetOdc }} {{ $targetOdcCenterY }}" stroke="#818cf8" stroke-width="3"/>

                                @php $midSubFeeder = ($xTier1 + $wTier + $xTargetOdc) / 2; @endphp
                                <g transform="translate({{ $midSubFeeder - 55 }}, {{ $targetOdcCenterY - 14 }})">
                                    <rect width="110" height="28" rx="7" fill="#090d16" stroke="#6366f1" stroke-width="1.5"/>
                                    <text x="55" y="18" fill="#c7d2fe" font-size="10" font-weight="bold" font-family="monospace" text-anchor="middle">Sub-Feeder</text>
                                </g>
                            @else
                                {{-- Standard Feeder Line: OLT -> Target ODC --}}
                                <path d="M {{ $xOlt + $wOlt }} {{ $targetOdcCenterY }} L {{ $xTargetOdc }} {{ $targetOdcCenterY }}" stroke="#0284c7" stroke-width="7" opacity="0.35"/>
                                <path d="M {{ $xOlt + $wOlt }} {{ $targetOdcCenterY }} L {{ $xTargetOdc }} {{ $targetOdcCenterY }}" stroke="#38bdf8" stroke-width="3" stroke-dasharray="8,5"/>

                                @php $midFeeder = ($xOlt + $wOlt + $xTargetOdc) / 2; @endphp
                                <g transform="translate({{ $midFeeder - 55 }}, {{ $targetOdcCenterY - 14 }})">
                                    <rect width="110" height="28" rx="7" fill="#090d16" stroke="#0284c7" stroke-width="1.5"/>
                                    <text x="55" y="18" fill="#bae6fd" font-size="10" font-weight="bold" font-family="monospace" text-anchor="middle">Feeder FO</text>
                                </g>
                            @endif

                            {{-- ============================================================== --}}
                            {{-- 2. STAGE 3: TARGET ODC HERO NODE                               --}}
                            {{-- ============================================================== --}}
                            <g id="nodeOdcTarget" transform="translate({{ $xTargetOdc }}, {{ $targetOdcCenterY - 85 }})"
                               class="cursor-pointer" filter="url(#glowTargetOdc)" wire:click="selectNode('odc-{{ $odc->id }}')">
                                <rect width="{{ $wTargetOdc }}" height="170" rx="18" fill="url(#gradOdcTarget)" stroke="#fbbf24" stroke-width="3.5"/>
                                
                                <rect x="16" y="14" width="{{ $isCascadeOdc ? '155' : '145' }}" height="22" rx="6" fill="#92400e"/>
                                <text x="24" y="29" fill="#fef3c7" font-size="10.5" font-weight="900" font-family="sans-serif">
                                    {{ $isCascadeOdc ? 'SUB-ODC T-2 (TARGET)' : 'ODC KABINET (TARGET)' }}
                                </text>

                                <circle cx="{{ $wTargetOdc - 24 }}" cy="25" r="5" fill="#fbbf24"/>

                                <text x="16" y="62" fill="#ffffff" font-size="18" font-weight="900" font-family="sans-serif" letter-spacing="0.5">{{ Str::limit($odc->name, 18) }}</text>
                                <text x="16" y="82" fill="#fde68a" font-size="12" font-family="monospace">{{ $odc->code }} • {{ $odcCap }} Port Kapasitas</text>

                                <rect x="14" y="92" width="{{ $wTargetOdc - 28 }}" height="42" rx="8" fill="#1c1917" stroke="#78350f" stroke-width="1.3"/>
                                <text x="22" y="108" fill="#fbbf24" font-size="11.5" font-weight="bold" font-family="sans-serif">Splitter Tray {{ $odcSplitterRatio }} (-{{ $odcLoss }} dB)</text>
                                <text x="22" y="123" fill="#fef3c7" font-size="10.5" font-family="monospace">Est. Port Out: ~ {{ $isCascadeOdc ? '-7.3' : '+0.4' }} dBm</text>

                                <text x="16" y="148" fill="#fef08a" font-size="10.5" font-weight="bold" font-family="sans-serif">
                                    {{ $this->summary['used_ports'] }}/{{ $this->summary['total_ports'] }} Port Digunakan ({{ round(($this->summary['used_ports'] / max(1, $this->summary['total_ports'])) * 100) }}%)
                                </text>

                                <rect x="14" y="154" width="{{ $wTargetOdc - 28 }}" height="7" rx="3.5" fill="#451a03"/>
                                <rect x="14" y="154" width="{{ max(8, ($wTargetOdc - 28) * ($this->summary['used_ports'] / max(1, $this->summary['total_ports']))) }}" height="7" rx="3.5" fill="#f59e0b"/>

                                {{-- Connector socket right side --}}
                                <rect x="{{ $wTargetOdc - 6 }}" y="73" width="10" height="24" rx="3" fill="#b45309" stroke="#fbbf24" stroke-width="1.5"/>
                            </g>

                            {{-- ============================================================== --}}
                            {{-- 3 & 4. STAGES 4 & 5: ODPS, SUB-ODCS & SUBSCRIBERS              --}}
                            {{-- ============================================================== --}}
                            @if(!empty($odpLayouts))
                                @foreach($odpLayouts as $layout)
                                    @php
                                        $isOdp = $layout['type'] === 'odp';
                                        $item = $layout['model'];
                                        $centerY = $layout['centerY'];
                                        $isTraced = false;

                                        if ($isOdp) {
                                            $isTraced = ($tracedNodeId === 'odp-' . $item->id) ||
                                                        (!empty($qSearch) && (str_contains(strtolower($item->name), $qSearch) || str_contains(strtolower($item->code), $qSearch)));
                                        } else {
                                            $isTraced = ($tracedNodeId === 'odc-' . $item->id) ||
                                                        (!empty($qSearch) && (str_contains(strtolower($item->name), $qSearch) || str_contains(strtolower($item->code), $qSearch)));
                                        }

                                        $lineStroke = $isTraced ? '#00e5ff' : ($isOdp ? '#10b981' : '#a855f7');
                                        $lineWidth = $isTraced ? 3.5 : 2.5;
                                    @endphp

                                    {{-- Curved Fiber Line from ODC to ODP / Sub-ODC --}}
                                    <path d="M {{ $xTargetOdc + $wTargetOdc }} {{ $targetOdcCenterY }} C {{ $xTargetOdc + $wTargetOdc + 65 }} {{ $targetOdcCenterY }}, {{ $xOdp - 50 }} {{ $centerY }}, {{ $xOdp }} {{ $centerY }}"
                                          stroke="{{ $lineStroke }}" stroke-width="{{ $lineWidth }}" fill="none" opacity="0.9"/>
                                    <circle cx="{{ $xOdp - 8 }}" cy="{{ $centerY }}" r="4" fill="{{ $lineStroke }}"/>

                                    @if($isOdp)
                                        {{-- ODP NODE CARD --}}
                                        @php
                                            $odpRatio = $item->port_count ?: 8;
                                            $odpLoss = match((int) $odpRatio) {
                                                2 => '3.8',
                                                4 => '7.2',
                                                8 => '10.5',
                                                16 => '13.8',
                                                default => '10.5'
                                            };
                                            $odpUsed = $layout['onus']->count();
                                            $isOdpCascade = !empty($item->parent_odp_id);
                                        @endphp
                                        <g id="nodeOdp_{{ $item->id }}" transform="translate({{ $xOdp }}, {{ $centerY - 58 }})"
                                           class="cursor-pointer" filter="url(#odcNodeShadow)"
                                           onclick="window.open('/isp/odps/{{ $item->id }}', '_blank')">
                                            <rect width="{{ $wOdp }}" height="116" rx="16" fill="url(#gradOdcOdp)"
                                                  stroke="{{ $isTraced ? '#00e5ff' : '#34d399' }}" stroke-width="{{ $isTraced ? '3' : '2' }}"/>
                                            <rect x="14" y="12" width="{{ $isOdpCascade ? '110' : '100' }}" height="18" rx="5" fill="#047857"/>
                                            <text x="20" y="25" fill="#a7f3d0" font-size="9.5" font-weight="bold" font-family="sans-serif">
                                                {{ $isOdpCascade ? 'ODP KASKADE' : 'ODP DISTRIBUSI' }}
                                            </text>
                                            <text x="14" y="52" fill="#ffffff" font-size="15" font-weight="bold" font-family="sans-serif">{{ Str::limit($item->name, 17) }}</text>
                                            <text x="14" y="70" fill="#d1fae5" font-size="11" font-family="monospace">{{ $item->code }} • Port {{ $item->odc_port ?: 'Dist' }}</text>
                                            <text x="14" y="87" fill="#a7f3d0" font-size="10" font-family="sans-serif">Splitter 1:{{ $odpRatio }} (-{{ $odpLoss }} dB)</text>
                                            <text x="14" y="103" fill="#6ee7b7" font-size="9.5" font-weight="bold" font-family="sans-serif">{{ $odpUsed }}/{{ $odpRatio }} Port Digunakan</text>

                                            {{-- Socket right side --}}
                                            <rect x="{{ $wOdp - 6 }}" y="46" width="10" height="24" rx="3" fill="#047857" stroke="#34d399" stroke-width="1.5"/>
                                        </g>

                                        {{-- CONNECTED ONUS / CUSTOMERS --}}
                                        @if($layout['onus']->isNotEmpty())
                                            @foreach($layout['onus'] as $onuIdx => $onu)
                                                @php
                                                    $onuY = $layout['topY'] + ($onuIdx * 76) + 10;
                                                    $rxVal = $onu->rx_power_dbm !== null ? (float) $onu->rx_power_dbm : null;
                                                    $custName = $onu->customerService?->customer?->name ?? 'Pelanggan FTTH';
                                                    $custUser = $onu->customerService?->username ?? $onu->serial_number;

                                                    $isThisOnuTraced = ($tracedNodeId === 'onu-' . $onu->id) ||
                                                                       (!empty($qSearch) && (str_contains(strtolower($custName), $qSearch) || str_contains(strtolower($onu->serial_number), $qSearch) || str_contains(strtolower($custUser), $qSearch)));

                                                    // Status color by Rx optical power
                                                    $dotColor = '#10b981';
                                                    $statusText = 'IDEAL';
                                                    if ($rxVal !== null) {
                                                        if ($rxVal >= -24.0) {
                                                            $dotColor = '#10b981';
                                                            $statusText = 'IDEAL';
                                                        } elseif ($rxVal >= -27.0) {
                                                            $dotColor = '#f59e0b';
                                                            $statusText = 'MARGINAL';
                                                        } else {
                                                            $dotColor = '#ef4444';
                                                            $statusText = 'REDAMAN TINGGI';
                                                        }
                                                    } else {
                                                        $dotColor = ($onu->operational_status === 'online') ? '#10b981' : '#64748b';
                                                        $statusText = strtoupper($onu->operational_status ?: 'STANDBY');
                                                    }
                                                @endphp

                                                {{-- Curve from ODP output to ONU input --}}
                                                <path d="M {{ $xOdp + $wOdp }} {{ $centerY }} C {{ $xOdp + $wOdp + 45 }} {{ $centerY }}, {{ $xOnu - 40 }} {{ $onuY + 32 }}, {{ $xOnu }} {{ $onuY + 32 }}"
                                                      stroke="{{ $isThisOnuTraced ? '#00e5ff' : '#10b981' }}" stroke-width="{{ $isThisOnuTraced ? '3' : '2' }}" fill="none" opacity="0.85"/>
                                                <circle cx="{{ $xOnu - 6 }}" cy="{{ $onuY + 32 }}" r="3" fill="{{ $isThisOnuTraced ? '#00e5ff' : '#10b981' }}"/>

                                                {{-- ONU / Customer Node Card --}}
                                                <g id="nodeOnu_{{ $onu->id }}" transform="translate({{ $xOnu }}, {{ $onuY }})"
                                                   class="cursor-pointer" filter="url(#odcNodeShadow)"
                                                   wire:click="selectNode('onu-{{ $onu->id }}')">
                                                    <rect width="{{ $wOnu }}" height="64" rx="12" fill="#0f172a"
                                                          stroke="{{ $isThisOnuTraced ? '#00e5ff' : $dotColor }}" stroke-width="{{ $isThisOnuTraced ? '2.5' : '1.8' }}"/>
                                                    
                                                    {{-- Left Accent Strip --}}
                                                    <rect x="0" y="0" width="5" height="64" rx="2.5" fill="{{ $dotColor }}"/>

                                                    {{-- Row 1: Customer Name & Service Pill --}}
                                                    <text x="16" y="22" fill="#ffffff" font-size="13.5" font-weight="bold" font-family="sans-serif">
                                                        {{ Str::limit($custName, 20) }}
                                                    </text>

                                                    <rect x="{{ $wOnu - 75 }}" y="8" width="65" height="18" rx="5" fill="#312e81"/>
                                                    <text x="{{ $wOnu - 42 }}" y="21" fill="#c7d2fe" font-size="9.5" font-weight="bold" font-family="monospace" text-anchor="middle">
                                                        {{ $onu->customerService?->service_type ?? 'PPPoE' }}
                                                    </text>

                                                    {{-- Row 2: ONT Serial Number & Username --}}
                                                    <text x="16" y="39" fill="#94a3b8" font-size="10.5" font-family="monospace">
                                                        ONT: {{ Str::limit($onu->serial_number, 14) }} • {{ Str::limit($custUser, 12) }}
                                                    </text>

                                                    {{-- Row 3: Optical Telemetry & Rx Status --}}
                                                    <text x="16" y="55" fill="{{ $dotColor }}" font-size="11" font-weight="900" font-family="monospace">
                                                        Rx: {{ $rxVal !== null ? number_format($rxVal, 2) . ' dBm' : '— dBm' }}
                                                    </text>

                                                    <rect x="135" y="44" width="70" height="15" rx="4" fill="{{ $dotColor }}"/>
                                                    <text x="170" y="55" fill="#ffffff" font-size="8.5" font-weight="bold" font-family="monospace" text-anchor="middle">
                                                        {{ $statusText }}
                                                    </text>

                                                    <circle cx="{{ $wOnu - 45 }}" cy="51" r="3.5" fill="#10b981"/>
                                                    <text x="{{ $wOnu - 36 }}" y="55" fill="#34d399" font-size="9.5" font-family="sans-serif">Online</text>
                                                </g>
                                            @endforeach
                                        @else
                                            {{-- Empty port placeholder --}}
                                            <path d="M {{ $xOdp + $wOdp }} {{ $centerY }} L {{ $xOnu }} {{ $centerY }}"
                                                  stroke="#334155" stroke-width="1.5" stroke-dasharray="4,4" fill="none"/>
                                            <g transform="translate({{ $xOnu }}, {{ $centerY - 25 }})" class="cursor-pointer">
                                                <rect width="280" height="50" rx="10" fill="#090d16" stroke="#334155" stroke-width="1.5" stroke-dasharray="5,4"/>
                                                <circle cx="22" cy="25" r="9" fill="#1e293b"/>
                                                <text x="22" y="29" fill="#64748b" font-size="13" font-weight="bold" text-anchor="middle">+</text>
                                                <text x="40" y="23" fill="#94a3b8" font-size="11" font-weight="bold" font-family="sans-serif">Port Kosong / Siap PSB</text>
                                                <text x="40" y="37" fill="#64748b" font-size="9.5" font-family="sans-serif">Belum ada pelanggan terhubung</text>
                                            </g>
                                        @endif

                                    @else
                                        {{-- SUB-ODC NODE CARD --}}
                                        <g id="nodeSubOdc_{{ $item->id }}" transform="translate({{ $xOdp }}, {{ $centerY - 58 }})"
                                           class="cursor-pointer" filter="url(#odcNodeShadow)"
                                           onclick="window.open('/isp/odcs/{{ $item->id }}', '_blank')">
                                            <rect width="{{ $wOdp }}" height="116" rx="16" fill="url(#gradOdcSub)"
                                                  stroke="{{ $isTraced ? '#00e5ff' : '#c084fc' }}" stroke-width="{{ $isTraced ? '3' : '2' }}"/>
                                            <rect x="14" y="12" width="115" height="18" rx="5" fill="#581c87"/>
                                            <text x="20" y="25" fill="#f3e8ff" font-size="9.5" font-weight="bold" font-family="sans-serif">SUB-ODC KASKADE</text>
                                            <text x="14" y="52" fill="#ffffff" font-size="15" font-weight="bold" font-family="sans-serif">{{ Str::limit($item->name, 17) }}</text>
                                            <text x="14" y="70" fill="#f3e8ff" font-size="11" font-family="monospace">{{ $item->code }} • {{ $item->port_count ?: 24 }} Port</text>
                                            <text x="14" y="87" fill="#e9d5ff" font-size="10" font-family="sans-serif">Splitter Kaskade (-10.5 dB)</text>
                                            <text x="14" y="103" fill="#d8b4fe" font-size="9.5" font-weight="bold" font-family="sans-serif">{{ $item->odps->count() }} ODP Terhubung</text>
                                        </g>
                                    @endif
                                @endforeach
                            @else
                                {{-- Empty state: No ODPs connected --}}
                                <g transform="translate({{ $xTargetOdc + $wTargetOdc + 60 }}, {{ $targetOdcCenterY - 45 }})">
                                    <rect width="320" height="90" rx="14" fill="#0f172a" stroke="#334155" stroke-dasharray="6,4"/>
                                    <text x="24" y="35" fill="#94a3b8" font-size="13" font-weight="bold" font-family="sans-serif">Belum Ada ODP Terhubung</text>
                                    <text x="24" y="55" fill="#64748b" font-size="11" font-family="sans-serif">Hubungkan ODP ke port ODC ini via tab</text>
                                    <text x="24" y="72" fill="#38bdf8" font-size="11" font-weight="bold" font-family="sans-serif">Port Management atau menu Data ODP</text>
                                </g>
                            @endif
                        </g>
                    </svg>

                    {{-- Status Legend (Bottom Left Floating HUD) --}}
                    <div class="absolute bottom-3 left-3 z-10 bg-slate-900/90 backdrop-blur-md border border-slate-800 rounded-xl p-2.5 text-[11px] text-slate-300 flex flex-wrap items-center gap-3 shadow-lg pointer-events-none sm:pointer-events-auto">
                        <span class="font-bold text-slate-400 uppercase text-[10px] tracking-wider">Status Rx:</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Ideal (&gt;= -24 dBm)</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Marginal (-24 s/d -27 dBm)</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Redaman Tinggi (&lt; -27 dBm)</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span> Tracing</span>
                    </div>

                    {{-- Optical Link Budget Guide (Top Right Floating HUD) --}}
                    <div class="hidden xl:flex absolute top-3 right-3 z-10 bg-slate-900/85 backdrop-blur-md border border-slate-800 rounded-xl px-3 py-1.5 text-[10.5px] text-slate-300 items-center gap-2 shadow-lg font-mono">
                        <span class="text-amber-400 font-bold">⚡ Link Budget:</span>
                        <span>OLT (+{{ $sfpTx }})</span>
                        <span class="text-slate-500">➔</span>
                        <span>Feeder (-{{ $feederLoss }})</span>
                        <span class="text-slate-500">➔</span>
                        <span>ODC (-{{ $odcLossVal }})</span>
                        <span class="text-slate-500">➔</span>
                        <span>ODP (-{{ $odpLossVal }})</span>
                        <span class="text-slate-500">=</span>
                        <span class="text-emerald-300 font-bold">Est. ONT: ~{{ $estOntPower }} dBm</span>
                    </div>

                    {{-- Tracing Active Banner --}}
                    @if($tracedNodeId || $traceQuery)
                        <div class="absolute top-3 left-3 z-10 bg-cyan-950/90 border border-cyan-800 text-cyan-200 px-3.5 py-1.5 rounded-xl text-xs flex items-center gap-2 shadow-lg">
                            <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                            <span class="font-mono">Active Tracing: {{ $traceQuery }}</span>
                            <button type="button" wire:click="clearTrace" class="ml-1 text-cyan-400 hover:text-white font-bold cursor-pointer">&times;</button>
                        </div>
                    @endif
                </div>

                {{-- Status Legend (Bottom Left) --}}
                <div class="absolute bottom-3 left-3 z-10 bg-slate-900/90 backdrop-blur-md border border-slate-800 rounded-xl p-2.5 text-[11px] text-slate-300 flex flex-wrap items-center gap-3 shadow-lg pointer-events-none sm:pointer-events-auto">
                    <span class="font-bold text-slate-400 uppercase text-[10px] tracking-wider">Status:</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Ideal (&gt;= -24 dBm)</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Marginal (&gt;= -27 dBm)</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Redaman Tinggi / Drop</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span> Jalur Tracing</span>
                </div>

                {{-- Tracing Active Banner --}}
                @if($tracedNodeId || $traceQuery)
                    <div class="absolute top-3 left-3 z-10 bg-cyan-950/90 border border-cyan-800 text-cyan-200 px-3.5 py-1.5 rounded-xl text-xs flex items-center gap-2 shadow-lg">
                        <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                        <span class="font-mono">Active Tracing: {{ $traceQuery }}</span>
                        <button type="button" wire:click="clearTrace" class="ml-1 text-cyan-400 hover:text-white font-bold cursor-pointer">&times;</button>
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

    {{-- ============================================================
         MODAL 1: PASANG SPLITTER BARU (PLC / FBT)
         ============================================================ --}}
    @if($showSplitterModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-xs p-4 animate-in fade-in duration-150">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 max-w-lg w-full shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-purple-600">call_split</span>
                        Pasang Splitter Baru pada ODC
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
                                    <option value="2:4">2:4 (Redundant: 4 Port)</option>
                                    <option value="2:8">2:8 (Redundant: 8 Port)</option>
                                    <option value="2:16">2:16 (Redundant: 16 Port)</option>
                                @else
                                    <option value="50:50">50:50 (Balanced Tap)</option>
                                    <option value="70:30">70:30 (Trunk 70% / Branch 30%)</option>
                                    <option value="80:20">80:20 (Trunk 80% / Branch 20%)</option>
                                    <option value="85:15">85:15 (Trunk 85% / Branch 15%)</option>
                                    <option value="90:10">90:10 (Trunk 90% / Drop 10%)</option>
                                    <option value="60:40">60:40 (Trunk 60% / Branch 40%)</option>
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
                            <option value="tray">Tray / Cassette Module</option>
                            <option value="cassette">ABS Cassette Box</option>
                            <option value="mini_tube">Steel Tube / Mini Tube</option>
                            <option value="rack_mount">Rack Mount 1U Tray</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan / Keterangan</label>
                        <textarea wire:model="splitterDescription" rows="2" placeholder="Contoh: Splitter Tray 1 untuk distribusi Cluster Mawar"
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
         MODAL 2: KONFIGURASI PORT SPLITTER (ALOKASI OUTPUT)
         ============================================================ --}}
    @if($showSplitterPortModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-xs p-4 animate-in fade-in duration-150">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-purple-600">settings_input_component</span>
                        Konfigurasi Output Port #{{ $selectedSplitterPort?->port_number }} ({{ $selectedSplitterPort?->splitter?->name }})
                    </h3>
                    <button type="button" wire:click="closeSplitterPortModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>

                <div class="space-y-3.5 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Port</label>
                        <select wire:model.live="portFormStatus"
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-800 dark:text-slate-100 font-semibold">
                            <option value="available">Available (Port Kosong / Siap)</option>
                            <option value="used">Used (Terhubung ke Downstream)</option>
                            <option value="reserved">Reserved (Dicadangkan)</option>
                            <option value="fault">Fault (Gangguan / Rusak)</option>
                        </select>
                    </div>

                    @if($portFormStatus === 'used')
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tipe Target</label>
                                <select wire:model.live="portFormTargetType"
                                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-800 dark:text-slate-100">
                                    <option value="odp">ODP (Optical Distribution Point)</option>
                                    <option value="closure">Joint Closure / Sambungan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Target</label>
                                @if($portFormTargetType === 'odp')
                                    <select wire:model="portFormTargetId"
                                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-800 dark:text-slate-100">
                                        <option value="">— Pilih ODP —</option>
                                        @foreach($this->availableOdps as $odpItem)
                                            <option value="{{ $odpItem->id }}">{{ $odpItem->code }} — {{ $odpItem->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <select wire:model="portFormTargetId"
                                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-800 dark:text-slate-100">
                                        <option value="">— Pilih Closure —</option>
                                        @foreach($this->availableClosures as $clItem)
                                            <option value="{{ $clItem->id }}">{{ $clItem->code }} — {{ $clItem->name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Warna Core (TIA-598)</label>
                            <select wire:model="portFormCoreColor"
                                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-800 dark:text-slate-100">
                                <option value="">— Pilih Warna —</option>
                                @foreach(\App\Models\ISP\SplitterPort::TIA_598_COLORS as $cIdx => $cName)
                                    <option value="{{ $cName }}">Core #{{ $cIdx }}: {{ $cName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Redaman Port (dB)</label>
                            <input type="number" step="0.01" wire:model="portFormAttenuation"
                                   placeholder="Contoh: 10.50"
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 font-mono text-slate-800 dark:text-slate-100">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan</label>
                        <input type="text" wire:model="portFormNotes" placeholder="Catatan kabel drop, arah jalur, atau teknisi"
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
            container: document.getElementById('odcTopologyContainer'),
            viewport: document.getElementById('odcTopologyViewport'),
            wrapper: document.getElementById('odc-topology-wrapper'),
            svg: document.getElementById('odcTopologySvg')
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
                scale = Math.min(1.35, Math.max(0.60, fitScale));

                // If diagram is wider than container at readable scale, center on Hero ODC
                if (scale * bbox.width > cW) {
                    const nodeHero = document.getElementById('nodeOdcTarget');
                    if (nodeHero) {
                        const heroBbox = nodeHero.getBBox();
                        panX = (cW * 0.40) - ((heroBbox.x + heroBbox.width / 2) * scale);
                    } else {
                        panX = (cW - (bbox.width * scale)) / 2 - (bbox.x * scale);
                    }
                } else {
                    panX = (cW - (bbox.width * scale)) / 2 - (bbox.x * scale);
                }
                panY = (cH - (bbox.height * scale)) / 2 - (bbox.y * scale);
                updateTransform();
            }
        } catch (err) {}
    }

    function initOdcTopology() {
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
        document.getElementById('btnOdcZoomIn')?.addEventListener('click', (e) => {
            e.stopPropagation();
            scale = Math.min(2.5, scale + 0.15);
            updateTransform();
        });

        // Zoom Out
        document.getElementById('btnOdcZoomOut')?.addEventListener('click', (e) => {
            e.stopPropagation();
            scale = Math.max(0.3, scale - 0.15);
            updateTransform();
        });

        // Reset (1:1)
        document.getElementById('btnOdcReset')?.addEventListener('click', (e) => {
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
        document.getElementById('btnOdcFit')?.addEventListener('click', (e) => {
            e.stopPropagation();
            fitToScreen();
        });

        // Double Click Container to Fit
        container.addEventListener('dblclick', (e) => {
            if (!e.target.closest('a, button, g[cursor="pointer"]')) {
                fitToScreen();
            }
        });

        // Fullscreen
        document.getElementById('btnOdcFullscreen')?.addEventListener('click', (e) => {
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

        // Listen for trace/focus event from Livewire
        window.addEventListener('topology-focus-node', (event) => {
            const nodeId = event.detail?.nodeId;
            if (!nodeId) return;

            let targetEl = null;
            if (nodeId.startsWith('odp-')) {
                const id = nodeId.replace('odp-', '');
                targetEl = document.getElementById('nodeOdp_' + id);
            } else if (nodeId.startsWith('onu-')) {
                const id = nodeId.replace('onu-', '');
                targetEl = document.getElementById('nodeOnu_' + id);
            } else if (nodeId.startsWith('odc-')) {
                targetEl = document.getElementById('nodeOdcTarget');
            } else if (nodeId.startsWith('olt-')) {
                targetEl = document.getElementById('nodeOlt');
            }

            if (targetEl) {
                try {
                    const bbox = targetEl.getBBox();
                    const { container } = getElements();
                    const cW = container?.clientWidth || 900;
                    const cH = container?.clientHeight || 620;
                    scale = 1.3;
                    panX = (cW / 2) - ((bbox.x + bbox.width / 2) * scale);
                    panY = (cH / 2) - ((bbox.y + bbox.height / 2) * scale);
                    updateTransform();
                } catch (err) {}
            }
        });

        window.addEventListener('topology-reset-focus', () => {
            fitToScreen();
        });
    }

    // Expose globally for Alpine and tab switches
    window.initOdcTopology = initOdcTopology;

    function bindOdcLivewireHooks() {
        if (window.Livewire && window.Livewire.hook) {
            window.Livewire.hook('commit', ({ succeed }) => {
                succeed(() => {
                    setTimeout(initOdcTopology, 50);
                });
            });
            window.Livewire.hook('morph.updated', () => {
                setTimeout(initOdcTopology, 50);
            });
        }
    }

    if (window.Livewire) {
        bindOdcLivewireHooks();
    } else {
        document.addEventListener('livewire:init', bindOdcLivewireHooks);
    }

    document.addEventListener('DOMContentLoaded', initOdcTopology);
    document.addEventListener('livewire:navigated', initOdcTopology);

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(initOdcTopology, 80);
    }
})();
</script>
