<?php

namespace Tests\Feature;

use App\Livewire\Isp\Technician\Attendance\Index;
use App\Models\Master\Branch;
use App\Models\User;
use App\Models\Workforce\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TechnicianAttendanceWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_component_mounts_and_renders()
    {
        $branch = Branch::create([
            'name' => 'Kantor Pusat',
            'code' => 'KP',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($user);

        $test = Livewire::test(Index::class);
        $test->assertStatus(200);
        $test->assertSee('ABSEN MASUK');
    }

    public function test_check_in_without_gps_and_not_field_assignment_fails_with_error()
    {
        $branch = Branch::create([
            'name' => 'Kantor Pusat',
            'code' => 'KP',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($user);

        $test = Livewire::test(Index::class);
        $test->call('checkIn');
        $this->assertNotEmpty($test->get('errorMessage'));
        $this->assertDatabaseMissing('attendances', ['technician_id' => $user->id]);
    }

    public function test_use_office_location_populates_coordinates()
    {
        $branch = Branch::create([
            'name' => 'Kantor Pusat',
            'code' => 'KP',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($user);

        $test = Livewire::test(Index::class);
        $test->call('useOfficeLocation');
        $test->assertSet('latitude', -6.200000);
        $test->assertSet('longitude', 106.816666);
    }

    public function test_check_in_with_field_assignment_and_notes_succeeds()
    {
        $branch = Branch::create([
            'name' => 'Kantor Pusat',
            'code' => 'KP',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($user);

        $test = Livewire::test(Index::class);
        $test->set('isFieldAssignment', true);
        $test->set('notes', 'Tugas perbaikan ODP Gandasoli');
        $test->call('checkIn');
        $test->assertSet('errorMessage', '');
        $this->assertDatabaseHas('attendances', [
            'technician_id' => $user->id,
        ]);
    }
}
