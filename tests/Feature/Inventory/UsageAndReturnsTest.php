<?php

namespace Tests\Feature\Inventory;

use App\Enums\UserRole;
use App\Models\CRM\Customer;
use App\Models\Inventory\InventoryCustody;
use App\Models\Inventory\InventoryDispatch;
use App\Models\Inventory\InventoryReturn;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\InventoryStockMutation;
use App\Models\Inventory\InventoryUsage;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemSerial;
use App\Models\Inventory\Warehouse;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use App\Services\Inventory\StockMutationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsageAndReturnsTest extends TestCase
{
    use RefreshDatabase;

    private StockMutationService $mutationService;
    private Branch $branch;
    private User $admin;
    private User $resellerAndi;
    private User $technicianRudi;
    private Customer $customerBudi;
    private Warehouse $warehouse;
    private Item $itemOnu;
    private Item $itemDropcore;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mutationService = app(StockMutationService::class);

        foreach (UserRole::cases() as $role) {
            Role::updateOrCreate(['name' => $role->value], ['display_name' => ucfirst($role->value)]);
        }

        $this->branch = Branch::factory()->create(['name' => 'Cabang Surabaya']);

        $this->admin = User::factory()->create(['name' => 'Admin Gudang', 'branch_id' => $this->branch->id]);
        $this->admin->attachRole(UserRole::Administrator->value);

        $this->resellerAndi = User::factory()->create(['name' => 'Reseller Andi', 'branch_id' => $this->branch->id]);
        $this->resellerAndi->attachRole(UserRole::Reseller->value);

        Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Teknisi']);

        $this->technicianRudi = User::factory()->create(['name' => 'Teknisi Rudi', 'branch_id' => $this->branch->id]);
        $this->technicianRudi->attachRole('technician');

        $this->customerBudi = Customer::create([
            'uuid' => (string) \Str::uuid(),
            'code' => 'CUST-BUDI-02',
            'name' => 'Budi Santoso',
            'phone' => '081234567891',
            'branch_id' => $this->branch->id,
            'reseller_id' => $this->resellerAndi->id,
        ]);

        $this->warehouse = Warehouse::create([
            'code' => 'WH-SBY-01',
            'name' => 'Gudang Surabaya',
            'type' => 'central',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $this->itemOnu = Item::create([
            'sku' => 'ONU-XPON-02',
            'name' => 'XPON ONU 1GE Gigabit',
            'category' => 'ONU/ONT',
            'unit' => 'unit',
            'is_serialized' => true,
            'branch_id' => $this->branch->id,
        ]);

        $this->itemDropcore = Item::create([
            'sku' => 'CAB-DROP-02',
            'name' => 'Kabel Dropcore 1 Core',
            'category' => 'Kabel Dropcore',
            'unit' => 'meter',
            'is_serialized' => false,
            'branch_id' => $this->branch->id,
        ]);

        // Stok Awal: 5 ONU dan 500m Dropcore
        $this->mutationService->recordInbound(
            warehouseId: $this->warehouse->id,
            itemId: $this->itemOnu->id,
            quantity: 5,
            referenceType: 'inbound',
            notes: 'Stok awal ONU',
            serials: ['SN-TEST-001', 'SN-TEST-002', 'SN-TEST-003', 'SN-TEST-004', 'SN-TEST-005'],
            actorId: $this->admin->id
        );

        $this->mutationService->recordInbound(
            warehouseId: $this->warehouse->id,
            itemId: $this->itemDropcore->id,
            quantity: 500,
            referenceType: 'inbound',
            notes: 'Stok awal Dropcore',
            actorId: $this->admin->id
        );

        $this->actingAs($this->admin);
    }

    public function test_actual_usage_by_technician_updates_custody_and_serial_status()
    {
        // 1. Gudang mendistribusikan barang ke Teknisi Rudi
        $dispatch = InventoryDispatch::create([
            'warehouse_id' => $this->warehouse->id,
            'branch_id' => $this->branch->id,
            'distribution_method' => 'direct_technician',
            'recipient_id' => $this->technicianRudi->id,
            'recipient_type' => 'technician',
            'customer_id' => $this->customerBudi->id,
            'status' => 'dispatched',
            'dispatched_by' => $this->admin->id,
            'dispatched_at' => now(),
        ]);

        $itemsPayload = [
            ['item_id' => $this->itemOnu->id, 'quantity' => 1, 'serials' => ['SN-TEST-001']],
            ['item_id' => $this->itemDropcore->id, 'quantity' => 100, 'serials' => []],
        ];

        $this->mutationService->recordDispatch($dispatch, $itemsPayload, $this->admin->id);

        // 2. Rudi mencatat pemakaian aktual: 1 ONU terpasang ke Budi, dan 80m kabel terpakai
        $usage = InventoryUsage::create([
            'technician_id' => $this->technicianRudi->id,
            'customer_id' => $this->customerBudi->id,
            'branch_id' => $this->branch->id,
            'work_order_type' => 'psb',
            'status' => 'completed',
            'installed_at' => now(),
            'notes' => 'Pemasangan sukses, redaman optik -19dBm',
        ]);

        $usageItems = [
            [
                'item_id' => $this->itemOnu->id,
                'quantity' => 1,
                'serials' => ['SN-TEST-001'],
                'notes' => 'Dipasang di ruang tengah',
            ],
            [
                'item_id' => $this->itemDropcore->id,
                'quantity' => 80,
                'serials' => [],
                'notes' => 'Dari ODP-01 ke rumah pelanggan 80 meter',
            ],
        ];

        $this->mutationService->recordUsage($usage, $usageItems, $this->technicianRudi->id);

        // Verifikasi Serial Number menjadi installed & terkait pelanggan
        $serial = ItemSerial::where('serial_number', 'SN-TEST-001')->first();
        $this->assertEquals('installed', $serial->status);
        $this->assertEquals($this->customerBudi->id, $serial->customer_id);

        // Verifikasi Custody ONU Rudi menjadi 'used'
        $onuCustody = InventoryCustody::where('holder_id', $this->technicianRudi->id)
            ->where('item_id', $this->itemOnu->id)
            ->first();
        $this->assertEquals(0, (float) $onuCustody->available_quantity);
        $this->assertEquals(1, (float) $onuCustody->used_quantity);
        $this->assertEquals('used', $onuCustody->status);

        // Verifikasi Custody Dropcore Rudi berkurang 80m, sisa 20m
        $dropCustody = InventoryCustody::where('holder_id', $this->technicianRudi->id)
            ->where('item_id', $this->itemDropcore->id)
            ->first();
        $this->assertEquals(20, (float) $dropCustody->available_quantity);
        $this->assertEquals(80, (float) $dropCustody->used_quantity);
        $this->assertEquals('in_custody', $dropCustody->status);
    }

    public function test_return_leftover_material_restocks_warehouse_upon_qc_approval()
    {
        // 1. Dispatch 100m dropcore ke Rudi
        $dispatch = InventoryDispatch::create([
            'warehouse_id' => $this->warehouse->id,
            'branch_id' => $this->branch->id,
            'distribution_method' => 'direct_technician',
            'recipient_id' => $this->technicianRudi->id,
            'recipient_type' => 'technician',
            'status' => 'dispatched',
            'dispatched_by' => $this->admin->id,
            'dispatched_at' => now(),
        ]);

        $this->mutationService->recordDispatch($dispatch, [
            ['item_id' => $this->itemDropcore->id, 'quantity' => 100, 'serials' => []],
        ], $this->admin->id);

        // Sisa stok gudang Dropcore: 500 - 100 = 400
        $stockDrop = InventoryStock::where('warehouse_id', $this->warehouse->id)
            ->where('item_id', $this->itemDropcore->id)
            ->first();
        $this->assertEquals(400, (float) $stockDrop->quantity);

        // 2. Rudi mengajukan retur sisa 20m kabel (kondisi baik / leftover)
        $return = InventoryReturn::create([
            'returner_id' => $this->technicianRudi->id,
            'returner_type' => 'technician',
            'warehouse_id' => $this->warehouse->id,
            'branch_id' => $this->branch->id,
            'reason' => 'leftover',
            'status' => 'pending_qc',
            'notes' => 'Sisa instalasi PSB Budi',
        ]);

        $returnItem = $return->items()->create([
            'item_id' => $this->itemDropcore->id,
            'quantity' => 20,
            'condition' => 'good',
            'notes' => 'Kabel gulungan sisa utuh',
        ]);

        // 3. Admin Gudang melakukan QC Inspection dan menyetujui retur
        $qcPayload = [
            [
                'return_item_id' => $returnItem->id,
                'qc_status' => 'approved',
                'qc_notes' => 'Kondisi mulus, layak pakai ulang',
            ],
        ];

        $this->mutationService->processReturnQC($return, $qcPayload, $this->admin->id);

        // Verifikasi Status Retur menjadi restocked
        $this->assertEquals('restocked', $return->fresh()->status);

        // Verifikasi Stok Gudang bertambah kembali 20m (400 + 20 = 420)
        $stockDropAfter = InventoryStock::where('warehouse_id', $this->warehouse->id)
            ->where('item_id', $this->itemDropcore->id)
            ->first();
        $this->assertEquals(420, (float) $stockDropAfter->quantity);

        // Verifikasi Ledger Mutasi mencatat return_good
        $mutation = InventoryStockMutation::where('warehouse_id', $this->warehouse->id)
            ->where('item_id', $this->itemDropcore->id)
            ->where('type', 'return_good')
            ->latest('id')
            ->first();
        $this->assertNotNull($mutation);
        $this->assertEquals(20, (float) $mutation->qty_in);
    }

    public function test_return_damaged_device_marks_serial_as_damaged()
    {
        // 1. Dispatch ONU SN-TEST-002 ke Rudi
        $dispatch = InventoryDispatch::create([
            'warehouse_id' => $this->warehouse->id,
            'branch_id' => $this->branch->id,
            'distribution_method' => 'direct_technician',
            'recipient_id' => $this->technicianRudi->id,
            'recipient_type' => 'technician',
            'status' => 'dispatched',
            'dispatched_by' => $this->admin->id,
            'dispatched_at' => now(),
        ]);

        $this->mutationService->recordDispatch($dispatch, [
            ['item_id' => $this->itemOnu->id, 'quantity' => 1, 'serials' => ['SN-TEST-002']],
        ], $this->admin->id);

        // 2. Rudi retur ONU karena port optik rusak / mati total
        $return = InventoryReturn::create([
            'returner_id' => $this->technicianRudi->id,
            'returner_type' => 'technician',
            'warehouse_id' => $this->warehouse->id,
            'branch_id' => $this->branch->id,
            'reason' => 'damaged',
            'status' => 'pending_qc',
            'notes' => 'Port LOS merah terus, laser mati',
        ]);

        $serial = ItemSerial::where('serial_number', 'SN-TEST-002')->first();

        $returnItem = $return->items()->create([
            'item_id' => $this->itemOnu->id,
            'item_serial_id' => $serial->id,
            'quantity' => 1,
            'condition' => 'damaged',
            'notes' => 'Hardware fault',
        ]);

        // 3. Admin QC menyetujui barang sebagai damaged (tidak masuk stok produktif)
        $qcPayload = [
            [
                'return_item_id' => $returnItem->id,
                'item_serial_id' => $serial->id,
                'condition' => 'damaged',
                'action_taken' => 'quarantine',
                'qc_status' => 'approved',
                'qc_notes' => 'Dikonfirmasi optical transmitter rusak',
            ],
        ];

        $this->mutationService->processReturnQC($return, $qcPayload, $this->admin->id);

        // Verifikasi Status Serial menjadi damaged
        $this->assertEquals('damaged', $serial->fresh()->status);
    }

    public function test_inventory_usage_and_returns_routes_are_accessible_and_sidebar_does_not_500()
    {
        $this->actingAs($this->admin);

        // Test route Pemakaian Barang
        $responseUsage = $this->get(route('inventory.usage.index'));
        $responseUsage->assertStatus(200);

        // Test route Retur Material
        $responseReturns = $this->get(route('inventory.returns.index'));
        $responseReturns->assertStatus(200);

        // Test route alias technician_portal/tickets redirects properly without 500 error
        $responseTech = $this->get('/technician_portal/tickets');
        $this->assertNotEquals(500, $responseTech->getStatusCode());
        $responseTech->assertRedirect('/technician-portal/tickets');
    }
}
