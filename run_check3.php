<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user1 = App\Models\User::where('customer_code', '2026082302')
    ->orWhere('username', '2026082302')
    ->orWhere('pppoe_username', '2026082302')
    ->orWhere('name', 'like', '%2026082302%')
    ->first();

if ($user1) {
    echo "Found user 1: " . $user1->name . ", username=" . $user1->username . ", code=" . $user1->customer_code . ", pppoe=" . $user1->pppoe_username . "\n";
    echo "Password check 123456: " . (password_verify('123456', $user1->password) ? 'OK' : 'FAIL') . "\n";
} else {
    echo "User 2026082302 not found in users table.\n";
    $customer1 = App\Models\CRM\Customer::where('code', '2026082302')->first();
    if ($customer1) {
        echo "Found customer 2026082302, user_id = " . $customer1->user_id . "\n";
    }
}

$user2 = App\Models\User::where('name', 'like', '%nanang%')
    ->orWhere('username', 'nanang')
    ->orWhere('pppoe_username', 'nanang')
    ->get();

foreach($user2 as $u) {
    echo "Found Nanang user: " . $u->name . ", username=" . $u->username . ", code=" . $u->customer_code . ", pppoe=" . $u->pppoe_username . "\n";
    echo "Password check 123456: " . (password_verify('123456', $u->password) ? 'OK' : 'FAIL') . "\n";
}

?>
