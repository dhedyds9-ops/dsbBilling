<?php

namespace App\Http\Controllers\Api\ISP;

use App\Http\Controllers\Controller;
use App\Models\ISP\Olt;
use App\Services\ISP\OltPollingService;
use App\Services\Adapters\Provisioning\OltRegistry;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OltApiController extends Controller
{
    public function __construct(
        protected OltPollingService $pollingService,
        protected OltRegistry $registry,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int)($request->input('per_page', 25));
        $query = Olt::with(['vendor:id,name', 'pop:id,name'])->latest('id');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->boolean('include_counts')) {
            $query->withCount('onus');
        }
        return response()->json($query->paginate($perPage));
    }

    public function show(int $id): JsonResponse
    {
        $olt = Olt::with(['vendor', 'pop', 'ponPorts'])->findOrFail($id);
        return response()->json(['data' => $olt]);
    }

    public function systemInfo(int $id): JsonResponse
    {
        $olt = Olt::findOrFail($id);
        try {
            $drv = $this->registry->forOlt($olt);
            return response()->json([
                'success' => true,
                'data' => $drv->getSystemInfo(),
                'pon_ports' => $drv->getPonPortsStatus(),
            ]);
        } catch (Exception $e) {
            Log::warning('OLT systemInfo error', ['olt_id' => $id, 'msg' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function ponPorts(int $id): JsonResponse
    {
        $olt = Olt::findOrFail($id);
        try {
            return response()->json([
                'success' => true,
                'data' => $this->registry->forOlt($olt)->getPonPortsStatus(),
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function onuList(int $id, Request $request): JsonResponse
    {
        $olt = Olt::findOrFail($id);
        $ponPort = (int)$request->input('pon_port', 0);
        try {
            $drv = $this->registry->forOlt($olt);
            if ($ponPort > 0) {
                return response()->json(['success' => true, 'data' => $drv->getOnuRxPower($ponPort)]);
            }
            $ports = $drv->getPonPortsStatus();
            $all = [];
            foreach ($ports as $p) {
                if ($p['status'] !== 'up') {
                    continue;
                }
                $idx = (int)($p['port_index'] ?? 0);
                if ($idx > 0) {
                    try {
                        $all[] = $drv->getOnuRxPower($idx);
                    } catch (Exception) {
                    }
                }
            }
            return response()->json(['success' => true, 'data' => array_merge(...$all)]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function discoverUnregistered(int $id): JsonResponse
    {
        $olt = Olt::findOrFail($id);
        try {
            return response()->json([
                'success' => true,
                'data' => $this->registry->forOlt($olt)->discoverUnregisteredOnus(),
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function executeCommand(int $id, Request $request): JsonResponse
    {
        $request->validate(['command' => 'required|string|max:500', 'mode' => 'nullable|in:telnet,ssh']);
        $olt = Olt::findOrFail($id);
        try {
            $result = $this->registry->forOlt($olt)->executeCommand(
                $request->string('command')->toString(),
                $request->input('mode', 'telnet')
            );
            return response()->json(['success' => $result !== false, 'output' => $result]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function poll(int $id): JsonResponse
    {
        $olt = Olt::findOrFail($id);
        return response()->json($this->pollingService->pollOlt($olt));
    }

    public function pollAll(): JsonResponse
    {
        return response()->json($this->pollingService->pollAll());
    }

    public function reboot(int $id): JsonResponse
    {
        $olt = Olt::findOrFail($id);
        try {
            $ok = $this->registry->forOlt($olt)->rebootOlt();
            return response()->json(['success' => $ok]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function saveConfig(int $id): JsonResponse
    {
        $olt = Olt::findOrFail($id);
        try {
            $ok = $this->registry->forOlt($olt)->saveConfig();
            return response()->json(['success' => $ok]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
