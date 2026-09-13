<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$oltId = 3; // OLT-Home
$total = \App\Models\ISP\Onu::withTrashed()->where("olt_id", $oltId)->count();
$active = \App\Models\ISP\Onu::withTrashed()->where("olt_id", $oltId)->where("status", "active")->count();
$activeNotTrashed = \App\Models\ISP\Onu::where("olt_id", $oltId)->where("status", "active")->count();

echo "OLT 3 Total (with trashed): $total\n";
echo "OLT 3 Active (with trashed): $active\n";
echo "OLT 3 Active (not trashed): $activeNotTrashed\n";

$oltId2 = 2; // Olt-Cisarap
$total2 = \App\Models\ISP\Onu::withTrashed()->where("olt_id", $oltId2)->count();
$active2 = \App\Models\ISP\Onu::withTrashed()->where("olt_id", $oltId2)->where("status", "active")->count();
$activeNotTrashed2 = \App\Models\ISP\Onu::where("olt_id", $oltId2)->where("status", "active")->count();

echo "\nOLT 2 Total (with trashed): $total2\n";
echo "OLT 2 Active (with trashed): $active2\n";
echo "OLT 2 Active (not trashed): $activeNotTrashed2\n";


