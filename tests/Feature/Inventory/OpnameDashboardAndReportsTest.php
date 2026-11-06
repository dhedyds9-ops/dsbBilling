<?php

namespace Tests\Feature\Inventory;

use App\Enums\UserRole;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\InventoryStockMutation;
use App\Models\Inventory\InventoryStockOpname;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use App\Services\Inventory\StockMutationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OpnameDashboardAndReportsTest extends TestCase
{
    use RefreshDatabase;

    private StockMutationService $mutationService;
    private Branch $branch;
    private User $admin;
    private Warehouse $warehouse;
    private Item $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mutationService = app(StockMutationService::class);

        foreach (UserRole::cases() as $role) {
            Role::updateOrCreate(['name' => $role->value], ['display_name' => ucfirst($role->value)]);
        }

        $this->branch = Branch::factory()->create(['name' => 'Cabang Malang']);

        $this->admin = User::factory()->create(['name' => 'Admin Opname', 'branch_id' => $this->branch->id]);
        $this->admin->attachRole(UserRole::Administrator->value);

        $this->warehouse = Warehouse::create([
            'code' => 'WH-MLG-01',
            'name' => 'Gudang Malang',
            'type' => 'central',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $this->item = Item::create([
            'sku' => 'PATCH-CORD-SC-3M',
            'name' => 'Patch Cord SC-UPC 3 Meter',
            'category' => 'Aksesoris Fiber',
            'unit' => 'pcs',
            'is_serialized' => false,
            'cost_price' => 15000,
            'branch_id' => $this->branch->id,
        ]);

        // Stok Awal Inbound: 10 pcs
        $this->mutationService->recordInbound(
            warehouseId: $this->warehouse->id,
            itemId: $this->item->id,
            quantity: 10,
            referenceType: 'inbound',
            notes: 'Stok awal patch cord',
            actorId: $this->admin->id
        );

        $this->actingAs($this->admin);
    }

    public function test_stock_opname_reconciliation_and_adjustment()
    {
        // 1. Verifikasi stok awal sistem adalah 10
        $stockBefore = InventoryStock::where('warehouse_id', $this->warehouse->id)
            ->where('item_id', $this->item->id)
            ->first();
        $this->assertEquals(10, (float) $stockBefore->quantity);

        // 2. Buat sesi stock opname dengan fisik aktual 7 (selisih -3 pcs)
        $opname = InventoryStockOpname::create([
            'warehouse_id' => $this->warehouse->id,
            'branch_id' => $this->branch->id,
            'status' => 'draft',
            'opname_date' => today(),
            'notes' => 'Opname akhir pekan',
            'created_by' => $this->admin->id,
        ]);

        $opnameItem = $opname->items()->create([
            'item_id' => $this->item->id,
            'system_quantity' => 10,
            'physical_quantity' => 7,
            'difference_quantity' => -3,
            'adjustment_reason' => 'barang_rusak',
            'notes' => '3 patch cord putus kabelnya saat pemindahan rak',
        ]);

        // 3. Approve and Adjust Opname via Livewire Component Action
        Livewire::test(\App\Livewire\Inventory\Opname\Index::class)
            ->call('approveAndAdjust', $opname->id);

        // 4. Verifikasi status Opname menjadi completed
        $this->assertEquals('completed', $opname->fresh()->status);
        $this->assertEquals($this->admin->id, $opname->fresh()->approved_by);

        // 5. Verifikasi Saldo Stok Gudang tersinkron ke 7 pcs
        $stockAfter = InventoryStock::where('warehouse_id', $this->warehouse->id)
            ->where('item_id', $this->item->id)
            ->first();
        $this->assertEquals(7, (float) $stockAfter->quantity);

        // 6. Verifikasi Mutasi Ledger mencatat adjustment_out
        $mutation = InventoryStockMutation::where('warehouse_id', $this->warehouse->id)
            ->where('item_id', $this->item->id)
            ->where('type', 'adjustment_out')
            ->latest('id')
            ->first();

        $this->assertNotNull($mutation);
        $this->assertEquals(3, (float) $mutation->qty_out);
        $this->assertEquals(10, (float) $mutation->balance_before);
        $this->assertEquals(7, (float) $mutation->balance_after);
        $this->assertEquals('StockOpname', $mutation->reference_type);
        $this->assertEquals($opname->id, $mutation->reference_id);
    }

    public function test_inventory_dashboard_renders_successfully()
    {
        $response = $this->get(route('inventory.dashboard'));
        $response->assertStatus(200);

        Livewire::test(\App\Livewire\Inventory\Dashboard\Index::class)
            ->assertSee('Dashboard Inventaris', false)
            ->assertSee('Patch Cord SC-UPC 3 Meter')
            ->assertSee('Gudang Malang');
    }

    public function test_inventory_reports_renders_and_tabs_switch()
    {
        $response = $this->get(route('inventory.reports.index'));
        $response->assertStatus(200);

        Livewire::test(\App\Livewire\Inventory\Reports\Index::class)
            ->assertSet('activeTab', 'stock_position')
            ->assertSee('Patch Cord SC-UPC 3 Meter')
            ->call('setTab', 'mutations')
            ->assertSet('activeTab', 'mutations')
            ->assertSee('INBOUND')
            ->call('setTab', 'custody')
            ->assertSet('activeTab', 'custody');
    }

    public function test_opname_index_route_is_accessible()
    {
        $response = $this->get(route('inventory.opnames.index'));
        $response->assertStatus(200);
    }
}
