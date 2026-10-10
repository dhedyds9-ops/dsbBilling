<?php
$file = 'resources/views/livewire/isp/router/show.blade.php';
$content = file_get_contents($file);

$targetUptime = '<td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ [\'uptime\'] ?? \'-\' }}</td>' . "\n" . '                                      </tr>';

$pppReplacement = '<td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ [\'uptime\'] ?? \'-\' }}</td>' . "\n" . '                                      <td class="px-6 py-4 whitespace-nowrap text-right"><button wire:click="disconnectPpp(\'{{ [\'name\'] }}\')" wire:confirm="Yakin ingin kick user ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition-colors" title="Kick"><span class="material-symbols-outlined notranslate text-[18px]" translate="no">power_settings_new</span></button></td></tr>';

$hotspotReplacement = '<td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ [\'uptime\'] ?? \'-\' }}</td>' . "\n" . '                                      <td class="px-6 py-4 whitespace-nowrap text-right"><button wire:click="disconnectHotspot(\'{{ [\'user\'] ?? [\'mac-address\'] }}\')" wire:confirm="Yakin ingin kick user ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition-colors" title="Kick"><span class="material-symbols-outlined notranslate text-[18px]" translate="no">power_settings_new</span></button></td></tr>';

// We know there are exactly two of these target uptimes. 
// First is PPP, second is Hotspot.
$pos1 = strpos($content, $targetUptime);
if ($pos1 !== false) {
    $content = substr_replace($content, $pppReplacement, $pos1, strlen($targetUptime));
    $pos2 = strpos($content, $targetUptime, $pos1 + 1);
    if ($pos2 !== false) {
        $content = substr_replace($content, $hotspotReplacement, $pos2, strlen($targetUptime));
    }
}

// Add the missing Uptime header for Hotspot (I accidentally only did PPP earlier)
$targetUptimeHeader = '<th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Uptime</th>' . "\n" . '                                  </tr>';
$headerReplacement = '<th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Uptime</th><th class="px-6 py-4 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th></tr>';
$content = str_replace($targetUptimeHeader, $headerReplacement, $content);


file_put_contents($file, $content);
