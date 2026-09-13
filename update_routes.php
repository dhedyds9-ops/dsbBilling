<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);
$content = str_replace("Route::get('/devices/create', \App\Livewire\ACS\Device\Create::class)->name('devices.create');\n", "", $content);
file_put_contents($file, $content);
echo "Removed route.";
?>
