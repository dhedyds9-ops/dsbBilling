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
            #odc-topology-wrapper:fullscreen {
                background-color: #070b14 !important;
                width: 100vw !important;
                height: 100vh !important;
                display: flex !important;
                flex-direction: column !important;
                border-radius: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
            }
            #odc-topology-wrapper:fullscreen .odc-topo-toolbar {
                border-radius: 0 !important;
                border-left: none !important;
                border-right: none !important;
                border-top: none !important;
                flex-shrink: 0 !important;
            }
            #odc-topology-wrapper:fullscreen #odcTopologyContainer {
                flex: 1 1 0% !important;
                height: calc(100vh - 65px) !important;
                max-height: none !important;
                width: 100vw !important;
            }
        </style>
        <div class="space-y-4">
            <div id="odc-topology-wrapper" class="relative w-full rounded-2xl overflow-hidden border border-slate-800 bg-[#070b14] shadow-xl">
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
                $oltIp = $olt ? ($olt->ip_address ?: 'PON Uplink') : 'PON Uplink';
                $oltModel = $olt?->model ?: 'OLT Chassis';

                $odpList = $this->connectedOdps;
                $childOdcList = $this->childOdcs;

                // Hitung posisi vertikal secara dinamis
                $odpLayouts = [];
                $currentY = 40;
                $rowSpacing = 24;

                foreach ($odpList as $odpItem) {
                    $onuList = $odpItem->onus;
                    $onuCount = $onuList->count();
                    $boxHeight = max(110, $onuCount > 0 ? ($onuCount * 64 + 16) : 110);
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
                    $boxHeight = 110;
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

                $totalCanvasHeight = max(620, $currentY + 40);

                if (!empty($odpLayouts)) {
                    $firstY = $odpLayouts[0]['centerY'];
                    $lastY = $odpLayouts[count($odpLayouts) - 1]['centerY'];
                    $targetOdcCenterY = ($firstY + $lastY) / 2;
                } else {
                    $targetOdcCenterY = 300;
                }

                // Koordinat Horizontal X
                $xOlt = 30;
                $xTier1 = $isCascadeOdc ? 260 : null;
                $xTargetOdc = $isCascadeOdc ? 500 : 280;
                $xOdp = $isCascadeOdc ? 780 : 560;
                $xOnu = $isCascadeOdc ? 1050 : 830;

                // Redaman ODC Splitter
                $odcCap = $odc->port_count ?: 48;
                $odcSplitterRatio = $odcCap > 16 ? '1:8' : ($odcCap > 8 ? '1:4' : '1:2');
                $odcLoss = $odcCap > 16 ? '10.5' : ($odcCap > 8 ? '7.2' : '3.8');

                $qSearch = trim(strtolower($traceQuery));
            @endphp

                <div id="odcTopologyContainer"
                     class="w-full h-[680px] lg:h-[720px] cursor-grab active:cursor-grabbing select-none overflow-hidden relative">
                    <svg id="odcTopologySvg" class="w-full h-full" xmlns="http://www.w3.org/2000/svg" style="min-height: 680px;">
                        <defs>
                            <linearGradient id="gradOdcOlt" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#1e3a8a"/>
                                <stop offset="100%" stop-color="#3b82f6"/>
                            </linearGradient>
                            <linearGradient id="gradOdcParent" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#312e81"/>
                                <stop offset="100%" stop-color="#6366f1"/>
                            </linearGradient>
                            <linearGradient id="gradOdcTarget" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#78350f"/>
                                <stop offset="100%" stop-color="#f59e0b"/>
                            </linearGradient>
                            <linearGradient id="gradOdcOdp" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#064e3b"/>
                                <stop offset="100%" stop-color="#10b981"/>
                            </linearGradient>
                            <linearGradient id="gradOdcSub" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#4c1d95"/>
                                <stop offset="100%" stop-color="#8b5cf6"/>
                            </linearGradient>
                            <filter id="odcNodeShadow" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="4" stdDeviation="6" flood-color="#000" flood-opacity="0.6"/>
                            </filter>
                            <filter id="glowFilter" x="-30%" y="-30%" width="160%" height="160%">
                                <feGaussianBlur stdDeviation="4" result="blur"/>
                                <feComposite in="SourceGraphic" in2="blur" operator="over"/>
                            </filter>
                        </defs>

                        <g id="odcTopologyViewport" transform="matrix(1 0 0 1 30 30)">
                            {{-- 1. OLT CORE NODE --}}
                            <g id="nodeOlt" transform="translate({{ $xOlt }}, {{ $targetOdcCenterY - 48 }})"
                               class="cursor-pointer" filter="url(#odcNodeShadow)"
                               @if($olt) onclick="window.open('/isp/olts/{{ $olt->id }}', '_blank')" @endif>
                                <rect width="180" height="96" rx="14" fill="url(#gradOdcOlt)" stroke="#60a5fa" stroke-width="2"/>
                                <rect x="12" y="12" width="70" height="18" rx="5" fill="#1e40af"/>
                                <text x="18" y="25" fill="#bfdbfe" font-size="9" font-weight="bold" font-family="sans-serif">OLT GPON</text>
                                <text x="12" y="52" fill="#ffffff" font-size="13" font-weight="bold" font-family="sans-serif">{{ Str::limit($oltName, 16) }}</text>
                                <text x="12" y="70" fill="#bfdbfe" font-size="10" font-family="monospace">IP: {{ $oltIp }}</text>
                                <text x="12" y="86" fill="#93c5fd" font-size="9" font-family="sans-serif">SFP Tx: +8.0 dBm (C++)</text>
                            </g>

                            @if($isCascadeOdc)
                                {{-- LINK OLT -> PARENT ODC --}}
                                <path d="M {{ $xOlt + 180 }} {{ $targetOdcCenterY }} L {{ $xTier1 }} {{ $targetOdcCenterY }}"
                                      stroke="#3b82f6" stroke-width="3" stroke-dasharray="6,4" fill="none"/>
                                <circle cx="{{ ($xOlt + 180 + $xTier1) / 2 }}" cy="{{ $targetOdcCenterY }}" r="4" fill="#60a5fa"/>
                                <text x="{{ ($xOlt + 180 + $xTier1) / 2 - 25 }}" y="{{ $targetOdcCenterY - 10 }}" fill="#94a3b8" font-size="9" font-family="sans-serif">Feeder FO</text>

                                {{-- NODE INDUK ODC (TIER-1) --}}
                                <g id="nodeParentOdc" transform="translate({{ $xTier1 }}, {{ $targetOdcCenterY - 48 }})"
                                   class="cursor-pointer" filter="url(#odcNodeShadow)"
                                   onclick="window.open('/isp/odcs/{{ $parentOdc->id }}', '_blank')">
                                    <rect width="180" height="96" rx="14" fill="url(#gradOdcParent)" stroke="#a5b4fc" stroke-width="2"/>
                                    <rect x="12" y="12" width="85" height="18" rx="5" fill="#3730a3"/>
                                    <text x="18" y="25" fill="#e0e7ff" font-size="9" font-weight="bold" font-family="sans-serif">INDUK ODC (T-1)</text>
                                    <text x="12" y="52" fill="#ffffff" font-size="13" font-weight="bold" font-family="sans-serif">{{ Str::limit($parentOdc->name, 16) }}</text>
                                    <text x="12" y="70" fill="#c7d2fe" font-size="10" font-family="monospace">{{ $parentOdc->code }}</text>
                                    <text x="12" y="86" fill="#e0e7ff" font-size="9" font-family="sans-serif">Splitter 1:4 (-7.2 dB)</text>
                                </g>

                                {{-- LINK INDUK ODC -> SUB-ODC --}}
                                <path d="M {{ $xTier1 + 180 }} {{ $targetOdcCenterY }} L {{ $xTargetOdc }} {{ $targetOdcCenterY }}"
                                      stroke="#00e5ff" stroke-width="3" fill="none"/>
                                <circle cx="{{ ($xTier1 + 180 + $xTargetOdc) / 2 }}" cy="{{ $targetOdcCenterY }}" r="4" fill="#00e5ff"/>
                                <text x="{{ ($xTier1 + 180 + $xTargetOdc) / 2 - 28 }}" y="{{ $targetOdcCenterY - 10 }}" fill="#67e8f9" font-size="9" font-family="sans-serif">Sub-Feeder</text>
                            @else
                                {{-- STANDARD: LINK OLT -> ODC --}}
                                <path d="M {{ $xOlt + 180 }} {{ $targetOdcCenterY }} L {{ $xTargetOdc }} {{ $targetOdcCenterY }}"
                                      stroke="#3b82f6" stroke-width="3" stroke-dasharray="6,4" fill="none"/>
                                <circle cx="{{ ($xOlt + 180 + $xTargetOdc) / 2 }}" cy="{{ $targetOdcCenterY }}" r="4" fill="#60a5fa"/>
                                <text x="{{ ($xOlt + 180 + $xTargetOdc) / 2 - 25 }}" y="{{ $targetOdcCenterY - 10 }}" fill="#94a3b8" font-size="9" font-family="sans-serif">Feeder FO</text>
                            @endif

                            {{-- 2. TARGET ODC CABINET NODE --}}
                            <g id="nodeOdcTarget" transform="translate({{ $xTargetOdc }}, {{ $targetOdcCenterY - 58 }})"
                               class="cursor-pointer" filter="url(#odcNodeShadow)" wire:click="selectNode('odc-{{ $odc->id }}')">
                                <rect width="220" height="116" rx="16" fill="url(#gradOdcTarget)" stroke="#fbbf24" stroke-width="2.5"/>
                                <rect x="14" y="12" width="{{ $isCascadeOdc ? '110' : '90' }}" height="18" rx="5" fill="#92400e"/>
                                <text x="20" y="25" fill="#fef3c7" font-size="9" font-weight="bold" font-family="sans-serif">
                                    {{ $isCascadeOdc ? 'SUB-ODC T-2 (TARGET)' : 'ODC KABINET (TARGET)' }}
                                </text>
                                <text x="14" y="54" fill="#ffffff" font-size="14" font-weight="black" font-family="sans-serif">{{ Str::limit($odc->name, 17) }}</text>
                                <text x="14" y="73" fill="#fef3c7" font-size="10" font-family="monospace">{{ $odc->code }} • {{ $odcCap }} Port</text>
                                <text x="14" y="91" fill="#fde68a" font-size="9" font-family="sans-serif">Splitter Tray {{ $odcSplitterRatio }} (-{{ $odcLoss }} dB)</text>
                                <text x="14" y="106" fill="#fef08a" font-size="9" font-weight="bold" font-family="sans-serif">
                                    {{ $this->summary['used_ports'] }}/{{ $this->summary['total_ports'] }} Port Distribusi Digunakan
                                </text>
                            </g>

                            {{-- 3. DOWNSTREAM ODPS & SUB-ODCS --}}
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
                                        $lineWidth = $isTraced ? 3.5 : 2;
                                    @endphp

                                    {{-- Curved Fiber Line from ODC to ODP / Sub-ODC --}}
                                    <path d="M {{ $xTargetOdc + 220 }} {{ $targetOdcCenterY }} C {{ $xTargetOdc + 280 }} {{ $targetOdcCenterY }}, {{ $xOdp - 60 }} {{ $centerY }}, {{ $xOdp }} {{ $centerY }}"
                                          stroke="{{ $lineStroke }}" stroke-width="{{ $lineWidth }}" fill="none" opacity="0.85"/>
                                    <circle cx="{{ $xOdp - 6 }}" cy="{{ $centerY }}" r="3.5" fill="{{ $lineStroke }}"/>

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
                                        <g id="nodeOdp_{{ $item->id }}" transform="translate({{ $xOdp }}, {{ $centerY - 48 }})"
                                           class="cursor-pointer" filter="url(#odcNodeShadow)"
                                           onclick="window.open('/isp/odps/{{ $item->id }}', '_blank')">
                                            <rect width="210" height="96" rx="14" fill="url(#gradOdcOdp)"
                                                  stroke="{{ $isTraced ? '#00e5ff' : '#34d399' }}" stroke-width="{{ $isTraced ? '3' : '2' }}"/>
                                            <rect x="12" y="10" width="{{ $isOdpCascade ? '95' : '85' }}" height="17" rx="5" fill="#047857"/>
                                            <text x="18" y="22" fill="#a7f3d0" font-size="9" font-weight="bold" font-family="sans-serif">
                                                {{ $isOdpCascade ? 'ODP KASKADE' : 'ODP DISTRIBUSI' }}
                                            </text>
                                            <text x="12" y="47" fill="#ffffff" font-size="13" font-weight="bold" font-family="sans-serif">{{ Str::limit($item->name, 16) }}</text>
                                            <text x="12" y="65" fill="#d1fae5" font-size="10" font-family="monospace">{{ $item->code }} • Port {{ $item->odc_port ?: 'Dist' }}</text>
                                            <text x="12" y="81" fill="#a7f3d0" font-size="9" font-family="sans-serif">Splitter 1:{{ $odpRatio }} (-{{ $odpLoss }} dB)</text>
                                            <text x="12" y="93" fill="#6ee7b7" font-size="8.5" font-family="sans-serif">{{ $odpUsed }}/{{ $odpRatio }} Port Digunakan</text>
                                        </g>

                                        {{-- CONNECTED ONUS / CUSTOMERS --}}
                                        @if($layout['onus']->isNotEmpty())
                                            @foreach($layout['onus'] as $onuIdx => $onu)
                                                @php
                                                    $onuY = $layout['topY'] + ($onuIdx * 64) + 12;
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
                                                <path d="M {{ $xOdp + 210 }} {{ $centerY }} C {{ $xOdp + 245 }} {{ $centerY }}, {{ $xOnu - 30 }} {{ $onuY + 26 }}, {{ $xOnu }} {{ $onuY + 26 }}"
                                                      stroke="{{ $isThisOnuTraced ? '#00e5ff' : '#10b981' }}" stroke-width="{{ $isThisOnuTraced ? '3' : '1.5' }}" fill="none" opacity="0.8"/>

                                                {{-- ONU / Customer Node Card --}}
                                                <g id="nodeOnu_{{ $onu->id }}" transform="translate({{ $xOnu }}, {{ $onuY }})"
                                                   class="cursor-pointer" filter="url(#odcNodeShadow)"
                                                   wire:click="selectNode('onu-{{ $onu->id }}')">
                                                    <rect width="240" height="52" rx="10" fill="#1e293b"
                                                          stroke="{{ $isThisOnuTraced ? '#00e5ff' : $dotColor }}" stroke-width="{{ $isThisOnuTraced ? '2.5' : '1.5' }}"/>
                                                    <circle cx="16" cy="26" r="5" fill="{{ $dotColor }}"/>
                                                    <text x="28" y="21" fill="#ffffff" font-size="11" font-weight="bold" font-family="sans-serif">
                                                        {{ Str::limit($custName, 18) }}
                                                    </text>
                                                    <text x="28" y="35" fill="#94a3b8" font-size="9" font-family="monospace">
                                                        {{ Str::limit($onu->serial_number, 13) }} • {{ Str::limit($custUser, 12) }}
                                                    </text>
                                                    <text x="28" y="46" fill="{{ $dotColor }}" font-size="9" font-weight="bold" font-family="monospace">
                                                        Rx: {{ $rxVal !== null ? number_format($rxVal, 2) . ' dBm' : '— dBm' }} ({{ $statusText }})
                                                    </text>
                                                </g>
                                            @endforeach
                                        @else
                                            {{-- Empty port placeholder --}}
                                            <path d="M {{ $xOdp + 210 }} {{ $centerY }} L {{ $xOnu }} {{ $centerY }}"
                                                  stroke="#475569" stroke-width="1.5" stroke-dasharray="4,4" fill="none"/>
                                            <text x="{{ $xOnu + 8 }}" y="{{ $centerY + 4 }}" fill="#64748b" font-size="10" font-family="sans-serif">
                                                Port Kosong / Siap Pasang Pelanggan
                                            </text>
                                        @endif

                                    @else
                                        {{-- SUB-ODC NODE CARD --}}
                                        <g id="nodeSubOdc_{{ $item->id }}" transform="translate({{ $xOdp }}, {{ $centerY - 48 }})"
                                           class="cursor-pointer" filter="url(#odcNodeShadow)"
                                           onclick="window.open('/isp/odcs/{{ $item->id }}', '_blank')">
                                            <rect width="210" height="96" rx="14" fill="url(#gradOdcSub)"
                                                  stroke="{{ $isTraced ? '#00e5ff' : '#c084fc' }}" stroke-width="{{ $isTraced ? '3' : '2' }}"/>
                                            <rect x="12" y="10" width="95" height="17" rx="5" fill="#581c87"/>
                                            <text x="18" y="22" fill="#f3e8ff" font-size="9" font-weight="bold" font-family="sans-serif">SUB-ODC KASKADE</text>
                                            <text x="12" y="47" fill="#ffffff" font-size="13" font-weight="bold" font-family="sans-serif">{{ Str::limit($item->name, 16) }}</text>
                                            <text x="12" y="65" fill="#f3e8ff" font-size="10" font-family="monospace">{{ $item->code }} • {{ $item->port_count ?: 24 }} Port</text>
                                            <text x="12" y="81" fill="#e9d5ff" font-size="9" font-family="sans-serif">Splitter Kaskade (-10.5 dB)</text>
                                            <text x="12" y="93" fill="#d8b4fe" font-size="8.5" font-family="sans-serif">{{ $item->odps->count() }} ODP Terhubung</text>
                                        </g>
                                    @endif
                                @endforeach
                            @else
                                {{-- Empty state: No ODPs connected --}}
                                <g transform="translate({{ $xTargetOdc + 280 }}, {{ $targetOdcCenterY - 45 }})">
                                    <rect width="280" height="90" rx="12" fill="#0f172a" stroke="#334155" stroke-dasharray="6,4"/>
                                    <text x="24" y="35" fill="#94a3b8" font-size="12" font-weight="bold" font-family="sans-serif">Belum Ada ODP Terhubung</text>
                                    <text x="24" y="55" fill="#64748b" font-size="10" font-family="sans-serif">Hubungkan ODP ke port ODC ini via tab</text>
                                    <text x="24" y="70" fill="#38bdf8" font-size="10" font-weight="bold" font-family="sans-serif">Port Management atau menu Data ODP</text>
                                </g>
                            @endif
                        </g>
                    </svg>
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
    let panX = 30;
    let panY = 30;
    let isDragging = false;
    let startX = 0;
    let startY = 0;
    let isInitialized = false;

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

    function initOdcTopology() {
        const { container, viewport, wrapper, svg } = getElements();
        if (!container || !viewport) return;

        // Reset state
        scale = 1.0;
        panX = 30;
        panY = 30;
        updateTransform();

        if (container.dataset.topoBound === 'true') return;
        container.dataset.topoBound = 'true';

        // Zoom In
        document.getElementById('btnOdcZoomIn')?.addEventListener('click', (e) => {
            e.stopPropagation();
            scale = Math.min(2.5, scale + 0.2);
            updateTransform();
        });

        // Zoom Out
        document.getElementById('btnOdcZoomOut')?.addEventListener('click', (e) => {
            e.stopPropagation();
            scale = Math.max(0.3, scale - 0.2);
            updateTransform();
        });

        // Reset (1:1)
        document.getElementById('btnOdcReset')?.addEventListener('click', (e) => {
            e.stopPropagation();
            scale = 1.0;
            panX = 30;
            panY = 30;
            updateTransform();
        });

        // Fit to Screen
        document.getElementById('btnOdcFit')?.addEventListener('click', (e) => {
            e.stopPropagation();
            fitToScreen();
        });

        function fitToScreen() {
            const { container, viewport } = getElements();
            if (!container || !viewport) return;
            try {
                const bbox = viewport.getBBox();
                const cW = container.clientWidth || 900;
                const cH = container.clientHeight || 700;
                if (bbox.width > 0 && bbox.height > 0) {
                    const isFullscreen = !!document.fullscreenElement;
                    const maxScale = isFullscreen ? 1.4 : 1.15;
                    const scaleX = (cW - 80) / bbox.width;
                    const scaleY = (cH - 80) / bbox.height;
                    scale = Math.min(Math.max(0.4, Math.min(scaleX, scaleY)), maxScale);
                    panX = (cW - (bbox.width * scale)) / 2 - (bbox.x * scale);
                    panY = (cH - (bbox.height * scale)) / 2 - (bbox.y * scale);
                    updateTransform();
                }
            } catch (err) {}
        }

        // Fullscreen
        document.getElementById('btnOdcFullscreen')?.addEventListener('click', (e) => {
            e.stopPropagation();
            const { wrapper } = getElements();
            if (!wrapper) return;
            if (!document.fullscreenElement) {
                wrapper.requestFullscreen?.().then(() => {
                    setTimeout(fitToScreen, 150);
                    setTimeout(fitToScreen, 350);
                }).catch(() => {});
            } else {
                document.exitFullscreen?.().then(() => {
                    setTimeout(fitToScreen, 150);
                    setTimeout(fitToScreen, 350);
                }).catch(() => {});
            }
        });

        document.addEventListener('fullscreenchange', () => {
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

        // Initial fit
        setTimeout(fitToScreen, 150);
    }

    // Attach lifecycle triggers
    document.addEventListener('DOMContentLoaded', initOdcTopology);
    document.addEventListener('livewire:navigated', initOdcTopology);
    document.addEventListener('livewire:init', () => {
        if (window.Livewire) {
            Livewire.hook('commit', ({ succeed }) => {
                succeed(() => {
                    setTimeout(initOdcTopology, 50);
                });
            });
        }
    });

    // Also run immediately in case DOM is already ready
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(initOdcTopology, 100);
    }
})();
</script>
