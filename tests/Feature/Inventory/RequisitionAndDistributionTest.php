<?php

namespace Tests\Feature\Inventory;

use App\Enums\UserRole;
use App\Models\CRM\Customer;
use App\Models\Inventory\InventoryCustody;
use App\Models\Inventory\InventoryCustodyTransfer;
use App\Models\Inventory\InventoryDispatch;
use App\Models\Inventory\InventoryRequisition;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemSerial;
use App\Models\Inventory\Warehouse;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use App\Services\Inventory\StockMutationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequisitionAndDistributionTest extends TestCase
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

        $this->branch = Branch::factory()->create(['name' => 'Cabang Utama']);

        $this->admin = User::factory()->create(['name' => 'Admin Gudang', 'branch_id' => $this->branch->id]);
        $this->admin->attachRole(UserRole::Administrator->value);

        $this->resellerAndi = User::factory()->create(['name' => 'Reseller Andi', 'branch_id' => $this->branch->id]);
        $this->resellerAndi->attachRole(UserRole::Reseller->value);

        Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Teknisi']);

        $this->technicianRudi = User::factory()->create(['name' => 'Teknisi Rudi', 'branch_id' => $this->branch->id]);
        $this->technicianRudi->attachRole('technician');

        $this->customerBudi = Customer::create([
            'uuid' => (string) \Str::uuid(),
            'code' => 'CUST-BUDI-01',
            'name' => 'Budi Sudarsono',
            'phone' => '081234567890',
            'branch_id' => $this->branch->id,
            'reseller_id' => $this->resellerAndi->id,
        ]);

        $this->warehouse = Warehouse::create([
            'code' => 'WH-PST-01',
            'name' => 'Gudang Pusat',
            'type' => 'central',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $this->itemOnu = Item::create([
            'sku' => 'ONU-XPON-GIGA',
            'name' => 'XPON ONU 1GE',
            'category' => 'ONU/ONT',
            'unit' => 'unit',
            'is_serialized' => true,
            'branch_id' => $this->branch->id,
        ]);

        $this->itemDropcore = Item::create([
            'sku' => 'CAB-DROP-1C',
            'name' => 'Kabel Dropcore 1C 1000m',
            'category' => 'Kabel Dropcore',
            'unit' => 'meter',
            'is_serialized' => false,
            'branch_id' => $this->branch->id,
        ]);

        // Stok Awal: 5 ONU (dengan serial) dan 1000m dropcore
        $this->mutationService->recordInbound(
            warehouseId: $this->warehouse->id,
            itemId: $this->itemOnu->id,
            quantity: 5,
            referenceType: 'inbound',
            notes: 'Stok awal ONU',
            serials: ['SN-ONU-001', 'SN-ONU-002', 'SN-ONU-003', 'SN-ONU-004', 'SN-ONU-005'],
            actorId: $this->admin->id
        );

        $this->mutationService->recordInbound(
            warehouseId: $this->warehouse->id,
            itemId: $this->itemDropcore->id,
            quantity: 1000,
            referenceType: 'inbound',
            notes: 'Stok awal Dropcore',
            actorId: $this->admin->id
        );

        $this->actingAs($this->admin);
    }

    public function test_metode_a_reseller_pickup_and_handover_to_technician()
    {
        // 1. Andi membuat permintaan barang (Metode A)
        $req = InventoryRequisition::create([
            'requester_id' => $this->resellerAndi->id,
            'requester_type' => 'reseller',
            'warehouse_id' => $this->warehouse->id,
            'branch_id' => $this->branch->id,
            'distribution_method' => 'pickup_reseller',
            'customer_id' => $this->customerBudi->id,
            'work_order_type' => 'psb',
            'designated_technician_id' => $this->technicianRudi->id,
            'status' => 'pending',
            'notes' => 'Permintaan PSB Budi',
        ]);

        $req->items()->createMany([
            ['item_id' => $this->itemOnu->id, 'quantity_requested' => 1, 'quantity_approved' => 1],
            ['item_id' => $this->itemDropcore->id, 'quantity_requested' => 100, 'quantity_approved' => 100],
        ]);

        $this->assertEquals('pending', $req->status);

        // 2. Pengurus Gudang menyetujui permintaan
        $req->update(['status' => 'approved', 'approved_by' => $this->admin->id, 'approved_at' => now()]);
        $this->assertEquals('approved', $req->fresh()->status);

        // 3. Pengurus Gudang mengeluarkan barang ke Reseller Andi
        $dispatch = InventoryDispatch::create([
            'requisition_id' => $req->id,
            'warehouse_id' => $this->warehouse->id,
            'branch_id' => $this->branch->id,
            'distribution_method' => 'pickup_reseller',
            'recipient_id' => $this->resellerAndi->id,
            'recipient_type' => 'reseller',
            'customer_id' => $this->customerBudi->id,
            'status' => 'dispatched',
            'dispatched_by' => $this->admin->id,
            'dispatched_at' => now(),
        ]);

        $itemsPayload = [
            ['item_id' => $this->itemOnu->id, 'quantity' => 1, 'serials' => ['SN-ONU-001']],
            ['item_id' => $this->itemDropcore->id, 'quantity' => 100, 'serials' => []],
        ];

        $this->mutationService->recordDispatch($dispatch, $itemsPayload, $this->admin->id);

        // Verifikasi stok gudang berkurang
        $stockOnu = InventoryStock::where('warehouse_id', $this->warehouse->id)->where('item_id', $this->itemOnu->id)->first();
        $this->assertEquals(4, (float) $stockOnu->quantity);

        $stockDrop = InventoryStock::where('warehouse_id', $this->warehouse->id)->where('item_id', $this->itemDropcore->id)->first();
        $this->assertEquals(900, (float) $stockDrop->quantity);

        // Verifikasi status SN adalah in_custody_reseller dengan custodian Andi
        $serial = ItemSerial::where('serial_number', 'SN-ONU-001')->first();
        $this->assertEquals('in_custody_reseller', $serial->status);
        $this->assertEquals($this->resellerAndi->id, $serial->custodian_id);

        // Verifikasi Andi tercatat sebagai pemegang barang (Custody)
        $custodiesAndi = InventoryCustody::where('holder_id', $this->resellerAndi->id)->get();
        $this->assertCount(2, $custodiesAndi);

        $onuCustody = $custodiesAndi->where('item_id', $this->itemOnu->id)->first();
        $this->assertEquals('in_custody', $onuCustody->status);
        $this->assertEquals(1, $onuCustody->available_quantity);

        // 4. Andi menyerahkan barang kepada Teknisi Rudi (Metode A langkah 11)
        $transfer = $this->mutationService->handoverCustody(
            custodyId: $onuCustody->id,
            toUserId: $this->technicianRudi->id,
            toUserType: 'technician',
            notes: 'Serah terima ONU untuk PSB Budi'
        );

        $this->assertInstanceOf(InventoryCustodyTransfer::class, $transfer);
        $this->assertEquals('pending_acceptance', $transfer->status);

        // 5. Teknisi Rudi mengonfirmasi penerimaan barang (Metode A langkah 12)
        $this->mutationService->acceptHandover($transfer->id, $this->technicianRudi->id);

        // Verifikasi transfer status accepted
        $this->assertEquals('accepted', $transfer->fresh()->status);

        // Verifikasi status custody lama Andi menjadi handed_over
        $this->assertEquals('handed_over', $onuCustody->fresh()->status);

        // Verifikasi custody baru untuk Rudi aktif
        $rudiCustody = InventoryCustody::where('holder_id', $this->technicianRudi->id)
            ->where('item_id', $this->itemOnu->id)
            ->first();
        $this->assertNotNull($rudiCustody);
        $this->assertEquals('in_custody', $rudiCustody->status);
        $this->assertEquals(1, $rudiCustody->available_quantity);

        // Verifikasi serial number berpindah ke Rudi
        $this->assertEquals('in_custody_technician', $serial->fresh()->status);
        $this->assertEquals($this->technicianRudi->id, $serial->fresh()->custodian_id);
    }

    public function test_metode_b_reseller_requests_warehouse_dispatches_directly_to_technician()
    {
        // 1. Andi membuat permintaan dengan Metode B (Gudang langsung ke Teknisi)
        $req = InventoryRequisition::create([
            'requester_id' => $this->resellerAndi->id,
            'requester_type' => 'reseller',
            'warehouse_id' => $this->warehouse->id,
            'branch_id' => $this->branch->id,
            'distribution_method' => 'direct_technician',
            'customer_id' => $this->customerBudi->id,
            'work_order_type' => 'psb',
            'designated_technician_id' => $this->technicianRudi->id,
            'status' => 'approved',
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        $req->items()->create([
            'item_id' => $this->itemOnu->id,
            'quantity_requested' => 1,
            'quantity_approved' => 1,
        ]);

        // 2. Gudang menyerahkan barang langsung kepada Rudi (Teknisi)
        $dispatch = InventoryDispatch::create([
            'requisition_id' => $req->id,
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
            ['item_id' => $this->itemOnu->id, 'quantity' => 1, 'serials' => ['SN-ONU-002']],
        ];

        $this->mutationService->recordDispatch($dispatch, $itemsPayload, $this->admin->id);

        // Verifikasi Rudi langsung tercatat sebagai pemegang barang (in_custody)
        $rudiCustody = InventoryCustody::where('holder_id', $this->technicianRudi->id)
            ->where('item_id', $this->itemOnu->id)
            ->first();
        $this->assertNotNull($rudiCustody);
        $this->assertEquals('in_custody', $rudiCustody->status);
        $this->assertEquals(1, $rudiCustody->available_quantity);

        // Verifikasi serial status langsung in_custody_technician
        $serial = ItemSerial::where('serial_number', 'SN-ONU-002')->first();
        $this->assertEquals('in_custody_technician', $serial->status);
        $this->assertEquals($this->technicianRudi->id, $serial->custodian_id);

        // Verifikasi stok gudang berkurang dari 5 menjadi 4
        $stockOnu = InventoryStock::where('warehouse_id', $this->warehouse->id)->where('item_id', $this->itemOnu->id)->first();
        $this->assertEquals(4, (float) $stockOnu->quantity);
    }

    public function test_distribution_routes_are_accessible_for_admin()
    {
        $this->get(route('inventory.requisitions.index'))->assertStatus(200);
        $this->get(route('inventory.dispatches.index'))->assertStatus(200);
        $this->get(route('inventory.custodies.index'))->assertStatus(200);
    }
}
