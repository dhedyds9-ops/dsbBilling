<div>
    @section('page_title')
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-400">
                <span class="material-symbols-outlined notranslate" translate="no">cell_tower</span>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100 dark:text-white leading-tight">Session Online</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Monitoring koneksi aktif pelanggan PPPoE, Hotspot & Voucher.</p>
            </div>
        </div>
    @endsection

    <div class="space-y-4" wire:poll.60s>
        {{-- KPI CARDS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- PPPoE --}}
            <div wire:click="setActiveTab('pppoe')" class="relative overflow-x-auto rounded-xl border {{ $activeTab === 'pppoe' ? 'border-blue-500 shadow-md ring-1 ring-blue-500' : 'border-blue-200 dark:border-blue-800/60 shadow-sm' }} bg-gradient-to-br from-blue-50 to-white dark:from-blue-950/50 dark:to-slate-800 group hover:shadow-md transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-cyan-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-blue-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">router</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-blue-500 uppercase tracking-widest mb-2">PPPoE Online</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-800 dark:text-slate-100 tracking-tight">{{ number_format($stats['pppoe']) }}</span>
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Sesi</span>
                    </div>
                </div>
            </div>

            {{-- Hotspot --}}
            <div wire:click="setActiveTab('hotspot')" class="relative overflow-x-auto rounded-xl border {{ $activeTab === 'hotspot' ? 'border-green-500 shadow-md ring-1 ring-green-500' : 'border-green-200 dark:border-green-800/60 shadow-sm' }} bg-gradient-to-br from-green-50 to-white dark:from-green-950/50 dark:to-slate-800 group hover:shadow-md transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-green-500 to-emerald-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-green-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">wifi</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-green-500 uppercase tracking-widest mb-2">Hotspot Online</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-800 dark:text-slate-100 tracking-tight">{{ number_format($stats['hotspot']) }}</span>
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Sesi</span>
                    </div>
                </div>
            </div>

            {{-- Voucher --}}
            <div wire:click="setActiveTab('voucher')" class="relative overflow-x-auto rounded-xl border {{ $activeTab === 'voucher' ? 'border-purple-500 shadow-md ring-1 ring-purple-500' : 'border-purple-200 dark:border-purple-800/60 shadow-sm' }} bg-gradient-to-br from-purple-50 to-white dark:from-purple-950/50 dark:to-slate-800 group hover:shadow-md transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-fuchsia-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-purple-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">confirmation_number</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-purple-500 uppercase tracking-widest mb-2">Voucher Online</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-800 dark:text-slate-100 tracking-tight">{{ number_format($stats['voucher']) }}</span>
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Sesi</span>
                    </div>
                </div>
            </div>

            {{-- Total --}}
            <div class="relative overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm bg-gradient-to-br from-slate-50 to-white dark:from-slate-800 dark:to-slate-900">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-slate-400 to-slate-300 dark:from-slate-600 dark:to-slate-500 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-slate-400 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">group</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Total Semua</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-800 dark:text-slate-100 tracking-tight">{{ number_format($stats['total']) }}</span>
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Sesi</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- TOOLBAR --}}
        <div class="flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex-1 w-full relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 20px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari User, IP, Router..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all dark:bg-slate-900 dark:text-slate-100">
            </div>
            <div class="flex items-center gap-3">
                <button wire:click="syncRouters" wire:loading.attr="disabled" class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer disabled:opacity-50">
                    <span class="material-symbols-outlined notranslate text-sm" wire:loading.class="animate-spin" translate="no">sync</span>
                    <span wire:loading.remove>Sinkronkan MikroTik</span>
                    <span wire:loading>Menyinkronkan...</span>
                </button>
                <div class="text-sm text-slate-500 dark:text-slate-400">
                    Menampilkan <span class="font-bold text-slate-700 dark:text-slate-300">{{ ucfirst($activeTab) }}</span> online
                </div>
            </div>
        </div>

        {{-- DATA TABLE --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/80">
                        <th class="px-3 py-3 text-center font-semibold whitespace-nowrap w-16">Aksi</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Nama Pelanggan</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Akun (Username)</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Paket</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Owner / Reseller</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Router</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Alamat IP</th>
                        @if($activeTab !== 'pppoe')
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">MAC Address</th>
                        @endif
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Uptime</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Traffic (In/Out)</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Mulai Session</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Aktif Sampai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($results as $session)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-3 py-3 whitespace-nowrap text-center">
                                @if($activeTab === 'pppoe')
                                    <button wire:click="kickPppoe({{ $session->id }})" wire:confirm="Yakin ingin memutuskan koneksi sesi PPPoE ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/30 dark:hover:bg-red-900/50 dark:text-red-400 transition-colors cursor-pointer shadow-sm" title="Putuskan Sesi (Kick)">
                                        <span class="material-symbols-outlined notranslate" style="font-size:18px" translate="no">power_settings_new</span>
                                    </button>
                                @else
                                    <button wire:click="kickHotspot({{ $session->id }})" wire:confirm="Yakin ingin memutuskan koneksi sesi Hotspot ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/30 dark:hover:bg-red-900/50 dark:text-red-400 transition-colors cursor-pointer shadow-sm" title="Putuskan Sesi (Kick)">
                                        <span class="material-symbols-outlined notranslate" style="font-size:18px" translate="no">power_settings_new</span>
                                    </button>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                                @if($activeTab === 'pppoe')
                                    {{ $session->pppoeUser?->customerService?->customer?->name ?? 'Anonim / Tidak Ditemukan' }}
                                @elseif($activeTab === 'voucher')
                                    <span class="italic text-slate-500 dark:text-slate-400">Pengguna Voucher</span>
                                @else
                                    {{ $session->hotspotUser?->customerService?->customer?->name ?? 'Anonim / Tidak Ditemukan' }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 font-mono text-xs font-semibold">
                                @if($activeTab === 'pppoe')
                                    {{ $session->name }}
                                @else
                                    {{ $session->user }}
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($activeTab === 'pppoe')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                        {{ $session->pppoeUser?->serviceProfile?->name ?? $session->pppoeUser?->customerService?->serviceProfile?->name ?? 'PPPoE' }}
                                    </span>
                                @elseif($activeTab === 'voucher')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">
                                        {{ $session->voucher?->serviceProfile?->name ?? $session->voucher?->voucherPool?->serviceProfile?->name ?? 'Voucher' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                        {{ $session->hotspotUser?->serviceProfile?->name ?? $session->hotspotUser?->customerService?->serviceProfile?->name ?? 'Hotspot' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                @if($activeTab === 'pppoe')
                                    @php
                                        $owner = $session->pppoeUser?->reseller?->name ?? $session->pppoeUser?->customer?->reseller?->name ?? $session->pppoeUser?->customerService?->customer?->reseller?->name ?? 'Pusat (HQ)';
                                    @endphp
                                    <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined notranslate text-slate-400" style="font-size:14px" translate="no">storefront</span> {{ $owner }}</span>
                                @elseif($activeTab === 'voucher')
                                    <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined notranslate text-slate-400" style="font-size:14px" translate="no">storefront</span> {{ $session->voucher?->reseller?->name ?? 'Pusat (HQ)' }}</span>
                                @else
                                    @php
                                        $owner = $session->hotspotUser?->reseller?->name ?? $session->hotspotUser?->customer?->reseller?->name ?? $session->hotspotUser?->customerService?->customer?->reseller?->name ?? 'Pusat (HQ)';
                                    @endphp
                                    <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined notranslate text-slate-400" style="font-size:14px" translate="no">storefront</span> {{ $owner }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                {{ $session->router?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 font-mono">
                                {{ $session->address }}
                            </td>
                            @if($activeTab !== 'pppoe')
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400 font-mono">
                                    {{ $session->mac_address ?? '-' }}
                                </td>
                            @endif
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 14px">schedule</span>
                                    {{ $session->uptime }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs font-mono">
                                <span class="text-blue-600 dark:text-blue-400">{{ number_format($session->bytes_in) }}</span> / 
                                <span class="text-green-600 dark:text-green-400">{{ number_format($session->bytes_out) }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 text-xs whitespace-nowrap">
                                @php
                                    $startedAt = $session->session_started_at 
                                        ?? ($activeTab === 'voucher' ? $session->voucher?->activated_at : null)
                                        ?? ($activeTab === 'hotspot' ? $session->hotspotUser?->activated_at : null)
                                        ?? ($activeTab === 'pppoe' ? $session->pppoeUser?->activated_at : null);
                                @endphp
                                {{ $startedAt ? $startedAt->format('d/m/Y H:i:s') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 text-xs whitespace-nowrap">
                                @php
                                    $expiresAt = match($activeTab) {
                                        'pppoe' => $session->pppoeUser?->expires_at,
                                        'voucher' => $session->voucher?->expires_at,
                                        default => $session->hotspotUser?->expires_at,
                                    };
                                @endphp
                                @if($expiresAt)
                                    <span class="inline-flex items-center gap-1 font-semibold text-slate-700 dark:text-slate-200">
                                        <span class="material-symbols-outlined notranslate text-emerald-500" style="font-size:14px" translate="no">event_available</span>
                                        {{ $expiresAt->format('d/m/Y H:i') }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $activeTab === 'pppoe' ? 11 : 12 }}" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined notranslate mb-2 text-slate-300 dark:text-slate-600 dark:text-slate-400" translate="no" style="font-size:48px">cell_tower</span>
                                    <p class="text-lg font-medium text-slate-900 dark:text-slate-100 mt-2">Belum ada Sesi Aktif</p>
                                    <p class="text-sm mt-1">Tidak ada pelanggan {{ ucfirst($activeTab) }} yang sedang online saat ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($results->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    {{ $results->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
