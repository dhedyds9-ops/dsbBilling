<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\ISP\Odp;
use App\Models\ISP\Onu;
use App\Models\Master\Branch;
use App\Models\Payroll;
use App\Models\Role;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Models\Workforce\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Src\Domain\Workforce\Enums\AttendanceStatus;
use Tests\TestCase;

class MobilePortalParityTest extends TestCase
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

        $this->branch = Branch::firstOrCreate(['code' => 'BR-PARITY'], ['name' => 'Cabang Parity']);

        $this->technician = User::factory()->create([
            'name' => 'Agung Teknisi Parity',
            'email' => 'agung@dsbilling.test',
            'job_function' => 'TECHNICIAN',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->technician->roles()->attach($techRole->id);

        $this->manager = User::factory()->create([
            'name' => 'Manager Parity',
            'email' => 'mgr_parity@dsbilling.test',
            'job_function' => 'MANAGER',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->manager->roles()->attach($mgrRole->id);
    }

    public function test_technician_can_check_in_and_check_out_attendance(): void
    {
        Sanctum::actingAs($this->technician, ['*']);

        // 1. Initial state: not checked in today
        $todayRes = $this->getJson('/api/mobile/attendance/today');
        $todayRes->assertOk()
            ->assertJsonPath('data.has_checked_in', false)
            ->assertJsonPath('data.is_active_shift', false);

        // 2. Perform check-in
        $checkInRes = $this->postJson('/api/mobile/attendance/check-in', [
            'latitude' => -7.6745,
            'longitude' => 109.6582,
            'notes' => 'Mulai shift pagi siap pemasangan',
            'is_field_assignment' => true,
        ]);
        $checkInRes->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['id', 'status', 'checked_in_at', 'latitude', 'longitude']]);

        // 3. Re-verify today's state: shift is active
        $todayActiveRes = $this->getJson('/api/mobile/attendance/today');
        $todayActiveRes->assertOk()
            ->assertJsonPath('data.has_checked_in', true)
            ->assertJsonPath('data.is_active_shift', true);

        // 4. Duplicate check-in is rejected
        $dupRes = $this->postJson('/api/mobile/attendance/check-in', [
            'latitude' => -7.6745,
            'longitude' => 109.6582,
        ]);
        $dupRes->assertStatus(422);

        // 5. Perform check-out
        $checkOutRes = $this->postJson('/api/mobile/attendance/check-out', [
            'latitude' => -7.6750,
            'longitude' => 109.6590,
            'notes' => 'Selesai shift seluruh SPK beres',
        ]);
        $checkOutRes->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'checked_out');

        // 6. Verify final state: checked in and checked out
        $finalRes = $this->getJson('/api/mobile/attendance/today');
        $finalRes->assertOk()
            ->assertJsonPath('data.has_checked_in', true)
            ->assertJsonPath('data.has_checked_out', true)
            ->assertJsonPath('data.is_active_shift', false);

        // 7. Verify history endpoint returns the recorded attendance
        $histRes = $this->getJson('/api/mobile/attendance/history');
        $histRes->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    public function test_technician_can_query_troubleshooting_tickets(): void
    {
        Sanctum::actingAs($this->technician, ['*']);

        \App\Models\Workforce\Attendance::create([
            'technician_id' => $this->technician->id,
            'date' => \Carbon\Carbon::today(),
            'checked_in_at' => \Carbon\Carbon::now()->subHour(),
            'checked_out_at' => null,
            'status' => \Src\Domain\Workforce\Enums\AttendanceStatus::CHECKED_IN,
        ]);

        Ticket::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'Redaman Drop di RT 03',
            'description' => 'Loss sinyal optik setelah hujan deras',
            'status' => 'open',
            'priority' => 'high',
            'category' => 'network',
            'assigned_to' => $this->technician->id,
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->getJson('/api/mobile/workforce/tickets');
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Redaman Drop di RT 03');
    }

    public function test_technician_can_perform_one_click_ont_swap(): void
    {
        Sanctum::actingAs($this->technician, ['*']);

        \App\Models\Workforce\Attendance::create([
            'technician_id' => $this->technician->id,
            'date' => \Carbon\Carbon::today(),
            'checked_in_at' => \Carbon\Carbon::now()->subHour(),
            'checked_out_at' => null,
            'status' => \Src\Domain\Workforce\Enums\AttendanceStatus::CHECKED_IN,
        ]);

        $odp = Odp::create([
            'uuid' => (string) Str::uuid(),
            'code' => 'ODP-SWAP-01',
            'name' => 'ODP Swap Test',
            'branch_id' => $this->branch->id,
        ]);

        $oldOnu = Onu::create([
            'uuid' => (string) Str::uuid(),
            'code' => 'ONU-OLD-01',
            'name' => 'ONT Lama Rusak',
            'serial_number' => 'ZTEG11112222',
            'model' => 'F609',
            'odp_id' => $odp->id,
            'wifi_ssid' => 'PelangganLama_Net',
            'wifi_password' => 'pass123456',
            'status' => 'active',
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->postJson('/api/mobile/network/devices/swap-ont', [
            'old_serial_number' => 'ZTEG11112222',
            'new_serial_number' => 'HWTC99998888',
            'new_mac_address' => '00:11:22:33:44:99',
            'new_model' => 'HG8245H5',
            'notes' => 'Port LAN 1 dan 2 petir tersambar',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.old_onu.status', 'replaced')
            ->assertJsonPath('data.new_onu.serial_number', 'HWTC99998888')
            ->assertJsonPath('data.new_onu.wifi_ssid', 'PelangganLama_Net');

        $this->assertEquals('replaced', $oldOnu->fresh()->status);
        $this->assertDatabaseHas('onus', [
            'serial_number' => 'HWTC99998888',
            'status' => 'active',
            'wifi_ssid' => 'PelangganLama_Net',
        ]);
    }

    public function test_technician_can_dismantle_customer_ont(): void
    {
        Sanctum::actingAs($this->technician, ['*']);

        \App\Models\Workforce\Attendance::create([
            'technician_id' => $this->technician->id,
            'date' => \Carbon\Carbon::today(),
            'checked_in_at' => \Carbon\Carbon::now()->subHour(),
            'checked_out_at' => null,
            'status' => \Src\Domain\Workforce\Enums\AttendanceStatus::CHECKED_IN,
        ]);

        $onu = Onu::create([
            'uuid' => (string) Str::uuid(),
            'code' => 'ONU-DISM-01',
            'name' => 'ONT Ditarik',
            'serial_number' => 'FH6243ABCDEF',
            'status' => 'active',
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->postJson('/api/mobile/network/devices/dismantle', [
            'serial_number' => 'FH6243ABCDEF',
            'dismantle_reason' => 'Pindah rumah ke luar kota',
            'device_condition' => 'good',
            'checklist' => [
                'modem' => true,
                'adapter' => true,
                'patchcord' => true,
                'lan_cable' => true,
                'box' => false,
            ],
            'notes' => 'Kondisi barang mulus berfungsi normal',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'dismantled_good')
            ->assertJsonPath('data.device_condition', 'good');

        $this->assertEquals('dismantled_good', $onu->fresh()->status);
    }

    public function test_technician_can_view_monthly_payroll_slips(): void
    {
        Sanctum::actingAs($this->technician, ['*']);

        $employee = Employee::create([
            'user_id' => $this->technician->id,
            'name' => $this->technician->name,
            'employee_code' => 'EMP-TECH-001',
            'branch_id' => $this->branch->id,
            'status' => 'active',
        ]);

        $payroll = Payroll::create([
            'employee_id' => $employee->id,
            'period_month' => 10,
            'period_year' => 2026,
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-31',
            'base_salary' => 4500000,
            'allowances' => 750000,
            'deductions' => 150000,
            'net_salary' => 5100000,
            'status' => 'paid',
            'payment_date' => '2026-11-01',
            'work_days' => 26,
            'present_days' => 26,
            'notes' => 'Insentif pemasangan 15 titik baru',
        ]);

        $listRes = $this->getJson('/api/mobile/payroll');
        $listRes->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.net_salary', 5100000);

        $showRes = $this->getJson("/api/mobile/payroll/{$payroll->id}");
        $showRes->assertOk()
            ->assertJsonPath('data.net_salary', 5100000)
            ->assertJsonPath('data.attendance_summary.present_days', 26);
    }
}
