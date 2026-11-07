@section('header_title', 'Diagnostik ONT & Jaringan')

<div class="space-y-4 p-4 pb-24" x-data="diagnosticManager()">
    {{-- Alerts --}}
    @if(session('success') || !empty($successMessage))
        <div class="p-3.5 bg-emerald-500 text-white rounded-2xl text-xs font-semibold flex items-center gap-2.5 shadow-sm animate-fadeIn">
            <span class="material-symbols-outlined text-sm">check_circle</span>
            <span>{{ session('success') ?? $successMessage }}</span>
        </div>
    @endif

    @if(session('error') || !empty($errorMessage))
        <div class="p-3.5 bg-rose-500 text-white rounded-2xl text-xs font-semibold flex items-center gap-2.5 shadow-sm animate-fadeIn">
            <span class="material-symbols-outlined text-sm">error</span>
            <span>{{ session('error') ?? $errorMessage }}</span>
        </div>
    @endif

    {{-- Header Banner --}}
    <div class="p-4 rounded-2xl shadow-md relative overflow-hidden text-white"
         style="background: linear-gradient(135deg, #4338ca 0%, #0369a1 50%, #1e40af 100%) !important; color: #ffffff !important;">
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/20 backdrop-blur-md text-[10px] font-bold uppercase tracking-wider text-white">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Live Diagnostics</span>
                </span>
                <h2 class="text-lg font-black mt-1 text-white">Diagnostik ONT & Jaringan</h2>
                <p class="text-xs text-sky-100">Cek Redaman Optik, Status Sinyal, Reboot & Perangkat Terhubung</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/20 shrink-0">
                <span class="material-symbols-outlined text-2xl text-white notranslate" translate="no">cell_tower</span>
            </div>
        </div>
    </div>

    {{-- PENCARIAN ONT / PELANGGAN --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-3.5 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 flex items-center gap-1.5 uppercase tracking-wider">
                <span class="material-symbols-outlined text-sky-500 text-base">search</span>
                <span>Pilih Perangkat ONT Pelanggan</span>
            </span>
            @if($selectedOnuId)
                <button wire:click="resetSelection" type="button"
                        class="text-[11px] font-bold text-sky-600 dark:text-sky-400 hover:underline">
                    Ganti Perangkat
                </button>
            @endif
        </div>

        @if(!$selectedOnuId)
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input type="text" wire:model.live.debounce.300ms="searchQuery"
                           placeholder="Ketik Nama Pelanggan / SN ONT / PPPoE..."
                           class="w-full pl-10 pr-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-sky-500">
                </div>
                <button type="button" @click="openScanner()" title="Scan Barcode / SN ONT"
                        class="px-3.5 py-2.5 bg-sky-50 hover:bg-sky-100 dark:bg-sky-950/60 dark:hover:bg-sky-900 text-sky-600 dark:text-sky-400 rounded-xl text-xs font-bold border border-sky-200 dark:border-sky-800 flex items-center gap-1 shrink-0 active:scale-95 transition-all">
                    <span class="material-symbols-outlined text-sm">photo_camera</span>
                    <span>Scan</span>
                </button>
            </div>

            @if(!empty($searchResults))
                <div class="divide-y divide-slate-100 dark:divide-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden shadow-sm bg-white dark:bg-slate-900 max-h-60 overflow-y-auto">
                    @foreach($searchResults as $res)
                        <div wire:click="selectOnu({{ $res['onu_id'] }})"
                             class="p-3 hover:bg-sky-50/70 dark:hover:bg-slate-800 cursor-pointer transition-colors flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $res['customer_name'] }}</h4>
                                <p class="text-[10px] text-slate-500 truncate">PPPoE: {{ $res['pppoe_username'] }} • {{ $res['package'] }}</p>
                                <p class="text-[10px] text-sky-600 dark:text-sky-400 font-mono font-bold truncate">SN: {{ $res['serial_number'] }} ({{ $res['model'] }})</p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="px-2 py-0.5 rounded-lg text-[9px] font-bold uppercase {{ $res['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $res['status'] }}
                                </span>
                                @if($res['rx_power'])
                                    <p class="text-[10px] font-bold font-mono text-emerald-600 mt-0.5">{{ $res['rx_power'] }} dBm</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif(strlen(trim($searchQuery)) >= 2)
                <p class="text-[11px] text-slate-400 text-center py-2">Tidak ditemukan ONT yang cocok.</p>
            @endif
        @elseif($selectedOnuId && $onu)
            {{-- SUMMARY TARGET --}}
            <div class="bg-slate-50 dark:bg-slate-900 rounded-xl p-3 border border-slate-200 dark:border-slate-700/80 flex items-center justify-between">
                <div class="min-w-0">
                    <h3 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white truncate">{{ $customer?->name ?? 'ONT ' . ($onu->serial_number ?? '-') }}</h3>
                    <p class="text-[10px] sm:text-[11px] text-slate-500 truncate">
                        {{ $customerService?->pppoeUser?->username ?? 'PPPoE' }} • {{ $customerService?->serviceProfile?->name ?? 'Layanan' }} • {{ $customer?->phone ?? '-' }}
                    </p>
                    <p class="text-[10px] font-mono font-bold text-sky-600 dark:text-sky-400 truncate">SN: {{ $onu->serial_number ?? '-' }} ({{ $onu->model ?: 'ONT' }})</p>
                </div>
                <span class="px-2 py-1 bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300 rounded-xl text-[10px] font-extrabold uppercase shrink-0">
                    Terpilih
                </span>
            </div>
        @endif
    </div>

    @if($selectedOnuId && $onu)
        {{-- AKSI PINTAS / REMOTE ACTIONS BAR --}}
        <div class="grid grid-cols-4 gap-2">
            <button wire:click="refreshSignal" type="button"
                    class="py-2.5 px-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-[11px] font-bold flex flex-col items-center justify-center gap-1 shadow-sm transition-colors">
                <span wire:loading.remove wire:target="refreshSignal" class="material-symbols-outlined text-lg">sync</span>
                <span wire:loading wire:target="refreshSignal" class="material-symbols-outlined animate-spin text-lg">autorenew</span>
                <span>Refresh</span>
            </button>
            <button wire:click="openRebootModal" type="button"
                    class="py-2.5 px-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-[11px] font-bold flex flex-col items-center justify-center gap-1 shadow-sm transition-colors">
                <span class="material-symbols-outlined text-lg">restart_alt</span>
                <span>Reboot</span>
            </button>
            <button wire:click="openWifiModal" type="button"
                    class="py-2.5 px-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-[11px] font-bold flex flex-col items-center justify-center gap-1 shadow-sm transition-colors">
                <span class="material-symbols-outlined text-lg">wifi</span>
                <span>Ganti Wi-Fi</span>
            </button>
            <a href="{{ route('technician.swap-ont.index', ['onu_id' => $onu->id]) }}"
               class="py-2.5 px-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-[11px] font-bold flex flex-col items-center justify-center gap-1 shadow-sm transition-colors">
                <span class="material-symbols-outlined text-lg">sync_alt</span>
                <span>Ganti ONT</span>
            </a>
        </div>

        {{-- STATUS & REDAMAN OPTIK (SIGNAL HEALTH METER) --}}
        @php
            $opt = $diagnostics['optical'] ?? null;
            $rx = $opt['rx_power'] ?? ($onu->rx_power_dbm ? $onu->rx_power_dbm . ' dBm' : null);
            $tx = $opt['tx_power'] ?? ($onu->tx_power_dbm ? $onu->tx_power_dbm . ' dBm' : null);
            $health = $opt['health'] ?? 'unknown';

            $healthColor = match($health) {
                'normal' => 'emerald',
                'warning' => 'amber',
                'critical' => 'rose',
                default => 'slate'
            };
        @endphp
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5 uppercase tracking-wider">
                    <span class="material-symbols-outlined text-emerald-500 text-sm">optics</span>
                    <span>Kualitas Sinyal & Redaman Optik (OPM)</span>
                </span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-{{ $healthColor }}-100 text-{{ $healthColor }}-800 dark:bg-{{ $healthColor }}-950 dark:text-{{ $healthColor }}-300">
                    {{ $health === 'normal' ? 'Optimal (-18 s/d -24 dBm)' : ($health === 'warning' ? 'Warning (-25 s/d -27 dBm)' : ($health === 'critical' ? 'Kritis / Redaman Buruk' : 'Offline / Standby')) }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="p-3.5 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 text-center">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Redaman Masuk (RX Power)</span>
                    <span class="text-xl sm:text-2xl font-black font-mono mt-1 block text-{{ $healthColor }}-600 dark:text-{{ $healthColor }}-400">
                        {{ $rx ?: 'Tidak Ada Sinyal' }}
                    </span>
                    <span class="text-[9px] text-slate-400 mt-0.5 block">Standar GPON: -18 s/d -25 dBm</span>
                </div>

                <div class="p-3.5 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 text-center">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Sinyal Pancar (TX Power)</span>
                    <span class="text-xl sm:text-2xl font-black font-mono mt-1 block text-slate-800 dark:text-slate-100">
                        {{ $tx ?: 'Normal' }}
                    </span>
                    <span class="text-[9px] text-slate-400 mt-0.5 block">Daya Pancar Laser ONT</span>
                </div>
            </div>

            @if(!empty($opt['temperature']) || !empty($opt['voltage']))
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 dark:border-slate-700 text-[11px] text-slate-500">
                    <div>Suhu Optik: <span class="font-bold text-slate-800 dark:text-slate-200">{{ $opt['temperature'] ?? '-' }}</span></div>
                    <div>Voltase BOSA: <span class="font-bold text-slate-800 dark:text-slate-200">{{ $opt['voltage'] ?? '-' }}</span></div>
                </div>
            @endif
        </div>

        {{-- STATUS PERANGKAT & HARDWARE --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5 uppercase tracking-wider">
                    <span class="material-symbols-outlined text-indigo-500 text-sm">info</span>
                    <span>Informasi Hardware & Jaringan</span>
                </span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ ($diagnostics['status'] ?? $onu->status) === 'online' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600' }}">
                    {{ strtoupper($diagnostics['status'] ?? $onu->status) }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-2.5 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Serial Number (SN):</span>
                    <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $onu->serial_number }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">MAC Address:</span>
                    <span class="font-mono font-bold text-slate-700 dark:text-slate-300">{{ $onu->mac_address ?: ($diagnostics['mac_address'] ?? '-') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Model / Vendor:</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $onu->model ?: 'ONT' }} ({{ $onu->vendor?->name ?? 'GPON' }})</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">IP WAN:</span>
                    <span class="font-mono text-slate-800 dark:text-slate-200">{{ $onu->ip_address ?: ($diagnostics['ip_address'] ?? '-') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Uptime (Masa Hidup):</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $diagnostics['uptime'] ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Last Inform GenieACS:</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $diagnostics['last_inform_formatted'] ?? ($onu->last_seen_at ? $onu->last_seen_at->format('d/m/Y H:i') : '-') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">OLT & PON:</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $onu->olt?->name ?? 'OLT Core' }} (PON {{ $onu->pon_port ?: '-' }})</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Titik ODP:</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $onu->odp?->name ?? 'ODP Belum Terikat' }}</span>
                </div>
            </div>
        </div>

        {{-- INFORMASI WI-FI & DAFTAR PERANGKAT TERHUBUNG --}}
        @php
            $wifi = $diagnostics['wifi'] ?? null;
            $clients = $wifi['clients'] ?? [];
        @endphp
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-indigo-500 text-sm">devices</span>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Perangkat Terhubung ke Modem</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">
                    {{ count($clients) }} Perangkat
                </span>
            </div>

            <div class="p-3 bg-indigo-50/50 dark:bg-indigo-950/20 rounded-xl border border-indigo-100 dark:border-indigo-900/40 flex items-center justify-between text-xs">
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">SSID Wi-Fi Utama:</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $wifi['ssid_24'] ?? ($onu->wifi_ssid ?: 'WINETS') }}</span>
                </div>
                <button wire:click="openWifiModal" type="button"
                        class="px-2.5 py-1 bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 font-bold rounded-lg border border-indigo-200 dark:border-indigo-800 text-[10px]">
                    Ubah Wi-Fi
                </button>
            </div>

            @if(empty($clients))
                <div class="py-6 text-center text-slate-400 text-xs">
                    <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">wifi_off</span>
                    <p>Belum ada daftar perangkat terhubung atau modem belum mengirimkan inform LAN.</p>
                </div>
            @else
                <div class="divide-y divide-slate-100 dark:divide-slate-700 border border-slate-100 dark:border-slate-700 rounded-xl overflow-hidden">
                    @foreach($clients as $client)
                        <div class="p-3 flex items-center justify-between gap-2 hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-lg {{ $client['interface_type'] === 'Ethernet' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40' : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40' }} flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-sm">
                                        {{ $client['interface_type'] === 'Ethernet' ? 'lan' : 'smartphone' }}
                                    </span>
                                </div>
                                <div class="min-w-0">
                                    <h5 class="text-xs font-bold text-slate-900 dark:text-white truncate">
                                        {{ $client['host_name'] ?: 'Perangkat Tanpa Nama' }}
                                    </h5>
                                    <p class="text-[10px] font-mono text-slate-500 truncate">
                                        {{ $client['ip_address'] }} • {{ $client['mac_address'] }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase {{ $client['active'] ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $client['active'] ? 'Aktif' : 'Standby' }}
                                </span>
                                <span class="text-[10px] block text-slate-400 mt-0.5">{{ $client['interface_type'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- AKSI LANJUTAN: CABUT / DISMANTLE PERANGKAT --}}
        <div class="pt-2">
            <a href="{{ route('technician.dismantle.index', ['onu_id' => $onu->id]) }}"
               class="w-full py-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-sm text-rose-500">power_off</span>
                <span>Pembongkaran / Cabut ONT Pelanggan</span>
            </a>
        </div>
    @endif

    {{-- MODAL REBOOT ONT --}}
    @if($showRebootModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fadeIn">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 max-w-sm w-full space-y-4 shadow-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/50 text-amber-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-sm">restart_alt</span>
                        </div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-slate-100">Reboot Perangkat ONT</h3>
                    </div>
                    <button wire:click="$set('showRebootModal', false)" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>

                <p class="text-xs text-slate-600 dark:text-slate-300">
                    Kirim perintah restart/reboot ke modem <strong class="text-slate-900 dark:text-white">{{ $onu?->serial_number }}</strong> via TR-069 GenieACS.
                </p>

                @if($rebootStatusMessage)
                    <div class="p-3 bg-amber-50 dark:bg-amber-950/40 rounded-xl border border-amber-200 text-xs text-amber-900 dark:text-amber-200">
                        {{ $rebootStatusMessage }}
                    </div>
                @endif

                <div class="flex gap-2 pt-2">
                    <button wire:click="$set('showRebootModal', false)" type="button"
                            class="flex-1 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-200 transition-colors">
                        Tutup
                    </button>
                    @if(!$rebootStatusMessage)
                        <button wire:click="executeReboot" type="button"
                                class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-md transition-colors flex items-center justify-center gap-1">
                            <span wire:loading.remove wire:target="executeReboot">Kirim Reboot</span>
                            <span wire:loading wire:target="executeReboot">Mengirim...</span>
                        </button>
                    @else
                        <button wire:click="verifyReboot" type="button"
                                class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md transition-colors">
                            Cek Status Hidup
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL UBAH WI-FI --}}
    @if($showWifiModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fadeIn">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 max-w-sm w-full space-y-4 shadow-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-sm">wifi</span>
                        </div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-slate-100">Ubah Konfigurasi Wi-Fi</h3>
                    </div>
                    <button wire:click="$set('showWifiModal', false)" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Wi-Fi (SSID)</label>
                        <input type="text" wire:model="wifi_ssid"
                               class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                        @error('wifi_ssid') <span class="text-rose-500 text-[10px] mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Password Wi-Fi Baru (Kosongkan jika tidak diubah)</label>
                        <input type="text" wire:model="wifi_password" placeholder="Minimal 8 karakter..."
                               class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono font-bold bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                        @error('wifi_password') <span class="text-rose-500 text-[10px] mt-0.5 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button wire:click="$set('showWifiModal', false)" type="button"
                            class="flex-1 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-200 transition-colors">
                        Batal
                    </button>
                    <button wire:click="saveWifi" type="button"
                            class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md transition-colors flex items-center justify-center gap-1">
                        <span wire:loading.remove wire:target="saveWifi">Terapkan ke ONT</span>
                        <span wire:loading wire:target="saveWifi">Mengirim...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL SCANNER BARCODE KAMERA --}}
    <div x-show="scannerOpen"
         style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fadeIn">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 max-w-sm w-full space-y-4 shadow-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-sky-100 dark:bg-sky-900/50 text-sky-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-sm">photo_camera</span>
                    </div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-slate-100">Scan Barcode / SN ONT</h3>
                </div>
                <button type="button" @click="closeScanner()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined text-sm">close</span>
                </button>
            </div>

            <div class="rounded-2xl overflow-hidden border-2 border-sky-500 relative bg-black aspect-video flex items-center justify-center">
                <div id="diag-camera-reader" class="w-full"></div>
            </div>

            <p class="text-[11px] text-slate-500 dark:text-slate-400 text-center">
                Arahkan kamera ke barcode stiker belakang modem ONT.
            </p>

            <button type="button" @click="closeScanner()"
                    class="w-full py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-200 transition-colors">
                Tutup Kamera
            </button>
        </div>
    </div>

    {{-- Script HTML5 QR Code Scanner --}}
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        function diagnosticManager() {
            return {
                scannerOpen: false,
                html5Scanner: null,

                openScanner() {
                    this.scannerOpen = true;
                    this.$nextTick(() => {
                        if (!this.html5Scanner) {
                            this.html5Scanner = new Html5Qrcode("diag-camera-reader");
                        }
                        this.html5Scanner.start(
                            { facingMode: "environment" },
                            { fps: 10, qrbox: { width: 250, height: 120 } },
                            (decodedText) => {
                                this.closeScanner();
                                $wire.set('searchQuery', decodedText.trim());
                                $wire.call('updatedSearchQuery');
                            },
                            (err) => {}
                        ).catch((err) => {
                            alert("Kamera tidak dapat diakses: " + err);
                            this.closeScanner();
                        });
                    });
                },

                closeScanner() {
                    if (this.html5Scanner) {
                        this.html5Scanner.stop().catch(() => {}).finally(() => {
                            this.scannerOpen = false;
                        });
                    } else {
                        this.scannerOpen = false;
                    }
                }
            };
        }
    </script>
</div>
