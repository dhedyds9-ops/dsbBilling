<?php

namespace Tests\Feature;

use App\Models\User;
use App\Enums\UserRole;
use App\Enums\JobFunction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Role;

class PortalSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup base roles if missing using custom model
        Role::firstOrCreate(['name' => UserRole::Administrator->value], ['display_name' => 'Administrator']);
        Role::firstOrCreate(['name' => UserRole::Manager->value], ['display_name' => 'Manager']);
        Role::firstOrCreate(['name' => UserRole::Reseller->value], ['display_name' => 'Reseller']);
        Role::firstOrCreate(['name' => UserRole::Customer->value], ['display_name' => 'Customer']);
    }

    private function createUserWithRole(string $role, string $jobFunction = '', array $permissions = [])
    {
        $user = User::factory()->create(['job_function' => $jobFunction]);
        $user->attachRole($role);
        
        $roleModel = Role::where('name', $role)->first();
        foreach ($permissions as $permName) {
            $perm = \App\Models\Permission::firstOrCreate(['name' => $permName], ['display_name' => $permName]);
            if (!$roleModel->permissions->contains('id', $perm->id)) {
                $roleModel->permissions()->attach($perm->id);
            }
        }

        return $user;
    }

    public function test_noc_can_access_noc_portal_but_not_others()
    {
        $noc = $this->createUserWithRole(UserRole::Manager->value, JobFunction::NOC->value, ['noc.view']);

        $this->actingAs($noc)->get('/noc')->assertStatus(200);
        $this->actingAs($noc)->get('/technician/dashboard')->assertStatus(403);
        $this->actingAs($noc)->get('/dashboard')->assertStatus(302); // Redirected to /noc
    }

    public function test_technician_can_access_technician_portal_but_not_others()
    {
        $tech = $this->createUserWithRole(UserRole::Manager->value, JobFunction::TECHNICIAN->value, ['technician.portal']);

        $this->actingAs($tech)->get('/technician/dashboard')->assertStatus(200);
        $this->actingAs($tech)->get('/noc')->assertStatus(403);
        $this->actingAs($tech)->get('/dashboard')->assertStatus(302); // Redirected to /technician
    }

    public function test_admin_can_access_dashboard_but_not_sub_portals()
    {
        $admin = $this->createUserWithRole(UserRole::Administrator->value, '');

        $this->actingAs($admin)->get('/dashboard')->assertStatus(200);
        // Administrator has full access to all portals
        $this->actingAs($admin)->get('/noc')->assertStatus(200); 
        $this->actingAs($admin)->get('/technician/dashboard')->assertStatus(200);
    }

    public function test_customer_cannot_access_backoffice()
    {
        $customer = $this->createUserWithRole(UserRole::Customer->value, '');

        // RoleMiddleware will redirect customer to customer-portal
        $this->actingAs($customer)->get('/noc')->assertStatus(302);
        $this->actingAs($customer)->get('/technician/dashboard')->assertStatus(302);
        $this->actingAs($customer)->get('/dashboard')->assertStatus(302);
        $this->actingAs($customer)->get('/reseller-portal/dashboard')->assertStatus(302);
    }

    // --- PHASE 8.13.8 RESELLER PORTAL TESTS ---

    public function test_reseller_owner_can_access_reseller_portal()
    {
        $owner = $this->createUserWithRole(UserRole::Reseller->value, ''); // null or empty job_function
        $this->actingAs($owner)->get('/reseller-portal/dashboard')->assertStatus(200);
    }

    public function test_pengurus_can_access_reseller_portal()
    {
        $pengurus = $this->createUserWithRole(UserRole::Reseller->value, JobFunction::PENGURUS->value);
        $this->actingAs($pengurus)->get('/reseller-portal/dashboard')->assertStatus(200);
    }

    public function test_sales_can_access_reseller_portal()
    {
        $sales = $this->createUserWithRole(UserRole::Reseller->value, JobFunction::SALES->value);
        $this->actingAs($sales)->get('/reseller-portal/dashboard')->assertStatus(200);
    }

    public function test_noc_cannot_access_reseller_portal()
    {
        $noc = $this->createUserWithRole(UserRole::Manager->value, JobFunction::NOC->value);
        $this->actingAs($noc)->get('/reseller-portal/dashboard')->assertStatus(302);
    }

    public function test_technician_cannot_access_reseller_portal()
    {
        $tech = $this->createUserWithRole(UserRole::Manager->value, JobFunction::TECHNICIAN->value);
        $this->actingAs($tech)->get('/reseller-portal/dashboard')->assertStatus(302);
    }

    public function test_sales_cannot_access_admin_or_manager_portals()
    {
        $sales = $this->createUserWithRole(UserRole::Reseller->value, JobFunction::SALES->value);
        // RoleMiddleware will redirect reseller away from admin/manager portals to reseller portal
        $this->actingAs($sales)->get('/dashboard')->assertStatus(302);
        $this->actingAs($sales)->get('/noc')->assertStatus(302);
        $this->actingAs($sales)->get('/technician/dashboard')->assertStatus(302);
    }

    public function test_sales_without_permission_cannot_access_customer_create()
    {
        $sales = $this->createUserWithRole(UserRole::Reseller->value, JobFunction::SALES->value);
        // No permissions granted
        $this->actingAs($sales)->get('/reseller-portal/customers/create')->assertStatus(403);
    }

    public function test_sales_with_permission_can_access_customer_create()
    {
        $sales = $this->createUserWithRole(UserRole::Reseller->value, JobFunction::SALES->value, ['customer.create']);
        $this->actingAs($sales)->get('/reseller-portal/customers/create')->assertStatus(200);
    }
}
