<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$search = "            Route::get('/hotspot', \App\Livewire\ResellerPortal\Customer\Hotspot::class)->name('hotspot');\n          });\n\n          Route::prefix('sales')->name('sales.')->group(function() {\n              Route::get('/voucher', \App\Livewire\ResellerPortal\Sales\VoucherIndex::class)->name('voucher');\n            Route::get('/active', \$comingSoon)->name('active');\n            Route::get('/isolated', \$comingSoon)->name('isolated');\n        });";

$replace = "            Route::get('/hotspot', \App\Livewire\ResellerPortal\Customer\Hotspot::class)->name('hotspot');\n            Route::get('/active', \$comingSoon)->name('active');\n            Route::get('/isolated', \$comingSoon)->name('isolated');\n        });\n\n        Route::prefix('sales')->name('sales.')->group(function() {\n            Route::get('/voucher', \App\Livewire\ResellerPortal\Sales\VoucherIndex::class)->name('voucher');\n        });";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Fixed route closure.\n";
?>
