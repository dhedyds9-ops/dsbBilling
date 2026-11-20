<?php

namespace Tests\Feature;

use App\Livewire\Isp\VoucherRequest\Index as AdminVoucherRequestIndex;
use App\Livewire\ResellerPortal\VoucherRequest\Index as ResellerVoucherRequestIndex;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\ServiceProfileType;
use App\Models\ISP\Voucher;
use App\Models\ISP\VoucherRequest;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use App\Services\ISP\VoucherRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class VoucherRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $reseller;
    protected User $otherReseller;
    protected Branch $branch;
    protected ServiceProfile $hotspotProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator', 'guard_name' => 'web']);
        $resellerRole = Role::firstOrCreate(['name' => 'reseller'], ['display_name' => 'Reseller', 'guard_name' => 'web']);

        $this->branch = Branch::create([
            'name' => 'Cabang Utama',
            'code' => 'BR-01',
            'status' => 'active',
        ]);

        $this->admin = User::factory()->create([
            'email' => 'admin_voucher_' . uniqid() . '@example.com',
            'job_function' => 'ADMINISTRATOR',
            'is_active' => true,
            'branch_id' => $this->branch->id,
        ]);
        $this->admin->roles()->attach($adminRole->id);

        $this->reseller = User::factory()->create([
            'email' => 'reseller_voucher_' . uniqid() . '@example.com',
            'job_function' => 'RESELLER',
            'is_active' => true,
            'branch_id' => $this->branch->id,
        ]);
        $this->reseller->roles()->attach($resellerRole->id);

        $this->otherReseller = User::factory()->create([
            'email' => 'other_reseller_' . uniqid() . '@example.com',
            'job_function' => 'RESELLER',
            'is_active' => true,
            'branch_id' => $this->branch->id,
        ]);
        $this->otherReseller->roles()->attach($resellerRole->id);

        $type = ServiceProfileType::firstOrCreate(['code' => 'HOTSPOT'], ['name' => 'Hotspot', 'status' => 'active']);

        $this->hotspotProfile = ServiceProfile::create([
            'service_profile_type_id' => $type->id,
            'name' => 'Paket Hotspot 5 Jam',
            'code' => 'HS-5H',
            'service_type' => 'Hotspot',
            'package_type' => 'time_based',
            'duration_value' => 5,
            'duration_unit' => 'hours',
            'base_price' => 10000,
            'promo_price' => 10000,
            'cost_price' => 7000,
            'reseller_price' => 7000,
            'status' => 'active',
            'branch_id' => $this->branch->id,
        ]);
    }

    public function test_reseller_can_create_voucher_request_via_service(): void
    {
        $service = app(VoucherRequestService::class);

        $request = $service->createRequest([
            'service_profile_id' => $this->hotspotProfile->id,
            'quantity' => 100,
            'delivery_type' => VoucherRequest::DELIVERY_PHYSICAL_PRINT,
            'payment_method' => VoucherRequest::PAYMENT_DEPOSIT,
            'notes' => 'Tolong dicetak cepat untuk acara festival',
        ], $this->reseller);

        $this->assertNotNull($request->id);
        $this->assertStringStartsWith('VR-', $request->request_no);
        $this->assertEquals($this->reseller->id, $request->reseller_id);
        $this->assertEquals(100, $request->quantity);
        $this->assertEquals(10000, (float) $request->unit_price);
        $this->assertEquals(7000, (float) $request->cost_price);
        $this->assertEquals(700000, (float) $request->total_amount);
        $this->assertEquals(300000, (float) $request->estimated_profit);
        $this->assertEquals(VoucherRequest::STATUS_PENDING, $request->status);
        $this->assertEquals(VoucherRequest::PAYMENT_STATUS_UNPAID, $request->payment_status);
        $this->assertEquals('Cetak Fisik', $request->delivery_type_label);
    }

    public function test_admin_can_approve_and_generate_vouchers(): void
    {
        $service = app(VoucherRequestService::class);

        $req = $service->createRequest([
            'service_profile_id' => $this->hotspotProfile->id,
            'quantity' => 10,
            'delivery_type' => VoucherRequest::DELIVERY_PHYSICAL_PRINT,
        ], $this->reseller);

        $updated = $service->approveAndGenerate($req, $this->admin, [
            'login_method' => 'voucher_code',
            'code_combination' => 'uppercase_alphanumeric',
            'length' => 6,
            'prefix' => 'TEST',
        ]);

        $this->assertEquals(VoucherRequest::STATUS_PRINTING, $updated->status);
        $this->assertNotNull($updated->batch_pool_id);
        $this->assertNotNull($updated->approved_at);
        $this->assertEquals($this->admin->id, $updated->approved_by);
        $this->assertCount(10, $updated->generated_voucher_ids);

        // Verify vouchers in database
        $vouchersCount = Voucher::whereIn('id', $updated->generated_voucher_ids)->count();
        $this->assertEquals(10, $vouchersCount);

        $sampleVoucher = Voucher::find($updated->generated_voucher_ids[0]);
        $this->assertStringStartsWith('TEST', $sampleVoucher->code);
        $this->assertEquals($this->reseller->id, $sampleVoucher->reseller_id);
        $this->assertEquals(7000, (float) $sampleVoucher->fee_seller);
    }

    public function test_admin_approves_digital_voucher_request_marks_completed(): void
    {
        $service = app(VoucherRequestService::class);

        $req = $service->createRequest([
            'service_profile_id' => $this->hotspotProfile->id,
            'quantity' => 5,
            'delivery_type' => VoucherRequest::DELIVERY_DIGITAL,
        ], $this->reseller);

        $updated = $service->approveAndGenerate($req, $this->admin);

        $this->assertEquals(VoucherRequest::STATUS_COMPLETED, $updated->status);
        $this->assertNotNull($updated->completed_at);
        $this->assertCount(5, $updated->generated_voucher_ids);
    }

    public function test_admin_can_transition_lifecycle_ready_and_completed(): void
    {
        $service = app(VoucherRequestService::class);

        $req = $service->createRequest([
            'service_profile_id' => $this->hotspotProfile->id,
            'quantity' => 5,
            'delivery_type' => VoucherRequest::DELIVERY_PHYSICAL_PRINT,
        ], $this->reseller);

        $approved = $service->approveAndGenerate($req, $this->admin);
        $this->assertEquals(VoucherRequest::STATUS_PRINTING, $approved->status);

        $ready = $service->markAsReady($approved);
        $this->assertEquals(VoucherRequest::STATUS_READY_FOR_PICKUP, $ready->status);

        $completed = $service->markAsCompleted($ready);
        $this->assertEquals(VoucherRequest::STATUS_COMPLETED, $completed->status);
        $this->assertEquals(VoucherRequest::PAYMENT_STATUS_PAID, $completed->payment_status);
        $this->assertNotNull($completed->completed_at);
    }

    public function test_admin_can_reject_request(): void
    {
        $service = app(VoucherRequestService::class);

        $req = $service->createRequest([
            'service_profile_id' => $this->hotspotProfile->id,
            'quantity' => 20,
        ], $this->reseller);

        $rejected = $service->rejectRequest($req, 'Kertas thermal sedang restock', $this->admin);

        $this->assertEquals(VoucherRequest::STATUS_REJECTED, $rejected->status);
        $this->assertEquals('Kertas thermal sedang restock', $rejected->rejection_reason);
        $this->assertEquals($this->admin->id, $rejected->approved_by);
    }

    public function test_reseller_can_cancel_own_pending_request(): void
    {
        $service = app(VoucherRequestService::class);

        $req = $service->createRequest([
            'service_profile_id' => $this->hotspotProfile->id,
            'quantity' => 15,
        ], $this->reseller);

        $cancelled = $service->cancelRequest($req, $this->reseller);
        $this->assertEquals(VoucherRequest::STATUS_CANCELLED, $cancelled->status);
    }

    public function test_reseller_cannot_cancel_another_reseller_request(): void
    {
        $this->expectException(\DomainException::class);

        $service = app(VoucherRequestService::class);

        $req = $service->createRequest([
            'service_profile_id' => $this->hotspotProfile->id,
            'quantity' => 15,
        ], $this->reseller);

        // otherReseller attempts to cancel
        $service->cancelRequest($req, $this->otherReseller);
    }

    public function test_mobile_api_packages_endpoint(): void
    {
        Sanctum::actingAs($this->reseller, ['*']);

        $response = $this->getJson('/api/mobile/vouchers/packages');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment([
                'id' => $this->hotspotProfile->id,
                'name' => 'Paket Hotspot 5 Jam',
                'unit_price' => 10000,
                'cost_price' => 7000,
                'margin' => 3000,
            ]);
    }

    public function test_mobile_api_store_and_list_requests(): void
    {
        Sanctum::actingAs($this->reseller, ['*']);

        // 1. Submit request
        $postRes = $this->postJson('/api/mobile/vouchers/requests', [
            'service_profile_id' => $this->hotspotProfile->id,
            'quantity' => 50,
            'delivery_type' => 'physical_print',
            'payment_method' => 'deposit',
            'notes' => 'Pengajuan via Mobile App',
        ]);

        $postRes->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'quantity' => 50,
                    'total_amount' => 350000,
                    'status' => 'pending',
                ],
            ]);

        // 2. List requests
        $listRes = $this->getJson('/api/mobile/vouchers/requests');

        $listRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 1,
            ])
            ->assertJsonFragment([
                'service_profile_name' => 'Paket Hotspot 5 Jam',
                'quantity' => 50,
                'cost_price' => 7000,
                'total_amount' => 350000,
                'delivery_type' => 'physical_print',
                'status' => 'pending',
            ]);
    }

    public function test_livewire_admin_component_renders(): void
    {
        $service = app(VoucherRequestService::class);
        $service->createRequest([
            'service_profile_id' => $this->hotspotProfile->id,
            'quantity' => 25,
        ], $this->reseller);

        // Test Livewire component
        Livewire::actingAs($this->admin)
            ->test(AdminVoucherRequestIndex::class)
            ->assertStatus(200)
            ->assertSee('Total Request')
            ->assertSee('Paket Hotspot 5 Jam');

        // Test HTTP Page
        $this->actingAs($this->admin)
            ->get('/isp/voucher-requests')
            ->assertStatus(200)
            ->assertSee('Permintaan Cetak Voucher');
    }

    public function test_livewire_reseller_component_renders(): void
    {
        $service = app(VoucherRequestService::class);
        $service->createRequest([
            'service_profile_id' => $this->hotspotProfile->id,
            'quantity' => 30,
        ], $this->reseller);

        // Test Livewire component
        Livewire::actingAs($this->reseller)
            ->test(ResellerVoucherRequestIndex::class)
            ->assertStatus(200)
            ->assertSee('Minta Cetak Voucher Baru')
            ->assertSee('Paket Hotspot 5 Jam');

        // Test HTTP Page
        $this->actingAs($this->reseller)
            ->get('/reseller-portal/voucher-requests')
            ->assertStatus(200)
            ->assertSee('Permintaan Cetak Voucher');
    }
}
