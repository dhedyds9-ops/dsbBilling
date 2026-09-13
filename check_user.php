<?php
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
    $customer = App\Models\CRM\Customer::where('code', 'nanang')->first();
    if ($customer) {
        echo "Found in CRM\Customer: " . $customer->name . ", user_id=" . $customer->user_id . "\n";
    }
}
?>
