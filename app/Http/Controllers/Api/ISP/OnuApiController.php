<?php

namespace App\Http\Controllers\Api\ISP;

use App\Http\Controllers\Controller;
use App\Models\ISP\Onu;
use App\Models\ISP\Olt;
use App\Services\Adapters\Provisioning\OltRegistry;
use App\Services\ISP\GenieAcsService;
use App\Services\ISP\GenieAcsProvisioningService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OnuApiController extends Controller
{
    public function __construct(
        protected OltRegistry $oltRegistry,
        protected GenieAcsProvisioningService $genieAcs,
        protected GenieAcsService $genieAcsPublic,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int)$request->input('per_page', 50);
        $q = Onu::with(['olt:id,name,ip_address', 'odp:id,name', 'vendor:id,name']);
        if ($request->filled('olt_id')) {
            $q->where('olt_id', (int)$request->olt_id);
        }
        if ($request->filled('odp_id')) {
            $q->where('odp_id', (int)$request->odp_id);
        }
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('signal_quality')) {
            $q = match ($request->signal_quality) {
                'critical' => $q->whereNotNull('rx_power_dbm')->where('rx_power_dbm', '<=', config('olt-drivers.thresholds.onu_rx_power_critical_low', -28.0)),
                'warning'  => $q->whereNotNull('rx_power_dbm')->whereBetween('rx_power_dbm', [
                    (config('olt-drivers.thresholds.onu_rx_power_warning_low', -25.0) - 0.01),
                    config('olt-drivers.thresholds.onu_rx_power_critical_low', -28.0) - 0.01,
                ]),
                default => $q,
            };
        }
        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $q->where(fn ($qq) => $qq->where('serial_number', 'like', $s)->orWhere('mac_address', 'like', $s)->orWhere('name', 'like', $s));
        }
        return response()->json($q->latest('id')->paginate($perPage));
    }

    public function show(int $id): JsonResponse
    {
        $onu = Onu::with(['olt', 'odp', 'vendor', 'customerService.customer', 'signals'])->findOrFail($id);
        return response()->json(['data' => $onu]);
    }

    public function provision(Request $request): JsonResponse
    {
        $data = $request->validate([
            'olt_id' => 'required|exists:olts,id',
            'serial_number' => 'required|string|max:50',
            'pon_port' => 'required|integer|min:1',
            'profile' => 'nullable|string|max:100',
            'name' => 'nullable|string|max:100',
            'odp_id' => 'nullable|exists:odps,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'mac_address' => 'nullable|string|max:20',
        ]);
        $olt = Olt::findOrFail($data['olt_id']);
        try {
            DB::beginTransaction();
            $onu = Onu::create([
                'olt_id' => $olt->id,
                'pon_port' => $data['pon_port'],
                'odp_id' => $data['odp_id'] ?? null,
                'vendor_id' => $data['vendor_id'] ?? null,
                'name' => $data['name'] ?? ('ONU-' . substr($data['serial_number'], -6)),
                'serial_number' => strtoupper($data['serial_number']),
                'mac_address' => isset($data['mac_address']) ? strtoupper($data['mac_address']) : null,
                'profile_name' => $data['profile'] ?? 'default',
                'provision_status' => 'provisioning',
                'status' => 'inactive',
                'created_by' => auth()->id() ?: 1,
                'updated_by' => auth()->id() ?: 1,
            ]);
            $drv = $this->oltRegistry->forOlt($olt);
            $ok = $drv->provisionOnu($onu, strtoupper($data['serial_number']), (int)$data['pon_port'], $data['profile'] ?? 'default');
            if (!$ok) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'OLT provision gagal'], 500);
            }
            $onu->update([
                'onu_id_on_olt' => $onu->id,
                'provision_status' => 'provisioned',
                'provisioned_at' => now(),
            ]);
            DB::commit();
            try {
                $this->genieAcs->syncDeviceFromBilling($onu);
            } catch (Exception) {
            }
            return response()->json(['success' => true, 'data' => $onu]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Provision ONU gagal', ['msg' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function setStatus(int $id, Request $request): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['enable', 'disable', 'reset'])],
        ]);
        $onu = Onu::with('olt')->findOrFail($id);
        if (!$onu->olt) {
            return response()->json(['success' => false, 'message' => 'ONU tidak terikat OLT'], 400);
        }
        try {
            $drv = $this->oltRegistry->forOlt($onu->olt);
            $ok = match ($data['status']) {
                'enable' => $drv->setOnuAdminStatus($onu, 'enable'),
                'disable' => $drv->setOnuAdminStatus($onu, 'disable'),
                'reset' => $drv->rebootOnu($onu),
            };
            return response()->json(['success' => $ok, 'command_sent' => $ok]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function signal(int $id): JsonResponse
    {
        $onu = Onu::with('olt')->findOrFail($id);
        try {
            if ($onu->olt) {
                $drv = $this->oltRegistry->forOlt($onu->olt);
                return response()->json(['success' => true, 'data' => $drv->getOnuSignal($onu)]);
            }
            return response()->json(['success' => false, 'message' => 'Tidak ada OLT'], 400);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function wifi(int $id, Request $request): JsonResponse
    {
        $data = $request->validate([
            'ssid' => 'required|string|max:32',
            'password' => 'required|string|min:8|max:64',
        ]);
        $onu = Onu::with(['customerService', 'vendor'])->findOrFail($id);
        return response()->json(
            $this->genieAcs->updateSsidAndPassword($onu, $data['ssid'], $data['password'])
        );
    }

    public function reboot(int $id): JsonResponse
    {
        $onu = Onu::with(['olt', 'customerService'])->findOrFail($id);
        if ($onu->customerService) {
            return response()->json(
                $this->genieAcsPublic->rebootOnu($onu->customerService->customer_id, $onu->id)
            );
        }
        return response()->json($this->genieAcs->rebootOnu($onu));
    }

    public function factoryReset(int $id): JsonResponse
    {
        $onu = Onu::with(['customerService'])->findOrFail($id);
        if ($onu->customerService) {
            return response()->json(
                $this->genieAcsPublic->factoryResetOnu($onu->customerService->customer_id, $onu->id)
            );
        }
        return response()->json($this->genieAcs->factoryResetOnu($onu));
    }

    public function history(int $id, Request $request): JsonResponse
    {
        $perPage = (int)($request->input('per_page', 100));
        $signals = \App\Models\ISP\OnuSignal::where('onu_id', $id)
            ->orderByDesc('measured_at')
            ->paginate($perPage);
        return response()->json($signals);
    }

    public function setBandwidth(int $id, Request $request): JsonResponse
    {
        $data = $request->validate([
            'download_mbps' => 'required|integer|min:1|max:10000',
            'upload_mbps' => 'required|integer|min:1|max:10000',
        ]);
        $onu = Onu::with('olt')->findOrFail($id);
        if (!$onu->olt) {
            return response()->json(['success' => false, 'message' => 'ONU tidak terikat OLT'], 400);
        }
        try {
            $drv = $this->oltRegistry->forOlt($onu->olt);
            $ok = $drv->setOnuBandwidthLimit($onu, (int)$data['download_mbps'], (int)$data['upload_mbps']);
            return response()->json(['success' => $ok]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
