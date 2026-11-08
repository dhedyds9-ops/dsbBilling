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
     * Login endpoint for GenieACS Mobile Client.
     * Accepts either email or username with password.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = trim($request->input('email'));
        $user = User::where('email', $login)
            ->orWhere('username', $login)
            ->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email/Username atau password yang dimasukkan salah.',
            ], 401);
        }

        if (isset($user->is_active) && !$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun pengguna sedang nonaktif. Hubungi Administrator.',
            ], 403);
        }

        // Generate Sanctum Personal Access Token
        $token = $user->createToken('genieacs-mobile')->plainTextToken;

        // ACS NBI config
        $acsUrl = config('genieacs.base_url', 'http://192.168.150.15:7557');
        $acsUser = config('genieacs.username', 'admin');

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email ?? $user->username,
                'role' => $user->job_function ?? 'TECHNICIAN',
            ],
            'acs_config' => [
                'url' => $acsUrl,
                'username' => $acsUser,
            ],
        ]);
    }

    /**
     * Get active work orders (SPK) for the authenticated technician.
     */
    public function workOrders(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = WorkOrder::with(['customer', 'checklists', 'materialUsages'])
            ->orderBy('id', 'desc');

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
        $workOrder = WorkOrder::findOrFail($id);
        $workOrder->update([
            'status' => 'in_progress',
            'started_at' => now(),
            'assigned_to' => $request->user()->id,
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
        $workOrder = WorkOrder::findOrFail($id);
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
        $request->validate([
            'material_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'material_id' => 'nullable',
            'serial_number' => 'nullable|string|max:255',
        ]);

        $workOrder = WorkOrder::findOrFail($id);

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
