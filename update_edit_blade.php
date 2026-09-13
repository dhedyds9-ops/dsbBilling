<?php
$file = 'D:/dsBilling/resources/views/livewire/acs/device/edit.blade.php';
$content = file_get_contents($file);

$wifiFields = <<<HTML
            {{-- Pengaturan WiFi --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden mt-6">
                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/80 font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">wifi</span>
                    Pengaturan WiFi (TR-069)
                </div>
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama WiFi (SSID)</label>
                        <input type="text" wire:model="wifi_ssid" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        <p class="text-[11px] text-slate-500 mt-1">Mengubah nilai ini akan mengirim task perubahan SSID ke modem CPE.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Password WiFi</label>
                        <input type="text" wire:model="wifi_password" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        <p class="text-[11px] text-slate-500 mt-1">Mengubah nilai ini akan mengirim task perubahan Password WiFi ke modem CPE.</p>
                    </div>
                </div>
            </div>
HTML;

$content = preg_replace('/(<\/form>\s*<\/div>)/', $wifiFields . "\n\n$1", $content);

file_put_contents($file, $content);
echo "Updated edit.blade.php";
?>
