<?php

namespace Tests\Feature;

use App\Imports\HotspotUserImport;
use App\Imports\PPPoEUserImport;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\ServiceProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PPPoEAndHotspotImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected ServiceProfile $pppoeProfile;
    protected ServiceProfile $hotspotProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin_import@test.com',
            'is_active' => true,
        ]);

        $profileType = \App\Models\ISP\ServiceProfileType::firstOrCreate(
            ['name' => 'Default'],
            ['code' => 'DEF', 'description' => 'Default Type']
        );

        $this->pppoeProfile = ServiceProfile::create([
            'service_profile_type_id' => $profileType->id,
            'code' => 'PKG-HOME-20M',
            'name' => 'Paket Home 20 Mbps',
            'service_type' => 'pppoe',
            'base_price' => 200000,
            'is_active' => true,
        ]);

        $this->hotspotProfile = ServiceProfile::create([
            'service_profile_type_id' => $profileType->id,
            'code' => 'PKG-HS-GOLD',
            'name' => 'Member Gold 2 User',
            'service_type' => 'hotspot',
            'base_price' => 50000,
            'is_active' => true,
        ]);
    }

    public function test_pppoe_import_standard_and_mikrotik_format(): void
    {
        $importer = new PPPoEUserImport($this->adminUser);

        // Simulasi baris Excel dengan format standar dan format ekspor MikroTik (name, comment, profile)
        $rows = new Collection([
            // Baris 1: Format standar template
            [
                'username' => 'budi_net01',
                'password' => 'pass1234',
                'nama_pelanggan' => 'Budi Santoso',
                'phone' => '081234567890',
                'paket_langganan' => 'Paket Home 20 Mbps',
            ],
            // Baris 2: Format ekspor MikroTik (name, comment, profile) & tanpa nomor HP
            [
                'name' => 'mikrotik_user02',
                'password' => 'secret99',
                'comment' => 'Joko Anwar',
                'profile' => 'Paket Home 20 Mbps',
                'phone' => null,
            ],
            // Baris 3: Nama paket tanpa spasi ("20Mbps" vs "Paket Home 20 Mbps") & nama pelanggan kosong
            [
                'login' => 'siti_wifi03',
                'password' => 'siti2026',
                'plan' => '20Mbps',
                'whatsapp' => '',
            ],
        ]);

        $importer->collection($rows);

        $this->assertDatabaseHas('pppoe_users', ['username' => 'budi_net01']);
        $this->assertDatabaseHas('pppoe_users', ['username' => 'mikrotik_user02']);
        $this->assertDatabaseHas('pppoe_users', ['username' => 'siti_wifi03']);
        $this->assertDatabaseHas('customers', ['name' => 'Budi Santoso']);
        $this->assertDatabaseHas('customers', ['name' => 'Joko Anwar']);
    }

    public function test_pppoe_reimport_updates_existing_users(): void
    {
        $importer = new PPPoEUserImport($this->adminUser);

        // Impor pertama
        $importer->collection(new Collection([
            [
                'username' => 'user_repeat',
                'password' => 'oldpass',
                'nama_pelanggan' => 'Pelanggan Repeat',
                'paket_langganan' => 'Paket Home 20 Mbps',
            ],
        ]));

        $this->assertDatabaseHas('pppoe_users', [
            'username' => 'user_repeat',
            'password' => 'oldpass',
        ]);

        // Impor kedua dengan update password & profile
        $importer->collection(new Collection([
            [
                'username' => 'user_repeat',
                'password' => 'newpass_updated',
                'nama_pelanggan' => 'Pelanggan Repeat',
                'paket_langganan' => 'Paket Home 20 Mbps',
            ],
        ]));

        $this->assertDatabaseHas('pppoe_users', [
            'username' => 'user_repeat',
            'password' => 'newpass_updated',
        ]);
    }

    public function test_hotspot_import_standard_and_freeradius_format(): void
    {
        $importer = new HotspotUserImport($this->adminUser);

        // Simulasi baris Excel Hotspot dengan format standar dan FreeRADIUS (login, groupname, value)
        $rows = new Collection([
            // Baris 1: Format standar template
            [
                'username' => 'rapi555777',
                'password' => 'rapi1234',
                'service_profile' => 'Member Gold 2 User',
                'customer' => 'Bilal',
                'phone' => '081234567890',
            ],
            // Baris 2: Format FreeRADIUS / MSRadius (login, groupname, value) & nomor HP kosong
            [
                'login' => 'hs_warkop01',
                'value' => 'warkop88',
                'groupname' => 'Member Gold 2 User',
                'customer' => 'Pak Slamet',
                'phone' => '',
            ],
            // Baris 3: Profil tanpa spasi & nama pelanggan kosong (auto-generate dari username)
            [
                'user' => 'hs_kafe02',
                'pass' => 'kafe123',
                'profile' => 'Gold 2 User',
            ],
        ]);

        $importer->collection($rows);

        $this->assertDatabaseHas('hotspot_users', ['username' => 'rapi555777']);
        $this->assertDatabaseHas('hotspot_users', ['username' => 'hs_warkop01']);
        $this->assertDatabaseHas('hotspot_users', ['username' => 'hs_kafe02']);
    }

    public function test_hotspot_reimport_updates_existing_users(): void
    {
        $importer = new HotspotUserImport($this->adminUser);

        // Impor pertama
        $importer->collection(new Collection([
            [
                'username' => 'hs_repeat',
                'password' => 'old_hs_pass',
                'service_profile' => 'Member Gold 2 User',
            ],
        ]));

        $this->assertDatabaseHas('hotspot_users', [
            'username' => 'hs_repeat',
            'password' => 'old_hs_pass',
        ]);

        // Impor kedua untuk update password
        $importer->collection(new Collection([
            [
                'username' => 'hs_repeat',
                'password' => 'new_hs_pass_updated',
                'service_profile' => 'Member Gold 2 User',
            ],
        ]));

        $this->assertDatabaseHas('hotspot_users', [
            'username' => 'hs_repeat',
            'password' => 'new_hs_pass_updated',
        ]);
    }
}
