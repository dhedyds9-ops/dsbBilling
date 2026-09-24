<?php
$user = \App\Models\User::where('name', 'Budi Tester')->first() ?? \App\Models\User::where('username', 'budi')->orWhere('whatsapp', '0255852222')->first();
if ($user) {
    echo "Found user: ID " . $user->id . "\n";
    $customer = $user->customer;
    if ($customer) {
        echo "Found customer: ID " . $customer->id . "\n";
    } else {
        echo "No customer found via relation!\n";
        $customerDirect = \App\Models\CRM\Customer::where('user_id', $user->id)->first();
        if ($customerDirect) {
            echo "But customer exists with user_id: " . $customerDirect->id . "\n";
        } else {
            echo "Customer table has NO record with user_id = " . $user->id . "\n";
            $customerByPhone = \App\Models\CRM\Customer::where('phone', $user->whatsapp)->first();
            if ($customerByPhone) {
                echo "Customer exists with same phone, but user_id is: " . var_export($customerByPhone->user_id, true) . "\n";
            }
        }
    }
} else {
    echo "User not found.\n";
}
