<?php
$file = 'routes/web.php';
$content = file_get_contents($file);

$oldCode = <<<PHP
            // Audit Trail: Administrator +
            Route::get('/audit-trail', \App\Livewire\Admin\AuditTrail\Index::class)->name('audit-trail.index');
            Route::get('/settings', \App\Livewire\Admin\Settings\Index::class)->name('settings.index');
            Route::get('/settings/billing', \App\Livewire\Admin\Settings\Billing::class)->name('settings.billing');
PHP;

$newCode = <<<PHP
            // Audit Trail: Administrator +
            Route::get('/audit-trail', \App\Livewire\Admin\AuditTrail\Index::class)->name('audit-trail.index');
            // Legacy settings routes removed in v2.0 SSOT migration
PHP;

$content = str_replace($oldCode, $newCode, $content);
file_put_contents($file, $content);
echo "Removed legacy routes\n";
