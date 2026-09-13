<?php
$file = 'D:/dsBilling/resources/views/livewire/keuangan/pengeluaran/index.blade.php';
$content = file_get_contents($file);

// Replace page_title section
$newTitle = <<<HTML
    @section('page_title')
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined notranslate text-rose-500" translate="no" style="font-size:24px">account_balance_wallet</span>
            <span class="text-lg">Pengeluaran (Opex)</span>
        </div>
    @endsection
HTML;
$content = preg_replace('/@section\(\'page_title\'\).*?@endsection/is', $newTitle, $content);

// Add export button to toolbar
$toolbarPattern = '/<div class="flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">/';
$newToolbar = <<<HTML
        <div class="flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button wire:click="exportCsv" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm flex items-center gap-2 transition-colors">
                    <span class="material-symbols-outlined notranslate text-[18px]" translate="no">download</span>
                    Export CSV
                </button>
            </div>
HTML;
$content = preg_replace($toolbarPattern, $newToolbar, $content);

file_put_contents($file, $content);
echo "Updated pengeluaran";
?>
