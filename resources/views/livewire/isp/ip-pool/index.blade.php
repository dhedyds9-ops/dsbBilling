<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">IP Pool Management</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manajemen IP Pool untuk PPPoE, Hotspot, dan Static IP dengan sinkronisasi MikroTik.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-wrap gap-2">
            <button wire:click="syncAll" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Sync All
            </button>
            <button wire:click="exportFromMikrotik" class="inline-flex items-center justify-center px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-xl shadow-sm text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Import
            </button>
            <button wire:click="bulkExport" class="inline-flex items-center justify-center px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-xl shadow-sm text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export
            </button>
            <a href="{{ route('isp.ip-pools.create') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors" </a>
                    <select wire:model.live="perPage" class="rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm py-2 px-3 focus:ring-primary-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100">
                        <option value="10">10 / page</option>
                        <option value="25">25 / page</option>
                        <option value="50">50 / page</option>
                        <option value="100">100 / page</option>
                        <option value="All">All</option>
                    </select>
                </div>
            </div>

            @if($showFilters)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2 border-t border-slate-200 dark:border-slate-700">
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Status</label>
                    <select wire:model.live="filters.status" class="block w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm py-2 px-3 focus:ring-primary-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Non Aktif</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Tipe</label>
                    <select wire:model.live="filters.type" class="block w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm py-2 px-3 focus:ring-primary-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Semua Tipe</option>
                        <option value="ppp">PPPoE / IP Range</option>
                        <option value="hotspot">Group Only</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">POP / Lokasi</label>
                    <select wire:model.live="filters.pop_id" class="block w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm py-2 px-3 focus:ring-primary-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Semua POP</option>
                        @foreach($pops as $pop)
                        <option value="{{ $pop->id }}">{{ $pop->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button wire:click="resetFilters" class="w-full inline-flex items-center justify-center px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800 transition-colors">
                        Reset Filter
                    </button>
                </div>
            </div>
            @endif
        </div>

        @if(count($selectedIds) > 0)
        <div class="px-4 py-3 bg-blue-50 dark:bg-blue-900/30 border-b border-blue-100 dark:border-blue-800 flex flex-wrap items-center justify-between gap-3">
            <div class="text-sm text-primary-700 dark:text-blue-200 font-medium">
                {{ count($selectedIds) }} item terpilih
            </div>
            <div class="flex flex-wrap gap-2">
                <button wire:click="bulkSync" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-lg text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                    <svg class="-ml-0.5 mr-1.5 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Sync
                </button>
                <button wire:click="bulkEnable" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-lg text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                    <svg class="-ml-0.5 mr-1.5 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Aktifkan
                </button>
                <button wire:click="bulkDisable" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-lg text-xs font-medium text-white bg-amber-600 hover:bg-amber-700 transition-colors">
                    <svg class="-ml-0.5 mr-1.5 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    Non Aktif
                </button>
                <button wire:click="bulkExport" class="inline-flex items-center px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg text-xs font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-colors">
                    <svg class="-ml-0.5 mr-1.5 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Export
                </button>
                <button wire:click="bulkDelete" wire:confirm="Yakin ingin menghapus {{ count($selectedIds) }} IP Pool terpilih?" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-lg text-xs font-medium text-white bg-red-600 hover:bg-red-700 transition-colors">
                    <svg class="-ml-0.5 mr-1.5 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus
                </button>
            </div>
        </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                    <tr class="text-slate-500 dark:text-slate-400">
                        <th scope="col" class="p-3 w-12">
                            <input type="checkbox" wire:model.live="selectAll" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-slate-300 dark:border-slate-600 rounded dark:bg-slate-900 dark:text-slate-100">
                        </th>
                        <th scope="col" class="p-3 font-semibold cursor-pointer hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-200" wire:click="sortBy('name')">
                            Nama / Code
                            @if($sortField === 'name') <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th scope="col" class="p-3 font-semibold cursor-pointer hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-200" wire:click="sortBy('network')">
                            Network
                            @if($sortField === 'network') <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th scope="col" class="p-3 font-semibold">IP Range</th>
                        <th scope="col" class="p-3 font-semibold">Gateway</th>
                        <th scope="col" class="p-3 font-semibold">POP</th>
                        <th scope="col" class="p-3 font-semibold">IP Usage</th>
                        <th scope="col" class="p-3 font-semibold">Status</th>
                        <th scope="col" class="p-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($ipPools as $pool)
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700/50 transition-colors">
                        <td class="p-3">
                            <input type="checkbox" wire:model.live="selectedIds" value="{{ (string)$pool->id }}" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-slate-300 dark:border-slate-600 rounded">
                        </td>
                        <td class="p-3 whitespace-nowrap">
                            <div class="font-medium text-slate-900 dark:text-slate-100">{{ $pool->name }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $pool->code }}</div>
                        </td>
                        <td class="p-3 whitespace-nowrap font-mono text-sm text-slate-700 dark:text-slate-200">
                            {{ $pool->network ?? '-' }}
                            @if($pool->netmask)<span class="text-slate-500 dark:text-slate-400">/{{ long2ip(ip2long($pool->netmask)) ? substr_count(decbin(ip2long($pool->netmask)), '1') : '24' }}</span>@endif
                        </td>
                        <td class="p-3 whitespace-nowrap font-mono text-xs">
                            @if($pool->start_ip && $pool->end_ip)
                                <div class="text-slate-700 dark:text-slate-200">{{ $pool->start_ip }}</div>
                                <div class="text-slate-500 dark:text-slate-400">s/d {{ $pool->end_ip }}</div>
                            @else
                                <span class="text-slate-400">Group Only</span>
                            @endif
                        </td>
                        <td class="p-3 whitespace-nowrap font-mono text-sm text-slate-700 dark:text-slate-200">
                            {{ $pool->gateway ?? '-' }}
                        </td>
                        <td class="p-3 whitespace-nowrap text-sm">
                            @if($pool->pop)
                                <div class="text-slate-700 dark:text-slate-200 font-medium">{{ optional($pool->pop)->name }}</div>
                                @if(optional($pool->pop)->router)
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ optional(optional($pool->pop)->router)->name }}</div>
                                @endif
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="p-3 whitespace-nowrap">
                            @if($pool->total_ips > 0)
                            <div class="flex items-center gap-2">
                                <div class="w-24 bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ min(100, round(($pool->used_ips / $pool->total_ips) * 100)) }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-200">{{ $pool->used_ips }}/{{ $pool->total_ips }}</span>
                            </div>
                            @else
                            <span class="text-slate-400 text-xs">0</span>
                            @endif
                        </td>
                        <td class="p-3 whitespace-nowrap">
                            <x-ui.badge variant="success">
                                {{ strtoupper($pool->status) }}
                            </x-ui.badge>
                        </td>
                        <td class="p-3 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('isp.ip-pools.show', $pool- </a>
                                <button wire:click="toggleStatus({{ $pool->id }})" class="{{ $pool->status === 'active' ? 'text-amber-600 hover:text-amber-900 dark:text-amber-400 dark:hover:text-amber-200' : 'text-emerald-600 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-200' }} px-2 py-1 rounded-lg hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors" title="{{ $pool->status === 'active' ? 'Non Aktifkan' : 'Aktifkan' }}">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </button>
                                <a href="{{ route('isp.ip-pools.edit', $pool- </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                        <td colspan="9" class="p-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <h3 class="mt-2 text-sm font-medium text-slate-900 dark:text-slate-100">Belum ada IP Pool</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Buat IP Pool pertama atau import dari MikroTik Router.</p>
                            <div class="mt-6 flex justify-center gap-2">
                                <button wire:click="exportFromMikrotik" class="inline-flex items-center px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800 transition-colors">
                                    <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    Import dari Router
                                </button>
                                <a href="{{ route('isp.ip-pools.create') }}" class="inline-flex items-center px-3 py-2 border border-transparent rounded-xl text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 transition-colors" </a>
                        <button wire:click="closeSyncModal" type="button" x-on:click="open = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 dark:border-slate-600 shadow-sm px-4 py-2 bg-white dark:bg-slate-800 text-base font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>







