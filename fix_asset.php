<?php
$code = <<<EOD
<div class="flex items-center gap-2 w-full sm:w-auto">
            <div class="flex-1 relative sm:w-64">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="filters.search"
                       placeholder="Cari kode, nama, SN..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            </div>
            <select wire:model.live="filters.status" class="px-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                <option value="all">Semua Status</option>
                <option value="in_use">In Use</option>
                <option value="available">Available</option>
                <option value="maintenance">Maintenance</option>
                <option value="retired">Retired</option>
            </select>
            <select wire:model.live="perPage" class="hidden sm:block px-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                <option value="15">15 Baris</option>
                <option value="25">25 Baris</option>
                <option value="50">50 Baris</option>
                <option value="100">100 Baris</option>
            </select>
        </div>
    </div>

    {{-- Data Table --}}
EOD;

$file = "resources/views/livewire/inventory/asset-list.blade.php";
$content = file_get_contents($file);
$pattern = '/<div class="flex items-center gap-2 w-full sm:w-auto">.*?<\/div>\s*<\/div>\s*\{\{-- Data Table --\}\}/s';
$content = preg_replace($pattern, $code, $content);
file_put_contents($file, $content);
echo "Asset fixed! ";
