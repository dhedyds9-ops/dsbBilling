<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$olt = \App\Models\ISP\Olt::find(3);
$driver = new \App\Services\Adapters\Provisioning\Drivers\CDataOltDriver($olt);

$ponPorts = $driver->getPonPortsStatus();
$snmpData = [];
foreach ($ponPorts as $port) {
    if ($port["status"] !== "up") continue;
    try {
        $onus = $driver->getOnuRxPower($port["port_index"]);
        foreach ($onus as $onu) {
            $sn = strtoupper(preg_replace("/[^A-Z0-9]/i", "", $onu["serial_number"] ?? ""));
            if (!isset($snmpData[$sn])) $snmpData[$sn] = [];
            $snmpData[$sn][] = [
                "status" => $onu["status"] ?? "",
                "port" => $port["port_index"],
                "onu_id" => $onu["onu_index"] ?? ""
            ];
        }
    } catch (\Exception $e) {}
}

foreach ($snmpData as $sn => $items) {
    if (count($items) > 1) {
        echo "Duplicate SN from OLT: $sn\n";
        foreach ($items as $item) {
            echo "  Port: {$item["port"]}, ONU ID: {$item["onu_id"]}, Status: {$item["status"]}\n";
        }
    }
}

