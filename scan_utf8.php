<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function findBadUtf8($data, $path = "") {
    if (is_array($data)) {
        foreach ($data as $k => $v) {
            findBadUtf8($v, $path ? "$path.$k" : $k);
        }
    } elseif (is_string($data)) {
        if (!mb_check_encoding($data, 'UTF-8')) {
            echo "Bad string found at path: $path\n";
            echo "Value (hex): " . bin2hex($data) . "\n";
            // Clean it to show what it is
            echo "Value (cleaned): " . mb_convert_encoding($data, 'UTF-8', 'UTF-8') . "\n\n";
        }
    }
}

try {
    $component = app(\Livewire\LivewireManager::class)->new('crm.customer.customer360');
    $component->mount(34);
    $component->render();
    
    $context = new \Livewire\Mechanisms\HandleComponents\ComponentContext($component);
    $data = app(\Livewire\Mechanisms\HandleComponents\HandleComponents::class)->snapshot($component, $context);
    
    echo "Scanning snapshot for Customer 34...\n";
    findBadUtf8($data);
    echo "Scan complete.\n";

} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
?>
