<?php
$file = 'resources/views/livewire/isp/vendor/index.blade.php';
$content = file_get_contents($file);

// Replace Top Dropdown
$topSearch = '        <div x-data="{ open: false }" class="relative inline-block text-left">
            <div>
                <button @click="open = !open" type="button" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium shadow-sm shadow-primary-500/20">
                    <span class="material-symbols-outlined notranslate" style="font-size:20px" translate="no">manage_accounts</span>
                    Manajemen Vendor
                    <span class="material-symbols-outlined notranslate" style="font-size:20px" translate="no">expand_more</span>
                </button>
            </div>
            <div x-show="open" @click.away="open = false" x-transition class="origin-top-left absolute left-0 mt-2 w-64 rounded-lg shadow-lg bg-white dark:bg-slate-800 ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 dark:divide-gray-700 z-50">
                <div class="py-1">
                    <a href="{{ route(\'isp.vendors.create\') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-900/50">
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
        </div>';

$topReplace = '        <div class="flex items-center gap-2">
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

            <a href="{{ route(\'isp.vendors.create\') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium shadow-sm shadow-primary-500/20">
                <span class="material-symbols-outlined notranslate" style="font-size:18px" translate="no">add</span>
                Tambah Vendor
            </a>
        </div>';

$content = str_replace(str_replace("\r\n", "\n", $topSearch), $topReplace, str_replace("\r\n", "\n", $content));

// Replace Row Dropdown
$rowSearch = '                                <div x-data="{ open: false }" class="relative inline-block text-left">
                                    <div>
                                        <button @click="open = !open" type="button" class="inline-flex items-center gap-1 p-2.5 text-gray-500 hover:text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:bg-gray-800 rounded-lg transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div x-show="open" @click.away="open = false" x-transition class="origin-top-right absolute right-0 mt-2 w-48 rounded-lg shadow-lg bg-white dark:bg-slate-800 ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 dark:divide-gray-700 z-50">
                                        @if(!$vendor->trashed())
                                            <div class="py-1">
                                                <a href="{{ route(\'isp.vendors.show\', $vendor->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-900/50">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Lihat Detail
                                                </a>
                                                <a href="{{ route(\'isp.vendors.edit\', $vendor->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-900/50">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit
                                                </a>
                                                <button
                                                    type="button"
                                                    wire:click="duplicate({{ $vendor->id }})"
                                                    wire:loading.attr="disabled"
                                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-900/50 w-full text-left">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v8a2 2 0 012 2z" />
                                                    </svg>
                                                    Duplicate
                                                    <span wire:loading class="ml-2 text-gray-400 text-xs">...</span>
                                                </button>
                                            </div>
                                            <div class="py-1">
                                                <button
                                                    type="button"
                                                    wire:click="toggleStatus({{ $vendor->id }})"
                                                    wire:loading.attr="disabled"
                                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-900/50 w-full text-left">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    {{ $vendor->status === \'active\' ? \'Nonaktifkan\' : \'Aktifkan\' }}
                                                    <span wire:loading class="ml-2 text-gray-400 text-xs">...</span>
                                                </button>
                                            </div>
                                            <div class="py-1">
                                                <button
                                                    type="button"
                                                    wire:click="delete({{ $vendor->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:confirm="Apakah Anda yakin ingin menghapus vendor ini?"
                                                    class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:bg-red-900/30 w-full text-left">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Hapus
                                                    <span wire:loading class="ml-2 text-gray-400 text-xs">...</span>
                                                </button>
                                            </div>
                                        @else
                                            <div class="py-1">
                                                <button
                                                    type="button"
                                                    wire:click="restore({{ $vendor->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:confirm="Apakah Anda yakin ingin memulihkan vendor ini?"
                                                    class="flex items-center gap-2 px-4 py-2 text-sm text-green-600 hover:bg-green-50 dark:bg-green-900/30 w-full text-left">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                    Pulihkan
                                                    <span wire:loading class="ml-2 text-gray-400 text-xs">...</span>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>';

$rowReplace = '                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route(\'isp.vendors.show\', $vendor->id) }}" class="p-1.5 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 bg-gray-50 hover:bg-blue-50 dark:bg-gray-800 dark:hover:bg-blue-900/30 rounded-md transition-colors" title="Detail">
                                        <span class="material-symbols-outlined notranslate" style="font-size: 18px;" translate="no">visibility</span>
                                    </a>
                                    @if(!$vendor->trashed())
                                    <a href="{{ route(\'isp.vendors.edit\', $vendor->id) }}" class="p-1.5 text-gray-500 hover:text-orange-600 dark:text-gray-400 dark:hover:text-orange-400 bg-gray-50 hover:bg-orange-50 dark:bg-gray-800 dark:hover:bg-orange-900/30 rounded-md transition-colors" title="Edit">
                                        <span class="material-symbols-outlined notranslate" style="font-size: 18px;" translate="no">edit</span>
                                    </a>
                                    <button wire:click="delete({{ $vendor->id }})" wire:confirm="Apakah Anda yakin ingin menghapus vendor ini?" class="p-1.5 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 bg-gray-50 hover:bg-red-50 dark:bg-gray-800 dark:hover:bg-red-900/30 rounded-md transition-colors" title="Hapus">
                                        <span class="material-symbols-outlined notranslate" style="font-size: 18px;" translate="no">delete</span>
                                    </button>
                                    @else
                                    <button wire:click="restore({{ $vendor->id }})" wire:confirm="Apakah Anda yakin ingin memulihkan vendor ini?" class="p-1.5 text-gray-500 hover:text-green-600 dark:text-gray-400 dark:hover:text-green-400 bg-gray-50 hover:bg-green-50 dark:bg-gray-800 dark:hover:bg-green-900/30 rounded-md transition-colors" title="Restore">
                                        <span class="material-symbols-outlined notranslate" style="font-size: 18px;" translate="no">restore</span>
                                    </button>
                                    @endif
                                </div>';

$content = str_replace(str_replace("\r\n", "\n", $rowSearch), $rowReplace, $content);
file_put_contents($file, $content);
echo "Done.\n";
