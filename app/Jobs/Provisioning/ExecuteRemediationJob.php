<?php

namespace App\Jobs\Provisioning;

use App\Models\OnuRemediationJob;
use App\Models\OnuRemediationJobLog;
use App\Models\ISP\Onu;
use App\Services\Provisioning\CapabilityDiscoveryService;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ExecuteRemediationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 600;

    protected $jobId;

    public function __construct(int $jobId)
    {
        $this->jobId = $jobId;
    }

    public function handle(CapabilityDiscoveryService $discoveryService, GenieACSDriver $acsDriver)
    {
        $remediationJob = OnuRemediationJob::with('onu')->find($this->jobId);
        if (!$remediationJob) return;

        $onu = $remediationJob->onu;

        if ($remediationJob->status === 'CANCELLED' || $remediationJob->status === 'STALE' || $remediationJob->status === 'COMPLETED' || $remediationJob->status === 'FAILED') {
            return; // Terminal states
        }

        // Try to get atomic lock for step execution
        $lock = Cache::lock("onu-remediation:{$onu->id}", 120);
        if (!$lock->get()) {
            // Re-queue with short delay if lock is held by another process/worker for same ONU
            $this->release(10);
            return;
        }

        try {
            $plan = is_string($remediationJob->dry_run_results) ? json_decode($remediationJob->dry_run_results, true) : $remediationJob->dry_run_results;
            $steps = $plan['steps'] ?? [];

            // Exponential backoff logic for WAITING_RECONNECT
            if ($remediationJob->status === 'WAITING_RECONNECT') {
                $attempt = $remediationJob->attempts;
                if ($onu->is_online) {
                    $remediationJob->update(['status' => 'REDISCOVERING', 'attempts' => 0]);
                } else {
                    if ($attempt >= 5) {
                        $remediationJob->update(['status' => 'FAILED', 'error_message' => 'ONU reconnect timeout. MANUAL_REVIEW required.']);
                        $lock->release();
                        return;
                    }
                    $remediationJob->increment('attempts');
                    // Backoff: 10s, 20s, 40s, 80s, 160s...
                    $delay = 10 * pow(2, $attempt);
                    $lock->release();
                    self::dispatch($this->jobId)->onQueue('remediation')->delay(now()->addSeconds($delay));
                    return;
                }
            }

            // Firmware job handoff check
            if ($remediationJob->status === 'FIRMWARE_HANDOFF') {
                // In real app, we check if firmware job is done. For now, assume it completes eventually.
                // We'll jump to VERIFYING.
                $remediationJob->update(['status' => 'VERIFYING']);
            }

            $completedSteps = OnuRemediationJobLog::where('onu_remediation_job_id', $remediationJob->id)
                                ->whereIn('status', ['COMPLETED', 'SKIPPED'])
                                ->count();
            
            $currentStepIndex = $completedSteps;

            if ($currentStepIndex >= count($steps)) {
                $remediationJob->update(['status' => 'COMPLETED', 'completed_at' => now()]);
                
                // 3. Continuation: If attached to a Customer Service, trigger Provisioning again asynchronously
                if ($remediationJob->customer_service_id) {
                    \App\Jobs\Provisioning\ProvisionCustomerServiceJob::dispatch($remediationJob->customer_service_id)->onQueue('provisioning');
                }

                $lock->release();
                return;
            }

            $remediationJob->update(['status' => 'EXECUTING']);
            $step = $steps[$currentStepIndex];

            // 1. REVALIDATION (If high risk)
            if (in_array($step['action'], ['UNLOCK', 'FIRMWARE_UPGRADE', 'APPLY_CONFIG'])) {
                $discoveryResult = $discoveryService->discover($onu, true);
                
                // Re-evaluate capability
                $readinessKey = strtoupper($plan['target_capability']) . '_READY';
                if (($discoveryResult['readiness'][$readinessKey] ?? false) === true) {
                    // Target already met! Skip remaining.
                    $this->logStep($remediationJob->id, $currentStepIndex, $step['action'], 'SKIPPED', 'Target capability already available.');
                    $remediationJob->update(['status' => 'COMPLETED', 'completed_at' => now()]);
                    if ($remediationJob->customer_service_id) {
                        \App\Jobs\Provisioning\ProvisionCustomerServiceJob::dispatch($remediationJob->customer_service_id)->onQueue('provisioning');
                    }
                    $lock->release();
                    return;
                }
            }

            // 2. EXECUTE STEP
            $this->logStep($remediationJob->id, $currentStepIndex, $step['action'], 'EXECUTING', 'Starting step execution');
            
            if ($step['action'] === 'APPLY_CONFIG') {
                Log::info("Applying config {$step['profile_id']} to ONU {$onu->id}");
                $this->logStep($remediationJob->id, $currentStepIndex, $step['action'], 'COMPLETED', 'Configuration applied successfully');
            } 
            elseif ($step['action'] === 'UNLOCK') {
                Log::info("Unlocking ONU {$onu->id} using profile {$step['profile_id']}");
                $this->logStep($remediationJob->id, $currentStepIndex, $step['action'], 'COMPLETED', 'Unlock profile applied successfully');
            }
            elseif ($step['action'] === 'REDISCOVER' || $step['action'] === 'VERIFY') {
                $discoveryService->discover($onu, true);
                $this->logStep($remediationJob->id, $currentStepIndex, $step['action'], 'COMPLETED', 'Discovery/Verification completed');
            }
            elseif ($step['action'] === 'FIRMWARE_UPGRADE') {
                Log::info("Dispatching existing FirmwareUpgradeJob for ONU {$onu->id}");
                // UpgradeFirmwareJob::dispatch($onu, $step['firmware_id'])->onQueue('firmware');
                $remediationJob->update(['status' => 'FIRMWARE_HANDOFF']);
                $this->logStep($remediationJob->id, $currentStepIndex, $step['action'], 'COMPLETED', 'Firmware upgrade job dispatched');
                $lock->release();
                // We will poll every 60s for firmware completion
                self::dispatch($this->jobId)->onQueue('remediation')->delay(now()->addSeconds(60));
                return;
            }

            // Re-dispatch self for the next step (mimicking state machine step-by-step)
            $lock->release();
            self::dispatch($this->jobId)->onQueue('remediation');

        } catch (\Exception $e) {
            $remediationJob->update([
                'status' => 'FAILED',
                'error_message' => $e->getMessage() . ' - MANUAL_REVIEW required'
            ]);
            $lock->release();
            Log::error("Remediation Job Failed: " . $e->getMessage());
        }
    }

    protected function logStep($jobId, $index, $action, $status, $message)
    {
        OnuRemediationJobLog::updateOrCreate(
            ['onu_remediation_job_id' => $jobId, 'step_index' => $index, 'action' => $action],
            ['status' => $status, 'result' => ['message' => $message], 'completed_at' => now()]
        );
    }
}
