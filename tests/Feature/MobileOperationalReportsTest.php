<?php

namespace Tests\Feature;

use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use App\Models\Workforce\MaterialUsage;
use App\Models\Workforce\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MobileOperationalReportsTest extends TestCase
{
    use RefreshDatabase;

    protected User $technician;
    protected User $manager;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $techRole = Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Teknisi', 'guard_name' => 'web']);
        $mgrRole = Role::firstOrCreate(['name' => 'manager'], ['display_name' => 'Manager', 'guard_name' => 'web']);

        $this->branch = Branch::firstOrCreate(['code' => 'BR-REP'], ['name' => 'Cabang Reports']);

        $this->technician = User::factory()->create([
            'name' => 'Budi Teknisi Lapangan',
            'email' => 'budi@dsbilling.test',
            'job_function' => 'TECHNICIAN',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->technician->roles()->attach($techRole->id);

        \App\Models\Workforce\Attendance::create([
            'technician_id' => $this->technician->id,
            'date' => \Carbon\Carbon::today(),
            'checked_in_at' => \Carbon\Carbon::now()->subHour(),
            'checked_out_at' => null,
            'status' => \Src\Domain\Workforce\Enums\AttendanceStatus::CHECKED_IN,
        ]);

        $this->manager = User::factory()->create([
            'name' => 'Agus Manager Ops',
            'email' => 'agus@dsbilling.test',
            'job_function' => 'MANAGER',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->manager->roles()->attach($mgrRole->id);
    }

    public function test_technician_can_get_performance_report(): void
    {
        $token = $this->technician->createToken('test-mobile-token')->plainTextToken;

        // Create completed work order
        WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'Pasang Baru 1',
            'type' => 'installation',
            'status' => 'completed',
            'assigned_to' => $this->technician->id,
            'branch_id' => $this->branch->id,
            'started_at' => now()->subHours(2),
            'completed_at' => now()->subHour(),
        ]);

        // Create in_progress work order
        WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'Gangguan 2',
            'type' => 'repair',
            'status' => 'in_progress',
            'assigned_to' => $this->technician->id,
            'branch_id' => $this->branch->id,
            'started_at' => now()->subMinutes(30),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/mobile/reports/technician-performance?period=all_time');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'technician_name' => 'Budi Teknisi Lapangan',
                    'total_assigned' => 2,
                    'total_completed' => 1,
                    'in_progress' => 1,
                    'completion_rate_percent' => 50.0,
                    'breakdown_by_type' => [
                        'installation' => 1,
                        'repair' => 0,
                    ],
                ],
            ]);
    }

    public function test_technician_can_get_material_consumption_report(): void
    {
        $token = $this->technician->createToken('test-mobile-token')->plainTextToken;

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'Pasang Baru Material Test',
            'status' => 'completed',
            'assigned_to' => $this->technician->id,
            'branch_id' => $this->branch->id,
        ]);

        MaterialUsage::create([
            'uuid' => (string) Str::uuid(),
            'work_order_id' => $wo->id,
            'material_id' => 'MAT-ONT-01',
            'material_name' => 'Modem ONT ZTE F663',
            'quantity' => 1,
        ]);

        MaterialUsage::create([
            'uuid' => (string) Str::uuid(),
            'work_order_id' => $wo->id,
            'material_id' => 'MAT-DC-100',
            'material_name' => 'Kabel Dropcore 1 Core',
            'quantity' => 120,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/mobile/reports/material-consumption?period=all_time');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_materials_used' => 121,
                    'total_ont_installed' => 1,
                    'total_dropcore_meters' => 120,
                ],
            ]);
    }

    public function test_technician_can_get_activity_logs_feed(): void
    {
        $token = $this->technician->createToken('test-mobile-token')->plainTextToken;

        WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'SPK Aktivitas 1',
            'status' => 'completed',
            'assigned_to' => $this->technician->id,
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/mobile/reports/activity-logs?limit=10');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'category',
                        'title',
                        'description',
                        'actor_name',
                        'badge',
                        'timestamp',
                    ],
                ],
            ]);
    }

    public function test_manager_can_query_other_technician_performance(): void
    {
        $token = $this->manager->createToken('test-mgr-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/mobile/reports/technician-performance?technician_id={$this->technician->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'technician_id' => $this->technician->id,
                    'technician_name' => 'Budi Teknisi Lapangan',
                ],
            ]);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/mobile/reports/technician-performance');
        $response->assertStatus(401);
    }
}
