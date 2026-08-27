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

    <!-- HEADER (Pola PPPoE: Judul Kiri + Dropdown Manajemen Kanan) -->
    <div class="flex justify-between items-center mb-3 gap-3 flex-wrap">
        <div x-data="{ open: false }" class="relative inline-block text-left">
            <div>
                <button @click="open = !open" type="button" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium shadow-sm shadow-primary-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    Manajemen Grup Profile
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
            <div x-show="open" @click.away="open = false" x-transition class="origin-top-right absolute right-0 mt-2 w-64 rounded-lg shadow-lg bg-white dark:bg-slate-800 ring-1 ring-black ring-opacity-5 dark:ring-white/10 divide-y divide-gray-100 dark:divide-slate-700 z-50">
                <div class="py-1">
                    <button type="button" wire:click="openCreateModal" @click="open = false" class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Grup Profile
                    </button>
                </div>
                <div class="py-1">
                    <button type="button" wire:click="exportFromMikrotik" @click="open = false" class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import dari Router MikroTik
                    </button>
                    <button type="button" wire:click="openSyncModal" @click="open = false" class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Export ke Router [NAS]
                    </button>
                    <button type="button" onclick="window.print()" @click="open = false" class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export CSV / Print
                    </button>
                </div>
                <div class="py-1">
                    <button
                        type="button"
                        wire:click="bulkDelete"
                        wire:confirm="Hapus grup profile yang terpilih?"
                        @if(empty($selectedIds)) disabled @endif
                        class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left @if(empty($selectedIds)) text-gray-300 dark:text-slate-600 cursor-not-allowed @else text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 @endif">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Hapus Yang Dipilih
                        @if(!empty($selectedIds))
                        <span class="ml-auto px-1.5 py-0.5 bg-red-100 dark:bg-red-900/40 rounded text-xs font-bold text-red-700 dark:text-red-300">{{ count($selectedIds) }}</span>
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- BADGE SELECTED (Pola PPPoE) -->
    @if(!empty($selectedIds))
        <div class="mb-3 flex items-center gap-2">
            <span class="px-3 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-800 dark:text-primary-200 rounded-full text-sm font-medium">
                {{ count($selectedIds) }} Grup Profile Dipilih
            </span>
            <button wire:click="selectedIds = []; selectAll = false" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                Batal Pilih
            </button>
        </div>
    @endif

    <!-- TOOLBAR (Pola PPPoE: x-base.card) -->
    <x-base.card class="mb-3">
        <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
            <div class="w-full lg:w-1/3">
                <label for="searchGroup" class="sr-only">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input id="searchGroup" wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" placeholder="Cari nama grup, kode, atau IP range...">
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
                        <th scope="col" class="px-6 py-3 text-left">
                            <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500" />
                        </th>
                        <th scope="col" wire:click="sortBy('name')" class="cursor-pointer px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">
                            <div class="flex items-center gap-1">Nama Grup <span class="text-gray-300 dark:text-slate-600">⇅</span></div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Tipe</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Modul</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">IP Range</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Router [NAS]</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-700">
                    @forelse($groups as $g)
                        @php
                            $isHotspot = !$g->start_ip;
                            $typeLabel = $isHotspot ? 'HOTSPOT' : 'PPP';
                            $module = $isHotspot ? 'GROUP ONLY' : 'mikrotik-ippool';
                            $ownerName = $g->createdBy->username ?? ($g->createdBy->name ?? '-');
                            $routerName = $g->pop->name ?? 'Belum di-assign';
                        @endphp
                        <tr wire:key="{{ $g->id }}" class="hover:bg-gray-50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" wire:model="selectedIds" value="{{ $g->id }}" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow
                                        @if($isHotspot) bg-gradient-to-br from-emerald-500 to-teal-500 text-white
                                        @else bg-gradient-to-br from-blue-500 to-indigo-500 text-white @endif">
                                        <span class="text-sm font-bold">{{ strtoupper(substr($typeLabel, 0, 1)) }}</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $g->name }}</div>
                                        <div class="text-xs text-gray-400 dark:text-slate-500 font-mono">{{ $g->code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 inline-flex text-[11px] leading-4 font-bold rounded-full uppercase tracking-wider
                                    @if($isHotspot) bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300
                                    @else bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 @endif">
                                    {{ $typeLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 inline-flex text-[11px] leading-4 font-bold rounded-full
                                    @if($isHotspot) bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200
                                    @else bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 @endif">
                                    {{ $module }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($g->start_ip && $g->end_ip)
                                    <div class="font-mono">
                                        <span class="text-primary-600 dark:text-primary-400 font-semibold">{{ $g->start_ip }}</span>
                                        <span class="text-gray-400"> — </span>
                                        <span class="text-success-600 dark:text-success-400 font-semibold">{{ $g->end_ip }}</span>
                                    </div>
                                    <div class="text-[11px] text-gray-400 dark:text-slate-500 mt-0.5">
                                        Gateway: {{ $g->gateway ?? '-' }} · {{ $g->total_ips ? $g->total_ips . ' IPs' : '' }}
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-slate-500 italic">Hotspot Group Only</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-orange-500"></div>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $routerName }}</span>
                                </div>
                                @if($g->pop->host ?? null)
                                    <div class="text-[11px] font-mono text-gray-400 dark:text-slate-500 pl-4">{{ $g->pop->host }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <!-- Soft Icon Button (Pola Profile Hotspot) -->
                                <div class="inline-flex items-center gap-1">
                                    <button wire:click="openEditModal({{ $g->id }})" title="Edit" type="button" class="p-2 rounded-lg text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button wire:click="syncToMikrotik({{ $g->id }})" title="Export ke Router" type="button" class="p-2 rounded-lg text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-900/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    </button>
                                    <button wire:click="delete({{ $g->id }})" wire:confirm="Yakin hapus grup profile {{ $g->name }}?" title="Hapus" type="button" class="p-2 rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400 dark:text-slate-500">
                                <svg class="w-10 h-10 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                Belum ada grup profile.
                                <div class="mt-2 text-sm text-gray-500 dark:text-slate-400">Gunakan dropdown <b>Manajemen Grup Profile → Tambah Grup Profile</b> atau Import dari Router.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($groups, 'hasPages') && $groups->hasPages())
            <div class="p-4 border-t border-gray-200 dark:border-slate-700">{{ $groups->links() }}</div>
        @endif
    </x-base.card>

    <!-- MODAL CREATE / EDIT -->
    @if($showCreateModal)
    <div x-data="{ open: true }" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 sm:p-0">
            <div x-show="open" x-transition class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
            <div class="inline-block w-full max-w-3xl p-6 my-8 text-left align-middle transition-all transform bg-white dark:bg-slate-800 shadow-2xl rounded-2xl">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    {{ $editingId ? 'Edit' : 'Tambah' }} Grup Profile
                </h3>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Nama Grup Profile</label>
                            <input type="text" wire:model="form.name" class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm" placeholder="Contoh: MSTORE.NET_PPPOE / MSTORE.NET">
                            @error('form.name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Kode</label>
                            <input type="text" wire:model="form.code" class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm font-mono uppercase">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-primary-600 mb-1">Tipe Group</label>
                            <select wire:model.live="form.type" class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
                                <option value="ppp">PPP (PPPoE)</option>
                                <option value="hotspot">HOTSPOT</option>
                            </select>
                            @error('form.type') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-purple-600 mb-1">Modul</label>
                            <input type="text" wire:model="form.module" readonly class="w-full rounded-lg border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 text-gray-500 dark:text-slate-400 text-sm font-mono cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1">Parent Pool</label>
                            <select wire:model="form.parent_pool_id" class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
                                <option value="">NONE</option>
                                @foreach($parentPools as $pp)
                                <option value="{{ $pp->id }}">{{ $pp->name }} ({{ $pp->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1">Owner Data</label>
                            <select wire:model="form.owner_user_id" class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
                                @foreach($owners as $o)
                                <option value="{{ $o->id }}">{{ $o->username ?? $o->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-orange-600 mb-1">Router [NAS]</label>
                        <select wire:model="form.router_id" class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
                            <option value="">Pilih Router / NAS</option>
                            @foreach($routers as $r)
                            <option value="{{ $r->id }}">{{ $r->name }} — {{ $r->ip_address }}</option>
                            @endforeach
                        </select>
                        @error('form.router_id') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    @if($form['type'] === 'ppp')
                    <div class="bg-cyan-50 dark:bg-cyan-900/10 border border-cyan-200 dark:border-cyan-800 p-4 rounded-xl">
                        <div class="text-xs font-bold text-cyan-700 dark:text-cyan-300 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            KONFIGURASI IP POOL (Modul: mikrotik-ippool)
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">IP Lokal (Gateway)</label>
                                <input type="text" wire:model="form.gateway" class="w-full rounded-lg border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-white text-sm font-mono" placeholder="10.100.112.1">
                                @error('form.gateway') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">IP Pertama</label>
                                <input type="text" wire:model="form.start_ip" class="w-full rounded-lg border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-white text-sm font-mono" placeholder="10.100.112.2">
                                @error('form.start_ip') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">IP Terakhir</label>
                                <input type="text" wire:model="form.end_ip" class="w-full rounded-lg border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-white text-sm font-mono" placeholder="10.100.115.254">
                                @error('form.end_ip') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Netmask</label>
                                <input type="text" wire:model="form.netmask" class="w-full rounded-lg border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-white text-sm font-mono" placeholder="255.255.252.0">
                                @error('form.netmask') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Network (Opsional)</label>
                                <input type="text" wire:model="form.network" class="w-full rounded-lg border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-white text-sm font-mono" placeholder="10.100.112.0">
                            </div>
                            <div class="flex items-end">
                                <div class="text-xs text-cyan-700 dark:text-cyan-300 bg-white dark:bg-slate-800 p-2 rounded border border-cyan-200 dark:border-cyan-800 w-full">
                                    <div class="font-semibold">Total IPs Auto:</div>
                                    @if($form['start_ip'] && $form['end_ip'])
                                    <div class="font-mono text-base font-bold">
                                        @php
                                            $totalCalc = intval(ip2long($form['end_ip']) - ip2long($form['start_ip']) + 1);
                                        @endphp
                                        {{ $totalCalc > 0 ? $totalCalc : 0 }} IP
                                    </div>
                                    @else
                                    <div class="text-gray-400 dark:text-slate-500">— Isi IP range</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200 dark:border-emerald-800 p-4 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-500 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                            </div>
                            <div class="text-sm text-emerald-800 dark:text-emerald-200">
                                <div class="font-bold">Tipe HOTSPOT = Module GROUP ONLY</div>
                                <div class="text-emerald-800/80 dark:text-emerald-200/80 text-xs">
                                    Untuk Hotspot, manajemen IP pool dilakukan di DHCP Server MikroTik. Grup Profile hanya sebagai container grouping user voucher & member hotspot per NAS.
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
                        {{ $editingId ? 'Simpan Perubahan' : 'Simpan Grup Profile' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- MODAL SYNC MIKROTIK -->
    @if($showSyncModal)
    <div x-data="{ open: true }" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 sm:p-0">
            <div x-show="open" x-transition class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
            <div class="inline-block w-full max-w-md p-6 my-8 text-left align-middle transition-all transform bg-white dark:bg-slate-800 shadow-2xl rounded-2xl">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Export Grup Profile ke Router [NAS]
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Pilih Router Tujuan</label>
                        <select wire:model="selectedRouterId" class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
                            <option value="">Semua Router (Export ke seluruh NAS)</option>
                            @foreach($routers as $r)
                                <option value="{{ $r->id }}">{{ $r->name }} — {{ $r->ip_address }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-[11px] space-y-1 bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-200 p-3 rounded-lg">
                        <div class="font-bold">Yang di-Export:</div>
                        <div>• Tipe PPP → <b>/ip pool</b> + <b>/ppp profile</b></div>
                        <div>• Tipe Hotspot → <b>/ip hotspot user profile</b></div>
                        <div>• IP Range otomatis sync ke mikrotik-ippool</div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="closeSyncModal" type="button" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-slate-300 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 rounded-lg">Batal</button>
                    <button wire:click="syncToMikrotik" type="button" class="px-5 py-2.5 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg shadow-sm shadow-primary-500/20">Export Sekarang</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
