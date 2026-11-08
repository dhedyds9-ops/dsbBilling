<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Models\Workforce\GPSLog;
use App\Models\Workforce\MaterialUsage;
use App\Models\Workforce\WorkOrder;
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

        // 2. Check branch isolation
        if ($user->branch_id && $workOrder->branch_id && $user->branch_id !== $workOrder->branch_id && !$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: SPK ini milik cabang lain.',
            ], 403);
        }

        // 3. Check technician assignment ownership (VULN-06)
        if ($user->isTechnician() && !$user->isSuperAdmin() && !$this->isManagerOrAdmin($user)) {
            if ($workOrder->assigned_to && (int)$workOrder->assigned_to !== (int)$user->id) {
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
        return $user->isSuperAdmin()
            || $user->hasRole(['manager', 'administrator', 'superadmin'])
            || in_array(strtoupper($user->job_function ?? ''), ['MANAGER', 'ADMINISTRATOR']);
    }

    /**
     * Get work orders (SPK) with dynamic filtering (Phase 2A).
     */
    public function workOrders(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = WorkOrder::with(['customer', 'technician', 'approver', 'ticket', 'checklists', 'materialUsages'])
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

        // Apply branch scope
        if ($user->branch_id && !$user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                    ->orWhereNull('branch_id');
            });
        }

        // Apply technician scope (if technician, show assigned to them or unassigned pending)
        if ($user->isTechnician() && !$this->isManagerOrAdmin($user)) {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhereNull('assigned_to');
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

        // If no work orders yet, automatically sync active tickets as work orders
        if ($workOrders->isEmpty() && !$request->filled('status') && !$request->filled('search')) {
            $openTickets = Ticket::withoutGlobalScopes()
                ->whereIn('status', ['open', 'in_progress', 'assigned'])
                ->when($user->isTechnician() && !$this->isManagerOrAdmin($user), function ($q) use ($user) {
                    $q->where(function ($sub) use ($user) {
                        $sub->where('assigned_to', $user->id)
                            ->orWhereNull('assigned_to');
                    });
                })
                ->latest()
                ->limit(15)
                ->get();

            foreach ($openTickets as $t) {
                WorkOrder::firstOrCreate(
                    ['ticket_id' => $t->id],
                    [
                        'uuid' => (string) Str::uuid(),
                        'company_id' => $user->company_id,
                        'branch_id' => $user->branch_id,
                        'type' => $t->category ?? 'troubleshooting',
                        'customer_id' => $t->customer_id ?? $t->user_id,
                        'assigned_to' => $t->assigned_to ?? $user->id,
                        'status' => $t->status === 'in_progress' ? 'in_progress' : 'pending',
                        'title' => $t->title ?? ($t->ticket_number . ' - ' . ($t->subject ?? 'Tiket Perbaikan')),
                        'description' => $t->description ?? '',
                        'priority' => $t->priority ?? 'medium',
                        'scheduled_at' => $t->created_at,
                    ]
                );
            }

            $workOrders = $query->limit(100)->get();
        }

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
            'status' => 'required|string|in:pending,assigned,in_progress,pending_approval,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $targetStatus = strtolower((string)$request->input('status'));
        $notes = $request->input('notes');
        $isManager = $this->isManagerOrAdmin($user);

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
        } elseif ($targetStatus === 'pending_approval') {
            // Waiting for supervisor approval
        } elseif ($targetStatus === 'completed') {
            $updates['completed_at'] = $workOrder->completed_at ?? now();
            $updates['approved_by'] = $user->id;
            $updates['approved_at'] = now();

            if ($workOrder->ticket_id) {
                Ticket::withoutGlobalScopes()
                    ->where('id', $workOrder->ticket_id)
                    ->update(['status' => 'resolved']);
            }
        } elseif ($targetStatus === 'cancelled') {
            if ($workOrder->ticket_id) {
                Ticket::withoutGlobalScopes()
                    ->where('id', $workOrder->ticket_id)
                    ->update(['status' => 'closed']);
            }
        }

        $workOrder->update($updates);

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
     * Format a work order for API response with role-aware permissions.
     */
    protected function formatWorkOrder(WorkOrder $wo, User $user, bool $includeDetails = false): array
    {
        $customer = $wo->customer;
        $customerData = null;

        if ($customer) {
            $customerData = [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->whatsapp ?? $customer->phone ?? null,
                'whatsapp' => $customer->whatsapp ?? $customer->phone ?? null,
                'address' => $customer->address ?? $customer->wilayah ?? null,
                'customer_code' => $customer->customer_code,
                'pppoe_username' => $customer->pppoe_username,
                'onu_sn' => $customer->onu_sn,
            ];
        }

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
            'ticket' => $wo->ticket ? [
                'id' => $wo->ticket->id,
                'ticket_number' => $wo->ticket->ticket_number ?? ('#' . $wo->ticket->id),
                'subject' => $wo->ticket->subject ?? $wo->ticket->title,
                'status' => $wo->ticket->status,
                'priority' => $wo->ticket->priority,
            ] : null,
            'checklists_count' => $wo->checklists->count(),
            'checklists_completed_count' => $wo->checklists->where('completed', true)->count(),
            'materials_count' => $wo->materialUsages->count(),
            'optical_rx_power' => $wo->optical_rx_power ? (float)$wo->optical_rx_power : null,
            'optical_tx_power' => $wo->optical_tx_power ? (float)$wo->optical_tx_power : null,
            'has_signature' => !empty($wo->customer_signature),
            'evidence_photos_count' => is_array($wo->evidence_photos) ? count($wo->evidence_photos) : 0,
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
}
