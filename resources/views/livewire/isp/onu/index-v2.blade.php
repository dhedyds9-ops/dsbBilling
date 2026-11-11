@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">router</span>
    <span class="text-lg">Data ONU</span>
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
    @if(session('warning'))
        <div class="flex items-center gap-3 px-4 py-3 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 rounded-xl text-amber-700 dark:text-amber-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">warning</span>
            {{ session('warning') }}
        </div>
    @endif
    @if(session('info'))
        <div class="flex items-center gap-3 px-4 py-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-xl text-blue-700 dark:text-blue-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">info</span>
            {{ session('info') }}
        </div>
    @endif

    {{-- STATISTIK & FILTER CEPAT ONU --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
        <button type="button" wire:click="$set('statusFilter', 'all')" class="text-left bg-white dark:bg-slate-800 p-4 rounded-xl border {{ $statusFilter === 'all' ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-600' }} shadow-sm transition-all flex items-center justify-between cursor-pointer">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total ONU</p>
                <div class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">{{ number_format($summary['total'] ?? 0) }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">router</span>
            </div>
        </button>

        <button type="button" wire:click="$set('statusFilter', 'online')" class="text-left bg-white dark:bg-slate-800 p-4 rounded-xl border {{ $statusFilter === 'online' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-emerald-300 dark:hover:border-emerald-600' }} shadow-sm transition-all flex items-center justify-between cursor-pointer">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Online</p>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($summary['online'] ?? 0) }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">wifi</span>
            </div>
        </button>

        <button type="button" wire:click="$set('statusFilter', 'offline')" class="text-left bg-white dark:bg-slate-800 p-4 rounded-xl border {{ $statusFilter === 'offline' ? 'border-slate-500 ring-2 ring-slate-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600' }} shadow-sm transition-all flex items-center justify-between cursor-pointer">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Offline</p>
                <div class="text-2xl font-black text-slate-700 dark:text-slate-200 mt-1">{{ number_format($summary['offline'] ?? 0) }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">wifi_off</span>
            </div>
        </button>

        <button type="button" wire:click="$set('statusFilter', 'los')" class="text-left bg-white dark:bg-slate-800 p-4 rounded-xl border {{ $statusFilter === 'los' ? 'border-red-500 ring-2 ring-red-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-red-300 dark:hover:border-red-600' }} shadow-sm transition-all flex items-center justify-between cursor-pointer">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">LOS / Crit</p>
                <div class="text-2xl font-black text-red-600 dark:text-red-400 mt-1">{{ number_format($summary['los'] ?? 0) }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/40 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">signal_disconnected</span>
            </div>
        </button>

        <button type="button" wire:click="$set('statusFilter', 'low_rx')" class="col-span-2 sm:col-span-1 text-left bg-white dark:bg-slate-800 p-4 rounded-xl border {{ $statusFilter === 'low_rx' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-amber-300 dark:hover:border-amber-600' }} shadow-sm transition-all flex items-center justify-between cursor-pointer">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Low RX</p>
                <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ number_format($summary['low_rx'] ?? 0) }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">warning</span>
            </div>
        </button>
    </div>

    {{-- TOOLBAR & FILTER --}}
    <div class="bg-white dark:bg-slate-800 p-3.5 sm:p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-3">
        {{-- Row 1: Action Buttons --}}
        <div class="flex flex-wrap items-center justify-between gap-2.5">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('isp.onus.create') }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm shadow-indigo-200 dark:shadow-none transition-all whitespace-nowrap">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">add</span>
                    <span>Tambah ONU</span>
                </a>
                <button type="button" wire:click="syncFromOlt" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-slate-50 hover:bg-slate-100 dark:bg-slate-700/50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg shadow-sm transition-all cursor-pointer whitespace-nowrap" title="Sinkronkan data ONU & Redaman dari OLT">
                    <span class="material-symbols-outlined notranslate text-indigo-500" wire:loading.class="animate-spin" wire:target="syncFromOlt,syncAll,syncOlt" translate="no" style="font-size:18px">sync</span>
                    <span wire:loading.remove wire:target="syncFromOlt,syncAll,syncOlt">Sinkronisasi OLT</span>
                    <span wire:loading wire:target="syncFromOlt,syncAll,syncOlt">Sinkronisasi...</span>
                </button>
                <button type="button" wire:click="cleanupDuplicates" wire:confirm="Sistem akan otomatis membersihkan baris ONU duplikat dan mempertahankan data ONU yang valid. Lanjutkan?" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/40 dark:hover:bg-amber-900/50 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60 text-sm font-medium rounded-lg shadow-sm transition-all cursor-pointer whitespace-nowrap">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">cleaning_services</span>
                    <span>Bersihkan Duplikat</span>
                </button>
                <button type="button" wire:click="deleteAllOfflineOnu" wire:confirm="Yakin ingin menghapus SEMUA ONU Offline / Orphan ({{ $summary['offline'] ?? 0 }} ONU)? Data di dsBilling dan registrasi di perangkat fisik OLT akan dihapus bersamaan." class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-red-50 hover:bg-red-100 dark:bg-red-950/40 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800/60 text-sm font-medium rounded-lg shadow-sm transition-all cursor-pointer whitespace-nowrap">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete_sweep</span>
                    <span>Hapus Offline</span>
                </button>
            </div>
        </div>

        {{-- Row 2: Search & Filters --}}
        <div class="flex flex-col lg:flex-row lg:items-center gap-2.5 pt-2.5 border-t border-slate-100 dark:border-slate-700/60">
            {{-- Search Box --}}
            <div class="relative flex-1 min-w-[220px]">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.400ms="search"
                       placeholder="Cari SN, MAC, Nama ONU, Pelanggan..."
                       class="w-full pl-9 pr-9 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                @if(!empty($search))
                    <button type="button" wire:click="$set('search', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors" title="Hapus Pencarian">
                        <span class="material-symbols-outlined notranslate" style="font-size:18px">close</span>
                    </button>
                @endif
            </div>

            {{-- Filters & Reset --}}
            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="oltFilter"
                        class="pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="">Semua OLT</option>
                    @foreach($olts as $olt)
                        <option value="{{ $olt->id }}">{{ $olt->name }}</option>
                    @endforeach
                </select>

                @if(!empty($oltFilter) && $oltFilter !== 'all')
                    <select wire:model.live="ponFilter"
                            class="pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                        <option value="">Semua PON</option>
                        @foreach($ponPorts as $pon)
                            <option value="{{ $pon->id }}">{{ $pon->name }}</option>
                        @endforeach
                    </select>
                @endif

                <select wire:model.live="statusFilter"
                        class="pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="all">Semua Status</option>
                    <option value="online">Online</option>
                    <option value="offline">Offline</option>
                    <option value="los">LOS / Critical RX</option>
                    <option value="low_rx">Low RX (-30 s/d -27)</option>
                </select>

                <select wire:model.live="perPage"
                        class="pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="25">25 / hal</option>
                    <option value="50">50 / hal</option>
                    <option value="100">100 / hal</option>
                    <option value="0">Semua</option>
                </select>

                @if($search !== '' || $statusFilter !== 'all' || $oltFilter !== '' || $ponFilter !== '')
                    <button type="button" wire:click="resetFilters"
                            title="Reset Filter"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-lg text-sm font-medium transition-colors cursor-pointer whitespace-nowrap">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">filter_alt_off</span>
                        <span>Reset</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- DATA TABLE --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50/80 dark:bg-slate-800/80">
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Aksi</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap cursor-pointer" wire:click="sort('status')">
                            Status {!! $sortField === 'status' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : '' !!}
                        </th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap cursor-pointer" wire:click="sort('serial_number')">
                            SN & Nama ONU {!! $sortField === 'serial_number' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : '' !!}
                        </th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">MAC / IP Host</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">OLT / PON</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">Cabang</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">Pelanggan</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap cursor-pointer" wire:click="sort('rx_power_dbm')">
                            Redaman (RX / TX) {!! $sortField === 'rx_power_dbm' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : '' !!}
                        </th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap cursor-pointer" wire:click="sort('last_seen_at')">
                            Terakhir Aktif {!! $sortField === 'last_seen_at' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : '' !!}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-800">
                    @forelse($onus as $onu)
                        @php
                            $st = $this->getOnuStatus($onu);
                            $badgeClass = match($st) {
                                'ONLINE' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
                                'LOW_RX' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
                                'LOS'    => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
                                default  => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
                            };
                            $dotClass = match($st) {
                                'ONLINE' => 'bg-emerald-500',
                                'LOW_RX' => 'bg-amber-500',
                                'LOS'    => 'bg-red-500',
                                default  => 'bg-slate-400',
                            };
                            $rxClass = $onu->rx_power_dbm !== null
                                ? ($onu->rx_power_dbm < -30 ? 'text-red-600 dark:text-red-400' : ($onu->rx_power_dbm < -27 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400'))
                                : 'text-slate-400';
                        @endphp
                        <tr wire:key="onu-{{ $onu->id }}" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" wire:click="syncSingleOnu({{ $onu->id }})" wire:loading.attr="disabled" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-indigo-50 hover:bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 dark:text-indigo-400 transition-colors cursor-pointer" title="Sinkronkan ONU dari OLT">
                                        <span class="material-symbols-outlined notranslate" wire:loading.class="animate-spin" wire:target="syncSingleOnu({{ $onu->id }})" translate="no" style="font-size:18px">sync</span>
                                    </button>
                                    <a href="{{ route('isp.onus.show', $onu->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 dark:text-blue-400 transition-colors" title="Detail">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">visibility</span>
                                    </a>
                                    <a href="{{ route('isp.onus.edit', $onu->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 dark:text-emerald-400 transition-colors" title="Edit">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                                    </a>
                                    <button type="button" wire:click="deleteOnu({{ $onu->id }})" wire:confirm="Yakin ingin menghapus ONU {{ $onu->serial_number ?: ('#' . $onu->id) }}? Registrasi di perangkat fisik OLT juga akan otomatis dihapus." class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/30 dark:hover:bg-red-900/50 dark:text-red-400 transition-colors cursor-pointer" title="Hapus">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 inline-flex items-center gap-1.5 text-xs font-semibold rounded-full {{ $badgeClass }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                    {{ str_replace('_', ' ', $st) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="text-sm font-mono font-semibold text-slate-800 dark:text-slate-100">
                                    <a href="{{ route('isp.onus.show', $onu->id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                        {{ $onu->serial_number ?: ($onu->code ?: '-') }}
                                    </a>
                                </div>
                                @if($onu->name && strtoupper(trim($onu->name)) !== strtoupper(trim($onu->serial_number ?? '')))
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5" title="Nama / Deskripsi ONU di OLT">
                                        <span class="text-[10px] uppercase tracking-wider font-medium px-1.5 py-0.5 bg-slate-100 dark:bg-slate-700/80 text-slate-600 dark:text-slate-300 rounded">Nama</span>
                                        <span class="font-mono text-slate-600 dark:text-slate-300">{{ $onu->name }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="text-xs font-mono text-slate-700 dark:text-slate-200">{{ $onu->mac_address ?? $onu->acsDevice?->mac_address ?? '-' }}</div>
                                <div class="text-xs font-mono text-slate-500 dark:text-slate-400 mt-0.5">{{ $onu->ip_address ?? $onu->acsDevice?->ip_address ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $onu->olt?->name ?? '-' }}</div>
                                <div class="text-xs font-mono text-slate-500 dark:text-slate-400">PON: {{ $onu->ponPort?->name ?? ($onu->pon_port ?? '-') }}</div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($onu->branch)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                        {{ $onu->branch->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">Global</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 group cursor-pointer text-sm text-slate-700 dark:text-slate-200" 
                                     wire:click="editCustomer({{ $onu->id }}, '{{ $onu->customerService->customer_id ?? '' }}')" 
                                     title="Klik untuk ubah / tautkan pelanggan">
                                    @if($onu->customerService?->customer)
                                        <div>
                                            <div class="font-medium text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                                {{ $onu->customerService->customer->name }}
                                            </div>
                                            <div class="text-[11px] font-mono text-slate-400">
                                                {{ $onu->customerService->customer->code ?: $onu->customerService->customer->phone }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic flex items-center gap-1 group-hover:text-indigo-500 transition-colors">
                                            <span class="material-symbols-outlined text-[15px]">person_add</span>
                                            <span>Tautkan Pelanggan</span>
                                        </span>
                                    @endif
                                    <span class="material-symbols-outlined notranslate opacity-0 group-hover:opacity-100 text-slate-400 hover:text-indigo-500 transition-opacity ml-1" translate="no" style="font-size:15px">edit</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="text-sm font-mono font-semibold {{ $rxClass }}">
                                    RX: {{ $onu->rx_power_dbm !== null ? number_format($onu->rx_power_dbm, 2) . ' dBm' : '-' }}
                                </div>
                                <div class="text-xs font-mono text-slate-500 dark:text-slate-400">
                                    TX: {{ $onu->tx_power_dbm !== null ? number_format($onu->tx_power_dbm, 2) . ' dBm' : '-' }}
                                    @if($onu->temperature)
                                        &bull; {{ $onu->temperature }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">
                                {{ $onu->last_seen_at ? $onu->last_seen_at->diffForHumans() : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-14 h-14 bg-slate-100 dark:bg-slate-700/60 rounded-full flex items-center justify-center mb-3">
                                        <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:28px">router</span>
                                    </div>
                                    <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-1">Belum ada data ONU</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Tambahkan ONU baru secara manual atau jalankan Sinkronisasi OLT.</p>
                                    <a href="{{ route('isp.onus.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">add</span>
                                        <span>Tambah ONU</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($onus, 'hasPages') && $onus->hasPages())
            <div class="bg-white dark:bg-slate-800 px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                {{ $onus->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>

    {{-- Modal Tautkan Pelanggan ke ONU (Dedicated Full Viewport Modal) --}}
    @if($showCustomerModal && $editingOnu)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 animate-in fade-in duration-150">
            <div class="fixed inset-0" wire:click="cancelEditCustomer"></div>

            <div x-data="{
                    search: '',
                    selectedId: @entangle('selectedCustomerId'),
                    customers: {{ json_encode($this->allCustomers->map(fn($c) => ['id' => (string)$c->id, 'name' => $c->name, 'code' => $c->code ?? '', 'phone' => $c->phone ?? ''])) }},
                    get filtered() {
                        if (!this.search.trim()) return this.customers.slice(0, 50);
                        const s = this.search.toLowerCase();
                        return this.customers.filter(c => 
                            (c.name && c.name.toLowerCase().includes(s)) || 
                            (c.code && c.code.toLowerCase().includes(s)) ||
                            (c.phone && c.phone.includes(s))
                        ).slice(0, 50);
                    },
                    get selectedCustomer() {
                        return this.customers.find(c => c.id == this.selectedId);
                    }
                 }" 
                 class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-lg overflow-hidden z-10 flex flex-col max-h-[90vh]">
                
                {{-- Header --}}
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/60 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/80">
                    <div>
                        <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-500 text-[20px]">link</span>
                            <span>Tautkan Pelanggan ke ONU</span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            ONU SN: <span class="font-mono font-medium text-slate-700 dark:text-slate-300">{{ $editingOnu->sn }}</span>
                            @if($editingOnu->olt)
                                &bull; OLT: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $editingOnu->olt->name }}</span>
                            @endif
                            @if($editingOnu->ponPort)
                                &bull; PON: <span class="font-mono font-medium text-slate-700 dark:text-slate-300">{{ $editingOnu->ponPort->name }}</span>
                            @endif
                        </p>
                    </div>
                    <button type="button" 
                            wire:click="cancelEditCustomer" 
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                {{-- Status Terpilih Saat Ini --}}
                <div class="px-5 py-3 bg-indigo-50/40 dark:bg-indigo-950/20 border-b border-indigo-100/50 dark:border-indigo-900/30 flex items-center justify-between gap-3">
                    <div class="text-xs truncate">
                        <span class="text-slate-500 dark:text-slate-400">Pilihan saat ini:</span>
                        <template x-if="selectedCustomer">
                            <span class="font-semibold text-indigo-700 dark:text-indigo-300 ml-1">
                                <span x-text="selectedCustomer.name"></span>
                                <span class="font-mono text-[11px] opacity-75" x-text="selectedCustomer.code ? '(' + selectedCustomer.code + ')' : ''"></span>
                            </span>
                        </template>
                        <template x-if="!selectedCustomer && !selectedId">
                            <span class="font-medium text-slate-400 italic ml-1">Tidak ada (ONU belum tertaut)</span>
                        </template>
                    </div>
                    <button type="button" 
                            x-show="selectedId" 
                            @click="selectedId = ''" 
                            class="text-[11px] text-red-600 dark:text-red-400 hover:underline shrink-0 flex items-center gap-1 font-medium">
                        <span class="material-symbols-outlined text-[13px]">link_off</span>
                        <span>Lepas Tautan</span>
                    </button>
                </div>

                {{-- Search Box --}}
                <div class="p-4 border-b border-slate-100 dark:border-slate-700/60 bg-white dark:bg-slate-800">
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-[18px] text-slate-400">search</span>
                        <input type="text" 
                               x-model="search" 
                               placeholder="Cari nama, ID pelanggan, atau nomor telepon..." 
                               class="w-full pl-9 pr-8 py-2 text-sm bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                        <button type="button" 
                                x-show="search" 
                                @click="search = ''" 
                                class="absolute right-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <span class="material-symbols-outlined text-[16px]">cancel</span>
                        </button>
                    </div>
                </div>

                {{-- Customer List --}}
                <div class="flex-1 overflow-y-auto p-2 divide-y divide-slate-100 dark:divide-slate-700/40 max-h-72">
                    <template x-for="c in filtered" :key="c.id">
                        <div @click="selectedId = c.id" 
                             class="p-2.5 rounded-xl cursor-pointer transition-all flex items-center justify-between gap-3"
                             :class="selectedId == c.id ? 'bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300' : 'hover:bg-slate-50 dark:hover:bg-slate-700/40 text-slate-700 dark:text-slate-200'">
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-sm text-slate-800 dark:text-slate-100 truncate" x-text="c.name"></div>
                                <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    <span class="font-mono text-indigo-600 dark:text-indigo-400 font-medium" x-show="c.code" x-text="c.code"></span>
                                    <span x-show="c.phone" x-text="c.phone"></span>
                                </div>
                            </div>
                            <div class="shrink-0">
                                <span x-show="selectedId == c.id" class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[15px]">check</span>
                                </span>
                                <span x-show="selectedId != c.id" class="w-6 h-6 rounded-full border border-slate-300 dark:border-slate-600"></span>
                            </div>
                        </div>
                    </template>

                    <div x-show="filtered.length === 0" class="py-8 text-center text-slate-400 text-sm">
                        <span class="material-symbols-outlined text-3xl mb-1 text-slate-300 dark:text-slate-600">search_off</span>
                        <p>Pelanggan tidak ditemukan untuk "<span x-text="search"></span>"</p>
                    </div>
                </div>

                {{-- Footer Action Buttons --}}
                <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80 flex items-center justify-end gap-2.5">
                    <button type="button" 
                            wire:click="cancelEditCustomer" 
                            class="px-4 py-2 text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-slate-700 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="button" 
                            wire:click="assignCustomer" 
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-xs sm:text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-xs transition-colors cursor-pointer disabled:opacity-50">
                        <span wire:loading.remove wire:target="assignCustomer" class="material-symbols-outlined text-[17px]">save</span>
                        <span wire:loading wire:target="assignCustomer" class="material-symbols-outlined text-[17px] animate-spin">refresh</span>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
