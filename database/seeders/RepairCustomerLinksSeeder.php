<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RepairCustomerLinksSeeder extends Seeder
{
    public function run()
    {
        $users = \App\Models\User::all();
        $fixed = 0;
        foreach($users as $user) {
            if ($user->hasRole('customer')) {
                $customer = $user->customer;
                if (!$customer) {
                    $cust = \App\Models\CRM\Customer::where('phone', $user->whatsapp)
                        ->orWhere('name', $user->name)
                        ->first();
                    if ($cust) {
                        $cust->user_id = $user->id;
                        $cust->save();
                        $fixed++;
                    }
                }
                
                if (empty($user->pppoe_username)) {
                    $service = \App\Models\Customer\CustomerService::whereHas('customer', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })->first();
                    
                    if ($service && !empty($service->username)) {
                        $user->pppoe_username = $service->username;
                        $user->save();
                    }
                }
            }
        }
        echo "Repaired " . $fixed . " broken customer links.\n";
    }
}
