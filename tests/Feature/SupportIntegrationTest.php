<?php

namespace Tests\Feature;

use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\CRM\Prospect;
use App\Models\CRM\Survey;
use App\Models\Customer\CustomerService;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\ServiceProfile;
use App\Models\Provisioning\NetworkProfile;
use App\Models\Support\SupportMaintenance;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Services\Provisioning\ProvisioningService;
use App\Services\Support\MaintenanceWorkOrderService;
use App\Services\Support\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cs_ticket_creation_with_technician_auto_creates_maintenance_wo(): void
    {
        $tech = User::factory()->create();
        $customer = Customer::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Budi Santoso',
            'code' => 'CUST-001',
            'phone' => '08123456789',
            'status' => 'active',
        ]);

        $ticketService = app(TicketService::class);
        $ticket = $ticketService->create([
            'customer_id' => $customer->id,
            'title' => 'Indikator LOS Merah Kedip-kedip',
            'description' => 'Kabel dropcore putus tertimpa dahan pohon',
            'category' => 'internet',
            'priority' => 'high',
            'assigned_to' => $tech->id,
            'status' => 'open',
        ], $tech->id);

        $this->assertNotNull($ticket->id);
        $this->assertEquals($tech->id, $ticket->assigned_to);

        // Assert that Maintenance WO was auto-created and linked
        $wo = SupportMaintenance::where('ticket_id', $ticket->id)->first();
        $this->assertNotNull($wo, 'Maintenance WO harus otomatis terbuat saat tiket lapangan di-assign');
        $this->assertEquals($ticket->id, $wo->ticket_id);
        $this->assertEquals($tech->id, $wo->assigned_to);
        $this->assertEquals('scheduled', $wo->status);
        $this->assertEquals('Budi Santoso', $wo->customer_name);
    }

    public function test_remote_ticket_does_not_create_maintenance_wo(): void
    {
        $tech = User::factory()->create();
        $customer = Customer::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Siti Aminah',
            'code' => 'CUST-002',
            'phone' => '08123456780',
            'status' => 'active',
        ]);

        $ticketService = app(TicketService::class);
        $ticket = $ticketService->create([
            'customer_id' => $customer->id,
            'title' => 'Pertanyaan Rincian Tagihan Bulanan',
            'description' => 'Pelanggan ingin mengetahui kenapa ada denda keterlambatan',
            'category' => 'billing',
            'priority' => 'low',
            'assigned_to' => $tech->id,
            'status' => 'open',
        ], $tech->id);

        $wo = SupportMaintenance::where('ticket_id', $ticket->id)->first();
        $this->assertNull($wo, 'Tiket billing/administrasi jarak jauh tidak boleh membuat Maintenance WO lapangan');
    }

    public function test_reassigning_technician_updates_existing_wo_without_duplication(): void
    {
        $tech1 = User::factory()->create();
        $tech2 = User::factory()->create();

        $customer = Customer::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Ahmad Fauzi',
            'code' => 'CUST-003',
            'phone' => '08123456781',
            'status' => 'active',
        ]);

        $ticketService = app(TicketService::class);
        $ticket = $ticketService->create([
            'customer_id' => $customer->id,
            'title' => 'Redaman Optik Drop (-32 dBm)',
            'description' => 'Sinyal drop drastis butuh splicing ulang',
            'category' => 'optical',
            'priority' => 'critical',
            'assigned_to' => $tech1->id,
            'status' => 'open',
        ], $tech1->id);

        $this->assertEquals(1, SupportMaintenance::where('ticket_id', $ticket->id)->count());

        // Reassign to tech2
        $ticketService->assign($ticket->id, $tech2->id, $tech1->id);

        // Count must still be 1 (No duplicate)
        $this->assertEquals(1, SupportMaintenance::where('ticket_id', $ticket->id)->count());
        $updatedWo = SupportMaintenance::where('ticket_id', $ticket->id)->first();
        $this->assertEquals($tech2->id, $updatedWo->assigned_to);
    }

    public function test_resolving_maintenance_auto_resolves_parent_ticket(): void
    {
        $tech = User::factory()->create();
        $customer = Customer::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Joko Anwar',
            'code' => 'CUST-004',
            'phone' => '08123456782',
            'status' => 'active',
        ]);

        $ticketService = app(TicketService::class);
        $ticket = $ticketService->create([
            'customer_id' => $customer->id,
            'title' => 'Modem Mati Terkena Petir',
            'description' => 'Perlu ganti adaptor atau unit ONT',
            'category' => 'hardware',
            'priority' => 'high',
            'assigned_to' => $tech->id,
            'status' => 'open',
        ], $tech->id);

        $wo = SupportMaintenance::where('ticket_id', $ticket->id)->first();
        $this->assertNotNull($wo);

        // Resolve maintenance via service
        $maintService = app(MaintenanceWorkOrderService::class);
        $maintService->resolveMaintenanceAndTicket($wo->id, 'Adaptor 12V 1.5A diganti baru, internet normal', $tech->id);

        $ticket->refresh();
        $this->assertEquals('resolved', $ticket->status);
        $this->assertStringContainsString('Adaptor 12V', $ticket->resolution_notes);
    }

    public function test_activation_modal_blocks_when_installation_is_not_completed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $prospect = Prospect::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Calon Pelanggan Baru',
            'phone' => '08999999999',
            'status' => 'new',
        ]);

        $survey = Survey::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'prospect_id' => $prospect->id,
            'status' => 'completed',
            'recommendation' => 'feasible',
        ]);

        // Create installation with status 'scheduled' (not yet completed)
        $inst = Installation::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'survey_id' => $survey->id,
            'customer_name' => $prospect->name,
            'status' => 'scheduled',
        ]);

        $component = new \App\Livewire\Crm\Activation\Index();
        $component->openActivationModal($survey->id);

        // Must not open modal and flash error
        $this->assertFalse($component->showActivationModal);
        $this->assertNotEmpty(session('error'));
        $this->assertStringContainsStringIgnoringCase('instalasi fisik', session('error'));
    }

    public function test_activation_pppoe_failure_throws_and_marks_failed_provisioning(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Doni Siregar',
            'code' => 'CUST-005',
            'phone' => '08123456783',
            'status' => 'active',
        ]);

        $contract = \App\Models\CRM\Contract::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'customer_id' => $customer->id,
            'contract_number' => 'CTR-001',
            'status' => 'active',
            'start_date' => now(),
        ]);

        $catalog = \App\Models\ServiceCatalog\ServiceCatalog::firstOrCreate(
            ['name' => 'Katalog Layanan'],
            ['uuid' => (string) \Illuminate\Support\Str::uuid(), 'is_active' => true, 'created_by' => $user->id, 'updated_by' => $user->id]
        );

        $service = \App\Models\ServiceCatalog\Service::firstOrCreate(
            ['name' => 'Default Service'],
            ['uuid' => (string) \Illuminate\Support\Str::uuid(), 'service_catalog_id' => $catalog->id, 'price' => 100000, 'type' => 'pppoe', 'is_active' => true, 'created_by' => $user->id, 'updated_by' => $user->id]
        );

        $typeId = \Illuminate\Support\Facades\DB::table('service_profile_types')->insertGetId([
            'name' => 'Broadband',
            'code' => 'bb',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $profile = ServiceProfile::create([
            'name' => '50Mbps',
            'code' => 'SP-50M',
            'service_profile_type_id' => $typeId,
            'service_type' => 'pppoe',
            'base_price' => 100000,
            'status' => 'active',
            'download_speed' => 50,
            'upload_speed' => 50,
            'created_by' => $user->id,
            'updated_by' => $user->id
        ]);

        $cs = CustomerService::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'customer_id' => $customer->id,
            'contract_id' => $contract->id,
            'service_id' => $service->id,
            'service_profile_id' => $profile->id,
            'username' => 'doni@test',
            'password' => 'secret123',
            'status' => 'pending',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        // Create an active router with invalid port/IP to simulate hardware failure
        $router = \App\Models\ISP\Router::create([
            'name' => 'Core-Unreachable-Router',
            'code' => 'RTR-CORE-01',
            'ip_address' => '192.0.2.1', // TEST-NET-1 (blackhole / unreachable)
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'wrong',
            'status' => 'active',
        ]);

        $pppoeService = app(\App\Services\ISP\PPPoEService::class);
        $pppoeUser = PPPoEUser::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'customer_service_id' => $cs->id,
            'service_profile_id' => $profile->id,
            'username' => 'doni@test',
            'password' => 'secret123',
            'router_id' => $router->id,
            'status' => 'pending',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        try {
            $pppoeService->activatePPPoEUser($pppoeUser->id, $user->id);
            $this->fail('Harus melempar RuntimeException saat router tidak dapat dihubungi');
        } catch (\Throwable $e) {
            $this->assertStringContainsString('router', strtolower($e->getMessage()));
        }

        $pppoeUser->refresh();
        // Crucial validation: Must NOT be falsely marked as 'active'
        $this->assertNotEquals('active', $pppoeUser->status);
        $this->assertContains($pppoeUser->status, ['pending', 'failed_provisioning']);
    }

    public function test_who_aggregates_all_four_work_order_entities(): void
    {
        $tech = User::factory()->create();
        $this->actingAs($tech);

        $customer = Customer::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Eko Prasetyo',
            'code' => 'CUST-006',
            'phone' => '08123456784',
            'status' => 'active',
        ]);

        // 1. Installation
        Installation::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'status' => 'completed',
            'completed_at' => now(),
            'assigned_to' => $tech->id,
        ]);

        // 2. Ticket
        Ticket::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'customer_id' => $customer->id,
            'title' => 'Gangguan WiFi Lambat',
            'category' => 'wifi',
            'status' => 'resolved',
            'resolved_at' => now(),
            'assigned_to' => $tech->id,
        ]);

        // 3. Maintenance
        SupportMaintenance::create([
            'title' => 'Perapian Kabel ODP-01',
            'type' => 'preventive',
            'status' => 'completed',
            'completed_at' => now(),
            'assigned_to' => $tech->id,
            'device_type' => 'odp',
            'device_name' => 'ODP-01',
        ]);

        $whoComponent = new \App\Livewire\Support\Installation\WorkOrderHistory();
        $whoComponent->mount();
        $viewData = $whoComponent->render()->getData();

        $items = $viewData['items'];
        $this->assertGreaterThanOrEqual(3, $items->count());

        $types = collect($items->items())->pluck('type')->unique()->values()->all();
        $this->assertContains('installation', $types);
        $this->assertContains('repair', $types);
        $this->assertContains('maintenance', $types);
    }
}
