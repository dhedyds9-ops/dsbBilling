<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$search = <<<'PHP'
            Route::get('/pppoe', $comingSoon)->name('pppoe');
            Route::get('/hotspot', $comingSoon)->name('hotspot');
PHP;

$replace = <<<'PHP'
            Route::get('/pppoe', \App\Livewire\ResellerPortal\Customer\Pppoe::class)->name('pppoe');
            Route::get('/hotspot', \App\Livewire\ResellerPortal\Customer\Hotspot::class)->name('hotspot');
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Routes updated.\n";
?>
