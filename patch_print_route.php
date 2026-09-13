<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$search = "            Route::get('/sales', \App\Livewire\ResellerPortal\Reports\Sales::class)->name('sales');";
$replace = "            Route::get('/sales', \App\Livewire\ResellerPortal\Reports\Sales::class)->name('sales');\n            Route::get('/sales/print', [\App\Http\Controllers\Reseller\ReportPrintController::class, 'printSales'])->name('sales.print');";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added print route.\n";
?>
