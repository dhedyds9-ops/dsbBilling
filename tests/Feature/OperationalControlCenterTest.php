<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Support\ControlCenter\Index as ControlCenterIndex;
use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Models\Workforce\Attendance;
use App\Models\Workforce\GPSLog;
use App\Models\Workforce\WorkOrder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class OperationalControlCenterTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $branchAdminA;
    protected User $branchAdminB;
    protected User $nocUser;
    protected User $techA;
    protected User $techB;
    protected Branch $branchA;
    protected Branch $branchB;
    protected Customer $customerA;
    protected Customer $customerB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Roles
        $adminRole = Role::firstOrCreate(['name' => UserRole::Administrator->value], ['display_name' => 'Administrator']);
        Role::firstOrCreate(['name' => 'superadmin'], ['display_name' => 'Super Administrator']);
        $nocRole = Role::firstOrCreate(['name' => 'noc'], ['display_name' => 'NOC']);
        $techRole = Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Technician']);

        // 2. Branches
        $this->branchA = Branch::create(['name' => 'Cabang Surabaya', 'code' => 'SBY', 'is_active' => true]);
        $this->branchB = Branch::create(['name' => 'Cabang Malang', 'code' => 'MLG', 'is_active' => true]);

        // 3. Superadmin
        $this->superAdmin = User::factory()->create([
            'name'         => 'Super Administrator',
            'email'        => 'superadmin@dsbilling.test',
            'is_active'    => true,
            'job_function' => 'ADMINISTRATOR',
            'branch_id'    => null,
        ]);
        $this->superAdmin->attachRole(UserRole::Administrator->value);

        // 4. Branch Admins
        $this->branchAdminA = User::factory()->create([
            'name'         => 'Admin Surabaya',
            'email'        => 'admin_sby@dsbilling.test',
            'branch_id'    => $this->branchA->id,
            'is_active'    => true,
            'job_function' => 'ADMINISTRATOR',
        ]);
        $this->branchAdminA->attachRole(UserRole::Administrator->value);

        $this->branchAdminB = User::factory()->create([
            'name'         => 'Admin Malang',
            'email'        => 'admin_mlg@dsbilling.test',
            'branch_id'    => $this->branchB->id,
            'is_active'    => true,
            'job_function' => 'ADMINISTRATOR',
        ]);
        $this->branchAdminB->attachRole(UserRole::Administrator->value);

        // 5. NOC
        $this->nocUser = User::factory()->create([
            'name'         => 'NOC Operator',
            'email'        => 'noc@dsbilling.test',
            'is_active'    => true,
            'job_function' => 'NOC',
        ]);
        $this->nocUser->roles()->attach($nocRole->id);

        // 6. Technicians
        $this->techA = User::factory()->create([
            'name'         => 'Budi Teknisi Surabaya',
            'email'        => 'budi_sby@dsbilling.test',
            'branch_id'    => $this->branchA->id,
            'is_active'    => true,
            'job_function' => 'TECHNICIAN',
            'whatsapp'     => '08123456701',
        ]);
        $this->techA->roles()->attach($techRole->id);

        $this->techB = User::factory()->create([
            'name'         => 'Siti Teknisi Malang',
            'email'        => 'siti_mlg@dsbilling.test',
            'branch_id'    => $this->branchB->id,
            'is_active'    => true,
            'job_function' => 'TECHNICIAN',
            'whatsapp'     => '08123456702',
        ]);
        $this->techB->roles()->attach($techRole->id);

        // 7. Customers with coordinates
        $this->customerA = Customer::create([
            'name'      => 'PT Maju Bersama Surabaya',
            'phone'     => '0811111111',
            'address'   => 'Jl. Basuki Rahmat No. 10, Surabaya',
            'branch_id' => $this->branchA->id,
            'status'    => 'active',
            'latitude'  => -7.2654,
            'longitude' => 112.7483,
        ]);

        $this->customerB = Customer::create([
            'name'      => 'CV Harapan Malang',
            'phone'     => '0822222222',
            'address'   => 'Jl. Ijen No. 25, Malang',
            'branch_id' => $this->branchB->id,
            'status'    => 'active',
            'latitude'  => -7.9797,
            'longitude' => 112.6304,
        ]);
    }

    public function test_admin_and_noc_can_access_control_center_page(): void
    {
        // 1. Admin access
        $this->actingAs($this->branchAdminA)
            ->get(route('support.control-center'))
            ->assertStatus(200);

        // 2. NOC route access
        $this->actingAs($this->nocUser)
            ->get(route('noc.control-center'))
            ->assertStatus(200);

        // 3. Guest redirected to login
        auth()->logout();
        $this->get(route('support.control-center'))
            ->assertRedirect(route('login'));
    }

    public function test_control_center_livewire_renders_kpi_cards_and_tabs(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(ControlCenterIndex::class)
            ->assertStatus(200)
            ->assertSee('Operational Control Center')
            ->assertSee('Total Pekerjaan Aktif')
            ->assertSee('Teknisi Bertugas')
            ->assertSee('Menunggu QC Approval')
            ->assertSee('SLA Breached')
            ->assertSee('SLA Warning (80%)')
            ->assertSee('SLA Compliance Rate')
            ->assertSee('Peta Terpadu');
    }

    public function test_branch_isolation_filters_jobs_and_technicians_for_branch_admin(): void
    {
        // Create active WO in Branch A
        $woA = WorkOrder::create([
            'uuid'        => (string) Str::uuid(),
            'branch_id'   => $this->branchA->id,
            'title'       => 'Pemasangan Drop Core Surabaya',
            'customer_id' => $this->customerA->id,
            'assigned_to' => $this->techA->id,
            'status'      => 'in_progress',
            'priority'    => 'high',
        ]);

        // Create active WO in Branch B
        $woB = WorkOrder::create([
            'uuid'        => (string) Str::uuid(),
            'branch_id'   => $this->branchB->id,
            'title'       => 'Perbaikan Redaman Malang',
            'customer_id' => $this->customerB->id,
            'assigned_to' => $this->techB->id,
            'status'      => 'in_progress',
            'priority'    => 'medium',
        ]);

        // Branch Admin A must only see Branch A data
        Livewire::actingAs($this->branchAdminA)
            ->test(ControlCenterIndex::class)
            ->assertSet('branchFilter', (string) $this->branchA->id)
            ->assertSee('Pemasangan Drop Core Surabaya')
            ->assertDontSee('Perbaikan Redaman Malang')
            ->assertSee('Budi Teknisi Surabaya')
            ->assertDontSee('Siti Teknisi Malang');
    }

    public function test_kpi_counters_reflect_sla_status_and_qc_pending(): void
    {
        // 1. In Progress Normal WO
        WorkOrder::create([
            'uuid'               => (string) Str::uuid(),
            'branch_id'          => $this->branchA->id,
            'title'              => 'Pekerjaan Normal',
            'customer_id'        => $this->customerA->id,
            'assigned_to'        => $this->techA->id,
            'status'             => 'in_progress',
            'priority'           => 'low',
            'sla_target_minutes' => 600,
            'sla_started_at'     => Carbon::now()->subHour(),
            'sla_due_at'         => Carbon::now()->addHours(9),
            'started_at'         => Carbon::now()->subHour(),
        ]);

        // 2. SLA Breached Ticket
        Ticket::create([
            'ticket_number'      => 'TCK-BREACHED-001',
            'title'              => 'Gangguan Total Los Merah Breached',
            'branch_id'          => $this->branchA->id,
            'customer_id'        => $this->customerA->id,
            'assigned_to'        => $this->techA->id,
            'status'             => 'in_progress',
            'priority'           => 'critical',
            'sla_target_minutes' => 120,
            'sla_started_at'     => Carbon::now()->subHours(6),
            'sla_due_at'         => Carbon::now()->subHours(2), // Overdue
            'created_at'         => Carbon::now()->subHours(6),
        ]);

        // 3. SLA Warning WO (90% elapsed)
        WorkOrder::create([
            'uuid'               => (string) Str::uuid(),
            'branch_id'          => $this->branchA->id,
            'title'              => 'Pekerjaan Mendekati SLA Warning',
            'customer_id'        => $this->customerA->id,
            'assigned_to'        => $this->techA->id,
            'status'             => 'in_progress',
            'priority'           => 'high',
            'sla_target_minutes' => 200,
            'sla_started_at'     => Carbon::now()->subMinutes(190),
            'created_at'         => Carbon::now()->subMinutes(190),
            'sla_due_at'         => Carbon::now()->addMinutes(10), // Total 200 min, 190 min passed = 95%
        ]);

        // 4. Pending Approval WO (Waiting for QC)
        WorkOrder::create([
            'uuid'               => (string) Str::uuid(),
            'branch_id'          => $this->branchA->id,
            'title'              => 'Pekerjaan Menunggu QC Approval',
            'customer_id'        => $this->customerA->id,
            'assigned_to'        => $this->techA->id,
            'status'             => 'pending_approval',
            'priority'           => 'medium',
            'sla_target_minutes' => 360,
            'sla_started_at'     => Carbon::now()->subHour(),
            'sla_due_at'         => Carbon::now()->addHours(5),
        ]);

        // Check KPI in Livewire
        $test = Livewire::actingAs($this->superAdmin)
            ->test(ControlCenterIndex::class);

        $viewData = $test->viewData('totalActiveJobs');
        $this->assertEquals(4, $viewData);

        $this->assertEquals(1, $test->viewData('pendingQcCount'));
        $this->assertEquals(1, $test->viewData('slaBreachedCount'));
        $this->assertEquals(1, $test->viewData('slaWarningCount'));
    }

    public function test_status_tabs_filtering_works_interactively(): void
    {
        // 1. SLA Breached
        $woBreached = WorkOrder::create([
            'uuid'               => (string) Str::uuid(),
            'branch_id'          => $this->branchA->id,
            'title'              => 'WO SLA Breached Exceeded',
            'customer_id'        => $this->customerA->id,
            'assigned_to'        => $this->techA->id,
            'status'             => 'in_progress',
            'sla_target_minutes' => 120,
            'sla_started_at'     => Carbon::now()->subHours(5),
            'sla_due_at'         => Carbon::now()->subHours(3),
        ]);

        // 2. QC Pending
        $woPending = WorkOrder::create([
            'uuid'               => (string) Str::uuid(),
            'branch_id'          => $this->branchA->id,
            'title'              => 'WO Pending Review QC',
            'customer_id'        => $this->customerA->id,
            'assigned_to'        => $this->techA->id,
            'status'             => 'pending_approval',
            'sla_target_minutes' => 360,
            'sla_started_at'     => Carbon::now()->subHour(),
            'sla_due_at'         => Carbon::now()->addHours(4),
        ]);

        // Tab: breached
        Livewire::actingAs($this->superAdmin)
            ->test(ControlCenterIndex::class)
            ->call('setStatusTab', 'breached')
            ->assertSee('WO SLA Breached Exceeded')
            ->assertDontSee('WO Pending Review QC');

        // Tab: qc_pending
        Livewire::actingAs($this->superAdmin)
            ->test(ControlCenterIndex::class)
            ->call('setStatusTab', 'qc_pending')
            ->assertSee('WO Pending Review QC')
            ->assertDontSee('WO SLA Breached Exceeded');
    }

    public function test_map_markers_generation_formats_technicians_and_jobs_correctly(): void
    {
        // Setup attendance and GPS Log for Tech A
        Attendance::create([
            'technician_id'       => $this->techA->id,
            'date'                => Carbon::today()->toDateString(),
            'checked_in_at'       => Carbon::now()->subHours(2),
            'check_in_latitude'   => -7.2650,
            'check_in_longitude'  => 112.7480,
            'status'              => 'checked_in',
        ]);

        GPSLog::create([
            'uuid'       => (string) Str::uuid(),
            'user_id'    => $this->techA->id,
            'latitude'   => -7.2700,
            'longitude'  => 112.7500,
            'accuracy'   => 5.0,
            'logged_at'  => Carbon::now()->subMinutes(10),
        ]);

        // Setup job for Tech A with customer coordinates
        WorkOrder::create([
            'uuid'               => (string) Str::uuid(),
            'branch_id'          => $this->branchA->id,
            'title'              => 'Pekerjaan Fiber Surabaya Map',
            'customer_id'        => $this->customerA->id,
            'assigned_to'        => $this->techA->id,
            'status'             => 'in_progress',
            'sla_target_minutes' => 120,
            'sla_started_at'     => Carbon::now()->subHours(3),
            'sla_due_at'         => Carbon::now()->subHour(), // Breached
        ]);

        $test = Livewire::actingAs($this->superAdmin)
            ->test(ControlCenterIndex::class);

        $markers = $test->viewData('mapMarkers');

        $this->assertIsArray($markers);
        $this->assertNotEmpty($markers);

        // Verify technician marker (priority GPSLog: -7.2700, 112.7500)
        $techMarker = collect($markers)->firstWhere('type', 'technician');
        $this->assertNotNull($techMarker);
        $this->assertEquals($this->techA->id, $techMarker['id']);
        $this->assertEquals(-7.2700, $techMarker['lat']);
        $this->assertEquals(112.7500, $techMarker['lng']);
        $this->assertTrue($techMarker['is_on_duty']);

        // Verify job marker (customer coords: -7.2654, 112.7483, red breached marker)
        $jobMarker = collect($markers)->firstWhere('type', 'job');
        $this->assertNotNull($jobMarker);
        $this->assertEquals(-7.2654, $jobMarker['lat']);
        $this->assertEquals(112.7483, $jobMarker['lng']);
        $this->assertEquals('#EF4444', $jobMarker['marker_color']); // Red for breached
    }

    public function test_mobile_api_control_center_endpoint_requires_auth_and_returns_valid_structure(): void
    {
        // 1. Unauthorized returns 401
        $this->getJson('/api/mobile/workforce/control-center')
            ->assertStatus(401);

        // 2. Authorized request
        $token = $this->branchAdminA->createToken('test-mobile-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/mobile/workforce/control-center');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Operational Control Center data loaded successfully.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'kpi' => [
                        'total_active_jobs',
                        'techs_on_duty',
                        'techs_total',
                        'pending_qc',
                        'sla_breached',
                        'sla_warning',
                        'compliance_rate',
                        'total_resolved',
                    ],
                    'active_jobs',
                    'technicians',
                    'recent_activities',
                ],
            ]);
    }
}
