<?php
$file = 'resources/views/livewire/isp/router/show.blade.php';
$content = file_get_contents($file);

// Replace PPP active section
$content = preg_replace(
    '/(<div x-show="subTab === \'active\'"><!-- PPPoE Active -->.*?)(<td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">\{\{ \\[\'uptime\'\] \?\? \'-\' \}\}<\/td>)/s',
    '<td class="px-6 py-4 whitespace-nowrap text-right"><button wire:click="disconnectPpp(\'{{ [\'name\'] }}\')" wire:confirm="Yakin ingin kick user ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition-colors" title="Kick"><span class="material-symbols-outlined notranslate text-[18px]" translate="no">power_settings_new</span></button></td>',
    $content
);

// Replace Hotspot active section
$content = preg_replace(
    '/(<div x-show="subTab === \'active\'"><!-- Hotspot Active -->.*?)(<td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">\{\{ \\[\'uptime\'\] \?\? \'-\' \}\}<\/td>)/s',
    '<td class="px-6 py-4 whitespace-nowrap text-right"><button wire:click="disconnectHotspot(\'{{ [\'user\'] ?? [\'mac-address\'] }}\')" wire:confirm="Yakin ingin kick user ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition-colors" title="Kick"><span class="material-symbols-outlined notranslate text-[18px]" translate="no">power_settings_new</span></button></td>',
    $content
);

file_put_contents($file, $content);
