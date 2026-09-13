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
        }
    }
}

try {
    $component = app(\Livewire\LivewireManager::class)->new('crm.customer.customer360');
    $component->mount(34);
    $component->setActiveTab('finance'); // Simulate user action!
    $component->render();
    
    $context = new \Livewire\Mechanisms\HandleComponents\ComponentContext($component);
    $data = app(\Livewire\Mechanisms\HandleComponents\HandleComponents::class)->snapshot($component, $context);
    
    echo "Scanning snapshot for Customer 34 (finance tab)...\n";
    findBadUtf8($data);
    
    $component->setActiveTab('devices');
    $component->render();
    $context = new \Livewire\Mechanisms\HandleComponents\ComponentContext($component);
    $data = app(\Livewire\Mechanisms\HandleComponents\HandleComponents::class)->snapshot($component, $context);
    echo "Scanning snapshot for Customer 34 (devices tab)...\n";
    findBadUtf8($data);
    
    echo "Scan complete.\n";

} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
?>
