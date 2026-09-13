<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cust = App\Models\CRM\Customer::where('code', '2026082302')->orWhere('phone', 'like', '%2026082302%')->first();
if ($cust) {
    echo "Customer found: " . $cust->name . ", code=" . $cust->code . "\n";
} else {
    echo "No customer with 2026082302 in CRM.\n";
}

$pppoe = App\Models\ISP\PppoeUser::where('username', 'nanang')->orWhere('username', 'like', '%nanang%')->first();
if ($pppoe) {
    echo "PPPoE found: " . $pppoe->username . "\n";
} else {
    echo "No PPPoE user nanang found.\n";
}
?>
