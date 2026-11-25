<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Billing\Invoice;
use App\Models\Billing\Subscription;
use App\Models\CRM\Contract;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\Router;
use App\Models\ISP\ServiceProfile;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\ServiceCatalog\Service;
use App\Models\ServiceCatalog\ServiceCatalog;
use App\Models\User;
use App\Services\Billing\CustomerBillingDueDateService;
use App\Services\Provisioning\ProvisioningService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use App\Integration\MikroTik\Contracts\RouterOSDriverInterface;
use App\Integration\MikroTik\Services\RouterOSService;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerBillingDueDateTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch1;
    protected Branch $branch2;
    protected User $adminUser;
    protected User $resellerUser1;
    protected User $resellerUser2;
    protected User $unauthorizedUser;
    protected ServiceProfile $pppoeProfile;
    protected ServiceProfile $hotspotProfile;
    protected Router $router;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => UserRole::Administrator->value], ['display_name' => 'Administrator']);
        Role::firstOrCreate(['name' => UserRole::Manager->value], ['display_name' => 'Manager']);
        Role::firstOrCreate(['name' => UserRole::Reseller->value], ['display_name' => 'Reseller']);
        Role::firstOrCreate(['name' => UserRole::Teknisi->value], ['display_name' => 'Teknisi']);

        $this->branch1 = Branch::create(['name' => 'Cabang Barat', 'code' => 'BR-01']);
        $this->branch2 = Branch::create(['name' => 'Cabang Timur', 'code' => 'BR-02']);

        $this->adminUser = User::factory()->create([
            'name' => 'Administrator',
            'username' => 'admin_test_' . Str::random(4),
            'branch_id' => $this->branch1->id,
        ]);
        $this->adminUser->attachRole(UserRole::Administrator->value);

        $this->resellerUser1 = User::factory()->create([
            'name' => 'Reseller 1',
            'username' => 'reseller1_' . Str::random(4),
            'branch_id' => $this->branch1->id,
            'job_function' => 'RESELLER',
        ]);
        $this->resellerUser1->attachRole(UserRole::Reseller->value);

        $this->resellerUser2 = User::factory()->create([
            'name' => 'Reseller 2',
            'username' => 'reseller2_' . Str::random(4),
            'branch_id' => $this->branch2->id,
            'job_function' => 'RESELLER',
        ]);
        $this->resellerUser2->attachRole(UserRole::Reseller->value);

        $this->unauthorizedUser = User::factory()->create([
            'name' => 'Teknisi Non Billing',
            'username' => 'teknisi_' . Str::random(4),
            'branch_id' => $this->branch1->id,
            'job_function' => 'TECHNICIAN',
        ]);
        $this->unauthorizedUser->attachRole(UserRole::Teknisi->value);

        $catalog = ServiceCatalog::firstOrCreate(
            ['name' => 'Katalog Test'],
            ['uuid' => (string) Str::uuid(), 'description' => 'Test Catalog', 'is_active' => true]
        );
        Service::firstOrCreate(
            ['name' => 'Default Internet Service'],
            ['uuid' => (string) Str::uuid(), 'service_catalog_id' => $catalog->id, 'type' => 'pppoe', 'price' => 150000, 'is_active' => true]
        );

        $pppoeType = \App\Models\ISP\ServiceProfileType::firstOrCreate(
            ['code' => 'PPPOE'],
            ['name' => 'PPPoE Profile', 'status' => 'active']
        );
        $hotspotType = \App\Models\ISP\ServiceProfileType::firstOrCreate(
            ['code' => 'HOTSPOT'],
            ['name' => 'Hotspot Profile', 'status' => 'active']
        );

        $this->pppoeProfile = ServiceProfile::create([
            'service_profile_type_id' => $pppoeType->id,
            'code' => 'PKG-PPPOE-20M',
            'name' => 'Paket PPPoE 20M',
            'service_type' => 'pppoe',
            'price' => 200000,
            'base_price' => 200000,
            'download_speed' => 20,
            'upload_speed' => 20,
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->hotspotProfile = ServiceProfile::create([
            'service_profile_type_id' => $hotspotType->id,
            'code' => 'PKG-HOTSPOT-10M',
            'name' => 'Paket Hotspot 10M',
            'service_type' => 'hotspot',
            'price' => 100000,
            'base_price' => 100000,
            'download_speed' => 10,
            'upload_speed' => 10,
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->router = Router::create([
            'code' => 'RTR-01',
            'name' => 'Router Utama',
            'ip_address' => '10.0.0.1',
            'username' => 'admin',
            'password' => 'secret',
            'status' => 'active',
        ]);

        $this->mockRouterSuccess();
    }

    protected function mockRouterSuccess(): void
    {
        $mockDriver = $this->createMock(RouterOSDriverInterface::class);
        $mockDriver->method('connect')->willReturn(true);
        $mockDriver->method('addPPPoESecret')->willReturn(true);
        $mockDriver->method('setPppSecretProfile')->willReturn(true);
        $mockDriver->method('enablePppSecret')->willReturn(true);
        $mockDriver->method('disconnectPppoeUser')->willReturn(true);

        $mockRouterOs = $this->createMock(RouterOSService::class);
        $mockRouterOs->method('getDriver')->willReturn($mockDriver);
        $this->app->instance(RouterOSService::class, $mockRouterOs);
    }

    /**
     * 1. Create PPPoE dengan tanggal jatuh tempo.
     */
    public function test_01_create_pppoe_with_due_date(): void
    {
        $this->actingAs($this->adminUser);

        $chosenDueDate = now()->addDays(14)->format('Y-m-d');

        Livewire::test(\App\Livewire\Isp\PPPoEUser\Create::class)
            ->set('name', 'Budi PPPoE')
            ->set('phone', '081234567891')
            ->set('username', 'budi_pppoe_' . Str::random(3))
            ->set('password', 'password123')
            ->set('service_profile_id', $this->pppoeProfile->id)
            ->set('router_id', $this->router->id)
            ->set('due_date', $chosenDueDate)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $cs = CustomerService::where('username', 'LIKE', 'budi_pppoe_%')->first();
        $this->assertNotNull($cs);
        $this->assertEquals($chosenDueDate, $cs->due_date);

        $subscription = Subscription::where('customer_service_id', $cs->id)->first();
        $this->assertNotNull($subscription);
        $this->assertEquals($chosenDueDate, $subscription->next_billing_date->format('Y-m-d'));

        $invoice = Invoice::where('customer_id', $cs->customer_id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals($chosenDueDate, $invoice->due_date->format('Y-m-d'));
    }

    /**
     * 2. Edit tanggal jatuh tempo PPPoE.
     */
    public function test_02_edit_pppoe_due_date(): void
    {
        $this->actingAs($this->adminUser);

        $initialDueDate = now()->addDays(5)->format('Y-m-d');
        $provisioning = app(ProvisioningService::class);
        $res = $provisioning->activatePPPoEService([
            'name' => 'Agus PPPoE',
            'phone' => '081234567892',
            'username' => 'agus_pppoe_' . Str::random(3),
            'password' => 'secret123',
            'service_profile_id' => $this->pppoeProfile->id,
            'router_id' => $this->router->id,
            'due_date' => $initialDueDate,
            'status' => 'active',
            'billing_cycle' => 'monthly',
        ], $this->adminUser->id);

        $pppoeUser = $res['pppoe_user'];
        $newDueDate = now()->addDays(20)->format('Y-m-d');

        Livewire::test(\App\Livewire\Isp\PPPoEUser\Edit::class, ['id' => $pppoeUser->id])
            ->set('due_date', $newDueDate)
            ->set('due_date_reason', 'Customer requested payday date change')
            ->call('updateService')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $cs = $pppoeUser->customerService->fresh();
        $this->assertEquals($newDueDate, $cs->due_date);

        $sub = Subscription::where('customer_service_id', $cs->id)->first();
        $this->assertEquals($newDueDate, $sub->next_billing_date->format('Y-m-d'));

        $unpaidInv = Invoice::where('customer_id', $cs->customer_id)->where('status', 'unpaid')->first();
        $this->assertEquals($newDueDate, $unpaidInv->due_date->format('Y-m-d'));

        // Assert Audit Log
        $audit = AuditLog::where('auditable_type', CustomerService::class)
            ->where('auditable_id', $cs->id)
            ->where('event', 'due_date_updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($audit);
        $this->assertEquals($initialDueDate, $audit->old_values['due_date']);
        $this->assertEquals($newDueDate, $audit->new_values['due_date']);
        $this->assertEquals('Customer requested payday date change', $audit->notes);
    }

    /**
     * 3. Edit dari Detail PPPoE.
     */
    public function test_03_edit_due_date_from_pppoe_detail(): void
    {
        $this->actingAs($this->adminUser);

        $provisioning = app(ProvisioningService::class);
        $res = $provisioning->activatePPPoEService([
            'name' => 'Charlie PPPoE',
            'phone' => '081234567893',
            'username' => 'charlie_' . Str::random(3),
            'password' => 'secret123',
            'service_profile_id' => $this->pppoeProfile->id,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'active',
            'billing_cycle' => 'monthly',
        ], $this->adminUser->id);

        $pppoeUser = $res['pppoe_user'];
        $newDueDate = now()->addDays(25)->format('Y-m-d');

        Livewire::test(\App\Livewire\Isp\PPPoEUser\Show::class, ['id' => $pppoeUser->id])
            ->call('openEditDueDateModal')
            ->assertSet('showEditDueDateModal', true)
            ->set('edit_due_date', $newDueDate)
            ->set('edit_due_date_reason', 'Diubah dari modal detail PPPoE')
            ->call('saveDueDate')
            ->assertHasNoErrors()
            ->assertSet('showEditDueDateModal', false)
            ->assertDispatched('toast');

        $cs = $pppoeUser->customerService->fresh();
        $this->assertEquals($newDueDate, $cs->due_date);
    }

    /**
     * 4. Create Hotspot dengan tanggal jatuh tempo.
     */
    public function test_04_create_hotspot_with_due_date(): void
    {
        $this->actingAs($this->adminUser);

        $chosenDueDate = now()->addDays(10)->format('Y-m-d');

        Livewire::test(\App\Livewire\Isp\HotspotUser\Create::class)
            ->set('name', 'Dewi Hotspot')
            ->set('phone', '081234567894')
            ->set('username', 'dewi_hs_' . Str::random(3))
            ->set('password', 'password123')
            ->set('service_profile_id', $this->hotspotProfile->id)
            ->set('router_id', $this->router->id)
            ->set('due_date', $chosenDueDate)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $cs = CustomerService::where('username', 'LIKE', 'dewi_hs_%')->first();
        $this->assertNotNull($cs);
        $this->assertEquals($chosenDueDate, $cs->due_date);

        $sub = Subscription::where('customer_service_id', $cs->id)->first();
        $this->assertNotNull($sub);
        $this->assertEquals($chosenDueDate, $sub->next_billing_date->format('Y-m-d'));

        $inv = Invoice::where('customer_id', $cs->customer_id)->first();
        $this->assertNotNull($inv);
        $this->assertEquals($chosenDueDate, $inv->due_date->format('Y-m-d'));
    }

    /**
     * 5. Edit tanggal jatuh tempo Hotspot.
     */
    public function test_05_edit_hotspot_due_date(): void
    {
        $this->actingAs($this->adminUser);

        $initialDueDate = now()->addDays(4)->format('Y-m-d');
        $provisioning = app(ProvisioningService::class);
        $res = $provisioning->activateHotspotService([
            'name' => 'Eko Hotspot',
            'phone' => '081234567895',
            'username' => 'eko_hs_' . Str::random(3),
            'password' => 'secret123',
            'service_profile_id' => $this->hotspotProfile->id,
            'router_id' => $this->router->id,
            'due_date' => $initialDueDate,
            'status' => 'active',
            'billing_cycle' => 'monthly',
        ], $this->adminUser->id);

        $hotspotUser = $res['hotspot_user'];
        $newDueDate = now()->addDays(18)->format('Y-m-d');

        Livewire::test(\App\Livewire\Isp\HotspotUser\Edit::class, ['id' => $hotspotUser->id])
            ->set('due_date', $newDueDate)
            ->set('due_date_reason', 'Perubahan jatuh tempo Hotspot')
            ->call('updateService')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $cs = $hotspotUser->customerService->fresh();
        $this->assertEquals($newDueDate, $cs->due_date);

        $sub = Subscription::where('customer_service_id', $cs->id)->first();
        $this->assertEquals($newDueDate, $sub->next_billing_date->format('Y-m-d'));
    }

    /**
     * 6. Edit dari Detail Hotspot.
     */
    public function test_06_edit_due_date_from_hotspot_detail(): void
    {
        $this->actingAs($this->adminUser);

        $provisioning = app(ProvisioningService::class);
        $res = $provisioning->activateHotspotService([
            'name' => 'Fajar Hotspot',
            'phone' => '081234567896',
            'username' => 'fajar_hs_' . Str::random(3),
            'password' => 'secret123',
            'service_profile_id' => $this->hotspotProfile->id,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'active',
            'billing_cycle' => 'monthly',
        ], $this->adminUser->id);

        $hotspotUser = $res['hotspot_user'];
        $newDueDate = now()->addDays(22)->format('Y-m-d');

        Livewire::test(\App\Livewire\Isp\HotspotUser\Show::class, ['id' => $hotspotUser->id])
            ->call('openEditDueDateModal')
            ->assertSet('showEditDueDateModal', true)
            ->set('edit_due_date', $newDueDate)
            ->set('edit_due_date_reason', 'Diubah via detail hotspot')
            ->call('saveDueDate')
            ->assertHasNoErrors()
            ->assertSet('showEditDueDateModal', false)
            ->assertDispatched('toast');

        $cs = $hotspotUser->customerService->fresh();
        $this->assertEquals($newDueDate, $cs->due_date);
    }

    /**
     * 7. Pelanggan memiliki beberapa layanan (independent due dates).
     */
    public function test_07_customer_with_multiple_services_has_independent_due_dates(): void
    {
        $this->actingAs($this->adminUser);

        $customer = Customer::create([
            'name' => 'Multi Service Customer',
            'phone' => '081234567897',
            'branch_id' => $this->branch1->id,
        ]);

        $contract = Contract::create([
            'customer_id' => $customer->id,
            'contract_number' => 'CTR-MULTI-01',
            'start_date' => now(),
            'status' => 'active',
        ]);

        $dueDateService = app(CustomerBillingDueDateService::class);

        // Service 1: PPPoE
        $cs1 = CustomerService::create([
            'customer_id' => $customer->id,
            'contract_id' => $contract->id,
            'service_profile_id' => $this->pppoeProfile->id,
            'username' => 'multi_pppoe',
            'password' => 'pass1',
            'status' => 'active',
            'branch_id' => $this->branch1->id,
            'attributes' => ['due_date' => '2026-10-10'],
        ]);
        Subscription::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'contract_id' => $contract->id,
            'customer_service_id' => $cs1->id,
            'next_billing_date' => Carbon::parse('2026-10-10'),
            'billing_cycle' => 'monthly',
            'recurring_price' => 200000,
        ]);

        // Service 2: Hotspot
        $cs2 = CustomerService::create([
            'customer_id' => $customer->id,
            'contract_id' => $contract->id,
            'service_profile_id' => $this->hotspotProfile->id,
            'username' => 'multi_hotspot',
            'password' => 'pass2',
            'status' => 'active',
            'branch_id' => $this->branch1->id,
            'attributes' => ['due_date' => '2026-10-20'],
        ]);
        Subscription::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'contract_id' => $contract->id,
            'customer_service_id' => $cs2->id,
            'next_billing_date' => Carbon::parse('2026-10-20'),
            'billing_cycle' => 'monthly',
            'recurring_price' => 100000,
        ]);

        // Update Service 1 due date to 2026-11-05
        $dueDateService->updateDueDate($cs1, '2026-11-05', 'Reschedule PPPoE only', $this->adminUser->id);

        $cs1Fresh = $cs1->fresh();
        $cs2Fresh = $cs2->fresh();

        $this->assertEquals('2026-11-05', $cs1Fresh->due_date);
        $this->assertEquals('2026-10-20', $cs2Fresh->due_date, 'Service 2 due date must remain untouched');

        $sub1 = Subscription::where('customer_service_id', $cs1->id)->first();
        $sub2 = Subscription::where('customer_service_id', $cs2->id)->first();

        $this->assertEquals('2026-11-05', $sub1->next_billing_date->format('Y-m-d'));
        $this->assertEquals('2026-10-20', $sub2->next_billing_date->format('Y-m-d'));
    }

    /**
     * 8. Invoice sudah dibayar tidak berubah (Paid invoice immutability).
     */
    public function test_08_paid_invoices_are_not_modified_when_due_date_changes(): void
    {
        $this->actingAs($this->adminUser);

        $customer = Customer::create([
            'name' => 'Paid Invoice Cust',
            'phone' => '081234567898',
            'branch_id' => $this->branch1->id,
        ]);
        $contract = Contract::create([
            'customer_id' => $customer->id,
            'contract_number' => 'CTR-PAID-01',
            'start_date' => now(),
            'status' => 'active',
        ]);
        $cs = CustomerService::create([
            'customer_id' => $customer->id,
            'contract_id' => $contract->id,
            'service_profile_id' => $this->pppoeProfile->id,
            'username' => 'paid_cust',
            'password' => 'secret',
            'status' => 'active',
            'branch_id' => $this->branch1->id,
            'attributes' => ['due_date' => '2026-09-10'],
        ]);

        $paidInvoice = Invoice::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'contract_id' => $contract->id,
            'invoice_number' => 'INV-PAID-001',
            'issue_date' => Carbon::parse('2026-09-01'),
            'due_date' => Carbon::parse('2026-09-10'),
            'total_amount' => 200000,
            'paid_amount' => 200000,
            'status' => 'paid',
            'branch_id' => $this->branch1->id,
        ]);

        $dueDateService = app(CustomerBillingDueDateService::class);
        $dueDateService->updateDueDate($cs, '2026-10-25', 'Pembaruan due date', $this->adminUser->id);

        $this->assertEquals(
            '2026-09-10',
            $paidInvoice->fresh()->due_date->format('Y-m-d'),
            'Paid invoice due date must NEVER be altered'
        );
    }

    /**
     * 9. Invoice belum dibayar mengikuti kebijakan yang dipilih (Unpaid invoice update).
     */
    public function test_09_unpaid_invoices_update_due_date(): void
    {
        $this->actingAs($this->adminUser);

        $customer = Customer::create([
            'name' => 'Unpaid Invoice Cust',
            'phone' => '081234567899',
            'branch_id' => $this->branch1->id,
        ]);
        $contract = Contract::create([
            'customer_id' => $customer->id,
            'contract_number' => 'CTR-UNPAID-01',
            'start_date' => now(),
            'status' => 'active',
        ]);
        $cs = CustomerService::create([
            'customer_id' => $customer->id,
            'contract_id' => $contract->id,
            'service_profile_id' => $this->pppoeProfile->id,
            'username' => 'unpaid_cust',
            'password' => 'secret',
            'status' => 'active',
            'branch_id' => $this->branch1->id,
            'attributes' => ['due_date' => '2026-10-01'],
        ]);

        $unpaidInvoice = Invoice::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'contract_id' => $contract->id,
            'invoice_number' => 'INV-UNPAID-001',
            'issue_date' => Carbon::parse('2026-10-01'),
            'due_date' => Carbon::parse('2026-10-08'),
            'total_amount' => 200000,
            'paid_amount' => 0,
            'status' => 'unpaid',
            'branch_id' => $this->branch1->id,
        ]);

        $dueDateService = app(CustomerBillingDueDateService::class);
        $res = $dueDateService->updateDueDate($cs, '2026-10-25', 'Follow payday policy', $this->adminUser->id);

        $this->assertEquals(1, $res['updated_invoices_count']);
        $this->assertEquals('2026-10-25', $unpaidInvoice->fresh()->due_date->format('Y-m-d'));
    }

    /**
     * 10. User tanpa permission ditolak.
     */
    public function test_10_user_without_billing_permission_is_rejected(): void
    {
        $customer = Customer::create([
            'name' => 'Protected Customer',
            'phone' => '081234567800',
            'branch_id' => $this->branch1->id,
        ]);
        $cs = CustomerService::create([
            'customer_id' => $customer->id,
            'service_profile_id' => $this->pppoeProfile->id,
            'username' => 'protected_cust',
            'password' => 'secret',
            'status' => 'active',
            'branch_id' => $this->branch1->id,
            'attributes' => ['due_date' => '2026-10-10'],
        ]);

        $dueDateService = app(CustomerBillingDueDateService::class);

        $this->expectException(AuthorizationException::class);
        $dueDateService->updateDueDate($cs, '2026-10-30', 'Unauthorized try', $this->unauthorizedUser->id);
    }

    /**
     * 11. Cross-tenant update ditolak (Branch / Reseller isolation).
     */
    public function test_11_cross_tenant_update_is_rejected(): void
    {
        // Customer belongs to Reseller 2 in Branch 2
        $customer = Customer::create([
            'name' => 'Branch 2 Customer',
            'phone' => '081234567801',
            'branch_id' => $this->branch2->id,
            'reseller_id' => $this->resellerUser2->id,
        ]);
        $cs = CustomerService::create([
            'customer_id' => $customer->id,
            'service_profile_id' => $this->pppoeProfile->id,
            'username' => 'branch2_cust',
            'password' => 'secret',
            'status' => 'active',
            'branch_id' => $this->branch2->id,
            'reseller_id' => $this->resellerUser2->id,
            'attributes' => ['due_date' => '2026-10-10'],
        ]);

        $dueDateService = app(CustomerBillingDueDateService::class);

        // Reseller 1 (Branch 1) attempts to update customer of Reseller 2 (Branch 2)
        $this->expectException(AuthorizationException::class);
        $dueDateService->updateDueDate($cs, '2026-10-30', 'Cross tenant attack', $this->resellerUser1->id);
    }

    /**
     * 12. Tidak ada duplikasi invoice setelah perubahan.
     */
    public function test_12_no_duplicate_invoices_generated_after_due_date_change(): void
    {
        $this->actingAs($this->adminUser);

        $provisioning = app(ProvisioningService::class);
        $res = $provisioning->activatePPPoEService([
            'name' => 'Single Invoice Cust',
            'phone' => '081234567802',
            'username' => 'single_inv_' . Str::random(3),
            'password' => 'secret123',
            'service_profile_id' => $this->pppoeProfile->id,
            'due_date' => '2026-10-15',
            'status' => 'active',
            'billing_cycle' => 'monthly',
        ], $this->adminUser->id);

        $cs = $res['customer_service'];
        $customerId = $cs->customer_id;

        $initialInvoiceCount = Invoice::where('customer_id', $customerId)->count();
        $this->assertEquals(1, $initialInvoiceCount);

        $dueDateService = app(CustomerBillingDueDateService::class);
        // Change due date 3 consecutive times
        $dueDateService->updateDueDate($cs, '2026-10-20', 'Change 1', $this->adminUser->id);
        $dueDateService->updateDueDate($cs, '2026-10-25', 'Change 2', $this->adminUser->id);
        $dueDateService->updateDueDate($cs, '2026-10-30', 'Change 3', $this->adminUser->id);

        $finalInvoiceCount = Invoice::where('customer_id', $customerId)->count();
        $this->assertEquals(
            1,
            $finalInvoiceCount,
            'Invoice count must remain strictly 1 with zero duplication'
        );

        $finalInvoice = Invoice::where('customer_id', $customerId)->first();
        $this->assertEquals('2026-10-30', $finalInvoice->due_date->format('Y-m-d'));
    }
}
