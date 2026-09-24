<?php
$file = 'resources/views/livewire/acs/device/show.blade.php';
$content = file_get_contents($file);

$toggleHtml = <<<'HTML'
                    <div class="mb-4 flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-800">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Status WiFi (SSID)</label>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Aktifkan atau matikan pancaran sinyal WiFi ini.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="wifiEnabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                    
                    <div x-data="{ enabled: @entangle('wifiEnabled') }" x-show="enabled" x-transition>
                        <div class="grid grid-cols-1 gap-4">
HTML;

// Find `<div class="grid grid-cols-1 gap-4">` (assuming there's only one in the modal or it's the first one after the wifi form declaration)
$pos = strpos($content, '<div class="grid grid-cols-1 gap-4">');
if ($pos !== false) {
    $content = substr_replace($content, $toggleHtml, $pos, strlen('<div class="grid grid-cols-1 gap-4">'));
    
    // Find the end of the form to close the x-show div
    $endPos = strpos($content, '<div class="flex justify-end gap-2 mt-6">', $pos);
    if ($endPos !== false) {
        $content = substr_replace($content, "</div>\n                    <div class=\"flex justify-end gap-2 mt-6\">", $endPos, strlen('<div class="flex justify-end gap-2 mt-6">'));
    }
}

file_put_contents($file, $content);
echo "Show.blade.php updated with wifiEnabled toggle.\n";
