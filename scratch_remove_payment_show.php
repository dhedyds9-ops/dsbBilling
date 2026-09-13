<?php
$file = 'routes/web.php';
$content = file_get_contents($file);

$content = preg_replace("/Route::get\('\/payments\/\{id\}',\s*\\\\App\\\\Livewire\\\\Billing\\\\Payment\\\\Show::class\)->name\('payments\.show'\);/", "// Route::get('/payments/{id}', \App\Livewire\Billing\Payment\Show::class)->name('payments.show');", $content);
$content = preg_replace("/Route::get\('\/\{id\}',\s*\\\\App\\\\Livewire\\\\Billing\\\\Payment\\\\Show::class\)->name\('show'\);/", "// Route::get('/{id}', \App\Livewire\Billing\Payment\Show::class)->name('show');", $content);

file_put_contents($file, $content);
echo "Commented out payment detail routes\n";
