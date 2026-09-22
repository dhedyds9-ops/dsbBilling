<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CRM\Customer;
use App\Models\User;
use App\Models\Customer\CustomerService;
use Illuminate\Support\Facades\Hash;

class DebugLoginCommand extends Command
{
    protected $signature = 'debug:login {identity} {password?}';
    protected $description = 'Debug why an identity cannot login';

    public function handle()
    {
        $identity = $this->argument('identity');
        $password = $this->argument('password') ?: '123456';
        $this->info("Debugging identity: " . $identity . " with password: " . $password);
        
        $customer = Customer::where('code', $identity)->first();
        if ($customer) {
            $this->info("[Customer Table] Found Customer! ID: {$customer->id}");
        } else {
            $this->error("[Customer Table] No customer found with code {$identity}");
        }
        
        $userDir = User::where('customer_code', $identity)->orWhere('username', $identity)->first();
        if ($userDir) {
            $this->info("[User Table] Found User! ID: {$userDir->id}, Username: {$userDir->username}");
            
            // Check Role
            $hasRole = $userDir->hasRole('customer');
            if ($hasRole) {
                $this->info("-> Role Check: PASS (User has 'customer' role)");
            } else {
                $this->error("-> Role Check: FAIL (User DOES NOT have 'customer' role!) => This will cause 'Kredensial tidak cocok'");
                
                // Fix Role
                $role = \App\Models\Role::where('name', 'customer')->first();
                if ($role) {
                    $userDir->roles()->attach($role->id);
                    $this->info("   [!] AUTO-FIXED: Attached 'customer' role to user!");
                }
            }
            
            // Check Password
            $passCheck = Hash::check($password, $userDir->password);
            if ($passCheck) {
                $this->info("-> Password Check: PASS (Password matches)");
            } else {
                $this->error("-> Password Check: FAIL (Password is NOT {$password}) => This will cause 'Kredensial tidak cocok'");
            }
            
            // Check Active
            if ($userDir->is_active) {
                $this->info("-> Status Check: PASS (User is active)");
            } else {
                $this->error("-> Status Check: FAIL (User is inactive) => This will cause 'Akun belum aktif'");
            }
            
        } else {
            $this->error("[User Table] User not found at all!");
        }
    }
}
