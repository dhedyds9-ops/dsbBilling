<?php

namespace Tests\Feature;

use App\Jobs\ACS\ProvisionOntLifecycleJob;
use App\Models\CRM\Customer;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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

        if (Schema::hasTable('attendances')) {
            \App\Models\Workforce\Attendance::create([
                'uuid' => (string) Str::uuid(),
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

    protected function giveTechnicianOntCustody(string $sn): ItemSerial
    {
        $serial = ItemSerial::withoutGlobalScopes()->create([
            'item_id' => $this->ontItem->id,
            'serial_number' => strtoupper(trim($sn)),
            'custodian_id' => $this->technician->id,
            'custodian_type' => 'technician',
            'status' => 'in_custody_technician',
            'condition' => 'good',
            'branch_id' => $this->branch->id,
        ]);

        InventoryCustody::withoutGlobalScopes()->create([
            'item_id' => $this->ontItem->id,
            'item_serial_id' => $serial->id,
            'holder_id' => $this->technician->id,
            'holder_type' => 'technician',
            'quantity' => 1,
            'used_quantity' => 0,
            'returned_quantity' => 0,
            'status' => 'in_custody',
            'branch_id' => $this->branch->id,
        ]);

        return $serial;
    }

    /**
     * 1. Sensus ONU baru & existing + Perpindahan ODP & Perhitungan used_port_count.
     */
    public function test_sensus_new_existing_and_odp_port_recalculation(): void
    {
        $odp2 = Odp::withoutGlobalScopes()->create([
            'name' => 'ODP-02',
            'code' => 'ODP-02',
            'olt_id' => $this->olt->id,
            'branch_id' => $this->branch->id,
            'port_count' => 16,
            'used_port_count' => 0,
            'status' => 'active',
        ]);

        $customer = Customer::withoutGlobalScopes()->create([
            'code' => '2026111701',
            'name' => 'Pelanggan Sensus',
            'phone' => '081234567801',
            'status' => 'active',
            'branch_id' => $this->branch->id,
        ]);

        $service = app(OntLifecycleService::class);

        // A. Sensus ONU Baru di ODP-01 (tanpa sinyal -> operational_status = unknown)
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

        $this->assertEquals(Onu::STATUS_ACTIVE, $resNew['status']);
        $this->assertEquals(Onu::AUDIT_AUDITED, $resNew['audit_status']);
        $this->assertEquals(Onu::OP_UNKNOWN, $resNew['operational_status']);
        $this->assertEquals(1, $this->odp->fresh()->used_port_count);
        $this->assertEquals(0, $odp2->fresh()->used_port_count);

        // B. Pindahkan ONU ke ODP-02 + beri sinyal RX -> ODP-01 harus berkurang jadi 0, ODP-02 naik jadi 1
        $onu = Onu::withoutGlobalScopes()->find($resNew['onu_id']);
        $onu->update(['rx_power_dbm' => -19.5]);

        $resMove = $service->recordSensus([
            'transaction_id' => 'TX-SENSUS-MOVE-1',
            'onu_sn' => 'ZTEG11112222',
            'olt_id' => $this->olt->id,
            'pon_port' => 2,
            'odp_id' => $odp2->id,
            'odp_port' => 5,
            'customer_id' => $customer->id,
        ], $this->technician);

        $this->assertEquals(Onu::OP_ONLINE, $resMove['operational_status']);
        $this->assertEquals(0, $this->odp->fresh()->used_port_count);
        $this->assertEquals(1, $odp2->fresh()->used_port_count);

        // C. Saat ONU di-dismantle dari ODP-02, used_port_count pada ODP-02 harus turun menjadi 0
        $service->executeDismantle([
            'transaction_id' => 'TX-DISM-ODP-1',
            'onu_id' => $onu->id,
            'serial_number' => 'ZTEG11112222',
            'device_condition' => 'good',
        ], $this->technician);

        $this->assertEquals(0, $odp2->fresh()->used_port_count);
    }

    /**
     * 2. Sensus dengan mode Titipan & blokir pencurian SN aktif milik pelanggan resmi.
     */
    public function test_sensus_titipan_and_blocks_stealing_active_customer_sn(): void
    {
        $service = app(OntLifecycleService::class);

        // A. Sensus Titipan Valid
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

        // B. Buat pelanggan aktif dengan ONU resmi
        $activeCust = Customer::withoutGlobalScopes()->create([
            'code' => '2026111710',
            'name' => 'Pelanggan Resmi Aktif',
            'phone' => '081299990001',
            'status' => 'active',
        ]);
        $activeOnu = Onu::withoutGlobalScopes()->create([
            'serial_number' => 'ZTEGACTIVE01',
            'name' => 'ONU Pelanggan Resmi',
            'status' => Onu::STATUS_ACTIVE,
        ]);
        CustomerService::withoutGlobalScopes()->create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $activeCust->id,
            'onu_id' => $activeOnu->id,
            'username' => 'resmi_pppoe',
            'password' => '123456',
            'status' => 'active',
        ]);

        // C. Coba klaim ZTEGACTIVE01 sebagai ONU Titipan -> WAJIB DITOLAK!
        $this->expectException(\InvalidArgumentException::class);
        $service->recordSensus([
            'is_titipan' => true,
            'titipan_name' => 'Titipan Ilegal',
            'onu_sn' => 'ZTEGACTIVE01',
            'olt_id' => $this->olt->id,
            'pon_port' => 1,
            'odp_id' => $this->odp->id,
        ], $this->technician);
    }

    /**
     * 3 & 4. Concurrent Swap & Concurrent Dismantle (APK lama tanpa transaction_id & request paralel).
     */
    public function test_concurrent_swap_and_concurrent_dismantle_prevent_double_mutation(): void
    {
        Queue::fake();

        $customer = Customer::withoutGlobalScopes()->create([
            'code' => '2026111720',
            'name' => 'Pelanggan Concurrent',
            'phone' => '081299990002',
            'status' => 'active',
        ]);

        $oldOnu = Onu::withoutGlobalScopes()->create([
            'serial_number' => 'ZTEGCONC0001',
            'name' => 'ONU Lama Concurrent',
            'status' => Onu::STATUS_ACTIVE,
            'branch_id' => $this->branch->id,
        ]);

        $this->giveTechnicianOntCustody('ZTEGCONC0002');
        $this->giveTechnicianOntCustody('ZTEGCONC0003');

        Sanctum::actingAs($this->technician);

        // A. Swap pertama tanpa transaction_id (APK lama) -> Berhasil (200)
        $swap1 = $this->postJson('/api/mobile/network/devices/swap-ont', [
            'old_serial_number' => 'ZTEGCONC0001',
            'new_serial_number' => 'ZTEGCONC0002',
            'old_device_condition' => 'damaged',
        ]);
        $swap1->assertStatus(200);

        // B. Concurrent / Retry Swap kedua tanpa transaction_id pada ONT lama yang sama -> Ditolak 422!
        $swap2 = $this->postJson('/api/mobile/network/devices/swap-ont', [
            'old_serial_number' => 'ZTEGCONC0001',
            'new_serial_number' => 'ZTEGCONC0003',
            'old_device_condition' => 'damaged',
        ]);
        $swap2->assertStatus(422);

        // Pastikan ZTEGCONC0003 masih utuh di custody teknisi (tidak terpotong!)
        $custody3 = InventoryCustody::withoutGlobalScopes()
            ->whereHas('itemSerial', fn ($q) => $q->where('serial_number', 'ZTEGCONC0003'))
            ->first();
        $this->assertEquals(1.0, $custody3->available_quantity);

        // C. Dismantle pertama tanpa transaction_id pada ZTEGCONC0002 -> Berhasil (200)
        $dism1 = $this->postJson('/api/mobile/network/devices/dismantle', [
            'serial_number' => 'ZTEGCONC0002',
            'device_condition' => 'good',
            'dismantle_reason' => 'Cabut Uji Concurrent',
        ]);
        $dism1->assertStatus(200);

        // D. Concurrent / Retry Dismantle kedua tanpa transaction_id pada ZTEGCONC0002 -> Ditolak 422!
        $dism2 = $this->postJson('/api/mobile/network/devices/dismantle', [
            'serial_number' => 'ZTEGCONC0002',
            'device_condition' => 'good',
            'dismantle_reason' => 'Cabut Uji Concurrent Duplikat',
        ]);
        $dism2->assertStatus(422);

        // Pastikan histori dismantle untuk ZTEGCONC0002 hanya 1 baris
        $this->assertEquals(
            1,
            OnuHistory::withoutGlobalScopes()
                ->where('serial_number', 'ZTEGCONC0002')
                ->where('activity', 'dismantle')
                ->count()
        );
    }

    /**
     * 5 & 6 & 7. Inventory Shortage, Kepemilikan Serial, ONT Lama Kondisi Good vs Damaged.
     */
    public function test_inventory_shortage_ownership_and_good_vs_damaged_custody_handling(): void
    {
        Queue::fake();
        $service = app(OntLifecycleService::class);

        $otherTech = User::create([
            'name' => 'Teknisi Lain',
            'email' => 'other.tech@example.com',
            'username' => 'other_tech',
            'password' => bcrypt('password'),
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        // Serial 1: Masih di gudang (in_warehouse)
        ItemSerial::withoutGlobalScopes()->create([
            'item_id' => $this->ontItem->id,
            'serial_number' => 'ZTEGWAREHOUSE1',
            'warehouse_id' => 1,
            'status' => 'in_warehouse',
            'condition' => 'good',
        ]);

        // Serial 2: Dipegang teknisi lain
        ItemSerial::withoutGlobalScopes()->create([
            'item_id' => $this->ontItem->id,
            'serial_number' => 'ZTEGOTHERTECH1',
            'custodian_id' => $otherTech->id,
            'custodian_type' => 'technician',
            'status' => 'in_custody_technician',
            'condition' => 'good',
        ]);

        // Serial 3: Kondisi damaged
        ItemSerial::withoutGlobalScopes()->create([
            'item_id' => $this->ontItem->id,
            'serial_number' => 'ZTEGDAMAGED01',
            'custodian_id' => $this->technician->id,
            'custodian_type' => 'technician',
            'status' => 'damaged',
            'condition' => 'damaged',
        ]);

        $oldOnu = Onu::withoutGlobalScopes()->create([
            'serial_number' => 'ZTEGOLDGOOD01',
            'name' => 'ONU Lama Bagus',
            'status' => Onu::STATUS_ACTIVE,
        ]);

        // A. Coba Swap pakai SN yang masih di gudang -> Gagal!
        try {
            $service->executeSwap([
                'old_onu_id' => $oldOnu->id,
                'old_serial_number' => 'ZTEGOLDGOOD01',
                'new_serial_number' => 'ZTEGWAREHOUSE1',
            ], $this->technician);
            $this->fail('Swap seharusnya gagal karena SN masih di gudang.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('gudang', $e->getMessage());
        }

        // B. Coba Swap pakai SN milik teknisi lain -> Gagal!
        try {
            $service->executeSwap([
                'old_onu_id' => $oldOnu->id,
                'old_serial_number' => 'ZTEGOLDGOOD01',
                'new_serial_number' => 'ZTEGOTHERTECH1',
            ], $this->technician);
            $this->fail('Swap seharusnya gagal karena SN milik teknisi lain.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('teknisi lain', $e->getMessage());
        }

        // C. Coba Swap pakai SN rusak (damaged) -> Gagal!
        try {
            $service->executeSwap([
                'old_onu_id' => $oldOnu->id,
                'old_serial_number' => 'ZTEGOLDGOOD01',
                'new_serial_number' => 'ZTEGDAMAGED01',
            ], $this->technician);
            $this->fail('Swap seharusnya gagal karena SN rusak.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('rusak', $e->getMessage());
        }

        // D. Coba Swap dengan enforce_stock = true saat teknisi tidak punya stok -> Gagal Inventory Shortage!
        try {
            $service->executeSwap([
                'old_onu_id' => $oldOnu->id,
                'old_serial_number' => 'ZTEGOLDGOOD01',
                'new_serial_number' => 'ZTEGNOSTOCK99',
                'enforce_stock' => true,
            ], $this->technician);
            $this->fail('Swap seharusnya gagal karena Inventory Shortage.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('Inventory Shortage', $e->getMessage());
        }

        // Pastikan oldOnu sama sekali belum berubah statusnya (tetap 'active') karena semua percobaan di atas gagal!
        $this->assertEquals(Onu::STATUS_ACTIVE, $oldOnu->fresh()->status);

        // E. Beri 1 stok valid ke teknisi, lalu lakukan Swap dengan old_device_condition = 'good'
        $this->giveTechnicianOntCustody('ZTEGVALIDNEW1');

        $swapOk = $service->executeSwap([
            'transaction_id' => 'TX-SWAP-GOOD-RET-1',
            'old_onu_id' => $oldOnu->id,
            'old_serial_number' => 'ZTEGOLDGOOD01',
            'new_serial_number' => 'ZTEGVALIDNEW1',
            'old_device_condition' => 'good',
            'queue_provisioning' => true,
        ], $this->technician);

        $this->assertTrue($swapOk['success']);
        $this->assertEquals(Onu::CONDITION_GOOD, $oldOnu->fresh()->condition);

        // Pastikan ONT lama ('ZTEGOLDGOOD01') yang kondisinya 'good' benar-benar masuk ke InventoryCustody teknisi (+1 siap pakai)!
        $returnedOldCustody = InventoryCustody::withoutGlobalScopes()
            ->where('holder_id', $this->technician->id)
            ->where('status', 'in_custody')
            ->whereHas('itemSerial', fn ($q) => $q->where('serial_number', 'ZTEGOLDGOOD01'))
            ->first();
        $this->assertNotNull($returnedOldCustody);
        $this->assertEquals(1.0, $returnedOldCustody->available_quantity);
    }

    /**
     * 8 & 9 & 10. Duplicate long transaction_id, GenieACS Timeout Queue Retry, & Ticket Proof Gate.
     */
    public function test_long_transaction_id_and_genieacs_retry_job_execution(): void
    {
        Queue::fake();

        $oldOnu = Onu::withoutGlobalScopes()->create([
            'serial_number' => 'ZTEGLONG0001',
            'name' => 'ONU Long Tx',
            'status' => Onu::STATUS_ACTIVE,
        ]);

        $this->giveTechnicianOntCustody('ZTEGLONG0002');

        // Transaction ID sepanjang 98 karakter (menguji pemotongan aman sebelum ditambah '-OUT')
        $longTxId = 'TX-' . str_repeat('A', 95);

        Sanctum::actingAs($this->technician);

        $res1 = $this->postJson('/api/mobile/network/devices/swap-ont', [
            'transaction_id' => $longTxId,
            'old_serial_number' => 'ZTEGLONG0001',
            'new_serial_number' => 'ZTEGLONG0002',
            'old_device_condition' => 'damaged',
        ]);
        $res1->assertStatus(200)->assertJsonPath('data.idempotent_replay', false);

        // Retry dengan longTxId yang sama -> Idempotent replay = true!
        $res2 = $this->postJson('/api/mobile/network/devices/swap-ont', [
            'transaction_id' => $longTxId,
            'old_serial_number' => 'ZTEGLONG0001',
            'new_serial_number' => 'ZTEGLONG0002',
            'old_device_condition' => 'damaged',
        ]);
        $res2->assertStatus(200)->assertJsonPath('data.idempotent_replay', true);

        // Pastikan ProvisionOntLifecycleJob dijadwalkan otomatis ketika NBI offline/gagal saat sinkron
        Queue::assertPushed(ProvisionOntLifecycleJob::class);

        // Uji eksekusi ProvisionOntLifecycleJob secara langsung
        $newOnu = Onu::withoutGlobalScopes()->where('serial_number', 'ZTEGLONG0002')->first();
        $this->assertNotNull($newOnu);

        $mockGenie = \Mockery::mock(GenieAcsProvisioningService::class);
        $mockGenie->shouldReceive('provisionOnt')
            ->once()
            ->andReturn(['status' => 'success']);

        $job = new ProvisionOntLifecycleJob($newOnu->id, ['wifi_ssid' => 'TEST']);
        $job->handle($mockGenie);

        $this->assertEquals('success', $newOnu->fresh()->provision_status);
        $this->assertEquals(Onu::OP_ONLINE, $newOnu->fresh()->operational_status);

        // Jika dijalankan ulang (retry job), karena provision_status sudah 'success', tidak memanggil GenieACS lagi
        $job->handle($mockGenie);
    }

    /**
     * 13. Test Migration Rollback & Re-Migration (tidak menghapus onu_histories yang sudah berisi data operasional).
     */
    public function test_migration_rollback_preserves_operational_history_and_remigrates_cleanly(): void
    {
        $onu = Onu::withoutGlobalScopes()->create([
            'serial_number' => 'ZTEGMIGR0001',
            'name' => 'ONU Migration Test',
            'status' => 'dismantled_damaged', // Legacy status
        ]);

        OnuHistory::withoutGlobalScopes()->create([
            'transaction_id' => 'TX-MIGR-PRESERVE-1',
            'onu_id' => $onu->id,
            'serial_number' => 'ZTEGMIGR0001',
            'activity' => 'dismantle',
            'new_status' => 'dismantled',
            'condition' => 'damaged',
            'description' => 'Histori penting tidak boleh hilang saat rollback',
        ]);

        $migration = include base_path('database/migrations/2026_11_17_001000_create_enterprise_ont_lifecycle_tables.php');

        // Jalankan down() -> tabel onu_histories TIDAK boleh terhapus karena berisi data operasional!
        $migration->down();
        $this->assertTrue(Schema::hasTable('onu_histories'));
        $this->assertEquals(1, DB::table('onu_histories')->count());

        // Jalankan up() kembali -> kolom-kolom kembali ada & data legacy 'dismantled_damaged' ternormalisasi!
        $migration->up();
        $this->assertTrue(Schema::hasColumn('onus', 'condition'));
        $this->assertTrue(Schema::hasColumn('onus', 'audit_status'));
        $this->assertTrue(Schema::hasColumn('onus', 'operational_status'));
        $this->assertEquals('dismantled', $onu->fresh()->status);
        $this->assertEquals('damaged', $onu->fresh()->condition);
    }
}
