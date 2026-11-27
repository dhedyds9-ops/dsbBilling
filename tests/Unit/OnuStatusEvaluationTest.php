<?php

namespace Tests\Unit;

use App\Livewire\NOC\Onu\Index as NocOnuIndex;
use App\Models\ACS\ACSDevice;
use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnuStatusEvaluationTest extends TestCase
{
    use RefreshDatabase;

    public function test_stale_onu_with_historical_rx_is_offline(): void
    {
        $component = new NocOnuIndex();

        // ONU seen 5 days ago with historical rx_power_dbm = -20.36
        $onu = new Onu([
            'status' => 'active',
            'serial_number' => 'HMUD9FC063BD',
            'rx_power_dbm' => -20.36,
            'tx_power_dbm' => 2.06,
            'last_seen_at' => now()->subDays(5),
        ]);

        $status = $component->getOnuStatus($onu);
        $this->assertEquals('OFFLINE', $status);
    }

    public function test_inactive_onu_is_offline(): void
    {
        $component = new NocOnuIndex();

        $onu = new Onu([
            'status' => 'inactive',
            'serial_number' => 'HMUD5F9746AE',
            'rx_power_dbm' => -21.19,
            'last_seen_at' => now()->subMinutes(2),
        ]);

        $status = $component->getOnuStatus($onu);
        $this->assertEquals('OFFLINE', $status);
    }

    public function test_freshly_seen_onu_with_normal_rx_is_online(): void
    {
        $component = new NocOnuIndex();

        $onu = new Onu([
            'status' => 'active',
            'serial_number' => 'HMUD5FB4588A',
            'rx_power_dbm' => -20.04,
            'tx_power_dbm' => 2.34,
            'last_seen_at' => now()->subMinutes(3),
        ]);

        $status = $component->getOnuStatus($onu);
        $this->assertEquals('ONLINE', $status);
    }

    public function test_freshly_seen_onu_with_low_rx_is_low_rx(): void
    {
        $component = new NocOnuIndex();

        $onu = new Onu([
            'status' => 'active',
            'serial_number' => 'HMUD5FB8820F',
            'rx_power_dbm' => -28.5,
            'last_seen_at' => now()->subMinutes(2),
        ]);

        $status = $component->getOnuStatus($onu);
        $this->assertEquals('LOW_RX', $status);
    }

    public function test_onu_with_fresh_acs_inform_is_online(): void
    {
        $component = new NocOnuIndex();

        $acs = new ACSDevice([
            'status' => 'online',
            'last_inform' => now()->subMinutes(5),
        ]);

        $onu = new Onu([
            'status' => 'active',
            'serial_number' => 'HMUD_ACS_ACTIVE',
            'rx_power_dbm' => -19.5,
            'last_seen_at' => now()->subDays(2),
        ]);
        $onu->setRelation('acsDevice', $acs);

        $status = $component->getOnuStatus($onu);
        $this->assertEquals('ONLINE', $status);
    }

    public function test_deleting_olt_marks_child_onus_as_inactive_and_clears_signal(): void
    {
        $olt = Olt::create([
            'name' => 'OLT Test Lab',
            'code' => 'OLT-TEST-01',
            'ip_address' => '10.10.10.1',
            'status' => 'active',
        ]);

        $onu = Onu::create([
            'olt_id' => $olt->id,
            'name' => 'Test ONU',
            'serial_number' => 'TESTONU001',
            'status' => 'active',
            'rx_power_dbm' => -20.5,
            'tx_power_dbm' => 2.1,
            'last_seen_at' => now()->subDays(3),
        ]);

        // Delete OLT
        $olt->delete();

        // Refresh ONU from DB
        $onu->refresh();

        $this->assertEquals('inactive', $onu->status);
        $this->assertNull($onu->rx_power_dbm);
        $this->assertNull($onu->tx_power_dbm);
    }
}
