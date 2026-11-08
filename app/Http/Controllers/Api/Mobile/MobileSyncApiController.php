<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryCustody;
use App\Models\ISP\Odp;
use App\Models\Notification\Notification;
use App\Models\Workforce\WorkOrder;
use App\Models\Workforce\WorkOrderChecklist;
use App\Models\Workforce\WorkOrderMaterialUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MobileSyncApiController extends Controller
{
    /**
     * Bootstrap data for offline cache (Work Orders, Custody Inventory, ODPs).
     */
    public function bootstrap(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Work Orders (Assigned to technician or active in branch)
        $workOrdersQuery = WorkOrder::query()->withoutGlobalScopes();
        if ($user->hasRole('technician')) {
            $workOrdersQuery->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere(function ($sub) use ($user) {
                      $sub->whereNull('assigned_to')
                          ->where('branch_id', $user->branch_id);
                  });
            });
        } elseif (!empty($user->branch_id)) {
            $workOrdersQuery->where('branch_id', $user->branch_id);
        }

        $workOrders = $workOrdersQuery->with(['customer', 'checklists', 'materialUsages'])
            ->whereNotIn('status', ['cancelled'])
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get();

        // 2. Custody items held by this technician
        $custodyItems = InventoryCustody::query()->withoutGlobalScopes()
            ->where('holder_id', $user->id)
            ->where('status', 'in_custody')
            ->with(['item'])
            ->get();

        // 3. ODP list for branch
        $odpsQuery = Odp::query()->withoutGlobalScopes();
        if (!empty($user->branch_id)) {
            $odpsQuery->where('branch_id', $user->branch_id);
        }
        $odps = $odpsQuery->orderBy('name')->limit(100)->get();

        // 4. Unread notifications
        $unreadCount = Notification::where('recipient_id', $user->id)
            ->where('status', '!=', 'read')
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Bootstrap data offline berhasil dimuat.',
            'data' => [
                'server_time' => now()->toIso8601String(),
                'unread_notifications' => $unreadCount,
                'work_orders_count' => $workOrders->count(),
                'work_orders' => $workOrders,
                'custody_count' => $custodyItems->count(),
                'custody_items' => $custodyItems,
                'odps_count' => $odps->count(),
                'odps' => $odps,
            ],
        ]);
    }

    /**
     * Process batch of offline queued actions from mobile device.
     */
    public function syncBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'nullable|string|max:100',
            'synced_at' => 'nullable|string',
            'actions' => 'required|array|min:1',
            'actions.*.uuid' => 'required|string',
            'actions.*.action_type' => 'required|string',
            'actions.*.work_order_id' => 'nullable|integer',
            'actions.*.payload' => 'present|array',
            'actions.*.client_timestamp' => 'nullable|string',
        ]);

        $user = $request->user();
        $results = [];

        foreach ($validated['actions'] as $act) {
            $uuid = $act['uuid'];
            $type = strtolower($act['action_type']);
            $woId = $act['work_order_id'] ?? null;
            $payload = $act['payload'];
            $clientTime = $act['client_timestamp'] ?? now()->toIso8601String();

            try {
                DB::beginTransaction();

                switch ($type) {
                    case 'status_update':
                        $this->processStatusUpdate($user, $woId, $payload, $clientTime);
                        break;

                    case 'checklist_toggle':
                        $this->processChecklistToggle($user, $woId, $payload);
                        break;

                    case 'checklist_add':
                        $this->processChecklistAdd($user, $woId, $payload);
                        break;

                    case 'measurement':
                        $this->processMeasurement($user, $woId, $payload);
                        break;

                    case 'signature':
                        $this->processSignature($user, $woId, $payload);
                        break;

                    case 'material_usage':
                        $this->processMaterialUsage($user, $woId, $payload);
                        break;

                    default:
                        throw new \Exception("Tipe aksi '{$type}' tidak didukung.");
                }

                DB::commit();

                $results[] = [
                    'uuid' => $uuid,
                    'action_type' => $type,
                    'status' => 'success',
                    'message' => 'Aksi berhasil diproses.',
                    'processed_at' => now()->toIso8601String(),
                ];
            } catch (\Throwable $e) {
                DB::rollBack();

                $results[] = [
                    'uuid' => $uuid,
                    'action_type' => $type,
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                    'processed_at' => now()->toIso8601String(),
                ];
            }
        }

        $successCount = count(array_filter($results, fn($r) => $r['status'] === 'success'));
        $failedCount = count($results) - $successCount;

        return response()->json([
            'success' => true,
            'message' => "Sinkronisasi batch selesai: {$successCount} berhasil, {$failedCount} gagal.",
            'processed_count' => count($results),
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'results' => $results,
        ]);
    }

    /**
     * Get user notifications list.
     */
    public function notifications(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = Notification::where('recipient_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $unreadCount = Notification::where('recipient_id', $user->id)
            ->where('status', '!=', 'read')
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'data' => $notifications,
        ]);
    }

    /**
     * Mark single notification as read.
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $notif = Notification::where('recipient_id', $user->id)->find($id);

        if (!$notif) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan.',
            ], 404);
        }

        $notif->status = 'read';
        $notif->save();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sudah dibaca.',
        ]);
    }

    /**
     * Mark all notifications as read for current user.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        Notification::where('recipient_id', $user->id)
            ->where('status', '!=', 'read')
            ->update(['status' => 'read']);

        return response()->json([
            'success' => true,
            'message' => 'Seluruh notifikasi ditandai sudah dibaca.',
        ]);
    }

    // ----------------- Private Action Handlers -----------------

    private function processStatusUpdate($user, ?int $woId, array $payload, string $clientTime): void
    {
        if (!$woId) throw new \Exception("ID SPK wajib diisi untuk status update.");
        $wo = WorkOrder::withoutGlobalScopes()->findOrFail($woId);

        $targetStatus = $payload['status'] ?? 'in_progress';
        $notes = $payload['notes'] ?? null;

        $wo->status = $targetStatus;
        if (!empty($notes)) {
            $prev = $wo->notes ? $wo->notes . "\n" : '';
            $wo->notes = $prev . "[Offline Sync {$clientTime}] " . $notes;
        }

        if ($targetStatus === 'in_progress' && !$wo->started_at) {
            $wo->started_at = now();
        } elseif ($targetStatus === 'completed' && !$wo->completed_at) {
            $wo->completed_at = now();
        }

        $wo->save();
    }

    private function processChecklistToggle($user, ?int $woId, array $payload): void
    {
        $chkId = $payload['checklist_id'] ?? null;
        if (!$chkId) throw new \Exception("Checklist ID wajib diisi.");

        $chk = WorkOrderChecklist::findOrFail($chkId);
        $chk->completed = (bool) ($payload['completed'] ?? true);
        $chk->completed_at = $chk->completed ? now() : null;
        $chk->save();
    }

    private function processChecklistAdd($user, ?int $woId, array $payload): void
    {
        if (!$woId) throw new \Exception("ID SPK wajib diisi.");
        $item = trim((string) ($payload['item'] ?? ''));
        if (empty($item)) throw new \Exception("Item checklist tidak boleh kosong.");

        WorkOrderChecklist::create([
            'uuid' => (string) Str::uuid(),
            'work_order_id' => $woId,
            'item' => $item,
            'completed' => (bool) ($payload['completed'] ?? false),
            'completed_at' => !empty($payload['completed']) ? now() : null,
        ]);
    }

    private function processMeasurement($user, ?int $woId, array $payload): void
    {
        if (!$woId) throw new \Exception("ID SPK wajib diisi.");
        $wo = WorkOrder::withoutGlobalScopes()->findOrFail($woId);

        if (isset($payload['optical_rx_power'])) {
            $wo->optical_rx_power = (float) $payload['optical_rx_power'];
        }
        if (isset($payload['optical_tx_power'])) {
            $wo->optical_tx_power = (float) $payload['optical_tx_power'];
        }
        if (!empty($payload['notes'])) {
            $wo->notes = ($wo->notes ? $wo->notes . "\n" : '') . $payload['notes'];
        }
        $wo->save();
    }

    private function processSignature($user, ?int $woId, array $payload): void
    {
        if (!$woId) throw new \Exception("ID SPK wajib diisi.");
        $sig = $payload['signature'] ?? null;
        if (empty($sig)) throw new \Exception("Tanda tangan digital kosong.");

        $wo = WorkOrder::withoutGlobalScopes()->findOrFail($woId);
        $wo->customer_signature = $sig;
        $wo->save();
    }

    private function processMaterialUsage($user, ?int $woId, array $payload): void
    {
        if (!$woId) throw new \Exception("ID SPK wajib diisi.");
        $name = trim((string) ($payload['material_name'] ?? ''));
        if (empty($name)) throw new \Exception("Nama material wajib diisi.");

        WorkOrderMaterialUsage::create([
            'work_order_id' => $woId,
            'material_id' => $payload['material_id'] ?? 'MAT-' . Str::random(6),
            'material_name' => $name,
            'quantity' => (int) ($payload['quantity'] ?? 1),
            'serial_number' => $payload['serial_number'] ?? null,
            'used_by' => $user->id,
            'used_at' => now(),
        ]);
    }
}
