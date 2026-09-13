<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$acsService = new \App\Services\Adapters\Monitoring\GenieACSDriver();
$devices = $acsService->listDevices([], 10000);
$countAcs = count($devices);
$countLocal = \App\Models\ACS\ACSDevice::count();
echo "Total in ACS: $countAcs \n";
echo "Total in Local DB: $countLocal \n";

