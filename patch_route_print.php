<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$search = "            Route::get('/voucher', \App\Livewire\ResellerPortal\Sales\VoucherIndex::class)->name('voucher');";
$replace = "            Route::get('/voucher', \App\Livewire\ResellerPortal\Sales\VoucherIndex::class)->name('voucher');\n            Route::post('/voucher/print', [\App\Http\Controllers\ISP\VoucherPrintController::class, 'print'])->name('voucher.print');";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added print route.\n";
?>
