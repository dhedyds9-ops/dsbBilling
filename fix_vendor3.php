<?php
$code = <<<EOD
<div class="flex items-center gap-2 w-full sm:w-auto">
            <div class="flex-1 relative sm:w-64">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Cari nama vendor..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            </div>
            <select wire:model.live="filters.status" class="px-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                <option value="">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
            </select>
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer ml-1 bg-slate-50 dark:bg-slate-900/50 px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700">
                <input type="checkbox" wire:model.live="showTrashed" class="w-4 h-4 text-primary-600 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100" />
                Sampah
            </label>
        </div>
    </div>

    <!-- Data Table -->
EOD;

$file = "resources/views/livewire/isp/vendor/index.blade.php";
$content = file_get_contents($file);
$pattern = '/<div class="flex items-center gap-2 w-full sm:w-auto">.*?<\/div>\s*<\/div>\s*<!-- Data Table -->/s';
$content = preg_replace($pattern, $code, $content);
file_put_contents($file, $content);
echo "Replaced!";
