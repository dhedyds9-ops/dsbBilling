<?php

namespace Tests\Feature;

use App\Livewire\Isp\Technician\MyJobs\Index;
use App\Models\CRM\Customer;
use App\Models\Support\SupportMaintenance;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Services\Support\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TechnicianJobsDeduplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_with_field_maintenance_wo_is_deduplicated_and_single_click_resolved(): void
    {
        // 1. Setup User Technician
        $tech = User::factory()->create([
            'name' => 'Teknisi Lapangan',
            'email' => 'teknisi@winets.id',
            'job_function' => 'TECHNICIAN',
        ]);

        $customer = Customer::create([
            'name' => 'Bpk Sukirman',
            'phone' => '081299998888',
            'address' => 'Desa Karanganyar RT 01 RW 02',
            'status' => 'active',
        ]);

        // 2. Buat Tiket Gangguan Internet melalui TicketService
        $ticketService = app(TicketService::class);
        $ticket = $ticketService->create([
            'customer_id' => $customer->id,
            'title' => 'Internet Putus / LOS Merah',
            'description' => 'Lampu PON berkedip merah kabel drop putus',
            'category' => 'internet',
            'priority' => 'critical',
            'assigned_to' => $tech->id,
            'requires_field_visit' => true,
        ], $tech->id);

        // Pastikan SupportMaintenance otomatis terbuat dari TicketService
        $this->assertDatabaseHas('support_maintenances', [
            'ticket_id' => $ticket->id,
            'source' => 'ticket',
            'type' => 'corrective',
            'status' => 'scheduled',
        ]);

        $maintenance = SupportMaintenance::where('ticket_id', $ticket->id)->first();
        $this->assertNotNull($maintenance);

        // 3. Uji tampilan pada portal teknisi MyJobs/Index
        $this->actingAs($tech);

        $component = Livewire::test(Index::class);

        // Dapatkan data jobs dari component
        $jobs = $component->get('jobs');

        // Pastikan HANYA ADA 1 tugas yang tampil di daftar (Tiket), bukan 2 tugas (Tiket + Maintenance Corrective)
        $this->assertCount(1, $jobs);
        $firstJob = $jobs->first();
        $this->assertEquals('ticket_' . $ticket->id, $firstJob->id);
        $this->assertEquals('TIKET', $firstJob->service_type);

        // Pastikan statistik tugas aktif hanya bernilai 1 (bukan 2)
        $stats = $component->get('stats');
        $this->assertEquals(1, $stats['active']);
        $this->assertEquals(1, $stats['active_tickets']);
        $this->assertEquals(0, $stats['active_maintenances']); // Maintenance dari tiket tidak dihitung dobel

        // 4. Teknisi melakukan 1x klik penyelesaikan tiket (confirmResolveTicket)
        $component->call('openResolveTicketModal', $ticket->id)
            ->set('ticket_resolution_notes', 'Penyambungan kabel dropcore selesai, redaman -19 dBm')
            ->call('confirmResolveTicket');

        // 5. Pastikan Tiket menjadi resolved
        $this->assertEquals('resolved', $ticket->fresh()->status);
        $this->assertNotNull($ticket->fresh()->resolved_at);

        // 6. Pastikan SupportMaintenance OTOMATIS terselesaikan (completed) tanpa perlu teknisi klik 2x!
        $freshMaintenance = $maintenance->fresh();
        $this->assertEquals('completed', $freshMaintenance->status);
        $this->assertNotNull($freshMaintenance->completed_at);
        $this->assertStringContainsString('Penyambungan kabel dropcore', $freshMaintenance->resolution_notes);

        // 7. Uji jika tiket dibuka kembali (reopen) ke in_progress, status maintenance ikut sinkron
        $component->call('openEditTicketResolutionModal', $ticket->id)
            ->set('ticket_edit_status', 'in_progress')
            ->call('saveTicketResolutionUpdate');

        $this->assertEquals('in_progress', $ticket->fresh()->status);
        $this->assertEquals('in_progress', $maintenance->fresh()->status);
    }
}
