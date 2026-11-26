<?php

namespace Tests\Feature;

use App\Models\CRM\Customer;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\Router;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\ServiceProfileType;
use App\Models\Role;
use App\Models\User;
use App\Services\CRM\CustomerCodeGenerator;
use App\Services\ISP\PPPoEService;
use App\Services\Provisioning\ProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CustomerCodeCollisionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan role yang diperlukan
        Role::firstOrCreate(['name' => 'customer'], ['display_name' => 'Customer']);
        Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator']);
    }

    protected function tearDown(): void
    {
        \App\Models\User::flushEventListeners();
        try {
            Mockery::close();
        } finally {
            parent::tearDown();
        }
    }

    public function test_customer_code_generator_generates_sequential_codes(): void
    {
        $prefix = date('Ymd');
        $code1 = CustomerCodeGenerator::generate();
        $this->assertEquals($prefix . '01', $code1);

        // Buat customer dengan code1
        Customer::create([
            'code' => $code1,
            'name' => 'Customer Satu',
            'phone' => '081234567890',
            'status' => 'active',
        ]);

        $code2 = CustomerCodeGenerator::generate();
        $this->assertEquals($prefix . '02', $code2);
    }

    public function test_customer_code_generator_detects_existing_in_users_and_customers(): void
    {
        $prefix = date('Ymd');
        $codeInUser = $prefix . '01';

        // Buat user dengan customer_code
        User::create([
            'name' => 'User Eksisting',
            'whatsapp' => '081111111111',
            'customer_code' => $codeInUser,
            'username' => $codeInUser,
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->assertTrue(CustomerCodeGenerator::codeExists($codeInUser));

        // Code generator harus otomatis lompat ke 02
        $nextCode = CustomerCodeGenerator::generate();
        $this->assertEquals($prefix . '02', $nextCode);
    }

    public function test_customer_code_generator_skips_occupied_sequences(): void
    {
        $prefix = date('Ymd');

        // Buat sequence 01, 02, dan 03 di users dan customers
        User::create([
            'name' => 'User 1',
            'whatsapp' => '081200000001',
            'customer_code' => $prefix . '01',
            'username' => $prefix . '01',
            'password' => bcrypt('password'),
        ]);

        Customer::create([
            'code' => $prefix . '02',
            'name' => 'Cust 2',
            'phone' => '081200000002',
            'status' => 'active',
        ]);

        User::create([
            'name' => 'User 3',
            'whatsapp' => '081200000003',
            'customer_code' => $prefix . '03',
            'username' => $prefix . '03',
            'password' => bcrypt('password'),
        ]);

        $nextCode = CustomerCodeGenerator::generate();
        $this->assertEquals($prefix . '04', $nextCode);

        // Pastikan ensureUnique dengan kode yang sudah ada menghasilkan sequence baru
        $ensured = CustomerCodeGenerator::ensureUnique($prefix . '03');
        $this->assertEquals($prefix . '04', $ensured);
    }

    public function test_find_or_create_customer_self_heals_on_duplicate_user_code_collision(): void
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'customer_code' => 'ADM-001',
            'username' => 'adm001',
            'password' => bcrypt('secret'),
        ]);

        $prefix = date('Ymd');
        $conflictingCode = $prefix . '01';

        // Pre-create user dengan code 01 untuk mensimulasikan kode yang sudah diambil oleh proses lain
        User::create([
            'name' => 'Colliding User',
            'whatsapp' => '089999999999',
            'customer_code' => $conflictingCode,
            'username' => $conflictingCode,
            'password' => bcrypt('secret'),
        ]);

        $mockPppoe = Mockery::mock(PPPoEService::class);
        $mockUser = new PPPoEUser([
            'username' => 'Rasitaitot',
            'status' => 'active',
        ]);
        $mockUser->id = 1;

        $mockPppoe->shouldReceive('createPPPoEUser')
            ->once()
            ->andReturn($mockUser);
        $mockPppoe->shouldReceive('activatePPPoEUser')
            ->with(1, Mockery::any(), Mockery::any())
            ->zeroOrMoreTimes()
            ->andReturn($mockUser);
        $this->app->instance(PPPoEService::class, $mockPppoe);

        $router = Router::create([
            'code' => 'RTR-01',
            'name' => 'Router Test',
            'ip_address' => '10.0.0.1',
            'username' => 'admin',
            'password' => 'admin',
            'status' => 'active',
        ]);

        $profileType = ServiceProfileType::firstOrCreate(
            ['code' => 'PPPOE'],
            ['name' => 'PPPoE Profile', 'status' => 'active']
        );

        $profile = ServiceProfile::create([
            'service_profile_type_id' => $profileType->id,
            'code' => 'PKG-10M',
            'name' => 'Paket 10M',
            'service_type' => 'pppoe',
            'price' => 150000,
            'base_price' => 150000,
            'billing_cycle' => 'monthly',
            'is_active' => true,
        ]);

        $provisioningService = app(ProvisioningService::class);

        // Eksekusi aktivasi layanan PPPoE dengan meminta customer_code yang bertabrakan (01)
        $result = $provisioningService->activatePPPoEService([
            'name' => 'Resita',
            'customer_code' => $conflictingCode, // Memaksa tabrakan dengan 01
            'phone' => '082582252255',
            'email' => 'resita@gmail.com',
            'address' => 'Jl. Mawar No. 12',
            'username' => 'Rasitaitot',
            'password' => 'secret123',
            'service_profile_id' => $profile->id,
            'router_id' => $router->id,
            'status' => 'active',
        ], $admin->id);

        $this->assertNotNull($result['customer']);
        $this->assertNotNull($result['pppoe_user']);

        // Pastikan customer code yang dihasilkan TIDAK menabrak 01, melainkan 02
        $this->assertEquals($prefix . '02', $result['customer']->code);

        // Pastikan User portal login terbuat dengan customer_code 02
        $user = User::where('customer_code', $prefix . '02')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Resita', $user->name);
        $this->assertEquals($prefix . '02', $user->username);
    }

    public function test_simulated_db_query_exception_1062_triggers_automatic_retry_and_succeeds(): void
    {
        $admin = User::create([
            'name' => 'Admin Test 2',
            'email' => 'admin2@test.com',
            'customer_code' => 'ADM-002',
            'username' => 'adm002',
            'password' => bcrypt('secret'),
        ]);

        $mockPppoe = Mockery::mock(PPPoEService::class);
        $mockUser = new PPPoEUser([
            'username' => 'Joni123',
            'status' => 'active',
        ]);
        $mockUser->id = 2;

        $mockPppoe->shouldReceive('createPPPoEUser')
            ->once()
            ->andReturn($mockUser);
        $mockPppoe->shouldReceive('activatePPPoEUser')
            ->with(2, Mockery::any(), Mockery::any())
            ->zeroOrMoreTimes()
            ->andReturn($mockUser);
        $this->app->instance(PPPoEService::class, $mockPppoe);

        $router = Router::create([
            'code' => 'RTR-02',
            'name' => 'Router Test 2',
            'ip_address' => '10.0.0.2',
            'username' => 'admin',
            'password' => 'admin',
            'status' => 'active',
        ]);

        $profileType = ServiceProfileType::firstOrCreate(
            ['code' => 'PPPOE'],
            ['name' => 'PPPoE Profile', 'status' => 'active']
        );

        $profile = ServiceProfile::create([
            'service_profile_type_id' => $profileType->id,
            'code' => 'PKG-20M',
            'name' => 'Paket 20M',
            'service_type' => 'pppoe',
            'price' => 200000,
            'base_price' => 200000,
            'billing_cycle' => 'monthly',
            'is_active' => true,
        ]);

        // Simulasikan QueryException 1062 pada pembuatan user pertama kali
        $attemptsCount = 0;
        User::creating(function ($user) use (&$attemptsCount) {
            if ($attemptsCount === 0 && str_starts_with($user->name, 'Joni')) {
                $attemptsCount++;
                throw new \Illuminate\Database\QueryException(
                    'sqlite',
                    'insert into "users" ("customer_code") values (?)',
                    [$user->customer_code],
                    new \PDOException("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '{$user->customer_code}' for key 'users_customer_code_unique'", 1062)
                );
            }
        });

        $provisioningService = app(ProvisioningService::class);

        $result = $provisioningService->activatePPPoEService([
            'name' => 'Joni',
            'phone' => '089876543210',
            'email' => 'joni@gmail.com',
            'address' => 'Jl. Anggrek No. 5',
            'username' => 'Joni123',
            'password' => 'secret456',
            'service_profile_id' => $profile->id,
            'router_id' => $router->id,
            'status' => 'active',
        ], $admin->id);

        $this->assertNotNull($result['customer']);
        $this->assertEquals(1, $attemptsCount, 'Attempt count must show collision was triggered and retried');
        $this->assertNotEmpty($result['customer']->code);
    }
}
