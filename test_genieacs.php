<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$acsService = new \App\Services\Adapters\Monitoring\GenieACSDriver();
$query = [
    "projection" => "_id,_deviceId,_lastInform,VirtualParameters,InternetGatewayDevice.DeviceInfo,Device.DeviceInfo,InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection,Device.WANDevice.1.WANConnectionDevice.1.WANPPPConnection,InternetGatewayDevice.ManagementServer.ConnectionRequestURL,Device.ManagementServer.ConnectionRequestURL"
];
try {
    $devices = $acsService->listDevices($query, 2);
    echo json_encode($devices, JSON_PRETTY_PRINT);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}

