<div class="space-y-5 pb-10 min-w-0 max-w-full overflow-x-hidden">
    {{-- SESSION ALERTS --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-700 dark:text-emerald-300 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-300 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">error</span>
            {{ session('error') }}
        </div>
    @endif

    {{-- HEADER BAR (RESPONSIF MOBILE RAMAH LAYAR 360px+) --}}
    <div class="px-4 sm:px-5 py-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start sm:items-center gap-3 min-w-0">
            <a href="{{ route('reseller-portal.network.acs-devices.index') }}" class="p-2 shrink-0 rounded-lg text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors" title="Kembali ke Daftar">
                <span class="material-symbols-outlined notranslate" translate="no">arrow_back</span>
            </a>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-slate-100 truncate">
                        {{ $device->serial_number ?? 'Device Detail' }}
                    </h1>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider {{ $device->status === 'online' ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300' : 'bg-rose-100 dark:bg-rose-900/50 text-rose-700 dark:text-rose-300' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $device->status === 'online' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                        {{ $device->status }}
                    </span>
                </div>
                <div class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-mono mt-1 flex flex-wrap items-center gap-x-2 gap-y-1">
                    <span>MAC: {{ $device->mac_address ?? '-' }}</span>
                    <span class="hidden sm:inline">|</span>
                    <span>IP: <x-ui.ip-link :ip="$device->ip_address" /></span>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 w-full sm:w-auto">
            <button wire:click="summonDevice" wire:loading.attr="disabled" class="w-full sm:w-auto inline-flex items-center justify-center px-3 py-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 border border-emerald-200 dark:border-emerald-800 rounded-lg text-xs sm:text-sm font-semibold transition-colors">
                <span wire:loading.remove wire:target="summonDevice" class="material-symbols-outlined notranslate mr-1.5 text-base sm:text-lg" translate="no">sensors</span>
                <span wire:loading wire:target="summonDevice" class="material-symbols-outlined notranslate mr-1.5 text-base sm:text-lg animate-spin" translate="no">hourglass_empty</span>
                <span>Summon</span>
            </button>
            <button wire:click="openWifiModal" class="w-full sm:w-auto inline-flex items-center justify-center px-3 py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 border border-indigo-200 dark:border-indigo-800 rounded-lg text-xs sm:text-sm font-semibold transition-colors">
                <span class="material-symbols-outlined notranslate mr-1.5 text-base sm:text-lg" translate="no">wifi</span>
                <span>Ganti WiFi</span>
            </button>
            <div class="w-full sm:w-auto">
                @livewire(\App\Livewire\ACS\Device\WanManager::class, ['device' => $device])
            </div>
            <button wire:click="rebootDevice" wire:confirm="Yakin ingin me-reboot modem {{ $device->serial_number }} ini dari jarak jauh via TR-069?" wire:loading.attr="disabled" class="w-full sm:w-auto inline-flex items-center justify-center px-3 py-2 bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/50 border border-amber-200 dark:border-amber-800 rounded-lg text-xs sm:text-sm font-semibold transition-colors">
                <span wire:loading.remove wire:target="rebootDevice" class="material-symbols-outlined notranslate mr-1.5 text-base sm:text-lg" translate="no">restart_alt</span>
                <span wire:loading wire:target="rebootDevice" class="material-symbols-outlined notranslate mr-1.5 text-base sm:text-lg animate-spin" translate="no">hourglass_empty</span>
                <span>Reboot</span>
            </button>
        </div>
    </div>

    {{-- KPI DIAGNOSTICS (RESPONSIF MOBILE 2 KOLOM TANPA OVERFLOW) --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
        {{-- RX Power --}}
        <div class="rounded-xl border border-indigo-200 dark:border-indigo-800/60 shadow-sm bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-950/40 dark:to-slate-800 p-3.5 sm:p-4">
            <h3 class="text-[11px] sm:text-xs font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">cable</span> RX Power
            </h3>
            <div class="text-xl sm:text-2xl font-black {{ isset($deviceStatus['rx_power']) && floatval($deviceStatus['rx_power']) < -26 ? 'text-rose-600 dark:text-rose-400' : 'text-indigo-700 dark:text-indigo-300' }} mb-0.5 truncate">
                {{ $deviceStatus['rx_power'] ?? '-' }}
            </div>
            <div class="text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400">Redaman Fiber (RX)</div>
        </div>

        {{-- TX Power --}}
        <div class="rounded-xl border border-sky-200 dark:border-sky-800/60 shadow-sm bg-gradient-to-br from-sky-50 to-white dark:from-sky-950/40 dark:to-slate-800 p-3.5 sm:p-4">
            <h3 class="text-[11px] sm:text-xs font-bold text-sky-500 dark:text-sky-400 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">sensors</span> TX Power
            </h3>
            <div class="text-xl sm:text-2xl font-black text-sky-700 dark:text-sky-300 mb-0.5 truncate">
                {{ $deviceStatus['tx_power'] ?? '-' }}
            </div>
            <div class="text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400">Sinyal Pancar (TX)</div>
        </div>

        {{-- PPPoE User --}}
        <div class="rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-sm bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/40 dark:to-slate-800 p-3.5 sm:p-4">
            <h3 class="text-[11px] sm:text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">account_circle</span> PPPoE
            </h3>
            <div class="text-base sm:text-xl font-bold text-emerald-700 dark:text-emerald-300 mb-0.5 truncate" title="{{ $deviceStatus['pppoe_username'] ?? '-' }}">
                {{ $deviceStatus['pppoe_username'] ?? '-' }}
            </div>
            <div class="text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 font-mono truncate">Pass: {{ $deviceStatus['pppoe_password'] ?? '***' }}</div>
        </div>

        {{-- WiFi SSID --}}
        <div class="rounded-xl border border-purple-200 dark:border-purple-800/60 shadow-sm bg-gradient-to-br from-purple-50 to-white dark:from-purple-950/40 dark:to-slate-800 p-3.5 sm:p-4">
            <h3 class="text-[11px] sm:text-xs font-bold text-purple-500 dark:text-purple-400 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">wifi</span> WiFi
            </h3>
            <div class="text-base sm:text-xl font-bold text-purple-700 dark:text-purple-300 mb-0.5 truncate" title="{{ $deviceStatus['ssid_1'] ?? '-' }}">
                {{ $deviceStatus['ssid_1'] ?? '-' }}
            </div>
            <div class="text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 font-mono">SSID Utama</div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Device Details Table --}}
        <div class="xl:col-span-1 space-y-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 font-semibold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:18px">info</span>
                    Informasi Sistem
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                    <div class="p-3 flex items-center justify-between gap-2 min-w-0">
                        <span class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm shrink-0">Manufacturer</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100 text-xs sm:text-sm truncate text-right">{{ $device->manufacturer ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex items-center justify-between gap-2 min-w-0">
                        <span class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm shrink-0">Product Class</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100 text-xs sm:text-sm truncate text-right">{{ $device->product_class ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex items-center justify-between gap-2 min-w-0">
                        <span class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm shrink-0">Hardware Ver</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100 text-xs sm:text-sm truncate text-right">{{ $device->hardware_version ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex items-center justify-between gap-2 min-w-0">
                        <span class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm shrink-0">Software Ver</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100 text-xs sm:text-sm truncate text-right">{{ $device->software_version ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex items-center justify-between gap-2 min-w-0">
                        <span class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm shrink-0">Firmware Ver</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100 text-xs sm:text-sm truncate text-right">{{ $device->firmware_version ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex items-center justify-between gap-2 min-w-0">
                        <span class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm shrink-0">Last Inform</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100 text-xs sm:text-sm truncate text-right">{{ $device->last_inform?->format('d/m/Y H:i') ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex items-center justify-between gap-2 min-w-0 bg-indigo-50/50 dark:bg-indigo-950/20">
                        <span class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm font-medium shrink-0">WAN IP</span>
                        <span class="font-bold text-indigo-700 dark:text-indigo-400 text-xs sm:text-sm truncate text-right">{{ $deviceStatus['wan_ip'] ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex items-center justify-between gap-2 min-w-0 bg-indigo-50/50 dark:bg-indigo-950/20">
                        <span class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm font-medium shrink-0">WAN MAC</span>
                        <span class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300 truncate text-right">{{ $deviceStatus['wan_mac'] ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
        </div>

        {{-- Connected Devices (Hosts) --}}
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden flex flex-col h-full">
                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 font-semibold text-slate-800 dark:text-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-emerald-500 dark:text-emerald-400" translate="no" style="font-size:18px">devices</span>
                        Perangkat yang Terhubung ke Modem
                    </div>
                    <button wire:click="loadConnectedDevices" class="text-xs font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 flex items-center gap-1 cursor-pointer">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">refresh</span> Refresh
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs uppercase text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Hostname</th>
                                <th class="px-4 py-3 font-semibold">IP Address</th>
                                <th class="px-4 py-3 font-semibold">MAC Address</th>
                                <th class="px-4 py-3 font-semibold">Koneksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse($connectedDevices ?? [] as $host)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                    <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                                        {{ $host['HostName'] ?? 'Unknown Device' }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs">{{ $host['IPAddress'] ?? '-' }}</td>
                                    <td class="px-4 py-3 font-mono text-xs">{{ $host['MACAddress'] ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400">
                                            {{ $host['Layer1Interface'] ?? 'WLAN' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                        <span class="material-symbols-outlined notranslate text-3xl mb-2 text-slate-300 dark:text-slate-600" translate="no">wifi_tethering_off</span>
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
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden transform transition-all border border-slate-200 dark:border-slate-700">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Ganti Nama/Pass WiFi</h3>
                <button wire:click="$set('showWifiModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                    <span class="material-symbols-outlined notranslate" translate="no">close</span>
                </button>
            </div>
            
            <form wire:submit="saveWifi">
                <div class="p-5 space-y-4">
                    <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-700">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Status WiFi (SSID)</label>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Aktifkan atau matikan pancaran sinyal WiFi ini.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="wifiEnabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                    
                    <div x-data="{ enabled: @entangle('wifiEnabled') }" x-show="enabled" x-collapse class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Target WLAN</label>
                            <select wire:model.live="wlanTarget" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100">
                                @if(!empty($availableWlans))
                                    @foreach($availableWlans as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                @else
                                    <option value="1">WLAN 1 (Utama - 2.4GHz)</option>
                                    <option value="5">WLAN 5 (Utama - 5GHz)</option>
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama WiFi (SSID)</label>
                            <input type="text" wire:model="wifiSsid" required class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100">
                            @error('wifiSsid') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Security Mode</label>
                            <select wire:model.live="wifiSecurity" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100">
                                <option value="None">Open / None (Tanpa Password)</option>
                                <option value="WPA2PSK">WPA2-PSK (Standar)</option>
                                <option value="WPAPSKWPA2PSK">WPA/WPA2-PSK (Mixed)</option>
                                <option value="Basic">WEP (Tidak Disarankan)</option>
                            </select>
                            @error('wifiSecurity') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Password Baru</label>
                            <input type="text" wire:model="wifiPassword" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100">
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Minimal 8 karakter. Biarkan sama jika tidak ingin mengganti sandi.</p>
                            @error('wifiPassword') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="px-5 py-3 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-3">
                    <button type="button" wire:click="$set('showWifiModal', false)" class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">send</span> Kirim ke Modem
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
