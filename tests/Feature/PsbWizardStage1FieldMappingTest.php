<?php

namespace Tests\Feature;

use App\Livewire\Isp\Technician\Installation\Wizard;
use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Models\Workforce\WorkOrder;
use App\Services\Support\CustomerFieldSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class PsbWizardStage1FieldMappingTest extends TestCase
{
    use RefreshDatabase;

    protected User $technician;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Branch Pusat',
            'code' => 'PST',
            'is_active' => true,
        ]);

        $role = Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Teknisi', 'guard_name' => 'web']);

        $this->technician = User::factory()->create([
            'is_active' => true,
            'branch_id' => $this->branch->id,
            'job_function' => 'TECHNICIAN',
        ]);
        $this->technician->roles()->attach($role->id);

        \App\Models\Workforce\Attendance::create([
            'technician_id' => $this->technician->id,
            'date' => \Carbon\Carbon::today(),
            'checked_in_at' => \Carbon\Carbon::now()->subHour(),
            'checked_out_at' => null,
            'status' => \Src\Domain\Workforce\Enums\AttendanceStatus::CHECKED_IN,
        ]);
    }

    /**
     * Pathway 1: Ticket Admin -> Mobile API (/api/mobile/workforce/work-orders)
     * Verifies that when a ticket is created with PSB info, phone is never '-' and address is free of phone & coordinates.
     */
    public function test_pathway_1_ticket_admin_to_mobile_api_clean_mapping(): void
    {
        $ticket = Ticket::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'PSB: anda',
            'description' => "[Calon Pelanggan: anda | Telp: 081234567890 | Alamat: 081234567890 titik koordinat Kp. Cibodas RT 02/RW 04, Desa Sukamaju | Koordinat: -6.123456, 106.789012 | Paket: 20 Mbps]",
            'status' => 'assigned',
            'priority' => 'medium',
            'branch_id' => $this->branch->id,
            'assigned_to' => $this->technician->id,
        ]);

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'PSB: anda',
            'type' => 'installation',
            'status' => 'assigned',
            'ticket_id' => $ticket->id,
            'assigned_to' => $this->technician->id,
            'branch_id' => $this->branch->id,
            'description' => "081234567890 titik koordinat Kp. Cibodas RT 02/RW 04, Desa Sukamaju",
            'latitude' => -6.123456,
            'longitude' => 106.789012,
        ]);

        Sanctum::actingAs($this->technician);

        $response = $this->getJson('/api/mobile/workforce/work-orders');
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertNotEmpty($data);

        $matched = collect($data)->firstWhere('id', $wo->id);
        $this->assertNotNull($matched);

        // Verify customer object
        $this->assertEquals('anda', $matched['customer']['name']);
        $this->assertEquals('081234567890', $matched['customer']['phone']);
        $this->assertEquals('081234567890', $matched['customer']['whatsapp']);
        $this->assertStringNotContainsString('081234567890', $matched['customer']['address']);
        $this->assertStringNotContainsString('titik koordinat', $matched['customer']['address']);
        $this->assertStringContainsString('Kp. Cibodas RT 02/RW 04, Desa Sukamaju', $matched['customer']['address']);

        // Verify coordinates are separate numeric fields
        $this->assertEquals(-6.123456, $matched['latitude']);
        $this->assertEquals(106.789012, $matched['longitude']);
    }

    /**
     * Pathway 2: Ticket Admin -> Web Wizard (Livewire selectWorkOrder)
     */
    public function test_pathway_2_ticket_admin_to_web_wizard_clean_mapping(): void
    {
        $ticket = Ticket::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'PSB: anda',
            'description' => "[Calon Pelanggan: anda | Telp: 081234567890 | Alamat: 081234567890 titik koordinat Kp. Cibodas RT 02/RW 04, Desa Sukamaju | Koordinat: -6.123456, 106.789012 | Paket: 20 Mbps]",
            'status' => 'assigned',
            'priority' => 'medium',
            'branch_id' => $this->branch->id,
            'assigned_to' => $this->technician->id,
        ]);

        $this->actingAs($this->technician);

        Livewire::test(Wizard::class)
            ->call('selectWorkOrder', 'ticket', $ticket->id)
            ->assertSet('is_new_customer', true)
            ->assertSet('new_customer_name', 'anda')
            ->assertSet('new_customer_phone', '081234567890')
            ->assertSet('new_customer_address', 'Kp. Cibodas RT 02/RW 04, Desa Sukamaju')
            ->assertSet('latitude', -6.123456)
            ->assertSet('longitude', 106.789012);
    }

    /**
     * Pathway 3: Installation Admin -> Mobile API (/api/mobile/workforce/work-orders)
     */
    public function test_pathway_3_installation_admin_to_mobile_api_clean_mapping(): void
    {
        $installation = Installation::create([
            'code' => 'INS-2026-001',
            'status' => 'assigned',
            'branch_id' => $this->branch->id,
            'technician_id' => $this->technician->id,
            'customer_name' => 'anda',
            'customer_phone' => '081234567890',
            'installation_address' => '081234567890 titik koordinat Kp. Cibodas RT 02/RW 04, Desa Sukamaju [Titik: -6.123456, 106.789012]',
            'latitude' => -6.123456,
            'longitude' => 106.789012,
        ]);

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'Instalasi: anda',
            'type' => 'installation',
            'status' => 'assigned',
            'installation_id' => $installation->id,
            'assigned_to' => $this->technician->id,
            'branch_id' => $this->branch->id,
            'description' => "081234567890 titik koordinat Kp. Cibodas RT 02/RW 04, Desa Sukamaju",
            'latitude' => -6.123456,
            'longitude' => 106.789012,
            'customer_id' => null,
        ]);

        Sanctum::actingAs($this->technician);

        $response = $this->getJson('/api/mobile/workforce/work-orders');
        $response->assertStatus(200);

        $data = $response->json('data');
        $matched = collect($data)->firstWhere('id', $wo->id);
        $this->assertNotNull($matched);

        $this->assertEquals('anda', $matched['customer']['name']);
        $this->assertEquals('081234567890', $matched['customer']['phone']);
        $this->assertEquals('081234567890', $matched['customer']['whatsapp']);
        $this->assertStringNotContainsString('081234567890', $matched['customer']['address']);
        $this->assertStringNotContainsString('titik koordinat', $matched['customer']['address']);
        $this->assertStringContainsString('Kp. Cibodas RT 02/RW 04, Desa Sukamaju', $matched['customer']['address']);
        $this->assertEquals(-6.123456, $matched['latitude']);
        $this->assertEquals(106.789012, $matched['longitude']);
    }

    /**
     * Pathway 4: Installation Admin -> Web Wizard (Livewire selectWorkOrder)
     */
    public function test_pathway_4_installation_admin_to_web_wizard_clean_mapping(): void
    {
        $installation = Installation::create([
            'code' => 'INS-2026-002',
            'status' => 'assigned',
            'branch_id' => $this->branch->id,
            'technician_id' => $this->technician->id,
            'customer_name' => 'anda',
            'customer_phone' => '081234567890',
            'installation_address' => '081234567890 titik koordinat Kp. Cibodas RT 02/RW 04, Desa Sukamaju [Titik: -6.123456, 106.789012]',
            'latitude' => -6.123456,
            'longitude' => 106.789012,
        ]);

        $this->actingAs($this->technician);

        Livewire::test(Wizard::class)
            ->call('selectWorkOrder', 'installation', $installation->id)
            ->assertSet('is_new_customer', true)
            ->assertSet('new_customer_name', 'anda')
            ->assertSet('new_customer_phone', '081234567890')
            ->assertSet('new_customer_address', 'Kp. Cibodas RT 02/RW 04, Desa Sukamaju')
            ->assertSet('latitude', -6.123456)
            ->assertSet('longitude', 106.789012);
    }

    /**
     * Test direct sanitizer prevents corrupt input from ever displaying '-' or mixed phone in address.
     */
    public function test_sanitizer_resolves_actual_reported_corrupted_payload(): void
    {
        // Actual user reported case:
        // Source had Phone: 081234567890, but came into Wizard as Phone: '-', Address: '081234567890 titik koordinat Kp. Cibodas, Desa Sukamaju'
        $result = CustomerFieldSanitizer::sanitize(
            name: 'anda',
            phone: '-',
            address: '081234567890 titik koordinat Kp. Cibodas, Desa Sukamaju'
        );

        $this->assertEquals('anda', $result['name']);
        $this->assertEquals('081234567890', $result['phone']);
        $this->assertEquals('081234567890', $result['whatsapp']);
        $this->assertEquals('Kp. Cibodas, Desa Sukamaju', $result['address']);
        $this->assertNotEquals('-', $result['phone']);
    }
}
