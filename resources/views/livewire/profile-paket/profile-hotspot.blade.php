<div class="max-w-7xl mx-auto p-3">
    <x-admin.breadcrumbs />

    <div class="mb-3">
        @if(session()->has('success'))
            <x-feedback.alert variant="success">{{ session('success') }}</x-feedback.alert>
        @endif
        @if(session()->has('error'))
            <x-feedback.alert variant="danger">{{ session('error') }}</x-feedback.alert>
        @endif
        @if(session()->has('warning'))
            <x-feedback.alert variant="warning">{{ session('warning') }}</x-feedback.alert>
        @endif
        @if(session()->has('info'))
            <x-feedback.alert variant="info">{{ session('info') }}</x-feedback.alert>
        @endif
    </div>

    <!-- HEADER (SSOT: Judul Kiri + Dropdown Manajemen Kanan) -->
    <div class="flex justify-between items-center mb-3 gap-3 flex-wrap">
        <div x-data="{ open: false }" class="relative inline-block text-left">
            <div>
                <button @click="open = !open" type="button" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium shadow-sm shadow-primary-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    Manajemen Profile Hotspot
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
            <div x-show="open" @click.away="open = false" x-transition class="origin-top-right absolute right-0 mt-2 w-64 rounded-lg shadow-lg bg-white dark:bg-slate-800 ring-1 ring-black ring-opacity-5 dark:ring-white/10 divide-y divide-gray-100 dark:divide-slate-700 z-50">
                <div class="py-1">
                    <button type="button" wire:click="openCreateModal" @click="open = false" class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        @if($activeTab === 'member') Tambah Profile Member @else Tambah Profile Voucher @endif
                    </button>
                </div>
                <div class="py-1">
                    <button type="button" class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        Generate Voucher Massal
                    </button>
                    <button type="button" class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Cetak Voucher
                    </button>
                    <button type="button" onclick="window.print()" @click="open = false" class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export CSV / Print
                    </button>
                </div>
                <div class="py-1">
                    <button type="button" class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700">
                        <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Sync ke Router MikroTik
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- TABS (Member Bulanan | Voucher Sekali Pakai) -->
    <div class="mb-3">
        <div class="border-b border-gray-200 dark:border-slate-700">
            <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                <button wire:click="setTab('member')" type="button" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors
                    @if($activeTab === 'member') border-primary-500 text-primary-600 dark:text-primary-400 @else border-transparent text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-200 hover:border-gray-300 dark:hover:border-slate-600 @endif">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Member Hotspot
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold @if($activeTab === 'member') bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-200 @else bg-gray-100 text-gray-600 dark:bg-slate-700 dark:text-slate-300 @endif">
                            {{ $stats['total'] }}
                        </span>
                    </span>
                </button>
                <button wire:click="setTab('voucher')" type="button" class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors
                    @if($activeTab === 'voucher') border-primary-500 text-primary-600 dark:text-primary-400 @else border-transparent text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-200 hover:border-gray-300 dark:hover:border-slate-600 @endif">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        Voucher
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold @if($activeTab === 'voucher') bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-200 @else bg-gray-100 text-gray-600 dark:bg-slate-700 dark:text-slate-300 @endif">
                            {{ $stats['total'] }}
                        </span>
                    </span>
                </button>
            </nav>
        </div>
    </div>

    <!-- STATS (Gunakan x-base.card, pola PPPoE) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-3">
        <x-base.card>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">Total Paket</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</div>
                </div>
            </div>
        </x-base.card>
        <x-base.card>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/30 text-green-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">Aktif</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['active'] }}</div>
                </div>
            </div>
        </x-base.card>
        <x-base.card>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 text-purple-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">Rata-rata Harga</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($stats['avg_price'], 0, ',', '.') }}</div>
                </div>
            </div>
        </x-base.card>
        <x-base.card>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-900/30 text-orange-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">Estimasi MRR</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($stats['total_revenue_monthly_est'], 0, ',', '.') }}</div>
                </div>
            </div>
        </x-base.card>
    </div>

    <!-- TOOLBAR (Pola PPPoE: x-base.card) -->
    <x-base.card class="mb-3">
        <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
            <div class="w-full lg:w-1/3">
                <label for="searchHotspot" class="sr-only">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input id="searchHotspot" wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" placeholder="Cari nama paket, kode, prefix...">
                </div>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <div class="min-w-[150px]">
                    <select wire:model.live="perPage" class="w-full border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white rounded-lg focus:border-primary-500 focus:ring-primary-500">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
    </x-base.card>

    <!-- TABLE (Pola Router Index: x-base.card overflow-hidden) -->
    <x-base.card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                <thead class="bg-gray-50 dark:bg-slate-800/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Nama Paket</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Harga</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Masa Aktif</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Speed</th>
                        @if($activeTab === 'voucher')
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Kuota / Waktu</th>
                        @else
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Shared Users</th>
                        @endif
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-700">
                    @forelse($profiles as $p)
                        @php
                            $activeVals = [
                                'active' => ['label' => 'Aktif', 'class' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'],
                                'inactive' => ['label' => 'Nonaktif', 'class' => 'bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-300'],
                            ];
                            $sv = $activeVals[$p->status] ?? $activeVals['inactive'];
                            $ownerName = $p->owner->name ?? '-';
                            $prefix = $p->prefix_code ?? '-';
                            preg_match('/([0-9.]+)\s*/i', $p->download_speed ?? '0', $dlMatch);
                            preg_match('/([0-9.]+)\s*/i', $p->upload_speed ?? '0', $ulMatch);
                            $unit = str_contains($p->download_speed, 'Gbps') ? 'Gbps' : (str_contains($p->download_speed, 'Kbps') ? 'Kbps' : 'Mbps');
                        @endphp
                        <tr wire:key="{{ $p->id }}" class="hover:bg-gray-50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 text-white flex items-center justify-center font-bold shadow">
                                        {{ $p->name ? strtoupper(substr($p->name, 0, 1)) : 'H' }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $p->name }}</div>
                                        <div class="text-xs text-gray-400 dark:text-slate-500 font-mono">
                                            {{ $p->code }} @if($prefix) · {{ $prefix }}@endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format($p->base_price ?? 0, 0, ',', '.') }}</div>
                                @if($activeTab === 'member')
                                    <div class="text-[11px] text-gray-400 dark:text-slate-500">
                                        Reseller: Rp {{ number_format($p->reseller_price ?? 0, 0, ',', '.') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 inline-flex text-xs leading-4 font-bold rounded-full uppercase tracking-wider bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                    {{ $p->validity_value }} {{ $p->validity_unit }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2 text-xs">
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase text-primary-600">DL</span>
                                        <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $dlMatch[1] ?? 0 }} {{ $unit }}</div>
                                    </div>
                                    <div class="w-px h-8 bg-gray-200 dark:bg-slate-700"></div>
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase text-success-600">UL</span>
                                        <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $ulMatch[1] ?? 0 }} {{ $unit }}</div>
                                    </div>
                                </div>
                            </td>
                            @if($activeTab === 'voucher')
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($p->quota_gb)
                                <div class="font-semibold text-purple-600 dark:text-purple-400">{{ $p->quota_gb }} GB</div>
                                @endif
                                @if($p->time_limit_hours)
                                <div class="text-[11px] text-gray-500 dark:text-slate-400">Limit: {{ $p->time_limit_hours }} Jam</div>
                                @endif
                                @if(!$p->quota_gb && !$p->time_limit_hours)
                                <span class="text-gray-400 dark:text-slate-500 italic">Unlimited</span>
                                @endif
                            </td>
                            @else
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2.5 py-1 inline-flex text-xs leading-4 font-bold rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200">
                                    {{ $p->shared_users ?? 1 }} User
                                </span>
                            </td>
                            @endif
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 inline-flex text-[11px] leading-4 font-bold rounded-full uppercase tracking-wider {{ $sv['class'] }}">
                                    {{ $sv['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="inline-flex items-center gap-1">
                                    <button wire:click="toggleStatus({{ $p->id }})" title="{{ $p->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}" type="button" class="p-2 rounded-lg text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button wire:click="openEditModal({{ $p->id }})" title="Edit" type="button" class="p-2 rounded-lg text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button wire:click="duplicate({{ $p->id }})" title="Duplikat" type="button" class="p-2 rounded-lg text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </button>
                                    <button wire:click="delete({{ $p->id }})" wire:confirm="Yakin hapus profile {{ $p->name }}?" title="Hapus" type="button" class="p-2 rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400 dark:text-slate-500">
                                <svg class="w-10 h-10 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                Belum ada profile {{ $activeTab === 'member' ? 'Member' : 'Voucher' }}.
                                <div class="mt-2 text-sm text-gray-500 dark:text-slate-400">Gunakan dropdown <b>Manajemen Profile Hotspot → @if($activeTab === 'member') Tambah Profile Member @else Tambah Profile Voucher @endif</b>.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($profiles, 'hasPages') && $profiles->hasPages())
            <div class="p-4 border-t border-gray-200 dark:border-slate-700">{{ $profiles->links() }}</div>
        @endif
    </x-base.card>

    <!-- MODAL CREATE / EDIT -->
    @if($showCreateModal)
    <div x-data="{ open: true }" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 sm:p-0">
            <div x-show="open" x-transition class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
            <div class="inline-block w-full max-w-3xl p-6 my-8 text-left align-middle transition-all transform bg-white dark:bg-slate-800 shadow-2xl rounded-2xl">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    {{ $editingId ? 'Edit' : 'Tambah' }} Profile {{ $activeTab === 'member' ? 'Member Hotspot Bulanan' : 'Voucher Sekali Pakai' }}
                </h3>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Nama Paket</label>
                            <input type="text" wire:model="form.name" class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm" placeholder="Contoh: Paket Premium 10Mbps">
                            @error('form.name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Harga (Rp)</label>
                            <input type="number" wire:model="form.price" class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm font-mono" placeholder="150000">
                            @error('form.price') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-blue-600 mb-1">Masa Berlaku</label>
                            <select wire:model="form.validity_unit" class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
                                <option value="hours">Jam</option>
                                <option value="days">Hari</option>
                                <option value="months">Bulan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-blue-600 mb-1">Durasi</label>
                            <input type="number" wire:model="form.validity_value" min="1" class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
                            @error('form.validity_value') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1">Shared Users</label>
                            <input type="number" wire:model="form.shared_users" min="1" class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-purple-600 mb-1">Prefix Code</label>
                            <input type="text" wire:model="form.auto_voucher_code_prefix" class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm font-mono uppercase">
                        </div>
                    </div>

                    <!-- SPEED LIMIT -->
                    <div class="bg-slate-50 dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                        <div class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            SPEED LIMIT
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-primary-600 mb-1">Download (Mbps)</label>
                                <input type="number" step="0.01" wire:model="form.download_max" class="w-full rounded-lg border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-white text-sm focus:border-primary-500 focus:ring-primary-500">
                                @error('form.download_max') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-success-600 mb-1">Upload (Mbps)</label>
                                <input type="number" step="0.01" wire:model="form.upload_max" class="w-full rounded-lg border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-white text-sm focus:border-primary-500 focus:ring-primary-500">
                                @error('form.upload_max') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- VOUCHER ONLY -->
                    @if($activeTab === 'voucher')
                    <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 p-4 rounded-xl">
                        <div class="text-xs font-bold text-amber-700 dark:text-amber-300 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                            PENGATURAN VOUCHER SEKALI PAKAI
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Kuota Data (GB) - Opsional</label>
                                <input type="number" wire:model="form.quota_gb" class="w-full rounded-lg border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-white text-sm font-mono" placeholder="Kosong = Unlimited">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Waktu Limit (Jam) - Opsional</label>
                                <input type="number" wire:model="form.time_limit_hours" class="w-full rounded-lg border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-white text-sm font-mono" placeholder="Kosong = Unlimited">
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 p-4 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-500 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div class="text-sm text-blue-800 dark:text-blue-200">
                                <div class="font-bold">Tipe MEMBER BULANAN - Unlimited</div>
                                <div class="text-blue-800/80 dark:text-blue-200/80 text-xs">
                                    Berlaku perpanjangan tiap bulan. Pricing 3-tier sudah diset otomatis (Base 100%, Reseller 85%, Owner 70%).
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Deskripsi / Catatan</label>
                        <textarea wire:model="form.description" rows="2" class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="closeCreateModal" type="button" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-slate-300 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 rounded-lg">Batal</button>
                    <button wire:click="save" type="button" class="px-5 py-2.5 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg shadow-sm shadow-primary-500/20">
                        {{ $editingId ? 'Simpan Perubahan' : 'Simpan Profile' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
