<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $component = app(\Livewire\LivewireManager::class)->new('crm.customer.customer360');
    $component->mount(34);
    $component->render();
    
    $context = new \Livewire\Mechanisms\HandleComponents\ComponentContext($component);
    $data = app(\Livewire\Mechanisms\HandleComponents\HandleComponents::class)->snapshot($component, $context);
    
    json_encode($data, JSON_THROW_ON_ERROR);
    echo "Customer 34 initial render encoded successfully.\n";

} catch (\Exception $e) {
    echo "Exception 1: " . $e->getMessage() . "\n";
}

try {
    $component = app(\Livewire\LivewireManager::class)->new('crm.customer.customer360');
    $component->mount(34);
    $component->setActiveTab('finance');
    $component->render();
    
    $context = new \Livewire\Mechanisms\HandleComponents\ComponentContext($component);
    $data = app(\Livewire\Mechanisms\HandleComponents\HandleComponents::class)->snapshot($component, $context);
    
    json_encode($data, JSON_THROW_ON_ERROR);
    echo "Customer 34 finance tab encoded successfully.\n";

} catch (\Exception $e) {
    echo "Exception 2: " . $e->getMessage() . "\n";
}
?>
