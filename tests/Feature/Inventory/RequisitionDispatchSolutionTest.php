<?php

namespace Tests\Feature\Inventory;

use App\Enums\UserRole;
use App\Models\Inventory\InventoryDispatch;
use App\Models\Inventory\InventoryRequisition;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RequisitionDispatchSolutionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $reseller;
    private Warehouse $warehouse;
    private Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value], ['display_name' => ucfirst($role->value)]);
        }

        $branch = Branch::factory()->create(['name' => 'Cabang Uji']);

        $this->admin = User::factory()->create([
            'name' => 'Admin Gudang',
            'branch_id' => $branch->id,
            'job_function' => 'GUDANG',
        ]);
        $this->admin->attachRole(UserRole::Administrator->value);

        \App\Models\Workforce\Attendance::create([
            'technician_id' => $this->admin->id,
            'date' => \Carbon\Carbon::today(),
            'checked_in_at' => now(),
            'status' => 'checked_in',
        ]);

        $this->reseller = User::factory()->create([
            'name' => 'Reseller Mitra',
            'branch_id' => $branch->id,
        ]);
        $this->reseller->attachRole(UserRole::Reseller->value);

        $this->warehouse = Warehouse::create([
            'uuid' => (string) \Str::uuid(),
            'name' => 'Gudang Pusat',
            'code' => 'GDG-PST',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $this->item = Item::create([
            'name' => 'Kabel Dropcore 150m',
            'sku' => 'DC-150',
            'category' => 'Kabel Dropcore',
            'type' => 'consumable',
            'unit' => 'roll',
            'is_serialized' => false,
            'is_active' => true,
        ]);

        InventoryStock::create([
            'warehouse_id' => $this->warehouse->id,
            'item_id' => $this->item->id,
            'quantity' => 10,
        ]);
    }

    public function test_solusi_a_approve_and_dispatch_redirects_with_requisition_id(): void
    {
        $this->actingAs($this->admin);

        $req = InventoryRequisition::create([
            'uuid' => (string) \Str::uuid(),
            'requisition_number' => 'REQ-TEST-001',
            'requester_id' => $this->reseller->id,
            'requester_type' => 'reseller',
            'warehouse_id' => $this->warehouse->id,
            'distribution_method' => 'pickup_reseller',
            'status' => 'pending',
        ]);

        $req->items()->create([
            'item_id' => $this->item->id,
            'quantity_requested' => 2,
        ]);

        Livewire::test(\App\Livewire\Inventory\Requisition\Index::class)
            ->call('openApprovalModal', $req->id)
            ->call('approveAndDispatch')
            ->assertRedirect(route('inventory.dispatches.index', ['requisition_id' => $req->id]));

        $this->assertEquals('approved', $req->fresh()->status);
        $this->assertEquals($this->admin->id, $req->fresh()->approved_by);
    }

    public function test_solusi_b_dispatch_form_auto_populates_when_requisition_selected(): void
    {
        $this->actingAs($this->admin);

        $req = InventoryRequisition::create([
            'uuid' => (string) \Str::uuid(),
            'requisition_number' => 'REQ-TEST-002',
            'requester_id' => $this->reseller->id,
            'requester_type' => 'reseller',
            'warehouse_id' => $this->warehouse->id,
            'distribution_method' => 'pickup_reseller',
            'status' => 'approved',
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        $req->items()->create([
            'item_id' => $this->item->id,
            'quantity_requested' => 3,
            'quantity_approved' => 3,
            'quantity_dispatched' => 0,
        ]);

        Livewire::test(\App\Livewire\Inventory\Dispatch\Index::class)
            ->call('openCreateModal')
            ->set('selected_requisition_id', $req->id)
            ->assertSet('warehouse_id', $this->warehouse->id)
            ->assertSet('recipient_id', $this->reseller->id)
            ->assertSet('distribution_method', 'pickup_reseller')
            ->call('saveDispatch')
            ->assertHasNoErrors();

        $latestDispatch = InventoryDispatch::latest('id')->first();
        $this->assertNotNull($latestDispatch);
        $this->assertEquals($req->id, $latestDispatch->requisition_id);

        // Requisition otomatis ter-dispatch
        $freshReq = $req->fresh();
        $this->assertEquals(3, $freshReq->items()->first()->quantity_dispatched);
        $this->assertEquals('dispatched', $freshReq->status);

        // Stok gudang berkurang 3 (10 - 3 = 7)
        $this->assertEquals(7, InventoryStock::where('warehouse_id', $this->warehouse->id)->where('item_id', $this->item->id)->first()->quantity);
    }

    public function test_link_existing_manual_dispatch_to_requisition(): void
    {
        $this->actingAs($this->admin);

        // 1. Reseller punya permohonan berstatus approved
        $req = InventoryRequisition::create([
            'uuid' => (string) \Str::uuid(),
            'requisition_number' => 'REQ-TEST-003',
            'requester_id' => $this->reseller->id,
            'requester_type' => 'reseller',
            'warehouse_id' => $this->warehouse->id,
            'distribution_method' => 'pickup_reseller',
            'status' => 'approved',
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        $req->items()->create([
            'item_id' => $this->item->id,
            'quantity_requested' => 1,
            'quantity_approved' => 1,
            'quantity_dispatched' => 0,
        ]);

        // 2. Petugas gudang terlanjur membuat dispatch manual (requisition_id = null)
        $dispatch = InventoryDispatch::create([
            'uuid' => (string) \Str::uuid(),
            'dispatch_number' => 'DSP-MANUAL-001',
            'warehouse_id' => $this->warehouse->id,
            'branch_id' => $this->warehouse->branch_id,
            'recipient_id' => $this->reseller->id,
            'recipient_type' => 'reseller',
            'distribution_method' => 'pickup_reseller',
            'status' => 'dispatched',
            'dispatched_by' => $this->admin->id,
            'dispatched_at' => now(),
            'requisition_id' => null, // Belum tertaut
        ]);

        $dispatch->items()->create([
            'item_id' => $this->item->id,
            'quantity' => 1,
        ]);

        // 3. Admin menautkan lewat modal tautkan dispatch
        Livewire::test(\App\Livewire\Inventory\Requisition\Index::class)
            ->call('openLinkDispatchModal', $req->id)
            ->set('selectedExistingDispatchId', $dispatch->id)
            ->call('saveLinkDispatch')
            ->assertHasNoErrors()
            ->assertSet('showLinkDispatchModal', false);

        // Dispatch sekarang memiliki requisition_id
        $this->assertEquals($req->id, $dispatch->fresh()->requisition_id);

        // Status requisition berubah menjadi dispatched
        $this->assertEquals('dispatched', $req->fresh()->status);
        $this->assertEquals(1, $req->fresh()->items()->first()->quantity_dispatched);
    }
}
