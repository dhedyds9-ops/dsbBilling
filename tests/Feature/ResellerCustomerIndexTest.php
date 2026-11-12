<?php

namespace Tests\Feature;

use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\ServiceProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ResellerCustomerIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_customer_is_excluded_from_reseller_customer_list(): void
    {
        $resellerRole = Role::firstOrCreate(['name' => 'reseller'], ['label' => 'Reseller']);
        $resellerUser = User::factory()->create(['email' => 'reseller@test.local', 'is_active' => true]);
        $resellerUser->roles()->attach($resellerRole->id);

        // Regular customer
        $normalCustomer = Customer::create([
            'name' => 'Regular Customer',
            'code' => 'CUST-001',
            'phone' => '081234567890',
            'reseller_id' => $resellerUser->id,
            'status' => 'active',
        ]);

        // Guest voucher buyer
        $guestCustomer = Customer::create([
            'name' => 'Guest 6281400390753',
            'code' => 'GST-YJ9PYC',
            'phone' => '6281400390753',
            'email' => '6281400390753@guest.local',
            'reseller_id' => $resellerUser->id,
            'status' => 'active',
        ]);

        Livewire::actingAs($resellerUser)
            ->test(\App\Livewire\ResellerPortal\Customer\Index::class)
            ->assertSee('Regular Customer')
            ->assertDontSee('GST-YJ9PYC')
            ->assertDontSee('Guest 6281400390753');
    }

    public function test_toggle_status_synchronizes_customer_service_and_pppoe_user(): void
    {
        $resellerRole = Role::firstOrCreate(['name' => 'reseller'], ['label' => 'Reseller']);
        $resellerUser = User::factory()->create(['email' => 'reseller2@test.local', 'is_active' => true]);
        $resellerUser->roles()->attach($resellerRole->id);

        $customer = Customer::create([
            'name' => 'Dedi Surya',
            'code' => '2026110401',
            'phone' => '081299999999',
            'reseller_id' => $resellerUser->id,
            'status' => 'active',
        ]);

        $profileType = \App\Models\ISP\ServiceProfileType::firstOrCreate(
            ['code' => 'PPPOE'],
            ['name' => 'PPPoE Profile', 'status' => 'active']
        );

        $profile = ServiceProfile::create([
            'code' => 'PKG-GOLD',
            'name' => 'Home Gold',
            'service_profile_type_id' => $profileType->id,
            'service_type' => 'pppoe',
            'base_price' => 150000,
            'price' => 150000,
            'status' => 'active',
        ]);

        $cs = CustomerService::create([
            'customer_id' => $customer->id,
            'service_profile_id' => $profile->id,
            'status' => 'active',
            'username' => 'Tes@dsbilling',
            'password' => 'secret123',
        ]);

        $pppoeUser = PPPoEUser::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'customer_service_id' => $cs->id,
            'service_profile_id' => $profile->id,
            'username' => 'Tes@dsbilling',
            'password' => 'secret123',
            'status' => 'active',
            'reseller_id' => $resellerUser->id,
        ]);

        // Toggle to suspend
        Livewire::actingAs($resellerUser)
            ->test(\App\Livewire\ResellerPortal\Customer\Index::class)
            ->call('toggleStatus', $customer->id);

        $customer->refresh();
        $cs->refresh();
        $pppoeUser->refresh();

        $this->assertEquals('suspend', $customer->status);
        $this->assertEquals('suspended', $cs->status);
        $this->assertEquals('suspended', $pppoeUser->status);

        // Toggle back to active
        Livewire::actingAs($resellerUser)
            ->test(\App\Livewire\ResellerPortal\Customer\Index::class)
            ->call('toggleStatus', $customer->id);

        $customer->refresh();
        $cs->refresh();
        $pppoeUser->refresh();

        $this->assertEquals('active', $customer->status);
        $this->assertEquals('active', $cs->status);
        $this->assertEquals('active', $pppoeUser->status);
    }

    public function test_can_edit_isolir_date_and_apply_janji_bayar(): void
    {
        $resellerRole = Role::firstOrCreate(['name' => 'reseller'], ['label' => 'Reseller']);
        $resellerUser = User::factory()->create(['email' => 'reseller3@test.local', 'is_active' => true]);
        $resellerUser->roles()->attach($resellerRole->id);

        $customer = Customer::create([
            'name' => 'Dedi Surya',
            'code' => '2026110403',
            'phone' => '081288888888',
            'reseller_id' => $resellerUser->id,
            'status' => 'active',
        ]);

        $profileType = \App\Models\ISP\ServiceProfileType::firstOrCreate(
            ['code' => 'PPPOE'],
            ['name' => 'PPPoE Profile', 'status' => 'active']
        );

        $profile = ServiceProfile::create([
            'code' => 'PKG-GOLD-2',
            'name' => 'Home Gold',
            'service_profile_type_id' => $profileType->id,
            'service_type' => 'pppoe',
            'base_price' => 150000,
            'price' => 150000,
            'status' => 'active',
        ]);

        $cs = CustomerService::create([
            'customer_id' => $customer->id,
            'service_profile_id' => $profile->id,
            'status' => 'active',
            'username' => 'Tes2@dsbilling',
            'password' => 'secret123',
        ]);

        $pppoeUser = PPPoEUser::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'customer_service_id' => $cs->id,
            'service_profile_id' => $profile->id,
            'username' => 'Tes2@dsbilling',
            'password' => 'secret123',
            'status' => 'active',
            'reseller_id' => $resellerUser->id,
        ]);

        // 1. Test set isolir date manual
        Livewire::actingAs($resellerUser)
            ->test(\App\Livewire\ResellerPortal\Customer\Index::class)
            ->call('openEditIsolirModal', $customer->id)
            ->set('editIsolirDate', '2026-11-20')
            ->call('saveIsolirDate')
            ->assertHasNoErrors();

        $cs->refresh();
        $this->assertEquals('suspended', $cs->status);
        $this->assertEquals('2026-11-20', $cs->suspended_at->format('Y-m-d'));

        // 2. Test apply Janji Bayar (Dispensasi)
        Livewire::actingAs($resellerUser)
            ->test(\App\Livewire\ResellerPortal\Customer\Index::class)
            ->call('openEditIsolirModal', $customer->id)
            ->set('graceDays', 5)
            ->call('applyJanjiBayar')
            ->assertHasNoErrors();

        $customer->refresh();
        $cs->refresh();
        $this->assertEquals('active', $customer->status);
        $this->assertEquals('active', $cs->status);
        $this->assertNull($cs->suspended_at);
    }
}
