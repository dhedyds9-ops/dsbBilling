<?php

namespace Tests\Feature\Inventory;

use App\Models\Inventory\InventoryCustody;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Models\ISP\Odc;
use App\Models\ISP\Odp;
use App\Models\ISP\JointClosure;
use App\Models\Master\Branch;
use App\Models\User;
use App\Services\Inventory\StockMutationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NetworkAssetInventorySyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Branch $branch;
    protected Warehouse $warehouse;
    protected Item $odcItem;
    protected Item $odpItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Cabang Utama',
            'code' => 'PST',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'branch_id' => $this->branch->id,
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Gudang Utama',
            'code' => 'G-PST',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $this->odcItem = Item::create([
            'sku' => 'TEST-ODC-24',
            'name' => 'Box ODC Kabinet 24 Port',
            'category' => 'ODC',
            'unit' => 'unit',
            'min_stock' => 1,
            'cost_price' => 1000000,
            'selling_price' => 1200000,
        ]);

        $this->odpItem = Item::create([
            'sku' => 'TEST-ODP-8',
            'name' => 'Box ODP 8 Port Outdoor',
            'category' => 'ODP',
            'unit' => 'unit',
            'min_stock' => 1,
            'cost_price' => 100000,
            'selling_price' => 150000,
        ]);
    }

    public function test_record_infrastructure_deployment_from_warehouse_deducts_stock()
    {
        $service = app(StockMutationService::class);

        // Beri saldo awal 5 unit di gudang
        $service->recordInbound(
            $this->warehouse->id,
            $this->odcItem->id,
            5,
            'inbound_initial',
            null,
            'Saldo awal test',
            [],
            $this->admin->id
        );

        $stockBefore = InventoryStock::where('warehouse_id', $this->warehouse->id)
            ->where('item_id', $this->odcItem->id)
            ->value('quantity');
        $this->assertEquals(5, (float)$stockBefore);

        // Buat dummy ODC
        $odc = Odc::create([
            'code' => 'ODC-TEST-01',
            'name' => 'ODC Testing 01',
            'branch_id' => $this->branch->id,
            'item_id' => $this->odcItem->id,
            'status' => 'active',
        ]);

        // Catat deployment dari gudang
        $usage = $service->recordInfrastructureDeployment(
            'odc',
            $odc->id,
            [
                ['item_id' => $this->odcItem->id, 'quantity' => 1, 'notes' => 'Pemasangan ODC']
            ],
            'warehouse',
            $this->warehouse->id,
            null,
            $this->admin->id,
            'Deployment ODC Test',
            $this->branch->id
        );

        $this->assertNotNull($usage);
        $this->assertEquals('infrastructure', $usage->work_order_type);
        $this->assertEquals($odc->id, $usage->odc_id);

        // Verifikasi stok gudang berkurang jadi 4
        $stockAfter = InventoryStock::where('warehouse_id', $this->warehouse->id)
            ->where('item_id', $this->odcItem->id)
            ->value('quantity');
        $this->assertEquals(4, (float)$stockAfter);

        // Verifikasi mutasi tercatat
        $this->assertDatabaseHas('inventory_stock_mutations', [
            'warehouse_id' => $this->warehouse->id,
            'item_id' => $this->odcItem->id,
            'type' => 'outbound_dispatch',
            'reference_type' => 'infrastructure_deployment',
            'reference_id' => $odc->id,
            'qty_out' => 1,
        ]);
    }

    public function test_record_infrastructure_deployment_from_technician_custody()
    {
        $service = app(StockMutationService::class);

        // Siapkan custody 3 unit di tangan teknisi
        $custody = InventoryCustody::create([
            'holder_id' => $this->admin->id,
            'holder_type' => 'technician',
            'item_id' => $this->odpItem->id,
            'quantity' => 3,
            'used_quantity' => 0,
            'returned_quantity' => 0,
            'status' => 'in_custody',
            'branch_id' => $this->branch->id,
        ]);

        $odp = Odp::create([
            'code' => 'ODP-TEST-01',
            'name' => 'ODP Testing 01',
            'branch_id' => $this->branch->id,
            'item_id' => $this->odpItem->id,
            'status' => 'active',
        ]);

        $usage = $service->recordInfrastructureDeployment(
            'odp',
            $odp->id,
            [
                ['item_id' => $this->odpItem->id, 'quantity' => 1, 'notes' => 'Pasang ODP']
            ],
            'custody',
            null,
            $this->admin->id,
            $this->admin->id,
            'Deployment ODP via Teknisi',
            $this->branch->id
        );

        $this->assertNotNull($usage);
        $this->assertEquals($odp->id, $usage->odp_id);

        // Verifikasi custody used_quantity menjadi 1
        $custody->refresh();
        $this->assertEquals(1, (float)$custody->used_quantity);
    }

    public function test_livewire_reports_network_reconciliation_tab_renders_successfully()
    {
        $this->actingAs($this->admin);

        Livewire::test(\App\Livewire\Inventory\Reports\Index::class)
            ->set('activeTab', 'network_reconciliation')
            ->assertSee('Neraca Keseimbangan Logistik')
            ->assertSee('Total Pengadaan (Inbound)')
            ->assertSee('Tersedia di Gudang')
            ->assertSee('Bawaan Teknisi (Custody)')
            ->assertSee('Terpasang di Jaringan');
    }
}
