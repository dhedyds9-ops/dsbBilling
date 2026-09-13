<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$search = "            // Sales & Vouchers";
$replace = "            // Finance Data
            Route::prefix('finance')->name('finance.')->group(function() {
                Route::get('/balance', \App\Livewire\ResellerPortal\Finance\Balance::class)->name('balance');
                Route::get('/topup', \App\Livewire\ResellerPortal\Finance\Topup::class)->name('topup');
                Route::get('/mutations', \App\Livewire\ResellerPortal\Finance\Mutations::class)->name('mutations');
                Route::get('/commission', \App\Livewire\ResellerPortal\Finance\Commission::class)->name('commission');
            });

            // Sales & Vouchers";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added finance routes.\n";
?>
