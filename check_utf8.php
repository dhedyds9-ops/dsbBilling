<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$customer = \App\Models\CRM\Customer::with([
    'customerServices.service',
    'customerServices.serviceProfile',
    'customerServices.onu',
    'customerServices.pppoeUser',
    'customerServices.hotspotUser',
    'invoices',
    'payments',
    'contracts',
    'installations', 'tickets',
])->findOrFail(35);

$array = $customer->toArray();

function findBadUtf8($array, $path = "") {
    foreach ($array as $key => $value) {
        $currentPath = $path ? "$path.$key" : $key;
        if (is_array($value)) {
            findBadUtf8($value, $currentPath);
        } elseif (is_string($value)) {
            if (!mb_check_encoding($value, 'UTF-8')) {
                echo "Bad UTF-8 found at: $currentPath\n";
            }
        }
    }
}

findBadUtf8($array);
echo "Check completed.\n";
?>
