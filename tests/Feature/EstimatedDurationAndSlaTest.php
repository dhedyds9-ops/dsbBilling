<?php

namespace Tests\Feature;

use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\Support\SupportMaintenance;
use App\Models\Support\Ticket;
use App\Services\Notifications\WhatsApp\WaTemplateEngine;
use App\Services\Support\SlaTrackingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimatedDurationAndSlaTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_estimated_duration_formatting_and_sla_initialization(): void
    {
        $customer = Customer::create([
            'name' => 'Bpk Ahmad',
            'phone' => '081234567890',
            'address' => 'Jl. Mawar No. 10',
            'status' => 'active',
        ]);

        $ticket = Ticket::create([
            'customer_id' => $customer->id,
            'title' => 'Internet Loss Merah',
            'description' => 'Redaman kabel drop',
            'category' => 'internet',
            'priority' => 'critical',
            'estimated_hours' => 2,
            'status' => 'open',
        ]);

        $this->assertEquals('2 Jam', $ticket->formatEstimatedDuration());

        $slaService = app(SlaTrackingService::class);
        $slaService->initializeSla($ticket);

        $this->assertEquals(120, $ticket->fresh()->sla_target_minutes);
        $this->assertNotNull($ticket->fresh()->sla_due_at);

        $payload = $ticket->getNotificationPayload();
        $this->assertEquals('2 Jam', $payload['estimated_duration']);
        $this->assertNotNull($payload['sla_due_at']);

        $template = app(WaTemplateEngine::class);
        $waCreated = $template->ticketCreated($payload);
        $this->assertStringContainsString('⏳ Estimasi Pengerjaan: *2 Jam*', $waCreated);
        $this->assertStringContainsString('🎯 Target Selesai (SLA):', $waCreated);

        $waAssigned = $template->ticketAssigned($payload);
        $this->assertStringContainsString('⏳ Estimasi Pengerjaan: *2 Jam*', $waAssigned);
        $this->assertStringContainsString('🎯 Target Selesai (SLA):', $waAssigned);
    }

    public function test_installation_psb_estimated_duration_and_wa_template(): void
    {
        $customer = Customer::create([
            'name' => 'Ibu Siti',
            'phone' => '081298765432',
            'address' => 'Jl. Melati No. 5',
            'status' => 'active',
        ]);

        $installation = Installation::create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '10:00',
            'priority' => 'high',
            'estimated_hours' => 4,
            'status' => 'scheduled',
        ]);

        $this->assertEquals('4 Jam', $installation->formatEstimatedDuration());

        $slaService = app(SlaTrackingService::class);
        $slaService->initializeSla($installation);

        $this->assertEquals(240, $installation->fresh()->sla_target_minutes);

        $template = app(WaTemplateEngine::class);
        $waText = $template->installationCreated([
            'wo_id' => $installation->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'customer_address' => $customer->address,
            'scheduled_date' => $installation->scheduled_date,
            'scheduled_time' => $installation->scheduled_time,
            'technician_name' => 'Budi Teknisi',
            'priority' => 'high',
            'estimated_duration' => $installation->formatEstimatedDuration(),
            'sla_due_at' => $installation->fresh()->sla_due_at->format('d/m/Y H:i'),
        ]);

        $this->assertStringContainsString('⏳ Estimasi Pengerjaan: *4 Jam*', $waText);
        $this->assertStringContainsString('🎯 Target Selesai (SLA):', $waText);
    }

    public function test_maintenance_estimated_duration_and_wa_template(): void
    {
        $maint = SupportMaintenance::create([
            'title' => 'Pemeliharaan Core Switch POP',
            'type' => 'preventive',
            'priority' => 'medium',
            'device_type' => 'router',
            'device_name' => 'CCR1036',
            'location_name' => 'POP Cibadak',
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '01:00',
            'estimated_hours' => 24,
            'status' => 'scheduled',
        ]);

        $this->assertEquals('1 Hari', $maint->formatEstimatedDuration());

        $slaService = app(SlaTrackingService::class);
        $slaService->initializeSla($maint);

        $this->assertEquals(1440, $maint->fresh()->sla_target_minutes);

        $template = app(WaTemplateEngine::class);
        $waText = $template->maintenanceAssigned([
            'maintenance_id' => $maint->id,
            'technician_name' => 'NOC Team',
            'title' => $maint->title,
            'type' => 'preventive',
            'priority' => 'medium',
            'device_name' => 'CCR1036',
            'location_name' => 'POP Cibadak',
            'scheduled_date' => $maint->scheduled_date,
            'scheduled_time' => '01:00',
            'estimated_duration' => $maint->formatEstimatedDuration(),
            'sla_due_at' => $maint->fresh()->sla_due_at->format('d/m/Y H:i'),
        ]);

        $this->assertStringContainsString('🔧 *JADWAL MAINTENANCE: #MT-' . $maint->id . '*', $waText);
        $this->assertStringContainsString('⏳ Estimasi Pengerjaan: *1 Hari*', $waText);
        $this->assertStringContainsString('🎯 Target Selesai (SLA):', $waText);
    }
}
