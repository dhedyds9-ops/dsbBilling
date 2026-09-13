<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$olt = \App\Models\ISP\Olt::find(3);
$driver = app(\App\Services\Adapters\Provisioning\OltRegistry::class)->forOlt($olt);
$info = $driver->getSystemInfo();
echo json_encode($info, JSON_PRETTY_PRINT);

