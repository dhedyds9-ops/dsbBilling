<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\ServiceProfile;
use App\Models\Master\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PsbWizardBranchIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \App\Models\Role::firstOrCreate(['name' => UserRole::Manager->value], ['display_name' => 'Manager']);
        \App\Models\Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Technician']);
    }

    public function test_technician_can_create_hotspot_user_with_branch_scope(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang 7',
            'code' => 'CB7',
            'is_active' => true,
        ]);

        $tech = User::factory()->create([
            'job_function' => 'TECHNICIAN',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $tech->attachRole(UserRole::Manager->value);

        $customer = Customer::create([
            'code' => 'CUST-001',
            'name' => 'Pelanggan Hotspot',
            'phone' => '081234567890',
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $profileType = \App\Models\ISP\ServiceProfileType::firstOrCreate(
            ['code' => 'HOTSPOT'],
            ['name' => 'Hotspot Profile', 'status' => 'active']
        );

        $serviceProfile = ServiceProfile::create([
            'service_profile_type_id' => $profileType->id,
            'code' => 'HS-10M',
            'name' => 'Paket Hotspot 10M',
            'price' => 100000,
            'service_type' => 'hotspot',
            'base_price' => 100000,
            'download_speed' => 10240,
            'upload_speed' => 5120,
            'is_active' => true,
        ]);

        $this->actingAs($tech);

        $hotspotUser = HotspotUser::create([
            'uuid' => (string) Str::uuid(),
            'username' => 'RIAH_TEST',
            'password' => 'secret123',
            'service_profile_id' => $serviceProfile->id,
            'status' => 'pending',
            'mac_address' => 'FHTTC253EF19',
        ]);

        $this->assertDatabaseHas('hotspot_users', [
            'id' => $hotspotUser->id,
            'username' => 'RIAH_TEST',
            'branch_id' => $branch->id,
        ]);
    }

    public function test_technician_can_create_pppoe_user_with_branch_scope(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang 7',
            'code' => 'CB7',
            'is_active' => true,
        ]);

        $tech = User::factory()->create([
            'job_function' => 'TECHNICIAN',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $tech->attachRole(UserRole::Manager->value);

        $profileType = \App\Models\ISP\ServiceProfileType::firstOrCreate(
            ['code' => 'PPPOE'],
            ['name' => 'PPPoE Profile', 'status' => 'active']
        );

        $serviceProfile = ServiceProfile::create([
            'service_profile_type_id' => $profileType->id,
            'code' => 'PPP-20M',
            'name' => 'Paket PPPoE 20M',
            'price' => 200000,
            'service_type' => 'pppoe',
            'base_price' => 200000,
            'download_speed' => 20480,
            'upload_speed' => 10240,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'code' => 'CUST-002',
            'name' => 'Pelanggan PPPoE',
            'phone' => '081234567891',
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $contract = \App\Models\CRM\Contract::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'contract_number' => 'CTR-002',
            'start_date' => now(),
            'status' => 'active',
        ]);

        $catalog = \App\Models\ServiceCatalog\ServiceCatalog::firstOrCreate(
            ['name' => 'Katalog Internet'],
            ['uuid' => (string) Str::uuid(), 'is_active' => true]
        );

        $service = \App\Models\ServiceCatalog\Service::firstOrCreate(
            ['name' => 'Internet Service'],
            [
                'uuid' => (string) Str::uuid(),
                'service_catalog_id' => $catalog->id,
                'type' => 'pppoe',
                'price' => 200000,
                'is_active' => true,
            ]
        );

        $customerService = CustomerService::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'contract_id' => $contract->id,
            'service_id' => $service->id,
            'service_profile_id' => $serviceProfile->id,
            'username' => 'pppoe_user_test',
            'password' => 'secret123',
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $this->actingAs($tech);

        $pppoeUser = PPPoEUser::create([
            'uuid' => (string) Str::uuid(),
            'username' => 'pppoe_user_test',
            'password' => 'secret123',
            'customer_service_id' => $customerService->id,
            'service_profile_id' => $serviceProfile->id,
            'status' => 'pending',
            'mac_address' => 'AABBCCDDEEFF',
        ]);

        $this->assertDatabaseHas('pppoe_users', [
            'id' => $pppoeUser->id,
            'username' => 'pppoe_user_test',
            'branch_id' => $branch->id,
        ]);
    }

    public function test_has_branch_scope_safely_prevents_sql_error_when_table_has_no_branch_id_column(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang 7',
            'code' => 'CB7',
            'is_active' => true,
        ]);

        $tech = User::factory()->create([
            'job_function' => 'TECHNICIAN',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $tech->attachRole(UserRole::Manager->value);

        $this->actingAs($tech);

        $profileType = \App\Models\ISP\ServiceProfileType::firstOrCreate(
            ['code' => 'HOTSPOT'],
            ['name' => 'Hotspot Profile', 'status' => 'active']
        );

        $serviceProfile = ServiceProfile::create([
            'service_profile_type_id' => $profileType->id,
            'code' => 'HS-VCH',
            'name' => 'Paket Hotspot VCH',
            'price' => 10000,
            'service_type' => 'hotspot',
            'base_price' => 10000,
            'download_speed' => 10240,
            'upload_speed' => 5120,
            'is_active' => true,
        ]);

        $pool = \App\Models\ISP\VoucherPool::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Pool 10K',
            'service_profile_id' => $serviceProfile->id,
            'prefix' => 'VCH',
            'is_active' => true,
        ]);

        // Voucher uses HasBranchScope, but vouchers table does not have branch_id column
        $voucher = \App\Models\ISP\Voucher::create([
            'uuid' => (string) Str::uuid(),
            'code' => 'VCH-TEST-12345',
            'voucher_pool_id' => $pool->id,
            'status' => 'unused',
        ]);

        $this->assertDatabaseHas('vouchers', [
            'id' => $voucher->id,
            'code' => 'VCH-TEST-12345',
        ]);
    }

    public function test_provisioning_service_activate_hotspot_service_succeeds_for_technician(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang 7',
            'code' => 'CB7',
            'is_active' => true,
        ]);

        $tech = User::factory()->create([
            'job_function' => 'TECHNICIAN',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $tech->attachRole(UserRole::Manager->value);

        $customer = Customer::create([
            'code' => 'CUST-395',
            'name' => 'RIAH',
            'phone' => '081234567899',
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $profileType = \App\Models\ISP\ServiceProfileType::firstOrCreate(
            ['code' => 'HOTSPOT'],
            ['name' => 'Hotspot Profile', 'status' => 'active']
        );

        $serviceProfile = ServiceProfile::create([
            'service_profile_type_id' => $profileType->id,
            'code' => 'HS-6',
            'name' => 'Paket Hotspot 6',
            'price' => 50000,
            'service_type' => 'hotspot',
            'base_price' => 50000,
            'download_speed' => 10240,
            'upload_speed' => 5120,
            'is_active' => true,
        ]);

        $this->actingAs($tech);

        $provisioningService = app(\App\Services\Provisioning\ProvisioningService::class);

        $data = [
            'customer_id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'service_profile_id' => $serviceProfile->id,
            'username' => 'RIAH',
            'password' => 'Riah0101',
            'status' => 'pending',
            'branch_id' => $branch->id,
            'mac_address' => 'FHTTC253EF19',
        ];

        $result = $provisioningService->activateHotspotService($data, $tech->id);

        $this->assertNotNull($result);
        $this->assertDatabaseHas('hotspot_users', [
            'username' => 'RIAH',
            'mac_address' => 'FHTTC253EF19',
            'branch_id' => $branch->id,
        ]);
    }

    public function test_wizard_resolves_genieacs_server_url_dynamically_from_settings(): void
    {
        // 1. When connection.acs has explicit acs_url
        \App\Models\Setting::setValue('connection.acs', [
            'base_url' => 'http://103.10.10.5:7557',
            'acs_url' => 'http://103.10.10.5:7547',
            'tr069_port' => 7547,
        ]);
        $url1 = \App\Livewire\Isp\Technician\Installation\Wizard::resolveAcsServerUrl();
        $this->assertEquals('http://103.10.10.5:7547', $url1);

        // 2. When connection.acs has only base_url without acs_url
        \App\Models\Setting::setValue('connection.acs', [
            'base_url' => 'http://genieacs.mstore.id:7557',
            'tr069_port' => 7547,
        ]);
        $url2 = \App\Livewire\Isp\Technician\Installation\Wizard::resolveAcsServerUrl();
        $this->assertEquals('http://genieacs.mstore.id:7547', $url2);
    }

    public function test_installation_and_work_order_respect_branch_and_reseller_scope(): void
    {
        $branchA = Branch::create(['name' => 'Cabang A', 'code' => 'CBA', 'is_active' => true]);
        $branchB = Branch::create(['name' => 'Cabang B', 'code' => 'CBB', 'is_active' => true]);

        $mgrA = User::factory()->create([
            'branch_id' => $branchA->id,
            'is_active' => true,
        ]);
        $mgrA->attachRole(UserRole::Manager->value);

        $reseller = User::factory()->create([
            'is_active' => true,
        ]);
        $reseller->attachRole(UserRole::Reseller->value);

        $reseller2 = User::factory()->create([
            'is_active' => true,
        ]);
        $reseller2->attachRole(UserRole::Reseller->value);

        // Create Installation & WorkOrder under Branch A
        $instA = \App\Models\CRM\Installation::create([
            'customer_name' => 'Inst A',
            'branch_id' => $branchA->id,
            'reseller_id' => $reseller->id,
            'status' => 'pending',
        ]);
        $woA = \App\Models\Workforce\WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'WO A',
            'branch_id' => $branchA->id,
            'reseller_id' => $reseller->id,
            'status' => 'pending',
        ]);

        // Create Installation & WorkOrder under Branch B (different reseller)
        $instB = \App\Models\CRM\Installation::create([
            'customer_name' => 'Inst B',
            'branch_id' => $branchB->id,
            'reseller_id' => $reseller2->id,
            'status' => 'pending',
        ]);
        $woB = \App\Models\Workforce\WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'WO B',
            'branch_id' => $branchB->id,
            'reseller_id' => $reseller2->id,
            'status' => 'pending',
        ]);

        // 1. Manager of Branch A should see only Branch A records
        $this->actingAs($mgrA);
        $this->assertCount(1, \App\Models\CRM\Installation::all());
        $this->assertEquals($instA->id, \App\Models\CRM\Installation::first()->id);

        $this->assertCount(1, \App\Models\Workforce\WorkOrder::all());
        $this->assertEquals($woA->id, \App\Models\Workforce\WorkOrder::first()->id);

        // 2. Reseller should see only records matching their reseller_id
        $this->actingAs($reseller);
        $this->assertCount(1, \App\Models\CRM\Installation::all());
        $this->assertEquals($instA->id, \App\Models\CRM\Installation::first()->id);

        $this->assertCount(1, \App\Models\Workforce\WorkOrder::all());
        $this->assertEquals($woA->id, \App\Models\Workforce\WorkOrder::first()->id);
    }
}
