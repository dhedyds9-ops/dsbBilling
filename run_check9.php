<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$h = App\Models\ISP\HotspotUser::with('customerService')->find(1);
echo "Hotspot user username: " . $h->username . "\n";
echo "Customer Service attributes: " . print_r($h->customerService->attributes, true) . "\n";
?>
