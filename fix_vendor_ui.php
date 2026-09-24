<?php
$file = 'resources/views/livewire/isp/vendor/index.blade.php';
$content = file_get_contents($file);

// 1. Replace the top dropdown with standard buttons
$topSearch = <<<'HTML'
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
                        @if(empty($selectedVendors)) disabled @endif
                        class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left @if(empty($selectedVendors)) text-gray-300 cursor-not-allowed @else text-red-600 hover:bg-red-50 dark:bg-red-900/30 @endif">
                        <span class="material-symbols-outlined notranslate" style="font-size:18px" translate="no">delete</span>
                        Hapus Yang Dipilih
                        <span wire:loading class="ml-2 text-gray-400 text-xs">...</span>
                    </button>
                </div>
                <div class="py-1">
                    <div class="border-t border-gray-100 dark:border-slate-700 my-1"></div>
                    <button type="button" wire:click="export" class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-900/50">
                        <span class="material-symbols-outlined notranslate" style="font-size:18px" translate="no">download</span>
                        Export Data
                    </button>
                </div>
            </div>
        </div>
HTML;
$topSearch = str_replace("\r\n", "\n", $topSearch);
$content = str_replace("\r\n", "\n", $content);

$topReplace = <<<'HTML'
        <div class="flex items-center gap-2">
            @if(!empty($selectedVendors))
            <button
                type="button"
                wire:click="confirmBulkDelete"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:hover:bg-red-900/50 rounded-lg transition-colors font-medium border border-red-200 dark:border-red-800">
                <span class="material-symbols-outlined notranslate" style="font-size:18px" translate="no">delete</span>
                Hapus Terpilih
                <span wire:loading class="ml-2 text-red-400 text-xs">...</span>
            </button>
            @endif
            
            <button type="button" wire:click="export" class="inline-flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-gray-900/50 rounded-lg transition-colors font-medium border border-gray-200 dark:border-gray-700">
                <span class="material-symbols-outlined notranslate" style="font-size:18px" translate="no">download</span>
                Export
            </button>

            <a href="{{ route('isp.vendors.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium shadow-sm shadow-primary-500/20">
                <span class="material-symbols-outlined notranslate" style="font-size:18px" translate="no">add</span>
                Tambah Vendor
            </a>
        </div>
HTML;

$content = str_replace($topSearch, $topReplace, $content);

// 2. Replace Action dropdown with inline buttons
// I will use regex because the inner content of the dropdown is a bit complex
$pattern = '/<div x-data="\{ open: false \}" class="relative inline-block text-left">[\s\S]*?<\/div>\s*<\/div>\s*<\/td>/m';

$rowReplace = <<<'HTML'
<div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('isp.vendors.show', $vendor->id) }}" class="p-1.5 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 bg-gray-50 hover:bg-blue-50 dark:bg-gray-800 dark:hover:bg-blue-900/30 rounded-md transition-colors" title="Detail">
                                        <span class="material-symbols-outlined notranslate" style="font-size: 18px;" translate="no">visibility</span>
                                    </a>
                                    @if(!$vendor->trashed())
                                    <a href="{{ route('isp.vendors.edit', $vendor->id) }}" class="p-1.5 text-gray-500 hover:text-orange-600 dark:text-gray-400 dark:hover:text-orange-400 bg-gray-50 hover:bg-orange-50 dark:bg-gray-800 dark:hover:bg-orange-900/30 rounded-md transition-colors" title="Edit">
                                        <span class="material-symbols-outlined notranslate" style="font-size: 18px;" translate="no">edit</span>
                                    </a>
                                    <button wire:click="confirmDelete({{ $vendor->id }})" class="p-1.5 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 bg-gray-50 hover:bg-red-50 dark:bg-gray-800 dark:hover:bg-red-900/30 rounded-md transition-colors" title="Hapus">
                                        <span class="material-symbols-outlined notranslate" style="font-size: 18px;" translate="no">delete</span>
                                    </button>
                                    @else
                                    <button wire:click="restore({{ $vendor->id }})" class="p-1.5 text-gray-500 hover:text-green-600 dark:text-gray-400 dark:hover:text-green-400 bg-gray-50 hover:bg-green-50 dark:bg-gray-800 dark:hover:bg-green-900/30 rounded-md transition-colors" title="Restore">
                                        <span class="material-symbols-outlined notranslate" style="font-size: 18px;" translate="no">restore</span>
                                    </button>
                                    @endif
                                </div>
                            </td>
HTML;

$content = preg_replace($pattern, $rowReplace, $content);

file_put_contents($file, $content);
echo "UI updated successfully.\n";
