<?php

namespace Tests\Feature;

use App\Models\Master\Branch;
use App\Models\User;
use App\Models\ISP\Olt;
use App\Models\ISP\Vendor;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\ServiceProfileType;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Livewire\Isp\Technician\Installation\Wizard;
use App\Livewire\Crm\Customer\Index as CustomerIndex;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WizardSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;
    protected User $admin;
    protected User $reseller;
    protected Vendor $vendor;
    protected Olt $olt;
    protected ServiceProfile $pppoeProfile;
    protected ServiceProfile $hotspotProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Kantor Pusat',
            'code' => 'PST',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@winets.id',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->admin->attachRole('superadmin');

        $resellerRole = \App\Models\Role::firstOrCreate(['name' => 'reseller'], ['display_name' => 'Reseller']);
        $this->reseller = User::factory()->create([
            'name' => 'Dedi Suryadi (Reseller)',
            'email' => 'dedi@reseller.id',
            'whatsapp' => '081234567888',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->reseller->roles()->attach($resellerRole->id);

        $this->vendor = Vendor::create([
            'name' => 'ZTE',
            'code' => 'ZTE',
            'is_active' => true,
        ]);

        $this->olt = Olt::create([
            'code' => 'OLT-01',
            'name' => 'OLT C-DATA',
            'ip_address' => '192.168.100.2',
            'vendor_id' => $this->vendor->id,
            'branch_id' => $this->branch->id,
            'status' => 'active',
            'hardware_version' => 'C-DATA GPON',
        ]);

        $pppoeType = ServiceProfileType::firstOrCreate(
            ['code' => 'PPPOE'],
            ['name' => 'PPPoE Profile', 'status' => 'active']
        );

        $hotspotType = ServiceProfileType::firstOrCreate(
            ['code' => 'HOTSPOT'],
            ['name' => 'Hotspot Profile', 'status' => 'active']
        );

        $this->pppoeProfile = ServiceProfile::create([
            'service_profile_type_id' => $pppoeType->id,
            'code' => 'HG-20M',
            'name' => 'Home Gold 20M',
            'price' => 250000,
            'service_type' => 'pppoe',
            'base_price' => 250000,
            'download_speed' => 20480,
            'upload_speed' => 10240,
            'is_active' => true,
        ]);

        $this->hotspotProfile = ServiceProfile::create([
            'service_profile_type_id' => $hotspotType->id,
            'code' => 'HS-MEMBER',
            'name' => 'Member Hotspot Unlimited',
            'price' => 100000,
            'service_type' => 'hotspot',
            'base_price' => 100000,
            'download_speed' => 10240,
            'upload_speed' => 5120,
            'is_active' => true,
        ]);

        \App\Models\Provisioning\NetworkProfile::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Default PPPoE Profile',
            'type' => 'pppoe',
            'vlan_id' => 200,
        ]);

        \App\Models\Provisioning\NetworkProfile::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Default Hotspot Profile',
            'type' => 'hotspot',
            'vlan_id' => 300,
        ]);
    }

    public function test_wizard_submits_new_customer_and_verifies_all_linkages(): void
    {
        $this->actingAs($this->admin);

        $component = Livewire::test(Wizard::class)
            ->set('is_new_customer', true)
            ->set('new_customer_name', 'Sarminah')
            ->set('new_customer_phone', '625325232555')
            ->set('new_customer_address', 'Kp. Cisarap RT 01/02')
            ->set('new_customer_reseller_id', (string) $this->reseller->id)
            ->call('nextStep')
            ->call('selectPackage', $this->pppoeProfile->id)
            ->call('nextStep') // to step 3 (ODP)
            ->call('nextStep') // to step 4 (OLT)
            ->set('olt_id', (string) $this->olt->id)
            ->set('pon_port', '1')
            ->call('nextStep') // to step 5 (ONU)
            ->set('onu_sn', 'ZTEGC1234567')
            ->set('onu_vendor_id', (string) $this->vendor->id)
            ->set('onu_model', 'F609v3')
            ->call('nextStep') // to step 6 (Review)
            ->call('submit');

        $component->assertHasNoErrors();

        // 1. Assert Customer exists and has reseller_id and branch_id
        $customer = Customer::where('phone', '625325232555')->first();
        $this->assertNotNull($customer, 'Customer Sarminah should be created');
        $this->assertEquals('Sarminah', $customer->name);
        $this->assertEquals($this->reseller->id, $customer->reseller_id);
        $this->assertEquals($this->branch->id, $customer->branch_id);

        // 2. Assert CustomerService exists and is linked
        $customerServices = $customer->customerServices;
        $this->assertNotEmpty($customerServices, 'Customer should have customerServices');
        $svc = $customerServices->first();
        $this->assertEquals($this->pppoeProfile->id, $svc->service_profile_id);
        $this->assertEquals($this->reseller->id, $svc->reseller_id);
        $this->assertEquals($this->branch->id, $svc->branch_id);

        // 3. Assert ONU exists and is linked
        $this->assertNotNull($svc->onu, 'CustomerService should be linked to Onu');
        $this->assertEquals('ZTEGC1234567', $svc->onu->serial_number);
        $this->assertEquals($this->branch->id, $svc->onu->branch_id);

        // 4. Assert PPPoE User exists and has tenant tags
        $this->assertNotNull($svc->pppoeUser, 'CustomerService should have pppoeUser');
        $this->assertEquals($this->reseller->id, $svc->pppoeUser->reseller_id);
        $this->assertEquals($this->branch->id, $svc->pppoeUser->branch_id);

        // 5. Assert Invoice has reseller_id and branch_id
        $invoices = $customer->invoices;
        $this->assertNotEmpty($invoices, 'Invoice should be created');
        $invoice = $invoices->first();
        $this->assertEquals($this->reseller->id, $invoice->reseller_id);
        $this->assertEquals($this->branch->id, $invoice->branch_id);
    }

    public function test_wizard_submits_hotspot_customer_successfully(): void
    {
        $this->actingAs($this->admin);

        $component = Livewire::test(Wizard::class)
            ->set('is_new_customer', true)
            ->set('new_customer_name', 'Ahmad Hotspot')
            ->set('new_customer_phone', '628999888777')
            ->set('new_customer_address', 'Jl. Merdeka No. 10')
            ->set('new_customer_reseller_id', (string) $this->reseller->id)
            ->call('nextStep')
            ->set('service_type', 'hotspot')
            ->call('selectPackage', $this->hotspotProfile->id)
            ->call('nextStep')
            ->call('nextStep')
            ->set('olt_id', (string) $this->olt->id)
            ->set('pon_port', '1')
            ->call('nextStep')
            ->set('onu_sn', 'ZTEGCHS99999')
            ->set('onu_vendor_id', (string) $this->vendor->id)
            ->set('onu_model', 'F609v3')
            ->call('nextStep')
            ->call('submit');

        $component->assertHasNoErrors();

        $customer = Customer::where('phone', '628999888777')->first();
        $this->assertNotNull($customer);
        $svc = $customer->customerServices->first();
        $this->assertNotNull($svc);
        $this->assertEquals($this->hotspotProfile->id, $svc->service_profile_id);
        $this->assertNotNull($svc->hotspotUser);
    }

    public function test_wizard_direct_hq_customer_has_null_reseller_and_correct_branch(): void
    {
        $this->actingAs($this->admin);

        $component = Livewire::test(Wizard::class)
            ->set('is_new_customer', true)
            ->set('new_customer_name', 'Pelanggan Direct HQ')
            ->set('new_customer_phone', '628111222333')
            ->set('new_customer_address', 'Pusat Kota')
            ->set('new_customer_reseller_id', '') // Direct HQ
            ->call('nextStep')
            ->call('selectPackage', $this->pppoeProfile->id)
            ->call('nextStep')
            ->call('nextStep')
            ->set('olt_id', (string) $this->olt->id)
            ->set('pon_port', '1')
            ->call('nextStep')
            ->set('onu_sn', 'ZTEGCHQ00001')
            ->set('onu_vendor_id', (string) $this->vendor->id)
            ->set('onu_model', 'F609v3')
            ->call('nextStep')
            ->call('submit');

        $component->assertHasNoErrors();

        $customer = Customer::where('phone', '628111222333')->first();
        $this->assertNotNull($customer);
        $this->assertNull($customer->reseller_id, 'Direct HQ customer must have null reseller_id');
        $this->assertEquals($this->branch->id, $customer->branch_id);

        $svc = $customer->customerServices->first();
        $this->assertNotNull($svc);
        $this->assertNull($svc->reseller_id);
        $this->assertEquals($this->branch->id, $svc->branch_id);
    }

    public function test_repair_artisan_command_dry_run_and_execution(): void
    {
        // Create an orphaned customer (like Sarminah before fix)
        $orphan = Customer::create([
            'code' => '2026112001',
            'name' => 'Sarminah Orphan',
            'phone' => '625325232999',
            'address' => 'Desa Cisarap',
            'reseller_id' => $this->reseller->id,
            'branch_id' => $this->branch->id,
            'status' => 'active',
        ]);

        $this->assertEquals(0, $orphan->customerServices()->count());

        // 1. Dry Run test
        $this->artisan('dsbilling:repair-customer-provisioning', [
            'target' => $orphan->code,
            '--dry-run' => true,
        ])->assertSuccessful();

        $orphan->refresh();
        $this->assertEquals(0, $orphan->customerServices()->count(), 'Dry run must not create records');

        // 2. Live repair test
        $this->artisan('dsbilling:repair-customer-provisioning', [
            'target' => $orphan->code,
            '--package' => $this->pppoeProfile->code,
            '--sn' => 'ZTEGC99998888',
        ])->assertSuccessful();

        $orphan->refresh();
        $this->assertEquals(1, $orphan->customerServices()->count(), 'Live repair must create CustomerService');
        $svc = $orphan->customerServices->first();
        $this->assertEquals($this->pppoeProfile->id, $svc->service_profile_id);
        $this->assertEquals($this->reseller->id, $svc->reseller_id);
        $this->assertEquals($this->branch->id, $svc->branch_id);
        $this->assertNotNull($svc->pppoeUser);
        $this->assertNotNull($svc->onu);
        $this->assertEquals('ZTEGC99998888', $svc->onu->serial_number);
    }

    public function test_list_pelanggan_renders_accurate_service_package_and_reseller(): void
    {
        $this->actingAs($this->admin);

        // Create customer with active service
        $customer = Customer::create([
            'code' => '2026112002',
            'name' => 'Sarminah Valid',
            'phone' => '625325232777',
            'address' => 'Kp. Cisarap',
            'reseller_id' => $this->reseller->id,
            'branch_id' => $this->branch->id,
            'status' => 'active',
        ]);

        $contract = \App\Models\CRM\Contract::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'customer_id' => $customer->id,
            'contract_number' => 'CONTRACT-TEST-01',
            'start_date' => now(),
            'status' => 'active',
        ]);

        $onu = \App\Models\ISP\Onu::create([
            'code' => 'ONU-TEST01',
            'name' => 'ONU Sarminah Valid',
            'serial_number' => 'ZTEGC77778888',
            'status' => 'active',
            'branch_id' => $this->branch->id,
        ]);

        $svc = CustomerService::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'customer_id' => $customer->id,
            'contract_id' => $contract->id,
            'service_profile_id' => $this->pppoeProfile->id,
            'onu_id' => $onu->id,
            'username' => 'sarminah@winets',
            'password' => 'pppoe',
            'status' => 'active',
            'reseller_id' => $this->reseller->id,
            'branch_id' => $this->branch->id,
        ]);

        \App\Models\ISP\PPPoEUser::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'customer_service_id' => $svc->id,
            'service_profile_id' => $this->pppoeProfile->id,
            'username' => 'sarminah@winets',
            'password' => 'pppoe',
            'status' => 'active',
            'reseller_id' => $this->reseller->id,
            'branch_id' => $this->branch->id,
            'mac_address' => 'ZTEGC77778888',
        ]);

        // Render List Pelanggan Livewire Component
        $component = Livewire::test(CustomerIndex::class)
            ->set('search', 'Sarminah Valid');

        $component->assertSee('Sarminah Valid')
            ->assertSee('PPPoE')
            ->assertSee('Home Gold 20M')
            ->assertSee('Dedi Suryadi (Reseller)')
            ->assertSee('sarminah@winets');
    }
}
