<?php

namespace Tests\Feature\Inventory;

use App\Enums\UserRole;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\InventoryStockMutation;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemSerial;
use App\Models\Inventory\Warehouse;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use App\Services\Inventory\StockMutationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundationInventoryTest extends TestCase
{
    use RefreshDatabase;

    private StockMutationService $mutationService;
    private User $adminUser;
    private Branch $branchA;
    private Branch $branchB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mutationService = app(StockMutationService::class);

        foreach (UserRole::cases() as $role) {
            Role::updateOrCreate(['name' => $role->value], ['display_name' => ucfirst($role->value)]);
        }

        // Buat cabang & admin test
        $this->branchA = Branch::factory()->create(['name' => 'Cabang Barat']);
        $this->branchB = Branch::factory()->create(['name' => 'Cabang Timur']);

        $this->adminUser = User::factory()->create([
            'name' => 'Gudang Manager',
            'branch_id' => $this->branchA->id,
        ]);
        $this->adminUser->attachRole(UserRole::Administrator->value);

        $this->actingAs($this->adminUser);
    }

    public function test_can_create_warehouse_and_item()
    {
        $warehouse = Warehouse::create([
            'code' => 'WH-TEST-01',
            'name' => 'Gudang Utama Barat',
            'type' => 'central',
            'branch_id' => $this->branchA->id,
            'is_active' => true,
        ]);

        $item = Item::create([
            'sku' => 'ONU-TEST-001',
            'name' => 'XPON ONU 1GE Gigabit',
            'category' => 'ONU/ONT',
            'unit' => 'unit',
            'is_serialized' => true,
            'is_reusable' => true,
            'min_stock' => 5,
            'cost_price' => 125000,
            'selling_price' => 175000,
            'is_active' => true,
            'branch_id' => $this->branchA->id,
        ]);

        $this->assertDatabaseHas('warehouses', ['code' => 'WH-TEST-01', 'type' => 'central']);
        $this->assertDatabaseHas('items', ['sku' => 'ONU-TEST-001', 'is_serialized' => true]);
        $this->assertEquals(0, $item->total_stock);
    }

    public function test_record_inbound_updates_stock_and_creates_immutable_mutation_ledger()
    {
        $warehouse = Warehouse::create([
            'code' => 'WH-TEST-02',
            'name' => 'Gudang Barat',
            'type' => 'branch',
            'branch_id' => $this->branchA->id,
        ]);

        $item = Item::create([
            'sku' => 'ONU-HG6145F',
            'name' => 'Fiberhome HG6145F Dual Band',
            'category' => 'ONU/ONT',
            'unit' => 'unit',
            'is_serialized' => true,
            'branch_id' => $this->branchA->id,
        ]);

        $serials = ['FHTT9B2172C8', 'FHTT9B2172C9', 'FHTT9B2172CA'];

        $mutation = $this->mutationService->recordInbound(
            warehouseId: $warehouse->id,
            itemId: $item->id,
            quantity: 3,
            referenceType: 'inbound',
            referenceId: 99,
            notes: 'Penerimaan Batch 1 Supplier',
            serials: $serials,
            actorId: $this->adminUser->id
        );

        $this->assertInstanceOf(InventoryStockMutation::class, $mutation);
        $this->assertEquals(0, $mutation->balance_before);
        $this->assertEquals(3, $mutation->balance_after);
        $this->assertEquals(3, $mutation->qty_in);
        $this->assertEquals(0, $mutation->qty_out);

        // Verifikasi tabel inventory_stocks
        $stock = InventoryStock::where('warehouse_id', $warehouse->id)
            ->where('item_id', $item->id)
            ->first();
        $this->assertNotNull($stock);
        $this->assertEquals(3, (float) $stock->quantity);
        $this->assertEquals(3, (float) $item->fresh()->total_stock);

        // Verifikasi item_serials terdaftar dengan status in_warehouse
        $this->assertDatabaseHas('item_serials', [
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'serial_number' => 'FHTT9B2172C8',
            'status' => 'in_warehouse',
            'condition' => 'good',
        ]);
        $this->assertDatabaseHas('item_serials', [
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'serial_number' => 'FHTT9B2172CA',
            'status' => 'in_warehouse',
        ]);
        $this->assertEquals(3, ItemSerial::where('item_id', $item->id)->count());
    }

    public function test_record_outbound_prevents_negative_stock()
    {
        $warehouse = Warehouse::create([
            'code' => 'WH-TEST-03',
            'name' => 'Gudang Dropcore',
            'type' => 'central',
            'branch_id' => $this->branchA->id,
        ]);

        $item = Item::create([
            'sku' => 'CAB-DROP-1C',
            'name' => 'Kabel Dropcore 1 Core 1000m',
            'category' => 'Kabel Dropcore',
            'unit' => 'meter',
            'is_serialized' => false,
            'branch_id' => $this->branchA->id,
        ]);

        // Masukkan stok 100 meter
        $this->mutationService->recordInbound(
            warehouseId: $warehouse->id,
            itemId: $item->id,
            quantity: 100,
            referenceType: 'inbound',
            notes: 'Stok Awal Dropcore'
        );

        // Outbound 40 meter berhasil
        $outMutation = $this->mutationService->recordOutbound(
            warehouseId: $warehouse->id,
            itemId: $item->id,
            quantity: 40,
            referenceType: 'requisition',
            referenceId: 101,
            notes: 'Dispatch ke teknisi'
        );

        $this->assertEquals(100, $outMutation->balance_before);
        $this->assertEquals(60, $outMutation->balance_after);
        $this->assertEquals(60, (float) $item->fresh()->total_stock);

        // Coba outbound 100 meter (melebihi sisa 60) -> Harus throw exception
        $this->expectException(\RuntimeException::class);
        $this->mutationService->recordOutbound(
            warehouseId: $warehouse->id,
            itemId: $item->id,
            quantity: 100,
            referenceType: 'requisition',
            referenceId: 102
        );
    }

    public function test_inventory_routes_are_accessible_for_admin()
    {
        $this->get(route('inventory.items.index'))->assertStatus(200);
        $this->get(route('inventory.warehouses.index'))->assertStatus(200);
        $this->get(route('inventory.inbounds.index'))->assertStatus(200);
        $this->get(route('inventory.stock-card.index'))->assertStatus(200);
    }

    public function test_items_index_per_page_filtering()
    {
        for ($i = 1; $i <= 25; $i++) {
            Item::create([
                'sku' => "SKU-PAGE-TEST-{$i}",
                'name' => "Item Page Test {$i}",
                'category' => 'Material Lain',
                'unit' => 'pcs',
                'min_stock' => 1,
                'is_active' => true,
            ]);
        }

        \Livewire\Livewire::actingAs($this->adminUser)
            ->test(\App\Livewire\Inventory\Item\Index::class)
            ->assertSet('perPage', 20)
            ->assertViewHas('items', fn ($items) => $items->count() === 20 && $items->total() >= 25)
            ->set('perPage', '50')
            ->assertViewHas('items', fn ($items) => $items->count() >= 25)
            ->set('perPage', 'all')
            ->assertViewHas('items', fn ($items) => $items->count() >= 25);
    }
}
