<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$oltId = 3;
$snmpOnlines = DB::table("onus")->where("olt_id", $oltId)->where("status", "active")->count();
echo "Total active rows (including duplicates if any): " . $snmpOnlines . "\n";

$uniqueSnmpOnlines = DB::table("onus")->where("olt_id", $oltId)->where("status", "active")->distinct("serial_number")->count("serial_number");
echo "Unique active serials: " . $uniqueSnmpOnlines . "\n";

