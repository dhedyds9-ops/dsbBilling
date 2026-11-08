<div class="space-y-6 pb-10">
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
                </h1>
                <div class="text-sm text-slate-500 dark:text-slate-400 font-mono mt-1">
                    MAC: {{ $device->mac_address ?? '-' }} | IP: {{ $device->ip_address ?? '-' }}
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="summonDevice" class="inline-flex items-center justify-center px-4 py-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:bg-emerald-900/50 border border-emerald-200 rounded-lg text-sm font-semibold transition-colors">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">connect_without_contact</span> Summon
            </button>
            <button wire:click="openWifiModal" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:bg-indigo-900/50 border border-indigo-200 rounded-lg text-sm font-semibold transition-colors">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">wifi</span> Ganti WiFi
            </button>
            @livewire(\App\Livewire\ACS\Device\WanManager::class, ['device' => $device])
            <button wire:click="rebootDevice" wire:confirm="Yakin ingin merestart modem ini dari jarak jauh?" class="inline-flex items-center justify-center px-4 py-2 bg-amber-50 dark:bg-amber-900/30 text-amber-700 hover:bg-amber-100 dark:bg-amber-900/50 border border-amber-200 rounded-lg text-sm font-semibold transition-colors">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">restart_alt</span> Reboot
            </button>
            <button wire:click="factoryResetDevice" wire:confirm="PERINGATAN! Yakin ingin FACTORY RESET modem ini? Semua konfigurasi akan hilang!" class="inline-flex items-center justify-center px-4 py-2 bg-rose-50 dark:bg-rose-900/30 text-rose-700 hover:bg-rose-100 dark:bg-rose-900/50 border border-rose-200 rounded-lg text-sm font-semibold transition-colors">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">warning</span> Reset
            </button>
        </div>
    </div>

    {{-- KPI DIAGNOSTICS --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        {{-- RX Power --}}
        <div class="relative overflow-x-auto rounded-xl border border-indigo-200 shadow-sm bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-900/40 dark:to-slate-800 group">
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                    <span class="material-symbols-outlined notranslate text-[16px]" translate="no">cable</span> RX Power
                </h3>
                <div class="text-2xl font-black {{ isset($deviceStatus['rx_power']) && floatval($deviceStatus['rx_power']) < -26 ? 'text-rose-600' : (isset($deviceStatus['rx_power']) && floatval($deviceStatus['rx_power']) !== 0.0 && floatval($deviceStatus['rx_power']) >= -24 ? 'text-emerald-600 dark:text-emerald-400' : 'text-indigo-700 dark:text-indigo-300') }} mb-1">
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
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-sky-500 dark:text-sky-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                    <span class="material-symbols-outlined notranslate text-[16px]" translate="no">sensors</span> TX Power
                </h3>
                <div class="text-2xl font-black text-sky-700 dark:text-sky-300 mb-1">
                    {{ $deviceStatus['tx_power'] ?? '-' }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400">Sinyal Pancar PON</div>
            </div>
        </div>

        {{-- PPPoE User --}}
        <div class="relative overflow-x-auto rounded-xl border border-emerald-200 shadow-sm bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-900/40 dark:to-slate-800 group">
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                    <span class="material-symbols-outlined notranslate text-[16px]" translate="no">account_circle</span> PPPoE WAN
                </h3>
                <div class="text-lg font-bold text-emerald-700 dark:text-emerald-300 mb-1 truncate" title="{{ $deviceStatus['pppoe_username'] ?? '-' }}">
                    {{ $deviceStatus['pppoe_username'] ?? '-' }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono flex items-center justify-between">
                    <span>Pass:</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ $deviceStatus['pppoe_password'] ?? '***' }}</span>
                </div>
            </div>
        </div>

        {{-- Web GUI SuperAdmin (Teknisi) --}}
        <div class="relative overflow-x-auto rounded-xl border border-amber-200 shadow-sm bg-gradient-to-br from-amber-50 to-white dark:from-amber-900/40 dark:to-slate-800 group" x-data="{ showPass: false }">
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                    <span class="material-symbols-outlined notranslate text-[16px]" translate="no">admin_panel_settings</span> Web SuperAdmin
                </h3>
                <div class="text-base font-black text-amber-800 dark:text-amber-200 mb-1 font-mono truncate" title="User: {{ $deviceStatus['superadmin_user'] ?? 'admin' }}">
                    {{ $deviceStatus['superadmin_user'] ?? 'admin' }}
                </div>
                <div class="text-[11px] text-slate-600 dark:text-slate-400 font-mono flex items-center justify-between">
                    <span>Pass:</span>
                    <div class="flex items-center gap-1">
                        <span x-text="showPass ? '{{ $deviceStatus['superadmin_password'] ?? 'admin' }}' : '••••••••'" class="font-bold text-slate-800 dark:text-slate-200"></span>
                        <button type="button" @click="showPass = !showPass" class="text-amber-600 hover:text-amber-800">
                            <span class="material-symbols-outlined text-[14px]" x-text="showPass ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- WiFi SSID --}}
        <div class="relative overflow-x-auto rounded-xl border border-purple-200 shadow-sm bg-gradient-to-br from-purple-50 to-white dark:from-purple-900/40 dark:to-slate-800 group">
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-purple-500 dark:text-purple-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                    <span class="material-symbols-outlined notranslate text-[16px]" translate="no">wifi</span> WiFi Network
                </h3>
                <div class="text-base font-bold text-purple-700 dark:text-purple-300 mb-1 truncate" title="{{ $deviceStatus['ssid_1'] ?? '-' }}">
                    {{ $deviceStatus['ssid_1'] ?? '-' }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono truncate" title="5G: {{ $deviceStatus['ssid_2'] ?? '-' }}">
                    5G: {{ $deviceStatus['ssid_2'] && $deviceStatus['ssid_2'] !== '-' ? $deviceStatus['ssid_2'] : 'Tidak aktif' }}
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Device Details Table --}}
        <div class="xl:col-span-1 space-y-6">
            <div class="{{ $isNocLayout ? 'noc-panel-bg noc-border' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700' }} rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 {{ $isNocLayout ? 'noc-panel-bg noc-text' : 'bg-slate-50 dark:bg-slate-900/50' }} font-semibold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:18px">info</span>
                    Informasi Sistem
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                    <div class="p-3 flex justify-between bg-indigo-50/50 dark:bg-indigo-900/10">
                        <span class="text-slate-500 dark:text-slate-400 font-medium">Pelanggan</span>
                        <span class="font-bold text-indigo-700 dark:text-indigo-400 truncate max-w-[180px]" title="{{ $deviceStatus['customer_name'] ?? '-' }}">{{ $deviceStatus['customer_name'] ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Manufacturer</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $device->manufacturer ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Product Class</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $device->product_class ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Hardware Ver</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $device->hardware_version ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Software Ver</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $device->software_version ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Firmware Ver</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $device->firmware_version ?? '-' }}</span>
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

        {{-- Connected Devices (Hosts) --}}
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
                    <button wire:click="loadConnectedDevices" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-300 flex items-center gap-1">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">refresh</span> Refresh Klien
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="{{ $isNocLayout ? 'noc-panel-bg noc-text' : 'bg-slate-50 dark:bg-slate-900/50' }} text-xs uppercase text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Hostname / Nama</th>
                                <th class="px-4 py-3 font-semibold">IP Address</th>
                                <th class="px-4 py-3 font-semibold">MAC Address</th>
                                <th class="px-4 py-3 font-semibold">Jaringan</th>
                                <th class="px-4 py-3 font-semibold text-right">Sinyal (RSSI)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse($connectedDevices ?? [] as $host)
                                <tr class="hover:{{ $isNocLayout ? 'noc-panel-bg noc-text' : 'bg-slate-50 dark:bg-slate-900/50' }} transition-colors">
                                    <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                                        <div class="flex items-center gap-2">
                                            @if(str_contains(strtolower($host['InterfaceType'] ?? ''), 'lan') || ($host['Layer1Interface'] ?? '') === 'Ethernet')
                                                <span class="material-symbols-outlined text-blue-500 text-[18px]">lan</span>
                                            @else
                                                <span class="material-symbols-outlined text-indigo-500 text-[18px]">wifi</span>
                                            @endif
                                            <span class="truncate max-w-[160px]" title="{{ $host['HostName'] ?? 'Perangkat Klien' }}">
                                                {{ $host['HostName'] ?? 'Perangkat Klien' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-slate-700 dark:text-slate-300">
                                        {{ $host['IPAddress'] ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-slate-500 dark:text-slate-400">
                                        {{ $host['MACAddress'] ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $ifStr = $host['InterfaceType'] ?? $host['Layer1Interface'] ?? 'WiFi';
                                            $is5G = str_contains($ifStr, '5G');
                                            $isLan = str_contains(strtolower($ifStr), 'lan') || $ifStr === 'Ethernet';
                                        @endphp
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $isLan ? 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300' : ($is5G ? 'bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300' : 'bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300') }}">
                                            {{ $ifStr }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if(isset($host['RSSI']) && $host['RSSI'] !== null)
                                            @php
                                                $rssi = (int) $host['RSSI'];
                                                $rssiColor = $rssi >= -60 ? 'text-emerald-700 bg-emerald-50 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300' : ($rssi >= -74 ? 'text-sky-700 bg-sky-50 border-sky-200 dark:bg-sky-950/40 dark:text-sky-300' : 'text-amber-700 bg-amber-50 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300');
                                            @endphp
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-mono font-bold border {{ $rssiColor }}">
                                                <span class="material-symbols-outlined text-[13px]">signal_cellular_alt</span>
                                                <span>{{ $rssi }} dBm</span>
                                            </span>
                                        @elseif(str_contains(strtolower($host['InterfaceType'] ?? ''), 'lan'))
                                            <span class="text-[11px] font-bold text-blue-600 dark:text-blue-400 font-mono">1 Gbps</span>
                                        @else
                                            <span class="text-slate-400 text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                        <span class="material-symbols-outlined notranslate text-3xl mb-2 text-slate-300" translate="no">wifi_tethering_off</span>
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
                    {{-- Toggle Status Radio WiFi --}}
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
                        {{-- Target WLAN (Dual-Band) --}}
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

                        {{-- SSID Name --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama WiFi (SSID)</label>
                            <input type="text" wire:model="wifiSsid" required class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100" placeholder="Contoh: MyWifi_2.4G">
                            @error('wifiSsid') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Hide SSID Toggle --}}
                        <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-900/40 rounded-xl border border-slate-200 dark:border-slate-700">
                            <div>
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">Sembunyikan SSID (Hide SSID)</label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Nama WiFi tidak akan muncul di pencarian publik (harus tambah manual).</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="wifiHidden" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>

                        {{-- Security Mode --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Mode Keamanan (Security Mode)</label>
                            <select wire:model.live="wifiSecurity" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100">
                                <option value="WPA_WPA2">WPA/WPA2-PSK Mixed (Rekomendasi - Kompatibel Semua HP)</option>
                                <option value="WPA2">WPA2-PSK (AES Murni - Sangat Aman)</option>
                                <option value="WPA">WPA-PSK (TKIP - Khusus Perangkat Lama)</option>
                                <option value="OPEN">Open / Tanpa Password (Hotspot / Captive Portal)</option>
                            </select>
                            @error('wifiSecurity') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- WiFi Password --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Password WiFi</label>
                            <input type="text" wire:model="wifiPassword" {{ $wifiSecurity === 'OPEN' ? 'disabled' : '' }}
                                placeholder="{{ $wifiSecurity === 'OPEN' ? 'Tidak aktif (Mode Tanpa Password)' : 'Minimal 8 karakter' }}"
                                class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono font-semibold focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 disabled:opacity-50 disabled:bg-slate-100 dark:disabled:bg-slate-800">
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                {{ $wifiSecurity === 'OPEN' ? 'SSID ini terbuka tanpa sandi.' : 'Minimal 8 karakter. Sandi akan langsung disinkronkan ke GenieACS dan modem.' }}
                            </p>
                            @error('wifiPassword') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 {{ $isNocLayout ? 'noc-panel-bg noc-text' : 'bg-slate-50 dark:bg-slate-900/50' }} border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-3">
                    <button type="button" wire:click="$set('showWifiModal', false)" class="px-4 py-2 text-xs font-bold {{ $isNocLayout ? 'noc-text noc-panel-bg noc-border hover:noc-panel-bg hover:noc-text' : 'text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-900/50' }} rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">send</span> Terapkan ke Modem
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>