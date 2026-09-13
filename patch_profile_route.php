<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$searchStr = "Route::get('/connection-info', \App\Livewire\CustomerPortal\ConnectionInfo::class)->name('self-service.connection-info');";
$replaceStr = $searchStr . "\n        Route::get('/profile', \App\Livewire\CustomerPortal\Profile\Profile::class)->name('profile');";

$content = str_replace($searchStr, $replaceStr, $content);
file_put_contents($file, $content);
echo "Added customer-portal.profile route.\n";
?>
