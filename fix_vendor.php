<?php
$f = "resources/views/livewire/isp/vendor/index.blade.php";
$c = file_get_contents($f);
$find = <<<EOF
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
                    <a href="{{ route(\'isp.vendors.create\') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-900/50">
                        <span class="material-symbols-outlined notranslate" style="font-size:18px" translate="no">add</span>
                        Tambah Vendor
                    </a>
                </div>
EOF;
$find = str_replace("\'", "'", $find);

$replace = <<<EOF
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route(\'isp.vendors.create\') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium shadow-sm shadow-indigo-500/20">
                <span class="material-symbols-outlined notranslate" style="font-size:20px" translate="no">add</span>
                Tambah Vendor
            </a>

            <div x-data="{ open: false }" class="relative inline-block text-left">
                <button @click="open = !open" type="button" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors font-medium shadow-sm">
                    <span class="material-symbols-outlined notranslate" style="font-size:20px" translate="no">more_vert</span>
                    Aksi Lainnya
                </button>
                <div x-show="open" @click.away="open = false" x-transition class="origin-top-left absolute left-0 mt-2 w-64 rounded-lg shadow-lg bg-white dark:bg-slate-800 ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 dark:divide-gray-700 z-50">
EOF;
$replace = str_replace("\'", "'", $replace);

$c = str_replace($find, $replace, $c);
$c = str_replace("            </div>\n        </div>", "                </div>\n            </div>\n        </div>", $c); // fixing the extra div nesting if needed, or I'll just use replace_file_content.

