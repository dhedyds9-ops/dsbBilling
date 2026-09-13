<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/reports/commission.blade.php';
$content = file_get_contents($file);

$btnSearch = '<!-- Summary Cards -->';
$btnReplace = <<<PHP
<div class="mb-4 flex justify-end">
    <a href="{{ route('reseller-portal.reports.commission.print') }}" target="_blank" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-2 transition-colors shadow-sm text-sm font-medium">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">picture_as_pdf</span>
        Cetak PDF Laporan
    </a>
</div>
<!-- Summary Cards -->
PHP;

$content = str_replace($btnSearch, $btnReplace, $content);
file_put_contents($file, $content);
echo "Added PDF button to Commission.\n";
?>
