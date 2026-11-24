<?php

namespace Tests\Feature\Mobile;

use App\Models\AuditLog;
use App\Models\Billing\Invoice;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\Inventory\InventoryCustody;
use App\Models\Inventory\InventoryRequisition;
use App\Models\Inventory\InventoryRequisitionItem;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\ServiceProfileType;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use App\Models\Workforce\MaterialUsage;
use App\Models\Workforce\WorkOrder;
use App\Models\Workforce\WorkOrderChecklist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class Phase1aHotfixTest extends TestCase
{
    use RefreshDatabase;

    private function createBranch(string $name, string $code): Branch
    {
        return Branch::create([
            'name' => $name,
            'code' => $code,
            'is_active' => true,
        ]);
    }

    private function createUserWithRole(string $roleName, ?Branch $branch = null): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], ['display_name' => ucfirst($roleName), 'guard_name' => 'web']);
        $user = User::factory()->create([
            'is_active' => true,
            'branch_id' => $branch?->id,
            'job_function' => strtoupper($roleName),
        ]);
        $user->roles()->attach($role->id);
        return $user;
    }

    /**
     * BUG-01: Offline Sync IDOR rejection for cross-branch or unauthorized technician.
     */
    public function test_offline_sync_idor_rejection_for_cross_branch_technician(): void
    {
        $branchA = $this->createBranch('Branch Pusat', 'BP');
        $branchB = $this->createBranch('Branch Barat', 'BB');

        $techA = $this->createUserWithRole('technician', $branchA);
        $techB = $this->createUserWithRole('technician', $branchB);

        $customerB = Customer::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Pelanggan Cabang Barat',
            'code' => 'CUST-BB-001',
            'phone' => '081299990001',
            'branch_id' => $branchB->id,
            'status' => 'active',
        ]);

        $woBranchB = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'work_order_number' => 'WO-BB-001',
            'title' => 'Instalasi Fiber Barat',
            'customer_id' => $customerB->id,
            'branch_id' => $branchB->id,
            'assigned_to' => $techB->id,
            'type' => 'installation',
            'status' => 'in_progress',
        ]);

        $checklist = WorkOrderChecklist::create([
            'uuid' => (string) Str::uuid(),
            'work_order_id' => $woBranchB->id,
            'item' => 'Cek Redaman Fiber',
            'completed' => false,
        ]);

        // Tech A (Branch A) attempts to manipulate checklist of Work Order in Branch B
        $actionUuid = (string) Str::uuid();
        $payload = [
            'actions' => [
                [
                    'uuid' => $actionUuid,
                    'action_type' => 'checklist_toggle',
                    'work_order_id' => $woBranchB->id,
                    'payload' => [
                        'checklist_id' => $checklist->id,
                        'completed' => true,
                    ],
                ]
            ]
        ];

        $response = $this->actingAs($techA, 'sanctum')
            ->postJson('/api/mobile/sync/batch', $payload);

        $response->assertStatus(200);
        $result = $response->json('results.0');

        // Action must fail due to authorization rejection
        $this->assertEquals('failed', $result['status']);
        $this->assertStringContainsString('milik cabang lain', $result['message']);

        // Checklist in database must remain unmodified
        $this->assertFalse((bool) $checklist->fresh()->completed);

        // Audit log must have recorded the security incident
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $techA->id,
            'event' => 'offline_sync_cross_branch_violation',
        ]);
    }

    /**
     * BUG-01: Offline sync idempotency prevents duplicate item / checklist creation on retry.
     */
    public function test_offline_sync_idempotency_prevents_duplicate_material_usage(): void
    {
        $branch = $this->createBranch('Branch Utama', 'BU');
        $tech = $this->createUserWithRole('technician', $branch);

        $customer = Customer::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Pelanggan Idempotency',
            'code' => 'CUST-IDEM-01',
            'phone' => '081299990002',
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'work_order_number' => 'WO-IDEM-001',
            'title' => 'Instalasi Pelanggan Idempotent',
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'assigned_to' => $tech->id,
            'type' => 'installation',
            'status' => 'in_progress',
        ]);

        $actionUuid = (string) Str::uuid();
        $payload = [
            'actions' => [
                [
                    'uuid' => $actionUuid,
                    'action_type' => 'material_usage',
                    'work_order_id' => $wo->id,
                    'payload' => [
                        'material_name' => 'Drop Core 1 Core 100M',
                        'quantity' => 75,
                        'notes' => 'Tarik kabel tiang ke rumah',
                    ],
                ]
            ]
        ];

        // First attempt
        $res1 = $this->actingAs($tech, 'sanctum')->postJson('/api/mobile/sync/batch', $payload);
        $res1->assertStatus(200);
        $this->assertEquals('success', $res1->json('results.0.status'));

        // Replay identical sync action
        $res2 = $this->actingAs($tech, 'sanctum')->postJson('/api/mobile/sync/batch', $payload);
        $res2->assertStatus(200);
        $this->assertEquals('success', $res2->json('results.0.status'));

        // Assert only ONE material usage record exists for this action UUID
        $this->assertDatabaseCount('material_usages', 1);
    }

    /**
     * BUG-03: Double cash payment prevention and idempotency key handling.
     */
    public function test_double_cash_payment_idempotency_protection(): void
    {
        $branch = $this->createBranch('Branch Finansial', 'BF');
        $reseller = $this->createUserWithRole('reseller', $branch);

        $customer = Customer::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Customer Billing',
            'code' => 'CUST-BILL-001',
            'phone' => '081234567899',
            'reseller_id' => $reseller->id,
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'uuid' => (string) Str::uuid(),
            'invoice_number' => 'INV-202611-001',
            'customer_id' => $customer->id,
            'reseller_id' => $reseller->id,
            'branch_id' => $branch->id,
            'issue_date' => now(),
            'due_date' => now()->addDays(7),
            'total_amount' => 200000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        $idempotencyKey = 'IDEM-PAY-' . Str::uuid();

        // 1. First payment attempt with idempotency key
        $response1 = $this->actingAs($reseller, 'sanctum')
            ->postJson("/api/mobile/invoices/{$invoice->id}/pay-cash", [
                'amount' => 200000,
                'idempotency_key' => $idempotencyKey,
            ]);

        $response1->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals('paid', $invoice->fresh()->status);
        $this->assertEquals(1, $invoice->fresh()->payments()->count());

        // 2. Immediate duplicate submission (network retry with same key)
        $response2 = $this->actingAs($reseller, 'sanctum')
            ->postJson("/api/mobile/invoices/{$invoice->id}/pay-cash", [
                'amount' => 200000,
                'idempotency_key' => $idempotencyKey,
            ]);

        // Must succeed as an idempotent replay without creating a duplicate payment
        $response2->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('idempotent_replay', true);

        $this->assertEquals(1, $invoice->fresh()->payments()->count());

        // 3. New payment attempt with different key on an already paid invoice must be rejected
        $response3 = $this->actingAs($reseller, 'sanctum')
            ->postJson("/api/mobile/invoices/{$invoice->id}/pay-cash", [
                'amount' => 200000,
                'idempotency_key' => 'DIFFERENT-KEY-' . Str::uuid(),
            ]);

        $response3->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /**
     * BUG-04: Inventory race condition and branch isolation in requisition approval.
     */
    public function test_inventory_requisition_cross_branch_approval_rejected(): void
    {
        $branch1 = $this->createBranch('Gudang Cabang 1', 'GC1');
        $branch2 = $this->createBranch('Gudang Cabang 2', 'GC2');

        $adminGudang1 = $this->createUserWithRole('manager', $branch1);
        $tech2 = $this->createUserWithRole('technician', $branch2);

        $warehouse2 = Warehouse::create([
            'uuid' => (string) Str::uuid(),
            'code' => 'WH-GC2',
            'name' => 'Gudang Cabang 2',
            'branch_id' => $branch2->id,
            'is_active' => true,
        ]);

        $item = Item::create([
            'uuid' => (string) Str::uuid(),
            'sku' => 'PC-SC-3M',
            'name' => 'Patch Cord SC-UPC 3M',
            'unit' => 'pcs',
            'category' => 'accessory',
            'branch_id' => $branch2->id,
            'is_active' => true,
        ]);

        $reqBranch2 = InventoryRequisition::create([
            'uuid' => (string) Str::uuid(),
            'requester_id' => $tech2->id,
            'requester_type' => 'technician',
            'warehouse_id' => $warehouse2->id,
            'branch_id' => $branch2->id,
            'status' => 'pending',
            'requisition_type' => 'dispatch_request',
        ]);

        InventoryRequisitionItem::create([
            'requisition_id' => $reqBranch2->id,
            'item_id' => $item->id,
            'quantity_requested' => 5,
        ]);

        // Manager from Branch 1 tries to approve requisition from Branch 2
        $response = $this->actingAs($adminGudang1, 'sanctum')
            ->postJson("/api/mobile/inventory/requisitions/{$reqBranch2->id}/approve");

        $response->assertStatus(403)
            ->assertJsonPath('success', false);

        $this->assertEquals('pending', $reqBranch2->fresh()->status);
    }

    /**
     * BUG-06: Customer PPPoE / Hotspot credentials hidden in list and guarded in show with audit.
     */
    public function test_customer_credentials_masked_in_list_and_audited_in_show(): void
    {
        $branch = $this->createBranch('Cabang Alpha', 'CA');
        $admin = $this->createUserWithRole('administrator', $branch);
        $reseller = $this->createUserWithRole('reseller', $branch);

        $profileType = ServiceProfileType::firstOrCreate(
            ['code' => 'PPPOE'],
            ['name' => 'PPPoE Profile', 'status' => 'active']
        );

        $serviceProfile = ServiceProfile::create([
            'code' => 'PKG-TEST-50M',
            'name' => 'PAKET-TEST-50M',
            'service_profile_type_id' => $profileType->id,
            'price' => 250000,
            'speed_limit' => '50M/50M',
            'service_type' => 'pppoe',
            'status' => 'active',
        ]);

        $customer = Customer::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Pelanggan Rahasia',
            'code' => 'CUST-SEC-001',
            'phone' => '081233334444',
            'reseller_id' => $reseller->id,
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $service = CustomerService::create([
            'customer_id' => $customer->id,
            'service_profile_id' => $serviceProfile->id,
            'username' => 'rahasia_user_01',
            'password' => 'super_secret_pppoe_pass_123',
            'status' => 'active',
        ]);

        // 1. Listing endpoint: service.password MUST be null
        $listResponse = $this->actingAs($reseller, 'sanctum')
            ->getJson('/api/mobile/customers');

        $listResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $listedCustomer = collect($listResponse->json('data'))->firstWhere('id', $customer->id);
        $this->assertNotNull($listedCustomer);
        $this->assertNull($listedCustomer['service']['password']);

        // 2. Detail endpoint by authorized Admin: reveals password and records AuditLog
        $showResponse = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/mobile/customers/{$customer->id}");

        $showResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('customer.services.0.password', 'super_secret_pppoe_pass_123');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'event' => 'customer_password_revealed',
            'auditable_id' => $customer->id,
        ]);
    }
}
