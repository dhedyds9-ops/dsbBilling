<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$search = <<<'PHP'
    // ==================== RESELLER PORTAL ====================
    Route::middleware(['role:reseller'])->prefix('reseller-portal')->name('reseller-portal.')->group(function () {
        Route::get('/dashboard', \App\Livewire\ResellerPortal\Dashboard::class)->name('dashboard');
    });
PHP;

$replace = <<<'PHP'
    // ==================== RESELLER PORTAL ====================
    Route::middleware(['role:reseller'])->prefix('reseller-portal')->name('reseller-portal.')->group(function () {
        Route::get('/dashboard', \App\Livewire\ResellerPortal\Dashboard::class)->name('dashboard');
        
        // Placeholder routes for missing modules
        $comingSoon = \App\Livewire\ResellerPortal\ComingSoon::class;
        
        Route::prefix('customers')->name('customers.')->group(function() use ($comingSoon) {
            Route::get('/', $comingSoon)->name('index');
            Route::get('/pppoe', $comingSoon)->name('pppoe');
            Route::get('/hotspot', $comingSoon)->name('hotspot');
            Route::get('/active', $comingSoon)->name('active');
            Route::get('/isolated', $comingSoon)->name('isolated');
        });
        
        Route::get('/service-profiles', $comingSoon)->name('service-profiles.index');
        Route::get('/pppoe-users', $comingSoon)->name('pppoe-users.index');
        Route::get('/hotspot-users', $comingSoon)->name('hotspot-users.index');
        Route::get('/vouchers', $comingSoon)->name('vouchers.index');
        Route::get('/invoices', $comingSoon)->name('invoices.index');
        Route::get('/payments', $comingSoon)->name('payments.index');
        Route::get('/reports', $comingSoon)->name('reports.index');
    });
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added placeholder routes.\n";
?>
