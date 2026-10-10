<?php
$file = 'resources/views/livewire/isp/router/show.blade.php';
$content = file_get_contents($file);

$header = <<<EOT
    <!-- TopBar / Header Section -->
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('isp.routers.index') }}" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors" title="Kembali">
                <span class="material-symbols-outlined notranslate text-[20px]" translate="no">arrow_back</span>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ \->name }}</h1>
                    <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full {{ \->status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-400' }}">
                        {{ \->status === 'active' ? 'Beroperasi' : 'Nonaktif' }}
                    </span>
                </div>
                <div class="flex items-center gap-4 mt-2 text-sm text-slate-500 dark:text-slate-400 font-mono">
                    <span class="flex items-center gap-1">
                        <span class="text-slate-400">#</span> {{ str_pad(\->id, 3, '0', STR_PAD_LEFT) }}
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined notranslate text-[16px]" translate="no">lan</span> 
                        {{ \->ip_address }} : {{ \->api_port }}
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button wire:click="refreshData" wire:loading.attr="disabled" class="px-4 py-2 bg-slate-800 dark:bg-slate-700 text-white hover:bg-slate-900 dark:hover:bg-slate-600 rounded-xl text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-[18px]" wire:loading.class="animate-spin" translate="no">refresh</span>
                Segarkan Data
            </button>
            <button wire:click="generateProvisioningToken({{ \->id }})" class="px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-xl text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">terminal</span>
                Skrip Auto Config
            </button>
            <a href="{{ route('isp.routers.edit', \->id) }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">edit</span>
                Edit
            </a>
        </div>
    </div>
EOT;

// I will just use preg_replace on @if( === 'overview') exactly.
// Since it has spaces before it:
$content = preg_replace('/(\s*@if\(\\ === \'overview\'\))/', "\n\\n", $content);
file_put_contents($file, $content);
