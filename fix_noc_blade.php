<?php
$file = 'resources/views/livewire/noc/onu/index.blade.php';
$content = file_get_contents($file);

$search = <<<PHP
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="SN / MAC / Pelanggan" class="noc-input px-2 py-1 text-xs rounded border focus:outline-none w-56 dark:bg-slate-900 dark:text-slate-100">
        </div>
PHP;
$replace = <<<PHP
            <select wire:model.live="perPage" class="noc-input px-2 py-1 text-xs rounded border focus:outline-none dark:bg-slate-900 dark:text-slate-100">
                <option value="25">25 Baris</option>
                <option value="50">50 Baris</option>
                <option value="100">100 Baris</option>
                <option value="0">All</option>
            </select>
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="SN / MAC / Pelanggan" class="noc-input px-2 py-1 text-xs rounded border focus:outline-none w-56 dark:bg-slate-900 dark:text-slate-100">
        </div>
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added perPage select to NOC blade\n";
