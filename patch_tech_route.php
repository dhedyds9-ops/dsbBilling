<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$search = "Route::middleware('auth')->group(function () {";
$replace = "Route::middleware('auth')->group(function () {
    Route::get('/technician-portal/dashboard', \App\Livewire\Isp\Technician\Dashboard::class)->name('technician.dashboard');
";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added technician.dashboard route.\n";
?>
