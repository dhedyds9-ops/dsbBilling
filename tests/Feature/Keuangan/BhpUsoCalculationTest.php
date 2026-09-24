<?php

namespace Tests\Feature\Keuangan;

use App\Models\Billing\Invoice;
use App\Models\Billing\InvoiceItem;
use App\Models\Payment\Payment;
use App\Models\Finance\BhpUsoConfig;
use App\Models\Master\Member;
use App\Models\User;
use App\Services\Keuangan\BhpUsoCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BhpUsoCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected BhpUsoCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BhpUsoCalculationService::class);
    }

    public function test_calculate_with_retail_revenue_basis(): void
    {
        // Create User
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create Member
        $member = Member::create([
            'code' => 'M_TEST_1',
            'name' => 'Test Member',
            'phone' => '0812345678',
            'email' => 'member@example.com',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        // 1. Seed active config with RETAIL_REVENUE basis
        BhpUsoConfig::create([
            'period_name' => 'Test 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'calculation_basis' => 'RETAIL_REVENUE',
            'bhp_rate' => 0.0050, // 0.5%
            'uso_rate' => 0.0125, // 1.25%
            'is_active' => true,
        ]);

        // 2. Create Payment & Invoice
        $payment = Payment::create([
            'uuid' => 'p-1111',
            'customer_id' => $member->id,
            'amount' => 100000,
            'status' => 'success',
            'paid_at' => '2026-06-15 12:00:00',
        ]);

        $invoice = Invoice::create([
            'uuid' => 'i-1111',
            'customer_id' => $member->id,
            'invoice_number' => 'INV-TEST-001',
            'issue_date' => '2026-06-15 12:00:00',
            'due_date' => '2026-06-22 12:00:00',
            'total_amount' => 100000,
            'status' => 'paid',
        ]);

        $payment->invoices()->attach($invoice->id);

        InvoiceItem::create([
            'uuid' => 'ii-1111',
            'invoice_id' => $invoice->id,
            'description' => 'Test Package',
            'quantity' => 1,
            'unit_price' => 100000,
            'subtotal' => 100000,
            'owner_settlement_price' => 50000,
            'branch_settlement_price' => 60000,
            'reseller_settlement_price' => 80000,
        ]);

        // Calculate
        $result = $this->service->calculateForPeriod('2026-06-01', '2026-06-30');

        // Check basis & totals
        $this->assertEquals(100000, $result['total_retail']);
        $this->assertEquals(20000, $result['total_reseller_margin']); // 100000 - 80000 = 20000
        $this->assertEquals(100000, $result['dasar_pengenaan']); // basis is RETAIL_REVENUE
        $this->assertEquals(500, $result['bhp_total']); // 0.5% of 100000
        $this->assertEquals(1250, $result['uso_total']); // 1.25% of 100000
        $this->assertEquals(1750, $result['grand_total']);
    }

    public function test_calculate_with_reseller_margin_basis(): void
    {
        // Create User
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create Member
        $member = Member::create([
            'code' => 'M_TEST_2',
            'name' => 'Test Member 2',
            'phone' => '0812345679',
            'email' => 'member2@example.com',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        // 1. Seed active config with RESELLER_MARGIN basis
        BhpUsoConfig::create([
            'period_name' => 'Test 2027',
            'start_date' => '2027-01-01',
            'end_date' => '2027-12-31',
            'calculation_basis' => 'RESELLER_MARGIN',
            'bhp_rate' => 0.0050, // 0.5%
            'uso_rate' => 0.0125, // 1.25%
            'is_active' => true,
        ]);

        // 2. Create Payment & Invoice
        $payment = Payment::create([
            'uuid' => 'p-2222',
            'customer_id' => $member->id,
            'amount' => 100000,
            'status' => 'success',
            'paid_at' => '2027-06-15 12:00:00',
        ]);

        $invoice = Invoice::create([
            'uuid' => 'i-2222',
            'customer_id' => $member->id,
            'invoice_number' => 'INV-TEST-002',
            'issue_date' => '2027-06-15 12:00:00',
            'due_date' => '2027-06-22 12:00:00',
            'total_amount' => 100000,
            'status' => 'paid',
        ]);

        $payment->invoices()->attach($invoice->id);

        InvoiceItem::create([
            'uuid' => 'ii-2222',
            'invoice_id' => $invoice->id,
            'description' => 'Test Package',
            'quantity' => 1,
            'unit_price' => 100000,
            'subtotal' => 100000,
            'owner_settlement_price' => 50000,
            'branch_settlement_price' => 60000,
            'reseller_settlement_price' => 80000,
        ]);

        // Calculate
        $result = $this->service->calculateForPeriod('2027-06-01', '2027-06-30');

        // Check basis & totals
        $this->assertEquals(100000, $result['total_retail']);
        $this->assertEquals(20000, $result['total_reseller_margin']); // 100000 - 80000 = 20000
        $this->assertEquals(20000, $result['dasar_pengenaan']); // basis is RESELLER_MARGIN
        $this->assertEquals(100, $result['bhp_total']); // 0.5% of 20000
        $this->assertEquals(250, $result['uso_total']); // 1.25% of 20000
        $this->assertEquals(350, $result['grand_total']);
    }
}
