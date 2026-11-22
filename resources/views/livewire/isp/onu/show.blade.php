<div class="max-w-7xl mx-auto p-4 md:p-6 space-y-6">
    <!-- Success/Error Flash Message -->
    @if(session()->has('success'))
        <x-feedback.alert variant="success" class="mb-4">
            {{ session('success') }}
        </x-feedback.alert>
    @endif
    @if(session()->has('error'))
        <x-feedback.alert variant="danger" class="mb-4">
            {{ session('error') }}
        </x-feedback.alert>
    @endif

    <!-- Top Action Bar & Identity Header Card -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 md:p-6 shadow-xs border border-slate-200/80 dark:border-slate-700/80 flex flex-col md:flex-row md:items-center justify-between gap-5">
        <div class="flex items-center gap-4">
            <a href="{{ route('isp.onus.index') }}" class="p-2.5 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700/60 border border-slate-200 dark:border-slate-700 transition-colors" title="Kembali ke Daftar ONU">
                <span class="material-symbols-outlined notranslate text-xl block">arrow_back</span>
            </a>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/80 dark:border-indigo-800/80 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xl font-black shadow-2xs">
                {{ substr($this->realVendorName, 0, 1) }}
            </div>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight">{{ $onu->serial_number ?: $onu->name }}</h1>
                    @if($onu->status === 'active')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> ONLINE
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full bg-rose-50 text-rose-700 dark:bg-rose-950/70 dark:text-rose-300 border border-rose-300 dark:border-rose-700">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span> OFFLINE
                        </span>
                    @endif
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-mono border border-slate-200 dark:border-slate-600">
                        {{ $onu->code }}
                    </span>
                    <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-indigo-50 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                        {{ $this->realVendorName }}
                    </span>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-2 flex items-center gap-3 flex-wrap">
                    <span class="inline-flex items-center gap-1">
                        <span class="material-symbols-outlined notranslate text-sm text-indigo-500">router</span>
                        OLT: <strong class="text-slate-700 dark:text-slate-200 font-semibold">{{ $onu->olt?->name ?? 'OLT Tidak Terdata' }}</strong>
                    </span>
                    <span class="text-slate-300 dark:text-slate-600">•</span>
                    <span class="inline-flex items-center gap-1">
                        <span class="material-symbols-outlined notranslate text-sm text-sky-500">settings_ethernet</span>
                        Port PON: <strong class="text-slate-700 dark:text-slate-200 font-semibold">{{ $onu->pon_port ?: '1' }}</strong> (ID: #{{ $onu->onu_id_on_olt ?: '1' }})
                    </span>
                    @if($onu->last_seen_at)
                        <span class="text-slate-300 dark:text-slate-600">•</span>
                        <span class="inline-flex items-center gap-1 text-slate-500">
                            <span class="material-symbols-outlined notranslate text-sm text-amber-500">schedule</span>
                            Aktif: {{ $onu->last_seen_at->diffForHumans() }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <button wire:click="refreshLiveOpticalPower" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-xs transition-colors cursor-pointer disabled:opacity-50">
                <span wire:loading.remove wire:target="refreshLiveOpticalPower" class="material-symbols-outlined notranslate text-base">sync</span>
                <span wire:loading wire:target="refreshLiveOpticalPower" class="animate-spin text-base">progress_activity</span>
                <span>Tarik Redaman Live</span>
            </button>
            <a href="{{ route('isp.onus.edit', $onu->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-semibold border border-slate-200/80 dark:border-slate-600 transition-colors">
                <span class="material-symbols-outlined notranslate text-base">edit</span>
                <span>Edit</span>
            </a>
            <button wire:click="openWifiModal" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-sky-50 dark:bg-sky-950/50 hover:bg-sky-100 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800 rounded-xl text-sm font-semibold transition-colors">
                <span class="material-symbols-outlined notranslate text-base">wifi</span>
                <span>Wi-Fi</span>
            </button>
        </div>
    </div>

    <!-- DASHBOARD REDAMAN OPTIK (4 KARTU METRIK SERAGAM & RAPI) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4.5">
        {{-- Card 1: Optical RX Power --}}
        @php
            $rx = $onu->rx_power_dbm;
            $rxQuality = 'Belum Diukur';
            $rxTextColor = 'text-slate-700 dark:text-slate-300';
            $rxBadgeClass = 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400';
            $rxIconColor = 'text-slate-500 bg-slate-100 dark:bg-slate-700 dark:text-slate-300';

            if ($rx !== null && $rx != 0) {
                if ($rx >= -24.00 && $rx <= -14.00) {
                    $rxQuality = 'Optimal';
                    $rxTextColor = 'text-emerald-600 dark:text-emerald-400';
                    $rxBadgeClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800';
                    $rxIconColor = 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/60 dark:text-emerald-400';
                } elseif ($rx >= -27.00 && $rx < -24.00) {
                    $rxQuality = 'Toleransi';
                    $rxTextColor = 'text-amber-600 dark:text-amber-400';
                    $rxBadgeClass = 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800';
                    $rxIconColor = 'text-amber-600 bg-amber-50 dark:bg-amber-950/60 dark:text-amber-400';
                } else {
                    $rxQuality = 'Kritis / Redaman Buruk';
                    $rxTextColor = 'text-rose-600 dark:text-rose-400';
                    $rxBadgeClass = 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800';
                    $rxIconColor = 'text-rose-600 bg-rose-50 dark:bg-rose-950/60 dark:text-rose-400';
                }
            }
        @endphp
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs flex flex-col justify-between hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Daya Terima (RX)</span>
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center {{ $rxIconColor }}">
                        <span class="material-symbols-outlined notranslate text-lg">sensors</span>
                    </div>
                </div>
                <div class="text-2xl font-black font-mono tracking-tight {{ $rxTextColor }}">
                    {{ $rx !== null && $rx != 0 ? number_format($rx, 2) . ' dBm' : '—' }}
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                <span class="text-xs font-bold px-2 py-0.5 rounded-md {{ $rxBadgeClass }}">
                    {{ $rxQuality }}
                </span>
                <span class="text-[11px] font-medium text-slate-400">Standar: -14 ~ -24 dBm</span>
            </div>
        </div>

        {{-- Card 2: Optical TX Power --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs flex flex-col justify-between hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Daya Pancar (TX)</span>
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center text-sky-600 bg-sky-50 dark:bg-sky-950/60 dark:text-sky-400">
                        <span class="material-symbols-outlined notranslate text-lg">arrow_upward</span>
                    </div>
                </div>
                <div class="text-2xl font-black font-mono tracking-tight text-slate-900 dark:text-slate-100">
                    {{ $onu->tx_power_dbm !== null && $onu->tx_power_dbm != 0 ? number_format($onu->tx_power_dbm, 2) . ' dBm' : '—' }}
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-sky-50 text-sky-700 border border-sky-200 dark:bg-sky-950/60 dark:text-sky-300 dark:border-sky-800">
                    Normal
                </span>
                <span class="text-[11px] font-medium text-slate-400">Standar: +1.5 ~ +3.5 dBm</span>
            </div>
        </div>

        {{-- Card 3: Temperatur Transceiver --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs flex flex-col justify-between hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Suhu Transceiver</span>
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center text-amber-600 bg-amber-50 dark:bg-amber-950/60 dark:text-amber-400">
                        <span class="material-symbols-outlined notranslate text-lg">thermostat</span>
                    </div>
                </div>
                <div class="text-2xl font-black font-mono tracking-tight text-slate-900 dark:text-slate-100">
                    {{ $onu->temperature ? $onu->temperature . ' °C' : ($onu->acsDevice?->temperature ? $onu->acsDevice->temperature . ' °C' : '—') }}
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                    {{ $onu->temperature || $onu->acsDevice?->temperature ? 'Optimal' : 'Sensor OLT' }}
                </span>
                <span class="text-[11px] font-medium text-slate-400">Batas Aman: &lt; 70 °C</span>
            </div>
        </div>

        {{-- Card 4: Jarak Fiber Optik (Distance) --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs flex flex-col justify-between hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estimasi Jarak Kabel</span>
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center text-emerald-600 bg-emerald-50 dark:bg-emerald-950/60 dark:text-emerald-400">
                        <span class="material-symbols-outlined notranslate text-lg">straighten</span>
                    </div>
                </div>
                <div class="text-2xl font-black font-mono tracking-tight text-slate-900 dark:text-slate-100">
                    @if($onu->distance_meters && $onu->distance_meters > 0)
                        {{ $onu->distance_meters >= 1000 ? round($onu->distance_meters / 1000, 2) . ' km' : $onu->distance_meters . ' m' }}
                    @else
                        —
                    @endif
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                    {{ $onu->distance_meters > 0 ? 'RTT Terukur' : 'Estimasi Port' }}
                </span>
                <span class="text-[11px] font-medium text-slate-400">Maksimum GPON: 20 km</span>
            </div>
        </div>
    </div>

    <!-- DETAIL INFORMASI UTAMA (DUA PANEL SEIMBANG DENGAN STRUKTUR CARD TILES RAPI) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
        {{-- PANEL 1: INFORMASI PERANGKAT & JARINGAN OLT --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-xs border border-slate-200/80 dark:border-slate-700/80 flex flex-col justify-between space-y-5">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3.5 mb-4">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400 text-xl">router</span>
                        Informasi Perangkat & OLT
                    </h3>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                        Hardware
                    </span>
                </div>

                {{-- Grid 8 Item Tile Seragam --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="bg-slate-50/75 dark:bg-slate-900/40 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800/80">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Serial Number (SN)</label>
                        <p class="font-bold text-indigo-600 dark:text-indigo-400 font-mono text-sm">{{ $onu->serial_number ?: '-' }}</p>
                    </div>

                    <div class="bg-slate-50/75 dark:bg-slate-900/40 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800/80">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Vendor Hardware</label>
                        <p class="font-bold text-slate-900 dark:text-slate-100 text-sm">{{ $this->realVendorName }}</p>
                    </div>

                    <div class="bg-slate-50/75 dark:bg-slate-900/40 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800/80">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Model ONT</label>
                        <p class="font-semibold text-slate-900 dark:text-slate-100 text-sm">{{ $onu->model ?: ($onu->acsDevice?->model ?: 'ONT Terminal') }}</p>
                    </div>

                    <div class="bg-slate-50/75 dark:bg-slate-900/40 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800/80">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">MAC Address</label>
                        <p class="font-mono text-slate-900 dark:text-slate-100 text-sm">{{ $onu->mac_address ?: ($onu->acsDevice?->mac_address ?: '-') }}</p>
                    </div>

                    <div class="bg-slate-50/75 dark:bg-slate-900/40 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800/80">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Host OLT</label>
                        <p class="font-bold text-slate-900 dark:text-slate-100 text-sm">{{ $onu->olt?->name ?: '-' }}</p>
                    </div>

                    <div class="bg-slate-50/75 dark:bg-slate-900/40 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800/80">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">PON Port & Index</label>
                        <p class="font-semibold text-slate-900 dark:text-slate-100 text-sm">Port 0/1/{{ $onu->pon_port ?: '1' }} • Index #{{ $onu->onu_id_on_olt ?: '1' }}</p>
                    </div>

                    <div class="bg-slate-50/75 dark:bg-slate-900/40 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800/80">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">IP Host (ACS / TR-069)</label>
                        <p class="font-mono text-slate-900 dark:text-slate-100 text-sm">
                            <x-ui.ip-link :ip="$onu->acsDevice?->ip_address" />
                        </p>
                    </div>

                    <div class="bg-slate-50/75 dark:bg-slate-900/40 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800/80">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">SSID Wi-Fi 2.4 GHz</label>
                        <div class="flex items-center justify-between gap-1">
                            <p class="font-bold text-slate-900 dark:text-slate-100 text-sm truncate">{{ $onu->wifi_ssid ?: ($onu->acsDevice?->wifi_ssid ?: 'Default Wi-Fi') }}</p>
                            <button wire:click="openWifiModal" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 hover:underline shrink-0">
                                Ubah
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Baris Bawah: Koordinat & Peta Lokasi --}}
            <div class="pt-3 border-t border-slate-100 dark:border-slate-700/80 flex items-center justify-between flex-wrap gap-2">
                @if($onu->latitude && $onu->longitude)
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-mono flex items-center gap-1.5">
                        <span class="material-symbols-outlined notranslate text-sm text-rose-500">location_on</span>
                        {{ $onu->latitude }}, {{ $onu->longitude }}
                    </span>
                    <a href="https://www.google.com/maps?q={{ $onu->latitude }},{{ $onu->longitude }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 text-indigo-600 dark:text-indigo-400 text-xs font-semibold rounded-lg border border-indigo-200 dark:border-indigo-800 transition-colors">
                        <span>Buka Google Maps</span>
                        <span class="material-symbols-outlined notranslate text-xs">open_in_new</span>
                    </a>
                @else
                    <span class="text-xs text-slate-400 flex items-center gap-1.5">
                        <span class="material-symbols-outlined notranslate text-sm">location_off</span>
                        Titik koordinat belum dipetakan
                    </span>
                    <a href="{{ route('isp.onus.edit', $onu->id) }}" class="text-xs font-bold text-indigo-600 hover:underline">
                        Set Lokasi
                    </a>
                @endif
            </div>
        </div>

        {{-- PANEL 2: INFORMASI PELANGGAN & LAYANAN TERHUBUNG --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-xs border border-slate-200/80 dark:border-slate-700/80 flex flex-col justify-between space-y-5">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3.5 mb-4">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400 text-xl">account_circle</span>
                        Data Pelanggan & Layanan
                    </h3>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800">
                        Langganan Aktif
                    </span>
                </div>

                @php
                    $cs = $customerServices->first();
                    $customer = $cs?->customer;
                @endphp

                @if($customer)
                    {{-- Grid 6 Item Tile Pelanggan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 mb-3.5">
                        <div class="bg-slate-50/75 dark:bg-slate-900/40 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800/80">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Pelanggan</label>
                            <p class="font-bold text-slate-900 dark:text-slate-100 text-sm truncate">{{ $customer->name }}</p>
                        </div>

                        <div class="bg-slate-50/75 dark:bg-slate-900/40 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800/80">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">ID / Kode Pelanggan</label>
                            <p class="font-mono font-bold text-slate-900 dark:text-slate-100 text-sm">{{ $customer->code }}</p>
                        </div>

                        <div class="bg-slate-50/75 dark:bg-slate-900/40 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800/80">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">PPPoE Username</label>
                            <p class="font-bold text-indigo-600 dark:text-indigo-400 font-mono text-sm truncate">{{ $cs->username ?: '-' }}</p>
                        </div>

                        <div class="bg-slate-50/75 dark:bg-slate-900/40 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800/80">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Paket Layanan</label>
                            <p class="font-bold text-emerald-600 dark:text-emerald-400 text-sm truncate">
                                {{ $cs->service?->name ?: ($cs->serviceProfile?->name ?: ($cs->internetPlan?->name ?: '-')) }}
                            </p>
                        </div>

                        <div class="bg-slate-50/75 dark:bg-slate-900/40 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800/80">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Wilayah / Cabang</label>
                            <p class="font-semibold text-slate-900 dark:text-slate-100 text-sm truncate">{{ $onu->branch?->name ?: ($customer->branch?->name ?: '-') }}</p>
                        </div>

                        <div class="bg-slate-50/75 dark:bg-slate-900/40 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800/80">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Titik ODP</label>
                            <p class="font-semibold text-slate-900 dark:text-slate-100 text-sm truncate">{{ $onu->odp?->name ?: 'Belum Dipetakan' }}</p>
                        </div>
                    </div>

                    {{-- Tile Full Width: Alamat Pelanggan --}}
                    <div class="bg-slate-50/75 dark:bg-slate-900/40 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800/80">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Alamat Pemasangan</label>
                        <p class="text-slate-700 dark:text-slate-300 text-xs leading-relaxed flex items-start gap-1.5">
                            <span class="material-symbols-outlined notranslate text-sm text-slate-400 mt-0.5">home</span>
                            <span>{{ $customer->address ?: '-' }}</span>
                        </p>
                    </div>
                @else
                    <div class="p-8 rounded-xl bg-slate-50/80 dark:bg-slate-900/40 border border-dashed border-slate-200 dark:border-slate-700 text-center my-auto">
                        <span class="material-symbols-outlined notranslate text-slate-400 text-4xl mb-2">person_off</span>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Modem Belum Ditautkan ke Pelanggan</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Perangkat ini berada di inventaris OLT dan siap ditugaskan saat pemasangan baru (PSB).</p>
                    </div>
                @endif
            </div>

            {{-- Baris Bawah: Status Layanan & Tautan Profil Pelanggan --}}
            <div class="pt-3 border-t border-slate-100 dark:border-slate-700/80 flex items-center justify-between flex-wrap gap-2">
                @if($customer)
                    <span class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Status Layanan: <strong class="text-slate-700 dark:text-slate-200 uppercase font-bold">{{ $cs->status ?? 'ACTIVE' }}</strong>
                    </span>
                    <a href="{{ route('crm.customers.show', $customer->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-600 transition-colors">
                        <span>Lihat Profil Pelanggan</span>
                        <span class="material-symbols-outlined notranslate text-xs">arrow_forward</span>
                    </a>
                @else
                    <span class="text-xs text-slate-400">Siap untuk aktivasi pelanggan baru</span>
                    <a href="{{ route('isp.onus.edit', $onu->id) }}" class="text-xs font-bold text-indigo-600 hover:underline">
                        Tautkan Pelanggan
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- MODAL UBAH WI-FI POPUP --}}
    @if($showWifiModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 w-full max-w-md p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-600 text-xl">wifi</span>
                    <h3 class="font-bold text-base text-slate-900 dark:text-slate-100">Konfigurasi Wi-Fi ONT</h3>
                </div>
                <button wire:click="closeWifiModal" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg">
                    <span class="material-symbols-outlined notranslate text-xl">close</span>
                </button>
            </div>

            <form wire:submit.prevent="configureWifi">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Wi-Fi (SSID)</label>
                        <input type="text" wire:model="wifiSsid" placeholder="Contoh: MyWifi_Home" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        @error('wifiSsid') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Password Wi-Fi (WPA2-PSK)</label>
                        <input type="text" wire:model="wifiPassword" placeholder="Minimal 8 karakter" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none font-mono">
                        @error('wifiPassword') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        <span class="text-[11px] text-slate-400 mt-1 block">Kosongkan jika tidak ingin mengubah password lama.</span>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" id="wifiEnabled" wire:model="wifiEnabled" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="wifiEnabled" class="text-xs font-semibold text-slate-700 dark:text-slate-300">Aktifkan Radio Wi-Fi 2.4 GHz</label>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                    <button type="button" wire:click="closeWifiModal" class="px-4 py-2 text-xs font-bold rounded-xl text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white transition-colors">
                        Simpan ke ONT
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
