<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Workforce\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Src\Domain\Workforce\Enums\AttendanceStatus;
use Tests\TestCase;

class TechnicianPortalResponsiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_technician_dashboard_loads_responsive_layout(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'technician'],
            ['display_name' => 'Technician', 'guard_name' => 'web']
        );

        $user = User::factory()->create([
            'job_function' => 'TECHNICIAN',
        ]);
        $user->roles()->syncWithoutDetaching([$role->id]);

        // Check in for attendance gate
        Attendance::create([
            'technician_id' => $user->id,
            'date' => now()->toDateString(),
            'checked_in_at' => now(),
            'status' => AttendanceStatus::CHECKED_IN->value,
        ]);

        $response = $this->actingAs($user)->get(route('technician.dashboard'));

        $response->assertStatus(200);
        // Verify desktop sidebar elements exist in rendered HTML
        $response->assertSee('Technician Portal');
        $response->assertSee('dsBilling');
        $response->assertSee('SPK Lapangan');
        $response->assertSee('Wizard PSB (Pasang)');
        $response->assertSee('Field Service Tools');
    }

    public function test_technician_can_access_attendance(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'technician'],
            ['display_name' => 'Technician', 'guard_name' => 'web']
        );

        $user = User::factory()->create([
            'job_function' => 'TECHNICIAN',
        ]);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $responseDash = $this->actingAs($user)->get('/technician-portal/attendance');
        $responseDash->assertStatus(200);

        $responseUnderscore = $this->actingAs($user)->get('/technician_portal/attendance');
        $responseUnderscore->assertRedirect('/technician-portal/attendance');

        // Test when already checked in
        Attendance::create([
            'technician_id' => $user->id,
            'date' => now()->toDateString(),
            'checked_in_at' => now(),
            'status' => AttendanceStatus::CHECKED_IN->value,
            'notes' => 'Checked in successfully',
        ]);

        $responseCheckedIn = $this->actingAs($user)->get('/technician-portal/attendance');
        $responseCheckedIn->assertStatus(200);
        $responseCheckedIn->assertSee('Sudah Absen');
    }

    public function test_technician_portal_blocks_unauthorized_role(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'reseller'],
            ['display_name' => 'Reseller', 'guard_name' => 'web']
        );

        $resellerUser = User::factory()->create([
            'job_function' => 'RESELLER',
        ]);
        $resellerUser->roles()->syncWithoutDetaching([$role->id]);

        $response = $this->actingAs($resellerUser)->get(route('technician.dashboard'));

        // Reseller without technician role/job_function is blocked (either 403 or redirect 302)
        $this->assertTrue(in_array($response->getStatusCode(), [302, 403], true));
    }
}
