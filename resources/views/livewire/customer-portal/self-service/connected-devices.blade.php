<div class="space-y-6">
    <div class="flex items-center gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Perangkat Terhubung</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Daftar perangkat yang terhubung ke WiFi rumah Anda</p>
        </div>
        <button wire:click="loadData" wire:loading.attr="disabled" class="ml-auto flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-800/50 transition-colors">
            <svg wire:loading.class="animate-spin" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
        </button>
    </div>

    @if($error)
    <div class="p-4 bg-red-50 dark:bg-red-900/30 border border-red-100 rounded-xl flex items-start gap-3">
        <div class="p-2 bg-red-100 dark:bg-red-900/50 rounded-lg shrink-0">
            <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div>
            <h3 class="font-medium text-red-800">Gagal memuat data</h3>
            <p class="text-sm text-red-600 mt-0.5">{{ $error }}</p>
        </div>
    </div>
    @endif

    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden shadow-sm relative">
        <!-- Loading State -->
        <div wire:loading wire:target="loadData" class="absolute inset-0 bg-white dark:bg-slate-800/60 backdrop-blur-sm z-10 flex items-center justify-center">
            <div class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-100 dark:border-slate-700">
                <svg class="w-5 h-5 animate-spin text-primary-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Memuat perangkat...</span>
            </div>
        </div>

        @if(!empty($devices))
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700">
                    <tr class="text-slate-500 dark:text-slate-400">
                        <th class="p-3 font-semibold">Perangkat</th>
                        <th class="p-3 font-semibold">IP Address</th>
                        <th class="p-3 font-semibold">MAC Address</th>
                        <th class="p-3 font-semibold">Status</th>
                        <th class="p-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @foreach($devices as $device)
                    <tr class="hover:bg-slate-50 dark:bg-slate-800/50 transition-colors {{ ($device['IsBlocked'] ?? false) ? 'bg-red-50 dark:bg-red-900/30/50' : '' }}">
                        <td class="p-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full {{ ($device['IsBlocked'] ?? false) ? 'bg-red-100 text-red-600' : 'bg-blue-50 dark:bg-blue-900/30 text-primary-600' }} flex items-center justify-center shrink-0">
                                @php
                                    $h = strtolower($device['HostName'] ?? '');
                                    $isMobile = preg_match('/android|iphone|ipad|oppo|vivo|realme|samsung|galaxy|xiaomi|redmi|poco|infinix|tecno|v\d{4}/i', $h);
                                    $isLaptop = preg_match('/mac|windows|pc|laptop|desktop/i', $h);
                                @endphp
                                @if($isMobile)
                                    <span class="material-symbols-outlined text-[22px]">smartphone</span>
                                @elseif($isLaptop)
                                    <span class="material-symbols-outlined text-[22px]">laptop_mac</span>
                                @else
                                    <span class="material-symbols-outlined text-[22px]">devices_other</span>
                                @endif
                                </div>
                                <div>
                                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ $device['HostName'] ?? 'Unknown Device' }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Host</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-3  text-slate-600 dark:text-slate-400 font-mono">{{ $device['IPAddress'] ?? '-' }}</td>
                        <td class="p-3  text-slate-600 dark:text-slate-400 font-mono">{{ $device['MACAddress'] ?? '-' }}</td>
                        <td class="p-3">
                            @if($device['IsBlocked'] ?? false)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/50 text-red-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    Diblokir
                                </span>
                            @elseif($device['Active'] ?? false)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Online
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    Offline
                                </span>
                            @endif
                        </td>
                        <td class="p-3  text-right">
                            @if($device['IsBlocked'] ?? false)
                                <button wire:click="unblockMac('{{ $device['MACAddress'] }}')" 
                                        wire:confirm="Anda yakin ingin membuka blokir perangkat ini?"
                                        class="px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 dark:bg-emerald-900/30 hover:bg-emerald-100 dark:bg-emerald-900/50 rounded-lg transition-colors border border-emerald-200">
                                    Buka Blokir
                                </button>
                            @else
                                <button wire:click="blockMac('{{ $device['MACAddress'] }}')" 
                                        wire:confirm="Anda yakin ingin memblokir perangkat ini dari WiFi?"
                                        class="px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:bg-red-900/50 rounded-lg transition-colors border border-red-200">
                                    Blokir
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @elseif(!$isLoading && !$error)
        <div class="p-12 text-center">
            <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800/50 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100 mb-1">Tidak Ada Perangkat</h3>
            <p class="text-slate-500 dark:text-slate-400">Belum ada perangkat yang terdeteksi terhubung ke WiFi Anda.</p>
        </div>
        @endif
    </div>
</div>






