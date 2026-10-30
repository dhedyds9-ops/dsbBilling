@section('header_title', 'Perangkat Aktif & Modem')

<div class="p-4 sm:p-6 min-h-[calc(100vh-4rem)] pb-24 space-y-6 max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400">devices</span>
                Perangkat Aktif & Modem
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Status modem fiber optik (ONU) dan daftar perangkat yang terhubung ke jaringan internet Anda.
            </p>
        </div>
        <button 
            type="button"
            wire:click="refreshConnectedDevices" 
            wire:loading.attr="disabled"
            class="self-start sm:self-auto inline-flex items-center gap-2 px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-xl text-xs font-semibold transition-colors border border-indigo-200/60 dark:border-indigo-800 cursor-pointer shadow-sm"
        >
            <span wire:loading.remove wire:target="refreshConnectedDevices" class="material-symbols-outlined text-[18px]">sync</span>
            <span wire:loading wire:target="refreshConnectedDevices" class="material-symbols-outlined text-[18px] animate-spin">hourglass_empty</span>
            <span>Segarkan Perangkat</span>
        </button>
    </div>

    {{-- Alert Messages --}}
    @if($message)
        <div class="p-4 rounded-xl text-sm font-medium flex items-center gap-2.5 shadow-sm {{ $messageType === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800' }}">
            <span class="material-symbols-outlined text-[20px] shrink-0">{{ $messageType === 'success' ? 'check_circle' : 'error' }}</span>
            <span>{{ $message }}</span>
        </div>
    @endif

    {{-- Search Input --}}
    <div class="relative">
        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none">search</span>
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="Cari nama perangkat, IP, MAC, atau nomor seri..." 
            class="w-full pl-10 pr-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-slate-900 dark:text-slate-100 shadow-sm"
        >
    </div>

    {{-- PPPoE / ONU MODEMS SECTION --}}
    @if($pppoeItems->isNotEmpty())
        @foreach($pppoeItems as $modem)
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-all">
                {{-- Modem Header --}}
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-700/60 bg-gradient-to-r from-slate-50 to-white dark:from-slate-800/80 dark:to-slate-800 flex items-start justify-between gap-3 flex-wrap">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 shadow-sm">
                            <span class="material-symbols-outlined text-[24px]">router</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">
                                    {{ $modem->username }}
                                </h2>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 uppercase tracking-wider">
                                    PPPoE Fiber
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">
                                SN: {{ $modem->serial_number }} &bull; {{ $modem->model }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($modem->is_online)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 shadow-sm">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                ONLINE
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                OFFLINE
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Modem Diagnostic KPIs (from ONU & GenieACS) --}}
                <div class="p-4 sm:p-5">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800">
                        {{-- Redaman Fiber (RX Power) --}}
                        <div>
                            <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400 block uppercase tracking-wider">Redaman Fiber (RX)</span>
                            @if($modem->rx_power)
                                @php
                                    $rxNum = floatval($modem->rx_power);
                                    $rxColor = $rxNum < -26 ? 'text-rose-600 dark:text-rose-400' : ($rxNum < -23 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400');
                                @endphp
                                <span class="text-xs sm:text-sm font-bold font-mono {{ $rxColor }} block mt-0.5">
                                    {{ $modem->rx_power }}
                                </span>
                            @else
                                <span class="text-xs font-semibold text-slate-400 block mt-0.5">-</span>
                            @endif
                        </div>

                        {{-- Nama WiFi (SSID) --}}
                        <div>
                            <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400 block uppercase tracking-wider">Nama WiFi (SSID)</span>
                            <span class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 truncate block mt-0.5" title="{{ $modem->wifi_ssid ?? '-' }}">
                                {{ $modem->wifi_ssid ?? '-' }}
                            </span>
                        </div>

                        {{-- IP Address --}}
                        <div>
                            <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400 block uppercase tracking-wider">IP Address</span>
                            <span class="text-xs sm:text-sm font-bold font-mono text-slate-800 dark:text-slate-200 block mt-0.5">
                                {{ $modem->ip_address }}
                            </span>
                        </div>

                        {{-- Uptime --}}
                        <div>
                            <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400 block uppercase tracking-wider">Uptime Sesi</span>
                            <span class="text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 block mt-0.5">
                                {{ $modem->uptime }}
                            </span>
                        </div>
                    </div>

                    {{-- Quick Action Buttons for Modem --}}
                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        @if($modem->ppp_session_id)
                            <button 
                                wire:click="kickSession('pppoe', {{ $modem->ppp_session_id }})"
                                wire:confirm="Anda yakin ingin me-restart sesi koneksi internet modem ini? Modem akan otomatis terhubung kembali ke server."
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-900/20 dark:hover:bg-rose-900/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50 transition-colors cursor-pointer"
                                title="Restart Sesi PPPoE"
                            >
                                <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                                Restart Sesi Internet
                            </button>
                        @endif

                        <a 
                            href="{{ route('customer-portal.self-service.change-onu-wifi-password') }}" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-50 hover:bg-indigo-100 text-indigo-600 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/40 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50 transition-colors"
                        >
                            <span class="material-symbols-outlined text-[16px]">wifi_password</span>
                            Ganti Sandi WiFi
                        </a>
                    </div>
                </div>

                {{-- CONNECTED DEVICES LIST (FROM GENIEACS) --}}
                <div class="border-t border-slate-100 dark:border-slate-700/60 p-4 sm:p-5 bg-slate-50/50 dark:bg-slate-900/20">
                    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-500 text-[20px]">devices</span>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                                Perangkat yang Terhubung ke Modem
                            </h3>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">
                                {{ count($modem->connected_devices) }} Perangkat
                            </span>
                        </div>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400">
                            Data sinkron dari GenieACS TR-069
                        </span>
                    </div>

                    @if(!empty($modem->connected_devices))
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($modem->connected_devices as $dev)
                                @php
                                    $h = strtolower($dev['hostname'] ?? '');
                                    $isMobile = preg_match('/android|iphone|ipad|oppo|vivo|realme|samsung|galaxy|xiaomi|redmi|poco|infinix|tecno|huawei|honor/i', $h);
                                    $isLaptop = preg_match('/mac|windows|pc|laptop|desktop/i', $h);
                                    $isTv = preg_match('/tv|smart|box|mibox|chromecast|firetv|roku/i', $h);
                                @endphp
                                <div class="bg-white dark:bg-slate-800 rounded-xl p-3.5 border {{ $dev['is_blocked'] ? 'border-red-200 dark:border-red-800/60 bg-red-50/30' : 'border-slate-200 dark:border-slate-700/80' }} shadow-xs flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-10 h-10 rounded-xl {{ $dev['is_blocked'] ? 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400' : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300' }} flex items-center justify-center shrink-0">
                                            @if($isMobile)
                                                <span class="material-symbols-outlined text-[20px]">smartphone</span>
                                            @elseif($isLaptop)
                                                <span class="material-symbols-outlined text-[20px]">laptop_mac</span>
                                            @elseif($isTv)
                                                <span class="material-symbols-outlined text-[20px]">tv</span>
                                            @else
                                                <span class="material-symbols-outlined text-[20px]">devices_other</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100 truncate" title="{{ $dev['hostname'] }}">
                                                {{ $dev['hostname'] }}
                                            </p>
                                            <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400 font-mono mt-0.5">
                                                <span>{{ $dev['ip'] }}</span>
                                                <span>&bull;</span>
                                                <span class="truncate" title="{{ $dev['mac'] }}">{{ $dev['mac'] }}</span>
                                            </div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                                    {{ $dev['interface'] }}
                                                </span>
                                                @if($dev['is_blocked'])
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-600 dark:text-rose-400">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Diblokir
                                                    </span>
                                                @elseif($dev['active'])
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Terhubung
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium text-slate-400">
                                                        Offline
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Block / Unblock Action Button --}}
                                    @if(!empty($modem->genieacs_device_id) && $dev['mac'] !== 'Unknown')
                                        <div class="shrink-0">
                                            @if($dev['is_blocked'])
                                                <button 
                                                    type="button"
                                                    wire:click="unblockDevice('{{ $dev['mac'] }}', '{{ $modem->genieacs_device_id }}')" 
                                                    wire:confirm="Buka blokir WiFi untuk perangkat {{ $dev['mac'] }}?"
                                                    class="px-2.5 py-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 rounded-lg transition-colors border border-emerald-200 dark:border-emerald-800 cursor-pointer"
                                                >
                                                    Buka Blokir
                                                </button>
                                            @else
                                                <button 
                                                    type="button"
                                                    wire:click="blockDevice('{{ $dev['mac'] }}', '{{ $modem->genieacs_device_id }}')" 
                                                    wire:confirm="Yakin ingin memblokir akses perangkat {{ $dev['hostname'] }} ({{ $dev['mac'] }}) dari jaringan WiFi?"
                                                    class="px-2.5 py-1 text-[11px] font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 rounded-lg transition-colors border border-rose-200 dark:border-rose-800 cursor-pointer"
                                                    title="Blokir dari WiFi"
                                                >
                                                    Blokir
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-center bg-white dark:bg-slate-800 rounded-xl border border-dashed border-slate-200 dark:border-slate-700">
                            <span class="material-symbols-outlined text-slate-300 dark:text-slate-600 text-3xl mb-1">wifi_tethering_off</span>
                            <p class="text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300">Belum ada perangkat terdeteksi terhubung ke modem</p>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Pastikan WiFi modem aktif atau tekan tombol <strong>Segarkan Perangkat</strong> di atas.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @endif

    {{-- HOTSPOT ACTIVE SESSIONS SECTION (IF ANY) --}}
    @if($hotspotSessions->isNotEmpty())
        <div class="space-y-4">
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-500">wifi</span>
                Sesi Hotspot Aktif
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($hotspotSessions as $session)
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm border border-slate-100 dark:border-slate-700 relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-amber-500"></div>
                        <div class="pl-2">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2">
                                    <span class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300 uppercase">
                                        Hotspot
                                    </span>
                                    <span class="font-bold text-sm text-slate-900 dark:text-slate-100">{{ $session->username }}</span>
                                </div>
                                <span class="shrink-0 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> ONLINE
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 bg-slate-50 dark:bg-slate-900/50 rounded-xl p-3 mb-4 text-xs font-mono">
                                <div>
                                    <p class="text-[10px] text-slate-400 font-sans">IP Address</p>
                                    <p class="font-semibold text-slate-700 dark:text-slate-300">{{ $session->ip_address }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-slate-400 font-sans">MAC Address</p>
                                    <p class="font-semibold text-slate-700 dark:text-slate-300">{{ $session->mac_address }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-slate-400 font-sans">Uptime</p>
                                    <p class="font-semibold text-slate-700 dark:text-slate-300 font-sans">{{ $session->uptime }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-slate-400 font-sans">Router</p>
                                    <p class="font-semibold text-slate-700 dark:text-slate-300 font-sans">{{ $session->router }}</p>
                                </div>
                            </div>

                            <button 
                                wire:click="kickSession('hotspot', {{ $session->id }})"
                                wire:confirm="Anda yakin ingin memutus koneksi perangkat ini?"
                                class="w-full flex items-center justify-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-500/10 dark:hover:bg-red-500/20 py-2.5 rounded-xl text-xs font-semibold transition-colors cursor-pointer"
                            >
                                <span class="material-symbols-outlined text-[16px]">logout</span>
                                Putuskan Sesi
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- EMPTY STATE IF NO SESSIONS AT ALL --}}
    @if($pppoeItems->isEmpty() && $hotspotSessions->isEmpty())
        <div class="text-center py-16 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-700 flex items-center justify-center mx-auto mb-3 text-slate-400">
                <span class="material-symbols-outlined text-3xl">wifi_off</span>
            </div>
            <p class="text-slate-900 dark:text-slate-100 font-bold mb-1">Tidak ada perangkat atau layanan aktif</p>
            <p class="text-slate-500 dark:text-slate-400 text-xs px-6 max-w-md mx-auto">
                Saat ini belum ada sesi internet yang aktif atau modem fiber belum terhubung ke sistem.
            </p>
        </div>
    @endif
</div>
