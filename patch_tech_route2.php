<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$search = "Route::middleware(['auth'])->group(function () use (\$cs) {";
$replace = "Route::middleware(['auth'])->group(function () use (\$cs) {
    Route::get('/technician-portal/dashboard', \App\Livewire\ISP\Technician\Dashboard::class)->name('technician.dashboard');
";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added technician.dashboard route properly.\n";
?>
