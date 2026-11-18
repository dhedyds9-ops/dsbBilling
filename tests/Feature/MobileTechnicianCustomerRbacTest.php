<?php

namespace Tests\Feature;

use Src\Domain\Workforce\Enums\AttendanceStatus;
use App\Models\CRM\Customer;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Models\Workforce\Attendance;
use App\Models\Workforce\WorkOrder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MobileTechnicianCustomerRbacTest extends TestCase
{
    use RefreshDatabase;

    private function createCheckedInTechnician(): array
    {
        $branch = Branch::firstOrCreate(
            ['code' => 'PST'],
            ['name' => 'Cabang Pusat']
        );

        $role = Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Teknisi', 'guard_name' => 'web']);
        $technician = User::factory()->create([
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $technician->roles()->attach($role->id);

        Attendance::create([
            'technician_id' => $technician->id,
            'date' => Carbon::today(),
            'checked_in_at' => Carbon::now()->subHour(),
            'checked_out_at' => null,
            'status' => AttendanceStatus::CHECKED_IN,
        ]);

        return [$technician, $branch];
    }

    public function test_technician_cannot_register_new_customer(): void
    {
        [$technician, $branch] = $this->createCheckedInTechnician();

        $response = $this->actingAs($technician, 'sanctum')
            ->postJson('/api/mobile/customers', [
                'name' => 'Test Customer',
                'phone' => '081234567890',
                'username' => 'testuser',
                'password' => 'secret123',
                'service_profile_id' => 1,
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Akses ditolak: Teknisi tidak memiliki wewenang mendaftarkan master pelanggan baru.');
    }

    public function test_technician_cannot_view_unassigned_customer_detail(): void
    {
        [$technician, $branch] = $this->createCheckedInTechnician();

        $customer = Customer::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Unassigned Customer',
            'code' => 'CUST-UNASSIGNED',
            'phone' => '081234567890',
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($technician, 'sanctum')
            ->getJson("/api/mobile/customers/{$customer->id}");

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Akses ditolak: Teknisi hanya dapat melihat detail pelanggan yang memiliki penugasan SPK/Tiket aktif.');
    }

    public function test_technician_can_view_assigned_customer_detail_via_work_order(): void
    {
        [$technician, $branch] = $this->createCheckedInTechnician();

        $customer = Customer::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Assigned Customer',
            'code' => 'CUST-ASSIGNED',
            'phone' => '081234567890',
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'assigned_to' => $technician->id,
            'branch_id' => $branch->id,
            'title' => 'Pasang Baru',
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($technician, 'sanctum')
            ->getJson("/api/mobile/customers/{$customer->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('customer.id', $customer->id)
            ->assertJsonPath('customer.name', 'Assigned Customer');
    }

    public function test_reseller_can_update_customer_credentials_and_package(): void
    {
        $branch = Branch::firstOrCreate(
            ['code' => 'PST'],
            ['name' => 'Cabang Pusat']
        );

        $resellerRole = Role::firstOrCreate(['name' => 'reseller'], ['display_name' => 'Reseller', 'guard_name' => 'web']);
        $reseller = User::factory()->create([
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $reseller->roles()->attach($resellerRole->id);

        $customer = Customer::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Customer Before Edit',
            'code' => '2026110199',
            'phone' => '081234567899',
            'branch_id' => $branch->id,
            'reseller_id' => $reseller->id,
            'status' => 'active',
        ]);

        $profileType = \App\Models\ISP\ServiceProfileType::firstOrCreate(
            ['code' => 'PPPOE'],
            ['name' => 'PPPoE Profile', 'status' => 'active']
        );

        $profile = \App\Models\ISP\ServiceProfile::create([
            'service_profile_type_id' => $profileType->id,
            'code' => 'PKG-20M',
            'name' => 'PAKET 20 MBPS',
            'price' => 150000,
            'service_type' => 'pppoe',
        ]);

        $cs = \App\Models\Customer\CustomerService::create([
            'customer_id' => $customer->id,
            'username' => 'user_lama',
            'password' => 'pass_lama',
            'service_profile_id' => $profile->id,
            'branch_id' => $branch->id,
            'reseller_id' => $reseller->id,
            'status' => 'active',
        ]);

        // Reseller updates name, phone, username, password, status
        $response = $this->actingAs($reseller, 'sanctum')
            ->putJson("/api/mobile/customers/{$customer->id}", [
                'name' => 'Customer After Edit',
                'phone' => '089876543210',
                'username' => 'user_baru',
                'password' => 'pass_baru123',
                'status' => 'isolated',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals('Customer After Edit', $customer->fresh()->name);
        $this->assertEquals('089876543210', $customer->fresh()->phone);
        $this->assertEquals('suspend', $customer->fresh()->status);
        $this->assertEquals('user_baru', $cs->fresh()->username);
        $this->assertEquals('pass_baru123', $cs->fresh()->password);
    }
}
