<?php
$file = 'resources/views/livewire/isp/router/show.blade.php';
$content = file_get_contents($file);

// PPP
if (strpos($content, '<!-- PPPoE Servers -->') !== false) {
    $pppTabs = '<div class="space-y-6" x-data="{ subTab: \'active\' }">' . "\n" . 
        '<div class="flex gap-4 border-b border-slate-200 dark:border-slate-700 mb-2">' . "\n" .
        '<button @click="subTab = \'active\'" :class="subTab === \'active\' ? \'border-blue-600 text-blue-600 dark:text-blue-400\' : \'border-transparent text-slate-500 dark:text-slate-400\'" class="px-4 py-3 border-b-2 font-medium text-sm transition-colors">PPPoE Active</button>' . "\n" .
        '<button @click="subTab = \'servers\'" :class="subTab === \'servers\' ? \'border-blue-600 text-blue-600 dark:text-blue-400\' : \'border-transparent text-slate-500 dark:text-slate-400\'" class="px-4 py-3 border-b-2 font-medium text-sm transition-colors">PPPoE Servers</button>' . "\n" .
        '<button @click="subTab = \'profiles\'" :class="subTab === \'profiles\' ? \'border-blue-600 text-blue-600 dark:text-blue-400\' : \'border-transparent text-slate-500 dark:text-slate-400\'" class="px-4 py-3 border-b-2 font-medium text-sm transition-colors">PPPoE Profiles</button>' . "\n" .
        '</div>' . "\n" .
        '<div x-show="subTab === \'servers\'">' . "\n" . '                  <!-- PPPoE Servers -->';
    
    $content = str_replace('<div class="space-y-6">' . "\n" . '                  <!-- PPPoE Servers -->', $pppTabs, $content);
    // If exact match failed, try just replacing <!-- PPPoE Servers -->
    if (strpos($content, $pppTabs) === false) {
        $content = preg_replace('/<div class="space-y-6">\s*<!-- PPPoE Servers -->/', $pppTabs, $content);
    }

    $content = str_replace('<!-- PPPoE Profiles -->', '</div><div x-show="subTab === \'profiles\'"><!-- PPPoE Profiles -->', $content);
    $content = str_replace('<!-- PPPoE Active -->', '</div><div x-show="subTab === \'active\'"><!-- PPPoE Active -->', $content);
    
    // Close the alpine div
    $content = str_replace('@elseif( === \'hotspot\')', '</div></div>@elseif( === \'hotspot\')', $content);
}

// Hotspot
if (strpos($content, '<!-- Hotspot Servers -->') !== false) {
    $hotspotTabs = '<div class="space-y-6" x-data="{ subTab: \'active\' }">' . "\n" . 
        '<div class="flex gap-4 border-b border-slate-200 dark:border-slate-700 mb-2">' . "\n" .
        '<button @click="subTab = \'active\'" :class="subTab === \'active\' ? \'border-blue-600 text-blue-600 dark:text-blue-400\' : \'border-transparent text-slate-500 dark:text-slate-400\'" class="px-4 py-3 border-b-2 font-medium text-sm transition-colors">Hotspot Active</button>' . "\n" .
        '<button @click="subTab = \'servers\'" :class="subTab === \'servers\' ? \'border-blue-600 text-blue-600 dark:text-blue-400\' : \'border-transparent text-slate-500 dark:text-slate-400\'" class="px-4 py-3 border-b-2 font-medium text-sm transition-colors">Hotspot Servers</button>' . "\n" .
        '<button @click="subTab = \'profiles\'" :class="subTab === \'profiles\' ? \'border-blue-600 text-blue-600 dark:text-blue-400\' : \'border-transparent text-slate-500 dark:text-slate-400\'" class="px-4 py-3 border-b-2 font-medium text-sm transition-colors">Hotspot Profiles</button>' . "\n" .
        '</div>' . "\n" .
        '<div x-show="subTab === \'servers\'">' . "\n" . '                  <!-- Hotspot Servers -->';
    
    $content = preg_replace('/<div class="space-y-6">\s*<!-- Hotspot Servers -->/', $hotspotTabs, $content);
    $content = str_replace('<!-- Hotspot Profiles -->', '</div><div x-show="subTab === \'profiles\'"><!-- Hotspot Profiles -->', $content);
    $content = str_replace('<!-- Hotspot Active -->', '</div><div x-show="subTab === \'active\'"><!-- Hotspot Active -->', $content);
    
    // Close the alpine div
    $content = str_replace('@elseif( === \'logs\')', '</div></div>@elseif( === \'logs\')', $content);
}

// Add Kick to PPP
$content = preg_replace(
    '/<th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Uptime<\/th>\s*<\/tr>/',
    '<th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Uptime</th><th class="px-6 py-4 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th></tr>',
    $content,
    1 // only first match (PPP)
);

$content = preg_replace(
    '/<td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">\{\{ \\[\'uptime\'\] \?\? \'-\' \}\}<\/td>\s*<\/tr>/',
    '<td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ [\'uptime\'] ?? \'-\' }}</td><td class="px-6 py-4 whitespace-nowrap text-right"><button wire:click="disconnectPpp(\'{{ [\'name\'] }}\')" wire:confirm="Yakin ingin kick user ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition-colors" title="Kick"><span class="material-symbols-outlined notranslate text-[18px]" translate="no">power_settings_new</span></button></td></tr>',
    $content
); // this will hit all uptime cells, I need to distinguish PPP and Hotspot, wait, hotspot uses ['uptime'] too!

file_put_contents($file, $content);
