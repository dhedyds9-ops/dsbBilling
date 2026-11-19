<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\NOC\Onu\Index as OnuIndex;
use App\Livewire\ACS\Device\Index as AcsDeviceIndex;
use App\Models\ACS\ACSDevice;
use App\Models\CRM\Contract;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Onu;
use App\Models\Role;
use App\Models\ServiceCatalog\Service;
use App\Models\ServiceCatalog\ServiceCatalog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class OnuCustomerUnlinkSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function createContractAndService(Customer $customer, User $user): array
    {
        $contract = Contract::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'contract_number' => 'CTR-' . uniqid(),
            'status' => 'active',
            'start_date' => now(),
        ]);

        $catalog = ServiceCatalog::firstOrCreate(
            ['name' => 'Katalog Test'],
            ['uuid' => (string) Str::uuid(), 'is_active' => true, 'created_by' => $user->id, 'updated_by' => $user->id]
        );

        $service = Service::firstOrCreate(
            ['name' => 'Service Test'],
            ['uuid' => (string) Str::uuid(), 'service_catalog_id' => $catalog->id, 'price' => 100000, 'type' => 'pppoe', 'is_active' => true, 'created_by' => $user->id, 'updated_by' => $user->id]
        );

        return [$contract, $service];
    }

    public function test_unlinking_onu_synchronizes_customer_service_and_acs_device(): void
    {
        Role::firstOrCreate(['name' => UserRole::Administrator->value], ['display_name' => 'Administrator']);

        $admin = User::factory()->create([
            'email' => 'admin_onu_unlink@example.com',
            'is_active' => true,
        ]);
        $admin->attachRole(UserRole::Administrator->value);
        $this->actingAs($admin);

        $customer = Customer::create([
            'name' => 'Hendra Test',
            'phone' => '08123456789',
            'status' => 'active',
        ]);

        [$contract, $catalogService] = $this->createContractAndService($customer, $admin);

        $onu = Onu::create([
            'code' => 'ONU-TEST-01',
            'name' => 'ONU Hendra',
            'serial_number' => 'FHTTC19C20F1',
            'mac_address' => '64:9C:F3:84:09:28',
            'status' => 'active',
        ]);

        $service = CustomerService::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'contract_id' => $contract->id,
            'service_id' => $catalogService->id,
            'onu_id' => $onu->id,
            'username' => 'hendra_test',
            'password' => 'secret',
            'status' => 'active',
        ]);

        $acsDevice = ACSDevice::create([
            'uuid' => (string) Str::uuid(),
            'serial_number' => 'FHTTC19C20F1',
            'mac_address' => '64:9C:F3:84:09:28',
            'onu_id' => $onu->id,
            'customer_service_id' => $service->id,
            'status' => 'online',
        ]);

        // Assert awal: terhubung
        $this->assertEquals($customer->id, $acsDevice->resolved_customer?->id);

        // Aksi: Unlink customer via OnuIndex
        Livewire::test(OnuIndex::class)
            ->set('editingOnuId', $onu->id)
            ->set('selectedCustomerId', '')
            ->call('assignCustomer');

        // Assert 1: customer_service_id di ONU putus
        $service->refresh();
        $this->assertNull($service->onu_id);

        // Assert 2: customer_service_id di ACSDevice putus
        $acsDevice->refresh();
        $this->assertNull($acsDevice->customer_service_id);
        $this->assertNull($acsDevice->resolved_customer);

        // Assert 3: Customer tidak lagi mengklaim perangkat
        $customer->refresh();
        $this->assertNull($customer->onu_device);
    }

    public function test_save_device_data_clears_customer_service_id_if_onu_is_unlinked(): void
    {
        Role::firstOrCreate(['name' => UserRole::Administrator->value], ['display_name' => 'Administrator']);

        $admin = User::factory()->create([
            'email' => 'admin_acs_test@example.com',
            'is_active' => true,
        ]);
        $admin->attachRole(UserRole::Administrator->value);
        $this->actingAs($admin);

        $customer = Customer::create([
            'name' => 'Old Customer',
            'phone' => '0899999999',
            'status' => 'active',
        ]);

        [$contract, $catalogService] = $this->createContractAndService($customer, $admin);

        $service = CustomerService::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'contract_id' => $contract->id,
            'service_id' => $catalogService->id,
            'onu_id' => null, // already unlinked
            'username' => 'old_cust',
            'password' => 'secret',
            'status' => 'active',
        ]);

        $onu = Onu::create([
            'code' => 'ONU-TEST-02',
            'name' => 'ONU Unlinked',
            'serial_number' => 'FHTTC19C20F2',
            'mac_address' => '64:9C:F3:84:09:29',
            'status' => 'active',
        ]);

        $acsDevice = ACSDevice::create([
            'uuid' => 'fake-uuid-1234',
            'serial_number' => 'FHTTC19C20F2',
            'mac_address' => '64:9C:F3:84:09:29',
            'onu_id' => $onu->id,
            'customer_service_id' => $service->id, // stale customer service ID
            'status' => 'online',
        ]);

        $component = new AcsDeviceIndex();
        $component->saveDeviceData([
            '_id' => 'fake-uuid-1234',
            '_deviceId' => [
                '_SerialNumber' => 'FHTTC19C20F2',
                '_ProductClass' => 'HG6045F3',
            ],
            '_lastInform' => now()->toISOString(),
        ]);

        $acsDevice->refresh();
        // Pastikan stale customer_service_id di-nullkan
        $this->assertNull($acsDevice->customer_service_id);
    }
}
