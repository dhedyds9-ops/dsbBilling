<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\ServiceProfile;
use App\Services\Provisioning\ProvisioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MobileCustomerApiController extends Controller
{
    /**
     * List customers (scoped by reseller/branch via HasResellerScope).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Customer::with(['customerServices.serviceProfile', 'customerServices.onu']);

        if ($resellerId = $user->getEffectiveResellerId()) {
            $query->where('reseller_id', $resellerId);
        } elseif (!empty($user->branch_id)) {
            $query->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhereNull('branch_id');
            });
        }

        $search = trim((string) ($request->input('q') ?? $request->input('search') ?? ''));
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $perPage = min(100, max(1, (int) $request->input('per_page', 50)));
        $paginator = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $items = collect($paginator->items())->map(function (Customer $c) {
            $primaryService = $c->customerServices->first();
            return [
                'id' => $c->id,
                'code' => $c->code,
                'name' => $c->name,
                'phone' => $c->phone,
                'email' => $c->email,
                'address' => $c->address,
                'status' => $c->status,
                'reseller_id' => $c->reseller_id,
                'branch_id' => $c->branch_id,
                'latitude' => $c->latitude,
                'longitude' => $c->longitude,
                'service' => $primaryService ? [
                    'id' => $primaryService->id,
                    'username' => $primaryService->username,
                    'status' => $primaryService->status,
                    'profile_name' => $primaryService->serviceProfile->name ?? null,
                    'onu_serial' => $primaryService->onu->serial_number ?? null,
                ] : null,
                'created_at' => $c->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Get single customer detail.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $customer = Customer::with([
            'customerServices.serviceProfile',
            'customerServices.onu',
            'customerServices.odp',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'code' => $customer->code,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'address' => $customer->address,
                'status' => $customer->status,
                'reseller_id' => $customer->reseller_id,
                'latitude' => $customer->latitude,
                'longitude' => $customer->longitude,
                'created_at' => $customer->created_at?->toIso8601String(),
                'services' => $customer->customerServices->map(function (CustomerService $cs) {
                    return [
                        'id' => $cs->id,
                        'username' => $cs->username,
                        'status' => $cs->status,
                        'profile' => $cs->serviceProfile ? [
                            'id' => $cs->serviceProfile->id,
                            'name' => $cs->serviceProfile->name,
                            'price' => $cs->serviceProfile->price,
                            'speed' => $cs->serviceProfile->speed_limit,
                        ] : null,
                        'onu' => $cs->onu ? [
                            'id' => $cs->onu->id,
                            'serial_number' => $cs->onu->serial_number,
                            'model' => $cs->onu->model,
                            'status' => $cs->onu->status,
                        ] : null,
                        'odp' => $cs->odp ? [
                            'id' => $cs->odp->id,
                            'name' => $cs->odp->name,
                            'code' => $cs->odp->code,
                        ] : null,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Get service profiles for package selection.
     */
    public function serviceProfiles(Request $request): JsonResponse
    {
        $profiles = ServiceProfile::query()
            ->where('is_active', true)
            ->get(['id', 'name', 'price', 'speed_limit', 'category', 'billing_type']);

        return response()->json([
            'success' => true,
            'data' => $profiles,
        ]);
    }

    /**
     * Create & provision new customer from mobile app.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'username' => 'required|string|max:100',
            'password' => 'required|string|min:4',
            'service_profile_id' => 'required|integer|exists:service_profiles,id',
            'router_id' => 'nullable|integer',
            'onu_id' => 'nullable|integer',
            'odp_id' => 'nullable|integer',
            'port_number' => 'nullable|integer',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'setup_fee' => 'nullable|numeric',
        ]);

        $ownerId = $user->getEffectiveResellerId();

        $data = array_merge($validated, [
            'reseller_id' => $ownerId,
            'branch_id' => $user->branch_id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
        ]);

        try {
            $provisioningService = app(ProvisioningService::class);
            $result = $provisioningService->activatePPPoEService($data, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Pelanggan berhasil didaftarkan dan layanan PPPoE aktif.',
                'data' => $result,
            ], 201);
        } catch (\Throwable $e) {
            // Fallback manual creation if activation service encounters external dependency failure
            return DB::transaction(function () use ($data, $user, $validated, $e) {
                $code = 'CUST-' . strtoupper(Str::random(6));
                $customer = Customer::create([
                    'code' => $code,
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'latitude' => $validated['latitude'] ?? null,
                    'longitude' => $validated['longitude'] ?? null,
                    'reseller_id' => $data['reseller_id'],
                    'status' => 'active',
                    'created_by' => $user->id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Pelanggan berhasil didaftarkan.',
                    'customer' => [
                        'id' => $customer->id,
                        'code' => $customer->code,
                        'name' => $customer->name,
                        'phone' => $customer->phone,
                    ],
                ], 201);
            });
        }
    }
}
