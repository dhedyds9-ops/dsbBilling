<?php

namespace App\Http\Controllers\Api\Provisioning;

use App\Http\Controllers\Controller;
use App\Models\ISP\Onu;
use App\Services\Provisioning\CapabilityDiscoveryService;
use App\Services\Provisioning\RemediationEngineService;
use Illuminate\Http\Request;

class OnuCapabilityController extends Controller
{
    protected CapabilityDiscoveryService $discoveryService;
    protected RemediationEngineService $remediationEngine;

    public function __construct(CapabilityDiscoveryService $discoveryService, RemediationEngineService $remediationEngine)
    {
        $this->discoveryService = $discoveryService;
        $this->remediationEngine = $remediationEngine;
    }

    public function discover(Request $request, $id)
    {
        $onu = Onu::findOrFail($id);
        $force = $request->boolean('force', false);

        $result = $this->discoveryService->discover($onu, $force);

        if ($result['status'] === 'DISCOVERY_FAILED' || $result['status'] === 'DISCOVERY_INCOMPLETE') {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }

    public function getCapabilities($id)
    {
        $onu = Onu::findOrFail($id);
        
        $capability = $onu->capability;
        if (!$capability || empty($capability->capabilities)) {
            return response()->json([
                'status' => 'UNKNOWN',
                'message' => 'Device has not been discovered yet.'
            ], 404);
        }

        return response()->json(
            is_string($capability->capabilities) 
            ? json_decode($capability->capabilities, true) 
            : $capability->capabilities
        );
    }

    public function dryRunRemediation(Request $request, $id)
    {
        $request->validate([
            'target_capability' => 'required|string'
        ]);

        // 1-5. Auth, Role, JobFunction, Permission, Resource Scope
        $onu = Onu::where('id', $id)->firstOrFail();
        if (!auth()->user()->can('onu.remediation.view', $onu)) {
            return response()->json(['status' => 'FORBIDDEN', 'message' => 'Lacking onu.remediation.view permission.'], 403);
        }
        
        $plan = $this->remediationEngine->generateRemediationPlan($onu, strtoupper($request->target_capability));

        if (isset($plan['status']) && $plan['status'] === 'ERROR') {
            return response()->json($plan, 400);
        }

        return response()->json($plan);
    }

    public function remediate(Request $request, $id, \App\Services\Provisioning\RemediationExecutionService $executionService)
    {
        $request->validate([
            'plan_hash' => 'required|string',
            'target_capability' => 'required|string',
            'plan' => 'required|array',
            'customer_service_id' => 'nullable|exists:customer_services,id'
        ]);

        // 1-5. Auth, Role, JobFunction, Permission, Resource Scope
        // Scope terjamin melalui HasBranchScope / query builder, dan Policy
        $onu = Onu::where('id', $id)->firstOrFail();
        
        if (!auth()->user()->can('onu.remediation.execute', $onu)) {
            return response()->json(['status' => 'FORBIDDEN', 'message' => 'Lacking onu.remediation.execute permission.'], 403);
        }

        // 6. Resource State Validation
        if (!$onu->is_online || $onu->state === 'OFFLINE') {
            return response()->json(['status' => 'STATE_INVALID', 'message' => 'ONU is offline or invalid state.'], 422);
        }

        // 7. Approval Requirement (dilakukan di dalam service / engine jika task HIGH_RISK)
        // 8-10. Audit Log, Execution, Verification didelegasikan ke RemediationExecutionService
        $result = $executionService->startRemediation($onu, $request->plan, auth()->id(), false, $request->customer_service_id);

        if (in_array($result['status'], ['PLAN_STALE', 'ONU_REMEDIATION_LOCKED'])) {
            return response()->json($result, 409); // 409 Conflict for Resource State issues
        }

        if ($result['status'] === 'ERROR') {
            return response()->json($result, 400); // 400 Bad Request for generic errors
        }

        return response()->json($result);
    }

    public function getRemediationJob(Request $request, string $uuid)
    {
        if (!auth()->user()->can('onu.remediate')) {
            return response()->json(['status' => 'FORBIDDEN'], 403);
        }

        $job = \App\Models\OnuRemediationJob::with('onu')->where('uuid', $uuid)->firstOrFail();
        
        return response()->json([
            'data' => [
                'uuid' => $job->uuid,
                'status' => $job->status,
                'target_capability' => $job->target_capability,
                'error_message' => $job->error_message,
                'completed_at' => $job->completed_at,
                'attempts' => $job->attempts,
                'onu' => [
                    'id' => $job->onu->id,
                    'name' => $job->onu->name,
                    'is_online' => $job->onu->is_online
                ]
            ]
        ]);
    }
}
