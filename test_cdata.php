<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$olt = \App\Models\ISP\Olt::find(3);
$service = app(\App\Services\ISP\OltPollingService::class);
$result = $service->pollOlt($olt);
echo json_encode($result, JSON_PRETTY_PRINT);

