<?php

namespace Tests\Feature;

use App\Models\ACS\ACSDevice;
use App\Models\CRM\Contract;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Onu;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\ServiceCatalog\Service;
use App\Models\ServiceCatalog\ServiceCatalog;
use App\Models\User;
use App\Models\Workforce\Attendance;
use App\Models\Workforce\WorkOrder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Src\Domain\Workforce\Enums\AttendanceStatus;
use Tests\TestCase;

class AttendanceOperationalAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected User $technician;
    protected Branch $branchA;
    protected Branch $branchB;

    protected function setUp(): void
    {
        parent::setUp();

        $techRole = Role::firstOrCreate(['name' => 'teknisi'], ['display_name' => 'Teknisi', 'guard_name' => 'web']);

        $this->branchA = Branch::firstOrCreate(['code' => 'BR-A'], ['name' => 'Cabang Alpha']);
        $this->branchB = Branch::firstOrCreate(['code' => 'BR-B'], ['name' => 'Cabang Beta']);

        $this->technician = User::factory()->create([
            'name' => 'Budi Teknisi Alpha',
            'email' => 'tech_alpha_' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'branch_id' => $this->branchA->id,
            'job_function' => 'TECHNICIAN',
            'job_title' => 'Teknisi Lapangan',
            'is_active' => true,
        ]);
        $this->technician->roles()->attach($techRole->id);
    }

    /**
     * Skenario 1: Teknisi tanpa presensi mencoba diagnosa ONU -> Ditolak HTTP 403 ATTENDANCE_REQUIRED
     */
    public function test_unchecked_in_technician_blocked_from_diagnosis_with_403_attendance_required()
    {
        Sanctum::actingAs($this->technician);

        $response = $this->getJson('/api/mobile/network/devices/SN123456/diagnostics');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'ATTENDANCE_REQUIRED',
                'attendance_required' => true,
            ]);
    }

    /**
     * Skenario 2: Teknisi tanpa presensi mencoba provisioning PSB -> Ditolak HTTP 403 ATTENDANCE_REQUIRED
     */
    public function test_unchecked_in_technician_blocked_from_provisioning_psb_with_403()
    {
        Sanctum::actingAs($this->technician);

        $response = $this->postJson('/api/mobile/network/devices/provision-ont', [
            'serial_number' => 'ZTEG12345678',
            'customer_id' => 1,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'ATTENDANCE_REQUIRED',
                'attendance_required' => true,
            ]);
    }

    /**
     * Skenario 3: Teknisi tanpa presensi mencoba swap ONT -> Ditolak HTTP 403 ATTENDANCE_REQUIRED
     */
    public function test_unchecked_in_technician_blocked_from_swap_ont_with_403()
    {
        Sanctum::actingAs($this->technician);

        $response = $this->postJson('/api/mobile/network/devices/swap-ont', [
            'old_serial' => 'OLD123',
            'new_serial' => 'NEW123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'ATTENDANCE_REQUIRED',
                'attendance_required' => true,
            ]);
    }

    /**
     * Skenario 4: Teknisi tanpa presensi mencoba update status SPK -> Ditolak HTTP 403 ATTENDANCE_REQUIRED
     */
    public function test_unchecked_in_technician_blocked_from_updating_spk_status_with_403()
    {
        Sanctum::actingAs($this->technician);

        $workOrder = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branchA->id,
            'title' => 'Pemasangan PSB 01',
            'assigned_to' => $this->technician->id,
            'status' => 'assigned',
        ]);

        $response = $this->postJson("/api/mobile/workforce/work-orders/{$workOrder->id}/status", [
            'status' => 'in_progress',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'ATTENDANCE_REQUIRED',
                'attendance_required' => true,
            ]);
    }

    /**
     * Skenario 5: Teknisi dengan presensi aktif mengakses pekerjaan cabang sendiri -> Berhasil (200)
     */
    public function test_checked_in_technician_can_access_own_branch_work()
    {
        // Berikan presensi aktif
        Attendance::create([
            'technician_id' => $this->technician->id,
            'date' => Carbon::today(),
            'checked_in_at' => Carbon::now()->subHours(2),
            'checked_out_at' => null,
            'status' => AttendanceStatus::CHECKED_IN,
        ]);

        $workOrder = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branchA->id,
            'title' => 'Pemasangan PSB Cabang Sendiri',
            'assigned_to' => $this->technician->id,
            'status' => 'assigned',
        ]);

        Sanctum::actingAs($this->technician);

        $response = $this->getJson("/api/mobile/workforce/work-orders/{$workOrder->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Skenario 6: Teknisi Cabang A dengan presensi aktif mengerjakan SPK Cabang B yang ditugaskan kepadanya -> Berhasil (200)
     */
    public function test_checked_in_technician_branch_a_can_execute_branch_b_work_when_assigned()
    {
        Attendance::create([
            'technician_id' => $this->technician->id,
            'date' => Carbon::today(),
            'checked_in_at' => Carbon::now()->subHours(1),
            'checked_out_at' => null,
            'status' => AttendanceStatus::CHECKED_IN,
        ]);

        // SPK milik Cabang Beta, ditugaskan secara sah ke teknisi Cabang Alpha
        $workOrder = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branchB->id,
            'title' => 'Pemasangan Lintas Cabang Beta',
            'assigned_to' => $this->technician->id,
            'status' => 'assigned',
        ]);

        Sanctum::actingAs($this->technician);

        $response = $this->postJson("/api/mobile/workforce/work-orders/{$workOrder->id}/start");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals('in_progress', $workOrder->fresh()->status);
        $this->assertEquals($this->branchB->id, $workOrder->fresh()->branch_id); // Branch target dipertahankan
    }

    /**
     * Skenario 7: Teknisi Cabang A mencoba mengubah SPK Cabang B yang BUKAN penugasannya -> Ditolak 403
     */
    public function test_technician_branch_a_blocked_from_modifying_unassigned_branch_b_spk()
    {
        Attendance::create([
            'technician_id' => $this->technician->id,
            'date' => Carbon::today(),
            'checked_in_at' => Carbon::now()->subHours(1),
            'checked_out_at' => null,
            'status' => AttendanceStatus::CHECKED_IN,
        ]);

        $otherTech = User::factory()->create([
            'branch_id' => $this->branchB->id,
            'job_function' => 'TECHNICIAN',
        ]);

        // SPK Cabang Beta untuk teknisi lain
        $workOrder = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branchB->id,
            'title' => 'Pekerjaan Teknisi Lain',
            'assigned_to' => $otherTech->id,
            'status' => 'assigned',
        ]);

        Sanctum::actingAs($this->technician);

        $response = $this->postJson("/api/mobile/workforce/work-orders/{$workOrder->id}/start");

        $response->assertStatus(403);
    }

    /**
     * Skenario 8: Teknisi yang sudah check-out langsung kehilangan akses operasional -> 403 ATTENDANCE_REQUIRED
     */
    public function test_checked_out_technician_immediately_rejected_with_403_attendance_required()
    {
        Attendance::create([
            'technician_id' => $this->technician->id,
            'date' => Carbon::today(),
            'checked_in_at' => Carbon::now()->subHours(8),
            'checked_out_at' => Carbon::now()->subMinutes(10), // Sudah pulang
            'status' => AttendanceStatus::CHECKED_OUT,
        ]);

        Sanctum::actingAs($this->technician);

        $response = $this->getJson('/api/mobile/inventory/my-stock');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'ATTENDANCE_REQUIRED',
                'attendance_required' => true,
            ]);
    }

    /**
     * Skenario 9: Replay / manipulasi presensi milik staf lain tidak memberikan akses
     */
    public function test_attendance_ownership_strictly_bound_to_authenticated_user()
    {
        $otherTech = User::factory()->create([
            'branch_id' => $this->branchA->id,
            'job_function' => 'TECHNICIAN',
        ]);

        // Presensi aktif milik staf lain
        Attendance::create([
            'technician_id' => $otherTech->id,
            'date' => Carbon::today(),
            'checked_in_at' => Carbon::now()->subHours(1),
            'checked_out_at' => null,
            'status' => AttendanceStatus::CHECKED_IN,
        ]);

        // Teknisi login Budi (belum absen)
        Sanctum::actingAs($this->technician);

        $response = $this->getJson('/api/mobile/workforce/work-orders');

        $response->assertStatus(403)
            ->assertJson([
                'code' => 'ATTENDANCE_REQUIRED',
            ]);
    }

    /**
     * Skenario 10: Multi-Tenant/Reseller boundary: Teknisi tidak dapat mengakses perangkat milik reseller lain jika dibatasi scope
     */
    public function test_technician_blocked_from_different_tenant_or_unauthorized_reseller_device()
    {
        Attendance::create([
            'technician_id' => $this->technician->id,
            'date' => Carbon::today(),
            'checked_in_at' => Carbon::now()->subHour(),
            'checked_out_at' => null,
            'status' => AttendanceStatus::CHECKED_IN,
        ]);

        $resellerRole = Role::firstOrCreate(['name' => 'reseller'], ['display_name' => 'Reseller', 'guard_name' => 'web']);
        $reseller = User::factory()->create([
            'branch_id' => $this->branchB->id,
            'job_function' => 'RESELLER',
        ]);
        $reseller->roles()->attach($resellerRole->id);

        $cust = Customer::create([
            'code' => 'CUST-RES-X',
            'name' => 'Pelanggan Reseller X',
            'phone' => '081299991111',
            'reseller_id' => $reseller->id,
            'branch_id' => $this->branchB->id,
            'created_by' => $reseller->id,
        ]);

        $contract = Contract::create([
            'customer_id' => $cust->id,
            'start_date' => now(),
            'created_by' => $reseller->id,
        ]);

        $catalog = ServiceCatalog::create(['uuid' => (string) Str::uuid(), 'name' => 'Cat']);
        $srv = Service::create([
            'uuid' => (string) Str::uuid(),
            'service_catalog_id' => $catalog->id,
            'name' => 'Srv',
            'price' => 100000,
        ]);

        $onu = Onu::create([
            'code' => 'ONU-FOREIGN-RES',
            'name' => 'ONU Res',
            'serial_number' => 'ZTEG99998888',
            'branch_id' => $this->branchB->id,
            'created_by' => $reseller->id,
        ]);

        $cs = CustomerService::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $cust->id,
            'contract_id' => $contract->id,
            'service_id' => $srv->id,
            'username' => 'cs_user_res',
            'password' => 'secret_res',
            'onu_id' => $onu->id,
            'reseller_id' => $reseller->id,
            'branch_id' => $this->branchB->id,
            'created_by' => $reseller->id,
        ]);

        $dev = ACSDevice::create([
            'uuid' => (string) Str::uuid(),
            'serial_number' => 'ZTEG99998888',
            'customer_service_id' => $cs->id,
            'created_by' => $reseller->id,
        ]);

        // Simulasikan reseller user lain yang mencoba mengakses device reseller ini
        $otherReseller = User::factory()->create([
            'branch_id' => $this->branchA->id,
            'job_function' => 'RESELLER',
        ]);
        $otherReseller->roles()->attach($resellerRole->id);
        Sanctum::actingAs($otherReseller);

        $response = $this->postJson('/api/mobile/acs/devices/ZTEG99998888/reboot');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Skenario 11: Shift malam melewati tengah malam (misal masuk jam 23.00 kemarin, sekarang jam 02.00) tetap valid
     */
    public function test_night_shift_across_midnight_remains_valid_within_operational_window()
    {
        // Masuk jam 23:00 kemarin (3 jam yang lalu)
        Attendance::create([
            'technician_id' => $this->technician->id,
            'date' => Carbon::yesterday(),
            'checked_in_at' => Carbon::now()->subHours(3),
            'checked_out_at' => null,
            'status' => AttendanceStatus::CHECKED_IN,
        ]);

        Sanctum::actingAs($this->technician);

        $workOrder = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branchA->id,
            'title' => 'Shift Malam SPK',
            'assigned_to' => $this->technician->id,
            'status' => 'assigned',
        ]);

        $response = $this->getJson("/api/mobile/workforce/work-orders/{$workOrder->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Skenario 12: Endpoint pengecualian (me, payroll, attendance status/check-in) tetap dapat diakses tanpa presensi
     */
    public function test_exempt_endpoints_accessible_without_attendance()
    {
        Sanctum::actingAs($this->technician);

        // 1. Profil & Auth Check
        $meResponse = $this->getJson('/api/mobile/me');
        $meResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user' => [
                    'has_active_attendance' => false,
                    'can_operate' => false,
                ],
            ]);

        // 2. Status Presensi Hari Ini
        $attendanceTodayResponse = $this->getJson('/api/mobile/attendance/today');
        $attendanceTodayResponse->assertStatus(200);

        // 3. Payroll Pribadi
        $payrollResponse = $this->getJson('/api/mobile/payroll');
        $payrollResponse->assertStatus(200);
    }
}
