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
        // 1. Check branch isolation
        if ($user->branch_id && $workOrder->branch_id && $user->branch_id !== $workOrder->branch_id && !$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: SPK ini milik cabang lain.',
            ], 403);
        }

        // 2. Check technician assignment ownership
        if ($user->isTechnician() && !$user->isSuperAdmin()) {
            if ($workOrder->assigned_to && $workOrder->assigned_to !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: SPK ini ditugaskan kepada teknisi lain.',
                ], 403);
            }
        }

        return null;
    }

    /**
     * Get active work orders (SPK) for the authenticated technician / staff.
     */
    public function workOrders(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = WorkOrder::with(['customer', 'checklists', 'materialUsages'])
            ->orderBy('id', 'desc');

        // Apply branch scope
        if ($user->branch_id && !$user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                    ->orWhereNull('branch_id');
            });
        }

        // Apply technician scope
        if ($user->job_function === 'TECHNICIAN' || (method_exists($user, 'hasRole') && $user->hasRole('technician'))) {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhereNull('assigned_to');
            });
        }

        $workOrders = $query->limit(50)->get();

        // If no work orders yet, automatically sync active tickets as work orders
        if ($workOrders->isEmpty()) {
            $openTickets = Ticket::withoutGlobalScopes()
                ->whereIn('status', ['open', 'in_progress', 'assigned'])
                ->when($user->job_function === 'TECHNICIAN' || (method_exists($user, 'hasRole') && $user->hasRole('technician')), function ($q) use ($user) {
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
                        'type' => $t->category ?? 'troubleshooting',
                        'customer_id' => $t->customer_id ?? $t->user_id,
                        'assigned_to' => $t->assigned_to ?? $user->id,
                        'branch_id' => $user->branch_id,
                        'status' => $t->status === 'in_progress' ? 'in_progress' : 'pending',
                        'title' => $t->title ?? ($t->ticket_number . ' - ' . ($t->subject ?? 'Tiket Perbaikan')),
                        'description' => $t->description ?? '',
                        'priority' => $t->priority ?? 'medium',
                        'scheduled_at' => $t->created_at,
                    ]
                );
            }

            $workOrders = $query->limit(50)->get();
        }

        $formatted = $workOrders->map(function (WorkOrder $wo) {
            $customer = $wo->customer;
            $customerData = null;

            if ($customer) {
                $customerData = [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->whatsapp ?? $customer->phone ?? null,
                    'address' => $customer->address ?? $customer->wilayah ?? null,
                ];
            }

            return [
                'id' => $wo->id,
                'uuid' => $wo->uuid ?? (string) Str::uuid(),
                'title' => $wo->title ?? 'SPK #' . $wo->id,
                'description' => $wo->description ?? '',
                'type' => $wo->type ?? 'maintenance',
                'status' => $wo->status ?? 'pending',
                'priority' => $wo->priority ?? 'medium',
                'scheduled_at' => $wo->scheduled_at?->toIso8601String(),
                'started_at' => $wo->started_at?->toIso8601String(),
                'completed_at' => $wo->completed_at?->toIso8601String(),
                'customer_id' => $wo->customer_id,
                'customer' => $customerData,
                'checklists' => $wo->checklists ? $wo->checklists->map(fn($c) => [
                    'id' => $c->id,
                    'item' => $c->item,
                    'completed' => (bool) $c->completed,
                    'completed_at' => $c->completed_at?->toIso8601String(),
                ])->values() : [],
                'materialUsages' => $wo->materialUsages ? $wo->materialUsages->map(fn($m) => [
                    'id' => $m->id,
                    'material_name' => $m->material_name,
                    'quantity' => (int) $m->quantity,
                    'serial_number' => $m->serial_number,
                ])->values() : [],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted,
        ]);
    }

    /**
     * Start work order (mark in progress).
     */
    public function startWorkOrder(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $workOrder = WorkOrder::findOrFail($id);

        if ($authCheck = $this->authorizeWorkOrderAccess($user, $workOrder)) {
            return $authCheck;
        }

        $workOrder->update([
            'status' => 'in_progress',
            'started_at' => now(),
            'assigned_to' => $workOrder->assigned_to ?? $user->id,
        ]);

        if ($workOrder->ticket_id) {
            Ticket::withoutGlobalScopes()
                ->where('id', $workOrder->ticket_id)
                ->update(['status' => 'in_progress']);
        }

        return response()->json([
            'success' => true,
            'message' => 'SPK #' . $id . ' berhasil dimulai.',
        ]);
    }

    /**
     * Complete work order (mark completed).
     */
    public function completeWorkOrder(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $workOrder = WorkOrder::findOrFail($id);

        if ($authCheck = $this->authorizeWorkOrderAccess($user, $workOrder)) {
            return $authCheck;
        }

        $workOrder->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        if ($workOrder->ticket_id) {
            Ticket::withoutGlobalScopes()
                ->where('id', $workOrder->ticket_id)
                ->update(['status' => 'resolved']);
        }

        return response()->json([
            'success' => true,
            'message' => 'SPK #' . $id . ' berhasil diselesaikan.',
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
}
