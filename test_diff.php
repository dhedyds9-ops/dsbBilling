<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$olt = \App\Models\ISP\Olt::find(3);
$driver = new \App\Services\Adapters\Provisioning\Drivers\CDataOltDriver($olt);

$ponPorts = $driver->getPonPortsStatus();
$snmpOnlines = [];
foreach ($ponPorts as $port) {
    if ($port["status"] !== "up") continue;
    try {
        $onus = $driver->getOnuRxPower($port["port_index"]);
        foreach ($onus as $onu) {
            if (($onu["status"] ?? "") === "online") {
                $sn = strtoupper(preg_replace("/[^A-Z0-9]/i", "", $onu["serial_number"] ?? ""));
                $snmpOnlines[] = $sn;
            }
        }
    } catch (\Exception $e) {}
}

$dbOnus = \App\Models\ISP\Onu::whereIn("serial_number", $snmpOnlines)->where("olt_id", 3)->get();
echo "Found in DB: " . $dbOnus->count() . "\n";
foreach ($dbOnus as $o) {
    if ($o->status !== "active") {
        echo "Found SNMP online but DB not active: " . $o->serial_number . " status=" . $o->status . "\n";
    }
}
$dbSns = $dbOnus->pluck("serial_number")->toArray();
$missing = array_diff($snmpOnlines, $dbSns);
echo "Missing in DB completely: " . count($missing) . "\n";
print_r($missing);


