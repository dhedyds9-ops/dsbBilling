<?php

namespace Tests\Feature;

use App\Models\Inventory\InventoryCustody;
use App\Models\Inventory\InventoryRequisition;
use App\Models\Inventory\InventoryRequisitionItem;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MobileInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected User $technician1;
    protected User $technician2;
    protected Branch $branch;
    protected Warehouse $warehouse;
    protected Item $itemOnt;
    protected Item $itemCable;

    protected function setUp(): void
    {
        parent::setUp();

        $mgrRole = Role::firstOrCreate(['name' => 'manager'], ['display_name' => 'Manager', 'guard_name' => 'web']);
        $techRole = Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Teknisi', 'guard_name' => 'web']);

        $this->branch = Branch::firstOrCreate(['code' => 'BR-INV'], ['name' => 'Cabang Gudang']);

        $this->warehouse = Warehouse::create([
            'uuid' => (string) Str::uuid(),
            'code' => 'WH-PST',
            'name' => 'Gudang Pusat Operasional',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $this->manager = User::factory()->create([
            'name' => 'Manager Gudang',
            'email' => 'mgr_gudang@dsbilling.test',
            'job_function' => 'MANAGER',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->manager->roles()->attach($mgrRole->id);

        $this->technician1 = User::factory()->create([
            'name' => 'Bambang Teknisi 1',
            'email' => 'bambang@dsbilling.test',
            'job_function' => 'TECHNICIAN',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->technician1->roles()->attach($techRole->id);

        $this->technician2 = User::factory()->create([
            'name' => 'Eko Teknisi 2',
            'email' => 'eko@dsbilling.test',
            'job_function' => 'TECHNICIAN',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->technician2->roles()->attach($techRole->id);

        $this->itemOnt = Item::create([
            'uuid' => (string) Str::uuid(),
            'sku' => 'ONT-ZTE-F663',
            'name' => 'ONT ZTE F663 Dual Band Gigabit',
            'category' => 'ONT',
            'unit' => 'unit',
            'is_serialized' => true,
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $this->itemCable = Item::create([
            'uuid' => (string) Str::uuid(),
            'sku' => 'CAB-DC-1C',
            'name' => 'Drop Core 1 Core 3 Seling (1000m)',
            'category' => 'Cable',
            'unit' => 'meter',
            'is_serialized' => false,
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
    }

    public function test_technician_can_view_my_stock_custody(): void
    {
        $token = $this->technician1->createToken('test-mobile-token')->plainTextToken;

        InventoryCustody::create([
            'uuid' => (string) Str::uuid(),
            'item_id' => $this->itemOnt->id,
            'holder_id' => $this->technician1->id,
            'holder_type' => 'technician',
            'quantity' => 5,
            'used_quantity' => 2,
            'returned_quantity' => 0,
            'status' => 'in_custody',
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/mobile/inventory/my-stock');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment(['item_name' => 'ONT ZTE F663 Dual Band Gigabit'])
            ->assertJsonFragment(['available_quantity' => 3]);
    }

    public function test_technician_can_list_warehouse_items_catalog(): void
    {
        $token = $this->technician1->createToken('test-mobile-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/mobile/inventory/items?search=Drop Core');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonFragment(['sku' => 'CAB-DC-1C']);
    }

    public function test_technician_can_create_material_requisition(): void
    {
        $token = $this->technician1->createToken('test-mobile-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/mobile/inventory/requisitions', [
                'items' => [
                    ['item_id' => $this->itemOnt->id, 'quantity' => 3, 'notes' => 'Pasang baru blok B'],
                    ['item_id' => $this->itemCable->id, 'quantity' => 300, 'notes' => 'Tarik jalur ODP'],
                ],
                'notes' => 'Permintaan material tim 1 pagi',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.items_count', 2);

        $this->assertDatabaseHas('inventory_requisitions', [
            'requester_id' => $this->technician1->id,
            'status' => 'pending',
        ]);
    }

    public function test_manager_can_approve_requisition_and_grant_custody(): void
    {
        $token = $this->manager->createToken('test-mobile-token')->plainTextToken;

        $requisition = InventoryRequisition::create([
            'uuid' => (string) Str::uuid(),
            'requester_id' => $this->technician1->id,
            'requester_type' => 'technician',
            'warehouse_id' => $this->warehouse->id,
            'branch_id' => $this->branch->id,
            'status' => 'pending',
        ]);

        InventoryRequisitionItem::create([
            'requisition_id' => $requisition->id,
            'item_id' => $this->itemOnt->id,
            'quantity_requested' => 4,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/mobile/inventory/requisitions/{$requisition->id}/approve");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('inventory_requisitions', [
            'id' => $requisition->id,
            'status' => 'approved',
            'approved_by' => $this->manager->id,
        ]);

        // Verifies custody is automatically granted to technician1
        $this->assertDatabaseHas('inventory_custodies', [
            'holder_id' => $this->technician1->id,
            'item_id' => $this->itemOnt->id,
            'quantity' => 4,
            'status' => 'in_custody',
        ]);
    }

    public function test_technician_cannot_approve_requisitions(): void
    {
        $token = $this->technician1->createToken('test-mobile-token')->plainTextToken;

        $requisition = InventoryRequisition::create([
            'uuid' => (string) Str::uuid(),
            'requester_id' => $this->technician1->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'pending',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/mobile/inventory/requisitions/{$requisition->id}/approve");

        $response->assertStatus(403);
    }

    public function test_technician_can_transfer_custody_to_another_technician(): void
    {
        $token = $this->technician1->createToken('test-mobile-token')->plainTextToken;

        $custody = InventoryCustody::create([
            'uuid' => (string) Str::uuid(),
            'item_id' => $this->itemCable->id,
            'holder_id' => $this->technician1->id,
            'holder_type' => 'technician',
            'quantity' => 500,
            'used_quantity' => 0,
            'returned_quantity' => 0,
            'status' => 'in_custody',
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/mobile/inventory/custody/transfer', [
                'custody_id' => $custody->id,
                'to_user_id' => $this->technician2->id,
                'quantity' => 200,
                'notes' => 'Pinjam kabel dropcore untuk SPK #50',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Technician 1 custody reduced from 500 to 300
        $this->assertDatabaseHas('inventory_custodies', [
            'id' => $custody->id,
            'quantity' => 300,
        ]);

        // Technician 2 received 200
        $this->assertDatabaseHas('inventory_custodies', [
            'holder_id' => $this->technician2->id,
            'item_id' => $this->itemCable->id,
            'quantity' => 200,
        ]);
    }

    public function test_technician_cannot_transfer_custody_owned_by_someone_else(): void
    {
        $token = $this->technician1->createToken('test-mobile-token')->plainTextToken;

        $otherCustody = InventoryCustody::create([
            'uuid' => (string) Str::uuid(),
            'item_id' => $this->itemOnt->id,
            'holder_id' => $this->technician2->id,
            'quantity' => 5,
            'status' => 'in_custody',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/mobile/inventory/custody/transfer', [
                'custody_id' => $otherCustody->id,
                'to_user_id' => $this->technician1->id,
                'quantity' => 2,
            ]);

        $response->assertStatus(403);
    }

    public function test_technician_can_return_material_to_warehouse(): void
    {
        $token = $this->technician1->createToken('test-mobile-token')->plainTextToken;

        $custody = InventoryCustody::create([
            'uuid' => (string) Str::uuid(),
            'item_id' => $this->itemOnt->id,
            'holder_id' => $this->technician1->id,
            'holder_type' => 'technician',
            'quantity' => 2,
            'used_quantity' => 0,
            'returned_quantity' => 0,
            'status' => 'in_custody',
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/mobile/inventory/returns', [
                'custody_id' => $custody->id,
                'quantity' => 1,
                'condition' => 'damaged',
                'notes' => 'Port PON ONT terbakar petir',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('inventory_returns', [
            'returner_id' => $this->technician1->id,
            'status' => 'pending_qc',
        ]);

        $this->assertDatabaseHas('inventory_custodies', [
            'id' => $custody->id,
            'returned_quantity' => 1,
        ]);
    }
}
