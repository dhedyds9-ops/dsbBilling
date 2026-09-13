<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$search = "            Route::get('/active', \$comingSoon)->name('active');\n            Route::get('/isolated', \$comingSoon)->name('isolated');";
$replace = "            Route::get('/active', \App\Livewire\ResellerPortal\Customer\Active::class)->name('active');\n            Route::get('/isolated', \App\Livewire\ResellerPortal\Customer\Isolated::class)->name('isolated');";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Updated routes for active and isolated.\n";
?>
