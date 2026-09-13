<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::where('username', 'like', '%2026082302%')
    ->orWhere('customer_code', 'like', '%2026082302%')
    ->orWhere('pppoe_username', 'like', '%2026082302%')
    ->get();
foreach($users as $u) {
    echo "User Name: {$u->name}, Code: {$u->customer_code}, Username: {$u->username}, PPPoE: {$u->pppoe_username}\n";
}
?>
