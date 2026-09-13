<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$search = <<<'PHP'
        Route::prefix('customers')->name('customers.')->group(function() use ($comingSoon) {
            Route::get('/', \App\Livewire\ResellerPortal\Customer\Index::class)->name('index');
            Route::get('/{id}/detail', $comingSoon)->name('show');
            Route::get('/pppoe', \App\Livewire\ResellerPortal\Customer\Pppoe::class)->name('pppoe');
            Route::get('/hotspot', \App\Livewire\ResellerPortal\Customer\Hotspot::class)->name('hotspot');
            Route::get('/active', $comingSoon)->name('active');
            Route::get('/isolated', $comingSoon)->name('isolated');
        });
PHP;

$replace = <<<'PHP'
        Route::prefix('customers')->name('customers.')->group(function() use ($comingSoon) {
            Route::get('/', \App\Livewire\ResellerPortal\Customer\Index::class)->name('index');
            Route::get('/create', \App\Livewire\ResellerPortal\Customer\Create::class)->name('create');
            Route::get('/{id}/edit', \App\Livewire\ResellerPortal\Customer\Edit::class)->name('edit');
            Route::get('/{id}/detail', \App\Livewire\ResellerPortal\Customer\Customer360::class)->name('show');
            Route::get('/pppoe', \App\Livewire\ResellerPortal\Customer\Pppoe::class)->name('pppoe');
            Route::get('/hotspot', \App\Livewire\ResellerPortal\Customer\Hotspot::class)->name('hotspot');
            Route::get('/active', $comingSoon)->name('active');
            Route::get('/isolated', $comingSoon)->name('isolated');
        });
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Routes updated.\n";
?>
