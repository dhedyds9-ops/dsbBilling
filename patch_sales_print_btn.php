<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/reports/sales.blade.php';
$content = file_get_contents($file);

$searchBtn = "<button onclick=\"window.print()\" class=\"px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-2 transition-colors shadow-sm text-sm font-medium\">
                <span class=\"material-symbols-outlined notranslate\" translate=\"no\" style=\"font-size:18px\">print</span>
                Cetak Laporan
            </button>";
            
$replaceBtn = "<a href=\"{{ route('reseller-portal.reports.sales.print') }}\" target=\"_blank\" class=\"px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-2 transition-colors shadow-sm text-sm font-medium\">
                <span class=\"material-symbols-outlined notranslate\" translate=\"no\" style=\"font-size:18px\">picture_as_pdf</span>
                Unduh PDF Laporan
            </a>";

$content = str_replace($searchBtn, $replaceBtn, $content);
file_put_contents($file, $content);
echo "Updated print button to PDF.\n";
?>
