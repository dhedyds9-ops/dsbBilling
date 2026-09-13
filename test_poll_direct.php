<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(\App\Services\ISP\OltPollingService::class);
$olt = \App\Models\ISP\Olt::find(3);

try {
    $result = $service->pollOlt($olt);
    echo "ONU Online: " . $result["onu_online"] . "\n";
    echo "ONU Offline: " . $result["onu_offline"] . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

