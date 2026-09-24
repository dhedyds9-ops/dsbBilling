<?php
$file = 'resources/views/livewire/acs/device/show.blade.php';
$content = file_get_contents($file);

$search = <<<'HTML'
            <button wire:click="openWifiModal" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:bg-indigo-900/50 border border-indigo-200 rounded-lg text-sm font-semibold transition-colors">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">wifi</span> Ganti WiFi
            </button>
HTML;

$replace = $search . "\n            <livewire:acs.device.wan-manager :device=\"\$device\" />";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Show.blade.php updated with WanManager component.\n";
