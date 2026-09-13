<?php
$file = 'D:/dsBilling/resources/views/livewire/crm/customer/customer360.blade.php';
$content = file_get_contents($file);

$deviceTabPattern = '/@if\(\$activeTab === \'device\'\).*?<\/x-base\.card>\s*@endif/s';

$acsWidget = <<<'HTML'
        @if($activeTab === 'device')
        <div class="space-y-6">
            {{-- ROUTER PELANGGAN (TR-069) --}}
            <x-base.card>
                <x-slot name="header">
                    <div class="flex justify-between items-center w-full">
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">router</span>
                            Router Pelanggan (ACS / TR-069)
                        </h3>
                    </div>
                </x-slot>
                
                <div class="space-y-4">
                    @php $hasRouter = false; @endphp
                    @foreach($customer->customerServices as $service)
                        @if($service->acsDevice)
                            @php $hasRouter = true; @endphp
                            <div class="border border-slate-200 rounded-xl p-5 bg-slate-50 relative overflow-hidden">
                                {{-- Background Decoration --}}
                                <div class="absolute right-0 top-0 w-48 h-48 bg-indigo-50 rounded-full blur-3xl -mr-10 -mt-10 pointer-events-none"></div>
                                
                                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                                    <div>
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="font-bold text-lg text-slate-900">{{ $service->acsDevice->serial_number }}</span>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $service->acsDevice->status === 'online' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                                <span class="w-2 h-2 rounded-full {{ $service->acsDevice->status === 'online' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                                {{ $service->acsDevice->status }}
                                            </span>
                                        </div>
                                        <div class="text-sm font-medium text-slate-600 mb-1">
                                            {{ $service->acsDevice->manufacturer ?? 'Unknown Vendor' }} - {{ $service->acsDevice->model ?? 'Unknown Model' }}
                                        </div>
                                        <div class="text-xs text-slate-500 font-mono">
                                            IP: {{ $service->acsDevice->ip_address ?? '-' }} | MAC: {{ $service->acsDevice->mac_address ?? '-' }}
                                        </div>
                                        <div class="text-xs text-slate-400 mt-2 flex items-center gap-1">
                                            <span class="material-symbols-outlined notranslate" style="font-size:14px" translate="no">update</span>
                                            Last Inform: {{ $service->acsDevice->last_inform ? $service->acsDevice->last_inform->diffForHumans() : '-' }}
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-wrap items-center gap-3 bg-white p-3 rounded-lg border border-slate-100 shadow-sm">
                                        <button wire:click="openWifiModal({{ $service->acsDevice->id }})" class="px-4 py-2 bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 rounded-lg text-sm font-semibold flex items-center gap-2 transition-colors">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">wifi</span> Ubah WiFi
                                        </button>
                                        <button wire:click="rebootModem({{ $service->acsDevice->id }})" wire:confirm="Yakin ingin merestart modem ini dari jarak jauh?" class="px-4 py-2 bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 rounded-lg text-sm font-semibold flex items-center gap-2 transition-colors">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">restart_alt</span> Reboot
                                        </button>
                                        <a href="{{ route('acs.devices.show', $service->acsDevice->id) }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg text-sm font-semibold flex items-center gap-2 transition-colors" target="_blank">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">open_in_new</span> Detail Penuh
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    
                    @if(!$hasRouter)
                        <div class="text-center py-12 text-slate-500 bg-slate-50 rounded-xl border border-dashed border-slate-300">
                            <span class="material-symbols-outlined notranslate text-5xl text-slate-300 mb-3" translate="no">router</span>
                            <p class="text-base font-medium text-slate-600">Belum ada perangkat modem (ACS)</p>
                            <p class="text-sm mt-1">Perangkat akan otomatis terdaftar saat terhubung ke internet dan melakukan Inform.</p>
                        </div>
                    @endif
                </div>
            </x-base.card>

            {{-- LEGACY ONU DEVICES --}}
            @if(!empty($devices))
            <x-base.card :padding="false">
                <x-slot name="header">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:20px">inventory_2</span>
                        Perangkat Inventaris (Legacy ONU)
                    </h3>
                </x-slot>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500">
                                <th class="px-6 py-3 text-left font-semibold">Tipe</th>
                                <th class="px-6 py-3 text-left font-semibold">Model</th>
                                <th class="px-6 py-3 text-left font-semibold">Serial Number</th>
                                <th class="px-6 py-3 text-left font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($devices as $device)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-slate-600">{{ $device['type'] }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $device['brand'] }} {{ $device['model'] }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-900">{{ $device['serial'] }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-lg {{ $device['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($device['status']) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-base.card>
            @endif
        </div>
        @endif
HTML;

$content = preg_replace($deviceTabPattern, $acsWidget, $content);

// Add WiFi modal
$modalCode = <<<'HTML'

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

$content = preg_replace('/(<\/div>\s*)$/s', "$modalCode\n$1", $content);
file_put_contents($file, $content);
echo "Updated Customer360 Blade.";
?>
