<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('username', 'nanang')
    ->orWhere('customer_code', 'nanang')
    ->orWhere('pppoe_username', 'nanang')
    ->first();
if ($user) {
    echo "Found user: " . $user->name . "\n";
    echo "Username: " . $user->username . "\n";
    echo "Customer Code: " . $user->customer_code . "\n";
    echo "PPPoE Username: " . $user->pppoe_username . "\n";
    echo "Whatsapp: " . $user->whatsapp . "\n";
    echo "Password check (123456): " . (password_verify('123456', $user->password) ? 'OK' : 'FAIL') . "\n";
    echo "Is active: " . $user->is_active . "\n";
    echo "Has customer role: " . ($user->hasRole('customer') ? 'YES' : 'NO') . "\n";
} else {
    echo "User nanang not found in users table!\n";
    
    // Check customers table
    $customer = App\Models\CRM\Customer::where('code', 'nanang')->orWhere('name', 'like', '%nanang%')->first();
    if ($customer) {
        echo "Found in CRM\Customer: " . $customer->name . ", code=" . $customer->code . ", user_id=" . $customer->user_id . "\n";
        if ($customer->user_id) {
            $u = App\Models\User::find($customer->user_id);
            if ($u) {
                echo "Customer User account details:\n";
                echo "Username: " . $u->username . "\n";
                echo "Customer Code: " . $u->customer_code . "\n";
                echo "PPPoE Username: " . $u->pppoe_username . "\n";
            }
        }
    } else {
        echo "Customer nanang not found anywhere.\n";
    }
}
?>
