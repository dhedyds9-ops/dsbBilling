<?php

namespace Tests\Feature;

use App\Models\CRM\Customer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class MobileRevenueSummaryTest extends TestCase
{
    use RefreshDatabase;

    private function insertPayment(int $customerId, ?int $resellerId, string $status, float $amount, string $gateway = 'ipaymu'): void
    {
        DB::table('payments')->insert([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customerId,
            'reseller_id' => $resellerId,
            'amount' => $amount,
            'method' => 'cash',
            'status' => $status,
            'gateway' => $gateway,
            'paid_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_revenue_summary_counts_only_successful_payments_scoped_to_reseller(): void
    {
        $role = Role::firstOrCreate(['name' => 'reseller'], ['display_name' => 'Reseller', 'guard_name' => 'web']);
        $reseller = User::factory()->create(['is_active' => true]);
        $reseller->roles()->attach($role->id);
        $other = User::factory()->create(['is_active' => true]);

        $cust = Customer::create(['uuid' => Str::uuid(), 'code' => 'CUST-R1', 'name' => 'Dedi Surya', 'phone' => '0811', 'reseller_id' => $reseller->id]);
        $custOther = Customer::create(['uuid' => Str::uuid(), 'code' => 'CUST-R2', 'name' => 'Lain', 'phone' => '0822', 'reseller_id' => $other->id]);

        $this->insertPayment($cust->id, $reseller->id, 'success', 150000, 'manual');
        $this->insertPayment($cust->id, $reseller->id, 'success', 130000, 'manual');
        $this->insertPayment($cust->id, $reseller->id, 'failed', 150000);
        $this->insertPayment($cust->id, $reseller->id, 'failed', 150000);
        $this->insertPayment($cust->id, $reseller->id, 'pending', 99000);
        // milik reseller lain: tidak boleh ikut terhitung
        $this->insertPayment($custOther->id, $other->id, 'success', 999000);

        $token = $reseller->createToken('t')->plainTextToken;

        $res = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/mobile/reports/revenue-summary?period=all_time');

        $res->assertOk();
        $this->assertEquals(280000, $res->json('data.paid_total'));
        $this->assertEquals(2, $res->json('data.paid_count'));
        $this->assertEquals(2, $res->json('data.failed_count'));
        $this->assertEquals(1, $res->json('data.pending_count'));
    }
}
