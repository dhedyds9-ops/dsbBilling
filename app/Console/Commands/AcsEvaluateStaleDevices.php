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

        // 1. Auto-refresh last_inform directly from GenieACS NBI (lightweight projection _id, _lastInform)
        try {
            $acsDriver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $gDevices = $acsDriver->listDevices(['projection' => '_id,_lastInform'], 5000);
            if (!empty($gDevices)) {
                foreach ($gDevices as $gd) {
                    $gId = $gd['_id'] ?? null;
                    if ($gId && !empty($gd['_lastInform'])) {
                        try {
                            $dt = Carbon::parse($gd['_lastInform']);
                            ACSDevice::withoutGlobalScopes()
                                ->where('uuid', $gId)
                                ->update([
                                    'last_inform' => $dt,
                                    'last_contact' => $dt,
                                ]);
                        } catch (\Throwable) {}
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::info("AcsEvaluateStaleDevices: NBI sync skipped ({$e->getMessage()}), evaluating local records.");
        }

        $defaultInterval = (int) ($this->option('interval')
            ?: config('genieacs.cpe_defaults.periodic_inform_interval_seconds', 300));

        $onlineThreshold = max(600, $defaultInterval * 2); // 10 menit toleransi online
        $offlineThreshold = max(720, ($defaultInterval * 2) + 120); // 12 menit toleransi offline (sesuai standard TR-069 inform)

        $now = now();
        $transitionedOnline = 0;
        $transitionedOffline = 0;
        $transitionedStale = 0;
        $evaluatedCount = 0;

        // Query all devices to evaluate and auto-recover online state
        ACSDevice::withoutGlobalScopes()
            ->with(['customerService.pppoeUser', 'customerService.hotspotUser', 'onu'])
            ->chunkById(250, function ($devices) use ($now, $onlineThreshold, $offlineThreshold, $stateEngine, &$transitionedOnline, &$transitionedOffline, &$transitionedStale, &$evaluatedCount) {
                foreach ($devices as $device) {
                    $evaluatedCount++;

                    $lastInform = $device->last_inform ?: $device->last_contact;

                    if (!$lastInform) {
                        // Never contacted or informed
                        if ($device->status !== 'offline') {
                            $oldStatus = $device->status;
                            $device->update(['status' => 'offline']);
                            $transitionedOffline++;
                            $this->notifyStatusChange($device, 'offline', $now, $stateEngine, $oldStatus);
                        }
                        continue;
                    }

                    $informTime = Carbon::parse($lastInform);
                    $elapsedSeconds = $now->timestamp - $informTime->timestamp;

                    // Clock skew protection: if timestamp is wildly in the future (e.g. > 120s), do not treat as recent inform
                    if ($elapsedSeconds < -120) {
                        Log::warning("AcsEvaluateStaleDevices: Device {$device->uuid} has last_inform in future ({$informTime->toIso8601String()}). Skipping.");
                        continue;
                    }

                    // Dynamic threshold: jika modem terkonfirmasi memiliki sesi PPPoE aktif di MikroTik atau link optik OLT aktif,
                    // gunakan toleransi inform lebih longgar (hingga 45-60 menit) agar modem dengan interval inform bawaan 30 menit tidak berkedip offline.
                    $hasActivePppoe = false;
                    $pppoeUsername = $device->customerService?->pppoeUser?->username;
                    if ($pppoeUsername) {
                        $hasActivePppoe = \App\Models\ISP\OnlineSession::where('username', $pppoeUsername)->exists();
                    }
                    $hasActiveOnu = $device->onu && $device->onu->status === 'active' && $device->onu->rx_power_dbm !== null && (float)$device->onu->rx_power_dbm >= -35.0;

                    $effectiveOnlineThreshold = ($hasActivePppoe || $hasActiveOnu) ? max($onlineThreshold, 2700) : $onlineThreshold; // 45 menit jika PPPoE/OLT aktif
                    $effectiveOfflineThreshold = ($hasActivePppoe || $hasActiveOnu) ? max($offlineThreshold, 3600) : $offlineThreshold; // 60 menit jika PPPoE/OLT aktif

                    if ($elapsedSeconds <= $effectiveOnlineThreshold) {
                        // Recent Inform -> Automatically recover to ONLINE!
                        if ($device->status !== 'online') {
                            $oldStatus = $device->status;
                            $device->update(['status' => 'online']);
                            $transitionedOnline++;

                            if ($device->onu_id) {
                                Onu::withoutGlobalScopes(['branch_isolation'])
                                    ->where('id', $device->onu_id)
                                    ->update(['status' => 'active', 'last_seen_at' => $informTime]);
                            }

                            $this->notifyStatusChange($device, 'online', $now, $stateEngine, $oldStatus);
                        } else {
                            if ($device->onu_id) {
                                Onu::withoutGlobalScopes(['branch_isolation'])
                                    ->where('id', $device->onu_id)
                                    ->update(['last_seen_at' => $informTime]);
                            }
                        }
                    } elseif ($elapsedSeconds > $effectiveOfflineThreshold) {
                        // Passed offline threshold -> Mark offline
                        if ($device->status !== 'offline') {
                            $oldStatus = $device->status;
                            $device->update(['status' => 'offline']);
                            $transitionedOffline++;

                            // Hanya tandai ONU inactive jika ONU tidak memiliki redaman optik valid dari OLT
                            if ($device->onu_id) {
                                Onu::withoutGlobalScopes(['branch_isolation'])
                                    ->where('id', $device->onu_id)
                                    ->where('status', 'active')
                                    ->where(function($oq) {
                                        $oq->whereNull('rx_power_dbm')
                                           ->orWhere('rx_power_dbm', '<', -35.0);
                                    })
                                    ->update(['status' => 'inactive']);
                            }

                            $this->notifyStatusChange($device, 'offline', $now, $stateEngine, $oldStatus);
                        }
                    } elseif ($elapsedSeconds > $effectiveOnlineThreshold && $device->status !== 'stale') {
                        // Passed online threshold but within offline tolerance -> Mark stale
                        $oldStatus = $device->status;
                        $device->update(['status' => 'stale']);
                        $transitionedStale++;

                        $this->notifyStatusChange($device, 'stale', $now, $stateEngine, $oldStatus);
                    }
                }
            });

        $this->info("Evaluation finished. Evaluated: {$evaluatedCount}, Online: {$transitionedOnline}, Stale: {$transitionedStale}, Offline: {$transitionedOffline}.");
        Log::info("AcsEvaluateStaleDevices: Evaluated {$evaluatedCount} devices. {$transitionedOnline} transitioned to ONLINE, {$transitionedStale} to STALE, {$transitionedOffline} to OFFLINE.");

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
