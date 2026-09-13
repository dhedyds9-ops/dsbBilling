<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$searchRoute = "Route::get('/sales/print', [\App\Http\Controllers\Reseller\ReportPrintController::class, 'printSales'])->name('sales.print');";
$replaceRoute = $searchRoute . "\n        Route::get('/commission/print', [\App\Http\Controllers\Reseller\ReportPrintController::class, 'printCommission'])->name('commission.print');";

$content = str_replace($searchRoute, $replaceRoute, $content);
file_put_contents($file, $content);
echo "Added Commission print route.\n";
?>
