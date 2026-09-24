<?php
$file = 'resources/views/livewire/isp/onu/index-v2.blade.php';
$content = file_get_contents($file);

$search = <<<PHP
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="SN / MAC / Pelanggan" class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600 px-2 py-1 text-xs rounded border focus:outline-none w-56 dark:bg-slate-900 dark:text-slate-100">
PHP;
$replace = <<<PHP
            <select wire:model.live="perPage" class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600 px-2 py-1 text-xs rounded border focus:outline-none dark:bg-slate-900 dark:text-slate-100">
                <option class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="25">25 Baris</option>
                <option class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="50">50 Baris</option>
                <option class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="100">100 Baris</option>
                <option class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="0">All</option>
            </select>
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="SN / MAC / Pelanggan" class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600 px-2 py-1 text-xs rounded border focus:outline-none w-56 dark:bg-slate-900 dark:text-slate-100">
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added perPage select to ISP blade\n";
