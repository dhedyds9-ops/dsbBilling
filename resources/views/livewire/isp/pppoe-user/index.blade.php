@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">dialpad</span>
    <span class="text-lg">Daftar PPPoE</span>
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

    {{-- KPI CARDS --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        {{-- Total --}}
        <div class="relative overflow-x-auto rounded-xl border border-indigo-200 dark:border-indigo-800/60 shadow-md bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-indigo-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">dialpad</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-widest mb-2">Total PPPoE</h3>
                <div class="text-4xl font-black text-indigo-700 dark:text-indigo-300 mb-3">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Semua User PPPoE</div>
            </div>
        </div>
        
        {{-- Aktif --}}
        <div wire:click="$set('filters.status', 'active')" class="relative overflow-x-auto rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-md bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800 group hover:shadow-lg transition-all cursor-pointer">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-emerald-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">wifi</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-2">Aktif (Berlangganan)</h3>
                <div class="text-4xl font-black text-emerald-700 dark:text-emerald-300 mb-3">{{ number_format($stats['active'] ?? 0) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Klik untuk memfilter</div>
            </div>
        </div>

        {{-- Online (Jaringan) --}}
        <div wire:click="$set('filters.status', 'online')" class="relative overflow-x-auto rounded-xl border border-cyan-200 cursor-pointer dark:border-cyan-800/60 shadow-md bg-gradient-to-br from-cyan-50 to-white dark:from-cyan-950/50 dark:to-slate-800 group transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-cyan-500 to-blue-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-cyan-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">rss_feed</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-cyan-500 dark:text-cyan-400 uppercase tracking-widest mb-2">Online (Jaringan)</h3>
                <div class="text-4xl font-black text-cyan-700 dark:text-cyan-300 mb-3">{{ number_format($stats['online'] ?? 0) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Live dari Mikrotik</div>
            </div>
        </div>

        {{-- Offline (Jaringan) --}}
        <div wire:click="$set('filters.status', 'offline')" class="relative overflow-x-auto rounded-xl border border-slate-200 cursor-pointer dark:border-slate-700/60 shadow-md bg-gradient-to-br from-slate-50 to-white dark:from-slate-900/50 dark:to-slate-800 group transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-slate-400 to-slate-500 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-slate-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">wifi_off</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Offline (Jaringan)</h3>
                <div class="text-4xl font-black text-slate-700 dark:text-slate-300 mb-3">{{ number_format(max(0, ($stats['active'] ?? 0) - ($stats['online'] ?? 0))) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Live dari Mikrotik</div>
            </div>
        </div>
        


        {{-- Suspend --}}
        <div wire:click="$set('filters.status', 'suspended')" class="relative overflow-x-auto rounded-xl border border-red-200 dark:border-red-800/60 shadow-md bg-gradient-to-br from-red-50 to-white dark:from-red-950/50 dark:to-slate-800 group hover:shadow-lg transition-all cursor-pointer">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 to-rose-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-red-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">gpp_bad</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-red-500 dark:text-red-400 uppercase tracking-widest mb-2">Suspend / Isolir</h3>
                <div class="text-4xl font-black text-red-700 dark:text-red-300 mb-3">{{ number_format($stats['suspended'] ?? 0) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Klik untuk memfilter</div>
            </div>
        </div>
    </div>

    {{-- TOOLBAR & FILTER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('isp.pppoe-users.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm shadow-indigo-200 dark:shadow-none transition-all">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">add</span>
                Tambah
            </a>
            <button wire:click="export" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg shadow-sm transition-all">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">download</span>
                Export
            </button>
            <button wire:click="openImportModal" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg shadow-sm transition-all">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">upload</span>
                Import
            </button>
        </div>
        
        <div class="flex items-center gap-2">
            <div class="flex-1 relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Cari username..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
            </div>
            <select wire:model.live="filters.status"
                    class="pl-3 pr-10 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="">Semua Status</option>
                <option value="active">Aktif (Semua)</option>
                <option value="online">Aktif & Online</option>
                <option value="offline">Aktif & Offline</option>
                <option value="suspended">Suspend / Isolir</option>
            </select>
            <select wire:model.live="perPage"
                    class="pl-3 pr-10 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="20">20 / halaman</option>
                <option value="50">50 / halaman</option>
                <option value="100">100 / halaman</option>
                <option value="500">500 / halaman</option>
                <option value="999999">Semua</option>
            </select>
            @if($search || $filters['status'])
            <button wire:click="resetFilters"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:bg-red-900/50 dark:hover:bg-red-800/50 text-red-600 dark:text-red-400 rounded-lg text-sm font-medium transition-colors cursor-pointer">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">filter_alt_off</span>
                Reset
            </button>
            @endif
        </div>
    </div>

    {{-- DATA TABLE --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/80 dark:bg-slate-800/80">
                        <th class="px-4 py-3 text-left font-semibold">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                        </th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">
                            <button wire:click="sortBy('id')" class="flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer">
                                ID
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">unfold_more</span>
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Nama</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Username</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Password</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Layanan</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Paket</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Ip Address</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Owner/Reseller</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">WhatsApp</th>
                        <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">
                            <button wire:click="sortBy('created_at')" class="flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer">
                                Tgl Register
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">unfold_more</span>
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Tgl Login</th>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Tgl Logout</th>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Tgl Isolir</th>
                        <th class="px-4 py-3 text-right font-semibold whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($pppoeUsers as $user)
                        @php
                            $isOnline = $user->is_online ?? false;
                            $isSuspend = $user->status === 'suspended';
                            $dynamicId = $user->customer ? (($user->customer->created_at ? $user->customer->created_at->format('Ymd') : date('Ymd')) . str_pad($user->customer->id % 100, 2, '0', STR_PAD_LEFT)) : '-';
                            $layanan = 'PPPoE';
                            $tglLogin = $user->latestAccounting?->acct_start_time ? $user->latestAccounting->acct_start_time->format('d-m-Y H:i') : '-';
                            $tglLogout = $user->latestAccounting?->acct_stop_time ? $user->latestAccounting->acct_stop_time->format('d-m-Y H:i') : '-';
                            
                            $rowBg = 'hover:bg-slate-50 dark:bg-slate-900/50/50 dark:hover:bg-slate-700/20 transition-colors';
                            $textPrimary = 'text-slate-900 dark:text-slate-100 font-medium';
                            $textSecondary = 'text-slate-600 dark:text-slate-300';
                            $idColor = 'text-slate-700 dark:text-slate-300';
                            
                            if ($isSuspend) {
                                $rowBg = 'bg-red-50/40 hover:bg-red-100/60 dark:bg-red-900/10 dark:hover:bg-red-900/20 transition-colors grayscale-[20%]';
                                $textPrimary = 'text-red-900 dark:text-red-100 font-semibold';
                                $textSecondary = 'text-red-700 dark:text-red-300';
                                $idColor = 'text-red-800 dark:text-red-400';
                            } elseif ($isOnline) {
                                $rowBg = 'bg-emerald-50/30 hover:bg-emerald-100/50 dark:bg-emerald-900/10 dark:hover:bg-emerald-900/20 transition-colors';
                                $textPrimary = 'text-emerald-900 dark:text-emerald-100 font-semibold';
                                $textSecondary = 'text-emerald-700 dark:text-emerald-300';
                                $idColor = 'text-emerald-800 dark:text-emerald-400';
                            }
                        @endphp
                        <tr class="{{ $rowBg }}">
                            <td class="px-4 py-3">
                                <input type="checkbox" wire:model.live="selectedIds" value="{{ $user->id }}" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-slate-700 dark:border-slate-600">
                            </td>
                            <td class="px-4 py-3 font-mono text-xs {{ $idColor }}">
                                  <div class="flex items-center gap-2">
                                      <input type="checkbox" wire:change="toggleStatus({{ $user->id }})" 
                                             class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                             {{ $user->status === 'active' ? 'checked' : '' }}
                                             title="Toggle Aktif/Suspend">
                                      {{ $dynamicId }}
                                  </div>
                              </td>
                            <td class="px-4 py-3 {{ $textPrimary }}">
                                @if(isset($user->customer->id))
                                    <a href="{{ route('crm.customers.show', $user->customer->id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline transition-colors">
                                        {{ $user->customer->name ?? '-' }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold {{ $isSuspend ? 'text-red-700 dark:text-red-400' : 'text-indigo-600 dark:text-indigo-400' }} font-mono">{{ $user->username }}</span>
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $user->username }}'); alert('Username disalin!')" class="text-slate-400 hover:text-indigo-500 transition-colors" title="Salin Username">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">content_copy</span>
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-3" x-data="{ showPw: false }">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-mono text-slate-500 dark:text-slate-400" x-show="!showPw">••••••••</span>
                                    <span class="text-xs font-mono {{ $textSecondary }}" x-show="showPw" x-cloak>{{ $user->password }}</span>
                                    <button type="button" @click="showPw = !showPw" class="text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-300 transition-colors" title="Lihat/Sembunyikan Password">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px" x-text="showPw ? 'visibility_off' : 'visibility'"></span>
                                    </button>
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $user->password }}'); alert('Password disalin!')" class="text-slate-400 hover:text-indigo-500 transition-colors" title="Salin Password">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">content_copy</span>
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold {{ $layanan === 'PPPoE' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400' }}">
                                    {{ $layanan }}
                                </span>
                            </td>
                            <td class="px-4 py-3 {{ $textSecondary }}">
                                {{ $user->serviceProfile->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 {{ $textSecondary }} font-mono text-xs">
                                {{ $user->static_ip ?? $user->ip_address ?? 'Dynamic' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($user->reseller)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-400">
                                        {{ $user->reseller->name }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400">
                                        Pusat (HQ)
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 {{ $textSecondary }}">
                                {{ $user->customer->phone ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if($user->status === 'suspended')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-rose-700 text-white shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-300"></span>
                                        Suspend
                                    </span>
                                @elseif($isOnline)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-green-500 text-white shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white dark:bg-slate-800 animate-pulse"></span>
                                        Online
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-red-500 text-white shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-200"></span>
                                        Offline
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 {{ $textSecondary }} text-xs">
                                {{ $user->created_at ? $user->created_at->format('d-m-Y') : '-' }}
                            </td>
                            <td class="px-4 py-3 {{ $textSecondary }} text-xs font-mono">
                                {{ $tglLogin }}
                            </td>
                            <td class="px-4 py-3 {{ $textSecondary }} text-xs font-mono">
                                {{ $tglLogout }}
                            </td>
                            <td class="px-4 py-3 {{ $textSecondary }} text-xs">
                                {{ $user->subscription->next_billing_date ? \Carbon\Carbon::parse($user->subscription->next_billing_date)->format('d-m-Y') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('isp.pppoe-users.edit', $user->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 transition-colors" title="Edit">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                                    </a>
                                    @if(isset($user->customer->id))
                                        <a href="{{ route('crm.customers.show', $user->customer->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-sky-50 hover:bg-sky-100 dark:bg-sky-900/30 dark:hover:bg-sky-900/50 text-sky-600 dark:text-sky-400 transition-colors" title="Portal Customer 360">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">account_circle</span>
                                        </a>
                                    @endif
                                    <button wire:click="delete({{ $user->id }}, null)" onclick="confirm('Yakin ingin menghapus user ini?') || event.stopImmediatePropagation()" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 transition-colors" title="Hapus">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined notranslate mb-2 text-slate-300 dark:text-slate-600 dark:text-slate-400" translate="no" style="font-size:48px">folder_open</span>
                                    <p>Belum ada data PPPoE User</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pppoeUsers->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                {{ $pppoeUsers->links() }}
            </div>
        @endif
    </div>

    {{-- IMPORT MODAL --}}
    @if($showImportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm transition-opacity">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Import Data PPPoE</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Unggah file Excel (.xlsx, .csv) dari sistem radius lama Anda (mendukung format MixRadius/MSRadius/dsBilling).</p>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Pilih File Excel</label>
                        <input type="file" wire:model="importFile" accept=".xlsx,.xls,.csv" class="w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:bg-indigo-900/30 file:text-indigo-700 hover:file:bg-indigo-100 dark:bg-indigo-900/50 dark:file:bg-indigo-900/30 dark:file:text-indigo-400 transition-all cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                        @error('importFile') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-8">
                        <button wire:click="closeImportModal" class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-lg transition-colors">
                            Batal
                        </button>
                        <button wire:click="import" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                            <span wire:loading.remove wire:target="import" class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">upload</span>
                            <span wire:loading wire:target="import" class="material-symbols-outlined notranslate mr-1.5 animate-spin" translate="no" style="font-size:18px">sync</span>
                            <span wire:loading.remove wire:target="import">Proses Import</span>
                            <span wire:loading wire:target="import">Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
