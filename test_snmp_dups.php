<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$olt = \App\Models\ISP\Olt::find(3);
$driver = new \App\Services\Adapters\Provisioning\Drivers\CDataOltDriver($olt);

$ponPorts = $driver->getPonPortsStatus();
$allSn = [];
$snmpOnlineCount = 0;
foreach ($ponPorts as $port) {
    if ($port["status"] !== "up") continue;
    $idx = $port["port_index"];
    try {
        $onus = $driver->getOnuRxPower($idx);
        foreach ($onus as $onu) {
            if (($onu["status"] ?? "") === "online") {
                $snmpOnlineCount++;
                $sn = strtoupper(preg_replace("/[^A-Z0-9]/i", "", $onu["serial_number"] ?? ""));
                if (!isset($allSn[$sn])) {
                    $allSn[$sn] = 0;
                }
                $allSn[$sn]++;
            }
        }
    } catch (\Exception $e) {}
}

echo "SNMP Online Count: " . $snmpOnlineCount . "\n";
echo "Unique SNs: " . count($allSn) . "\n";
$dups = array_filter($allSn, fn($c) => $c > 1);
echo "Duplicate SNs in SNMP response: " . count($dups) . "\n";
if (count($dups) > 0) {
    print_r($dups);
}

