<?php

namespace Tests\Feature;

use App\Livewire\Isp\Technician\Installation\Wizard;
use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\Customer\CustomerService;
use App\Models\Inventory\InventoryCustody;
use App\Models\Inventory\Item;
use App\Models\ISP\Odp;
use App\Models\ISP\Olt;
use App\Models\ISP\PonPort;
use App\Models\ISP\ServiceProfile;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Models\Workforce\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class PsbWizardRoundTripPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected User $technician;
    protected Branch $branch;
    protected ServiceProfile $serviceProfile;
    protected Olt $olt;
    protected PonPort $ponPort;
    protected Odp $odp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Branch Jakarta Pusat',
            'code' => 'JKT-PST',
            'is_active' => true,
        ]);

        $role = Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Teknisi', 'guard_name' => 'web']);

        $this->technician = User::factory()->create([
            'name' => 'Budi Teknisi',
            'email' => 'budi_technician@dsbilling.test',
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

        $profileType = \App\Models\ISP\ServiceProfileType::firstOrCreate(
            ['code' => 'INTERNET'],
            ['name' => 'Internet Profile', 'status' => 'active']
        );

        $this->serviceProfile = ServiceProfile::create([
            'service_profile_type_id' => $profileType->id,
            'name' => 'Paket Internet 20 Mbps',
            'code' => 'INET-20M',
            'service_type' => 'pppoe',
            'base_price' => 150000,
            'price' => 150000,
            'download_speed' => 20480,
            'upload_speed' => 20480,
            'is_active' => true,
            'branch_id' => $this->branch->id,
        ]);

        $this->olt = Olt::create([
            'name' => 'OLT Pusat 01',
            'code' => 'OLT-PST-01',
            'ip_address' => '10.10.10.1',
            'brand' => 'Huawei',
            'model' => 'MA5800-X7',
            'status' => 'active',
            'branch_id' => $this->branch->id,
        ]);

        $this->ponPort = PonPort::create([
            'olt_id' => $this->olt->id,
            'code' => 'PON-01',
            'port_number' => 1,
            'port_index' => 1,
            'name' => 'PON 1',
            'status' => 'active',
        ]);

        $this->odp = Odp::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'ODP Cibodas 01',
            'code' => 'ODP-CBD-01',
            'port_count' => 8,
            'used_port_count' => 1,
            'latitude' => -6.123456,
            'longitude' => 106.789012,
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        // Provide technician with inventory custody for ONT devices
        $item = Item::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'ONT FiberHome HG6821M',
            'code' => 'ONT-FH-01',
            'sku' => 'ONT-FH-01',
            'unit' => 'unit',
            'type' => 'serialized',
            'is_active' => true,
        ]);

        InventoryCustody::create([
            'holder_id' => $this->technician->id,
            'item_id' => $item->id,
            'quantity' => 10,
            'used_quantity' => 0,
            'returned_quantity' => 0,
            'status' => 'in_custody',
        ]);
    }

    /**
     * Read-only integrity: Opening or viewing the Wizard must NOT mutate database records.
     */
    public function test_opening_wizard_or_fetching_work_order_does_not_mutate_records(): void
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

        $initialTicketUpdatedAt = $ticket->updated_at;
        $initialWoUpdatedAt = $wo->updated_at;

        // 1. Mobile API read
        Sanctum::actingAs($this->technician);
        $res = $this->getJson("/api/mobile/workforce/work-orders/{$wo->id}");
        $res->assertOk();

        // 2. Web Wizard Livewire select
        $this->actingAs($this->technician);
        Livewire::test(Wizard::class)
            ->call('selectWorkOrder', 'ticket', $ticket->id)
            ->assertSet('new_customer_phone', '081234567890')
            ->assertSet('new_customer_address', 'Kp. Cibodas RT 02/RW 04, Desa Sukamaju');

        // Verify neither Ticket nor WorkOrder records were mutated
        $this->assertEquals($initialTicketUpdatedAt, $ticket->fresh()->updated_at);
        $this->assertEquals($initialWoUpdatedAt, $wo->fresh()->updated_at);
        $this->assertNull(Customer::where('name', 'anda')->first());
    }

    /**
     * Pathway A: Ticket Admin -> Android Wizard Provisioning -> DB Persistence -> Read-back
     */
    public function test_pathway_a_ticket_admin_android_roundtrip_persistence(): void
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

        // 1. Initial Read via Mobile API
        $fetchRes = $this->getJson("/api/mobile/workforce/work-orders/{$wo->id}");
        $fetchRes->assertOk();
        $woData = $fetchRes->json('data');

        $this->assertEquals('081234567890', $woData['customer']['phone']);
        $this->assertEquals('Kp. Cibodas RT 02/RW 04, Desa Sukamaju', $woData['customer']['address']);
        $this->assertEquals(-6.123456, $woData['customer']['latitude']);
        $this->assertEquals(106.789012, $woData['customer']['longitude']);

        // 2. Android Provisioning ONT Call
        $provRes = $this->postJson('/api/mobile/network/devices/provision-ont', [
            'work_order_id' => $wo->id,
            'serial_number' => 'ZTEG00000001',
            'customer_name' => $woData['customer']['name'],
            'customer_phone' => $woData['customer']['phone'],
            'customer_address' => $woData['customer']['address'],
            'latitude' => $woData['customer']['latitude'],
            'longitude' => $woData['customer']['longitude'],
            'service_profile_id' => $this->serviceProfile->id,
            'odp_id' => $this->odp->id,
            'odp_port' => 1,
            'olt_id' => $this->olt->id,
            'pon_port' => $this->ponPort->port_number,
        ]);

        $provRes->assertOk()->assertJsonPath('success', true);

        // 3. Database Persistence Verification
        $customer = Customer::where('phone', '081234567890')->first();
        $this->assertNotNull($customer, 'Customer record must be created in DB');
        $this->assertEquals('anda', $customer->name);
        $this->assertSame('081234567890', $customer->phone, 'Leading zero must be preserved as string');
        $this->assertEquals('Kp. Cibodas RT 02/RW 04, Desa Sukamaju', $customer->address);
        $this->assertStringNotContainsString('081234567890', $customer->address);
        $this->assertStringNotContainsString('titik koordinat', $customer->address);
        $this->assertEquals(-6.123456, (float) $customer->latitude);
        $this->assertEquals(106.789012, (float) $customer->longitude);

        // Work order is linked
        $this->assertEquals($customer->id, $wo->fresh()->customer_id);

        // 4. Re-fetch via Mobile API (Read-back)
        $readBackRes = $this->getJson("/api/mobile/workforce/work-orders/{$wo->id}");
        $readBackRes->assertOk();
        $readData = $readBackRes->json('data');

        $this->assertSame('081234567890', $readData['customer']['phone']);
        $this->assertSame('081234567890', $readData['customer']['whatsapp']);
        $this->assertEquals('Kp. Cibodas RT 02/RW 04, Desa Sukamaju', $readData['customer']['address']);
        $this->assertEquals(-6.123456, $readData['customer']['latitude']);
        $this->assertEquals(106.789012, $readData['customer']['longitude']);
    }

    /**
     * Pathway B: Ticket Admin -> Web Wizard -> DB Persistence -> Re-opening Web Wizard
     */
    public function test_pathway_b_ticket_admin_web_wizard_roundtrip_persistence(): void
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

        // 1. Initial select in Web Wizard
        $testable = Livewire::test(Wizard::class)
            ->call('selectWorkOrder', 'ticket', $ticket->id)
            ->assertSet('is_new_customer', true)
            ->assertSet('new_customer_name', 'anda')
            ->assertSet('new_customer_phone', '081234567890')
            ->assertSet('new_customer_address', 'Kp. Cibodas RT 02/RW 04, Desa Sukamaju')
            ->assertSet('latitude', -6.123456)
            ->assertSet('longitude', 106.789012);

        // 2. Simulate Save/Submit (Direct Customer creation using sanitized state as Wizard does in Step 6)
        $cleanPhone = \App\Services\Support\CustomerFieldSanitizer::cleanPhone($testable->get('new_customer_phone'));
        $cleanAddr = \App\Services\Support\CustomerFieldSanitizer::cleanAddress($testable->get('new_customer_address'));
        $lat = $testable->get('latitude');
        $lng = $testable->get('longitude');

        $customer = Customer::create([
            'code' => 'CUST-TEST-001',
            'name' => $testable->get('new_customer_name'),
            'phone' => $cleanPhone,
            'address' => $cleanAddr,
            'latitude' => $lat,
            'longitude' => $lng,
            'branch_id' => $this->branch->id,
            'status' => 'active',
        ]);

        $ticket->customer_id = $customer->id;
        $ticket->save();

        // 3. Database Persistence Verification
        $saved = Customer::find($customer->id);
        $this->assertSame('081234567890', $saved->phone);
        $this->assertEquals('Kp. Cibodas RT 02/RW 04, Desa Sukamaju', $saved->address);
        $this->assertEquals(-6.123456, (float) $saved->latitude);
        $this->assertEquals(106.789012, (float) $saved->longitude);

        // 4. Re-opening Web Wizard with Ticket linked to Customer
        Livewire::test(Wizard::class)
            ->call('selectWorkOrder', 'ticket', $ticket->id)
            ->assertSet('is_new_customer', false)
            ->assertSet('customer_id', (string) $customer->id)
            ->assertSet('latitude', -6.123456)
            ->assertSet('longitude', 106.789012);
    }

    /**
     * Pathway C: Installation Admin -> Android Wizard Provisioning -> DB Persistence -> Read-back
     */
    public function test_pathway_c_installation_admin_android_roundtrip_persistence(): void
    {
        $installation = Installation::create([
            'code' => 'INS-2026-ROUNDTRIP-C',
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
        ]);

        Sanctum::actingAs($this->technician);

        // 1. Initial Read via Mobile API
        $fetchRes = $this->getJson("/api/mobile/workforce/work-orders/{$wo->id}");
        $fetchRes->assertOk();
        $woData = $fetchRes->json('data');

        $this->assertEquals('081234567890', $woData['customer']['phone']);
        $this->assertEquals('Kp. Cibodas RT 02/RW 04, Desa Sukamaju', $woData['customer']['address']);
        $this->assertEquals(-6.123456, $woData['customer']['latitude']);
        $this->assertEquals(106.789012, $woData['customer']['longitude']);

        // 2. Android Provisioning ONT Call
        $provRes = $this->postJson('/api/mobile/network/devices/provision-ont', [
            'work_order_id' => $wo->id,
            'serial_number' => 'ZTEG00000002',
            'customer_name' => $woData['customer']['name'],
            'customer_phone' => $woData['customer']['phone'],
            'customer_address' => $woData['customer']['address'],
            'latitude' => $woData['customer']['latitude'],
            'longitude' => $woData['customer']['longitude'],
            'service_profile_id' => $this->serviceProfile->id,
            'odp_id' => $this->odp->id,
            'odp_port' => 2,
            'olt_id' => $this->olt->id,
            'pon_port' => $this->ponPort->port_number,
        ]);

        $provRes->assertOk()->assertJsonPath('success', true);

        // 3. Database Persistence Verification
        $customer = Customer::where('phone', '081234567890')->first();
        $this->assertNotNull($customer);
        $this->assertSame('081234567890', $customer->phone);
        $this->assertEquals('Kp. Cibodas RT 02/RW 04, Desa Sukamaju', $customer->address);
        $this->assertEquals(-6.123456, (float) $customer->latitude);
        $this->assertEquals(106.789012, (float) $customer->longitude);

        // 4. Read-back via Mobile API
        $readBackRes = $this->getJson("/api/mobile/workforce/work-orders/{$wo->id}");
        $readBackRes->assertOk();
        $readData = $readBackRes->json('data');

        $this->assertSame('081234567890', $readData['customer']['phone']);
        $this->assertEquals('Kp. Cibodas RT 02/RW 04, Desa Sukamaju', $readData['customer']['address']);
        $this->assertEquals(-6.123456, $readData['customer']['latitude']);
        $this->assertEquals(106.789012, $readData['customer']['longitude']);
    }

    /**
     * Pathway D: Installation Admin -> Web Wizard -> DB Persistence -> Re-opening Web Wizard
     */
    public function test_pathway_d_installation_admin_web_wizard_roundtrip_persistence(): void
    {
        $installation = Installation::create([
            'code' => 'INS-2026-ROUNDTRIP-D',
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

        // 1. Initial select in Web Wizard
        $testable = Livewire::test(Wizard::class)
            ->call('selectWorkOrder', 'installation', $installation->id)
            ->assertSet('is_new_customer', true)
            ->assertSet('new_customer_name', 'anda')
            ->assertSet('new_customer_phone', '081234567890')
            ->assertSet('new_customer_address', 'Kp. Cibodas RT 02/RW 04, Desa Sukamaju')
            ->assertSet('latitude', -6.123456)
            ->assertSet('longitude', 106.789012);

        // 2. Persist Customer
        $cleanPhone = \App\Services\Support\CustomerFieldSanitizer::cleanPhone($testable->get('new_customer_phone'));
        $cleanAddr = \App\Services\Support\CustomerFieldSanitizer::cleanAddress($testable->get('new_customer_address'));

        $customer = Customer::create([
            'code' => 'CUST-TEST-002',
            'name' => $testable->get('new_customer_name'),
            'phone' => $cleanPhone,
            'address' => $cleanAddr,
            'latitude' => $testable->get('latitude'),
            'longitude' => $testable->get('longitude'),
            'branch_id' => $this->branch->id,
            'status' => 'active',
        ]);

        $installation->customer_id = $customer->id;
        $installation->save();

        // 3. Database Persistence Verification
        $saved = Customer::find($customer->id);
        $this->assertSame('081234567890', $saved->phone);
        $this->assertEquals('Kp. Cibodas RT 02/RW 04, Desa Sukamaju', $saved->address);
        $this->assertEquals(-6.123456, (float) $saved->latitude);
        $this->assertEquals(106.789012, (float) $saved->longitude);

        // 4. Re-open Web Wizard
        Livewire::test(Wizard::class)
            ->call('selectWorkOrder', 'installation', $installation->id)
            ->assertSet('is_new_customer', false)
            ->assertSet('customer_id', (string) $customer->id)
            ->assertSet('latitude', -6.123456)
            ->assertSet('longitude', 106.789012);
    }
}
