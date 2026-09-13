<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CRM\Customer;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use App\Services\Auth\BusinessScopeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        foreach (UserRole::cases() as $role) {
            Role::updateOrCreate(['name' => $role->value], ['display_name' => ucfirst($role->value)]);
        }
    }

    /** @test */
    public function reseller_can_only_see_their_own_customers()
    {
        // Setup Resellers
        $resellerA = User::factory()->create();
        $resellerA->attachRole(UserRole::Reseller->value);

        $resellerB = User::factory()->create();
        $resellerB->attachRole(UserRole::Reseller->value);

        // Setup Customers
        $customerA = Customer::create(['uuid' => \Str::uuid(), 'code' => 'CUST-A', 'name' => 'A', 'phone' => '123', 'reseller_id' => $resellerA->id]);
        $customerB = Customer::create(['uuid' => \Str::uuid(), 'code' => 'CUST-B', 'name' => 'B', 'phone' => '456', 'reseller_id' => $resellerB->id]);

        $service = app(BusinessScopeService::class);

        // Act as Reseller A
        $this->actingAs($resellerA);
        
        $queryA = Customer::query();
        $service->scopeCustomerQuery($queryA);
        
        $customersForA = $queryA->get();
        $this->assertTrue($customersForA->contains($customerA));
        $this->assertFalse($customersForA->contains($customerB), 'Reseller A seharusnya tidak bisa melihat customer Reseller B');

        // Act as Administrator (Should see all)
        $admin = User::factory()->create();
        $admin->attachRole(UserRole::Administrator->value);
        $this->actingAs($admin);
        
        $queryAdmin = Customer::query();
        $service->scopeCustomerQuery($queryAdmin);
        
        $customersForAdmin = $queryAdmin->get();
        $this->assertTrue($customersForAdmin->contains($customerA));
        $this->assertTrue($customersForAdmin->contains($customerB));
    }

    /** @test */
    public function branch_manager_can_only_see_customers_in_their_branch()
    {
        $branchSukabumi = Branch::factory()->create(['name' => 'Sukabumi']);
        $branchCianjur = Branch::factory()->create(['name' => 'Cianjur']);

        $managerSukabumi = User::factory()->create(['branch_id' => $branchSukabumi->id]);
        $managerSukabumi->attachRole(UserRole::Manager->value);

        $customerSukabumi = Customer::create(['uuid' => \Str::uuid(), 'code' => 'CUST-S', 'name' => 'S', 'phone' => '1', 'branch_id' => $branchSukabumi->id]);
        $customerCianjur = Customer::create(['uuid' => \Str::uuid(), 'code' => 'CUST-C', 'name' => 'C', 'phone' => '2', 'branch_id' => $branchCianjur->id]);

        $this->actingAs($managerSukabumi);
        
        $service = app(BusinessScopeService::class);
        $query = Customer::query();
        $service->scopeCustomerQuery($query);
        
        $results = $query->get();
        $this->assertTrue($results->contains($customerSukabumi));
        $this->assertFalse($results->contains($customerCianjur), 'Manager Sukabumi tidak boleh melihat data cabang Cianjur');
    }
}
