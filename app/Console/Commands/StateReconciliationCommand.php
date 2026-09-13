<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Onu;
use App\Models\ACS\ACSDevice;
use App\Services\ISP\Session\OnlineSessionStore;
use App\Services\ISP\UnifiedDeviceStateEngine;
use Illuminate\Support\Facades\Log;

class StateReconciliationCommand extends Command
{
    protected $signature = 'gacs:reconcile-states';
    protected $description = 'Perform periodic reconciliation of states (OLT, GenieACS, Sessions) to fix ghost sessions and out-of-sync data.';

    public function handle(OnlineSessionStore $sessionStore, UnifiedDeviceStateEngine $stateEngine)
    {
        $this->info('Starting state reconciliation...');
        
        // 1. Ghost Session Cleanup
        $this->info('Cleaning up ghost sessions...');
        $deletedSessions = $sessionStore->cleanupGhostSessions();
        $this->info("Deleted $deletedSessions ghost sessions.");
        Log::info("StateReconciliation: Deleted $deletedSessions ghost sessions.");

        // 2. Audit OLT ↔ ONU (Database)
        // Mark ONUs as inactive if they haven't been seen for > 15 minutes by the poller
        $this->info('Auditing ONU states...');
        $updatedOnus = Onu::where('status', 'active')
            ->where('last_seen_at', '<', now()->subMinutes(15))
            ->update(['status' => 'inactive']);
        $this->info("Marked $updatedOnus ONUs as inactive due to staleness.");

        // 3. Audit GenieACS ↔ ACSDevice
        // Handled dynamically via `isDeviceOnline` logic in the engine, but we can clean up the `acs_devices` table
        // STALE threshold is handled at read-time in the poller, but if poller misses it:
        $this->info('Auditing ACS Device states...');
        // Just setting them to offline if they haven't been contacted in 2 hours
        $updatedAcs = ACSDevice::whereIn('status', ['online', 'stale'])
            ->where('last_contact', '<', now()->subHours(2))
            ->update(['status' => 'offline']);
        $this->info("Marked $updatedAcs ACS devices as offline due to staleness.");

        // 4. Force evaluate state for all active customer services in chunks
        $this->info('Re-evaluating active customer services...');
        
        $count = 0;
        CustomerService::where('status', 'active')
            ->chunkById(500, function ($services) use ($stateEngine, &$count) {
                foreach ($services as $service) {
                    $stateEngine->evaluateAndSave($service);
                    $count++;
                }
            });

        $this->info("Re-evaluated states for $count active customer services.");
        Log::info("StateReconciliation: Completed. Re-evaluated $count services.");

        // 5. Run Root Cause Analysis
        $this->info('Running Root Cause Analysis...');
        app(\App\Services\ISP\RootCauseAnalysisEngine::class)->runAnalysis();
        $this->info('Root Cause Analysis completed.');

        return 0;
    }
}
