<?php

namespace Tests\Feature;

use App\Jobs\ACS\ProvisionOntLifecycleJob;
use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\Customer\CustomerService;
use App\Models\Inventory\InventoryCustody;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemSerial;
use App\Models\ISP\Odp;
use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\ISP\OnuHistory;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Models\Workforce\WorkOrder;
use App\Services\ISP\GenieAcsProvisioningService;
use App\Services\ISP\OntLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EnterpriseOntLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $technician;
    protected Branch $branch;
    protected Olt $olt;
    protected Odp $odp;
    protected Item $ontItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Cabang Utama',
            'code' => 'CBG-01',
            'is_active' => true,
        ]);

        $techRole = Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Technician']);

        $this->technician = User::create([
            'name' => 'Teknisi Lapangan',
            'email' => 'tech.lifecycle@example.com',
            'username' => 'tech_lifecycle',
            'password' => bcrypt('password'),
            'branch_id' => $this->branch->id,
            'job_function' => 'TECHNICIAN',
            'is_active' => true,
        ]);
        $this->technician->roles()->attach($techRole->id);

        if (\Illuminate\Support\Facades\Schema::hasTable('attendances')) {
            \App\Models\Workforce\Attendance::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'technician_id' => $this->technician->id,
                'date' => now()->toDateString(),
                'checked_in_at' => now(),
                'status' => 'checked_in',
                'branch_id' => $this->branch->id,
            ]);
        }

        $this->olt = Olt::withoutGlobalScopes()->create([
            'name' => 'OLT ZTE Pusat',
            'code' => 'OLT-01',
            'ip_address' => '10.10.10.2',
            'branch_id' => $this->branch->id,
            'status' => 'active',
        ]);

        $this->odp = Odp::withoutGlobalScopes()->create([
            'name' => 'ODP-01',
            'code' => 'ODP-01',
            'olt_id' => $this->olt->id,
            'branch_id' => $this->branch->id,
            'port_count' => 8,
            'used_port_count' => 0,
            'status' => 'active',
        ]);

        $this->ontItem = Item::withoutGlobalScopes()->create([
            'sku' => 'ONT-ZTE-F609',
            'name' => 'Modem ONT ZTE F609',
            'category' => 'Perangkat Aktif',
            'unit' => 'unit',
            'is_serialized' => true,
            'is_active' => true,
            'branch_id' => $this->branch->id,
        ]);
    }

    /**
     * 1. Test Sensus ONU baru dan existing (operational_status tidak dipaksa online tanpa sinyal).
     */
    public function test_sensus_new_and_existing_onu_sets_audit_status_without_forcing_online(): void
    {
        $customer = Customer::withoutGlobalScopes()->create([
            'code' => '2026111701',
            'name' => 'Pelanggan Sensus',
            'phone' => '081234567801',
            'status' => 'active',
            'branch_id' => $this->branch->id,
        ]);

        $service = app(OntLifecycleService::class);

        // A. Sensus ONU Baru (belum ada sinyal OLT -> operational_status = unknown)
        $resNew = $service->recordSensus([
            'transaction_id' => 'TX-SENSUS-NEW-1',
            'onu_sn' => 'ZTEG11112222',
            'olt_id' => $this->olt->id,
            'pon_port' => 2,
            'odp_id' => $this->odp->id,
            'odp_port' => 3,
            'customer_id' => $customer->id,
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ], $this->technician);

        $this->assertTrue($resNew['success']);
        $this->assertEquals(Onu::STATUS_ACTIVE, $resNew['status']);
        $this->assertEquals(Onu::AUDIT_AUDITED, $resNew['audit_status']);
        $this->assertEquals(Onu::OP_UNKNOWN, $resNew['operational_status']);

        $onu = Onu::withoutGlobalScopes()->find($resNew['onu_id']);
        $this->assertNotNull($onu->audited_at);
        $this->assertEquals($this->technician->id, $onu->audited_by);
        $this->assertEquals(1, $this->odp->fresh()->used_port_count);

        // B. Ketika ONU memiliki sinyal RX dari OLT, Sensus mengenali operational_status = online
        $onu->update(['rx_power_dbm' => -19.5]);

        $resExisting = $service->recordSensus([
            'transaction_id' => 'TX-SENSUS-EXIST-1',
            'onu_sn' => 'ZTEG11112222',
            'olt_id' => $this->olt->id,
            'pon_port' => 2,
            'odp_id' => $this->odp->id,
            'odp_port' => 4,
            'customer_id' => $customer->id,
        ], $this->technician);

        $this->assertEquals(Onu::OP_ONLINE, $resExisting['operational_status']);
        $this->assertEquals(2, OnuHistory::withoutGlobalScopes()->where('onu_id', $onu->id)->where('activity', 'sensus')->count());
    }

    /**
     * 2. Test Sensus ONU Titipan.
     */
    public function test_sensus_onu_titipan(): void
    {
        $service = app(OntLifecycleService::class);

        $res = $service->recordSensus([
            'transaction_id' => 'TX-SENSUS-TITIPAN-1',
            'is_titipan' => true,
            'titipan_name' => 'CCTV RT 05',
            'titipan_notes' => 'Titipan perangkat pemantau lingkungan',
            'onu_sn' => 'HWTC99998888',
            'olt_id' => $this->olt->id,
            'pon_port' => 1,
            'odp_id' => $this->odp->id,
            'odp_port' => 8,
        ], $this->technician);

        $this->assertTrue($res['success']);
        $this->assertTrue($res['is_titipan']);
        $this->assertNull($res['customer_id']);

        $onu = Onu::withoutGlobalScopes()->find($res['onu_id']);
        $this->assertEquals('Titipan: CCTV RT 05', $onu->name);
        $this->assertEquals(Onu::AUDIT_AUDITED, $onu->audit_status);
        $this->assertDatabaseHas('onu_histories', [
            'onu_id' => $onu->id,
            'activity' => 'sensus',
            'transaction_id' => 'TX-SENSUS-TITIPAN-1',
        ]);
    }

    /**
     * 3 & 5. Test Swap ONT berhasil, stok teknisi berkurang 1, dan gagal jika SN baru dipakai pelanggan lain.
     */
    public function test_swap_ont_success_and_failure_and_stock_deduction(): void
    {
        $customer1 = Customer::withoutGlobalScopes()->create([
            'code' => '2026111702',
            'name' => 'Pelanggan Swap 1',
            'phone' => '081234567802',
            'status' => 'active',
            'branch_id' => $this->branch->id,
        ]);

        $oldOnu = Onu::withoutGlobalScopes()->create([
            'serial_number' => 'ZTEG00001111',
            'name' => 'ONU Lama',
            'model' => 'ZTE F609',
            'olt_id' => $this->olt->id,
            'odp_id' => $this->odp->id,
            'wifi_ssid' => 'WINETS-KELUARGA',
            'wifi_password' => 'rahasia123',
            'status' => Onu::STATUS_ACTIVE,
            'branch_id' => $this->branch->id,
        ]);

        $cs1 = CustomerService::withoutGlobalScopes()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'customer_id' => $customer1->id,
            'onu_id' => $oldOnu->id,
            'username' => 'pelanggan1_pppoe',
            'password' => 'pass123',
            'status' => 'active',
        ]);

        // Siapkan stok ONT baru di Custody Teknisi
        $newSerial = ItemSerial::withoutGlobalScopes()->create([
            'item_id' => $this->ontItem->id,
            'serial_number' => 'ZTEG00002222',
            'custodian_id' => $this->technician->id,
            'custodian_type' => 'technician',
            'status' => 'in_custody_technician',
            'condition' => 'good',
            'branch_id' => $this->branch->id,
        ]);

        $custody = InventoryCustody::withoutGlobalScopes()->create([
            'item_id' => $this->ontItem->id,
            'item_serial_id' => $newSerial->id,
            'holder_id' => $this->technician->id,
            'holder_type' => 'technician',
            'quantity' => 1,
            'used_quantity' => 0,
            'returned_quantity' => 0,
            'status' => 'in_custody',
            'branch_id' => $this->branch->id,
        ]);

        $service = app(OntLifecycleService::class);

        $swapRes = $service->executeSwap([
            'transaction_id' => 'TX-SWAP-001',
            'old_onu_id' => $oldOnu->id,
            'old_serial_number' => 'ZTEG00001111',
            'new_serial_number' => 'ZTEG00002222',
            'new_model' => 'ZTE F609 V3',
            'optical_power' => -18.4,
            'old_device_condition' => 'damaged',
            'replacement_reason' => 'Modem tersambar petir',
            'customer_id' => $customer1->id,
            'customer_service_id' => $cs1->id,
            'queue_provisioning' => true,
        ], $this->technician);

        $this->assertTrue($swapRes['success']);
        $this->assertEquals(Onu::STATUS_REPLACED, $oldOnu->fresh()->status);
        $this->assertEquals(Onu::CONDITION_DAMAGED, $oldOnu->fresh()->condition);

        $newOnu = Onu::withoutGlobalScopes()->find($swapRes['new_onu_id']);
        $this->assertEquals(Onu::STATUS_ACTIVE, $newOnu->status);
        $this->assertEquals('WINETS-KELUARGA', $newOnu->wifi_ssid);
        $this->assertEquals($newOnu->id, $cs1->fresh()->onu_id);

        // Pastikan stok custody teknisi berkurang (used_quantity = 1, status = used)
        $this->assertEquals(1.0, (float) $custody->fresh()->used_quantity);
        $this->assertEquals('used', $custody->fresh()->status);
        $this->assertEquals('installed', $newSerial->fresh()->status);

        // Pastikan riwayat swap_out & swap_in tercatat di onu_histories
        $this->assertDatabaseHas('onu_histories', [
            'onu_id' => $oldOnu->id,
            'activity' => 'swap_out',
            'new_status' => Onu::STATUS_REPLACED,
        ]);
        $this->assertDatabaseHas('onu_histories', [
            'onu_id' => $newOnu->id,
            'activity' => 'swap_in',
            'new_status' => Onu::STATUS_ACTIVE,
        ]);

        // Test Gagal Swap jika SN baru sedang aktif dipakai pelanggan lain
        $customer2 = Customer::withoutGlobalScopes()->create([
            'code' => '2026111703',
            'name' => 'Pelanggan 2',
            'phone' => '081234567803',
            'status' => 'active',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $service->executeSwap([
            'old_serial_number' => 'ZTEG99990000',
            'new_serial_number' => 'ZTEG00002222', // Sedang aktif di customer1!
            'customer_id' => $customer2->id,
        ], $this->technician);
    }

    /**
     * 4 & 5. Test Dismantle kondisi good (menambah stok teknisi) dan damaged (tidak menambah stok bagus).
     */
    public function test_dismantle_good_and_damaged_standardizes_status_and_updates_stock(): void
    {
        $customer = Customer::withoutGlobalScopes()->create([
            'code' => '2026111704',
            'name' => 'Pelanggan Dismantle',
            'phone' => '081234567804',
            'status' => 'active',
            'branch_id' => $this->branch->id,
        ]);

        // A. Dismantle kondisi GOOD
        $onuGood = Onu::withoutGlobalScopes()->create([
            'serial_number' => 'ZTEG55551111',
            'name' => 'ONU Dismantle Good',
            'model' => 'ZTE F609',
            'status' => Onu::STATUS_ACTIVE,
            'branch_id' => $this->branch->id,
        ]);

        $serialGood = ItemSerial::withoutGlobalScopes()->create([
            'item_id' => $this->ontItem->id,
            'serial_number' => 'ZTEG55551111',
            'customer_id' => $customer->id,
            'status' => 'installed',
            'condition' => 'good',
            'branch_id' => $this->branch->id,
        ]);

        $service = app(OntLifecycleService::class);
        $resGood = $service->executeDismantle([
            'transaction_id' => 'TX-DISM-GOOD-1',
            'onu_id' => $onuGood->id,
            'serial_number' => 'ZTEG55551111',
            'device_condition' => 'good',
            'dismantle_reason' => 'Pindah Rumah',
            'customer_id' => $customer->id,
        ], $this->technician);

        $this->assertEquals(Onu::STATUS_DISMANTLED, $resGood['status']);
        $this->assertEquals(Onu::CONDITION_GOOD, $resGood['condition']);
        $this->assertTrue($resGood['stock']['restocked']);
        $this->assertEquals('in_custody_technician', $serialGood->fresh()->status);

        // Pastikan masuk ke InventoryCustody teknisi (+1 available)
        $techCustody = InventoryCustody::withoutGlobalScopes()
            ->where('holder_id', $this->technician->id)
            ->where('item_serial_id', $serialGood->id)
            ->first();
        $this->assertNotNull($techCustody);
        $this->assertEquals(1.0, $techCustody->available_quantity);

        // B. Dismantle kondisi DAMAGED via API Android (memastikan status seragam 'dismantled' + condition 'damaged')
        $onuDamaged = Onu::withoutGlobalScopes()->create([
            'serial_number' => 'ZTEG55552222',
            'name' => 'ONU Dismantle Damaged',
            'model' => 'ZTE F609',
            'status' => Onu::STATUS_ACTIVE,
            'branch_id' => $this->branch->id,
        ]);

        Sanctum::actingAs($this->technician);
        $response = $this->postJson('/api/mobile/network/devices/dismantle', [
            'transaction_id' => 'TX-DISM-DMG-API-1',
            'serial_number' => 'ZTEG55552222',
            'device_condition' => 'damaged',
            'dismantle_reason' => 'ONT Terbakar',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', Onu::STATUS_DISMANTLED)
            ->assertJsonPath('data.condition', Onu::CONDITION_DAMAGED);

        $this->assertEquals(Onu::STATUS_DISMANTLED, $onuDamaged->fresh()->status);
        $this->assertEquals(Onu::CONDITION_DAMAGED, $onuDamaged->fresh()->condition);
    }

    /**
     * 6 & 7 & 11. Test Idempotency: Double submit dan Retry API saat jaringan terputus tidak menggandakan mutasi/histori.
     */
    public function test_idempotency_prevents_duplicate_mutations_and_histories_on_retry(): void
    {
        $onu = Onu::withoutGlobalScopes()->create([
            'serial_number' => 'ZTEG77771111',
            'name' => 'ONU Idempotent',
            'status' => Onu::STATUS_ACTIVE,
            'branch_id' => $this->branch->id,
        ]);

        Sanctum::actingAs($this->technician);

        $payload = [
            'transaction_id' => 'TX-RETRY-MOBILE-999',
            'serial_number' => 'ZTEG77771111',
            'device_condition' => 'good',
            'dismantle_reason' => 'Test Retry Jaringan Terputus',
        ];

        // Request 1
        $res1 = $this->postJson('/api/mobile/network/devices/dismantle', $payload);
        $res1->assertStatus(200)->assertJsonPath('data.idempotent_replay', false);

        // Request 2 (Retry dengan transaction_id yang sama)
        $res2 = $this->postJson('/api/mobile/network/devices/dismantle', $payload);
        $res2->assertStatus(200)->assertJsonPath('data.idempotent_replay', true);

        // Pastikan hanya ada 1 baris di onu_histories untuk transaction_id tersebut
        $this->assertEquals(
            1,
            OnuHistory::withoutGlobalScopes()->where('transaction_id', 'TX-RETRY-MOBILE-999')->count()
        );
    }

    /**
     * 8 & 9. Test GenieACS Queue/Pending/Failed & Tiket tidak resolved sebelum bukti pekerjaan valid.
     */
    public function test_ticket_not_resolved_before_valid_proof_and_genieacs_queue_job(): void
    {
        Queue::fake();

        $customer = Customer::withoutGlobalScopes()->create([
            'code' => '2026111705',
            'name' => 'Pelanggan Tiket',
            'phone' => '081234567805',
            'status' => 'active',
        ]);

        $ticket = Ticket::withoutGlobalScopes()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'customer_id' => $customer->id,
            'title' => 'Gangguan LOS Merah',
            'description' => 'Ganti ONT pelanggan',
            'status' => 'in_progress',
            'priority' => 'high',
            'assigned_to' => $this->technician->id,
        ]);

        $oldOnu = Onu::withoutGlobalScopes()->create([
            'serial_number' => 'ZTEG88881111',
            'name' => 'ONU Tiket Lama',
            'status' => Onu::STATUS_ACTIVE,
        ]);

        $service = app(OntLifecycleService::class);

        // 1. Jika require_proof = true tetapi foto/signature kosong -> tiket tetap 'in_progress'
        $res1 = $service->executeSwap([
            'transaction_id' => 'TX-TICKET-SWAP-1',
            'old_onu_id' => $oldOnu->id,
            'old_serial_number' => 'ZTEG88881111',
            'new_serial_number' => 'ZTEG88882222',
            'customer_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'require_proof' => true,
            'queue_provisioning' => true,
        ], $this->technician);

        $this->assertFalse($res1['ticket_resolved']);
        $this->assertEquals('in_progress', $ticket->fresh()->status);
        Queue::assertPushed(ProvisionOntLifecycleJob::class);

        // 2. Ketika dilengkapi bukti foto/tanda tangan -> tiket menjadi 'resolved'
        $res2 = $service->executeSwap([
            'transaction_id' => 'TX-TICKET-SWAP-2',
            'old_serial_number' => 'ZTEG88882222',
            'new_serial_number' => 'ZTEG88883333',
            'customer_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'require_proof' => true,
            'photo_after_path' => 'technician/swap/bukti.jpg',
            'digital_signature' => 'data:image/png;base64,iVBORw0KGgo=',
            'queue_provisioning' => true,
        ], $this->technician);

        $this->assertTrue($res2['ticket_resolved']);
        $this->assertEquals('resolved', $ticket->fresh()->status);
    }

    /**
     * 10 & 11. Test PSB Provisioning & Sinkronisasi WorkOrder source_type/source_id di Android.
     */
    public function test_psb_provisioning_records_history_and_work_order_tracks_source(): void
    {
        $customer = Customer::withoutGlobalScopes()->create([
            'code' => '2026111706',
            'name' => 'Pelanggan PSB Baru',
            'phone' => '081234567806',
            'status' => 'active',
            'branch_id' => $this->branch->id,
        ]);

        $wo = WorkOrder::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'installation',
            'source_type' => 'installation',
            'source_id' => 77,
            'customer_id' => $customer->id,
            'assigned_to' => $this->technician->id,
            'status' => 'in_progress',
            'title' => 'PSB: Pelanggan PSB Baru',
            'branch_id' => $this->branch->id,
        ]);

        Sanctum::actingAs($this->technician);

        $res = $this->postJson('/api/mobile/network/devices/provision-ont', [
            'serial_number' => 'ZTEG44449999',
            'model' => 'ZTE F609',
            'olt_id' => $this->olt->id,
            'odp_id' => $this->odp->id,
            'customer_id' => $customer->id,
            'work_order_id' => $wo->id,
            'wifi_ssid' => 'WINETS-PSB',
            'wifi_password' => '12345678',
            'optical_power' => -17.8,
            'enable_tr069_iphost' => false,
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.onu.status', Onu::STATUS_ACTIVE)
            ->assertJsonPath('data.onu.condition', Onu::CONDITION_GOOD)
            ->assertJsonPath('data.onu.operational_status', Onu::OP_ONLINE);

        $this->assertDatabaseHas('onu_histories', [
            'serial_number' => 'ZTEG44449999',
            'activity' => 'psb',
            'work_order_id' => $wo->id,
        ]);

        // Cek endpoint WorkOrders mengembalikan source_type & source_id
        $woRes = $this->getJson("/api/mobile/work-orders/{$wo->id}");
        $woRes->assertStatus(200)
            ->assertJsonPath('data.source_type', 'installation')
            ->assertJsonPath('data.source_id', 77);
    }
}
