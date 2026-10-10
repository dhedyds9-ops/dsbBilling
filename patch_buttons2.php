<?php
$file = 'resources/views/livewire/isp/router/show.blade.php';
$content = file_get_contents($file);

$targetLine = '<td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ [\'uptime\'] ?? \'-\' }}</td>';

$pppReplacement = $targetLine . "\n" . '                                          <td class="px-6 py-4 whitespace-nowrap text-right"><button wire:click="disconnectPpp(\'{{ [\'name\'] }}\')" wire:confirm="Yakin ingin kick user ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition-colors" title="Kick"><span class="material-symbols-outlined notranslate text-[18px]" translate="no">power_settings_new</span></button></td>';

$hotspotReplacement = $targetLine . "\n" . '                                          <td class="px-6 py-4 whitespace-nowrap text-right"><button wire:click="disconnectHotspot(\'{{ [\'user\'] ?? [\'mac-address\'] }}\')" wire:confirm="Yakin ingin kick user ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition-colors" title="Kick"><span class="material-symbols-outlined notranslate text-[18px]" translate="no">power_settings_new</span></button></td>';

$pos1 = strpos($content, $targetLine);
if ($pos1 !== false) {
    $content = substr_replace($content, $pppReplacement, $pos1, strlen($targetLine));
    $pos2 = strpos($content, $targetLine, $pos1 + 1);
    if ($pos2 !== false) {
        $content = substr_replace($content, $hotspotReplacement, $pos2, strlen($targetLine));
    }
}

file_put_contents($file, $content);
