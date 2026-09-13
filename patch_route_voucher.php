<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$search = "Route::get('/hotspot', \App\Livewire\ResellerPortal\Customer\Hotspot::class)->name('hotspot');";
$replace = "Route::get('/hotspot', \App\Livewire\ResellerPortal\Customer\Hotspot::class)->name('hotspot');\n          });\n\n          Route::prefix('sales')->name('sales.')->group(function() {\n              Route::get('/voucher', \App\Livewire\ResellerPortal\Sales\Voucher::class)->name('voucher');";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Registered voucher route.\n";
?>
