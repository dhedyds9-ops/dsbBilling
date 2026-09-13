<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$search = "            Route::post('/voucher/print', [\App\Http\Controllers\ISP\VoucherPrintController::class, 'print'])->name('voucher.print');";
$replace = "            Route::post('/voucher/print', [\App\Http\Controllers\ISP\VoucherPrintController::class, 'print'])->name('voucher.print');\n            Route::post('/voucher', [\App\Http\Controllers\ISP\VoucherPrintController::class, 'print']);";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added fallback POST route.\n";
?>
