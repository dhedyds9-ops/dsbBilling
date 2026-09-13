<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$oltId = 3;
$onus = \App\Models\ISP\Onu::where("olt_id", $oltId)->where("status", "!=", "active")->get();
echo "Non-active ONUs on OLT 3: " . $onus->count() . "\n";
foreach ($onus->take(10) as $o) {
    echo $o->serial_number . " : " . $o->status . "\n";
}

