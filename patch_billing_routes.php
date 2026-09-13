<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$search = "        Route::prefix('sales')->name('sales.')->group(function() {";
$replace = "        Route::prefix('billing')->name('billing.')->group(function() {
            Route::get('/invoices', \App\Livewire\ResellerPortal\Billing\Invoices::class)->name('invoices');
            Route::get('/payments', \App\Livewire\ResellerPortal\Billing\Payments::class)->name('payments');
        });

        Route::prefix('reports')->name('reports.')->group(function() {
            Route::get('/sales', \App\Livewire\ResellerPortal\Reports\Sales::class)->name('sales');
            Route::get('/revenue', \App\Livewire\ResellerPortal\Reports\Revenue::class)->name('revenue');
            Route::get('/commission', \App\Livewire\ResellerPortal\Reports\Commission::class)->name('commission');
        });

        Route::prefix('sales')->name('sales.')->group(function() {";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added billing and reports routes.\n";
?>
