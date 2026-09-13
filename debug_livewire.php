<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $component = app(\Livewire\LivewireManager::class)->new('crm.customer.customer360');
    $component->mount(35);
    
    // Simulate what Livewire dehydrates
    $properties = $component->all();
    
    // Try to json_encode properties
    foreach ($properties as $key => $value) {
        $encoded = json_encode($value);
        if ($encoded === false && json_last_error() === JSON_ERROR_UTF8) {
            echo "Bad UTF-8 in property: $key\n";
        }
    }
    
    $fullEncoded = json_encode($properties);
    if ($fullEncoded === false) {
        echo "Full encoding failed: " . json_last_error_msg() . "\n";
    } else {
        echo "Properties encoded successfully!\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
?>
