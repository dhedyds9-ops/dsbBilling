<?php

namespace App\Services\Provisioning;

use App\Models\ISP\Onu;
use App\Models\OnuRemediationJob;
use App\Jobs\Provisioning\ExecuteRemediationJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RemediationExecutionService
{
    /**
     * Start the remediation process from a provided Dry Run Plan.
     */
    public function startRemediation(Onu $onu, array $plan, ?int $userId = null, bool $isApproval = false, ?int $customerServiceId = null): array
    {
        // 1. Stale Plan Protection (Expiry & Hash check)
        if (isset($plan['expires_at']) && now()->parse($plan['expires_at'])->isPast()) {
            return ['status' => 'PLAN_STALE', 'message' => 'The remediation plan has expired. Please run dry-run again.'];
        }

        $providedHash = $plan['plan_hash'] ?? '';
        
        // Re-generate hash to ensure payload hasn't been tampered with
        $hashData = [
            'identity' => $plan['identity'] ?? [],
            'target_capability' => $plan['target_capability'] ?? '',
            'capability_state_hash' => $onu->capability->state_hash ?? '',
            'steps' => $plan['steps'] ?? [],
            'requires_approval' => $plan['requires_approval'] ?? false,
            'customer_service_id' => $customerServiceId
        ];
        $expectedHash = hash('sha256', json_encode($hashData));

        if ($providedHash !== $expectedHash) {
            return ['status' => 'PLAN_STALE', 'message' => 'Plan hash mismatch. Data might have been tampered with or device capability changed.'];
        }

        // 2. Concurrency Lock using Atomic Lock
        $lock = Cache::lock("onu-remediation:{$onu->id}", 600); // 10 minutes max initial lock for starting
        if (!$lock->get()) {
            return ['status' => 'ONU_REMEDIATION_LOCKED', 'message' => 'Another remediation job is currently active for this ONU.'];
        }

        try {
            // Check for existing RUNNING job
            $existingJob = OnuRemediationJob::where('onu_id', $onu->id)
                ->whereIn('status', ['PENDING', 'VALIDATING', 'EXECUTING', 'WAITING_RECONNECT', 'REDISCOVERING', 'VERIFYING'])
                ->first();

            if ($existingJob) {
                $lock->release();
                return ['status' => 'ONU_REMEDIATION_LOCKED', 'message' => 'A remediation job is already running for this ONU.'];
            }

            // 3. Approval Check
            $status = 'PENDING';
            $approvedBy = null;
            $approvedAt = null;

            if ($plan['requires_approval'] ?? false) {
                $status = 'APPROVAL_REQUIRED';
                if ($isApproval) {
                    $approvePermission = $plan['target_capability'] === 'SUPERADMIN_UNLOCK' || $plan['target_capability'] === 'TELNET_UNLOCK' 
                                            ? 'onu.unlock.approve' 
                                            : 'onu.remediation.approve';

                    if (!auth()->check() || !auth()->user()->can($approvePermission)) {
                        $lock->release();
                        return ['status' => 'FORBIDDEN', 'message' => 'You do not have permission to approve this action.'];
                    }
                    $status = 'APPROVED';
                    $approvedBy = $userId;
                    $approvedAt = now();
                } else {
                    $lock->release();
                    return ['status' => 'APPROVAL_REQUIRED', 'message' => 'This plan requires manual approval by an administrator.'];
                }
            }

            // 4. Create Job Record
            $jobRecord = OnuRemediationJob::create([
                'onu_id' => $onu->id,
                'customer_service_id' => $customerServiceId,
                'type' => 'CAPABILITY_REMEDIATION',
                'status' => $status === 'APPROVED' ? 'PENDING' : $status,
                'target_capability' => $plan['target_capability'],
                'dry_run_results' => $plan,
                'requested_by' => $userId,
                'approved_by' => $approvedBy,
                'approved_at' => $approvedAt,
                'plan_hash' => $expectedHash,
                'expires_at' => $plan['expires_at'] ?? null,
            ]);

            // AUDIT LOG
            ProvisioningAuditService::log(
                action: 'onu.remediation.request',
                resourceType: Onu::class,
                resourceId: $onu->id,
                oldState: ['capability_hash' => $onu->capability->state_hash ?? ''],
                newState: ['plan_id' => $jobRecord->id, 'target' => $plan['target_capability']],
                status: $jobRecord->status
            );

            // 5. Dispatch Queue Job to Dedicated Queue
            if ($jobRecord->status === 'PENDING') {
                ExecuteRemediationJob::dispatch($jobRecord->id)->onQueue('remediation');
            }

            $lock->release();
            return ['status' => 'SUCCESS', 'job_uuid' => $jobRecord->uuid, 'job_status' => $jobRecord->status];
        } catch (\Exception $e) {
            $lock->release();
            Log::error("Failed to start remediation: " . $e->getMessage());
            if (app()->environment('testing')) {
                throw $e;
            }
            return ['status' => 'ERROR', 'message' => 'Failed to initialize remediation job.'];
        }
    }
}
