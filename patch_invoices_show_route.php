<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$search = "            Route::get('/invoices', \App\Livewire\ResellerPortal\Billing\Invoices::class)->name('invoices');
            Route::get('/payments', \App\Livewire\ResellerPortal\Billing\Payments::class)->name('payments');";
$replace = "            Route::get('/invoices', \App\Livewire\ResellerPortal\Billing\Invoices::class)->name('invoices');
            Route::get('/invoices/{id}', \App\Livewire\ResellerPortal\Billing\InvoiceShow::class)->name('invoices.show');
            Route::get('/payments', \App\Livewire\ResellerPortal\Billing\Payments::class)->name('payments');";
$content = str_replace($search, $replace, $content);

file_put_contents($file, $content);
echo "Added invoices.show to web.php\n";
?>
