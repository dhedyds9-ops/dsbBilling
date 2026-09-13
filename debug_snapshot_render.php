<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $component = app(\Livewire\LivewireManager::class)->new('crm.customer.customer360');
    $component->mount(35);
    $component->render(); // CALL RENDER!
    
    $context = new \Livewire\Mechanisms\HandleComponents\ComponentContext($component);
    $data = app(\Livewire\Mechanisms\HandleComponents\HandleComponents::class)->snapshot($component, $context);
    
    $full = json_encode($data);
    if ($full === false) {
        echo "Full snapshot failed: " . json_last_error_msg() . "\n";
        
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
        echo "Snapshot encoded fine after render!\n";
    }

} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
?>
