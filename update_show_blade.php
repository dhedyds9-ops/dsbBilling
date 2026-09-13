<?php
$file = 'D:/dsBilling/resources/views/livewire/acs/device/show.blade.php';
$content = <<<'HTML'
<div class="space-y-6 pb-10">
    {{-- SESSION ALERTS --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">error</span>
            {{ session('error') }}
        </div>
    @endif

    {{-- HEADER BAR --}}
    <div class="px-5 py-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm rounded-xl flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('acs.devices.index') }}" class="p-2 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-slate-100 transition-colors" title="Kembali ke Daftar">
                <span class="material-symbols-outlined notranslate" translate="no">arrow_back</span>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    {{ $device->serial_number ?? 'Device Detail' }}
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider {{ $device->status === 'online' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $device->status === 'online' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                        {{ $device->status }}
                    </span>
                </h1>
                <div class="text-sm text-slate-500 font-mono mt-1">
                    MAC: {{ $device->mac_address ?? '-' }} | IP: {{ $device->ip_address ?? '-' }}
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="openWifiModal" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 rounded-lg text-sm font-semibold transition-colors">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">wifi</span> Ganti WiFi
            </button>
            <button wire:click="rebootDevice" wire:confirm="Yakin ingin merestart modem ini dari jarak jauh?" class="inline-flex items-center justify-center px-4 py-2 bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 rounded-lg text-sm font-semibold transition-colors">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">restart_alt</span> Reboot
            </button>
            <button wire:click="factoryResetDevice" wire:confirm="PERINGATAN! Yakin ingin FACTORY RESET modem ini? Semua konfigurasi akan hilang!" class="inline-flex items-center justify-center px-4 py-2 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-lg text-sm font-semibold transition-colors">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">warning</span> Reset
            </button>
        </div>
    </div>

    {{-- KPI DIAGNOSTICS --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- RX Power --}}
        <div class="relative overflow-hidden rounded-xl border border-indigo-200 shadow-sm bg-gradient-to-br from-indigo-50 to-white group">
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                    <span class="material-symbols-outlined notranslate text-[16px]" translate="no">cable</span> RX Power
                </h3>
                <div class="text-2xl font-black {{ isset($deviceStatus['rx_power']) && floatval($deviceStatus['rx_power']) < -26 ? 'text-rose-600' : 'text-indigo-700' }} mb-1">
                    {{ $deviceStatus['rx_power'] ?? '-' }}
                </div>
                <div class="text-[11px] text-slate-500">Redaman Fiber (Optical)</div>
            </div>
        </div>

        {{-- TX Power --}}
        <div class="relative overflow-hidden rounded-xl border border-sky-200 shadow-sm bg-gradient-to-br from-sky-50 to-white group">
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-sky-500 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                    <span class="material-symbols-outlined notranslate text-[16px]" translate="no">sensors</span> TX Power
                </h3>
                <div class="text-2xl font-black text-sky-700 mb-1">
                    {{ $deviceStatus['tx_power'] ?? '-' }}
                </div>
                <div class="text-[11px] text-slate-500">Sinyal Pancar (Optical)</div>
            </div>
        </div>

        {{-- PPPoE User --}}
        <div class="relative overflow-hidden rounded-xl border border-emerald-200 shadow-sm bg-gradient-to-br from-emerald-50 to-white group">
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-emerald-500 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                    <span class="material-symbols-outlined notranslate text-[16px]" translate="no">account_circle</span> PPPoE Account
                </h3>
                <div class="text-xl font-bold text-emerald-700 mb-1 truncate" title="{{ $deviceStatus['pppoe_username'] ?? '-' }}">
                    {{ $deviceStatus['pppoe_username'] ?? '-' }}
                </div>
                <div class="text-[11px] text-slate-500 font-mono">Pass: {{ $deviceStatus['pppoe_password'] ?? '***' }}</div>
            </div>
        </div>

        {{-- WiFi SSID --}}
        <div class="relative overflow-hidden rounded-xl border border-purple-200 shadow-sm bg-gradient-to-br from-purple-50 to-white group">
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-purple-500 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                    <span class="material-symbols-outlined notranslate text-[16px]" translate="no">wifi</span> WiFi Network
                </h3>
                <div class="text-xl font-bold text-purple-700 mb-1 truncate" title="{{ $deviceStatus['ssid_1'] ?? '-' }}">
                    {{ $deviceStatus['ssid_1'] ?? '-' }}
                </div>
                <div class="text-[11px] text-slate-500 font-mono">SSID Utama</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Device Details Table --}}
        <div class="xl:col-span-1 space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200 bg-slate-50 font-semibold text-slate-800 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:18px">info</span>
                    Informasi Sistem
                </div>
                <div class="divide-y divide-slate-100 text-sm">
                    <div class="p-3 flex justify-between">
                        <span class="text-slate-500">Manufacturer</span>
                        <span class="font-medium text-slate-900">{{ $device->manufacturer ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex justify-between">
                        <span class="text-slate-500">Product Class</span>
                        <span class="font-medium text-slate-900">{{ $device->product_class ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex justify-between">
                        <span class="text-slate-500">Hardware Ver</span>
                        <span class="font-medium text-slate-900">{{ $device->hardware_version ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex justify-between">
                        <span class="text-slate-500">Software Ver</span>
                        <span class="font-medium text-slate-900">{{ $device->software_version ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex justify-between">
                        <span class="text-slate-500">Firmware Ver</span>
                        <span class="font-medium text-slate-900">{{ $device->firmware_version ?? '-' }}</span>
                    </div>
                    <div class="p-3 flex justify-between">
                        <span class="text-slate-500">Last Inform</span>
                        <span class="font-medium text-slate-900">{{ $device->last_inform?->format('d/m/Y H:i') ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Connected Devices (Hosts) --}}
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col h-full">
                <div class="px-4 py-3 border-b border-slate-200 bg-slate-50 font-semibold text-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-emerald-500" translate="no" style="font-size:18px">devices</span>
                        Perangkat yang Terhubung ke Modem
                    </div>
                    <button wire:click="loadConnectedDevices" class="text-xs font-medium text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">refresh</span> Refresh
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50/50 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Hostname</th>
                                <th class="px-4 py-3 font-semibold">IP Address</th>
                                <th class="px-4 py-3 font-semibold">MAC Address</th>
                                <th class="px-4 py-3 font-semibold">Koneksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($connectedDevices ?? [] as $host)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 font-medium text-slate-900">
                                        {{ $host['HostName'] ?? 'Unknown Device' }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs">{{ $host['IPAddress'] ?? '-' }}</td>
                                    <td class="px-4 py-3 font-mono text-xs">{{ $host['MACAddress'] ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600">
                                            {{ $host['Layer1Interface'] ?? 'WLAN' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-slate-500">
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
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900">Ganti Nama/Pass WiFi</h3>
                <button wire:click="$set('showWifiModal', false)" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined notranslate" translate="no">close</span>
                </button>
            </div>
            
            <form wire:submit.prevent="saveWifi">
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Target WLAN</label>
                        <select wire:model.live="wlanTarget" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="1">WLAN 1 (Utama - 2.4GHz)</option>
                            <option value="5">WLAN 5 (Utama - 5GHz)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama WiFi (SSID)</label>
                        <input type="text" wire:model="wifiSsid" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        @error('wifiSsid') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Password Baru</label>
                        <input type="text" wire:model="wifiPassword" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        <p class="text-[11px] text-slate-500 mt-1">Minimal 8 karakter. Biarkan sama jika tidak ingin mengganti sandi.</p>
                        @error('wifiPassword') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" wire:click="$set('showWifiModal', false)" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">send</span> Kirim ke Modem
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
HTML;
file_put_contents($file, $content);
echo "Updated show.blade.php";
?>
