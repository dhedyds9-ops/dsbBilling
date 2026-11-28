<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Inventory\InventoryCustody;
use App\Models\ISP\Odp;
use App\Models\Notification\Notification;
use App\Models\User;
use App\Models\Workforce\MaterialUsage;
use App\Models\Workforce\WorkOrder;
use App\Models\Workforce\WorkOrderChecklist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncSecurityException extends \Exception
{
    public string $event;
    public array $details;

    public function __construct(string $message, string $event, array $details = [])
    {
        parent::__construct($message);
        $this->event = $event;
        $this->details = $details;
    }
}

class MobileSyncApiController extends Controller
{
    /**
     * Bootstrap data for offline cache (Work Orders, Custody Inventory, ODPs).
     */
    public function bootstrap(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Work Orders (Assigned to technician or active in branch, strictly company & branch scoped)
        $workOrdersQuery = WorkOrder::query();
        if ($user->company_id && !$user->isSuperAdmin()) {
            $workOrdersQuery->where('company_id', $user->company_id);
        }

        $isTech = $user->hasRole('technician') || (method_exists($user, 'isTechnician') && $user->isTechnician());
        $isManagerOrAdmin = $user->isSuperAdmin() || $user->hasAnyRole(['administrator', 'admin', 'manager', 'superadmin']);

        if ($isTech && !$isManagerOrAdmin) {
            $workOrdersQuery->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
                try {
                    $q->orWhereJsonContains('technician_ids', $user->id);
                } catch (\Throwable) {}
                if (!empty($user->branch_id)) {
                    $q->orWhere(function ($sub) use ($user) {
                        $sub->whereNull('assigned_to')
                            ->where('branch_id', $user->branch_id);
                    });
                }
            });
        } elseif (!empty($user->branch_id) && !$user->isSuperAdmin()) {
            $workOrdersQuery->where('branch_id', $user->branch_id);
        }

        $rawWorkOrders = $workOrdersQuery->with(['customer', 'technician', 'approver', 'ticket', 'checklists', 'materialUsages', 'installation'])
            ->whereNotIn('status', ['cancelled'])
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get();

        $workforceApi = app(MobileWorkforceApiController::class);
        $workOrders = $rawWorkOrders->map(fn($wo) => $workforceApi->formatWorkOrder($wo, $user, true));

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
                        $this->processStatusUpdate($user, $woId, $payload, $clientTime, $uuid);
                        break;

                    case 'checklist_toggle':
                        $this->processChecklistToggle($user, $woId, $payload, $uuid);
                        break;

                    case 'checklist_add':
                        $this->processChecklistAdd($user, $woId, $payload, $uuid);
                        break;

                    case 'measurement':
                        $this->processMeasurement($user, $woId, $payload, $uuid);
                        break;

                    case 'signature':
                        $this->processSignature($user, $woId, $payload, $uuid);
                        break;

                    case 'material_usage':
                        $this->processMaterialUsage($user, $woId, $payload, $uuid);
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

                if ($e instanceof SyncSecurityException) {
                    AuditLog::record(
                        $user,
                        $e->event,
                        $e->getMessage(),
                        $e->details
                    );
                }

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

    // ----------------- Private Action Handlers & Authorization -----------------

    /**
     * Authorize and validate WorkOrder access for offline sync actions.
     * Prevents cross-company, cross-branch, and cross-technician IDOR.
     */
    protected function getAuthorizedWorkOrder(User $user, ?int $woId, string $actionType, string $actionUuid): WorkOrder
    {
        if (!$woId) {
            throw new \InvalidArgumentException("ID SPK wajib diisi untuk aksi {$actionType}.");
        }

        // 1. Check existence with company scope
        $workOrder = WorkOrder::withoutGlobalScopes()->find($woId);

        if (!$workOrder) {
            throw new SyncSecurityException(
                "SPK #{$woId} tidak ditemukan.",
                'offline_sync_rejected',
                [
                    'work_order_id' => $woId,
                    'action_type' => $actionType,
                    'action_uuid' => $actionUuid,
                ]
            );
        }

        // 1a. Cross-company isolation check
        if ($user->company_id && $workOrder->company_id && (int)$user->company_id !== (int)$workOrder->company_id && !$user->isSuperAdmin()) {
            throw new SyncSecurityException(
                "Akses ditolak: SPK #{$woId} adalah milik perusahaan lain.",
                'offline_sync_cross_company_violation',
                [
                    'work_order_id' => $woId,
                    'spk_company_id' => $workOrder->company_id,
                    'user_company_id' => $user->company_id,
                    'action_type' => $actionType,
                    'action_uuid' => $actionUuid,
                ]
            );
        }

        $isManagerOrAdmin = $user->isSuperAdmin() || $user->hasAnyRole(['administrator', 'admin', 'manager', 'superadmin']);
        $isAssigned = ($workOrder->assigned_to && (int)$workOrder->assigned_to === (int)$user->id)
            || (is_array($workOrder->technician_ids) && in_array((int)$user->id, array_map('intval', $workOrder->technician_ids)));

        // 2. Cross-branch isolation check
        if ($user->branch_id && $workOrder->branch_id && (int)$user->branch_id !== (int)$workOrder->branch_id && !$user->isSuperAdmin()) {
            if (!$isAssigned && !$isManagerOrAdmin) {
                throw new SyncSecurityException(
                    "Akses ditolak: SPK #{$woId} adalah milik cabang lain.",
                    'offline_sync_cross_branch_violation',
                    [
                        'work_order_id' => $woId,
                        'spk_branch_id' => $workOrder->branch_id,
                        'user_branch_id' => $user->branch_id,
                        'action_type' => $actionType,
                        'action_uuid' => $actionUuid,
                    ]
                );
            }
        }

        // 3. Technician assignment ownership check
        $isTech = $user->hasRole('technician') || (method_exists($user, 'isTechnician') && $user->isTechnician());
        if ($isTech && !$user->isSuperAdmin() && !$isManagerOrAdmin) {
            if ($workOrder->assigned_to && !$isAssigned) {
                throw new SyncSecurityException(
                    "Akses ditolak: SPK #{$woId} ditugaskan kepada teknisi lain.",
                    'offline_sync_cross_technician_violation',
                    [
                        'work_order_id' => $woId,
                        'spk_assigned_to' => $workOrder->assigned_to,
                        'user_id' => $user->id,
                        'action_type' => $actionType,
                        'action_uuid' => $actionUuid,
                    ]
                );
            }
        }

        // 4. Status validation: Cannot modify cancelled or closed SPK
        $currentStatus = strtolower((string)$workOrder->status);
        if (in_array($currentStatus, ['cancelled', 'closed'])) {
            throw new SyncSecurityException(
                "SPK #{$woId} sudah berstatus '{$currentStatus}' dan tidak dapat dimodifikasi lagi.",
                'offline_sync_invalid_status',
                [
                    'work_order_id' => $woId,
                    'status' => $currentStatus,
                    'action_type' => $actionType,
                    'action_uuid' => $actionUuid,
                ]
            );
        }

        return $workOrder;
    }

    private function processStatusUpdate($user, ?int $woId, array $payload, string $clientTime, string $uuid): void
    {
        $wo = $this->getAuthorizedWorkOrder($user, $woId, 'status_update', $uuid);

        $targetStatus = strtolower(trim((string) ($payload['status'] ?? 'in_progress')));
        $allowedStatuses = ['pending', 'scheduled', 'assigned', 'in_progress', 'completed', 'pending_qc'];

        if (!in_array($targetStatus, $allowedStatuses, true)) {
            throw new \InvalidArgumentException("Status target '{$targetStatus}' tidak valid untuk pembaruan SPK.");
        }

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

    private function processChecklistToggle($user, ?int $woId, array $payload, string $uuid): void
    {
        $chkId = $payload['checklist_id'] ?? null;
        if (!$chkId) throw new \InvalidArgumentException("Checklist ID wajib diisi.");

        $wo = $this->getAuthorizedWorkOrder($user, $woId, 'checklist_toggle', $uuid);

        $chk = WorkOrderChecklist::where('id', $chkId)
            ->where('work_order_id', $wo->id)
            ->first();

        if (!$chk) {
            throw new SyncSecurityException(
                "Checklist #{$chkId} tidak ditemukan pada SPK #{$wo->id}.",
                'offline_sync_checklist_mismatch',
                [
                    'work_order_id' => $wo->id,
                    'checklist_id' => $chkId,
                    'action_uuid' => $uuid,
                ]
            );
        }

        $chk->completed = (bool) ($payload['completed'] ?? true);
        $chk->completed_at = $chk->completed ? now() : null;
        $chk->save();
    }

    private function processChecklistAdd($user, ?int $woId, array $payload, string $uuid): void
    {
        $wo = $this->getAuthorizedWorkOrder($user, $woId, 'checklist_add', $uuid);

        $item = trim((string) ($payload['item'] ?? ''));
        if (empty($item)) throw new \InvalidArgumentException("Item checklist tidak boleh kosong.");

        // Idempotency: Jika checklist dengan action UUID ini sudah ada, pastikan payload identik
        $existing = WorkOrderChecklist::where('uuid', $uuid)->first();
        if ($existing) {
            if ($existing->item !== $item || (int)$existing->work_order_id !== (int)$wo->id) {
                throw new SyncSecurityException(
                    "Konflik Idempotensi: Action UUID '{$uuid}' sudah digunakan untuk item checklist berbeda.",
                    'offline_sync_payload_conflict',
                    [
                        'action_uuid' => $uuid,
                        'existing_item' => $existing->item,
                        'attempted_item' => $item,
                        'work_order_id' => $wo->id,
                    ]
                );
            }
            return;
        }

        WorkOrderChecklist::create([
            'uuid' => $uuid,
            'work_order_id' => $wo->id,
            'item' => $item,
            'completed' => (bool) ($payload['completed'] ?? false),
            'completed_at' => !empty($payload['completed']) ? now() : null,
        ]);
    }

    private function processMeasurement($user, ?int $woId, array $payload, string $uuid): void
    {
        $wo = $this->getAuthorizedWorkOrder($user, $woId, 'measurement', $uuid);

        if (isset($payload['optical_rx_power'])) {
            $wo->optical_rx_power = (float) $payload['optical_rx_power'];
        }
        if (isset($payload['optical_tx_power'])) {
            $wo->optical_tx_power = (float) $payload['optical_tx_power'];
        }
        if (!empty($payload['notes'])) {
            $wo->notes = ($wo->notes ? $wo->notes . "\n" : '') . "[Offline Ukur] " . $payload['notes'];
        }
        $wo->save();
    }

    private function processSignature($user, ?int $woId, array $payload, string $uuid): void
    {
        $wo = $this->getAuthorizedWorkOrder($user, $woId, 'signature', $uuid);

        $sig = $payload['signature'] ?? null;
        if (empty($sig)) throw new \InvalidArgumentException("Tanda tangan digital kosong.");

        $wo->customer_signature = $sig;
        $wo->save();
    }

    private function processMaterialUsage($user, ?int $woId, array $payload, string $uuid): void
    {
        $wo = $this->getAuthorizedWorkOrder($user, $woId, 'material_usage', $uuid);

        // Idempotency: Jika material usage dengan action UUID ini sudah tercatat, periksa konflik payload
        $existing = MaterialUsage::where('uuid', $uuid)->first();
        if ($existing) {
            $name = trim((string) ($payload['material_name'] ?? ''));
            $qty = max(1, (int) ($payload['quantity'] ?? 1));
            if ($existing->work_order_id !== $wo->id || $existing->material_name !== $name || (int) $existing->quantity !== $qty) {
                throw new SyncSecurityException(
                    "Konflik Idempotensi: Action UUID '{$uuid}' sudah digunakan untuk material usage berbeda.",
                    'offline_sync_payload_conflict',
                    [
                        'action_uuid' => $uuid,
                        'existing_material' => $existing->material_name,
                        'existing_quantity' => $existing->quantity,
                        'attempted_material' => $name,
                        'attempted_quantity' => $qty,
                        'work_order_id' => $wo->id,
                    ]
                );
            }
            return;
        }

        $name = trim((string) ($payload['material_name'] ?? ''));
        if (empty($name)) throw new \InvalidArgumentException("Nama material wajib diisi.");

        MaterialUsage::create([
            'uuid' => $uuid,
            'work_order_id' => $wo->id,
            'material_id' => $payload['material_id'] ?? ('MAT-' . substr(md5($name), 0, 8)),
            'material_name' => $name,
            'quantity' => max(1, (int) ($payload['quantity'] ?? 1)),
            'serial_number' => $payload['serial_number'] ?? null,
        ]);
    }
}
