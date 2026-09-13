<?php
$file = 'D:/dsBilling/resources/views/livewire/acs/device/edit.blade.php';
$content = file_get_contents($file);

$wifiFields = <<<HTML
            {{-- Pengaturan WiFi --}}
            <div class="col-span-1 md:col-span-2 pt-4 mt-2 border-t border-slate-200">
                <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">wifi</span>
                    Pengaturan WiFi (TR-069)
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama WiFi (SSID)</label>
                        <input type="text" wire:model="wifi_ssid" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Biarkan kosong jika tidak diubah">
                        <p class="text-[11px] text-slate-500 mt-1">Isi untuk mengirim task perubahan SSID ke modem.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Password WiFi</label>
                        <input type="text" wire:model="wifi_password" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Biarkan kosong jika tidak diubah">
                        <p class="text-[11px] text-slate-500 mt-1">Isi untuk mengirim task perubahan Password ke modem.</p>
                    </div>
                </div>
            </div>
HTML;

$content = preg_replace('/(<div class="flex justify-end gap-4 pt-4 border-t border-slate-200">)/', $wifiFields . "\n\n            $1", $content);

file_put_contents($file, $content);
echo "Updated edit.blade.php again";
?>
