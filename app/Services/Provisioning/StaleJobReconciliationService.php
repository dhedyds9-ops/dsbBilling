<?php

namespace App\Services\Provisioning;

use App\Models\OnuRemediationJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StaleJobReconciliationService
{
    /**
     * Finds jobs that are stuck in executing/waiting state longer than TTL,
     * marks them as UNKNOWN, and clears their locks.
     * Scheduled to run every minute via Laravel Scheduler.
     */
    public function reconcileStaleJobs()
    {
        $timeoutThreshold = now()->subMinutes(12); // Slightly higher than 10m firmware timeout to act as fallback

        $staleJobs = OnuRemediationJob::whereIn('status', ['EXECUTING', 'WAITING_RECONNECT'])
            ->where('updated_at', '<', $timeoutThreshold)
            ->get();

        foreach ($staleJobs as $job) {
            Log::warning("Reconciling stale remediation job {$job->id} (Status: {$job->status}) to UNKNOWN.");
            
            $job->status = 'UNKNOWN';
            $job->error_message = 'Job timed out and orphaned by worker. Manual review required.';
            $job->save();

            // Clear the lock defensively
            Cache::lock("onu-remediation:{$job->onu_id}")->forceRelease();

            ProvisioningAuditService::log(
                action: 'onu.remediation.reconcile',
                resourceType: \App\Models\ISP\Onu::class,
                resourceId: $job->onu_id,
                oldState: ['status' => $job->status],
                newState: ['job_id' => $job->id],
                status: 'UNKNOWN',
                reason: 'Orphaned job reconciled.'
            );
        }
    }
}
