<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Services\Notifications\WhatsApp\WhatsAppDirectSender;
use App\Services\Support\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TicketNotificationDuplicateTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_creation_and_assignment_flow(): void
    {
        $role = Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Technician', 'guard_name' => 'web']);
        $tech = User::factory()->create([
            'name' => 'Teknisi Lapangan',
            'is_active' => true,
        ]);
        $tech->roles()->attach($role->id);

        $admin = User::factory()->create([
            'name' => 'Admin Support',
            'is_active' => true,
        ]);

        \App\Models\Setting::setValue('whatsapp.connection', [
            'provider' => 'fonnte',
            'api_token' => 'dummy_token',
            'ticket_group_id' => '1203630283726@g.us',
        ]);

        \Illuminate\Support\Facades\Http::fake();

        dump('Listeners for TicketCreatedEvent:', \Illuminate\Support\Facades\Event::getListeners(\Src\Domain\Support\Events\TicketCreatedEvent::class));

        $service = app(TicketService::class);
        $ticket = $service->create([
            'title' => 'Internet Putus Total',
            'description' => 'Lampu PON berkedip merah',
            'priority' => 'high',
            'category' => 'internet',
            'customer_id' => null,
            'assigned_to' => $tech->id,
        ], $admin->id);

        $sentRequests = \Illuminate\Support\Facades\Http::recorded(function ($request) {
            return str_contains($request->url(), 'api.fonnte.com');
        });

        dump('Total Fonnte HTTP requests sent: ' . count($sentRequests));
        foreach ($sentRequests as $idx => [$req, $res]) {
            dump("Request #$idx: target=" . $req['target'] . " | msg=" . substr($req['message'], 0, 50) . "...");
        }
        $this->assertEquals(1, count($sentRequests), 'Group notification should only be sent ONCE, not duplicated!');
    }
}
