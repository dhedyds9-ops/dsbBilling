<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$search = "            Route::post('/voucher', [\App\Http\Controllers\ISP\VoucherPrintController::class, 'print']);";
$replace = "            Route::post('/voucher', [\App\Http\Controllers\ISP\VoucherPrintController::class, 'print']);\n            Route::get('/voucher/preview-template/{id}', [\App\Http\Controllers\ISP\VoucherPrintController::class, 'previewTemplate']);";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added preview-template route.\n";
?>
