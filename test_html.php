<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $component = app(\Livewire\LivewireManager::class)->new('crm.customer.customer360');
    $component->mount(34);
    $html = \Livewire\Livewire::mount('crm.customer.customer360', ['id' => 34]);
    
    // Test if $html contains bad UTF-8
    if (!mb_check_encoding((string) $html, 'UTF-8')) {
        echo "HTML contains bad UTF-8!\n";
    }
    
    $encoded = json_encode((string) $html);
    if ($encoded === false) {
        echo "json_encode failed on HTML! " . json_last_error_msg() . "\n";
    } else {
        echo "HTML encoded successfully.\n";
    }

} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
?>
