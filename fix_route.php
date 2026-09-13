<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$searchStr = "Route::get('/connection-info', \App\Livewire\CustomerPortal\ConnectionInfo::class)->name('self-service.connection-info');";
$insertStr = $searchStr . "\n        Route::get('/speed-test', \App\Livewire\CustomerPortal\SelfService\SpeedTest::class)->name('self-service.speed-test');";

$content = str_replace($searchStr, $insertStr, $content);
file_put_contents($file, $content);
echo "Added speed-test route correctly.\n";
?>
