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

        if ($roleName === 'technician') {
            \App\Models\Workforce\Attendance::create([
                'technician_id' => $user->id,
                'date' => \Carbon\Carbon::today(),
                'checked_in_at' => \Carbon\Carbon::now()->subHour(),
                'checked_out_at' => null,
                'status' => \Src\Domain\Workforce\Enums\AttendanceStatus::CHECKED_IN,
            ]);
        }

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
            ->postJson('/api/mobile/sync/offline-batch', $payload);

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
        $res1 = $this->actingAs($tech, 'sanctum')->postJson('/api/mobile/sync/offline-batch', $payload);
        $res1->assertStatus(200);
        $this->assertEquals('success', $res1->json('results.0.status'));

        // Replay identical sync action
        $res2 = $this->actingAs($tech, 'sanctum')->postJson('/api/mobile/sync/offline-batch', $payload);
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

    /**
     * Phase 1A.1: Offline sync cross-company isolation rejection and audit log recording.
     */
    public function test_offline_sync_cross_company_isolation(): void
    {
        $branchA = $this->createBranch('Branch Comp A', 'BCA');
        $techA = $this->createUserWithRole('technician', $branchA);
        $techA->company_id = 1;

        $customerB = Customer::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Pelanggan Comp B',
            'code' => 'CUST-COMP-B',
            'phone' => '081299990099',
            'branch_id' => $branchA->id,
            'status' => 'active',
        ]);

        $woCompB = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'work_order_number' => 'WO-COMP-B-001',
            'title' => 'Instalasi Comp B',
            'customer_id' => $customerB->id,
            'branch_id' => $branchA->id,
            'assigned_to' => $techA->id,
            'company_id' => 2,
            'type' => 'installation',
            'status' => 'in_progress',
        ]);

        $checklist = WorkOrderChecklist::create([
            'uuid' => (string) Str::uuid(),
            'work_order_id' => $woCompB->id,
            'item' => 'Cek Koneksi Perusahaan B',
            'completed' => false,
        ]);

        $actionUuid = (string) Str::uuid();
        $payload = [
            'actions' => [
                [
                    'uuid' => $actionUuid,
                    'action_type' => 'checklist_toggle',
                    'work_order_id' => $woCompB->id,
                    'payload' => [
                        'checklist_id' => $checklist->id,
                        'completed' => true,
                    ],
                ]
            ]
        ];

        $response = $this->actingAs($techA, 'sanctum')
            ->postJson('/api/mobile/sync/offline-batch', $payload);

        $response->assertStatus(200);
        $result = $response->json('results.0');
        $this->assertEquals('failed', $result['status']);
        $this->assertStringContainsString('perusahaan lain', $result['message']);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $techA->id,
            'event' => 'offline_sync_cross_company_violation',
        ]);
    }

    /**
     * Phase 1A.1: Offline sync action UUID replay with conflicting payload must be rejected.
     */
    public function test_offline_sync_action_uuid_payload_conflict_rejected(): void
    {
        $branch = $this->createBranch('Branch Utama', 'BU2');
        $tech = $this->createUserWithRole('technician', $branch);

        $customer = Customer::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Pelanggan Conflict Test',
            'code' => 'CUST-CONF-01',
            'phone' => '081299990088',
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'work_order_number' => 'WO-CONF-001',
            'title' => 'Instalasi Conflict Test',
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'assigned_to' => $tech->id,
            'type' => 'installation',
            'status' => 'in_progress',
        ]);

        $actionUuid = (string) Str::uuid();

        // 1. Initial successful submission
        $payload1 = [
            'actions' => [
                [
                    'uuid' => $actionUuid,
                    'action_type' => 'material_usage',
                    'work_order_id' => $wo->id,
                    'payload' => [
                        'material_name' => 'Kabel Dropcore Optik 100M',
                        'quantity' => 100,
                    ],
                ]
            ]
        ];

        $res1 = $this->actingAs($tech, 'sanctum')->postJson('/api/mobile/sync/offline-batch', $payload1);
        $res1->assertStatus(200);
        $this->assertEquals('success', $res1->json('results.0.status'));

        // 2. Conflicting replay with same UUID but modified quantity
        $payload2 = [
            'actions' => [
                [
                    'uuid' => $actionUuid,
                    'action_type' => 'material_usage',
                    'work_order_id' => $wo->id,
                    'payload' => [
                        'material_name' => 'Kabel Dropcore Optik 100M',
                        'quantity' => 200, // Conflict: quantity differs!
                    ],
                ]
            ]
        ];

        $res2 = $this->actingAs($tech, 'sanctum')->postJson('/api/mobile/sync/offline-batch', $payload2);
        $res2->assertStatus(200);
        $this->assertEquals('failed', $res2->json('results.0.status'));
        $this->assertStringContainsString('Konflik Idempotensi', $res2->json('results.0.message'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tech->id,
            'event' => 'offline_sync_payload_conflict',
        ]);
    }

    /**
     * Phase 1A.1: Submitting actions on a closed or cancelled WorkOrder must be rejected.
     */
    public function test_offline_sync_closed_or_cancelled_work_order_rejected(): void
    {
        $branch = $this->createBranch('Branch Closed Test', 'BCT');
        $tech = $this->createUserWithRole('technician', $branch);

        $customer = Customer::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Pelanggan Closed WO',
            'code' => 'CUST-CLOSED-01',
            'phone' => '081299990077',
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'work_order_number' => 'WO-CLOSED-001',
            'title' => 'Instalasi Sudah Selesai',
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'assigned_to' => $tech->id,
            'type' => 'installation',
            'status' => 'closed',
        ]);

        $checklist = WorkOrderChecklist::create([
            'uuid' => (string) Str::uuid(),
            'work_order_id' => $wo->id,
            'item' => 'Item pada tiket closed',
            'completed' => false,
        ]);

        $payload = [
            'actions' => [
                [
                    'uuid' => (string) Str::uuid(),
                    'action_type' => 'checklist_toggle',
                    'work_order_id' => $wo->id,
                    'payload' => [
                        'checklist_id' => $checklist->id,
                        'completed' => true,
                    ],
                ]
            ]
        ];

        $response = $this->actingAs($tech, 'sanctum')
            ->postJson('/api/mobile/sync/offline-batch', $payload);

        $response->assertStatus(200);
        $this->assertEquals('failed', $response->json('results.0.status'));
        $this->assertStringContainsString("berstatus 'closed'", $response->json('results.0.message'));
    }

    /**
     * Phase 1A.1: PSB Provisioning without custody must be rejected (BUG-02 verification).
     */
    public function test_psb_provisioning_requires_technician_custody(): void
    {
        $branch = $this->createBranch('Branch PSB Test', 'BPST');
        $tech = $this->createUserWithRole('technician', $branch);

        $customer = Customer::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Pelanggan PSB No Custody',
            'code' => 'CUST-PSB-01',
            'phone' => '081299990066',
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'work_order_number' => 'WO-PSB-001',
            'title' => 'PSB Pelanggan No Custody',
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'assigned_to' => $tech->id,
            'type' => 'installation',
            'status' => 'in_progress',
        ]);

        // Attempt provisioning without having custody for ONT-TEST-0001
        $response = $this->actingAs($tech, 'sanctum')
            ->postJson('/api/mobile/network/devices/provision-ont', [
                'serial_number' => 'ZTEGCUSTODY01',
                'work_order_id' => $wo->id,
                'customer_id' => $customer->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
        $this->assertStringContainsString('tidak ditemukan dalam custody/bagasi aktif', $response->json('message'));
    }

    /**
     * Phase 1A.1: PSB Provisioning must reject ONU serial number that is already active on another customer.
     */
    public function test_psb_provisioning_rejects_active_serial_already_used_by_another_customer(): void
    {
        $branch = $this->createBranch('Branch Dup SN', 'BDS');
        $tech = $this->createUserWithRole('technician', $branch);

        $customerA = Customer::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Pelanggan Lama A',
            'code' => 'CUST-DUP-A',
            'phone' => '081299990055',
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $customerB = Customer::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Pelanggan Baru B',
            'code' => 'CUST-DUP-B',
            'phone' => '081299990056',
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'work_order_number' => 'WO-DUP-001',
            'title' => 'PSB Pelanggan B',
            'customer_id' => $customerB->id,
            'branch_id' => $branch->id,
            'assigned_to' => $tech->id,
            'type' => 'installation',
            'status' => 'in_progress',
        ]);

        $item = Item::create([
            'uuid' => (string) Str::uuid(),
            'sku' => 'ONT-ZTE-F670L',
            'name' => 'ONT ZTE F670L Dual Band',
            'unit' => 'unit',
            'category' => 'modem',
            'is_serialized' => true,
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $itemSerial = \App\Models\Inventory\ItemSerial::create([
            'uuid' => (string) Str::uuid(),
            'item_id' => $item->id,
            'serial_number' => 'ZTEGACTIVE01',
            'status' => 'in_custody_technician',
            'custodian_id' => $tech->id,
            'branch_id' => $branch->id,
        ]);

        InventoryCustody::create([
            'uuid' => (string) Str::uuid(),
            'item_id' => $item->id,
            'item_serial_id' => $itemSerial->id,
            'holder_id' => $tech->id,
            'holder_type' => 'technician',
            'quantity' => 1,
            'used_quantity' => 0,
            'returned_quantity' => 0,
            'status' => 'in_custody',
            'branch_id' => $branch->id,
        ]);

        // ONU is already created and in active CustomerService for Customer A
        $onu = \App\Models\ISP\Onu::create([
            'serial_number' => 'ZTEGACTIVE01',
            'code' => 'ONU-ACT-01',
            'name' => 'ONT Customer A',
            'status' => 'active',
            'branch_id' => $branch->id,
        ]);

        CustomerService::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customerA->id,
            'onu_id' => $onu->id,
            'status' => 'active',
            'username' => 'active_user_a',
        ]);

        // Tech attempts to provision this same serial number for Customer B
        $response = $this->actingAs($tech, 'sanctum')
            ->postJson('/api/mobile/network/devices/provision-ont', [
                'serial_number' => 'ZTEGACTIVE01',
                'work_order_id' => $wo->id,
                'customer_id' => $customerB->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
        $this->assertStringContainsString('sedang aktif digunakan oleh pelanggan lain', $response->json('message'));
    }

    /**
     * Phase 1A.1: Cash payment idempotency key payload conflict must be rejected (BUG-03 verification).
     */
    public function test_cash_payment_idempotency_key_payload_conflict_rejected(): void
    {
        $branch = $this->createBranch('Branch Pay Conf', 'BPC');
        $reseller = $this->createUserWithRole('reseller', $branch);

        $customer = Customer::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Customer Conflict Pay',
            'code' => 'CUST-PAY-CONF',
            'phone' => '081234567888',
            'reseller_id' => $reseller->id,
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'uuid' => (string) Str::uuid(),
            'invoice_number' => 'INV-CONF-001',
            'customer_id' => $customer->id,
            'reseller_id' => $reseller->id,
            'branch_id' => $branch->id,
            'issue_date' => now(),
            'due_date' => now()->addDays(7),
            'total_amount' => 150000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        $idempotencyKey = 'IDEM-CONFLICT-' . Str::uuid();

        // 1. First valid payment of 150000
        $res1 = $this->actingAs($reseller, 'sanctum')
            ->postJson("/api/mobile/invoices/{$invoice->id}/pay-cash", [
                'amount' => 150000,
                'idempotency_key' => $idempotencyKey,
            ]);

        $res1->assertStatus(200)->assertJsonPath('success', true);

        // 2. Second request with the SAME idempotency key but DIFFERENT nominal (300000)
        $res2 = $this->actingAs($reseller, 'sanctum')
            ->postJson("/api/mobile/invoices/{$invoice->id}/pay-cash", [
                'amount' => 300000, // Conflict!
                'idempotency_key' => $idempotencyKey,
            ]);

        $res2->assertStatus(422)
            ->assertJsonPath('success', false);
        $this->assertStringContainsString('Konflik Idempotensi', $res2->json('message'));
    }

    /**
     * Phase 1A.1: Custody transfer exceeding available stock must be rejected (BUG-04 verification).
     */
    public function test_inventory_custody_transfer_concurrency_and_negative_stock_prevention(): void
    {
        $branch = $this->createBranch('Branch Transfer Test', 'BTT');
        $tech1 = $this->createUserWithRole('technician', $branch);
        $tech2 = $this->createUserWithRole('technician', $branch);

        $item = Item::create([
            'uuid' => (string) Str::uuid(),
            'sku' => 'DC-1C-TEST',
            'name' => 'Kabel Dropcore Test 100M',
            'unit' => 'meter',
            'category' => 'cable',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $custody = InventoryCustody::create([
            'uuid' => (string) Str::uuid(),
            'item_id' => $item->id,
            'holder_id' => $tech1->id,
            'holder_type' => 'technician',
            'quantity' => 50,
            'used_quantity' => 40, // Available: 10
            'returned_quantity' => 0,
            'status' => 'in_custody',
            'branch_id' => $branch->id,
        ]);

        // Tech 1 attempts to transfer 15 meters when only 10 is available
        $response = $this->actingAs($tech1, 'sanctum')
            ->postJson('/api/mobile/inventory/custody/transfer', [
                'custody_id' => $custody->id,
                'to_user_id' => $tech2->id,
                'quantity' => 15,
                'notes' => 'Transfer melebihi sisa stok',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
        $this->assertStringContainsString('melebihi stok tersedia', $response->json('message'));

        // Stock in database must remain exactly 50 (available 10)
        $this->assertEquals(50, (float) $custody->fresh()->quantity);
    }
}

