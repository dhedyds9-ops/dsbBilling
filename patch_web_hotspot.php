<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$search = <<<'PHP'
            Route::get('/create', \App\Livewire\ResellerPortal\Customer\Create::class)->name('create');
PHP;

$replace = <<<'PHP'
            Route::get('/create', \App\Livewire\ResellerPortal\Customer\Create::class)->name('create');
            Route::get('/hotspot/create', \App\Livewire\ResellerPortal\Customer\CreateHotspot::class)->name('hotspot.create');
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Updated web.php\n";
?>
