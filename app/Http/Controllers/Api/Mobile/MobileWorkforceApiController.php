<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CRM\Installation;
use App\Models\Support\SupportMaintenance;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Models\Workforce\Attendance;
use App\Models\Workforce\GPSLog;
use App\Models\Workforce\MaterialUsage;
use App\Models\Workforce\WorkOrder;
use App\Services\Support\SlaTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MobileWorkforceApiController extends Controller
{
    /**
     * Backward-compatible login endpoint for GenieACS Mobile Client.
     * Delegates to MobileAuthController logic.
     */
    public function login(Request $request): JsonResponse
    {
        return app(MobileAuthController::class)->login($request);
    }

    /**
     * Check if user is authorized to manage the given Work Order.
     */
    protected function authorizeWorkOrderAccess(User $user, WorkOrder $workOrder): ?JsonResponse
    {
        // 1. Check company isolation
        if ($user->company_id && $workOrder->company_id && $user->company_id !== $workOrder->company_id && !$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: SPK ini milik perusahaan lain.',
            ], 403);
        }

        // 2. Cross-branch and assignment authorization (Directive Section 6)
        $isAssigned = ($workOrder->assigned_to && (int)$workOrder->assigned_to === (int)$user->id)
            || in_array((int)$user->id, (array) ($workOrder->technician_ids ?? []));
        $isManagerOrAdmin = $this->isManagerOrAdmin($user);

        if ($user->branch_id && $workOrder->branch_id && (int)$user->branch_id !== (int)$workOrder->branch_id && !$user->isSuperAdmin()) {
            if (!$isAssigned && !$isManagerOrAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: SPK ini milik cabang lain.',
                ], 403);
            }
        }

        // 3. Check technician assignment ownership
        if ($user->isTechnician() && !$user->isSuperAdmin() && !$isManagerOrAdmin) {
            if ($workOrder->assigned_to && !$isAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: SPK ini ditugaskan kepada teknisi lain.',
                ], 403);
            }
        }

        return null;
    }

    /**
     * Helper to verify if user has manager or administrator privileges.
     */
    protected function isManagerOrAdmin(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Direct check on user roles relation to prevent false positives from staff role fallbacks
        $hasManagerRole = $user->roles->contains(function ($role) {
            return in_array($role->name, ['manager', 'administrator', 'superadmin']);
        });

        if ($hasManagerRole) {
            return true;
        }

        $jobFunc = strtoupper((string)$user->job_function);
        return in_array($jobFunc, ['MANAGER', 'ADMINISTRATOR']);
    }

    /**
     * Get work orders (SPK) with dynamic filtering (Phase 2A).
     */
    public function workOrders(Request $request): JsonResponse
    {
        $user = $request->user();

        // Otomatis sinkronisasi tugas aktif (Tiket Gangguan & SPK Instalasi CRM) ke tabel WorkOrder sebelum query
        $this->syncActiveTasksToWorkOrders($user);

        $query = WorkOrder::with(['customer', 'technician', 'approver', 'ticket', 'checklists', 'materialUsages', 'installation.customer.reseller', 'installation.reseller', 'installation.survey.prospect'])
            ->orderByRaw("CASE 
                WHEN priority = 'urgent' THEN 1 
                WHEN priority = 'high' THEN 2 
                WHEN priority = 'medium' THEN 3 
                ELSE 4 END")
            ->orderBy('id', 'desc');

        // Apply company scope
        if ($user->company_id && !$user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('company_id', $user->company_id)
                    ->orWhereNull('company_id');
            });
        }

        // Apply branch & assignment scope (Cross-branch assignment supported per Directive Section 6)
        $hasTechIdsCol = false;
        try {
            $hasTechIdsCol = in_array('technician_ids', (new WorkOrder)->getConnection()->getSchemaBuilder()->getColumnListing('work_orders'));
        } catch (\Throwable $e) {
            $hasTechIdsCol = false;
        }
        if ($user->isTechnician() && !$this->isManagerOrAdmin($user)) {
            $query->where(function ($q) use ($user, $hasTechIdsCol) {
                // 1. Show work orders assigned to this technician (regardless of branch)
                $q->where(function ($sub) use ($user, $hasTechIdsCol) {
                    $sub->where('assigned_to', $user->id);
                    if ($hasTechIdsCol) {
                        $sub->orWhereJsonContains('technician_ids', $user->id);
                    }
                });
                // 2. Or unassigned work orders in their home branch
                if ($user->branch_id) {
                    $q->orWhere(function ($sub) use ($user) {
                        $sub->where('branch_id', $user->branch_id)
                            ->whereNull('assigned_to');
                    });
                }
            });
        } elseif ($user->branch_id && !$user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                    ->orWhereNull('branch_id');
            });
        }

        // Filter by status (single or comma-separated)
        if ($request->filled('status')) {
            $statuses = array_map('trim', explode(',', (string)$request->input('status')));
            $query->whereIn('status', $statuses);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', strtolower((string)$request->input('priority')));
        }

        // Filter by assigned technician (for manager/admin)
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', (int)$request->input('assigned_to'));
        }

        // Filter by search query
        if ($request->filled('search')) {
            $search = '%' . trim((string)$request->input('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                    ->orWhere('description', 'like', $search)
                    ->orWhere('notes', 'like', $search)
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', $search)
                            ->orWhere('email', 'like', $search)
                            ->orWhere('whatsapp', 'like', $search)
                            ->orWhere('address', 'like', $search)
                            ->orWhere('customer_code', 'like', $search);
                    });
            });
        }

        $workOrders = $query->limit(100)->get();

        $formatted = $workOrders->map(fn(WorkOrder $wo) => $this->formatWorkOrder($wo, $user));

        return response()->json([
            'success' => true,
            'data' => $formatted,
            'summary' => [
                'total' => $workOrders->count(),
                'pending' => $workOrders->where('status', 'pending')->count(),
                'assigned' => $workOrders->where('status', 'assigned')->count(),
                'in_progress' => $workOrders->where('status', 'in_progress')->count(),
                'pending_approval' => $workOrders->where('status', 'pending_approval')->count(),
                'completed' => $workOrders->where('status', 'completed')->count(),
            ],
        ]);
    }

    /**
     * Show single work order detail (Phase 2A).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $workOrder = WorkOrder::with([
            'customer',
            'technician',
            'approver',
            'ticket',
            'checklists',
            'materialUsages',
            'branch'
        ])->findOrFail($id);

        if ($authCheck = $this->authorizeWorkOrderAccess($user, $workOrder)) {
            return $authCheck;
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatWorkOrder($workOrder, $user, true),
        ]);
    }

    /**
     * Assign technician to work order (Phase 2A - Manager/Admin only).
     */
    public function assign(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$this->isManagerOrAdmin($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Administrator atau Manager yang berwenang menugaskan teknisi.',
            ], 403);
        }

        $workOrder = WorkOrder::findOrFail($id);

        if ($authCheck = $this->authorizeWorkOrderAccess($user, $workOrder)) {
            return $authCheck;
        }

        $request->validate([
            'technician_id' => 'required|integer|exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $technician = User::findOrFail($request->input('technician_id'));

        // Validate technician branch isolation
        if ($user->branch_id && $technician->branch_id && $user->branch_id !== $technician->branch_id && !$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Teknisi yang dipilih berada di cabang yang berbeda.',
            ], 422);
        }

        $newStatus = in_array($workOrder->status, ['pending', 'cancelled']) ? 'assigned' : $workOrder->status;

        $workOrder->update([
            'assigned_to' => $technician->id,
            'status' => $newStatus,
            'notes' => $request->input('notes') ?? $workOrder->notes,
        ]);

        if ($workOrder->ticket_id) {
            Ticket::withoutGlobalScopes()
                ->where('id', $workOrder->ticket_id)
                ->update(['assigned_to' => $technician->id, 'status' => 'assigned']);
        }

        return response()->json([
            'success' => true,
            'message' => "SPK #{$id} berhasil ditugaskan kepada teknisi {$technician->name}.",
            'data' => $this->formatWorkOrder($workOrder->fresh(['technician', 'customer']), $user),
        ]);
    }

    /**
     * Update work order operational status (Phase 2A State Machine).
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $workOrder = WorkOrder::findOrFail($id);

        if ($authCheck = $this->authorizeWorkOrderAccess($user, $workOrder)) {
            return $authCheck;
        }

        $request->validate([
            'status' => 'required|string|in:pending,assigned,in_progress,pending_approval,completed,rejected,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $targetStatus = strtolower((string)$request->input('status'));
        $notes = $request->input('notes');
        $isManager = $this->isManagerOrAdmin($user);

        // Jika Manager menyetujui (completed) atau menolak (rejected), gunakan UnifiedQcApprovalService SSOT
        if ($isManager && $targetStatus === 'completed') {
            try {
                $approvedWo = app(\App\Services\Workforce\UnifiedQcApprovalService::class)->approve($workOrder, $user, $notes);
                return response()->json([
                    'success' => true,
                    'message' => "QC SPK #{$id} berhasil disetujui (Completed).",
                    'data' => $this->formatWorkOrder($approvedWo, $user),
                ]);
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
            } catch (\Throwable $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
        }

        if ($isManager && $targetStatus === 'rejected') {
            try {
                $rejectedWo = app(\App\Services\Workforce\UnifiedQcApprovalService::class)->reject($workOrder, $user, $notes ?: 'Pekerjaan perlu direvisi');
                return response()->json([
                    'success' => true,
                    'message' => "QC SPK #{$id} ditolak dan dikembalikan ke teknisi untuk revisi.",
                    'data' => $this->formatWorkOrder($rejectedWo, $user),
                ]);
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
            } catch (\Throwable $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
        }

        // State Machine validation
        if (!$isManager) {
            // Technician rules
            if ($targetStatus === 'completed') {
                // If technician wants completion, check if auto-approval or redirect to pending_approval
                // Technicians can request approval
                $targetStatus = 'pending_approval';
            } elseif ($targetStatus === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Teknisi tidak dapat membatalkan SPK secara sepihak. Hubungi Manager/Admin.',
                ], 403);
            }
        }

        $updates = [
            'status' => $targetStatus,
        ];

        if ($notes) {
            $updates['notes'] = $workOrder->notes ? ($workOrder->notes . "\n[" . now()->toDateTimeString() . " {$user->name}]: " . $notes) : $notes;
        }

        if ($targetStatus === 'in_progress') {
            if (!$workOrder->started_at) {
                $updates['started_at'] = now();
            }
            $updates['assigned_to'] = $workOrder->assigned_to ?? $user->id;

            if ($workOrder->ticket_id) {
                Ticket::withoutGlobalScopes()
                    ->where('id', $workOrder->ticket_id)
                    ->update(['status' => 'in_progress']);
            }

            if ($workOrder->installation_id) {
                \App\Models\CRM\Installation::withoutGlobalScopes()
                    ->where('id', $workOrder->installation_id)
                    ->update([
                        'status' => 'in_progress',
                        'assigned_to' => $workOrder->assigned_to ?? $user->id,
                        'technician_id' => $workOrder->assigned_to ?? $user->id,
                    ]);
            }
        } elseif ($targetStatus === 'pending_approval') {
            // Waiting for supervisor approval - tandai pekerjaan fisik selesai di CRM Installation
            if ($workOrder->installation_id) {
                $instUpdates = [
                    'status' => 'completed',
                    'completed_at' => now(),
                    'completed_by' => $user->id,
                ];
                if (!empty($workOrder->optical_rx_power)) {
                    $instUpdates['optical_power'] = (string) $workOrder->optical_rx_power;
                }
                \App\Models\CRM\Installation::withoutGlobalScopes()
                    ->where('id', $workOrder->installation_id)
                    ->update($instUpdates);
            }
        } elseif ($targetStatus === 'completed') {
            $updates['completed_at'] = $workOrder->completed_at ?? now();
            $updates['approved_by'] = $user->id;
            $updates['approved_at'] = now();

            if ($workOrder->ticket_id) {
                Ticket::withoutGlobalScopes()
                    ->where('id', $workOrder->ticket_id)
                    ->update(['status' => 'resolved']);
            }

            if ($workOrder->installation_id) {
                $inst = \App\Models\CRM\Installation::withoutGlobalScopes()->find($workOrder->installation_id);
                if ($inst) {
                    $inst->update([
                        'status' => 'verified',
                        'completed_at' => $inst->completed_at ?? ($workOrder->completed_at ?? now()),
                        'completed_by' => $inst->completed_by ?: $user->id,
                    ]);
                    if ($inst->survey) {
                        $inst->survey->update(['status' => 'completed']);
                    }
                }
            }
        } elseif ($targetStatus === 'cancelled') {
            if ($workOrder->ticket_id) {
                Ticket::withoutGlobalScopes()
                    ->where('id', $workOrder->ticket_id)
                    ->update(['status' => 'closed']);
            }

            if ($workOrder->installation_id) {
                \App\Models\CRM\Installation::withoutGlobalScopes()
                    ->where('id', $workOrder->installation_id)
                    ->update(['status' => 'cancelled']);
            }
        }

        $workOrder->update($updates);

        // Dispatch Operational Notification Event
        if ($targetStatus === 'in_progress') {
            \App\Events\FieldService\WorkOrderOperationalEvent::dispatch('started', $workOrder, $user->id);
        } elseif ($targetStatus === 'pending_approval') {
            \App\Events\FieldService\WorkOrderOperationalEvent::dispatch('completed', $workOrder, $user->id);
        } elseif ($targetStatus === 'completed') {
            \App\Events\FieldService\WorkOrderOperationalEvent::dispatch('approved', $workOrder, $user->id);
        }

        return response()->json([
            'success' => true,
            'message' => "Status SPK #{$id} berhasil diperbarui menjadi {$targetStatus}.",
            'data' => $this->formatWorkOrder($workOrder->fresh(['technician', 'customer', 'approver']), $user),
        ]);
    }

    /**
     * Start work order (mark in progress) - convenience wrapper.
     */
    public function startWorkOrder(Request $request, int $id): JsonResponse
    {
        $request->merge(['status' => 'in_progress']);
        return $this->updateStatus($request, $id);
    }

    /**
     * Complete work order (mark completed) - convenience wrapper.
     */
    public function completeWorkOrder(Request $request, int $id): JsonResponse
    {
        $request->merge(['status' => 'completed']);
        return $this->updateStatus($request, $id);
    }

    /**
     * Get list of active technicians in the user's branch for assignment.
     */
    public function technicians(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = User::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereHas('roles', function ($rq) {
                    $rq->whereIn('name', ['technician', 'Technician', 'teknisi']);
                })
                ->orWhereRaw('UPPER(COALESCE(job_function, "")) IN (?, ?)', ['TECHNICIAN', 'TEKNISI'])
                ->orWhereRaw('UPPER(COALESCE(job_title, "")) LIKE ?', ['%TEKNISI%'])
                ->orWhereRaw('UPPER(COALESCE(job_title, "")) LIKE ?', ['%TECHNICIAN%']);
            });

        if ($user->branch_id && !$user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                    ->orWhereNull('branch_id');
            });
        }

        $technicians = $query->select(['id', 'name', 'email', 'whatsapp', 'job_title', 'branch_id'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $technicians,
        ]);
    }

    /**
     * Record material usage under the specified work order.
     */
    public function addMaterialUsage(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $workOrder = WorkOrder::findOrFail($id);

        if ($authCheck = $this->authorizeWorkOrderAccess($user, $workOrder)) {
            return $authCheck;
        }

        $request->validate([
            'material_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'material_id' => 'nullable',
            'serial_number' => 'nullable|string|max:255',
        ]);

        $usage = $workOrder->materialUsages()->create([
            'uuid' => (string) Str::uuid(),
            'installation_id' => $workOrder->installation_id ?: null,
            'material_id' => $request->input('material_id', 0),
            'material_name' => $request->input('material_name'),
            'quantity' => (int) $request->input('quantity', 1),
            'serial_number' => $request->input('serial_number'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pemakaian material berhasil dicatat ke SPK #' . $id,
            'data' => $usage,
        ]);
    }

    /**
     * Add a new checklist item to the specified work order (Phase 2C).
     */
    public function addChecklist(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $workOrder = WorkOrder::findOrFail($id);

        if ($authCheck = $this->authorizeWorkOrderAccess($user, $workOrder)) {
            return $authCheck;
        }

        $request->validate([
            'item' => 'required|string|max:255',
        ]);

        $checklist = $workOrder->checklists()->create([
            'uuid' => (string) Str::uuid(),
            'item' => $request->input('item'),
            'completed' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item checklist berhasil ditambahkan.',
            'data' => [
                'id' => $checklist->id,
                'item' => $checklist->item,
                'completed' => false,
            ],
        ]);
    }

    /**
     * Toggle checklist item completion status (Phase 2C).
     */
    public function toggleChecklist(Request $request, int $id, int $checklistId): JsonResponse
    {
        $user = $request->user();
        $workOrder = WorkOrder::findOrFail($id);

        if ($authCheck = $this->authorizeWorkOrderAccess($user, $workOrder)) {
            return $authCheck;
        }

        $checklist = \App\Models\Workforce\WorkOrderChecklist::where('work_order_id', $id)->findOrFail($checklistId);

        $newCompleted = $request->has('completed') 
            ? (bool)$request->input('completed') 
            : !$checklist->completed;

        $checklist->update([
            'completed' => $newCompleted,
            'completed_at' => $newCompleted ? now() : null,
        ]);

        $total = $workOrder->checklists()->count();
        $completed = $workOrder->checklists()->where('completed', true)->count();

        return response()->json([
            'success' => true,
            'message' => 'Status checklist diperbarui.',
            'data' => [
                'id' => $checklist->id,
                'item' => $checklist->item,
                'completed' => (bool)$checklist->completed,
                'completed_at' => $checklist->completed_at?->toIso8601String(),
                'total_checklists' => $total,
                'completed_checklists' => $completed,
            ],
        ]);
    }

    /**
     * Save optical power measurements (RX/TX redaman dBm) (Phase 2C).
     */
    public function saveMeasurements(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $workOrder = WorkOrder::findOrFail($id);

        if ($authCheck = $this->authorizeWorkOrderAccess($user, $workOrder)) {
            return $authCheck;
        }

        $request->validate([
            'optical_rx_power' => 'required|numeric|between:-45,15',
            'optical_tx_power' => 'nullable|numeric|between:-45,15',
            'notes' => 'nullable|string|max:500',
        ]);

        $rx = (float)$request->input('optical_rx_power');
        $tx = $request->filled('optical_tx_power') ? (float)$request->input('optical_tx_power') : null;

        $updates = [
            'optical_rx_power' => $rx,
        ];
        if ($tx !== null) {
            $updates['optical_tx_power'] = $tx;
        }
        if ($request->filled('notes')) {
            $updates['notes'] = $workOrder->notes ? ($workOrder->notes . "\n[Redaman RX {$rx} dBm]: " . $request->input('notes')) : "Redaman RX {$rx} dBm: " . $request->input('notes');
        }

        $workOrder->update($updates);

        $quality = match (true) {
            $rx >= -23.0 && $rx <= -14.0 => 'Optimal (Bagus)',
            $rx >= -26.0 && $rx < -23.0 => 'Waspada (Batas Toleransi)',
            $rx < -26.0 => 'Kritis (Redaman Redup)',
            default => 'Tinggi',
        };

        return response()->json([
            'success' => true,
            'message' => "Hasil ukur redaman tersimpan: {$rx} dBm ({$quality}).",
            'data' => [
                'optical_rx_power' => $rx,
                'optical_tx_power' => $tx,
                'quality' => $quality,
            ],
        ]);
    }

    /**
     * Upload work evidence photo (Phase 2C).
     */
    public function uploadEvidence(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $workOrder = WorkOrder::findOrFail($id);

        if ($authCheck = $this->authorizeWorkOrderAccess($user, $workOrder)) {
            return $authCheck;
        }

        $request->validate([
            'photo' => 'required|file|mimes:jpeg,png,jpg,webp|max:10240',
            'type' => 'nullable|string|in:before,after,odp,ont,modem,speedtest,general',
            'caption' => 'nullable|string|max:255',
        ]);

        $file = $request->file('photo');
        $photoType = $request->input('type', 'general');
        $caption = $request->input('caption', '');

        $filename = 'evidence_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('public/evidence/' . $id, $filename);
        $publicUrl = '/storage/evidence/' . $id . '/' . $filename;

        $photos = $workOrder->evidence_photos ?? [];
        $photos[] = [
            'uuid' => (string) Str::uuid(),
            'type' => $photoType,
            'caption' => $caption,
            'url' => $publicUrl,
            'path' => $path,
            'uploaded_by' => $user->id,
            'uploaded_at' => now()->toIso8601String(),
        ];

        $workOrder->update(['evidence_photos' => $photos]);

        return response()->json([
            'success' => true,
            'message' => 'Foto bukti pekerjaan berhasil diunggah.',
            'data' => [
                'photos' => $photos,
                'count' => count($photos),
            ],
        ]);
    }

    /**
     * Save customer digital signature (Phase 2C).
     */
    public function saveSignature(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $workOrder = WorkOrder::findOrFail($id);

        if ($authCheck = $this->authorizeWorkOrderAccess($user, $workOrder)) {
            return $authCheck;
        }

        $request->validate([
            'signature' => 'required|string',
        ]);

        $workOrder->update([
            'customer_signature' => $request->input('signature'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tanda tangan digital pelanggan berhasil disimpan.',
        ]);
    }

    /**
     * Log GPS coordinates of technician.
     */
    public function logGPS(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = $request->user();

        GPSLog::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'latitude' => (float) $request->input('latitude'),
            'longitude' => (float) $request->input('longitude'),
            'accuracy' => (float) $request->input('accuracy', 0),
            'logged_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lokasi GPS berhasil dicatat.',
        ]);
    }

    /**
     * Sinkronisasi otomatis SPK Instalasi (CRM) dan Tiket Gangguan aktif ke WorkOrder untuk aplikasi mobile.
     */
    protected function syncActiveTasksToWorkOrders(User $user): void
    {
        try {
            $hasInstIdCol = \Illuminate\Support\Facades\Schema::hasColumn('work_orders', 'installation_id');

            $hasInstTechIds = false;
            try {
                $hasInstTechIds = in_array('technician_ids', (new \App\Models\CRM\Installation)->getConnection()->getSchemaBuilder()->getColumnListing('installations'));
            } catch (\Throwable $e) {}

            $hasWoTechIds = false;
            try {
                $hasWoTechIds = in_array('technician_ids', (new WorkOrder)->getConnection()->getSchemaBuilder()->getColumnListing('work_orders'));
            } catch (\Throwable $e) {}

            $hasTicketTechIds = false;
            try {
                $hasTicketTechIds = in_array('technician_ids', (new Ticket)->getConnection()->getSchemaBuilder()->getColumnListing('tickets'));
            } catch (\Throwable $e) {}

            // 1. Sync SPK Instalasi (CRM) Aktif & Riwayat Selesai
            $allInstallations = \App\Models\CRM\Installation::withoutGlobalScopes()
                ->with(['customer' => fn ($q) => $q->withoutGlobalScopes(), 'survey.prospect'])
                ->when($user->isTechnician() && !$this->isManagerOrAdmin($user), function ($q) use ($user, $hasInstTechIds) {
                    $q->where(function ($sub) use ($user, $hasInstTechIds) {
                        $sub->where('technician_id', $user->id)
                            ->orWhere('assigned_to', $user->id)
                            ->orWhereNull('technician_id');
                        if ($hasInstTechIds) {
                            $sub->orWhereJsonContains('technician_ids', $user->id);
                        }
                    });
                })
                ->whereIn('status', ['pending', 'scheduled', 'in_progress', 'assigned', 'completed', 'verified', 'active'])
                ->latest('updated_at')
                ->limit(50)
                ->get();

            foreach ($allInstallations as $inst) {
                $custName = $inst->customer?->name ?? ($inst->survey?->prospect?->name ?? ($inst->customer_name ?: 'Pelanggan Baru'));
                $schedAt = null;
                if ($inst->scheduled_date) {
                    try {
                        $time = $inst->scheduled_time ? substr((string)$inst->scheduled_time, 0, 5) : '09:00';
                        $schedAt = \Carbon\Carbon::parse($inst->scheduled_date . ' ' . $time);
                    } catch (\Throwable) {
                        $schedAt = now();
                    }
                }

                $assignedTechId = $inst->assigned_to ?: ($inst->technician_id ?: null);
                $isCompleted = in_array($inst->status, ['completed', 'verified', 'active']);
                $isProgress = in_array($inst->status, ['in_progress']);

                $status = $isCompleted ? 'completed' : ($isProgress ? 'in_progress' : ($assignedTechId ? 'assigned' : 'pending'));

                $data = [
                    'company_id' => $user->company_id,
                    'branch_id' => $inst->branch_id ?: ($inst->customer?->branch_id ?: $user->branch_id),
                    'reseller_id' => $inst->reseller_id ?: $inst->customer?->reseller_id,
                    'type' => 'installation',
                    'source_type' => 'installation',
                    'source_id' => $inst->id,
                    'customer_id' => $inst->customer_id,
                    'assigned_to' => $assignedTechId,
                    'status' => $status,
                    'title' => 'PSB: ' . $custName,
                    'description' => 'Instalasi Pasang Baru (PSB)' . ($inst->notes ? " - {$inst->notes}" : ''),
                    'notes' => $inst->notes,
                    'priority' => $inst->priority ?? 'medium',
                    'scheduled_at' => $schedAt ?: $inst->created_at,
                    'completed_at' => $isCompleted ? ($inst->completed_at ?: $inst->updated_at) : null,
                ];

                if ($hasWoTechIds && isset($inst->technician_ids)) {
                    $data['technician_ids'] = $inst->technician_ids;
                }

                if ($hasInstIdCol) {
                    WorkOrder::updateOrCreate(
                        ['installation_id' => $inst->id],
                        array_merge(['uuid' => (string) Str::uuid()], $data)
                    );
                } else {
                    $existing = WorkOrder::where('type', 'installation')
                        ->where('title', 'PSB: ' . $custName)
                        ->first();
                    if ($existing) {
                        $existing->update($data);
                    } else {
                        WorkOrder::create(array_merge(['uuid' => (string) Str::uuid()], $data));
                    }
                }
            }

            // 2. Sync Tiket Gangguan Aktif & Selesai (Eksklusif Tiket Perbaikan Lapangan - Tiket PSB sudah dihandle oleh SPK Instalasi)
            $hasTicketInstId = \Illuminate\Support\Facades\Schema::hasColumn('tickets', 'installation_id');

            // Bersihkan duplikat WorkOrder yang berasal dari tiket PSB yang sudah memiliki record SPK Instalasi
            if ($hasTicketInstId) {
                WorkOrder::whereIn('ticket_id', function ($sub) {
                    $sub->select('id')->from('tickets')->whereNotNull('installation_id');
                })->delete();
            }

            $allTickets = Ticket::withoutGlobalScopes()
                ->with(['customer' => fn ($q) => $q->withoutGlobalScopes()])
                ->whereNotIn('category', ['installation', 'psb'])
                ->when($hasTicketInstId, function ($q) {
                    $q->whereNull('installation_id');
                })
                ->when($user->isTechnician() && !$this->isManagerOrAdmin($user), function ($q) use ($user, $hasTicketTechIds) {
                    $q->where(function ($sub) use ($user, $hasTicketTechIds) {
                        $sub->where('assigned_to', $user->id)
                            ->orWhereNull('assigned_to');
                        if ($hasTicketTechIds) {
                            $sub->orWhereJsonContains('technician_ids', $user->id);
                        }
                    });
                })
                ->whereIn('status', ['open', 'in_progress', 'assigned', 'pending_customer', 'resolved', 'closed'])
                ->latest('updated_at')
                ->limit(50)
                ->get();

            foreach ($allTickets as $t) {
                $isTicketCompleted = in_array($t->status, ['resolved', 'closed']);
                $ticketStatus = $isTicketCompleted ? 'completed' : ($t->status === 'in_progress' ? 'in_progress' : ($t->assigned_to ? 'assigned' : 'pending'));

                $ticketPayload = method_exists($t, 'getNotificationPayload') ? $t->getNotificationPayload() : [];
                $custName = $ticketPayload['customer_name'] ?? ($t->customer?->name ?? null);
                if (($custName === 'Pelanggan' || empty($custName)) && !empty($ticketPayload['object_name'])) {
                    $custName = $ticketPayload['object_name'];
                }

                $ticketWoData = [
                    'company_id' => $user->company_id,
                    'branch_id' => $t->branch_id ?: ($t->customer?->branch_id ?: $user->branch_id),
                    'reseller_id' => $t->reseller_id ?? $t->customer?->reseller_id,
                    'type' => $t->category ?? 'troubleshooting',
                    'source_type' => 'ticket',
                    'source_id' => $t->id,
                    'customer_id' => $t->customer_id ?? $t->user_id,
                    'assigned_to' => $t->assigned_to ?? $user->id,
                    'status' => $ticketStatus,
                    'title' => $t->title ?? ($custName ?: ($t->ticket_number . ' - ' . ($t->subject ?? 'Tiket Perbaikan'))),
                    'description' => $t->description ?? '',
                    'notes' => $t->resolution_notes ?? null,
                    'priority' => $t->priority ?? 'medium',
                    'scheduled_at' => $t->created_at,
                    'completed_at' => $isTicketCompleted ? ($t->resolved_at ?: $t->updated_at) : null,
                ];

                if ($hasWoTechIds && isset($t->technician_ids)) {
                    $ticketWoData['technician_ids'] = $t->technician_ids;
                }

                WorkOrder::updateOrCreate(
                    ['ticket_id' => $t->id],
                    array_merge(['uuid' => (string) Str::uuid()], $ticketWoData)
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[WorkforceApi] Gagal sync active tasks ke WorkOrders: ' . $e->getMessage());
        }
    }

    /**
     * Format a work order for API response with role-aware permissions.
     */
    protected function formatWorkOrder(WorkOrder $wo, User $user, bool $includeDetails = false): array
    {
        $customer = $wo->customer ?: $wo->userCustomer;
        if (!$customer && !empty($wo->customer_id)) {
            $customer = \App\Models\CRM\Customer::withoutGlobalScopes()->find($wo->customer_id)
                ?: \App\Models\User::withoutGlobalScopes()->find($wo->customer_id);
        }
        $customerData = null;
        $packageName = null;
        $latitude = null;
        $longitude = null;

        if ($customer) {
            $activeService = null;
            if (method_exists($customer, 'customerServices')) {
                try {
                    $activeService = $customer->customerServices()->with('serviceProfile')->first();
                } catch (\Throwable) {
                    $activeService = null;
                }
            } elseif ($customer instanceof User && $customer->customer) {
                try {
                    $activeService = $customer->customer->customerServices()->with('serviceProfile')->first();
                } catch (\Throwable) {
                    $activeService = null;
                }
            }

            $packageName = $customer->package_name 
                ?? $activeService?->serviceProfile?->name 
                ?? $wo->installation?->package_name 
                ?? $wo->installation?->package?->name 
                ?? null;

            $latitude = $customer->latitude ?? $wo->installation?->latitude;
            $longitude = $customer->longitude ?? $wo->installation?->longitude;

            $customerData = [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->whatsapp ?? $customer->phone ?? null,
                'whatsapp' => $customer->whatsapp ?? $customer->phone ?? null,
                'address' => $customer->address ?? ($customer->wilayah ?? null),
                'customer_code' => $customer->customer_code ?? ($customer->code ?? null),
                'package_name' => $packageName,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'pppoe_username' => $customer->pppoe_username ?? null,
                'onu_sn' => $customer->onu_sn ?? null,
            ];
        } elseif ($wo->relationLoaded('installation') || $wo->installation) {
            $inst = $wo->installation;
            if ($inst) {
                if (!$inst->relationLoaded('customer') || !$inst->relationLoaded('reseller') || !$inst->relationLoaded('survey')) {
                    $inst->loadMissing(['customer.reseller', 'survey.prospect.reseller', 'reseller', 'package']);
                }
                $instCust = $inst->customer;

                // Smart Regex Fallback dari notes jika field di tabel masih kosong (kompatibilitas SPK lama)
                $notesPhone = null;
                $notesAddress = null;
                $notesPackage = null;
                if (!empty($inst->notes)) {
                    if (preg_match('/(?:Telp|Phone|WA|WhatsApp):\s*([0-9+\s\-]+?)(?:\s*\||\n|$)/i', $inst->notes, $m)) {
                        $notesPhone = trim($m[1]);
                    }
                    if (preg_match('/(?:Alamat|Address):\s*([^|\n]+?)(?:\s*\||\n|$)/i', $inst->notes, $m)) {
                        $notesAddress = trim($m[1]);
                    }
                    if (preg_match('/(?:Paket|Package):\s*([^|\n]+?)(?:\s*\||\n|$)/i', $inst->notes, $m)) {
                        $notesPackage = trim($m[1]);
                    }
                }

                $phone = $instCust?->phone 
                    ?: ($instCust?->whatsapp 
                    ?: ($inst->customer_phone 
                    ?: ($inst->survey?->prospect?->phone 
                    ?: ($notesPhone ?? null))));

                $address = $instCust?->address 
                    ?: ($inst->installation_address 
                    ?: ($inst->survey?->address 
                    ?: ($inst->survey?->prospect?->address 
                    ?: ($notesAddress ?? null))));

                $resellerId = $inst->reseller_id 
                    ?? $instCust?->reseller_id 
                    ?? $inst->survey?->prospect?->reseller_id 
                    ?? null;

                $resellerName = $inst->reseller?->name 
                    ?? $instCust?->reseller?->name 
                    ?? $inst->survey?->prospect?->reseller?->name 
                    ?? null;

                $packageName = $inst->package_name 
                    ?? $inst->package?->name 
                    ?? $instCust?->package_name 
                    ?? ($notesPackage ?? null);

                $latitude = $inst->latitude 
                    ?? $instCust?->latitude 
                    ?? $inst->survey?->latitude 
                    ?? null;

                $longitude = $inst->longitude 
                    ?? $instCust?->longitude 
                    ?? $inst->survey?->longitude 
                    ?? null;

                $customerData = [
                    'id' => $instCust?->id ?? null,
                    'name' => $instCust?->name ?? ($inst->survey?->prospect?->name ?? ($inst->customer_name ?: 'Pelanggan Baru')),
                    'email' => $instCust?->email ?? null,
                    'phone' => $phone,
                    'whatsapp' => $phone,
                    'address' => $address,
                    'customer_code' => $instCust?->code ?? null,
                    'reseller_id' => $resellerId,
                    'reseller_name' => $resellerName,
                    'package_name' => $packageName,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'pppoe_username' => null,
                    'onu_sn' => $inst->onu_serial_number ?? null,
                ];
            }
        }

        $ticket = $wo->ticket;
        if (!$ticket && !empty($wo->ticket_id)) {
            $ticket = Ticket::withoutGlobalScopes()->with(['customer.reseller', 'reseller'])->find($wo->ticket_id);
        }
        $ticketPayload = [];
        if ($ticket && method_exists($ticket, 'getNotificationPayload')) {
            try {
                $ticketPayload = $ticket->getNotificationPayload();
            } catch (\Throwable) {}
        }

        $resellerName = $ticket?->reseller?->name 
            ?? $ticket?->customer?->reseller?->name 
            ?? $wo->reseller?->name 
            ?? ($customerData['reseller_name'] ?? null);

        $resellerId = $ticket?->reseller_id 
            ?? $ticket?->customer?->reseller_id 
            ?? $wo->reseller_id 
            ?? ($customerData['reseller_id'] ?? null);

        $ticketCode = $ticket 
            ? (!empty($ticket->uuid) ? ('TKT-' . strtoupper(substr(str_replace('-', '', $ticket->uuid), 0, 6))) : ('TKT-' . $ticket->id)) 
            : null;

        $ticketLat = $ticketPayload['latitude'] ?? ($ticket?->customer?->latitude ?? null);
        $ticketLng = $ticketPayload['longitude'] ?? ($ticket?->customer?->longitude ?? null);

        $latitude = $latitude ?: $ticketLat;
        $longitude = $longitude ?: $ticketLng;

        if (!$customerData && $ticket) {
            $custName = ($ticketPayload['customer_name'] ?? null) && ($ticketPayload['customer_name'] !== 'Pelanggan')
                ? $ticketPayload['customer_name']
                : ($ticketPayload['object_name'] ?? ($ticket->title ?: 'Pelanggan Lapangan'));

            $customerData = [
                'id' => $ticket->customer_id ?? null,
                'name' => $custName,
                'email' => $ticket->customer?->email ?? null,
                'phone' => (($ticketPayload['contact_phone'] ?? null) !== '-') ? ($ticketPayload['contact_phone'] ?? null) : ($ticket->customer?->phone ?? null),
                'whatsapp' => (($ticketPayload['contact_phone'] ?? null) !== '-') ? ($ticketPayload['contact_phone'] ?? null) : ($ticket->customer?->whatsapp ?? null),
                'address' => (($ticketPayload['address'] ?? null) !== '-') ? ($ticketPayload['address'] ?? null) : ($ticket->customer?->address ?? null),
                'customer_code' => $ticketCode,
                'reseller_id' => $resellerId,
                'reseller_name' => $resellerName,
                'package_name' => $ticket->title ?? $packageName,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'pppoe_username' => null,
                'onu_sn' => null,
            ];
        } elseif ($customerData) {
            if (empty($customerData['reseller_name']) && $resellerName) {
                $customerData['reseller_name'] = $resellerName;
                $customerData['reseller_id'] = $resellerId;
            }
            if (empty($customerData['latitude']) && $latitude) {
                $customerData['latitude'] = $latitude;
                $customerData['longitude'] = $longitude;
            }
            if (empty($customerData['customer_code']) && $ticketCode) {
                $customerData['customer_code'] = $ticketCode;
            }
        }

        $photos = is_array($wo->evidence_photos) && !empty($wo->evidence_photos) 
            ? $wo->evidence_photos 
            : ($ticket?->photos ?? []);

        $technician = $wo->technician;
        $isManager = $this->isManagerOrAdmin($user);
        $isAssignedTech = $wo->assigned_to && (int)$wo->assigned_to === (int)$user->id;

        $payload = [
            'id' => $wo->id,
            'uuid' => $wo->uuid ?? (string) Str::uuid(),
            'title' => $wo->title ?? 'SPK #' . $wo->id,
            'description' => $wo->description ?? '',
            'notes' => $wo->notes ?? '',
            'type' => $wo->type ?? 'maintenance',
            'package_name' => $packageName,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'maps_url' => ($latitude && $longitude) ? "https://www.google.com/maps/search/?api=1&query={$latitude},{$longitude}" : null,
            'source_type' => $wo->source_type ?: ($wo->ticket_id ? 'ticket' : ($wo->installation_id ? 'installation' : 'manual')),
            'source_id' => $wo->source_id ?: ($wo->ticket_id ?: $wo->installation_id),
            'status' => $wo->status ?? 'pending',
            'priority' => $wo->priority ?? 'medium',
            'scheduled_at' => $wo->scheduled_at?->toIso8601String(),
            'started_at' => $wo->started_at?->toIso8601String(),
            'completed_at' => $wo->completed_at?->toIso8601String(),
            'approved_at' => $wo->approved_at?->toIso8601String(),
            'customer_id' => $wo->customer_id,
            'customer' => $customerData,
            'assigned_to' => $wo->assigned_to,
            'assigned_technician' => $technician ? [
                'id' => $technician->id,
                'name' => $technician->name,
                'email' => $technician->email,
                'phone' => $technician->whatsapp ?? null,
            ] : null,
            'approver' => $wo->approver ? [
                'id' => $wo->approver->id,
                'name' => $wo->approver->name,
            ] : null,
            'ticket_id' => $wo->ticket_id,
            'ticket_code' => $ticketCode,
            'reseller_id' => $resellerId,
            'reseller_name' => $resellerName,
            'odp_name' => $ticketPayload['object_name'] ?? $ticketPayload['network_details'] ?? null,
            'network_object' => $ticketPayload['object_name'] ?? null,
            'ticket' => $ticket ? [
                'id' => $ticket->id,
                'ticket_code' => $ticketCode,
                'ticket_number' => $ticket->ticket_number ?? $ticketCode ?? ('#' . $ticket->id),
                'subject' => $ticket->subject ?? $ticket->title,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'reseller_id' => $resellerId,
                'reseller_name' => $resellerName,
                'object_name' => $ticketPayload['object_name'] ?? null,
                'photos' => $ticket->photos ?? [],
                'latitude' => $latitude,
                'longitude' => $longitude,
            ] : null,
            'evidence_photos' => $photos,
            'photos' => $photos,
            'checklists_count' => $wo->checklists->count(),
            'checklists_completed_count' => $wo->checklists->where('completed', true)->count(),
            'materials_count' => $wo->materialUsages->count(),
            'optical_rx_power' => $wo->optical_rx_power ? (float)$wo->optical_rx_power : null,
            'optical_tx_power' => $wo->optical_tx_power ? (float)$wo->optical_tx_power : null,
            'has_signature' => !empty($wo->customer_signature),
            'evidence_photos_count' => count($photos),
            'permissions' => [
                'can_start' => ($isAssignedTech || $isManager) && in_array($wo->status, ['pending', 'assigned']),
                'can_request_approval' => ($isAssignedTech || $isManager) && $wo->status === 'in_progress',
                'can_complete' => $isManager || ($isAssignedTech && in_array($wo->status, ['in_progress', 'pending_approval'])),
                'can_approve' => $isManager && in_array($wo->status, ['pending_approval', 'in_progress']),
                'can_assign' => $isManager,
                'can_cancel' => $isManager && !in_array($wo->status, ['completed', 'cancelled']),
            ],
        ];

        if ($includeDetails) {
            $payload['evidence_photos'] = $wo->evidence_photos ?? [];
            $payload['customer_signature'] = $wo->customer_signature;
            $payload['checklists'] = $wo->checklists->map(fn($c) => [
                'id' => $c->id,
                'item' => $c->item,
                'completed' => (bool) $c->completed,
                'completed_at' => $c->completed_at?->toIso8601String(),
            ])->values();

            $payload['material_usages'] = $wo->materialUsages->map(fn($m) => [
                'id' => $m->id,
                'material_name' => $m->material_name,
                'quantity' => (int) $m->quantity,
                'serial_number' => $m->serial_number,
            ])->values();
        }

        return $payload;
    }

    /**
     * Get troubleshooting tickets assigned to technician or open in branch.
     * Mirrors Web Portal Tickets\Index.php.
     *
     * GET /api/mobile/workforce/tickets
     */
    public function tickets(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $ownerId = $user->getEffectiveResellerId() ?? ($user->hasRole('reseller') ? $user->id : null);
            $isManager = $this->isManagerOrAdmin($user);

            $query = \App\Models\Support\Ticket::withoutGlobalScopes()
                ->with(['customer' => fn ($q) => $q->withoutGlobalScopes()]);

            $columns = [];
            try {
                $columns = (new Ticket)->getConnection()->getSchemaBuilder()->getColumnListing('tickets');
            } catch (\Throwable $e) {}

            $hasBranchId = in_array('branch_id', $columns);
            $hasCreatedBy = in_array('created_by', $columns);
            $hasTicketTechIds = in_array('technician_ids', $columns);

            if ($ownerId) {
                $query->where(function ($q) use ($ownerId, $user, $hasCreatedBy) {
                    $q->where('reseller_id', $ownerId);
                    if ($hasCreatedBy) {
                        $q->orWhere('created_by', $user->id);
                    }
                    $q->orWhereHas('customer', function ($cq) use ($ownerId) {
                        $cq->withoutGlobalScopes()->where('reseller_id', $ownerId);
                    });
                });
            } elseif (!$isManager) {
                $query->where(function ($q) use ($user, $hasTicketTechIds, $hasBranchId) {
                    $q->where(function ($sub) use ($user, $hasTicketTechIds) {
                        $sub->where('assigned_to', $user->id);
                        if ($hasTicketTechIds) {
                            $sub->orWhereJsonContains('technician_ids', $user->id);
                        }
                    })
                    ->orWhere(function ($sub) use ($user, $hasBranchId) {
                        $sub->whereNull('assigned_to')
                            ->whereIn('status', ['open', 'pending']);
                        if ($user->branch_id && $hasBranchId) {
                            $sub->where('branch_id', $user->branch_id);
                        }
                    });
                });
            } elseif ($user->branch_id && $hasBranchId && !$user->isSuperAdmin()) {
                $query->where(function ($q) use ($user) {
                    $q->where('branch_id', $user->branch_id)
                      ->orWhereNull('branch_id');
                });
            }

            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }

            if ($search = trim((string) $request->input('q', ''))) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            $tickets = $query->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'title' => $t->title,
                        'description' => $t->description,
                        'status' => $t->status,
                        'priority' => $t->priority,
                        'category' => $t->category,
                        'customer_id' => $t->customer_id,
                        'customer_name' => $t->customer?->name ?? 'Klien Umum',
                        'assigned_to' => $t->assigned_to,
                        'created_at' => $t->created_at?->format('d M Y H:i'),
                    ];
                });

            return response()->json([
                'success' => true,
                'count' => $tickets->count(),
                'data' => $tickets,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("MobileWorkforceApi tickets error: {$e->getMessage()}");
            return response()->json([
                'success' => true,
                'count' => 0,
                'data' => [],
                'message' => 'Data tiket gangguan kosong atau belum tersedia.',
            ]);
        }
    }

    /**
     * Reseller / Staff submit trouble ticket to central NOC.
     * POST /api/mobile/tickets
     */
    public function createTicket(Request $request): JsonResponse
    {
        $user = $request->user();
        $ownerId = $user->getEffectiveResellerId() ?? ($user->hasRole('reseller') ? $user->id : null);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'customer_id' => 'nullable|integer',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
            'category' => 'nullable|string',
        ]);

        $columns = [];
        try {
            $columns = (new Ticket)->getConnection()->getSchemaBuilder()->getColumnListing('tickets');
        } catch (\Throwable $e) {}

        $payload = [
            'uuid' => (string) Str::uuid(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'customer_id' => $validated['customer_id'] ?? null,
            'status' => 'open',
            'priority' => $validated['priority'] ?? 'medium',
            'category' => $validated['category'] ?? 'troubleshoot',
            'reseller_id' => $ownerId,
        ];

        if (in_array('branch_id', $columns) && !empty($user->branch_id)) {
            $payload['branch_id'] = $user->branch_id;
        }
        if (in_array('created_by', $columns)) {
            $payload['created_by'] = $user->id;
        }

        $ticket = \App\Models\Support\Ticket::create($payload);

        return response()->json([
            'success' => true,
            'message' => 'Tiket gangguan berhasil dilaporkan ke pusat NOC.',
            'data' => $ticket,
        ], 201);
    }

    /**
     * Unified Operational Control Center for Field Service (Phase 4.4).
     * GET /api/mobile/workforce/control-center
     */
    public function controlCenter(Request $request, SlaTrackingService $slaService): JsonResponse
    {
        $user = $request->user();

        $filters = [
            'start_date' => now()->startOfDay()->toDateString(),
            'end_date'   => now()->endOfDay()->toDateString(),
            'branch_id'  => $request->input('branch_id') ?: ($user->branch_id ?: null),
            'reseller_id'=> $user->isReseller() ? $user->getEffectiveResellerId() : $request->input('reseller_id'),
        ];

        // 1. Fetch Active Jobs via SSOT SlaTrackingService
        $allActiveJobs = $slaService->getActiveJobsMonitoring($filters, $user);

        $totalActiveJobs = $allActiveJobs->count();
        $pendingQcCount = $allActiveJobs->where('status', 'pending_approval')->count();

        $slaBreachedCount = 0;
        $slaWarningCount = 0;
        $urgentJobs = [];

        foreach ($allActiveJobs as $job) {
            $metrics = $job->metrics ?? [];
            $isBreached = !empty($metrics['is_breached']) || ($metrics['status'] ?? '') === 'breached';
            $isWarning = !empty($metrics['is_warning']) || ($metrics['status'] ?? '') === 'warning';

            if ($isBreached) {
                $slaBreachedCount++;
            } elseif ($isWarning) {
                $slaWarningCount++;
            }

            // Include coordinates if available
            $model = $job->model ?? null;
            $lat = null;
            $lng = null;
            if ($model instanceof WorkOrder) {
                $lat = $model->customer?->latitude ?: ($model->installation?->latitude ?: null);
                $lng = $model->customer?->longitude ?: ($model->installation?->longitude ?: null);
            } elseif ($model instanceof Installation) {
                $lat = $model->latitude ?: ($model->customer?->latitude ?: null);
                $lng = $model->longitude ?: ($model->customer?->longitude ?: null);
            } elseif ($model instanceof Ticket) {
                $lat = $model->customer?->latitude ?: null;
                $lng = $model->customer?->longitude ?: null;
            }

            $urgentJobs[] = [
                'id'              => $job->entity_id,
                'entity_type'     => $job->entity_type,
                'code'            => $job->code,
                'type'            => $job->type,
                'title'           => $job->title,
                'customer_name'   => $job->customer_name,
                'technician_name' => $job->technician_name,
                'status'          => $job->status,
                'priority'        => $job->priority,
                'is_breached'     => $isBreached,
                'is_warning'      => $isWarning,
                'is_qc_pending'   => $job->status === 'pending_approval',
                'remaining_text'  => $metrics['remaining_formatted'] ?? '-',
                'due_at'          => !empty($metrics['due_at']) ? $metrics['due_at']->toIso8601String() : null,
                'latitude'        => $lat ? (float) $lat : null,
                'longitude'       => $lng ? (float) $lng : null,
            ];
        }

        // 2. Fetch Technicians On Duty
        $techQuery = User::withoutGlobalScopes()
            ->where(function ($q) {
                $q->whereHas('roles', fn($rq) => $rq->whereIn('name', ['technician', 'teknisi']))
                  ->orWhere('job_function', 'TECHNICIAN');
            })
            ->where('is_active', true);

        if (!empty($filters['branch_id'])) {
            $techQuery->where('branch_id', (int) $filters['branch_id']);
        }
        $techs = $techQuery->get();
        $techIds = $techs->pluck('id')->toArray();

        $today = now()->toDateString();
        $attendances = Attendance::whereIn('technician_id', $techIds)
            ->whereDate('date', $today)
            ->get()
            ->keyBy('technician_id');

        $recentGpsLogs = GPSLog::whereIn('user_id', $techIds)
            ->where('logged_at', '>=', now()->subHours(12))
            ->orderBy('logged_at', 'desc')
            ->get()
            ->groupBy('user_id')
            ->map(fn($logs) => $logs->first());

        $techsOnDuty = [];
        $onDutyCount = 0;
        foreach ($techs as $tech) {
            $att = $attendances->get($tech->id);
            $gps = $recentGpsLogs->get($tech->id);
            $isWorking = !empty($att?->checked_in_at) && empty($att?->checked_out_at);
            if ($isWorking) {
                $onDutyCount++;
            }

            $lat = $gps?->latitude ?: ($att?->check_in_latitude ?: null);
            $lng = $gps?->longitude ?: ($att?->check_in_longitude ?: null);

            $techsOnDuty[] = [
                'id'            => $tech->id,
                'name'          => $tech->name,
                'phone'         => $tech->whatsapp ?: $tech->phone,
                'branch_name'   => $tech->branch?->name ?? 'Pusat',
                'is_on_duty'    => $isWorking,
                'checked_in_at' => $att?->checked_in_at?->format('H:i'),
                'latitude'      => $lat ? (float) $lat : null,
                'longitude'     => $lng ? (float) $lng : null,
                'last_seen'     => ($gps?->logged_at ?: $att?->checked_in_at)?->diffForHumans(),
            ];
        }

        // 3. Compliance Rate
        $slaReport = $slaService->getSlaReport($filters, $user);

        // 4. Recent Audit Log (Last 10)
        $activities = AuditLog::query()
            ->whereIn('auditable_type', [
                WorkOrder::class,
                Ticket::class,
                Installation::class,
                SupportMaintenance::class,
            ])
            ->with(['user'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($act) => [
                'id'         => $act->id,
                'event'      => $act->event,
                'notes'      => $act->notes,
                'user_name'  => $act->user?->name ?? 'Sistem',
                'created_at' => $act->created_at->diffForHumans(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Operational Control Center data loaded successfully.',
            'data'    => [
                'kpi' => [
                    'total_active_jobs'  => $totalActiveJobs,
                    'techs_on_duty'      => $onDutyCount,
                    'techs_total'        => count($techs),
                    'pending_qc'         => $pendingQcCount,
                    'sla_breached'       => $slaBreachedCount,
                    'sla_warning'        => $slaWarningCount,
                    'compliance_rate'    => $slaReport['compliance_rate'] ?? 100.0,
                    'total_resolved'     => $slaReport['total_jobs'] ?? 0,
                ],
                'active_jobs'        => $urgentJobs,
                'technicians'        => $techsOnDuty,
                'recent_activities'  => $activities,
            ],
        ]);
    }
}
