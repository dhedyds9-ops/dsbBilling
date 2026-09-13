<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$acs = new \App\Livewire\ACS\Device\Index();
$acs->syncDevices();
$acs->deleteAllOfflineDevices();

$count = \App\Models\ACS\ACSDevice::count();
echo "Cleaned up DB. Total devices now: $count \n";

