<?php

namespace Tests\Feature\Support;

use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\Notification\Notification;
use App\Models\Master\Branch;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Services\CRM\InstallationTechnicianNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Src\Domain\Support\Events\TicketAssignedEvent;
use Tests\TestCase;

class MultiTechnicianNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected $branch;
    protected $tech1;
    protected $tech2;
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create(['name' => 'Cabang Utama', 'code' => 'MAIN']);

        $this->tech1 = User::factory()->create([
            'name' => 'Budi Santoso',
            'whatsapp' => '081234567891',
            'job_function' => 'TECHNICIAN',
            'branch_id' => $this->branch->id,
        ]);

        $this->tech2 = User::factory()->create([
            'name' => 'Joko Widodo',
            'whatsapp' => '081234567892',
            'job_function' => 'TECHNICIAN',
            'branch_id' => $this->branch->id,
        ]);

        $this->customer = Customer::create([
            'branch_id' => $this->branch->id,
            'name' => 'Pak Ahmad',
            'code' => 'CUST-001',
            'phone' => '081299998888',
            'address' => 'Jl. Merdeka No. 10',
            'status' => 'active',
        ]);
    }

    public function test_ticket_payload_combines_multiple_technicians_names()
    {
        $ticket = Ticket::create([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'title' => 'Kabel FO Putus',
            'description' => 'Kabel fiber optik putus tertimpa pohon',
            'priority' => 'high',
            'category' => 'network',
            'assigned_to' => $this->tech1->id,
            'technician_ids' => [$this->tech1->id, $this->tech2->id],
            'status' => 'in_progress',
        ]);

        $payload = $ticket->getNotificationPayload();

        $this->assertEquals('Budi Santoso & Joko Widodo', $payload['technician_name']);
        $this->assertCount(2, $payload['technicians']);
        $this->assertEquals($this->tech1->id, $payload['technicians'][0]['id']);
        $this->assertEquals($this->tech2->id, $payload['technicians'][1]['id']);
    }

    public function test_ticket_assigned_listener_notifies_all_assigned_technicians()
    {
        $ticket = Ticket::create([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'title' => 'Redaman Tinggi',
            'description' => 'Redaman drop ke -32 dBm',
            'priority' => 'high',
            'category' => 'internet',
            'assigned_to' => $this->tech1->id,
            'technician_ids' => [$this->tech1->id, $this->tech2->id],
            'status' => 'in_progress',
        ]);

        $event = TicketAssignedEvent::create(
            (string) $ticket->id,
            (string) $this->tech1->id,
            (string) $this->tech1->id,
        );

        $listener = app(\App\Listeners\Support\TicketAssignedListener::class);
        $listener->handle($event);

        // Verify in-app notifications created for BOTH technicians
        $notifTech1 = Notification::where('recipient_id', $this->tech1->id)->first();
        $notifTech2 = Notification::where('recipient_id', $this->tech2->id)->first();

        $this->assertNotNull($notifTech1, 'In-app notification for Tech 1 should exist');
        $this->assertNotNull($notifTech2, 'In-app notification for Tech 2 should exist');

        $this->assertStringContainsString('Budi Santoso & Joko Widodo', $notifTech1->message);
        $this->assertStringContainsString('Budi Santoso & Joko Widodo', $notifTech2->message);
    }

    public function test_installation_notifier_notifies_all_assigned_technicians()
    {
        $wo = Installation::create([
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'assigned_to' => $this->tech1->id,
            'technician_id' => $this->tech1->id,
            'technician_ids' => [$this->tech1->id, $this->tech2->id],
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '10:00',
            'status' => 'scheduled',
            'priority' => 'high',
            'notes' => 'Tarik kabel drop 150m',
        ]);

        $notifier = app(InstallationTechnicianNotifier::class);
        $res = $notifier->notifyAssigned($wo, 'created');

        $this->assertTrue($res['in_app']);

        $notifTech1 = Notification::where('recipient_id', $this->tech1->id)->where('data->installation_id', $wo->id)->first();
        $notifTech2 = Notification::where('recipient_id', $this->tech2->id)->where('data->installation_id', $wo->id)->first();

        $this->assertNotNull($notifTech1, 'In-app notification for Tech 1 must exist');
        $this->assertNotNull($notifTech2, 'In-app notification for Tech 2 must exist');

        $this->assertStringContainsString('Budi Santoso & Joko Widodo', $notifTech1->message);
        $this->assertStringContainsString('Budi Santoso & Joko Widodo', $notifTech2->message);
    }
}
