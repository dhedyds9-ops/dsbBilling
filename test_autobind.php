<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$acs = new \App\Livewire\ACS\Device\Index();
$acs->syncDevices();
echo "Sync completed with auto-binding.\n";

