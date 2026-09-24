<?php

namespace App\Http\Controllers\Api\ISP;

use App\Http\Controllers\Controller;
use App\Models\CRM\Customer;
use App\Services\ISP\FiberLinkStatusService;
use App\Services\ISP\GenieAcsService;
use App\Services\ISP\GenieAcsProvisioningService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GenieAcsApiController extends Controller
{
    public function __construct(
        protected GenieAcsService $service,
        protected GenieAcsProvisioningService $provisioning,
        protected FiberLinkStatusService $linkStatus,
    ) {
    }

    public function listDevices(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $this->service->listDevices($request->except(['limit']), (int)($request->input('limit', 100))),
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function publishProvisions(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->service->publishDefaultProvisions()]);
    }

    public function provisionsList(): JsonResponse
    {
        try {
            $list = $this->provisioning->publishDefaultProvisions;
            return response()->json(['success' => true, 'data' => array_keys(\App\Services\ISP\GenieAcsProvisioningService::DEFAULT_PROVISIONS)]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deviceInfo(Request $request): JsonResponse
    {
        $request->validate(['device_id' => 'required|string']);
        try {
            return response()->json([
                'success' => true,
                'data' => $this->provisioning->refreshAndSyncSignal(
                    \App\Models\ISP\Onu::where(fn ($q) => $q->where('serial_number', $request->device_id)->orWhere('mac_address', $request->device_id))->firstOrFail()
                ),
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function setParameterValues(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_id' => 'required|string',
            'parameter_values' => 'required|array|min:1',
            'parameter_values.*.name' => 'required|string',
            'parameter_values.*.value' => 'required',
            'parameter_values.*.type' => 'nullable|string',
        ]);
        try {
            $driver = app(\App\Services\Adapters\Monitoring\GenieACSDriver::class);
            $kv = [];
            foreach ($data['parameter_values'] as $pv) {
                $kv[$pv['name']] = $pv['value'];
            }
            return response()->json(['success' => $driver->setParameterValues($data['device_id'], $kv)]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function rebootDevice(Request $request): JsonResponse
    {
        $request->validate(['device_id' => 'required|string']);
        try {
            $driver = app(\App\Services\Adapters\Monitoring\GenieACSDriver::class);
            return response()->json(['success' => $driver->rebootDevice($request->device_id)]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function factoryReset(Request $request): JsonResponse
    {
        $request->validate(['device_id' => 'required|string']);
        try {
            $driver = app(\App\Services\Adapters\Monitoring\GenieACSDriver::class);
            return response()->json(['success' => $driver->factoryResetDevice($request->device_id)]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function tasks(Request $request): JsonResponse
    {
        $request->validate(['device_id' => 'required|string', 'status' => 'nullable|in:pending,stale,completed,fault']);
        try {
            $driver = app(\App\Services\Adapters\Monitoring\GenieACSDriver::class);
            return response()->json([
                'success' => true,
                'data' => $driver->getTasks($request->device_id, $request->input('status')),
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function customerChain(int $customerId): JsonResponse
    {
        Customer::findOrFail($customerId);
        return response()->json($this->linkStatus->getChainForCustomer($customerId));
    }

    public function customerServiceDiagnose(int $customerServiceId): JsonResponse
    {
        return response()->json($this->linkStatus->diagnoseChain($customerServiceId));
    }
}
