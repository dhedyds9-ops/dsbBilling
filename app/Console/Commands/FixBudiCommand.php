<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CRM\Customer;
use App\Models\User;
use App\Models\Customer\CustomerService;
use Illuminate\Support\Facades\Hash;

class FixBudiCommand extends Command
{
    protected $signature = 'fix:budi {identity}';
    protected $description = 'Untangle a reseller account from a customer account';

    public function handle()
    {
        $identity = $this->argument('identity');
        $this->info("Fixing tangles for ID: " . $identity);
        
        $customer = Customer::where('code', $identity)->first();
        if (!$customer) {
            $this->error("Customer not found. Maybe it's soft deleted? Searching withTrashed...");
            $customer = Customer::withTrashed()->where('code', $identity)->first();
            if ($customer) {
                $this->info("Found soft-deleted customer! Restoring it...");
                $customer->restore();
            } else {
                $this->error("Still not found. Creating it manually from CustomerService!");
                $cs = CustomerService::where('username', 'budi')->orWhere('username', $identity)->first();
                if ($cs) {
                    $this->info("Found service! Recreating customer...");
                    $customer = Customer::create([
                        'code' => $identity,
                        'name' => 'Budi',
                        'phone' => '0255852222',
                        'status' => 'active'
                    ]);
                    $cs->update(['customer_id' => $customer->id]);
                }
            }
        }
        
        if ($customer) {
            if ($customer->user_id) {
                $u = User::find($customer->user_id);
                if ($u && $u->username === 'denroy') {
                    $this->error("WARNING: This customer is linked to the RESELLER 'denroy'!");
                    $this->info("Untangling...");
                    $customer->update(['user_id' => null]);
                    
                    // Clear the wrong customer_code from denroy
                    if ($u->customer_code === $identity) {
                        $u->update(['customer_code' => null]);
                        $this->info("Removed customer_code from denroy.");
                    }
                }
            }
            
            // Create a dedicated user for Budi
            if (!$customer->user_id) {
                $this->info("Creating a fresh, dedicated portal user for this customer...");
                $newUser = clone $customer;
                $user = User::create([
                    'name' => $customer->name,
                    'username' => $customer->code,
                    'customer_code' => $customer->code,
                    'pppoe_username' => 'budi',
                    'whatsapp' => $customer->phone,
                    'password' => Hash::make('123456'),
                    'is_active' => true,
                ]);
                $role = \App\Models\Role::where('name', 'customer')->first();
                if ($role) $user->roles()->attach($role->id);
                
                $customer->update(['user_id' => $user->id]);
                $this->info("SUCCESS! Dedicated user created. Username: {$user->username}, Password: 123456");
            }
        }
    }
}
