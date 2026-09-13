<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = \App\Models\CRM\Customer::find(35);
if (!$c) die("No customer");

$services = $c->customerServices;
echo "Services count: " . $services->count() . "\n";
foreach ($services as $s) {
    echo "Service ID: {$s->id}\n";
    $onu = $s->onu;
    echo "Has ONU? " . ($onu ? "YES ({$onu->id})" : "NO") . "\n";
}
?>
