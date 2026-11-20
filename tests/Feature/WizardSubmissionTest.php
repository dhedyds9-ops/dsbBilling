<?php

namespace Tests\Feature;

use App\Models\Master\Branch;
use App\Models\User;
use App\Models\ISP\Olt;
use App\Models\ISP\Vendor;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\ServiceProfileType;
use App\Livewire\Isp\Technician\Installation\Wizard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WizardSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_wizard_submits_new_customer_and_verifies_all_linkages(): void
    {
        $branch = Branch::create([
            'name' => 'Kantor Pusat',
            'code' => 'PST',
            'is_active' => true,
        ]);

        $admin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@winets.id',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $admin->attachRole('superadmin');

        $resellerRole = \App\Models\Role::firstOrCreate(['name' => 'reseller'], ['display_name' => 'Reseller']);
        $reseller = User::factory()->create([
            'name' => 'Dedi Suryadi (Reseller)',
            'email' => 'dedi@reseller.id',
            'whatsapp' => '081234567888',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $reseller->roles()->attach($resellerRole->id);

        $vendor = Vendor::create([
            'name' => 'ZTE',
            'code' => 'ZTE',
            'is_active' => true,
        ]);

        $olt = Olt::create([
            'code' => 'OLT-01',
            'name' => 'OLT C-DATA',
            'ip_address' => '192.168.100.2',
            'vendor_id' => $vendor->id,
            'branch_id' => $branch->id,
            'status' => 'active',
            'hardware_version' => 'C-DATA GPON',
        ]);

        $profileType = ServiceProfileType::firstOrCreate(
            ['code' => 'PPPOE'],
            ['name' => 'PPPoE Profile', 'status' => 'active']
        );

        $serviceProfile = ServiceProfile::create([
            'service_profile_type_id' => $profileType->id,
            'code' => 'HG-20M',
            'name' => 'Home Gold 20M',
            'price' => 250000,
            'service_type' => 'pppoe',
            'base_price' => 250000,
            'download_speed' => 20480,
            'upload_speed' => 10240,
            'is_active' => true,
        ]);

        \App\Models\Provisioning\NetworkProfile::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Default PPPoE Profile',
            'type' => 'pppoe',
            'vlan_id' => 200,
        ]);

        $this->actingAs($admin);

        $component = Livewire::test(Wizard::class)
            ->set('is_new_customer', true)
            ->set('new_customer_name', 'Sarminah')
            ->set('new_customer_phone', '625325232555')
            ->set('new_customer_address', 'Kp. Cisarap RT 01/02')
            ->set('new_customer_reseller_id', (string) $reseller->id)
            ->call('nextStep')
            ->call('selectPackage', $serviceProfile->id)
            ->call('nextStep') // to step 3 (ODP)
            ->call('nextStep') // to step 4 (OLT)
            ->set('olt_id', (string) $olt->id)
            ->set('pon_port', '1')
            ->call('nextStep') // to step 5 (ONU)
            ->set('onu_sn', 'ZTEGC1234567')
            ->set('onu_vendor_id', (string) $vendor->id)
            ->set('onu_model', 'F609v3')
            ->call('nextStep') // to step 6 (Review)
            ->call('submit');

        $component->assertHasNoErrors();

        // 1. Assert Customer exists and has reseller_id
        $customer = \App\Models\CRM\Customer::where('phone', '625325232555')->first();
        $this->assertNotNull($customer, 'Customer Sarminah should be created');
        $this->assertEquals('Sarminah', $customer->name);
        $this->assertEquals($reseller->id, $customer->reseller_id, 'Customer reseller_id should match chosen reseller');

        // 2. Assert CustomerService exists and is linked
        $customerServices = $customer->customerServices;
        $this->assertNotEmpty($customerServices, 'Customer should have customerServices');
        $svc = $customerServices->first();
        $this->assertEquals($serviceProfile->id, $svc->service_profile_id, 'CustomerService should be linked to selected ServiceProfile');
        $this->assertEquals($reseller->id, $svc->reseller_id, 'CustomerService reseller_id should match chosen reseller');

        // 3. Assert ONU exists and is linked
        $this->assertNotNull($svc->onu, 'CustomerService should be linked to Onu');
        $this->assertEquals('ZTEGC1234567', $svc->onu->serial_number);

        // 4. Assert PPPoE User exists
        $this->assertNotNull($svc->pppoeUser, 'CustomerService should have pppoeUser');
    }
}
