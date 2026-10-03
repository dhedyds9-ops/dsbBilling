<?php

namespace Tests\Feature\Finance;

use App\Models\Finance\CashTransaction;
use App\Models\Finance\MemberIncome;
use App\Models\Master\ExpenseCategory;
use App\Models\Master\Member;
use App\Models\User;
use App\Services\RevenueSharingEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevenueSharingEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculate_preview_correctly_distributes_revenue_and_expense()
    {
        $service = new RevenueSharingEngineService();
        $period = "2026-10";

        $member1 = Member::factory()->create(["status" => "active"]);
        $member2 = Member::factory()->create(["status" => "active"]);

        // Total revenue 10,000,000
        MemberIncome::factory()->create([
            "member_id" => $member1->id,
            "period" => $period,
            "amount" => 7000000,
            "status" => "posted"
        ]);
        MemberIncome::factory()->create([
            "member_id" => $member2->id,
            "period" => $period,
            "amount" => 3000000,
            "status" => "posted"
        ]);

        // Expense
        $expenseCat = ExpenseCategory::factory()->create(["affects_revenue_sharing" => true]);
        CashTransaction::factory()->create([
            "type" => "expense",
            "expense_category_id" => $expenseCat->id,
            "amount" => 1000000,
            "date" => "2026-10-15",
            "status" => "posted"
        ]);

        $preview = $service->calculatePreview($period);

        $this->assertEquals(10000000, $preview["total_revenue"]);
        $this->assertEquals(1000000, $preview["total_expense"]);
        $this->assertEquals(9000000, $preview["total_distributed"]);

        // Member 1: 70% revenue -> 70% expense share (700k) -> net 6.3m
        $this->assertEquals(70, $preview["items"][0]["percentage"]);
        $this->assertEquals(700000, $preview["items"][0]["expense_share"]);
        $this->assertEquals(6300000, $preview["items"][0]["net_share"]);

        // Member 2: 30% revenue -> 30% expense share (300k) -> net 2.7m
        $this->assertEquals(30, $preview["items"][1]["percentage"]);
        $this->assertEquals(300000, $preview["items"][1]["expense_share"]);
        $this->assertEquals(2700000, $preview["items"][1]["net_share"]);
    }
}

