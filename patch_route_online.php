<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$search = "Route::get('/hotspot', \App\Livewire\ResellerPortal\Customer\Hotspot::class)->name('hotspot');";
$replace = "Route::get('/hotspot', \App\Livewire\ResellerPortal\Customer\Hotspot::class)->name('hotspot');\n            Route::get('/user-online', \App\Livewire\ResellerPortal\Customer\UserOnline::class)->name('user-online');";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added route.\n";
?>
