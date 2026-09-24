<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use App\Services\Auth\UserQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleStandardizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed core roles
        foreach (UserRole::cases() as $role) {
            Role::updateOrCreate(['name' => $role->value], ['display_name' => ucfirst($role->value)]);
        }
    }

    /** @test */
    public function it_fetches_only_resellers_for_sales_options()
    {
        $reseller = User::factory()->create();
        $reseller->attachRole(UserRole::Reseller->value);

        $customer = User::factory()->create();
        $customer->attachRole(UserRole::Customer->value);
        
        $admin = User::factory()->create();
        $admin->attachRole(UserRole::Administrator->value);

        $service = app(UserQueryService::class);
        
        // When fetching resellers (e.g. for IncomeReport dropdown)
        $options = $service->getResellersForDropdown();

        $this->assertArrayHasKey($reseller->id, $options);
        $this->assertArrayNotHasKey($customer->id, $options, 'Customer seharusnya tidak muncul di dropdown reseller');
        $this->assertArrayNotHasKey($admin->id, $options, 'Admin seharusnya tidak muncul di dropdown reseller');
    }

    /** @test */
    public function it_fetches_eligible_assignees_correctly()
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->attachRole(UserRole::Administrator->value);

        $manager = User::factory()->create(['is_active' => true]);
        $manager->attachRole(UserRole::Manager->value);

        $customer = User::factory()->create(['is_active' => true]);
        $customer->attachRole(UserRole::Customer->value);
        
        $inactiveManager = User::factory()->create(['is_active' => false]);
        $inactiveManager->attachRole(UserRole::Manager->value);

        $service = app(UserQueryService::class);
        $options = $service->getEligibleAssigneesForDropdown();

        $this->assertArrayHasKey($admin->id, $options, 'Admin harus ada di assignee');
        $this->assertArrayHasKey($manager->id, $options, 'Manager harus ada di assignee');
        
        $this->assertArrayNotHasKey($customer->id, $options, 'Customer tidak boleh jadi assignee');
        $this->assertArrayNotHasKey($inactiveManager->id, $options, 'User tidak aktif tidak boleh jadi assignee');
    }
}
