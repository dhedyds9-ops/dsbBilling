<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $component = app(\Livewire\LivewireManager::class)->new('crm.customer.customer360');
    $component->mount(35);
    
    $context = new \Livewire\Mechanisms\HandleComponents\ComponentContext($component);
    $data = app(\Livewire\Mechanisms\HandleComponents\HandleComponents::class)->snapshot($component, $context);
    
    // Test encoding specific pieces
    foreach ($data['data'] as $key => $value) {
        $encoded = json_encode($value);
        if ($encoded === false && json_last_error() === JSON_ERROR_UTF8) {
            echo "Bad UTF-8 in data: $key\n";
        }
    }
    
    foreach ($data['memo'] as $key => $value) {
        $encoded = json_encode($value);
        if ($encoded === false && json_last_error() === JSON_ERROR_UTF8) {
            echo "Bad UTF-8 in memo: $key\n";
        }
    }

    $full = json_encode($data);
    if ($full === false) {
        echo "Full snapshot failed!\n";
        
        // Deep search
        function deepSearch($arr, $path = "") {
            foreach ($arr as $k => $v) {
                $p = $path ? "$path.$k" : $k;
                if (is_array($v)) {
                    deepSearch($v, $p);
                } elseif (is_string($v)) {
                    if (!mb_check_encoding($v, 'UTF-8')) {
                        echo "Bad string at: $p\n";
                    }
                }
            }
        }
        deepSearch($data);
    } else {
        echo "Snapshot encoded fine?!?\n";
    }

} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
?>
