<?php

namespace Tests\Feature;

use App\Models\Inventory\Item;
use App\Models\Inventory\ItemSerial;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ItemPasangTest extends TestCase
{
    use RefreshDatabase;

    public function test_open_assign_customer_modal(): void
    {
        $role = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->roles()->attach($role->id);
        $this->actingAs($user);

        $item = Item::create([
            'name' => 'ONT Huawei',
            'sku' => 'ONT-HUA',
            'category' => 'ONU/ONT',
            'type' => 'serialized',
            'unit' => 'unit',
        ]);

        $serial = ItemSerial::create([
            'item_id' => $item->id,
            'serial_number' => 'HWTC12345678',
            'status' => 'in_warehouse',
            'condition' => 'good',
        ]);

        Livewire::test(\App\Livewire\Inventory\Item\Index::class)
            ->call('openAssignCustomerModal', $serial->id)
            ->assertSet('showAssignCustomerModal', true)
            ->assertSet('assigningSerialNumber', 'HWTC12345678');
    }

    public function test_open_assign_customer_modal_with_existing_onu_and_service(): void
    {
        $role = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->roles()->attach($role->id);
        $this->actingAs($user);

        $item = Item::create([
            'name' => 'ONT Huawei',
            'sku' => 'ONT-HUA-2',
            'category' => 'ONU/ONT',
            'type' => 'serialized',
            'unit' => 'unit',
        ]);

        $serial = ItemSerial::create([
            'item_id' => $item->id,
            'serial_number' => 'HWTC8278B19A',
            'status' => 'in_warehouse',
            'condition' => 'good',
        ]);

        $olt = \App\Models\ISP\Olt::create([
            'name' => 'CDATA-HOME',
            'code' => 'OLT-CDATA-01',
            'ip_address' => '10.10.10.1',
            'status' => 'online',
        ]);

        $onu = \App\Models\ISP\Onu::create([
            'name' => 'ONU Test',
            'olt_id' => $olt->id,
            'serial_number' => 'HWTC8278B19A',
            'status' => 'active',
        ]);

        $customer = \App\Models\CRM\Customer::create([
            'name' => 'Pelanggan Uji',
            'phone' => '081234567890',
        ]);

        $catalog = \App\Models\ServiceCatalog\ServiceCatalog::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Katalog Internet',
            'status' => 'active',
        ]);

        $svc = \App\Models\ServiceCatalog\Service::create([
            'service_catalog_id' => $catalog->id,
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Paket 20M',
            'code' => 'PKT-20M',
            'type' => 'internet',
            'price' => 150000,
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        $service = \App\Models\Customer\CustomerService::create([
            'customer_id' => $customer->id,
            'onu_id' => $onu->id,
            'service_id' => $svc->id,
            'status' => 'active',
        ]);

        Livewire::test(\App\Livewire\Inventory\Item\Index::class)
            ->call('openAssignCustomerModal', $serial->id)
            ->assertSet('showAssignCustomerModal', true)
            ->assertSet('assigningSerialNumber', 'HWTC8278B19A')
            ->assertSet('assignCustomerId', $customer->id)
            ->call('saveAssignCustomer')
            ->assertSet('showAssignCustomerModal', false);

        $this->assertEquals('installed', $serial->fresh()->status);
        $this->assertEquals($customer->id, $serial->fresh()->customer_id);
    }

    public function test_assign_customer_synchronizes_warehouse_stock(): void
    {
        $role = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->roles()->attach($role->id);
        $this->actingAs($user);

        $warehouse = \App\Models\Inventory\Warehouse::create([
            'name' => 'Gudang Pusat',
            'code' => 'GD-PUSAT-01',
            'is_active' => true,
        ]);

        $item = Item::create([
            'name' => 'ONT FiberHome',
            'sku' => 'ONT-FH-01',
            'category' => 'ONU/ONT',
            'type' => 'serialized',
            'unit' => 'unit',
        ]);

        $stock = \App\Models\Inventory\InventoryStock::create([
            'warehouse_id' => $warehouse->id,
            'item_id' => $item->id,
            'quantity' => 5,
        ]);

        $serial = ItemSerial::create([
            'item_id' => $item->id,
            'serial_number' => 'FHTT12345678',
            'warehouse_id' => $warehouse->id,
            'status' => 'in_warehouse',
            'condition' => 'good',
        ]);

        $customer = \App\Models\CRM\Customer::create([
            'name' => 'Dedi Customer',
            'code' => 'CUST-DEDI-01',
            'phone' => '087711223344',
        ]);

        Livewire::test(\App\Livewire\Inventory\Item\Index::class)
            ->call('openAssignCustomerModal', $serial->id)
            ->assertSet('showAssignCustomerModal', true)
            ->set('assignCustomerSearch', 'DEDI')
            ->set('assignCustomerId', $customer->id)
            ->call('saveAssignCustomer')
            ->assertSet('showAssignCustomerModal', false);

        $this->assertEquals('installed', $serial->fresh()->status);
        $this->assertNull($serial->fresh()->warehouse_id);
        $this->assertEquals($customer->id, $serial->fresh()->customer_id);
        $this->assertEquals(4.0, (float) $stock->fresh()->quantity);
    }
}
