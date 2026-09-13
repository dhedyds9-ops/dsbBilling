<?php

namespace Tests\Feature;

use App\Services\Keuangan\SettlementCalculator;
use PHPUnit\Framework\TestCase;

class SettlementCalculationTest extends TestCase
{
    /** @test */
    public function it_calculates_standard_settlement_correctly()
    {
        $calculator = new SettlementCalculator();

        // Scenario: 
        // Selling Price = 200,000
        // Reseller Price = 170,000
        // Branch Price = 150,000
        // Owner Price = 150,000
        $result = $calculator->calculateItem(200000, 150000, 150000, 170000);

        $this->assertEquals(30000, $result['reseller_margin'], 'Reseller Margin harus 30.000');
        $this->assertEquals(20000, $result['branch_margin'], 'Branch Margin harus 20.000');
        $this->assertEquals(150000, $result['owner_settlement'], 'Owner Settlement harus 150.000');
        $this->assertEquals(200000, $result['total_allocation'], 'Total Allocation harus 200.000');
    }

    /** @test */
    public function it_calculates_partial_payment_settlement_correctly()
    {
        $calculator = new SettlementCalculator();

        // Scenario: Partial payment 50%
        // Selling Price = 200,000
        // Paid = 100,000 (Payment ratio = 0.5)
        $result = $calculator->calculateItem(200000, 150000, 150000, 170000, 1, 0.5);

        $this->assertEquals(15000, $result['reseller_margin'], 'Reseller Margin harus 50% (15.000)');
        $this->assertEquals(10000, $result['branch_margin'], 'Branch Margin harus 50% (10.000)');
        $this->assertEquals(75000, $result['owner_settlement'], 'Owner Settlement harus 50% (75.000)');
        $this->assertEquals(100000, $result['total_allocation'], 'Total Allocation harus 50% (100.000)');
    }

    /** @test */
    public function it_calculates_multiple_quantity_settlement_correctly()
    {
        $calculator = new SettlementCalculator();

        // Scenario: Quantity = 2
        // Selling Price = 200,000
        $result = $calculator->calculateItem(200000, 150000, 150000, 170000, 2, 1.0);

        $this->assertEquals(60000, $result['reseller_margin'], 'Reseller Margin harus dikali 2 (60.000)');
        $this->assertEquals(40000, $result['branch_margin'], 'Branch Margin harus dikali 2 (40.000)');
        $this->assertEquals(300000, $result['owner_settlement'], 'Owner Settlement harus dikali 2 (300.000)');
        $this->assertEquals(400000, $result['total_allocation'], 'Total Allocation harus 400.000');
    }

    /** @test */
    public function it_calculates_default_allocation_when_prices_are_zero()
    {
        $calculator = new SettlementCalculator();

        // Scenario: Prices are 0, fallback to 35% HPP
        $result = $calculator->calculateItem(200000, 0, 0, 0);

        $this->assertEquals(0, $result['reseller_margin']);
        $this->assertEquals(0, $result['branch_margin']);
        $this->assertEquals(70000, $result['owner_settlement'], 'Default HPP is 35% of 200,000 = 70,000');
    }
}
