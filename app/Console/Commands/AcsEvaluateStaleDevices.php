<?php

namespace App\Console\Commands;

use App\Events\ISP\Tr069StatusChanged;
use App\Models\ACS\ACSDevice;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Onu;
use App\Services\ISP\UnifiedDeviceStateEngine;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AcsEvaluateStaleDevices extends Command
{
    protected $signature = 'acs:evaluate-stale-devices {--interval= : Override default inform interval in seconds}';
    protected $description = 'Evaluate database-driven online/stale/offline state of ACS devices and ONUs without NBI polling';

    public function handle(UnifiedDeviceStateEngine $stateEngine): int
    {
        $this->info('Starting database-driven stale device evaluation...');

        $defaultInterval = (int) ($this->option('interval')
            ?: config('genieacs.cpe_defaults.periodic_inform_interval_seconds', 300));

        $onlineThreshold = $defaultInterval + 60;
        $offlineThreshold = ($defaultInterval * 2) + 120; // Existing threshold rule: (interval * 2) + 120 seconds

        $now = now();
        $transitionedOffline = 0;
        $transitionedStale = 0;
        $evaluatedCount = 0;

        // Query devices currently considered online or stale
        ACSDevice::withoutGlobalScopes()
            ->whereIn('status', ['online', 'stale'])
            ->chunkById(250, function ($devices) use ($now, $onlineThreshold, $offlineThreshold, $stateEngine, &$transitionedOffline, &$transitionedStale, &$evaluatedCount) {
                foreach ($devices as $device) {
                    $evaluatedCount++;
                    $lastInform = $device->last_inform ?: $device->last_contact;

                    if (!$lastInform) {
                        // Never contacted or informed
                        $device->update(['status' => 'offline']);
                        $transitionedOffline++;
                        $this->notifyStatusChange($device, 'offline', $now, $stateEngine);
                        continue;
                    }

                    $informTime = Carbon::parse($lastInform);
                    $elapsedSeconds = $now->timestamp - $informTime->timestamp;

                    // Clock skew protection: if timestamp is wildly in the future (e.g. > 120s), do not treat as recent inform
                    if ($elapsedSeconds < -120) {
                        Log::warning("AcsEvaluateStaleDevices: Device {$device->uuid} has last_inform in future ({$informTime->toIso8601String()}). Skipping.");
                        continue;
                    }

                    if ($elapsedSeconds > $offlineThreshold) {
                        // Passed offline threshold -> Mark offline
                        $oldStatus = $device->status;
                        $device->update(['status' => 'offline']);
                        $transitionedOffline++;

                        // Also update linked ONU status if present
                        if ($device->onu_id) {
                            Onu::withoutGlobalScopes(['branch_isolation'])
                                ->where('id', $device->onu_id)
                                ->where('status', 'active')
                                ->update(['status' => 'inactive']);
                        }

                        $this->notifyStatusChange($device, 'offline', $now, $stateEngine, $oldStatus);
                    } elseif ($elapsedSeconds > $onlineThreshold && $device->status !== 'stale') {
                        // Passed online threshold but within offline tolerance -> Mark stale
                        $oldStatus = $device->status;
                        $device->update(['status' => 'stale']);
                        $transitionedStale++;

                        $this->notifyStatusChange($device, 'stale', $now, $stateEngine, $oldStatus);
                    }
                }
            });

        $this->info("Evaluation finished. Evaluated: {$evaluatedCount}, Stale: {$transitionedStale}, Offline: {$transitionedOffline}.");
        Log::info("AcsEvaluateStaleDevices: Evaluated {$evaluatedCount} devices. {$transitionedStale} transitioned to STALE, {$transitionedOffline} transitioned to OFFLINE.");

        return Command::SUCCESS;
    }

    protected function notifyStatusChange(ACSDevice $device, string $newStatus, Carbon $time, UnifiedDeviceStateEngine $stateEngine, string $oldStatus = 'unknown'): void
    {
        // 1. Dispatch TR-069 Status Changed Event
        event(new Tr069StatusChanged($device->uuid, $oldStatus, $newStatus, $time));

        // 2. Trigger UnifiedDeviceStateEngine if linked to CustomerService
        $cs = $device->customerService
            ?? ($device->onu_id ? CustomerService::withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])->where('onu_id', $device->onu_id)->first() : null);

        if ($cs) {
            try {
                $stateEngine->evaluateAndSave($cs);
            } catch (\Throwable $e) {
                Log::warning("AcsEvaluateStaleDevices: Error evaluating CustomerService {$cs->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
