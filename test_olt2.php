<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check what status values exist in onus table across all OLTs
$statuses = DB::table("onus")->select("status", DB::raw("count(*) as total"))->groupBy("status")->get();
echo "All ONU status values in DB:\n";
foreach ($statuses as $s) {
    echo "  \"" . $s->status . "\" => " . $s->total . "\n";
}

