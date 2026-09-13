<?php

namespace App\Http\Controllers\Api\Provisioning;

use App\Http\Controllers\Controller;
use App\Models\OnuRemediationJob;
use App\Services\Provisioning\ProvisioningAuditService;
use Illuminate\Http\Request;

class OnuRemediationReconciliationController extends Controller
{
    /**
     * Manually reconcile a job that ended up in UNKNOWN state.
     */
    public function reconcile(Request $request, string $uuid)
    {
        $request->validate([
            'status' => 'required|in:SUCCESS,FAILED',
            'reason' => 'required|string|min:5',
            'verification_method' => 'required|string',
            'verification_evidence' => 'required|array'
        ]);

        $job = OnuRemediationJob::where('uuid', $uuid)->firstOrFail();

        // 1. Authorization
        $onu = $job->onu;
        if (!auth()->user()->can('onu.remediation.reconcile', $onu)) {
            return response()->json(['status' => 'FORBIDDEN', 'message' => 'Lacking onu.remediation.reconcile permission.'], 403);
        }

        // 2. State Validation
        if ($job->status !== 'UNKNOWN') {
            return response()->json([
                'status' => 'INVALID_STATE', 
                'message' => 'Only UNKNOWN jobs can be manually reconciled.'
            ], 422);
        }

        // 3. Execution
        $oldStatus = $job->status;
        $job->status = $request->status;
        $job->error_message = $request->reason;
        $job->completed_at = now();
        $job->save();

        // 4. Audit
        ProvisioningAuditService::log(
            action: 'onu.remediation.manual_reconcile',
            resourceType: \App\Models\ISP\Onu::class,
            resourceId: $onu->id,
            oldState: ['job_id' => $job->id, 'status' => $oldStatus],
            newState: [
                'status' => $job->status, 
                'verification_method' => $request->verification_method,
                'verification_evidence' => $request->verification_evidence
            ],
            status: $job->status,
            reason: "Manual reconciliation: " . $request->reason
        );

        return response()->json([
            'status' => 'SUCCESS',
            'job_status' => $job->status,
            'message' => 'Job successfully reconciled.'
        ]);
    }
}
