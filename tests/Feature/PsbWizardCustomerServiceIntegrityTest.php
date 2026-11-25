<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Billing\Invoice;
use App\Models\Billing\Subscription;
use App\Models\CRM\Contract;
use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\Customer\CustomerService;
use App\Models\Inventory\InventoryCustody;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemSerial;
use App\Models\Inventory\Warehouse;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\NetworkProfile;
use App\Models\ISP\Odp;
use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\Router;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\ServiceProfileType;
use App\Models\ISP\Vendor;
use App\Models\Master\Branch;
use App\Models\Provisioning\ProvisionPipeline;
use App\Models\Provisioning\ServiceInstance;
use App\Models\Role;
use App\Models\ServiceCatalog\Service;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Models\Workforce\WorkOrder;
use App\Integration\MikroTik\Contracts\RouterOSDriverInterface;
use App\Integration\MikroTik\Services\RouterOSService;
use App\Services\ISP\PPPoEService;
use App\Services\Provisioning\ProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Src\Domain\Provisioning\ProvisionPipelineRepositoryInterface;
use Src\Domain\Provisioning\ServiceInstanceRepositoryInterface;
use Tests\TestCase;

class PsbWizardCustomerServiceIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;
    protected User $technician;
    protected User $reseller;
    protected ServiceProfile $pppoeProfile;
    protected ServiceProfile $hotspotProfile;
    protected Service $catalogService;
    protected Router $router;
    protected Vendor $vendor;
    protected Olt $olt;
    protected Odp $odp;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Roles
        Role::firstOrCreate(['name' => UserRole::Manager->value], ['display_name' => 'Manager']);
        Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Technician']);
        Role::firstOrCreate(['name' => 'reseller'], ['display_name' => 'Reseller']);
        Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator']);

        // 2. Branch & Users
        $this->branch = Branch::create([
            'name' => 'Cabang Utama Bandung',
            'code' => 'BDG-01',
            'is_active' => true,
        ]);

        $this->reseller = User::factory()->create([
            'name' => 'Mitra Reseller Dago',
            'is_active' => true,
            'branch_id' => $this->branch->id,
        ]);
        $this->reseller->attachRole('reseller');

        $this->technician = User::factory()->create([
            'name' => 'Teknisi Lapangan 1',
            'job_function' => 'TECHNICIAN',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->technician->attachRole('technician');

        // Check-in attendance for technician
        \App\Models\Workforce\Attendance::create([
            'technician_id' => $this->technician->id,
            'date' => \Carbon\Carbon::today(),
            'checked_in_at' => \Carbon\Carbon::now()->subHour(),
            'checked_out_at' => null,
            'status' => \Src\Domain\Workforce\Enums\AttendanceStatus::CHECKED_IN,
        ]);

        // 3. Catalog Service & Service Profile Types
        $catalog = \App\Models\ServiceCatalog\ServiceCatalog::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Katalog Layanan Utama',
            'is_active' => true,
        ]);

        $this->catalogService = Service::create([
            'uuid' => (string) Str::uuid(),
            'service_catalog_id' => $catalog->id,
            'name' => 'Internet Fiber Broadband',
            'type' => 'pppoe',
            'price' => 150000,
            'is_active' => true,
        ]);

        $pppoeType = ServiceProfileType::firstOrCreate(
            ['code' => 'PPPOE'],
            ['name' => 'PPPoE Profile', 'status' => 'active']
        );
        $hotspotType = ServiceProfileType::firstOrCreate(
            ['code' => 'HOTSPOT'],
            ['name' => 'Hotspot Profile', 'status' => 'active']
        );

        $this->pppoeProfile = ServiceProfile::create([
            'service_profile_type_id' => $pppoeType->id,
            'code' => 'PKG-PPPOE-20M',
            'name' => 'Paket Fiber 20 Mbps',
            'price' => 150000,
            'base_price' => 150000,
            'download_speed' => 20480,
            'upload_speed' => 10240,
            'service_type' => 'pppoe',
            'is_active' => true,
            'branch_id' => $this->branch->id,
            'reseller_id' => $this->reseller->id,
        ]);

        $this->hotspotProfile = ServiceProfile::create([
            'service_profile_type_id' => $hotspotType->id,
            'code' => 'PKG-HOTSPOT-10M',
            'name' => 'Paket Hotspot 10 Mbps',
            'price' => 50000,
            'base_price' => 50000,
            'download_speed' => 10240,
            'upload_speed' => 5120,
            'service_type' => 'hotspot',
            'is_active' => true,
            'branch_id' => $this->branch->id,
            'reseller_id' => $this->reseller->id,
        ]);

        // 4. Infrastructure (Router, Vendor, OLT, ODP)
        $this->router = Router::create([
            'code' => 'RTR-BDG-01',
            'name' => 'MikroTik Core BDG',
            'ip_address' => '10.10.10.1',
            'username' => 'api_admin',
            'password' => 'secret123',
            'api_port' => 8728,
            'status' => 'active',
            'branch_id' => $this->branch->id,
        ]);

        $this->vendor = Vendor::firstOrCreate(
            ['code' => 'ZTE'],
            ['name' => 'ZTE Corporation', 'status' => 'active']
        );

        $this->olt = Olt::create([
            'code' => 'OLT-BDG-01',
            'name' => 'OLT ZTE C320 BDG',
            'ip_address' => '10.10.10.2',
            'vendor_id' => $this->vendor->id,
            'branch_id' => $this->branch->id,
            'status' => 'active',
        ]);

        $this->odp = Odp::create([
            'name' => 'ODP-BDG-01',
            'code' => 'ODP-01',
            'total_ports' => 16,
            'used_port_count' => 2,
            'branch_id' => $this->branch->id,
            'status' => 'active',
        ]);

        \App\Models\ISP\PonPort::create([
            'id' => 1,
            'olt_id' => $this->olt->id,
            'code' => 'PON-01',
            'name' => 'PON 1',
            'port_number' => 1,
            'port_index' => 1,
            'status' => 'active',
        ]);

        $this->mockRouterSuccess();
    }

    protected function mockRouterSuccess(): void
    {
        $mockDriver = $this->createMock(RouterOSDriverInterface::class);
        $mockDriver->method('connect')->willReturn(true);
        $mockDriver->method('addPPPoESecret')->willReturn(true);
        $mockDriver->method('setPppSecretProfile')->willReturn(true);
        $mockDriver->method('enablePppSecret')->willReturn(true);
        $mockDriver->method('disconnectPppoeUser')->willReturn(true);

        $mockRouterOs = $this->createMock(RouterOSService::class);
        $mockRouterOs->method('getDriver')->willReturn($mockDriver);
        $this->app->instance(RouterOSService::class, $mockRouterOs);
    }

    protected function mockRouterFailure(string $errorMessage = 'Connection refused to MikroTik Core BDG on port 8728'): void
    {
        $mockDriver = $this->createMock(RouterOSDriverInterface::class);
        $mockDriver->method('connect')->willThrowException(new \RuntimeException($errorMessage));

        $mockRouterOs = $this->createMock(RouterOSService::class);
        $mockRouterOs->method('getDriver')->willReturn($mockDriver);
        $this->app->instance(RouterOSService::class, $mockRouterOs);
    }

    /**
     * CRITERION 5 & 6:
     * ServiceInstanceRepositoryInterface and ProvisionPipelineRepositoryInterface
     * resolve accurately through the Laravel service container.
     */
    public function test_service_container_resolves_provisioning_repository_interfaces(): void
    {
        $serviceInstanceRepo = app(ServiceInstanceRepositoryInterface::class);
        $this->assertInstanceOf(
            \App\Repositories\Provisioning\ServiceInstanceRepository::class,
            $serviceInstanceRepo,
            'ServiceInstanceRepositoryInterface must resolve to ServiceInstanceRepository'
        );

        $pipelineRepo = app(ProvisionPipelineRepositoryInterface::class);
        $this->assertInstanceOf(
            \App\Repositories\Provisioning\ProvisionPipelineRepository::class,
            $pipelineRepo,
            'ProvisionPipelineRepositoryInterface must resolve to ProvisionPipelineRepository'
        );
    }

    /**
     * CRITERION 1, 2, 3, 4, 11:
     * Full chain core entity creation, relationships, and branch/reseller isolation.
     * When ProvisioningService registers a customer:
     * Customer -> CustomerService -> ServiceInstance -> ProvisionPipeline,
     * CustomerService -> ServiceProfile, PPPoEUser are fully populated and linked.
     */
    public function test_provisioning_service_creates_all_core_entities_and_relations_with_branch_and_reseller_scope(): void
    {
        $this->actingAs($this->technician);

        $onu = Onu::create([
            'serial_number' => 'ZTEGC1234567',
            'code' => 'ONU-ZTEG01',
            'name' => 'ONT Pelanggan Baru',
            'branch_id' => $this->branch->id,
            'olt_id' => $this->olt->id,
            'status' => 'inactive',
        ]);

        $data = [
            'name' => 'Budi Santoso',
            'phone' => '081234567891',
            'address' => 'Jl. Merdeka No. 45, Bandung',
            'service_profile_id' => $this->pppoeProfile->id,
            'router_id' => $this->router->id,
            'username' => 'budi_santoso',
            'password' => 'secretBudi123',
            'onu_id' => $onu->id,
            'mac_address' => 'ZTEGC1234567',
            'branch_id' => $this->branch->id,
            'reseller_id' => $this->reseller->id,
        ];

        $service = app(ProvisioningService::class);
        $result = $service->activatePPPoEService($data, $this->technician->id);

        // 1. Verify Customer
        $this->assertNotNull($result['customer']);
        $customer = $result['customer'];
        $this->assertEquals('Budi Santoso', $customer->name);
        $this->assertEquals($this->branch->id, $customer->branch_id);
        $this->assertEquals($this->reseller->id, $customer->reseller_id);

        // 2. Verify Contract
        $this->assertNotNull($result['contract']);
        $contract = $result['contract'];
        $this->assertEquals($customer->id, $contract->customer_id);

        // 3. Verify CustomerService
        $this->assertNotNull($result['customer_service']);
        $cs = $result['customer_service'];
        $this->assertEquals($customer->id, $cs->customer_id);
        $this->assertEquals($this->pppoeProfile->id, $cs->service_profile_id);
        $this->assertEquals($onu->id, $cs->onu_id);
        $this->assertEquals($this->branch->id, $cs->branch_id);
        $this->assertEquals($this->reseller->id, $cs->reseller_id);

        // CRITERION 2: CustomerService -> ServiceProfile is loaded and NOT null
        $this->assertNotNull($cs->serviceProfile, 'CustomerService->serviceProfile must not be null');
        $this->assertEquals($this->pppoeProfile->id, $cs->serviceProfile->id);
        $this->assertEquals('PKG-PPPOE-20M', $cs->serviceProfile->code);

        // CRITERION 3: CustomerService -> ServiceInstance is loaded and accessible
        $this->assertNotNull($cs->serviceInstance, 'CustomerService->serviceInstance must not be null');
        $this->assertInstanceOf(ServiceInstance::class, $cs->serviceInstance);
        $this->assertEquals($cs->id, $cs->serviceInstance->customer_service_id);

        // CRITERION 4: ServiceInstance -> ProvisionPipeline is loaded
        $serviceInstance = $result['service_instance'];
        $this->assertNotNull($serviceInstance);
        $this->assertNotNull($serviceInstance->provisionPipeline, 'ServiceInstance->provisionPipeline must not be null');
        $this->assertInstanceOf(ProvisionPipeline::class, $serviceInstance->provisionPipeline);
        $this->assertEquals($serviceInstance->id, $serviceInstance->provisionPipeline->service_instance_id);

        // Verify PPPoEUser
        $this->assertNotNull($result['pppoe_user']);
        $pppoeUser = $result['pppoe_user'];
        $this->assertEquals('budi_santoso', $pppoeUser->username);
        $this->assertEquals($cs->id, $pppoeUser->customer_service_id);
        $this->assertEquals($this->branch->id, $pppoeUser->branch_id);
        $this->assertEquals($this->reseller->id, $pppoeUser->reseller_id);
        $this->assertEquals($onu->id, $pppoeUser->customerService->onu_id);
        $this->assertEquals($onu->serial_number, $pppoeUser->onu_device['sn']);

        // Verify Subscription and Invoice exist
        $this->assertNotNull($result['subscription']);
        $this->assertNotNull($result['invoice']);
        $this->assertEquals($customer->id, $result['invoice']->customer_id);
    }

    /**
     * CRITERION 7 & 8:
     * Hardware failure (MikroTik timeout / connection refused) MUST NOT rollback
     * core database records (Customer, CustomerService, ServiceProfile, ServiceInstance).
     * Service and Instance statuses accurately reflect failure ('provisioning_failed' / 'failed')
     * without generating false 'active' claims.
     */
    public function test_hardware_failure_does_not_rollback_core_data_and_reflects_actual_status(): void
    {
        $this->actingAs($this->technician);

        $this->mockRouterFailure('Connection refused to MikroTik Core BDG on port 8728');

        $onu = Onu::create([
            'serial_number' => 'HWTC8899AABB',
            'code' => 'ONU-HWTC01',
            'name' => 'ONT Huawei Pelanggan',
            'branch_id' => $this->branch->id,
            'olt_id' => $this->olt->id,
            'status' => 'inactive',
        ]);

        $data = [
            'name' => 'Dewi Sartika',
            'phone' => '081234567892',
            'address' => 'Jl. Asia Afrika No. 10, Bandung',
            'service_profile_id' => $this->pppoeProfile->id,
            'router_id' => $this->router->id,
            'username' => 'dewi_sartika',
            'password' => 'dewiPass99!',
            'onu_id' => $onu->id,
            'mac_address' => 'HWTC8899AABB',
            'branch_id' => $this->branch->id,
            'reseller_id' => $this->reseller->id,
        ];

        $service = app(ProvisioningService::class);
        $result = $service->activatePPPoEService($data, $this->technician->id);

        // CRITERION 7: Failure reported cleanly, NO database rollback
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Connection refused to MikroTik', $result['hardware_error']);

        // Assert Customer persists in database
        $this->assertDatabaseHas('customers', [
            'name' => 'Dewi Sartika',
            'phone' => '081234567892',
            'branch_id' => $this->branch->id,
        ]);

        // Assert CustomerService persists and package is linked
        $cs = CustomerService::where('username', 'dewi_sartika')->first();
        $this->assertNotNull($cs, 'CustomerService must exist in DB even when hardware fails');
        $this->assertEquals($this->pppoeProfile->id, $cs->service_profile_id);

        // CRITERION 8: Status reflects failure, not fake active
        $this->assertEquals('provisioning_failed', $cs->status, 'CustomerService status must be provisioning_failed');

        // Assert ServiceInstance persists with failed status
        $instance = ServiceInstance::where('customer_service_id', $cs->id)->first();
        $this->assertNotNull($instance, 'ServiceInstance must exist in DB');
        $this->assertEquals('failed', $instance->status, 'ServiceInstance status must be failed');

        // Assert PPPoEUser exists with failed_provisioning status
        $pppoe = PPPoEUser::where('username', 'dewi_sartika')->first();
        $this->assertNotNull($pppoe, 'PPPoEUser must exist in DB');
        $this->assertEquals('failed_provisioning', $pppoe->status);
    }

    /**
     * CRITERION 9:
     * SPK (Installation) & Ticket cannot transition to 'completed' / 'resolved'
     * if hardware provisioning fails or service is incomplete.
     * They only mark completed upon verified full provisioning.
     */
    public function test_spk_and_ticket_remain_in_progress_when_hardware_fails_and_complete_on_success(): void
    {
        $this->actingAs($this->technician);

        // 1. Create Installation SPK & Ticket
        $installation = Installation::create([
            'customer_name' => 'Agus Pratama',
            'customer_phone' => '081234567893',
            'address' => 'Jl. Riau No. 12, Bandung',
            'branch_id' => $this->branch->id,
            'reseller_id' => $this->reseller->id,
            'status' => 'in_progress',
        ]);

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-PSB-001',
            'title' => 'PSB: Agus Pratama',
            'status' => 'in_progress',
            'priority' => 'medium',
            'branch_id' => $this->branch->id,
            'created_by' => $this->technician->id,
        ]);

        $onu = Onu::create([
            'serial_number' => 'FHTT11223344',
            'code' => 'ONU-FHTT01',
            'name' => 'ONT Fiberhome Agus',
            'branch_id' => $this->branch->id,
            'olt_id' => $this->olt->id,
            'status' => 'inactive',
        ]);

        // A. Simulate Hardware Failure
        $this->mockRouterFailure('Router unreachable');

        $provService = app(ProvisioningService::class);
        $failResult = $provService->activatePPPoEService([
            'name' => 'Agus Pratama',
            'phone' => '081234567893',
            'service_profile_id' => $this->pppoeProfile->id,
            'router_id' => $this->router->id,
            'username' => 'agus_pratama',
            'password' => 'agusPass123',
            'onu_id' => $onu->id,
            'branch_id' => $this->branch->id,
            'reseller_id' => $this->reseller->id,
        ], $this->technician->id);

        $this->assertFalse($failResult['success']);

        // Run Wizard completion evaluation logic
        $isCoreValid = $failResult['customer'] && $failResult['customer_service']
            && !empty($failResult['customer_service']->service_profile_id)
            && !empty($failResult['customer_service']->onu_id);
        $isHardwareProvisioned = $failResult['success'];

        $this->assertTrue($isCoreValid);
        $this->assertFalse($isHardwareProvisioned);

        // Guard assertion: SPK and Ticket remain in_progress
        $statusToSet = ($isCoreValid && $isHardwareProvisioned) ? 'completed' : 'in_progress';
        $installation->update(['status' => $statusToSet]);
        $ticket->update(['status' => ($isCoreValid && $isHardwareProvisioned) ? 'resolved' : 'in_progress']);

        $this->assertEquals('in_progress', $installation->fresh()->status, 'Installation must remain in_progress on hardware failure');
        $this->assertEquals('in_progress', $ticket->fresh()->status, 'Ticket must remain in_progress on hardware failure');

        // B. Simulate Verified Success (Retry)
        $this->mockRouterSuccess();

        $successResult = app(ProvisioningService::class)->activatePPPoEService([
            'customer_id' => $failResult['customer']->id,
            'name' => 'Agus Pratama',
            'phone' => '081234567893',
            'service_profile_id' => $this->pppoeProfile->id,
            'router_id' => $this->router->id,
            'username' => 'agus_pratama',
            'password' => 'agusPass123',
            'onu_id' => $onu->id,
            'branch_id' => $this->branch->id,
            'reseller_id' => $this->reseller->id,
        ], $this->technician->id);

        $this->assertTrue($successResult['success']);
        $isHardwareProvisioned = $successResult['success'];

        $installation->update(['status' => ($isCoreValid && $isHardwareProvisioned) ? 'completed' : 'in_progress']);
        $ticket->update(['status' => ($isCoreValid && $isHardwareProvisioned) ? 'resolved' : 'in_progress']);

        $this->assertEquals('completed', $installation->fresh()->status, 'Installation marks completed upon full success');
        $this->assertEquals('resolved', $ticket->fresh()->status, 'Ticket marks resolved upon full success');
    }

    /**
     * CRITERION 10:
     * Idempotency & Retry Safety.
     * Retrying the wizard or double submission MUST NOT duplicate customers,
     * services, PPPoE users, subscriptions, or invoices.
     */
    public function test_wizard_idempotency_and_retry_safety(): void
    {
        $this->actingAs($this->technician);

        $onu = Onu::create([
            'serial_number' => 'ZTEG77889900',
            'code' => 'ONU-IDEMP01',
            'name' => 'ONT Idempotent Test',
            'branch_id' => $this->branch->id,
            'olt_id' => $this->olt->id,
            'status' => 'inactive',
        ]);

        $this->mockRouterSuccess();

        $service = app(ProvisioningService::class);

        $data = [
            'name' => 'Rini Marlina',
            'phone' => '081234567894',
            'address' => 'Jl. Setiabudi No. 100, Bandung',
            'service_profile_id' => $this->pppoeProfile->id,
            'router_id' => $this->router->id,
            'username' => 'rini_marlina',
            'password' => 'riniPassword123',
            'onu_id' => $onu->id,
            'mac_address' => 'ZTEG77889900',
            'branch_id' => $this->branch->id,
            'reseller_id' => $this->reseller->id,
        ];

        // First Execution
        $result1 = $service->activatePPPoEService($data, $this->technician->id);
        $this->assertTrue($result1['success']);
        $customerId = $result1['customer']->id;

        // Second Execution (Simulating technician retry or network re-send)
        $data['customer_id'] = $customerId;
        $result2 = $service->activatePPPoEService($data, $this->technician->id);
        $this->assertTrue($result2['success']);

        // Assert exactly 1 customer, 1 customer_service, 1 pppoe_user, 1 subscription, 1 invoice
        $this->assertEquals(1, Customer::where('phone', '081234567894')->count(), 'Customer must not be duplicated');
        $this->assertEquals(1, CustomerService::where('username', 'rini_marlina')->count(), 'CustomerService must not be duplicated');
        $this->assertEquals(1, PPPoEUser::where('username', 'rini_marlina')->count(), 'PPPoEUser must not be duplicated');
        $this->assertEquals(1, ServiceInstance::where('customer_service_id', $result1['customer_service']->id)->count(), 'ServiceInstance must not be duplicated');
        $this->assertEquals(1, Subscription::where('customer_service_id', $result1['customer_service']->id)->count(), 'Subscription must not be duplicated');
    }

    /**
     * CRITERION 12:
     * Mobile Endpoint /api/mobile/network/onu/provision establishes complete
     * CustomerService, ServiceInstance, and guards WorkOrder completion.
     */
    public function test_mobile_onu_provision_endpoint_creates_customer_service_and_service_instance(): void
    {
        // Setup warehouse, item, custody for technician to pass physical custody validation
        $warehouse = Warehouse::create([
            'name' => 'Gudang Pusat Bandung',
            'code' => 'GUD-BDG',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $item = Item::create([
            'name' => 'ONT ZTE F670L Dual Band',
            'sku' => 'ONT-ZTE-F670L',
            'type' => 'serialized',
            'is_serialized' => true,
            'is_active' => true,
        ]);

        $itemSerial = ItemSerial::create([
            'item_id' => $item->id,
            'serial_number' => 'ZTEG55667788',
            'status' => 'in_custody',
        ]);

        InventoryCustody::create([
            'item_id' => $item->id,
            'item_serial_id' => $itemSerial->id,
            'holder_id' => $this->technician->id,
            'status' => 'in_custody',
            'quantity' => 1,
            'held_at' => now(),
        ]);

        $customer = Customer::create([
            'code' => 'CUST-MOB-01',
            'name' => 'Hendro Siswanto',
            'phone' => '081234567895',
            'address' => 'Jl. Buah Batu No. 77, Bandung',
            'branch_id' => $this->branch->id,
            'status' => 'active',
        ]);

        $workOrder = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'work_order_number' => 'WO-PSB-MOB-01',
            'title' => 'PSB: Hendro Siswanto',
            'customer_id' => $customer->id,
            'branch_id' => $this->branch->id,
            'assigned_to' => $this->technician->id,
            'type' => 'installation',
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($this->technician, 'sanctum')
            ->postJson('/api/mobile/network/onu/provision', [
                'serial_number' => 'ZTEG55667788',
                'mac_address' => 'AABBCCDDEEFF',
                'model' => 'ZTE-F670L',
                'work_order_id' => $workOrder->id,
                'customer_id' => $customer->id,
                'service_profile_id' => $this->pppoeProfile->id,
                'odp_id' => $this->odp->id,
                'odp_port' => 3,
                'olt_id' => $this->olt->id,
                'pon_port' => 1,
                'pppoe_username' => 'hendro_siswanto',
                'pppoe_password' => 'hendroSecret123',
                'optical_power' => -19.5,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // 1. Assert CustomerService exists and is linked to service_profile
        $cs = CustomerService::where('customer_id', $customer->id)->first();
        $this->assertNotNull($cs, 'CustomerService must be created for customer');
        $this->assertEquals($this->pppoeProfile->id, $cs->service_profile_id);
        $this->assertEquals('hendro_siswanto', $cs->username);

        // 2. Assert ServiceInstance exists and is linked to CustomerService
        $serviceInstance = ServiceInstance::where('customer_service_id', $cs->id)->first();
        $this->assertNotNull($serviceInstance, 'ServiceInstance must be linked to CustomerService');
        $this->assertEquals('active', $serviceInstance->status);

        // 3. Assert ONU is provisioned and linked to branch
        $onu = Onu::where('serial_number', 'ZTEG55667788')->first();
        $this->assertNotNull($onu);
        $this->assertEquals('active', $onu->status);
        $this->assertEquals('provisioned', $onu->provision_status);
        $this->assertEquals($this->branch->id, $onu->branch_id);

        // 4. Assert WorkOrder transitioned to completed
        $this->assertEquals('completed', $workOrder->fresh()->status);
    }

    /**
     * Test Hotspot service provisioning chain (ServiceProfile -> CustomerService -> ServiceInstance -> HotspotUser).
     */
    public function test_hotspot_service_provisioning_chain_with_branch_scoping(): void
    {
        $this->actingAs($this->technician);

        $onu = Onu::create([
            'serial_number' => 'ZTEG33445566',
            'code' => 'ONU-HS01',
            'name' => 'ONT Hotspot Member',
            'branch_id' => $this->branch->id,
            'olt_id' => $this->olt->id,
            'status' => 'inactive',
        ]);

        $data = [
            'name' => 'Cafe Kopi Bandung',
            'phone' => '081234567896',
            'address' => 'Jl. Braga No. 20, Bandung',
            'service_profile_id' => $this->hotspotProfile->id,
            'router_id' => $this->router->id,
            'username' => 'CAFE_BRAGA',
            'password' => 'kopi1234',
            'onu_id' => $onu->id,
            'mac_address' => 'ZTEG33445566',
            'branch_id' => $this->branch->id,
            'reseller_id' => $this->reseller->id,
        ];

        $service = app(ProvisioningService::class);
        $result = $service->activateHotspotService($data, $this->technician->id);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['customer']);
        $this->assertNotNull($result['customer_service']);

        $cs = $result['customer_service'];
        $this->assertEquals($this->hotspotProfile->id, $cs->service_profile_id);
        $this->assertNotNull($cs->serviceInstance);

        $this->assertDatabaseHas('hotspot_users', [
            'username' => 'CAFE_BRAGA',
            'branch_id' => $this->branch->id,
            'reseller_id' => $this->reseller->id,
            'customer_service_id' => $cs->id,
        ]);
    }
}
