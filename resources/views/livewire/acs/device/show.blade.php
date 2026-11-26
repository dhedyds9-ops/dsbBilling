<div class="space-y-6 pb-12">
    {{-- SESSION ALERTS --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 rounded-xl text-emerald-700 dark:text-emerald-300 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 rounded-xl text-red-700 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">error</span>
            {{ session('error') }}
        </div>
    @endif

    {{-- HEADER BAR --}}
    <div class="px-5 py-4 {{ $isNocLayout ? 'noc-panel-bg noc-border' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700' }} shadow-sm rounded-xl flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('acs.devices.index') }}" class="p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:text-indigo-600 hover:bg-slate-100 dark:bg-slate-800 transition-colors" title="Kembali ke Daftar">
                <span class="material-symbols-outlined notranslate" translate="no">arrow_back</span>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    {{ $device->serial_number ?? 'Device Detail' }}
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider {{ $device->status === 'online' ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300' : 'bg-rose-100 dark:bg-rose-900/50 text-rose-700' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $device->status === 'online' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                        {{ $device->status }}
                    </span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                        {{ strtoupper($detectedVendor !== 'generic' ? $detectedVendor : ($device->vendor?->name ?? 'Generic')) }}
                    </span>
                </h1>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-1">
                    MAC: {{ $device->mac_address ?? '-' }} | IP: <x-ui.ip-link :ip="$device->ip_address" /> | Model: {{ $hardwareMetrics['product_class'] ?? ($device->product_class ?? '-') }}
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="summonDevice" wire:loading.attr="disabled" class="inline-flex items-center justify-center px-3.5 py-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 border border-emerald-200 dark:border-emerald-800 rounded-lg text-xs font-semibold transition-colors disabled:opacity-50" title="Kirim Connection Request via port 7547 agar modem sinkronisasi instan">
                <span wire:loading.remove wire:target="summonDevice" class="material-symbols-outlined notranslate mr-1.5 text-[17px]" translate="no">connect_without_contact</span>
                <span wire:loading wire:target="summonDevice" class="material-symbols-outlined notranslate animate-spin mr-1.5 text-[17px]" translate="no">refresh</span>
                <span>Summon</span>
            </button>
            <button wire:click="openWifiModal" class="inline-flex items-center justify-center px-3.5 py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 border border-indigo-200 dark:border-indigo-800 rounded-lg text-xs font-semibold transition-colors">
                <span class="material-symbols-outlined notranslate mr-1.5 text-[17px]" translate="no">wifi</span> Ganti WiFi
            </button>
            <button wire:click="openCreateWanModal" class="inline-flex items-center justify-center px-3.5 py-2 bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/50 border border-purple-200 dark:border-purple-800 rounded-lg text-xs font-semibold transition-colors">
                <span class="material-symbols-outlined notranslate mr-1.5 text-[17px]" translate="no">settings_ethernet</span> Konfigurasi WAN
            </button>
            <button wire:click="rebootDevice" wire:confirm="Yakin ingin merestart modem ini dari jarak jauh?" class="inline-flex items-center justify-center px-3.5 py-2 bg-amber-50 dark:bg-amber-900/30 text-amber-700 hover:bg-amber-100 dark:bg-amber-900/50 border border-amber-200 dark:border-amber-800 rounded-lg text-xs font-semibold transition-colors">
                <span class="material-symbols-outlined notranslate mr-1.5 text-[17px]" translate="no">restart_alt</span> Reboot
            </button>
            <button wire:click="factoryResetDevice" wire:confirm="PERINGATAN! Yakin ingin FACTORY RESET modem ini? Semua konfigurasi akan hilang!" class="inline-flex items-center justify-center px-3.5 py-2 bg-rose-50 dark:bg-rose-900/30 text-rose-700 hover:bg-rose-100 dark:bg-rose-900/50 border border-rose-200 dark:border-rose-800 rounded-lg text-xs font-semibold transition-colors">
                <span class="material-symbols-outlined notranslate mr-1.5 text-[17px]" translate="no">warning</span> Reset
            </button>
        </div>
    </div>

    {{-- EXECUTIVE KPI CARDS --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3.5">
        {{-- RX Power --}}
        <div class="relative overflow-x-auto rounded-xl border border-indigo-200 shadow-sm bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-900/40 dark:to-slate-800 group">
            <div class="p-3.5 pt-4">
                <h3 class="text-xs font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                    <span class="material-symbols-outlined notranslate text-[16px]" translate="no">cable</span> RX Power
                </h3>
                <div class="text-xl font-black {{ isset($deviceStatus['rx_power']) && floatval($deviceStatus['rx_power']) < -26 ? 'text-rose-600' : (isset($deviceStatus['rx_power']) && floatval($deviceStatus['rx_power']) !== 0.0 && floatval($deviceStatus['rx_power']) >= -24 ? 'text-emerald-600 dark:text-emerald-400' : 'text-indigo-700 dark:text-indigo-300') }} mb-1">
                    {{ $deviceStatus['rx_power'] ?? '-' }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                    <span>Redaman Fiber</span>
                    @if(isset($deviceStatus['rx_power']) && $deviceStatus['rx_power'] !== '-')
                        @php $rxVal = floatval($deviceStatus['rx_power']); @endphp
                        @if($rxVal < -27)
                            <span class="text-[10px] font-bold text-rose-600">Kritis</span>
                        @elseif($rxVal < -24)
                            <span class="text-[10px] font-bold text-amber-500">Cukup</span>
                        @else
                            <span class="text-[10px] font-bold text-emerald-600">Bagus</span>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- TX Power --}}
        <div class="relative overflow-x-auto rounded-xl border border-sky-200 shadow-sm bg-gradient-to-br from-sky-50 to-white dark:from-sky-900/40 dark:to-slate-800 group">
            <div class="p-3.5 pt-4">
                <h3 class="text-xs font-bold text-sky-500 dark:text-sky-400 uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                    <span class="material-symbols-outlined notranslate text-[16px]" translate="no">sensors</span> TX Power
                </h3>
                <div class="text-xl font-black text-sky-700 dark:text-sky-300 mb-1">
                    {{ $deviceStatus['tx_power'] ?? '-' }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400">Sinyal Laser PON</div>
            </div>
        </div>

        {{-- PPPoE WAN --}}
        <div class="relative overflow-x-auto rounded-xl border border-emerald-200 shadow-sm bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-900/40 dark:to-slate-800 group" x-data="{ showPppoePass: false }">
            <div class="p-3.5 pt-4">
                <div class="flex items-center justify-between mb-1.5">
                    <h3 class="text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest flex items-center gap-1.5">
                        <span class="material-symbols-outlined notranslate text-[16px]" translate="no">account_circle</span> PPPoE WAN
                    </h3>
                    <button type="button" wire:click="openPppoeModal" class="p-1 rounded-lg text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-colors" title="Ubah Akun PPPoE WAN">
                        <span class="material-symbols-outlined notranslate text-[15px]" translate="no">edit</span>
                    </button>
                </div>
                <div class="text-sm font-bold text-emerald-700 dark:text-emerald-300 mb-1 truncate" title="{{ !empty($deviceStatus['pppoe_username']) && $deviceStatus['pppoe_username'] !== '-' ? $deviceStatus['pppoe_username'] : '-' }}">
                    {{ !empty($deviceStatus['pppoe_username']) && $deviceStatus['pppoe_username'] !== '-' ? $deviceStatus['pppoe_username'] : '-' }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono flex items-center justify-between">
                    <span>Pass:</span>
                    @if(!empty($deviceStatus['pppoe_password']) && $deviceStatus['pppoe_password'] !== '-')
                        <div class="flex items-center gap-1">
                            <span x-text="showPppoePass ? '{{ $deviceStatus['pppoe_password'] }}' : '••••••••'" class="font-bold text-slate-700 dark:text-slate-300"></span>
                            <button type="button" @click="showPppoePass = !showPppoePass" class="text-emerald-600 hover:text-emerald-800">
                                <span class="material-symbols-outlined text-[13px]" x-text="showPppoePass ? 'visibility_off' : 'visibility'">visibility</span>
                            </button>
                        </div>
                    @else
                        <span class="font-bold text-slate-700 dark:text-slate-300">-</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Web GUI SuperAdmin --}}
        <div class="relative overflow-x-auto rounded-xl border border-amber-200 shadow-sm bg-gradient-to-br from-amber-50 to-white dark:from-amber-900/40 dark:to-slate-800 group" x-data="{ showPass: false }">
            <div class="p-3.5 pt-4">
                <div class="flex items-center justify-between mb-1.5">
                    <h3 class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-widest flex items-center gap-1.5">
                        <span class="material-symbols-outlined notranslate text-[16px]" translate="no">admin_panel_settings</span> Web SuperAdmin
                    </h3>
                    <button type="button" wire:click="openSuperadminModal" class="p-1 rounded-lg text-amber-600 dark:text-amber-400 hover:text-amber-800 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition-colors" title="Ubah Akun SuperAdmin Web">
                        <span class="material-symbols-outlined notranslate text-[15px]" translate="no">edit</span>
                    </button>
                </div>
                <div class="text-sm font-black text-amber-800 dark:text-amber-200 mb-1 font-mono truncate" title="User: {{ $deviceStatus['superadmin_user'] ?? 'admin' }}">
                    {{ $deviceStatus['superadmin_user'] ?? 'admin' }}
                </div>
                <div class="text-[11px] text-slate-600 dark:text-slate-400 font-mono flex items-center justify-between">
                    <span>Pass:</span>
                    <div class="flex items-center gap-1">
                        <span x-text="showPass ? '{{ $deviceStatus['superadmin_password'] ?? 'admin' }}' : '••••••••'" class="font-bold text-slate-800 dark:text-slate-200"></span>
                        <button type="button" @click="showPass = !showPass" class="text-amber-600 hover:text-amber-800">
                            <span class="material-symbols-outlined text-[13px]" x-text="showPass ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- WiFi Network --}}
        <div class="relative overflow-x-auto rounded-xl border border-purple-200 shadow-sm bg-gradient-to-br from-purple-50 to-white dark:from-purple-900/40 dark:to-slate-800 group">
            <div class="p-3.5 pt-4">
                <h3 class="text-xs font-bold text-purple-500 dark:text-purple-400 uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                    <span class="material-symbols-outlined notranslate text-[16px]" translate="no">wifi</span> WiFi Network
                </h3>
                <div class="text-sm font-bold text-purple-700 dark:text-purple-300 mb-1 truncate" title="{{ $deviceStatus['ssid_1'] ?? '-' }}">
                    {{ $deviceStatus['ssid_1'] ?? '-' }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono truncate" title="5G: {{ $deviceStatus['ssid_2'] ?? '-' }}">
                    5G: {{ $deviceStatus['ssid_2'] && $deviceStatus['ssid_2'] !== '-' ? $deviceStatus['ssid_2'] : 'Tidak aktif' }}
                </div>
            </div>
        </div>
    </div>

    {{-- NAVIGATION TABS BAR --}}
    <div class="border-b border-slate-200 dark:border-slate-700 overflow-x-auto">
        <nav class="flex space-x-2 sm:space-x-4 min-w-max" aria-label="Tabs">
            @php
                $tabs = [
                    'overview' => ['label' => 'Overview', 'icon' => 'dashboard'],
                    'wan' => ['label' => 'WAN Configuration', 'icon' => 'settings_ethernet', 'badge' => count($wanConnections)],
                    'hardware' => ['label' => 'Hardware Monitoring', 'icon' => 'memory'],
                    'optical' => ['label' => 'Optical Diagnostics', 'icon' => 'cable'],
                    'wifi' => ['label' => 'WiFi Management', 'icon' => 'wifi'],
                    'clients' => ['label' => 'Connected Clients', 'icon' => 'devices', 'badge' => count($connectedDevices)],
                    'topology' => ['label' => 'Network Topology', 'icon' => 'account_tree'],
                    'tasks' => ['label' => 'Activity & Tasks', 'icon' => 'history', 'badge' => count($recentTasks)],
                ];
            @endphp

            @foreach($tabs as $tabKey => $tab)
                @php $isActive = ($activeTab === $tabKey); @endphp
                <button wire:click="setTab('{{ $tabKey }}')"
                    class="group inline-flex items-center gap-2 py-3 px-3.5 border-b-2 font-bold text-xs transition-colors {{ $isActive ? 'border-purple-600 text-purple-600 dark:text-purple-400 dark:border-purple-400' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-200' }}">
                    <span class="material-symbols-outlined text-[18px] notranslate {{ $isActive ? 'text-purple-600 dark:text-purple-400' : 'text-slate-400 group-hover:text-slate-600' }}" translate="no">{{ $tab['icon'] }}</span>
                    <span>{{ $tab['label'] }}</span>
                    @if(isset($tab['badge']) && $tab['badge'] > 0)
                        <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $isActive ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                            {{ $tab['badge'] }}
                        </span>
                    @endif
                </button>
            @endforeach
        </nav>
    </div>

    {{-- TAB 1: OVERVIEW --}}
    @if($activeTab === 'overview')
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Device Details Table --}}
        <div class="xl:col-span-1 space-y-6">
            <div class="{{ $isNocLayout ? 'noc-panel-bg noc-border' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700' }} rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 {{ $isNocLayout ? 'noc-panel-bg noc-text' : 'bg-slate-50 dark:bg-slate-900/50' }} font-semibold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:18px">info</span>
                    Informasi Sistem
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700 text-xs">
                    <div class="p-3 flex justify-between bg-indigo-50/50 dark:bg-indigo-900/10">
                        <span class="text-slate-500 dark:text-slate-400 font-medium">Pelanggan</span>
                        <span class="font-bold text-indigo-700 dark:text-indigo-400 truncate max-w-[180px]" title="{{ $deviceStatus['customer_name'] ?? '-' }}">{{ $deviceStatus['customer_name'] ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Manufacturer</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $hardwareMetrics['manufacturer'] ?? ($device->manufacturer ?? '-') }}</span>
                    </div>
                    <div class="p-3 flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Product Class / Model</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $hardwareMetrics['product_class'] ?? ($device->product_class ?? '-') }}</span>
                    </div>
                    <div class="p-3 flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Hardware Ver</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $hardwareMetrics['hardware_version'] ?? ($device->hardware_version ?? '-') }}</span>
                    </div>
                    <div class="p-3 flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Software Ver</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $hardwareMetrics['software_version'] ?? ($device->software_version ?? '-') }}</span>
                    </div>
                    <div class="p-3 flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Device Uptime</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100 font-mono">{{ $hardwareMetrics['uptime_display'] ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Last Inform</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $device->last_inform?->format('d/m/Y H:i') ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex justify-between bg-indigo-50/50 dark:bg-indigo-900/10">
                        <span class="text-slate-500 dark:text-slate-400 font-medium">WAN IP</span>
                        <span class="font-bold text-indigo-700 dark:text-indigo-400">{{ $deviceStatus['wan_ip'] ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex justify-between bg-indigo-50/50 dark:bg-indigo-900/10">
                        <span class="text-slate-500 dark:text-slate-400 font-medium">WAN MAC</span>
                        <span class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300 mt-0.5">{{ $deviceStatus['wan_mac'] ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Connected Clients Snapshot --}}
        <div class="xl:col-span-2 space-y-6">
            <div class="{{ $isNocLayout ? 'noc-panel-bg noc-border' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700' }} rounded-xl shadow-sm overflow-hidden flex flex-col h-full">
                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 {{ $isNocLayout ? 'noc-panel-bg noc-text' : 'bg-slate-50 dark:bg-slate-900/50' }} font-semibold text-slate-800 dark:text-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-emerald-500 dark:text-emerald-400" translate="no" style="font-size:18px">devices</span>
                        <span>Perangkat Klien Tersambung</span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                            {{ count($connectedDevices ?? []) }}
                        </span>
                    </div>
                    <button wire:click="loadConnectedDevices" wire:loading.attr="disabled" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-300 flex items-center gap-1.5 px-2.5 py-1 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="loadConnectedDevices" class="material-symbols-outlined notranslate text-[16px]" translate="no">refresh</span>
                        <span wire:loading wire:target="loadConnectedDevices" class="material-symbols-outlined animate-spin text-[16px]">progress_activity</span>
                        <span wire:loading.remove wire:target="loadConnectedDevices">Refresh Klien</span>
                        <span wire:loading wire:target="loadConnectedDevices">Memperbarui...</span>
                    </button>
                </div>
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-xs text-left">
                        <thead class="{{ $isNocLayout ? 'noc-panel-bg noc-text' : 'bg-slate-50 dark:bg-slate-900/50' }} uppercase text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="px-4 py-2.5 font-semibold">Hostname / Nama</th>
                                <th class="px-4 py-2.5 font-semibold">IP Address</th>
                                <th class="px-4 py-2.5 font-semibold">MAC Address</th>
                                <th class="px-4 py-2.5 font-semibold">Jaringan</th>
                                <th class="px-4 py-2.5 font-semibold text-right">Sinyal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse(array_slice($connectedDevices ?? [], 0, 5) as $host)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors">
                                    <td class="px-4 py-2.5 font-medium text-slate-900 dark:text-slate-100">
                                        <div class="flex items-center gap-2">
                                            @if(str_contains(strtolower($host['InterfaceType'] ?? ''), 'lan') || ($host['Layer1Interface'] ?? '') === 'Ethernet')
                                                <span class="material-symbols-outlined text-blue-500 text-[16px]">lan</span>
                                            @else
                                                <span class="material-symbols-outlined text-indigo-500 text-[16px]">wifi</span>
                                            @endif
                                            <span class="truncate max-w-[160px]" title="{{ $host['HostName'] ?? 'Perangkat Klien' }}">
                                                {{ $host['HostName'] ?? 'Perangkat Klien' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2.5 font-mono text-slate-700 dark:text-slate-300">{{ $host['IPAddress'] ?? '-' }}</td>
                                    <td class="px-4 py-2.5 font-mono text-slate-500 dark:text-slate-400">{{ $host['MACAddress'] ?? '-' }}</td>
                                    <td class="px-4 py-2.5">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300">
                                            {{ $host['InterfaceType'] ?? $host['Layer1Interface'] ?? 'WiFi' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-mono">{{ $host['RSSI'] ?? '-' }} dBm</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                        <span class="material-symbols-outlined notranslate text-3xl mb-1 text-slate-300" translate="no">wifi_tethering_off</span>
                                        <p>Tidak ada perangkat yang terhubung</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB 2: WAN CONFIGURATION (DYNAMIC CRUD) --}}
    @if($activeTab === 'wan')
    <div class="space-y-4">
        {{-- WAN Toolbar & Subtabs --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl border border-purple-200 dark:border-purple-800/60 bg-purple-50/30 dark:bg-purple-950/20">
            <div class="flex items-center gap-2">
                <button wire:click="setWanSubTab('ppp')" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $wanSubTab === 'ppp' ? 'bg-purple-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
                    Tab WAN PPP ({{ count(array_filter($wanConnections, fn($w) => $w['type'] === 'PPPoE')) }})
                </button>
                <button wire:click="setWanSubTab('ip')" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $wanSubTab === 'ip' ? 'bg-purple-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
                    Tab WAN IP / Bridge ({{ count(array_filter($wanConnections, fn($w) => $w['type'] !== 'PPPoE')) }})
                </button>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="refreshWan" wire:loading.attr="disabled" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition-colors" title="Kirim instruksi refresh TR-069 ke modem">
                    <span wire:loading.remove wire:target="refreshWan" class="material-symbols-outlined text-[15px]">refresh</span>
                    <span wire:loading wire:target="refreshWan" class="material-symbols-outlined animate-spin text-[15px]">progress_activity</span>
                    <span>Refresh WAN</span>
                </button>
                <button wire:click="openCreateWanModal" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold text-white bg-purple-600 hover:bg-purple-700 shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-[16px]">add</span>
                    <span>+ Tambah WAN Baru</span>
                </button>
            </div>
        </div>

        {{-- Dynamic WAN Connections Table --}}
        <div class="overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 shadow-sm">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="uppercase tracking-wider font-bold text-[10px] bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Nama Koneksi</th>
                        <th class="px-4 py-3">Tipe & Mode</th>
                        <th class="px-4 py-3 text-center">VLAN</th>
                        <th class="px-4 py-3 text-center">NAT</th>
                        <th class="px-4 py-3 text-center">Status & Kesesuaian</th>
                        <th class="px-4 py-3">Kredensial & Service</th>
                        <th class="px-4 py-3">Port Binding</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @php
                        $filteredWans = array_filter($wanConnections, function($w) use ($wanSubTab) {
                            return ($wanSubTab === 'ppp') ? ($w['type'] === 'PPPoE') : ($w['type'] !== 'PPPoE');
                        });
                    @endphp

                    @forelse($filteredWans as $wan)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/30 transition-colors">
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                            <div class="font-mono font-semibold">{{ $wan['name'] }}</div>
                            <div class="text-[10px] text-slate-400 truncate max-w-[220px]" title="{{ $wan['fullPath'] }}">{{ $wan['fullPath'] }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $isBridge = str_contains($wan['mode_label'] ?? '', 'Bridge');
                                $badgeColor = $isBridge 
                                    ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-400 dark:border-amber-800' 
                                    : 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/50 dark:text-purple-400 dark:border-purple-800';
                            @endphp
                            <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $badgeColor }}">
                                {{ $wan['mode_label'] ?? $wan['type'] }}
                            </span>
                            @if(!empty($wan['connection_type']) && $wan['connection_type'] !== '-')
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $wan['connection_type'] }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center font-mono font-bold text-slate-800 dark:text-slate-200">
                            @if($wan['vlan'])
                                <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700 rounded text-purple-600 dark:text-purple-400">{{ $wan['vlan'] }}</span>
                            @else
                                <span class="text-slate-400">Untagged</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($wan['nat'])
                                <span class="text-emerald-500 material-symbols-outlined text-[18px]" title="NAT Aktif">check_circle</span>
                            @else
                                <span class="text-slate-300 dark:text-slate-600 material-symbols-outlined text-[18px]" title="NAT Nonaktif">cancel</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if(($wan['desired_status'] ?? '') === 'Mismatch')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/50 dark:text-rose-400 dark:border-rose-800" title="{{ implode('; ', $wan['mismatch_details'] ?? []) }}">
                                    <span class="material-symbols-outlined text-[13px]">warning</span>
                                    <span>Mismatch</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-400 dark:border-emerald-800">
                                    <span class="material-symbols-outlined text-[13px]">check</span>
                                    <span>Actual</span>
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-[11px] space-y-0.5">
                            @if($wan['username'])
                                <div><span class="text-slate-400 font-medium">User:</span> <span class="font-mono font-semibold text-slate-800 dark:text-slate-200">{{ $wan['username'] }}</span></div>
                            @endif
                            <div><span class="text-slate-400 font-medium">Service:</span> <span class="font-semibold text-purple-600 dark:text-purple-400">{{ $wan['service_list'] }}</span></div>
                            @if($wan['ip_address'])
                                <div><span class="text-slate-400 font-medium">IP:</span> <span class="font-mono">{{ $wan['ip_address'] }}</span></div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-[11px]">
                            @if($wan['port_bind'])
                                <div class="truncate max-w-[220px]" title="{{ $wan['port_bind'] }}">
                                    <span class="text-slate-600 dark:text-slate-300 font-mono">{{ $wan['port_bind'] }}</span>
                                </div>
                            @else
                                <span class="text-slate-400 italic">Semua Port Default</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <button wire:click="openEditWanModal('{{ $wan['fullPath'] }}')" class="px-2.5 py-1 text-xs font-semibold text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 rounded hover:bg-purple-50 dark:hover:bg-purple-950/40 transition-colors">
                                    Edit
                                </button>
                                @if(!$wan['is_tr069'])
                                    <button wire:click="deleteWan('{{ $wan['fullPath'] }}')" 
                                            wire:confirm="Apakah Anda yakin ingin menghapus koneksi WAN '{{ $wan['name'] }}'? Perintah penghapusan akan dikirim ke modem."
                                            class="px-2.5 py-1 text-xs font-semibold text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 rounded hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                                            title="Hapus Koneksi WAN ini">
                                        Hapus
                                    </button>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-semibold text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800 rounded cursor-not-allowed" title="Koneksi TR-069 dilindungi">
                                        Terkunci
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                            <span class="material-symbols-outlined text-[36px] text-slate-300 dark:text-slate-600 mb-1">wifi_off</span>
                            <p class="font-medium text-slate-600 dark:text-slate-300">Tidak ada koneksi WAN pada tab ini.</p>
                            <p class="text-[11px] text-slate-400">Klik tombol <strong>+ Tambah WAN Baru</strong> untuk mengonfigurasi koneksi baru.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- LAN Port Binding Engine Status Card --}}
        @if(!empty($portBindings['ports']))
        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-purple-600 text-[18px]">lan</span>
                        Pemetaan Fisik Port Binding LAN (LAN1 - LAN4)
                    </h4>
                    <p class="text-[11px] text-slate-400">Hubungan binding aktual antara port fisik LAN dengan interface WAN modem.</p>
                </div>
                <span class="text-[11px] font-mono font-semibold px-2.5 py-1 rounded bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                    Vendor: {{ strtoupper($portBindings['vendor'] ?? $detectedVendor) }}
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach($portBindings['ports'] as $portKey => $port)
                <div class="p-3 rounded-lg border {{ $port['is_bound'] ? 'border-purple-200 bg-purple-50/20 dark:border-purple-900/40 dark:bg-purple-950/10' : 'border-slate-200 bg-slate-50/40 dark:border-slate-700 dark:bg-slate-900/30' }}">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-bold text-xs text-slate-800 dark:text-slate-200 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px] {{ $port['is_bound'] ? 'text-purple-600' : 'text-slate-400' }}">router</span>
                            {{ $portKey }}
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ $port['is_bound'] ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                            {{ $port['is_bound'] ? 'Bound' : 'Unbound' }}
                        </span>
                    </div>
                    <div class="text-[11px] text-slate-600 dark:text-slate-300 font-medium truncate" title="{{ $port['bound_wan_name'] }}">
                        {{ $port['bound_wan_name'] }}
                    </div>
                    <div class="mt-1 flex items-center justify-between text-[10px] text-slate-400 font-mono">
                        <span>Speed: {{ $port['speed'] }}</span>
                        @if($port['vlan'])
                            <span class="text-purple-600 dark:text-purple-400 font-bold">VLAN {{ $port['vlan'] }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- TAB 3: HARDWARE MONITORING & DIAGNOSTICS --}}
    @if($activeTab === 'hardware')
    <div class="space-y-6">
        {{-- Action Bar --}}
        <div class="flex items-center justify-between p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-purple-600">memory</span>
                    Telemetri Hardware & Sumber Daya ONT
                </h3>
                <p class="text-xs text-slate-400">Parameter CPU, RAM, Suhu, dan Memori ONT terbaca langsung dari modem via GenieACS TR-069.</p>
            </div>
            <button wire:click="refreshHardware" wire:loading.attr="disabled" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                <span wire:loading.remove wire:target="refreshHardware" class="material-symbols-outlined text-[16px]">refresh</span>
                <span wire:loading wire:target="refreshHardware" class="material-symbols-outlined animate-spin text-[16px]">progress_activity</span>
                <span>Refresh Telemetri Hardware</span>
            </button>
        </div>

        {{-- 4 Metric Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- CPU Utilization --}}
            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-blue-500 text-[18px]">speed</span>
                        CPU Utilization
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $hardwareMetrics['cpu_status'] === 'supported' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                        {{ $hardwareMetrics['cpu_status'] === 'supported' ? 'Supported' : 'Tidak Didukung' }}
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-slate-100">
                    {{ $hardwareMetrics['cpu_usage_display'] ?? 'Tidak Didukung' }}
                </div>
                @if($hardwareMetrics['cpu_usage'] !== null)
                <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, max(0, (float)$hardwareMetrics['cpu_usage'])) }}%"></div>
                </div>
                @endif
                <p class="text-[11px] text-slate-400">Beban prosesor utama ONT.</p>
            </div>

            {{-- CPU Temperature --}}
            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-rose-500 text-[18px]">device_thermostat</span>
                        Suhu / Temperature
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $hardwareMetrics['temperature_status'] === 'supported' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                        {{ $hardwareMetrics['temperature_status'] === 'supported' ? 'Supported' : 'Tidak Didukung' }}
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-slate-100">
                    {{ $hardwareMetrics['temperature_display'] ?? 'Tidak Didukung' }}
                </div>
                <p class="text-[11px] text-slate-400">Suhu sensor transceiver / chip ONT.</p>
            </div>

            {{-- RAM Utilization --}}
            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-purple-500 text-[18px]">memory</span>
                        RAM Utilization
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $hardwareMetrics['ram_status'] === 'supported' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                        {{ $hardwareMetrics['ram_status'] === 'supported' ? 'Supported' : 'Tidak Didukung' }}
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-slate-100">
                    {{ $hardwareMetrics['ram_usage_display'] ?? 'Tidak Didukung' }}
                </div>
                @if($hardwareMetrics['ram_total_display'])
                    <div class="text-[11px] text-slate-500 font-mono">Total: {{ $hardwareMetrics['ram_total_display'] }} | Sisa: {{ $hardwareMetrics['ram_free_display'] ?? '-' }}</div>
                @else
                    <p class="text-[11px] text-slate-400">Penggunaan memori operasional.</p>
                @endif
            </div>

            {{-- Flash Storage / Uptime --}}
            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-amber-500 text-[18px]">schedule</span>
                        Device Uptime
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $hardwareMetrics['uptime_status'] === 'supported' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                        {{ $hardwareMetrics['uptime_status'] === 'supported' ? 'Supported' : 'Tidak Didukung' }}
                    </span>
                </div>
                <div class="text-xl font-black text-slate-900 dark:text-slate-100 font-mono">
                    {{ $hardwareMetrics['uptime_display'] ?? 'Tidak Didukung' }}
                </div>
                <p class="text-[11px] text-slate-400">Lama waktu modem menyala sejak restart.</p>
            </div>
        </div>

        {{-- Detailed Specifications Card --}}
        <div class="p-5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Identifikasi Model & Firmware Aktual</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/50">
                    <span class="text-slate-400 block mb-0.5">Vendor / Pabrikan</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 uppercase">{{ $hardwareMetrics['vendor'] }} ({{ $hardwareMetrics['manufacturer'] }})</span>
                </div>
                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/50">
                    <span class="text-slate-400 block mb-0.5">Model / Product Class</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $hardwareMetrics['product_class'] }}</span>
                </div>
                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/50">
                    <span class="text-slate-400 block mb-0.5">Hardware Version</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 font-mono">{{ $hardwareMetrics['hardware_version'] }}</span>
                </div>
                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/50">
                    <span class="text-slate-400 block mb-0.5">Software / Firmware Version</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 font-mono">{{ $hardwareMetrics['software_version'] }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB 4: OPTICAL DIAGNOSTICS --}}
    @if($activeTab === 'optical')
    <div class="space-y-6">
        {{-- Status & Freshness Banner --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ ($opticalDiagnostics['is_los'] ?? false) ? 'bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400' : 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400' }}">
                    <span class="material-symbols-outlined notranslate text-[24px]" translate="no">{{ ($opticalDiagnostics['is_los'] ?? false) ? 'link_off' : 'settings_input_antenna' }}</span>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Status Tautan GPON & Diagnostik Laser</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Status LOS: <strong class="{{ ($opticalDiagnostics['is_los'] ?? false) ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $opticalDiagnostics['los_status'] ?? 'Normal' }}</strong>
                        • Freshness: <span class="capitalize font-semibold text-slate-700 dark:text-slate-300">{{ $opticalDiagnostics['freshness_status'] ?? 'fresh' }}</span>
                    </p>
                </div>
            </div>
            <button wire:click="refreshOptical" wire:loading.attr="disabled" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-300 transition-colors">
                <span wire:loading.remove wire:target="refreshOptical" class="material-symbols-outlined notranslate text-[15px]" translate="no">refresh</span>
                <span wire:loading wire:target="refreshOptical" class="material-symbols-outlined notranslate animate-spin text-[15px]" translate="no">progress_activity</span>
                <span>Refresh Optik Live</span>
            </button>
        </div>

        {{-- Primary Optical Power Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 rounded-xl border border-indigo-200 bg-indigo-50/30 dark:bg-slate-800 dark:border-indigo-900/50 shadow-sm space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">ONU RX Power (TR-069)</span>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ ($opticalDiagnostics['rx_status'] ?? '') === 'normal' ? 'bg-emerald-100 text-emerald-800' : (($opticalDiagnostics['rx_status'] ?? '') === 'warning' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                        {{ strtoupper($opticalDiagnostics['rx_status'] ?? 'UNKNOWN') }}
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ $opticalDiagnostics['rx_power_display'] ?? ($deviceStatus['rx_power'] ?? '-') }}</div>
                <p class="text-[11px] text-slate-400">Standar optimal: -15.0 s/d -24.0 dBm</p>
                @if(!empty($opticalDiagnostics['rx_raw']))
                    <div class="pt-1 text-[10px] text-slate-500 font-mono border-t border-indigo-100 dark:border-indigo-950">
                        Raw: {{ $opticalDiagnostics['rx_raw'] }} ({{ $opticalDiagnostics['rx_formula'] ?? 'direct' }})
                    </div>
                @endif
            </div>

            <div class="p-4 rounded-xl border border-sky-200 bg-sky-50/30 dark:bg-slate-800 dark:border-sky-900/50 shadow-sm space-y-1">
                <span class="text-xs font-bold text-sky-600 dark:text-sky-400 uppercase tracking-wider">ONU TX Power</span>
                <div class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ $opticalDiagnostics['tx_power_display'] ?? ($deviceStatus['tx_power'] ?? '-') }}</div>
                <p class="text-[11px] text-slate-400">Pancar laser upstream (+0.5 s/d +5.0 dBm)</p>
                @if(!empty($opticalDiagnostics['tx_raw']))
                    <div class="pt-1 text-[10px] text-slate-500 font-mono border-t border-sky-100 dark:border-sky-950">
                        Raw: {{ $opticalDiagnostics['tx_raw'] }} ({{ $opticalDiagnostics['tx_formula'] ?? 'direct' }})
                    </div>
                @endif
            </div>

            <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/30 dark:bg-slate-800 dark:border-emerald-900/50 shadow-sm space-y-1">
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">OLT RX Power (DDM)</span>
                <div class="text-2xl font-black text-slate-900 dark:text-slate-100">
                    {{ $device->onu?->rx_power_dbm !== null ? round((float)$device->onu->rx_power_dbm, 2) . ' dBm' : '-' }}
                </div>
                <p class="text-[11px] text-slate-400">Diterima port PON OLT</p>
                <div class="pt-1 text-[10px] text-slate-500 font-mono border-t border-emerald-100 dark:border-emerald-950">
                    SNMP OLT Telemetry
                </div>
            </div>

            <div class="p-4 rounded-xl border border-purple-200 bg-purple-50/30 dark:bg-slate-800 dark:border-purple-900/50 shadow-sm space-y-1">
                <span class="text-xs font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider">Jarak Optik / Distance</span>
                <div class="text-2xl font-black text-slate-900 dark:text-slate-100">
                    {{ $device->onu?->distance_meters !== null ? round((float)$device->onu->distance_meters / 1000.0, 2) . ' km' : ($device->onu?->distance !== null ? $device->onu->distance . ' m' : '-') }}
                </div>
                <p class="text-[11px] text-slate-400">Estimasi bentangan kabel dari OLT</p>
                <div class="pt-1 text-[10px] text-slate-500 font-mono border-t border-purple-100 dark:border-purple-950">
                    Fiber Distance
                </div>
            </div>
        </div>

        {{-- Secondary Physical Diagnostics: Voltage, Bias Current, Temperature --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/20 dark:bg-slate-800 dark:border-amber-900/50 shadow-sm space-y-1">
                <span class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Tegangan Modul (Supply Voltage)</span>
                <div class="text-xl font-black text-slate-900 dark:text-slate-100">{{ $opticalDiagnostics['voltage_display'] ?? 'Tidak Didukung' }}</div>
                <p class="text-[11px] text-slate-400">Standar nominal GPON: 3.1 V s/d 3.5 V</p>
                @if(!empty($opticalDiagnostics['voltage_raw']))
                    <div class="pt-1 text-[10px] text-slate-500 font-mono border-t border-amber-100 dark:border-amber-950">
                        Raw: {{ $opticalDiagnostics['voltage_raw'] }} ({{ $opticalDiagnostics['voltage_formula'] ?? 'direct' }})
                    </div>
                @endif
            </div>

            <div class="p-4 rounded-xl border border-teal-200 bg-teal-50/20 dark:bg-slate-800 dark:border-teal-900/50 shadow-sm space-y-1">
                <span class="text-xs font-bold text-teal-600 dark:text-teal-400 uppercase tracking-wider">Arus Bias Laser (Bias Current)</span>
                <div class="text-xl font-black text-slate-900 dark:text-slate-100">{{ $opticalDiagnostics['bias_display'] ?? 'Tidak Didukung' }}</div>
                <p class="text-[11px] text-slate-400">Standar operasional: 5 mA s/d 40 mA</p>
                @if(!empty($opticalDiagnostics['bias_raw']))
                    <div class="pt-1 text-[10px] text-slate-500 font-mono border-t border-teal-100 dark:border-teal-950">
                        Raw: {{ $opticalDiagnostics['bias_raw'] }} ({{ $opticalDiagnostics['bias_formula'] ?? 'direct' }})
                    </div>
                @endif
            </div>

            <div class="p-4 rounded-xl border border-rose-200 bg-rose-50/20 dark:bg-slate-800 dark:border-rose-900/50 shadow-sm space-y-1">
                <span class="text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Suhu Transceiver Optik</span>
                <div class="text-xl font-black text-slate-900 dark:text-slate-100">{{ $opticalDiagnostics['temperature'] ?? ($deviceStatus['temperature'] ?? 'Tidak Didukung') }}</div>
                <p class="text-[11px] text-slate-400">Suhu sensor internal optik ONU</p>
                @if(!empty($opticalDiagnostics['temperature_raw']))
                    <div class="pt-1 text-[10px] text-slate-500 font-mono border-t border-rose-100 dark:border-rose-950">
                        Raw: {{ $opticalDiagnostics['temperature_raw'] }} ({{ $opticalDiagnostics['temperature_formula'] ?? 'direct' }})
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- TAB 5: WIFI MANAGEMENT (Phase 2.2 Accurate Radio & Multi-SSID Mapping) --}}
    @if($activeTab === 'wifi')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-600 text-[20px]">wifi</span>
                    Manajemen Frekuensi Wi-Fi ONT
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Total terdeteksi: <strong>{{ $wifiDetails['total_ssids'] ?? 0 }} SSID aktif</strong> pada pita
                    <strong>{{ implode(', ', $wifiDetails['bands_supported'] ?? ['2.4GHz']) }}</strong>.
                </p>
            </div>
            <button wire:click="openWifiModal" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[16px]">wifi_settings</span>
                <span>Ubah SSID / Sandi WiFi</span>
            </button>
        </div>

        {{-- 1. Pita 2.4 GHz --}}
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">wifi</span>
                    Pita Frekuensi 2.4 GHz (Jangkauan Luas • 802.11b/g/n)
                </h4>
                <span class="text-[11px] text-slate-400">{{ count($wifiDetails['wifi_24ghz'] ?? []) }} SSID Terdeteksi</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse($wifiDetails['wifi_24ghz'] ?? [] as $wlan)
                <div class="p-4 rounded-xl border border-indigo-200 dark:border-indigo-800/60 bg-indigo-50/20 dark:bg-slate-800 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/60 dark:text-indigo-300">
                                SSID #{{ $wlan['display_index'] }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono">Instance {{ $wlan['instance'] }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $wlan['enabled'] ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-500' }}">
                            {{ $wlan['enabled'] ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <div>
                        <div class="text-lg font-black text-slate-900 dark:text-slate-100 tracking-wide font-mono">{{ $wlan['ssid'] }}</div>
                        <div class="text-xs text-slate-500 font-mono mt-0.5">BSSID: {{ $wlan['bssid'] ?? '-' }}</div>
                    </div>

                    <div class="pt-2 border-t border-indigo-100 dark:border-indigo-950/60 grid grid-cols-2 gap-2 text-[11px]">
                        <div><span class="text-slate-400">Keamanan:</span> <strong class="text-slate-700 dark:text-slate-300">{{ $wlan['security_mode'] }}</strong></div>
                        <div><span class="text-slate-400">Channel:</span> <strong class="text-slate-700 dark:text-slate-300 font-mono">{{ $wlan['channel'] }}</strong></div>
                        <div><span class="text-slate-400">Visibilitas:</span> <strong class="text-slate-700 dark:text-slate-300">{{ $wlan['hidden'] ? 'Tersembunyi' : 'Terlihat' }}</strong></div>
                        <div><span class="text-slate-400">Sandi:</span> <strong class="text-slate-700 dark:text-slate-300 font-mono">{{ $wlan['password_masked'] }}</strong></div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button wire:click="openEditSpecificWifiModal({{ $wlan['instance'] }})" class="px-3 py-1 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-300 rounded-lg transition-colors">
                            Edit SSID Ini
                        </button>
                    </div>
                </div>
                @empty
                <div class="col-span-2 p-6 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 text-center text-slate-400 text-xs">
                    Tidak ada SSID 2.4 GHz yang terkonfigurasi pada perangkat.
                </div>
                @endforelse
            </div>
        </div>

        {{-- 2. Pita 5 GHz Dual-Band --}}
        <div class="space-y-3 pt-4 border-t border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-bold uppercase tracking-wider text-purple-700 dark:text-purple-400 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">wifi_tethering</span>
                    Pita Frekuensi 5 GHz (Kecepatan Tinggi • 802.11ac/ax)
                </h4>
                @if(!empty($wifiDetails['has_5ghz']))
                    <span class="text-[11px] text-slate-400">{{ count($wifiDetails['wifi_5ghz'] ?? []) }} SSID Terdeteksi</span>
                @else
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-500">Single-Band Only</span>
                @endif
            </div>

            @if(!empty($wifiDetails['has_5ghz']) && !empty($wifiDetails['wifi_5ghz']))
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($wifiDetails['wifi_5ghz'] as $wlan)
                <div class="p-4 rounded-xl border border-purple-200 dark:border-purple-800/60 bg-purple-50/20 dark:bg-slate-800 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300">
                                SSID #{{ $wlan['display_index'] }} (5G)
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono">Instance {{ $wlan['instance'] }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $wlan['enabled'] ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-500' }}">
                            {{ $wlan['enabled'] ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <div>
                        <div class="text-lg font-black text-slate-900 dark:text-slate-100 tracking-wide font-mono">{{ $wlan['ssid'] }}</div>
                        <div class="text-xs text-slate-500 font-mono mt-0.5">BSSID: {{ $wlan['bssid'] ?? '-' }}</div>
                    </div>

                    <div class="pt-2 border-t border-purple-100 dark:border-purple-950/60 grid grid-cols-2 gap-2 text-[11px]">
                        <div><span class="text-slate-400">Keamanan:</span> <strong class="text-slate-700 dark:text-slate-300">{{ $wlan['security_mode'] }}</strong></div>
                        <div><span class="text-slate-400">Channel:</span> <strong class="text-slate-700 dark:text-slate-300 font-mono">{{ $wlan['channel'] }}</strong></div>
                        <div><span class="text-slate-400">Visibilitas:</span> <strong class="text-slate-700 dark:text-slate-300">{{ $wlan['hidden'] ? 'Tersembunyi' : 'Terlihat' }}</strong></div>
                        <div><span class="text-slate-400">Sandi:</span> <strong class="text-slate-700 dark:text-slate-300 font-mono">{{ $wlan['password_masked'] }}</strong></div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button wire:click="openEditSpecificWifiModal({{ $wlan['instance'] }})" class="px-3 py-1 text-xs font-semibold text-purple-600 bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/60 dark:text-purple-300 rounded-lg transition-colors">
                            Edit SSID Ini
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-center text-slate-500 dark:text-slate-400 text-xs flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-slate-400">info</span>
                <span>Perangkat ini adalah <strong>ONT Single-Band 2.4 GHz</strong> (Tidak memiliki modul radio frekuensi 5 GHz).</span>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- TAB 6: CONNECTED CLIENTS --}}
    @if($activeTab === 'clients')
    <div class="space-y-4">
        <div class="flex items-center justify-between p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Daftar Klien Terhubung (DHCP & WLAN Hosts)</h3>
                <p class="text-xs text-slate-400">Total terdeteksi: {{ count($connectedDevices) }} perangkat aktif tersambung ke modem.</p>
            </div>
            <button wire:click="loadConnectedDevices" wire:loading.attr="disabled" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-300 transition-colors">
                <span wire:loading.remove wire:target="loadConnectedDevices" class="material-symbols-outlined text-[16px]">refresh</span>
                <span wire:loading wire:target="loadConnectedDevices" class="material-symbols-outlined animate-spin text-[16px]">progress_activity</span>
                <span>Refresh Klien</span>
            </button>
        </div>

        <div class="overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 shadow-sm">
            <table class="w-full text-xs text-left">
                <thead class="uppercase tracking-wider font-bold text-[10px] bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Hostname / Perangkat</th>
                        <th class="px-4 py-3">IP Address</th>
                        <th class="px-4 py-3">MAC Address</th>
                        <th class="px-4 py-3">Jaringan Interface</th>
                        <th class="px-4 py-3 text-right">Sinyal (RSSI)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($connectedDevices ?? [] as $host)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors">
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                            <div class="flex items-center gap-2">
                                @if(str_contains(strtolower($host['InterfaceType'] ?? ''), 'lan') || ($host['Layer1Interface'] ?? '') === 'Ethernet')
                                    <span class="material-symbols-outlined text-blue-500 text-[18px]">lan</span>
                                @else
                                    <span class="material-symbols-outlined text-indigo-500 text-[18px]">wifi</span>
                                @endif
                                <span class="font-semibold">{{ $host['HostName'] ?? 'Perangkat Klien' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-mono">{{ $host['IPAddress'] ?? '-' }}</td>
                        <td class="px-4 py-3 font-mono text-slate-500">{{ $host['MACAddress'] ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                {{ $host['InterfaceType'] ?? $host['Layer1Interface'] ?? 'WiFi' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-mono">{{ $host['RSSI'] ?? '-' }} dBm</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400">Tidak ada perangkat yang terhubung saat ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- TAB 7: NETWORK TOPOLOGY --}}
    @if($activeTab === 'topology')
    <div class="p-6 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm space-y-6">
        <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Topologi Jaringan Fiber Optik</h3>
            <p class="text-xs text-slate-400">Jalur distribusi fisik dari OLT sentral hingga ke modem pelanggan.</p>
        </div>

        {{-- Visual Flow Diagram --}}
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 py-6 px-4 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-200 dark:border-slate-700">
            {{-- Node 1: OLT --}}
            <div class="flex flex-col items-center text-center p-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm w-full md:w-36">
                <span class="material-symbols-outlined text-blue-600 text-3xl mb-1">dns</span>
                <span class="text-[10px] uppercase font-bold text-slate-400">OLT</span>
                <span class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate w-full">{{ $topologyData['olt'] ?? 'OLT' }}</span>
                <span class="text-[10px] text-slate-500 font-mono">{{ $topologyData['olt_ip'] ?? '-' }}</span>
            </div>

            <span class="material-symbols-outlined text-slate-400 text-xl hidden md:block">arrow_forward</span>

            {{-- Node 2: PON Port --}}
            <div class="flex flex-col items-center text-center p-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm w-full md:w-32">
                <span class="material-symbols-outlined text-sky-600 text-3xl mb-1">settings_input_component</span>
                <span class="text-[10px] uppercase font-bold text-slate-400">Port PON</span>
                <span class="text-xs font-bold text-slate-900 dark:text-slate-100">{{ $topologyData['pon_port'] ?? '-' }}</span>
            </div>

            <span class="material-symbols-outlined text-slate-400 text-xl hidden md:block">arrow_forward</span>

            {{-- Node 3: ODC --}}
            <div class="flex flex-col items-center text-center p-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm w-full md:w-32">
                <span class="material-symbols-outlined text-amber-600 text-3xl mb-1">domain</span>
                <span class="text-[10px] uppercase font-bold text-slate-400">ODC</span>
                <span class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate w-full">{{ $topologyData['odc'] ?? '-' }}</span>
            </div>

            <span class="material-symbols-outlined text-slate-400 text-xl hidden md:block">arrow_forward</span>

            {{-- Node 4: ODP & Splitter --}}
            <div class="flex flex-col items-center text-center p-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm w-full md:w-36">
                <span class="material-symbols-outlined text-emerald-600 text-3xl mb-1">alt_route</span>
                <span class="text-[10px] uppercase font-bold text-slate-400">ODP & Splitter</span>
                <span class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate w-full">{{ $topologyData['odp'] ?? '-' }}</span>
                <span class="text-[10px] text-slate-500">Rasio {{ $topologyData['splitter'] ?? '1:8' }}</span>
            </div>

            <span class="material-symbols-outlined text-slate-400 text-xl hidden md:block">arrow_forward</span>

            {{-- Node 5: ONU Modem --}}
            <div class="flex flex-col items-center text-center p-3 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800 shadow-sm w-full md:w-40 ring-2 ring-purple-500">
                <span class="material-symbols-outlined text-purple-600 text-3xl mb-1">router</span>
                <span class="text-[10px] uppercase font-bold text-purple-600">ONT / Modem</span>
                <span class="text-xs font-bold text-slate-900 dark:text-slate-100 font-mono">{{ $topologyData['onu'] ?? '-' }}</span>
                <span class="text-[10px] text-slate-500 truncate w-full">{{ $topologyData['customer'] ?? '-' }}</span>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB 8: ACTIVITY & TASKS --}}
    @if($activeTab === 'tasks')
    <div class="space-y-4">
        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm">
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Riwayat Tugas & Perubahan Konfigurasi</h3>
            <p class="text-xs text-slate-400">Catatan eksekusi perintah GenieACS dan sinkronisasi TR-069.</p>
        </div>

        <div class="overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 shadow-sm">
            <table class="w-full text-xs text-left">
                <thead class="uppercase tracking-wider font-bold text-[10px] bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Task ID / UUID</th>
                        <th class="px-4 py-3">Tipe Operasi</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Waktu Eksekusi</th>
                        <th class="px-4 py-3">Detail Parameter</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($recentTasks as $t)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors">
                        <td class="px-4 py-3 font-mono text-[11px] text-slate-500">{{ $t['uuid'] ?? '-' }}</td>
                        <td class="px-4 py-3 font-bold text-slate-800 dark:text-slate-200 uppercase">{{ $t['type'] ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $st = strtolower($t['status'] ?? 'pending');
                                $stColor = match($st) {
                                    'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
                                    'failed', 'fault' => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300',
                                    default => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $stColor }}">
                                {{ $st }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-500 font-mono">{{ isset($t['created_at']) ? \Carbon\Carbon::parse($t['created_at'])->format('d/m/Y H:i:s') : '-' }}</td>
                        <td class="px-4 py-3 font-mono text-[10px] text-slate-400 truncate max-w-[260px]">
                            {{ json_encode($t['parameters'] ?? []) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400">Belum ada riwayat aktivitas task.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- MODAL WAN CREATE & EDIT --}}
    @if($showWanModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden border border-slate-200 dark:border-slate-700 my-auto">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-purple-600 text-[22px]">{{ $isCreatingWan ? 'add_box' : 'edit_square' }}</span>
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
                        {{ $isCreatingWan ? 'Tambah Koneksi WAN Baru' : 'Edit Koneksi WAN: ' . $wanFormName }}
                    </h3>
                </div>
                <button wire:click="closeWanModal" class="text-slate-400 hover:text-slate-600 dark:text-slate-400">
                    <span class="material-symbols-outlined" translate="no">close</span>
                </button>
            </div>

            <form wire:submit.prevent="saveWan">
                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    {{-- Mode Selection --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                            Pilih Jenis Mode Koneksi & Slot WAN
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-start p-3 rounded-xl border cursor-pointer {{ $wanFormMode === 'pppoe_route' ? 'border-purple-500 bg-purple-50/60 dark:bg-purple-950/40 ring-1 ring-purple-500' : 'border-slate-200 dark:border-slate-700' }}">
                                <input type="radio" wire:model.live="wanFormMode" value="pppoe_route" class="mt-1 text-purple-600">
                                <div class="ml-2.5">
                                    <span class="block text-xs font-bold text-slate-900 dark:text-slate-100">PPPoE Dial-up (Route)</span>
                                    <span class="block text-[11px] text-slate-500 mt-0.5">Modem mendial PPPoE sendiri. Wajib mengisi Username & Password PPPoE.</span>
                                </div>
                            </label>
                            <label class="flex items-start p-3 rounded-xl border cursor-pointer {{ $wanFormMode === 'bridge_ppp' ? 'border-amber-500 bg-amber-50/60 dark:bg-amber-950/40 ring-1 ring-amber-500' : 'border-slate-200 dark:border-slate-700' }}">
                                <input type="radio" wire:model.live="wanFormMode" value="bridge_ppp" class="mt-1 text-amber-600">
                                <div class="ml-2.5">
                                    <span class="block text-xs font-bold text-slate-900 dark:text-slate-100">WAN Bridge (Slot PPP)</span>
                                    <span class="block text-[11px] text-slate-500 mt-0.5">Mode: PPPoE_Bridged. Dial PPPoE diteruskan ke router MikroTik.</span>
                                </div>
                            </label>
                            <label class="flex items-start p-3 rounded-xl border cursor-pointer {{ $wanFormMode === 'bridge_ip' ? 'border-amber-500 bg-amber-50/60 dark:bg-amber-950/40 ring-1 ring-amber-500' : 'border-slate-200 dark:border-slate-700' }}">
                                <input type="radio" wire:model.live="wanFormMode" value="bridge_ip" class="mt-1 text-amber-600">
                                <div class="ml-2.5">
                                    <span class="block text-xs font-bold text-slate-900 dark:text-slate-100">WAN Bridge (Slot IP)</span>
                                    <span class="block text-[11px] text-slate-500 mt-0.5">Mode: IP_Bridged. Untuk hotspot, trunk LAN, atau bridge IP.</span>
                                </div>
                            </label>
                            <label class="flex items-start p-3 rounded-xl border cursor-pointer {{ $wanFormMode === 'dhcp_route' ? 'border-purple-500 bg-purple-50/60 dark:bg-purple-950/40 ring-1 ring-purple-500' : 'border-slate-200 dark:border-slate-700' }}">
                                <input type="radio" wire:model.live="wanFormMode" value="dhcp_route" class="mt-1 text-purple-600">
                                <div class="ml-2.5">
                                    <span class="block text-xs font-bold text-slate-900 dark:text-slate-100">DHCP / IPoE (IP Route)</span>
                                    <span class="block text-[11px] text-slate-500 mt-0.5">Mode: IP_Routed. Menerima IP dinamis via DHCP client.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- VLAN Configuration Card --}}
                    <div class="p-4 rounded-xl border {{ $wanFormVlanEnabled ? 'bg-purple-50/40 border-purple-200 dark:bg-purple-950/20 dark:border-purple-800/50' : 'bg-slate-50 border-slate-200 dark:bg-slate-900/40 dark:border-slate-700' }}">
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" wire:model.live="wanFormVlanEnabled" class="w-4 h-4 text-purple-600 rounded">
                                <span class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Aktifkan VLAN Tagging (802.1Q)</span>
                            </label>
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded {{ $wanFormVlanEnabled ? 'bg-purple-100 text-purple-700' : 'bg-slate-200 text-slate-600' }}">
                                {{ $wanFormVlanEnabled ? 'VLAN ENABLED (TAGGED)' : 'UNTAGGED' }}
                            </span>
                        </div>
                        @if($wanFormVlanEnabled)
                        <div class="mt-3 pt-3 border-t border-purple-100 dark:border-purple-900/40 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-purple-900 dark:text-purple-300 mb-1">VLAN ID (1 - 4094) *</label>
                                <input type="number" wire:model="wanFormVlan" min="1" max="4094" placeholder="Contoh: 200" required
                                    class="w-full px-3.5 py-2 rounded-xl border border-purple-300 dark:border-purple-700 text-xs font-mono font-bold focus:ring-2 focus:ring-purple-500 dark:bg-slate-900 dark:text-slate-100">
                                @error('wanFormVlan') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 p-2.5 rounded-xl bg-white/70 dark:bg-slate-900/50 border border-purple-100">
                                Sistem otomatis menyesuaikan parameter VLAN vendor aktual: ZTE (<code class="text-purple-600">VLANEnable=1</code>), Huawei (<code class="text-purple-600">Mode=2</code>), FiberHome (<code class="text-purple-600">VLANIDMark</code>).
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- PPPoE Credentials --}}
                    @if($wanFormMode === 'pppoe_route')
                    <div class="p-4 rounded-xl border border-purple-200 bg-purple-50/30 dark:bg-purple-950/20 dark:border-purple-800/50 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-purple-900 dark:text-purple-300 mb-1">Username PPPoE *</label>
                                <input type="text" wire:model="wanFormUsername" placeholder="user@isp" required
                                    class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-mono focus:ring-2 focus:ring-purple-500 dark:bg-slate-900 dark:text-slate-100">
                                @error('wanFormUsername') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-purple-900 dark:text-purple-300 mb-1">Password PPPoE</label>
                                <input type="password" wire:model="wanFormPassword" placeholder="{{ $isEditingWan ? 'Biarkan kosong jika tidak diubah' : 'Sandi PPPoE' }}"
                                    class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-purple-500 dark:bg-slate-900 dark:text-slate-100">
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Service List & NAT --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Service List</label>
                            <select wire:model.live="wanFormServiceList" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold focus:ring-2 focus:ring-purple-500 dark:bg-slate-900 dark:text-slate-100">
                                <option value="INTERNET">INTERNET (Standar Umum)</option>
                                <option value="TR069,INTERNET">TR069,INTERNET (Wajib FiberHome)</option>
                                <option value="OTHER">OTHER (Bridge / Hotspot)</option>
                                <option value="TR069">TR069 (Khusus Management)</option>
                                <option value="CUSTOM">Input Manual Custom...</option>
                            </select>
                            @if($wanFormServiceList === 'CUSTOM')
                                <input type="text" wire:model="wanCustomServiceList" placeholder="Ketik service list..." class="mt-2 w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-mono dark:bg-slate-900">
                            @endif
                        </div>
                        <div class="flex items-center p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="wanFormNat" class="w-4 h-4 text-purple-600 rounded">
                                <div>
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Aktifkan NAT (Routing)</span>
                                    <p class="text-[10px] text-slate-400">Nonaktifkan untuk mode Bridge murni.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Port Binding LAN & WLAN --}}
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Port & WLAN Binding</span>
                                <p class="text-[11px] text-slate-400">Arahkan port LAN dan SSID Wi-Fi ke profil WAN ini.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="applyPresetFiberhome" class="px-2.5 py-1 text-[11px] font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/60 dark:text-purple-300 rounded-lg transition-colors border border-purple-200 dark:border-purple-800">
                                    Preset FiberHome (LAN1 + SSID1 + SSID5)
                                </button>
                                <button type="button" wire:click="applyPresetAllPorts" class="px-2.5 py-1 text-[11px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 rounded-lg transition-colors">
                                    Pilih Semua
                                </button>
                            </div>
                        </div>

                        @if(!$wanUseManualBinding)
                        <div class="space-y-2">
                            <div>
                                <span class="text-[11px] font-bold text-slate-500 uppercase">Port LAN Fisik:</span>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-1">
                                    @for($i=1; $i<=4; $i++)
                                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40 cursor-pointer">
                                        <input type="checkbox" wire:model.live="wanBindLan" value="{{ $i }}" class="w-4 h-4 text-purple-600 rounded">
                                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">LAN {{ $i }}</span>
                                    </label>
                                    @endfor
                                </div>
                            </div>

                            <div>
                                <span class="text-[11px] font-bold text-slate-500 uppercase">Wi-Fi SSIDs:</span>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-1">
                                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" wire:model.live="wanBindWlan" value="1" class="w-4 h-4 text-purple-600 rounded">
                                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">SSID 1 (2.4G)</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" wire:model.live="wanBindWlan" value="5" class="w-4 h-4 text-purple-600 rounded">
                                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">SSID 5 (5G)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        @else
                        <div>
                            <textarea wire:model="wanCustomBindString" rows="2" placeholder="InternetGatewayDevice.LANDevice.1.LANEthernetInterfaceConfig.1,..."
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-mono dark:bg-slate-900 dark:text-slate-100"></textarea>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-3 bg-slate-50 dark:bg-slate-900/50">
                    <button type="button" wire:click="closeWanModal" class="px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" wire:loading.attr="disabled" class="px-5 py-2 text-xs font-bold text-white bg-purple-600 hover:bg-purple-700 rounded-xl shadow-sm flex items-center gap-2">
                        <span wire:loading wire:target="saveWan" class="material-symbols-outlined text-[16px] animate-spin">autorenew</span>
                        <span>{{ $isCreatingWan ? 'Buat & Terapkan WAN' : 'Simpan Perubahan WAN' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL GANTI WIFI --}}
    @if($showWifiModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="{{ $isNocLayout ? 'noc-panel-bg noc-border' : 'bg-white dark:bg-slate-800' }} rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all border border-slate-200 dark:border-slate-700">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400 text-[22px]">wifi_settings</span>
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Pengaturan WiFi & Sandi Modem</h3>
                </div>
                <button wire:click="$set('showWifiModal', false)" class="text-slate-400 hover:text-slate-600 dark:text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no">close</span>
                </button>
            </div>
            
            <form wire:submit.prevent="saveWifi">
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between p-3.5 {{ $isNocLayout ? 'noc-panel-bg noc-text' : 'bg-slate-50 dark:bg-slate-900/50' }} rounded-xl border border-slate-200 dark:border-slate-700">
                        <div>
                            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">Pancaran Radio WiFi (Enable)</label>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Aktifkan atau matikan sinyal frekuensi WiFi ini.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="wifiEnabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                    
                    <div x-data="{ enabled: @entangle('wifiEnabled') }" x-show="enabled" x-collapse class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Pilih Pita Frekuensi (SSID)</label>
                            <select wire:model.live="wlanTarget" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100">
                                @if(!empty($availableWlans))
                                    @foreach($availableWlans as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                @else
                                    <option value="1">SSID 1 (2.4 GHz - Jangkauan Luas)</option>
                                    <option value="5">SSID 5 (5.8 GHz - Kecepatan Tinggi)</option>
                                @endif
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Jaringan (SSID) *</label>
                            <input type="text" wire:model="wifiSsid" placeholder="Contoh: MyWifi_Home" required
                                class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100">
                            @error('wifiSsid') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Mode Keamanan (Security)</label>
                            <select wire:model.live="wifiSecurity" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100">
                                <option value="WPA_WPA2">WPA/WPA2-Personal (Direkomendasikan)</option>
                                <option value="WPA2">WPA2-Personal (Standar)</option>
                                <option value="WPA">WPA-Personal (Lama)</option>
                                <option value="OPEN">Open (Tanpa Password - Hotspot)</option>
                            </select>
                        </div>

                        @if($wifiSecurity !== 'OPEN')
                        <div x-data="{ showWifiPass: false }">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Sandi WiFi (WPA Key) *</label>
                            <div class="relative">
                                <input :type="showWifiPass ? 'text' : 'password'" wire:model="wifiPassword" placeholder="Minimal 8 karakter"
                                    class="w-full px-3 py-2 pr-10 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono font-semibold focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100">
                                <button type="button" @click="showWifiPass = !showWifiPass" class="absolute right-3 top-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                    <span class="material-symbols-outlined text-sm" x-text="showWifiPass ? 'visibility_off' : 'visibility'">visibility</span>
                                </button>
                            </div>
                            @error('wifiPassword') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        <div class="flex items-center gap-2 pt-1">
                            <input type="checkbox" id="wifiHidden" wire:model="wifiHidden" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900">
                            <label for="wifiHidden" class="text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer">Sembunyikan SSID (Hidden Network)</label>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 {{ $isNocLayout ? 'noc-panel-bg noc-text' : 'bg-slate-50 dark:bg-slate-900/50' }} border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-3">
                    <button type="button" wire:click="$set('showWifiModal', false)" class="px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 rounded-xl">Batal</button>
                    <button type="submit" wire:loading.attr="disabled" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-sm flex items-center gap-2">
                        <span wire:loading.remove wire:target="saveWifi" class="material-symbols-outlined notranslate text-sm" translate="no">send</span>
                        <span wire:loading wire:target="saveWifi" class="animate-spin material-symbols-outlined notranslate text-sm" translate="no">refresh</span>
                        <span>Simpan ke Modem</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL UBAH PPPOE WAN --}}
    @if($showPppoeModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="{{ $isNocLayout ? 'noc-panel-bg noc-border' : 'bg-white dark:bg-slate-800' }} rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-200 dark:border-slate-700">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-[22px]">account_circle</span>
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Ubah Akun PPPoE WAN</h3>
                </div>
                <button wire:click="$set('showPppoeModal', false)" class="text-slate-400 hover:text-slate-600 dark:text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no">close</span>
                </button>
            </div>
            
            <form wire:submit.prevent="savePppoe">
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Username PPPoE *</label>
                        <input type="text" wire:model="pppoeEditUsername" placeholder="username@isp" required
                            class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono font-semibold focus:ring-2 focus:ring-emerald-500 dark:bg-slate-900 dark:text-slate-100">
                        @error('pppoeEditUsername') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div x-data="{ showPppPass: false }">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Password PPPoE *</label>
                        <div class="relative">
                            <input :type="showPppPass ? 'text' : 'password'" wire:model="pppoeEditPassword" placeholder="Sandi PPPoE" required
                                class="w-full px-3 py-2 pr-10 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono font-semibold focus:ring-2 focus:ring-emerald-500 dark:bg-slate-900 dark:text-slate-100">
                            <button type="button" @click="showPppPass = !showPppPass" class="absolute right-3 top-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                <span class="material-symbols-outlined text-sm" x-text="showPppPass ? 'visibility_off' : 'visibility'">visibility</span>
                            </button>
                        </div>
                        @error('pppoeEditPassword') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="px-6 py-4 {{ $isNocLayout ? 'noc-panel-bg noc-text' : 'bg-slate-50 dark:bg-slate-900/50' }} border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-3">
                    <button type="button" wire:click="$set('showPppoeModal', false)" class="px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 rounded-xl">Batal</button>
                    <button type="submit" wire:loading.attr="disabled" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 shadow-sm flex items-center gap-2">
                        <span wire:loading.remove wire:target="savePppoe" class="material-symbols-outlined notranslate text-sm" translate="no">send</span>
                        <span wire:loading wire:target="savePppoe" class="animate-spin material-symbols-outlined notranslate text-sm" translate="no">refresh</span>
                        <span>Simpan ke Modem</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL UBAH SUPERADMIN WEB GUI --}}
    @if($showSuperadminModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="{{ $isNocLayout ? 'noc-panel-bg noc-border' : 'bg-white dark:bg-slate-800' }} rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-200 dark:border-slate-700">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-600 dark:text-amber-400 text-[22px]">admin_panel_settings</span>
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Ubah Akun SuperAdmin Web</h3>
                </div>
                <button wire:click="$set('showSuperadminModal', false)" class="text-slate-400 hover:text-slate-600 dark:text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no">close</span>
                </button>
            </div>
            
            <form wire:submit.prevent="saveSuperadmin">
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Username SuperAdmin</label>
                        <input type="text" wire:model="superadminEditUser" placeholder="admin"
                            class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono font-semibold focus:ring-2 focus:ring-amber-500 dark:bg-slate-900 dark:text-slate-100">
                        @error('superadminEditUser') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div x-data="{ showAdminPass: false }">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Password SuperAdmin</label>
                        <div class="relative">
                            <input :type="showAdminPass ? 'text' : 'password'" wire:model="superadminEditPassword" placeholder="Password SuperAdmin"
                                class="w-full px-3 py-2 pr-10 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono font-semibold focus:ring-2 focus:ring-amber-500 dark:bg-slate-900 dark:text-slate-100">
                            <button type="button" @click="showAdminPass = !showAdminPass" class="absolute right-3 top-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                <span class="material-symbols-outlined text-sm" x-text="showAdminPass ? 'visibility_off' : 'visibility'">visibility</span>
                            </button>
                        </div>
                        @error('superadminEditPassword') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="px-6 py-4 {{ $isNocLayout ? 'noc-panel-bg noc-text' : 'bg-slate-50 dark:bg-slate-900/50' }} border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-3">
                    <button type="button" wire:click="$set('showSuperadminModal', false)" class="px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 rounded-xl">Batal</button>
                    <button type="submit" wire:loading.attr="disabled" class="px-5 py-2 text-xs font-bold text-white bg-amber-600 rounded-xl hover:bg-amber-700 shadow-sm flex items-center gap-2">
                        <span wire:loading.remove wire:target="saveSuperadmin" class="material-symbols-outlined notranslate text-sm" translate="no">send</span>
                        <span wire:loading wire:target="saveSuperadmin" class="animate-spin material-symbols-outlined notranslate text-sm" translate="no">refresh</span>
                        <span>Simpan ke Modem</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>