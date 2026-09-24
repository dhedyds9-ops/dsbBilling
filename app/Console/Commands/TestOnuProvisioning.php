<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ISP\Onu;
use App\Services\Provisioning\OnuCapabilityResolver;
use App\Services\Provisioning\WifiConfigurationService;
use App\Services\Provisioning\WanConfigurationService;
use App\Services\Provisioning\BridgeConfigurationService;
use App\Services\Provisioning\OnuConfigurationJobEngine;
use App\Models\ISP\OnuConfigurationJob;

class TestOnuProvisioning extends Command
{
    protected $signature = 'onu:test-provision {serial_number}';
    protected $description = 'Run E2E tests on a physical ONU';

    public function handle(
        OnuCapabilityResolver $capabilityResolver,
        WifiConfigurationService $wifiService,
        WanConfigurationService $wanService,
        BridgeConfigurationService $bridgeService
    ) {
        $serial = $this->argument('serial_number');
        $onu = Onu::where('serial_number', $serial)->orWhere('mac_address', $serial)->first();

        if (!$onu) {
            $this->error("ONU not found: {$serial}");
            return;
        }

        $this->info("Found ONU ID: {$onu->id}. Testing Capability Discovery...");
        
        $capability = $capabilityResolver->resolve($onu);
        $this->table(['Discovered'], [['Yes']]);
        $this->info("Capabilities: " . json_encode($capability->capabilities, JSON_PRETTY_PRINT));

        // Test 1: WiFi
        $this->info("\n--- TEST 1: WiFi Configuration ---");
        $job = $wifiService->configureWifi($onu, [
            1 => [
                'ssid' => 'DS-Billing-Test',
                'password' => 'secret123',
                'enable' => true
            ]
        ]);
        
        $this->waitForJob($job);

        // Test 2: WAN PPPoE (Mode A)
        $this->info("\n--- TEST 3: PPPoE WAN (Mode A) ---");
        $job = $wanService->configureWan($onu, 1, 'PPPOE', [
            'vlan' => 100,
            'username' => 'test_pppoe',
            'password' => 'test_pass'
        ]);
        
        $this->waitForJob($job);
    }

    private function waitForJob($jobRecord)
    {
        if (!$jobRecord) {
            $this->warn("Job ignored (Idempotent / No-op)");
            return;
        }

        $this->info("Job Dispatched: ID {$jobRecord->id}. Waiting for processing...");
        
        $maxRetries = 60;
        while ($maxRetries > 0) {
            $jobRecord->refresh();
            if (in_array($jobRecord->status, ['SUCCESS', 'FAILED'])) {
                break;
            }
            sleep(1);
            $maxRetries--;
        }

        $this->info("Job finished with status: {$jobRecord->status}");
        if ($jobRecord->status === 'FAILED') {
            $this->error("Error: {$jobRecord->error_message}");
        } elseif ($jobRecord->status === 'SUCCESS') {
            $this->info("Drift Status: " . ($jobRecord->onu->state->drift_status ?? 'N/A'));
        }
    }
}
