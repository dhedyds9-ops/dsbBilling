<?php
$file = 'routes/web.php';
$content = file_get_contents($file);

$content = preg_replace("/Route::get\('\/settings',\s*\\\\App\\\\Livewire\\\\Admin\\\\Settings\\\\Index::class\)->name\('settings\.index'\);/", "// Route::get('/settings', \App\Livewire\Admin\Settings\Index::class)->name('settings.index');", $content);
$content = preg_replace("/Route::get\('\/settings\/billing',\s*\\\\App\\\\Livewire\\\\Admin\\\\Settings\\\\Billing::class\)->name\('settings\.billing'\);/", "// Route::get('/settings/billing', \App\Livewire\Admin\Settings\Billing::class)->name('settings.billing');", $content);

file_put_contents($file, $content);
echo "Commented out legacy routes using regex\n";
