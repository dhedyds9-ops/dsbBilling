<?php

namespace App\Http\Controllers\Api\Provisioning;

use App\Http\Controllers\Controller;
use App\Models\Customer\CustomerService;
use App\Services\Provisioning\ServiceProvisioningEngine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerServiceProvisionController extends Controller
{
    protected ServiceProvisioningEngine $provisioningEngine;

    public function __construct(ServiceProvisioningEngine $provisioningEngine)
    {
        $this->provisioningEngine = $provisioningEngine;
    }

    /**
     * Provision the service on the ONU after capability checking.
     */
    public function provision(Request $request, int $id): JsonResponse
    {
        // Require explicit permission to provision services
        if (!auth()->user()->can('customer_service.provision')) {
            return response()->json(['status' => 'FORBIDDEN', 'message' => 'Lacking customer_service.provision permission.'], 403);
        }

        $customerService = CustomerService::with('onu')->findOrFail($id);

        if (!$customerService->onu) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Customer Service does not have an associated ONU.'
            ], 400);
        }

        $result = $this->provisioningEngine->provisionService($customerService);

        $statusCode = match ($result['status'] ?? '') {
            'ERROR' => 500,
            'BLOCKED' => 422,
            'REMEDIATION_REQUIRED' => 409,
            'SUCCESS' => 200,
            default => 400
        };

        return response()->json($result, $statusCode);
    }
}
