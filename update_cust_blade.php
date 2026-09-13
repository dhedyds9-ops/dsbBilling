<?php
$file = 'D:/dsBilling/resources/views/livewire/crm/customer/show.blade.php';
$content = file_get_contents($file);

$acsCard = <<<'HTML'
            {{-- ROUTER PELANGGAN (TR-069) --}}
            <x-base.card>
                <x-slot name="header">
                    <div class="flex justify-between items-center w-full">
                        <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                            <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">router</span>
                            Router Pelanggan (ACS)
                        </h3>
                    </div>
                </x-slot>
                
                <div class="space-y-4">
                    @php $hasRouter = false; @endphp
                    @foreach($customer->customerServices as $service)
                        @if($service->acsDevice)
                            @php $hasRouter = true; @endphp
                            <div class="border border-slate-200 rounded-xl p-4 bg-slate-50 relative overflow-hidden">
                                {{-- Background Decoration --}}
                                <div class="absolute right-0 top-0 w-32 h-32 bg-indigo-50 rounded-full blur-3xl -mr-10 -mt-10 pointer-events-none"></div>
                                
                                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-bold text-slate-900">{{ $service->acsDevice->serial_number }}</span>
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider {{ $service->acsDevice->status === 'online' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $service->acsDevice->status === 'online' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                                {{ $service->acsDevice->status }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-slate-500">
                                            Model: {{ $service->acsDevice->model ?? '-' }} &bull; IP: {{ $service->acsDevice->ip_address ?? '-' }}
                                        </div>
                                        <div class="text-xs text-slate-400 mt-1">
                                            Last Inform: {{ $service->acsDevice->last_inform ? $service->acsDevice->last_inform->diffForHumans() : '-' }}
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('acs.devices.show', $service->acsDevice->id) }}" class="px-3 py-1.5 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg text-xs font-semibold flex items-center gap-1 transition-colors">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">visibility</span> Detail
                                        </a>
                                        <button wire:click="openWifiModal({{ $service->acsDevice->id }})" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 rounded-lg text-xs font-semibold flex items-center gap-1 transition-colors">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">wifi</span> WiFi
                                        </button>
                                        <button wire:click="rebootModem({{ $service->acsDevice->id }})" wire:confirm="Reboot modem ini?" class="px-3 py-1.5 bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 rounded-lg text-xs font-semibold flex items-center gap-1 transition-colors">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">restart_alt</span> Reboot
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    
                    @if(!$hasRouter)
                        <div class="text-center py-6 text-slate-500 bg-slate-50 rounded-xl border border-dashed border-slate-300">
                            <span class="material-symbols-outlined notranslate text-4xl text-slate-300 mb-2" translate="no">router</span>
                            <p class="text-sm">Belum ada perangkat modem (ACS) yang dikaitkan ke pelanggan ini.</p>
                        </div>
                    @endif
                </div>
            </x-base.card>
HTML;

// Insert after Informasi Customer card
$content = preg_replace('/(<\/x-base\.card>\s*)<x-base\.card>\s*<x-slot name="header">\s*<h3 class="text-lg font-semibold text-slate-900">Timeline<\/h3>/s', "$1$acsCard\n\n            <x-base.card>\n                <x-slot name=\"header\">\n                    <h3 class=\"text-lg font-semibold text-slate-900\">Timeline</h3>", $content);

// Add WiFi modal to the end of the file
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
HTML;

$content = preg_replace('/(<\/div>\s*)$/s', "$modalCode\n$1", $content);
file_put_contents($file, $content);
echo "Updated Customer Show Blade.";
?>
