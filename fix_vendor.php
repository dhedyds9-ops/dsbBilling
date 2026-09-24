<?php
$file = "resources/views/livewire/isp/vendor/index.blade.php";
$content = file_get_contents($file);

$startPos = strpos($content, '<!-- Header -->');
$endPos = strpos($content, '<table class="w-full text-sm text-left">');

if ($startPos !== false && $endPos !== false) {
    $before = substr($content, 0, $startPos);
    $after = substr($content, $endPos);
    
    $newHeader = <<<EOD
    {{-- TOOLBAR & FILTER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
        <div x-data="{ open: false }" class="relative inline-block text-left">
            <div>
                <button @click="open = !open" type="button" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium shadow-sm shadow-primary-500/20">
                    <span class="material-symbols-outlined notranslate" style="font-size:20px" translate="no">manage_accounts</span>
                    Manajemen Vendor
                    <span class="material-symbols-outlined notranslate" style="font-size:20px" translate="no">expand_more</span>
                </button>
            </div>
            <div x-show="open" @click.away="open = false" x-transition class="origin-top-left absolute left-0 mt-2 w-64 rounded-lg shadow-lg bg-white dark:bg-slate-800 ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 dark:divide-gray-700 z-50">
                <div class="py-1">
                    <a href="{{ route('isp.vendors.create') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-900/50">
                        <span class="material-symbols-outlined notranslate" style="font-size:18px" translate="no">add</span>
                        Tambah Vendor
                    </a>
                </div>
                <div class="py-1">
                    <button
                        type="button"
                        wire:click="confirmBulkDelete"
                        wire:loading.attr="disabled"
                        @if(empty(\$selectedVendors)) disabled @endif
                        class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left @if(empty(\$selectedVendors)) text-gray-300 cursor-not-allowed @else text-red-600 hover:bg-red-50 dark:bg-red-900/30 @endif">
                        <span class="material-symbols-outlined notranslate" style="font-size:18px" translate="no">delete</span>
                        Hapus Yang Dipilih
                        <span wire:loading class="ml-2 text-gray-400 text-xs">...</span>
                    </button>
                </div>
                <div class="py-1">
                    <div class="border-t border-gray-100 dark:border-slate-700 my-1"></div>
                    <button type="button" wire:click="export" class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-900/50">
                        <span class="material-symbols-outlined notranslate" style="font-size:18px" translate="no">download</span>
                        Export Vendor
                    </button>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <div class="flex-1 relative sm:w-64">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Cari nama vendor..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            </div>
            <select wire:model.live="statusFilter" class="px-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                <option value="all">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
                <option value="deleted">Dihapus (Sampah)</option>
            </select>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            
EOD;

    file_put_contents($file, $before . $newHeader . $after);
    
    // Fix the closing card tag
    $content = file_get_contents($file);
    $content = str_replace('</x-base.card>', '</div>', $content);
    file_put_contents($file, $content);
    echo "Replaced properly!\n";
} else {
    echo "Tags not found!\n";
}
