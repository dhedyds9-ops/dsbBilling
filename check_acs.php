<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = \App\Models\CRM\Customer::find(35);
$s = $c->customerServices->first();
if ($s) {
    echo "Has ACS Device? " . ($s->acsDevice ? 'YES' : 'NO') . "\n";
} else {
    echo "No service.\n";
}
?>
