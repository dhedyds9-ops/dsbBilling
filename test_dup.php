<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$oltId = 3; // OLT-Home
// Check duplicate serials
$duplicates = DB::table("onus")
    ->select("serial_number", DB::raw("count(*) as total"))
    ->where("olt_id", $oltId)
    ->groupBy("serial_number")
    ->having("total", ">", 1)
    ->get();

echo "Duplicate SNs on OLT 3: " . $duplicates->count() . "\n";
foreach ($duplicates->take(5) as $dup) {
    echo "  " . $dup->serial_number . " : " . $dup->total . "\n";
}


