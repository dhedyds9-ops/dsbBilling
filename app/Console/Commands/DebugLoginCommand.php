<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CRM\Customer;
use App\Models\User;
use App\Models\Customer\CustomerService;

class DebugLoginCommand extends Command
{
    protected $signature = 'debug:login {identity}';
    protected $description = 'Debug why an identity cannot login';

    public function handle()
    {
        $identity = $this->argument('identity');
        $this->info("Debugging identity: " . $identity);
        
        $customer = Customer::where('code', $identity)->first();
        if ($customer) {
            $this->info("[Customer Table] Found Customer! ID: {$customer->id}, Name: {$customer->name}, Phone: {$customer->phone}, Email: {$customer->email}");
            if ($customer->user_id) {
                $this->info("[Customer Table] user_id is SET to: {$customer->user_id}");
                $user = User::find($customer->user_id);
                if ($user) {
                    $this->info("[User Table] User found! Username: {$user->username}, Phone: {$user->whatsapp}, Active: {$user->is_active}");
                } else {
                    $this->error("[User Table] ERROR: User ID {$customer->user_id} DOES NOT EXIST in users table!");
                }
            } else {
                $this->error("[Customer Table] user_id is NULL (Orphaned Customer)!");
                
                // Try to find matching user manually
                $u = User::where('whatsapp', $customer->phone)->orWhere('whatsapp', '0'.substr($customer->phone, 2))->orWhere('whatsapp', '62'.substr($customer->phone, 1))->first();
                if ($u) {
                    $this->info("[User Table] Found a User that matches this customer's phone! User ID: {$u->id}, Phone: {$u->whatsapp}");
                    $customer->update(['user_id' => $u->id]);
                    $this->info("=> AUTO-HEALED! Try logging in now.");
                } else {
                    $this->error("[User Table] Could not find ANY User with phone matching {$customer->phone}!");
                    $this->error("=> This means NO PORTAL LOGIN was ever created for this customer!");
                }
            }
        } else {
            $this->error("[Customer Table] No customer found with code {$identity}");
        }
        
        // Also check Users table
        $userDir = User::where('customer_code', $identity)->first();
        if ($userDir) {
            $this->info("[User Table] Found User by customer_code! ID: {$userDir->id}, Username: {$userDir->username}");
        }
    }
}
