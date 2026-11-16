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
use Illuminate\Support\Facades\Config;
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
            'port_count' => 16,
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

    protected function createActiveCustomerWithOnu(string $sn, string $code = null, ?int $odpId = null): array
    {
        $customer = Customer::withoutGlobalScopes()->create([
            'code' => $code ?? ('CUST-' . strtoupper(Str::random(6))),
            'name' => 'Pelanggan ' . $sn,
            'phone' => '0812' . rand(10000000, 99999999),
            'status' => 'active',
            'branch_id' => $this->branch->id,
        ]);

        $onu = Onu::withoutGlobalScopes()->create([
            'serial_number' => strtoupper(trim($sn)),
            'name' => 'ONU ' . $sn,
            'model' => 'ZTE F609',
            'olt_id' => $this->olt->id,
            'odp_id' => $odpId ?? $this->odp->id,
            'status' => Onu::STATUS_ACTIVE,
            'condition' => Onu::CONDITION_GOOD,
            'branch_id' => $this->branch->id,
        ]);

        $cs = CustomerService::withoutGlobalScopes()->create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'onu_id' => $onu->id,
            'username' => 'pppoe_' . strtolower($sn),
            'password' => 'secret123',
            'status' => 'active',
        ]);

        if ($odpId ?? $this->odp->id) {
            app(OntLifecycleService::class)->recalculateOdpUsedPorts($odpId ?? $this->odp->id);
        }

        return [$customer, $onu, $cs];
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
        $this->createActiveCustomerWithOnu('ZTEGACTIVE01', '2026111710');

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
     * 3. Concurrent Swap & Concurrent Dismantle (termasuk APK lama tanpa transaction_id).
     */
    public function test_concurrent_swap_and_concurrent_dismantle_prevent_double_mutation(): void
    {
        Queue::fake();

        [$customer, $oldOnu, $cs] = $this->createActiveCustomerWithOnu('ZTEGCONC0001', '2026111720');

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
     * 4. Duplicate transaction_id menggunakan dua koneksi database (dan pemulihan dari race condition QueryException 23000/1062).
     */
    public function test_duplicate_transaction_id_across_two_database_connections(): void
    {
        Queue::fake();

        $conn1 = DB::connection();
        $initialTxLevel = $conn1->transactionLevel();
        $defaultDriver = config('database.default');
        $defaultConfig = config("database.connections.{$defaultDriver}");
        $isRealMultiConn = ($defaultConfig['database'] ?? '') !== ':memory:';

        if ($isRealMultiConn) {
            while ($conn1->transactionLevel() > 0) {
                $conn1->commit();
            }
        }

        try {
            [$customer, $oldOnu, $cs] = $this->createActiveCustomerWithOnu('ZTEGDUAL0001', '2026111725');
            $this->giveTechnicianOntCustody('ZTEGDUAL0002');
            $this->giveTechnicianOntCustody('ZTEGDUAL0003');

            $service = app(OntLifecycleService::class);
            $sharedTxId = 'TX-DUAL-CONN-SWAP-001';

            // Koneksi 1 mengeksekusi Swap
            $resConn1 = $service->executeSwap([
                'transaction_id' => $sharedTxId,
                'old_onu_id' => $oldOnu->id,
                'old_serial_number' => 'ZTEGDUAL0001',
                'new_serial_number' => 'ZTEGDUAL0002',
                'old_device_condition' => 'damaged',
            ], $this->technician);

            $this->assertTrue($resConn1['success']);
            $this->assertEmpty($resConn1['idempotent_replay'] ?? false);

            if ($isRealMultiConn) {
                Config::set('database.connections.second_conn', $defaultConfig);
                DB::purge('second_conn');

                $this->assertNotSame(
                    $conn1->getPdo(),
                    DB::connection('second_conn')->getPdo()
                );

                $historyFromConn2 = DB::connection('second_conn')
                    ->table('onu_histories')
                    ->where('transaction_id', $sharedTxId)
                    ->first();
                $this->assertNotNull($historyFromConn2);

                // Uji Race Condition nyata antar 2 koneksi berbeda (pada isolasi READ COMMITTED di mana gap-lock tidak menahan insert paralel):
                // Koneksi 1 masuk ke dalam transaksi (belum ada baris), lalu Koneksi 2 menyisipkan & commit baris yang sama,
                // sehingga saat Koneksi 1 menyisipkan baris tersebut muncul QueryException 1062/23000 yang ditangkap oleh runIdempotentTransaction!
                if (in_array($defaultDriver, ['mysql', 'mariadb'], true)) {
                    $conn1->statement('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
                }

                $raceTxId = 'TX-RACE-UNIQUE-VIOLATION-001';
                $ref = new \ReflectionMethod(OntLifecycleService::class, 'runIdempotentTransaction');
                $ref->setAccessible(true);

                try {
                    $raceResponse = $ref->invoke($service, $raceTxId, function () use ($oldOnu, $raceTxId, $resConn1) {
                        // Koneksi ke-2 (independen) melakukan commit lebih dulu di tengah eksekusi Koneksi ke-1:
                        DB::connection('second_conn')->table('onu_histories')->insert([
                            'transaction_id' => $raceTxId,
                            'onu_id' => $oldOnu->id,
                            'serial_number' => $oldOnu->serial_number,
                            'activity' => 'swap_in',
                            'payload' => json_encode($resConn1),
                            'description' => 'Committed by parallel connection 2',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // Koneksi ke-1 mencoba insert transaction_id yang sama -> memicu QueryException UNIQUE constraint (1062)!
                        OnuHistory::withoutGlobalScopes()->create([
                            'transaction_id' => $raceTxId,
                            'onu_id' => $oldOnu->id,
                            'serial_number' => $oldOnu->serial_number,
                            'activity' => 'swap_in',
                            'payload' => $resConn1,
                            'description' => 'Duplicate insert by connection 1',
                        ]);

                        return $resConn1;
                    });
                } finally {
                    if (in_array($defaultDriver, ['mysql', 'mariadb'], true)) {
                        $conn1->statement('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
                    }
                }

                $this->assertTrue($raceResponse['idempotent_replay'] ?? false);
                $this->assertEquals('ZTEGDUAL0002', $raceResponse['new_serial_number']);
            }

            $resConn2 = $service->executeSwap([
                'transaction_id' => $sharedTxId,
                'old_onu_id' => $oldOnu->id,
                'old_serial_number' => 'ZTEGDUAL0001',
                'new_serial_number' => 'ZTEGDUAL0003',
                'old_device_condition' => 'damaged',
            ], $this->technician);

            $this->assertTrue($resConn2['idempotent_replay'] ?? false);
            $this->assertEquals('ZTEGDUAL0002', $resConn2['new_serial_number']);

            // Pastikan ZTEGDUAL0003 sama sekali tidak terpakai
            $serial3 = ItemSerial::withoutGlobalScopes()->where('serial_number', 'ZTEGDUAL0003')->first();
            $this->assertEquals('in_custody_technician', $serial3->status);
        } finally {
            if ($isRealMultiConn) {
                $this->cleanupCommittedMultiConnectionData();
                while ($conn1->transactionLevel() < $initialTxLevel) {
                    $conn1->beginTransaction();
                }
            }
        }
    }

    protected function cleanupCommittedMultiConnectionData(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach ([
            'onu_histories',
            'inventory_usage_items',
            'inventory_usages',
            'inventory_custodies',
            'item_serials',
            'items',
            'support_maintenance_usages',
            'customer_services',
            'contracts',
            'services',
            'service_catalogs',
            'onus',
            'odps',
            'olts',
            'customers',
            'attendances',
            'role_user',
            'users',
            'roles',
            'branches',
        ] as $tbl) {
            $tbl = trim($tbl);
            if (Schema::hasTable($tbl)) {
                DB::table($tbl)->delete();
            }
        }
        Schema::enableForeignKeyConstraints();
    }

    /**
     * 5. Inventory Shortage TANPA parameter enforce_stock, Serial milik teknisi lain, & Good vs Damaged custody.
     */
    public function test_inventory_shortage_without_enforce_stock_and_foreign_serial_ownership(): void
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

        [$customer, $oldOnu, $cs] = $this->createActiveCustomerWithOnu('ZTEGOLDGOOD01', '2026111730');

        // A. Coba Swap TANPA mengirim parameter enforce_stock (dan teknisi belum punya stok) -> WAJIB GAGAL Inventory Shortage!
        try {
            $service->executeSwap([
                'old_onu_id' => $oldOnu->id,
                'old_serial_number' => 'ZTEGOLDGOOD01',
                'new_serial_number' => 'ZTEGNOSTOCK99',
                // enforce_stock sengaja TIDAK dikirim!
            ], $this->technician);
            $this->fail('Swap seharusnya gagal karena Inventory Shortage meskipun enforce_stock tidak dikirim.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('Inventory Shortage', $e->getMessage());
        }

        // B. Coba Swap pakai SN yang masih di gudang -> Gagal!
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

        // C. Coba Swap pakai SN milik teknisi lain -> Gagal!
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

        // D. Coba Swap pakai SN rusak (damaged) -> Gagal!
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

        // Pastikan oldOnu sama sekali belum berubah statusnya (tetap 'active') karena semua percobaan di atas gagal secara atomik!
        $this->assertEquals(Onu::STATUS_ACTIVE, $oldOnu->fresh()->status);

        // E. Beri 1 stok valid ke teknisi, lalu lakukan Swap dengan old_device_condition = 'good'
        $this->giveTechnicianOntCustody('ZTEGVALIDNEW1');

        $swapOk = $service->executeSwap([
            'transaction_id' => 'TX-SWAP-GOOD-RET-1',
            'old_onu_id' => $oldOnu->id,
            'old_serial_number' => 'ZTEGOLDGOOD01',
            'new_serial_number' => 'ZTEGVALIDNEW1',
            'old_device_condition' => 'good',
        ], $this->technician);

        $this->assertTrue($swapOk['success']);
        $this->assertEquals(Onu::CONDITION_GOOD, $oldOnu->fresh()->condition);

        // Pastikan ONT lama ('ZTEGOLDGOOD01') yang kondisinya 'good' kembali ke InventoryCustody teknisi (+1 siap pakai)!
        $returnedOldCustody = InventoryCustody::withoutGlobalScopes()
            ->where('holder_id', $this->technician->id)
            ->where('status', 'in_custody')
            ->whereHas('itemSerial', fn ($q) => $q->where('serial_number', 'ZTEGOLDGOOD01'))
            ->first();
        $this->assertNotNull($returnedOldCustody);
        $this->assertEquals(1.0, $returnedOldCustody->available_quantity);
    }

    /**
     * 6. Prioritas 3: Tolak Swap dari ONT yang berstatus dismantled, spare, replaced, atau tanpa CustomerService valid.
     */
    public function test_swap_rejects_dismantled_spare_replaced_or_unlinked_ont(): void
    {
        Queue::fake();
        $service = app(OntLifecycleService::class);

        $this->giveTechnicianOntCustody('ZTEGSWAPNEW01');

        foreach (['dismantled', 'spare', 'replaced', 'damaged', 'inactive'] as $invalidStatus) {
            [$cust, $invalidOnu, $cs] = $this->createActiveCustomerWithOnu('ZTEGINV_' . strtoupper($invalidStatus));
            $invalidOnu->update(['status' => $invalidStatus]);

            try {
                $service->executeSwap([
                    'old_onu_id' => $invalidOnu->id,
                    'old_serial_number' => $invalidOnu->serial_number,
                    'new_serial_number' => 'ZTEGSWAPNEW01',
                ], $this->technician);
                $this->fail("Swap seharusnya ditolak untuk ONT lama berstatus '{$invalidStatus}'.");
            } catch (\InvalidArgumentException $e) {
                $this->assertStringContainsString($invalidStatus, $e->getMessage());
            }
        }

        // Uji ONT active tetapi TIDAK memiliki relasi CustomerService -> Wajib ditolak!
        $orphanOnu = Onu::withoutGlobalScopes()->create([
            'serial_number' => 'ZTEGORPHAN001',
            'name' => 'ONT Tanpa Layanan',
            'status' => Onu::STATUS_ACTIVE,
            'branch_id' => $this->branch->id,
        ]);

        try {
            $service->executeSwap([
                'old_onu_id' => $orphanOnu->id,
                'old_serial_number' => 'ZTEGORPHAN001',
                'new_serial_number' => 'ZTEGSWAPNEW01',
            ], $this->technician);
            $this->fail('Swap seharusnya ditolak jika ONT lama tidak terikat pada CustomerService aktif.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('CustomerService', $e->getMessage());
        }

        // Pastikan stok teknisi ZTEGSWAPNEW01 tetap utuh (belum terpotong)
        $custody = InventoryCustody::withoutGlobalScopes()
            ->whereHas('itemSerial', fn ($q) => $q->where('serial_number', 'ZTEGSWAPNEW01'))
            ->first();
        $this->assertEquals(1.0, $custody->available_quantity);
    }

    /**
     * 7. Prioritas 1: GenieACS dijalankan SETELAH transaksi DB commit, timeout tidak membatalkan inventory, dan retry aman.
     */
    public function test_genieacs_provisioning_runs_after_commit_with_timeout_and_retry_safety(): void
    {
        [$customer, $oldOnu, $cs] = $this->createActiveCustomerWithOnu('ZTEGACS0001', '2026111740');
        $this->giveTechnicianOntCustody('ZTEGACS0002');

        $txLevelWhenProvisionCalled = null;
        $initialTransactionLevel = DB::transactionLevel();

        // Simulasikan GenieACS timeout pada pemanggilan pertama (saat afterCommit), lalu sukses pada retry!
        $mockGenie = \Mockery::mock(GenieAcsProvisioningService::class);
        $mockGenie->shouldReceive('provisionOnt')
            ->once()
            ->andReturnUsing(function () use (&$txLevelWhenProvisionCalled) {
                $txLevelWhenProvisionCalled = DB::transactionLevel();
                throw new \RuntimeException('Connection timed out to GenieACS NBI port 7557');
            });

        $this->app->instance(GenieAcsProvisioningService::class, $mockGenie);

        $service = app(OntLifecycleService::class);
        $result = $service->executeSwap([
            'transaction_id' => 'TX-ACS-AFTER-COMMIT-1',
            'old_onu_id' => $oldOnu->id,
            'old_serial_number' => 'ZTEGACS0001',
            'new_serial_number' => 'ZTEGACS0002',
            'old_device_condition' => 'damaged',
        ], $this->technician);

        // 1. Bukti bahwa provisionOnt() dipanggil di luar DB::transaction() milik executeSwap()!
        $this->assertEquals($initialTransactionLevel, $txLevelWhenProvisionCalled);

        // 2. Meskipun GenieACS timeout, transaksi inventory & ONT swap TETAP SAH dan tersimpan!
        $this->assertTrue($result['success']);
        $this->assertEquals(Onu::STATUS_REPLACED, $oldOnu->fresh()->status);

        $newOnu = Onu::withoutGlobalScopes()->where('serial_number', 'ZTEGACS0002')->first();
        $this->assertNotNull($newOnu);
        $this->assertEquals(Onu::STATUS_ACTIVE, $newOnu->status);
        $this->assertEquals('failed', $newOnu->provision_status);

        $newSerial = ItemSerial::withoutGlobalScopes()->where('serial_number', 'ZTEGACS0002')->first();
        $this->assertEquals('installed', $newSerial->status);

        // 3. Sekarang jalankan retry ProvisionOntLifecycleJob -> Sukses!
        $mockGenieRetry = \Mockery::mock(GenieAcsProvisioningService::class);
        $mockGenieRetry->shouldReceive('provisionOnt')
            ->once()
            ->andReturn(['status' => 'success']);

        $job = new ProvisionOntLifecycleJob($newOnu->id, ['wifi_ssid' => 'WIFI_OK']);
        $job->handle($mockGenieRetry);

        $this->assertEquals('success', $newOnu->fresh()->provision_status);
        $this->assertEquals(Onu::OP_ONLINE, $newOnu->fresh()->operational_status);

        // 4. Jalankan job sekali lagi -> Karena sudah 'success', TIDAK boleh memanggil provisionOnt lagi!
        $job->handle($mockGenieRetry);
    }

    /**
     * 8. Kegagalan database sebelum commit TIDAK boleh men-dispatch atau memanggil GenieACS (DB::afterCommit protection).
     */
    public function test_database_failure_before_commit_never_dispatches_genieacs_provisioning(): void
    {
        Queue::fake();

        [$customer, $oldOnu, $cs] = $this->createActiveCustomerWithOnu('ZTEGDBFAIL01', '2026111750');
        $this->giveTechnicianOntCustody('ZTEGDBFAIL02');

        // Simulasikan kegagalan database saat insert OnuHistory kedua (swap_in) di akhir transaksi
        OnuHistory::creating(function (OnuHistory $history) {
            if ($history->activity === 'swap_in' && $history->serial_number === 'ZTEGDBFAIL02') {
                throw new \RuntimeException('Simulated DB failure before commit');
            }
        });

        try {
            $service = app(OntLifecycleService::class);
            $service->executeSwap([
                'transaction_id' => 'TX-DB-FAIL-ROLLBACK-1',
                'old_onu_id' => $oldOnu->id,
                'old_serial_number' => 'ZTEGDBFAIL01',
                'new_serial_number' => 'ZTEGDBFAIL02',
                'old_device_condition' => 'damaged',
            ], $this->technician);
            $this->fail('Transaksi seharusnya gagal karena Simulated DB failure.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Simulated DB failure', $e->getMessage());
        } finally {
            OnuHistory::flushEventListeners();
        }

        // Pastikan seluruh mutasi stok dan ONT ter-rollback utuh!
        $this->assertEquals(Onu::STATUS_ACTIVE, $oldOnu->fresh()->status);
        $this->assertNull(Onu::withoutGlobalScopes()->where('serial_number', 'ZTEGDBFAIL02')->first());

        $custody = InventoryCustody::withoutGlobalScopes()
            ->whereHas('itemSerial', fn ($q) => $q->where('serial_number', 'ZTEGDBFAIL02'))
            ->first();
        $this->assertEquals(1.0, $custody->available_quantity);

        // Pastikan ProvisionOntLifecycleJob TIDAK pernah di-dispatch karena transaksi gagal commit!
        Queue::assertNothingPushed();
    }

    /**
     * 9. Prioritas 4: Dua operasi bersamaan pada ODP yang sama menjaga used_port_count sama persis dengan jumlah ONU active.
     */
    public function test_two_concurrent_operations_on_same_odp_maintain_accurate_used_port_count(): void
    {
        Queue::fake();

        $odpShared = Odp::withoutGlobalScopes()->create([
            'name' => 'ODP-SHARED-01',
            'code' => 'ODP-SH-01',
            'olt_id' => $this->olt->id,
            'branch_id' => $this->branch->id,
            'port_count' => 16,
            'used_port_count' => 0,
            'status' => 'active',
        ]);

        $c1 = Customer::withoutGlobalScopes()->create([
            'code' => '2026111761',
            'name' => 'Pelanggan ODP 1',
            'phone' => '081200001111',
            'status' => 'active',
            'branch_id' => $this->branch->id,
        ]);
        $c2 = Customer::withoutGlobalScopes()->create([
            'code' => '2026111762',
            'name' => 'Pelanggan ODP 2',
            'phone' => '081200002222',
            'status' => 'active',
            'branch_id' => $this->branch->id,
        ]);

        $service = app(OntLifecycleService::class);

        // Operasi 1 & Operasi 2 pada ODP yang sama
        $service->recordSensus([
            'transaction_id' => 'TX-ODP-CONC-1',
            'onu_sn' => 'ZTEGODP0001',
            'olt_id' => $this->olt->id,
            'pon_port' => 1,
            'odp_id' => $odpShared->id,
            'odp_port' => 1,
            'customer_id' => $c1->id,
        ], $this->technician);

        $service->recordSensus([
            'transaction_id' => 'TX-ODP-CONC-2',
            'onu_sn' => 'ZTEGODP0002',
            'olt_id' => $this->olt->id,
            'pon_port' => 1,
            'odp_id' => $odpShared->id,
            'odp_port' => 2,
            'customer_id' => $c2->id,
        ], $this->technician);

        $activeOnuCount = Onu::withoutGlobalScopes()
            ->where('odp_id', $odpShared->id)
            ->where('status', Onu::STATUS_ACTIVE)
            ->count();

        $this->assertEquals(2, $activeOnuCount);
        $this->assertEquals($activeOnuCount, $odpShared->fresh()->used_port_count);

        // Swap pada salah satu ONU di ODP tersebut -> jumlah port aktif tetap 2 (karena ONU lama replaced, ONU baru active)
        $this->giveTechnicianOntCustody('ZTEGODP0003');
        $service->executeSwap([
            'transaction_id' => 'TX-ODP-CONC-SWAP-1',
            'old_serial_number' => 'ZTEGODP0001',
            'new_serial_number' => 'ZTEGODP0003',
            'old_device_condition' => 'damaged',
        ], $this->technician);

        $activeAfterSwap = Onu::withoutGlobalScopes()
            ->where('odp_id', $odpShared->id)
            ->where('status', Onu::STATUS_ACTIVE)
            ->count();

        $this->assertEquals(2, $activeAfterSwap);
        $this->assertEquals($activeAfterSwap, $odpShared->fresh()->used_port_count);
    }

    /**
     * 10. Prioritas 5: Tiket & WorkOrder tanpa bukti dengan require_proof TIDAK dikirim (atau dipalsukan false) TIDAK BOLEH resolved/completed.
     */
    public function test_ticket_and_work_order_not_resolved_without_proof_even_when_require_proof_omitted_or_false(): void
    {
        Queue::fake();

        [$customer, $oldOnu, $cs] = $this->createActiveCustomerWithOnu('ZTEGTKT0001', '2026111770');
        $this->giveTechnicianOntCustody('ZTEGTKT0002');
        $this->giveTechnicianOntCustody('ZTEGTKT0003');

        $ticket = Ticket::withoutGlobalScopes()->create([
            'ticket_number' => 'TKT-PROOF-001',
            'customer_id' => $customer->id,
            'title' => 'Ganti Modem Rusak',
            'subject' => 'Ganti Modem Rusak',
            'description' => 'Modem pelanggan mati total tersambar petir',
            'category' => 'troubleshooting',
            'status' => 'assigned',
            'priority' => 'high',
            'assigned_to' => $this->technician->id,
            'branch_id' => $this->branch->id,
        ]);

        $workOrder = WorkOrder::withoutGlobalScopes()->create([
            'uuid' => (string) Str::uuid(),
            'ticket_id' => $ticket->id,
            'customer_id' => $customer->id,
            'assigned_to' => $this->technician->id,
            'type' => 'troubleshooting',
            'source_type' => 'ticket',
            'source_id' => $ticket->id,
            'title' => 'SPK Ganti Modem',
            'status' => 'in_progress',
            'branch_id' => $this->branch->id,
        ]);

        Sanctum::actingAs($this->technician);

        // A. Kirim request Swap TANPA bukti foto/tanda tangan dan TANPA require_proof (bahkan client mencoba mengirim require_proof = false!)
        $resNoProof = $this->postJson('/api/mobile/network/devices/swap-ont', [
            'transaction_id' => 'TX-TKT-NOPROOF-1',
            'old_serial_number' => 'ZTEGTKT0001',
            'new_serial_number' => 'ZTEGTKT0002',
            'old_device_condition' => 'damaged',
            'ticket_id' => $ticket->id,
            'work_order_id' => $workOrder->id,
            'require_proof' => false, // Mencoba bypass dari sisi client -> Harus diabaikan oleh server!
        ]);

        $resNoProof->assertStatus(200)
            ->assertJsonPath('data.ticket_resolved', false);

        // Tiket TIDAK BOLEH resolved dan WorkOrder TIDAK BOLEH completed!
        $this->assertEquals('in_progress', $ticket->fresh()->status);
        $this->assertNull($ticket->fresh()->resolved_at);
        $this->assertEquals('in_progress', $workOrder->fresh()->status);
        $this->assertNull($workOrder->fresh()->completed_at);

        // B. Sekarang lakukan Dismantle dengan menyertakan bukti foto -> Tiket resolved & WorkOrder completed!
        $resWithProof = $this->postJson('/api/mobile/network/devices/dismantle', [
            'transaction_id' => 'TX-TKT-WITHPROOF-1',
            'serial_number' => 'ZTEGTKT0002',
            'device_condition' => 'good',
            'ticket_id' => $ticket->id,
            'work_order_id' => $workOrder->id,
            'photo_path' => 'technician/dismantle/bukti_sah.jpg',
        ]);

        $resWithProof->assertStatus(200)
            ->assertJsonPath('data.ticket_resolved', true);

        $this->assertEquals('resolved', $ticket->fresh()->status);
        $this->assertNotNull($ticket->fresh()->resolved_at);
        $this->assertEquals('completed', $workOrder->fresh()->status);
        $this->assertNotNull($workOrder->fresh()->completed_at);
    }

    /**
     * 11. Bukti nyata pengujian Concurrency menggunakan 2 koneksi paralel (InnoDB Row-Level Locking FOR UPDATE,
     * Concurrent Swap, Concurrent Dismantle, dan Concurrent ODP Port Recalculation).
     */
    public function test_two_connections_concurrent_row_locking_swap_dismantle_and_odp_consistency(): void
    {
        Queue::fake();

        $defaultDriver = config('database.default');
        $defaultConfig = config("database.connections.{$defaultDriver}");

        if (($defaultConfig['database'] ?? '') === ':memory:') {
            $this->assertTrue(true);
            return;
        }

        $conn1 = DB::connection();
        $initialTxLevel = $conn1->transactionLevel();
        while ($conn1->transactionLevel() > 0) {
            $conn1->commit();
        }

        try {
            Config::set('database.connections.second_conn', $defaultConfig);
            DB::purge('second_conn');

            $conn2 = DB::connection('second_conn');

            $this->assertNotSame($conn1->getPdo(), $conn2->getPdo());

            if (in_array($defaultDriver, ['mysql', 'mariadb'], true)) {
                $version = $conn1->selectOne('SELECT VERSION() as ver')->ver ?? '';
                $this->assertNotEmpty($version);
                $conn2->statement('SET SESSION innodb_lock_wait_timeout = 1');
            }

            [$customer, $oldOnu, $cs] = $this->createActiveCustomerWithOnu('ZTEGMYSQL001', '2026111788', $this->odp->id);
            $this->giveTechnicianOntCustody('ZTEGMYSQL002');
            $this->giveTechnicianOntCustody('ZTEGMYSQL003');

            // A. Buktikan Row-Level Lock (FOR UPDATE) pada ONT & ODP menahan Koneksi ke-2 saat Koneksi ke-1 sedang bertransaksi
            if (in_array($defaultDriver, ['mysql', 'mariadb'], true)) {
                $conn1->beginTransaction();
                try {
                    // Koneksi 1 mengunci baris ODP dan ONT lama dengan FOR UPDATE
                    $lockedOdp = Odp::withoutGlobalScopes()->where('id', $this->odp->id)->lockForUpdate()->first();
                    $lockedOnu = Onu::withoutGlobalScopes()->where('id', $oldOnu->id)->lockForUpdate()->first();
                    $this->assertNotNull($lockedOdp);
                    $this->assertNotNull($lockedOnu);

                    // Koneksi 2 mencoba mengunci baris ODP yang sama secara paralel -> Harus tertahan oleh InnoDB row lock!
                    $lockBlocked = false;
                    try {
                        $conn2->transaction(function () use ($conn2) {
                            $conn2->table('odps')->where('id', $this->odp->id)->lockForUpdate()->first();
                        });
                    } catch (\Illuminate\Database\QueryException $qe) {
                        $lockBlocked = str_contains($qe->getMessage(), 'Lock wait timeout') || (int) $qe->getCode() === 1205 || str_contains($qe->getMessage(), '1205');
                    }
                    $this->assertTrue($lockBlocked, 'Koneksi 2 wajib tertahan oleh InnoDB FOR UPDATE lock pada baris ODP.');
                } finally {
                    $conn1->rollBack();
                }
            }

            // B. Jalankan Swap pada Koneksi 1, lalu percobaan Swap paralel pada ONT lama yang sama -> Ditolak & stok ke-2 aman
            $service = app(OntLifecycleService::class);
            $swap1 = $service->executeSwap([
                'old_onu_id' => $oldOnu->id,
                'old_serial_number' => 'ZTEGMYSQL001',
                'new_serial_number' => 'ZTEGMYSQL002',
                'old_device_condition' => 'damaged',
            ], $this->technician);
            $this->assertTrue($swap1['success']);

            try {
                $service->executeSwap([
                    'old_onu_id' => $oldOnu->id,
                    'old_serial_number' => 'ZTEGMYSQL001',
                    'new_serial_number' => 'ZTEGMYSQL003',
                    'old_device_condition' => 'damaged',
                ], $this->technician);
                $this->fail('Concurrent Swap kedua pada ONT yang sudah replaced harus ditolak.');
            } catch (\InvalidArgumentException $e) {
                $this->assertStringContainsString('replaced', $e->getMessage());
            }

            // Pastikan ZTEGMYSQL003 tetap utuh di custody teknisi
            $serial3 = ItemSerial::withoutGlobalScopes()->where('serial_number', 'ZTEGMYSQL003')->first();
            $this->assertEquals('in_custody_technician', $serial3->status);

            // C. Pastikan used_port_count pada ODP akurat sesuai jumlah ONU berstatus active (dilihat dari Koneksi 2)
            $expectedActiveOnus = $conn2->table('onus')
                ->where('odp_id', $this->odp->id)
                ->where('status', Onu::STATUS_ACTIVE)
                ->count();
            $actualUsedPorts = (int) $conn2->table('odps')->where('id', $this->odp->id)->value('used_port_count');
            $this->assertEquals($expectedActiveOnus, $actualUsedPorts);
        } finally {
            $this->cleanupCommittedMultiConnectionData();
            while ($conn1->transactionLevel() < $initialTxLevel) {
                $conn1->beginTransaction();
            }
        }
    }

    /**
     * 12. Test Migration Rollback & Re-Migration (tidak menghapus onu_histories yang sudah berisi data operasional).
     */
    public function test_migration_rollback_preserves_operational_history_and_remigrates_cleanly(): void
    {
        $conn = DB::connection();
        $initialTxLevel = $conn->transactionLevel();

        $onu = Onu::withoutGlobalScopes()->create([
            'serial_number' => 'ZTEGMIGR0001',
            'name' => 'ONU Migration Test',
            'status' => 'dismantled_damaged',
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

        while ($conn->transactionLevel() < $initialTxLevel) {
            $conn->beginTransaction();
        }
    }
}
