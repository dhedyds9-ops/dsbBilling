<?php

namespace App\Http\Controllers\Api\ISP;

use App\Http\Controllers\Controller;
use App\Models\ISP\Olt;
use App\Services\ISP\OltPollingService;
use App\Services\Adapters\Provisioning\OltRegistry;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
                    } catch (\Exception $e) { \Illuminate\Support\Facades\Log::error("Swallowed exception caught: " . $e->getMessage());
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

        // Otorisasi: hanya pemegang hak modifikasi OLT yang boleh menjalankan CLI mentah.
        // Aturan otorisasi bersumber dari SSOT OltPolicy::execute() (di-resolve otomatis oleh Gate).
        if (Gate::forUser($request->user())->denies('execute', $olt)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak berhak menjalankan perintah CLI pada OLT ini.',
            ], 403);
        }

        $command = $request->string('command')->toString();

        // Tolak karakter kontrol yang memungkinkan injeksi perintah berantai
        if (preg_match('/[\r\n\t\0]/', $command)) {
            return response()->json([
                'success' => false,
                'message' => 'Perintah mengandung karakter yang tidak diizinkan.',
            ], 422);
        }

        // Whitelist prefix perintah (Super Admin dikecualikan)
        if (!$request->user()?->isSuperAdmin()) {
            $allowed = (array) config('olt-drivers.cli.allowed_command_prefixes', []);
            $normalized = strtolower(ltrim($command));
            $isAllowed = collect($allowed)->contains(
                fn ($prefix) => str_starts_with($normalized, strtolower((string) $prefix))
            );
            if (!$isAllowed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perintah tidak termasuk dalam daftar perintah yang diizinkan.',
                ], 422);
            }
        }

        try {
            $result = $this->registry->forOlt($olt)->executeCommand(
                $command,
                $request->input('mode', 'telnet')
            );

            try {
                \App\Models\AuditLog::create([
                    'auditable_type' => Olt::class,
                    'auditable_id' => $olt->id,
                    'event' => 'olt.execute_command',
                    'new_values' => ['command' => $command, 'mode' => $request->input('mode', 'telnet')],
                    'user_id' => $request->user()?->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Audit log OLT execute-command gagal: ' . $e->getMessage());
            }

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
