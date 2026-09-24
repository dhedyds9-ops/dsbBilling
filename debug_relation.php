<?php
$users = \App\Models\User::all();
$broken = 0;
foreach($users as $user) {
    if ($user->hasRole('customer')) {
        $customer = $user->customer;
        if (!$customer) {
            echo "Broken User ID: " . $user->id . " Name: " . $user->name . " - Has 'customer' role but ->customer is NULL!\n";
            $direct = \App\Models\CRM\Customer::where('user_id', $user->id)->first();
            if ($direct) {
                echo "  Wait, Customer actually exists with user_id! ID: " . $direct->id . "\n";
            } else {
                echo "  And NO customer has this user_id!\n";
            }
            $broken++;
        }
    }
}
if ($broken == 0) echo "All customer users have valid customer relations!\n";
