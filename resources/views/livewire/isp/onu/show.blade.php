<div class="max-w-7xl mx-auto p-4 space-y-6">
    <!-- Success/Error Flash Message -->
    <div>
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
    </div>

    <!-- Modern Navigation Breadcrumbs -->
    <nav class="flex items-center text-sm font-medium text-slate-500 dark:text-slate-400 gap-2 mb-1">
        <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
            <span class="material-symbols-outlined notranslate text-base align-middle">home</span> Dashboard
        </a>
        <span>/</span>
        <a href="{{ route('isp.onus.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
            Network
        </a>
        <span>/</span>
        <a href="{{ route('isp.onus.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
            ONU
        </a>
        <span>/</span>
        <span class="text-slate-900 dark:text-slate-100 font-semibold font-mono">{{ $onu->serial_number ?: $onu->code }}</span>
    </nav>

    <!-- Top Action Bar & Identity Header -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-xs border border-slate-200/80 dark:border-slate-700/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('isp.onus.index') }}" class="p-2.5 text-slate-500 hover:text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700/60 transition-colors" title="Kembali ke Daftar ONU">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/60 dark:border-indigo-800/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-2xl font-black shadow-xs">
                {{ substr($this->realVendorName, 0, 1) }}
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight">{{ $onu->serial_number ?: $onu->name }}</h1>
                    @if($onu->status === 'active')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> ONLINE
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300 border border-rose-300 dark:border-rose-700">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span> OFFLINE
                        </span>
                    @endif
                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-md bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-mono">
                        {{ $onu->code }}
                    </span>
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-md bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">
                        {{ $this->realVendorName }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-2">
                    <span>Terhubung ke OLT: <b>{{ $onu->olt?->name ?? 'OLT Tidak Terdata' }}</b></span>
                    <span>•</span>
                    <span>Port PON: <b>{{ $onu->pon_port ?: '1' }}</b> (ID: #{{ $onu->onu_id_on_olt ?: '1' }})</span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button wire:click="refreshLiveOpticalPower" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-xs transition-colors cursor-pointer disabled:opacity-50">
                <span wire:loading.remove wire:target="refreshLiveOpticalPower" class="material-symbols-outlined notranslate text-base">sync</span>
                <span wire:loading wire:target="refreshLiveOpticalPower" class="animate-spin text-base">⏳</span>
                <span>Tarik Redaman Live</span>
            </button>
            <a href="{{ route('isp.onus.edit', $onu->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-semibold transition-colors">
                <span class="material-symbols-outlined notranslate text-base">edit</span>
                <span>Edit</span>
            </a>
            <button wire:click="openWifiModal" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-sky-50 dark:bg-sky-950/50 hover:bg-sky-100 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800 rounded-xl text-sm font-semibold transition-colors">
                <span class="material-symbols-outlined notranslate text-base">wifi</span>
                <span>Wi-Fi</span>
            </button>
        </div>
    </div>

    <!-- DASHBOARD REDAMAN OPTIK (OPTICAL POWER METRICS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Optical RX Power --}}
        @php
            $rx = $onu->rx_power_dbm;
            $rxQuality = 'Belum Diukur';
            $rxBg = 'bg-slate-50 dark:bg-slate-800/80 border-slate-200 dark:border-slate-700';
            $rxTextColor = 'text-slate-900 dark:text-slate-100';
            $rxBadgeClass = 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400';

            if ($rx !== null && $rx != 0) {
                if ($rx >= -24.00 && $rx <= -14.00) {
                    $rxQuality = 'Optimal (Sangat Baik)';
                    $rxBg = 'bg-emerald-50/60 dark:bg-emerald-950/30 border-emerald-300 dark:border-emerald-800';
                    $rxTextColor = 'text-emerald-700 dark:text-emerald-300';
                    $rxBadgeClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/80 dark:text-emerald-200';
                } elseif ($rx >= -27.00 && $rx < -24.00) {
                    $rxQuality = 'Toleransi (Waspada)';
                    $rxBg = 'bg-amber-50/60 dark:bg-amber-950/30 border-amber-300 dark:border-amber-800';
                    $rxTextColor = 'text-amber-700 dark:text-amber-300';
                    $rxBadgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-900/80 dark:text-amber-200';
                } else {
                    $rxQuality = 'Kritis / Redaman Buruk';
                    $rxBg = 'bg-rose-50/60 dark:bg-rose-950/30 border-rose-300 dark:border-rose-800';
                    $rxTextColor = 'text-rose-700 dark:text-rose-300';
                    $rxBadgeClass = 'bg-rose-100 text-rose-800 dark:bg-rose-900/80 dark:text-rose-200';
                }
            }
        @endphp
        <div class="rounded-2xl p-4.5 border shadow-2xs {{ $rxBg }} flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Daya Terima (RX Power)</span>
                    <span class="material-symbols-outlined notranslate text-xl {{ $rxTextColor }}">sensors</span>
                </div>
                <div class="text-2xl font-black font-mono tracking-tight {{ $rxTextColor }}">
                    {{ $rx !== null && $rx != 0 ? number_format($rx, 2) . ' dBm' : '—' }}
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between">
                <span class="text-xs font-semibold px-2 py-0.5 rounded-md {{ $rxBadgeClass }}">
                    {{ $rxQuality }}
                </span>
                <span class="text-[11px] text-slate-400">Standar: -14 s/d -24 dBm</span>
            </div>
        </div>

        {{-- Card 2: Optical TX Power --}}
        <div class="rounded-2xl p-4.5 border bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Daya Pancar (TX Power)</span>
                    <span class="material-symbols-outlined notranslate text-xl text-indigo-600 dark:text-indigo-400">arrow_upward</span>
                </div>
                <div class="text-2xl font-black font-mono tracking-tight text-slate-900 dark:text-slate-100">
                    {{ $onu->tx_power_dbm !== null && $onu->tx_power_dbm != 0 ? number_format($onu->tx_power_dbm, 2) . ' dBm' : '—' }}
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Laser Upstream ONT</span>
                <span class="text-[11px] text-slate-400">Normal: +1.5 s/d +3.5 dBm</span>
            </div>
        </div>

        {{-- Card 3: Temperatur Transceiver --}}
        <div class="rounded-2xl p-4.5 border bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Suhu Perangkat</span>
                    <span class="material-symbols-outlined notranslate text-xl text-amber-500">thermostat</span>
                </div>
                <div class="text-2xl font-black font-mono tracking-tight text-slate-900 dark:text-slate-100">
                    {{ $onu->temperature ? $onu->temperature . ' °C' : ($onu->acsDevice?->temperature ? $onu->acsDevice->temperature . ' °C' : '—') }}
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Transceiver Optik</span>
                <span class="text-[11px] text-slate-400">Batas Aman: &lt; 70 °C</span>
            </div>
        </div>

        {{-- Card 4: Jarak Fiber Optik (Distance) --}}
        <div class="rounded-2xl p-4.5 border bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estimasi Jarak Kabel</span>
                    <span class="material-symbols-outlined notranslate text-xl text-emerald-500">straighten</span>
                </div>
                <div class="text-2xl font-black font-mono tracking-tight text-slate-900 dark:text-slate-100">
                    @if($onu->distance_meters && $onu->distance_meters > 0)
                        {{ $onu->distance_meters >= 1000 ? round($onu->distance_meters / 1000, 2) . ' km' : $onu->distance_meters . ' m' }}
                    @else
                        —
                    @endif
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Dari Port OLT</span>
                <span class="text-[11px] text-slate-400">Maksimum GPON: 20 km</span>
            </div>
        </div>
    </div>

    <!-- DETAIL INFORMASI UTAMA & RELASI -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- PANEL 1: INFORMASI SPESIFIKASI PERANGKAT --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-xs border border-slate-200 dark:border-slate-700 space-y-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2 border-b border-slate-200 dark:border-slate-700 pb-3">
                <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400" style="font-size:20px">memory</span>
                Informasi Perangkat ONT & OLT
            </h3>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-0.5">Serial Number (SN)</label>
                    <p class="font-bold text-slate-900 dark:text-slate-100 font-mono">{{ $onu->serial_number ?: '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-0.5">Vendor Hardware</label>
                    <p class="font-bold text-slate-900 dark:text-slate-100">{{ $this->realVendorName }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-0.5">Model ONT</label>
                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $onu->model ?: ($onu->acsDevice?->model ?: 'ONT Terminal') }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-0.5">MAC Address</label>
                    <p class="font-mono text-slate-900 dark:text-slate-100">{{ $onu->mac_address ?: ($onu->acsDevice?->mac_address ?: '-') }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-0.5">OLT Host</label>
                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $onu->olt?->name ?: '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-0.5">PON Port & Index</label>
                    <p class="font-semibold text-slate-900 dark:text-slate-100">
                        Port 0/1/{{ $onu->pon_port ?: '1' }} : Index #{{ $onu->onu_id_on_olt ?: '1' }}
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-0.5">IP Host / TR-069</label>
                    <p class="font-mono text-slate-900 dark:text-slate-100">
                        <x-ui.ip-link :ip="$onu->acsDevice?->ip_address" />
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-0.5">Terakhir Aktif (Last Seen)</label>
                    <p class="text-slate-900 dark:text-slate-100 text-xs">
                        {{ $onu->last_seen_at ? $onu->last_seen_at->diffForHumans() : '-' }}
                    </p>
                </div>
            </div>

            @if($onu->latitude && $onu->longitude)
            <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                    {{ $onu->latitude }}, {{ $onu->longitude }}
                </span>
                <a href="https://www.google.com/maps?q={{ $onu->latitude }},{{ $onu->longitude }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 text-indigo-600 dark:text-indigo-400 text-xs font-semibold rounded-lg border border-indigo-200 dark:border-indigo-800 transition-colors">
                    <span class="material-symbols-outlined notranslate text-sm">location_on</span>
                    <span>Buka Google Maps</span>
                </a>
            </div>
            @endif
        </div>

        {{-- PANEL 2: INFORMASI PELANGGAN, LAYANAN & ODP --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-xs border border-slate-200 dark:border-slate-700 space-y-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2 border-b border-slate-200 dark:border-slate-700 pb-3">
                <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400" style="font-size:20px">account_circle</span>
                Data Pelanggan & Layanan Terhubung
            </h3>

            @php
                $cs = $customerServices->first();
                $customer = $cs?->customer;
            @endphp

            @if($customer)
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-0.5">Nama Pelanggan</label>
                        <p class="font-bold text-slate-900 dark:text-slate-100">{{ $customer->name }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-0.5">ID / Kode Pelanggan</label>
                        <p class="font-mono text-slate-900 dark:text-slate-100">{{ $customer->code }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-0.5">PPPoE Username</label>
                        <p class="font-bold text-indigo-600 dark:text-indigo-400 font-mono">{{ $cs->username ?: '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-0.5">Paket Internet</label>
                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $cs->service?->name ?: ($cs->serviceProfile?->name ?: ($cs->internetPlan?->name ?: '-')) }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-0.5">Wilayah / Cabang</label>
                        <p class="text-slate-900 dark:text-slate-100">{{ $onu->branch?->name ?: ($customer->branch?->name ?: '-') }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-0.5">ODP Terhubung</label>
                        <p class="text-slate-900 dark:text-slate-100 font-semibold">{{ $onu->odp?->name ?: 'Belum Dipetakan' }}</p>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-0.5">Alamat Pemasangan</label>
                        <p class="text-slate-700 dark:text-slate-300 text-xs">{{ $customer->address ?: '-' }}</p>
                    </div>
                </div>
            @else
                <div class="p-6 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-dashed border-slate-200 dark:border-slate-700 text-center">
                    <span class="material-symbols-outlined notranslate text-slate-400 text-3xl mb-1">person_off</span>
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Modem Belum Ditautkan ke Pelanggan</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Perangkat ini berada di inventaris OLT dan siap ditugaskan untuk aktivasi PSB baru.</p>
                </div>
            @endif

            {{-- Konfigurasi Wi-Fi Ringkas --}}
            <div class="mt-4 pt-4 border-t border-slate-200/60 dark:border-slate-700/60">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">SSID Wi-Fi 2.4 GHz</span>
                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $onu->wifi_ssid ?: ($onu->acsDevice?->wifi_ssid ?: 'Default Wi-Fi') }}</span>
                    </div>
                    <button wire:click="openWifiModal" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                        Ganti Password / SSID
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
