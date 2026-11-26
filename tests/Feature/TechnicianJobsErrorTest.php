<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Support\Ticket;
use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\Customer\CustomerService;
use App\Models\Master\Branch;
use App\Models\Workforce\WorkOrder;
use App\Models\Workforce\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TechnicianJobsErrorTest extends TestCase
{
    use RefreshDatabase;

    protected User $technician;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'teknisi'], ['display_name' => 'Teknisi']);
        $this->branch = Branch::firstOrCreate(['name' => 'Cabang 1'], ['code' => 'CB1']);

        $this->technician = User::factory()->create([
            'name' => 'Ahmad Gofur',
            'username' => 'ahmad_gofur',
            'job_function' => 'TECHNICIAN',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->technician->attachRole('teknisi');

        // Check in attendance
        Attendance::create([
            'technician_id' => $this->technician->id,
            'date' => now()->toDateString(),
            'checked_in_at' => now(),
            'status' => 'checked_in',
        ]);
    }

    public function test_01_technician_portal_alias_redirect(): void
    {
        $this->actingAs($this->technician);

        $response = $this->get('/technician_portal/my_jobs');
        $this->assertEquals(302, $response->getStatusCode());
        
        $target = $response->headers->get('Location');
        $follow = $this->get($target);
        $this->assertEquals(200, $follow->getStatusCode());

        $directUnderscore = $this->get('/technician-portal/my_jobs');
        $this->assertEquals(302, $directUnderscore->getStatusCode());
        $followDirect = $this->get($directUnderscore->headers->get('Location'));
        $this->assertEquals(200, $followDirect->getStatusCode());
    }

    public function test_02_technician_portal_my_jobs_page(): void
    {
        $this->actingAs($this->technician);

        $response = $this->get('/technician-portal/my-jobs');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_03_my_jobs_livewire_component(): void
    {
        $this->actingAs($this->technician);

        $customer = Customer::create([
            'name' => 'Budi Pelanggan',
            'phone' => '081234567890',
            'address' => 'Jl. Mawar No. 12',
            'branch_id' => $this->branch->id,
        ]);

        $ticket = Ticket::create([
            'title' => 'Internet Mati Total',
            'description' => 'Lampu LOS merah berkedip',
            'customer_id' => $customer->id,
            'branch_id' => $this->branch->id,
            'assigned_to' => $this->technician->id,
            'status' => 'open',
            'priority' => 'high',
        ]);

        $component = Livewire::test(\App\Livewire\Isp\Technician\MyJobs\Index::class)
            ->assertStatus(200)
            ->assertSee('Internet Mati Total')
            ->call('switchTab', 'history')
            ->assertStatus(200)
            ->call('switchTab', 'active')
            ->assertStatus(200)
            ->call('filterJobType', 'ticket')
            ->assertStatus(200)
            ->call('startTicket', $ticket->id)
            ->assertStatus(200)
            ->call('openProgressTicketModal', $ticket->id)
            ->assertStatus(200)
            ->set('progress_status', 'in_progress')
            ->set('progress_notes', 'Sedang cek redaman kabel')
            ->call('saveTicketProgress')
            ->assertStatus(200)
            ->call('openResolveTicketModal', $ticket->id)
            ->assertStatus(200)
            ->set('ticket_resolution_notes', 'Kabel dropcore disambung ulang')
            ->set('ticket_optical_power', '-18.5 dBm')
            ->call('confirmResolveTicket')
            ->assertStatus(200);

        $this->assertEquals('resolved', $ticket->fresh()->status);
    }

    public function test_04_notification_center_with_null_created_at(): void
    {
        $this->actingAs($this->technician);

        // Insert notification with null created_at directly into DB
        \Illuminate\Support\Facades\DB::table('notifications')->insert([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'recipient_id' => $this->technician->id,
            'title' => 'Test Notif',
            'message' => 'Test message',
            'type' => 'in_app',
            'status' => 'pending',
            'data' => json_encode(['url' => '#']),
            'created_at' => null,
            'updated_at' => null,
        ]);

        $component = Livewire::test(\App\Livewire\NotificationCenter::class);
        $component->assertStatus(200);
    }
}

