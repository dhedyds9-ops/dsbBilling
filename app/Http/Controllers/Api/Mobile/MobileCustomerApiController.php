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
        $query = Customer::withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])
            ->withoutGuests()
            ->with([
                'customerServices' => function ($q) {
                    $q->withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])
                      ->with(['serviceProfile', 'onu', 'pppoeUser', 'hotspotUser']);
                }
            ]);

        $resellerId = $user->getEffectiveResellerId() ?? ($user->hasRole('reseller') ? $user->id : null);
        if ($resellerId) {
            $query->where(function ($q) use ($resellerId) {
                $q->where('reseller_id', $resellerId)
                  ->orWhere('created_by', $resellerId);
            });
        } elseif (!empty($user->branch_id)) {
            $query->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhereNull('branch_id');
            });
        } elseif (!$user->isSuperAdmin() && !$user->hasRole('administrator')) {
            // Non-admin without branch or reseller context cannot see global cross-branch data
            $query->whereRaw('1 = 0');
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
            if (in_array(strtolower($status), ['isolir', 'isolated', 'suspend', 'suspended'])) {
                $query->whereIn('status', ['suspend', 'suspended', 'isolated']);
            } else {
                $query->where('status', $status);
            }
        }

        $serviceTypeFilter = strtolower((string) ($request->input('service_type') ?? ''));
        if ($serviceTypeFilter === 'pppoe') {
            $query->whereHas('customerServices', function ($cs) {
                $cs->withoutGlobalScopes()
                   ->where(function ($sub) {
                       $sub->whereNotNull('pppoe_user_id')
                           ->orWhereHas('pppoeUser')
                           ->orWhereHas('serviceProfile', function ($sp) {
                               $sp->withoutGlobalScopes()->whereIn(DB::raw('LOWER(service_type)'), ['pppoe', 'broadband', 'combined']);
                           });
                   });
            });
        } elseif ($serviceTypeFilter === 'hotspot') {
            $query->whereHas('customerServices', function ($cs) {
                $cs->withoutGlobalScopes()
                   ->where(function ($sub) {
                       $sub->whereNotNull('hotspot_user_id')
                           ->orWhereHas('hotspotUser')
                           ->orWhereHas('serviceProfile', function ($sp) {
                               $sp->withoutGlobalScopes()->whereIn(DB::raw('LOWER(service_type)'), ['hotspot', 'member_hotspot', 'voucher']);
                           });
                   });
            });
        }

        $perPage = min(100, max(1, (int) $request->input('per_page', 50)));
        $paginator = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $items = collect($paginator->items())->map(function (Customer $c) {
            $primaryService = $c->customerServices->first();
            $serviceUsername = $primaryService?->username
                ?? $primaryService?->pppoeUser?->username
                ?? $primaryService?->hotspotUser?->username;

            $serviceType = 'pppoe';
            if ($primaryService?->hotspotUser) {
                $serviceType = 'hotspot';
            } elseif ($primaryService?->pppoeUser) {
                $serviceType = 'pppoe';
            } elseif ($primaryService) {
                $raw = strtolower($primaryService->serviceProfile?->service_type ?? '');
                $serviceType = in_array($raw, ['hotspot', 'member_hotspot', 'voucher']) ? 'hotspot' : 'pppoe';
            }

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
                    'username' => $serviceUsername,
                    'status' => $primaryService->status,
                    'profile_name' => $primaryService->serviceProfile->name ?? null,
                    'onu_serial' => $primaryService->onu->serial_number ?? null,
                    'service_type' => $serviceType,
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
        $user = $request->user();

        // RBAC: Technicians can only view customer detail if assigned via Work Order or Ticket
        if ($user && method_exists($user, 'isTechnician') && $user->isTechnician() && !$user->isSuperAdmin() && !$user->hasAnyRole(['administrator', 'manager', 'reseller'])) {
            $hasAssignedWo = \App\Models\Workforce\WorkOrder::where('customer_id', $id)
                ->where(function ($wq) use ($user) {
                    $wq->where('assigned_to', $user->id)
                      ->orWhereJsonContains('technician_ids', $user->id);
                })->exists();

            $hasAssignedTicket = \App\Models\Support\Ticket::where('customer_id', $id)
                ->where(function ($tq) use ($user) {
                    $tq->where('assigned_to', $user->id)
                      ->orWhereJsonContains('technician_ids', $user->id);
                })->exists();

            if (!$hasAssignedWo && !$hasAssignedTicket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Teknisi hanya dapat melihat detail pelanggan yang memiliki penugasan SPK/Tiket aktif.',
                ], 403);
            }
        }

        $query = Customer::withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])->with([
            'customerServices' => function ($q) {
                $q->withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])
                  ->with(['serviceProfile', 'onu', 'odp', 'pppoeUser', 'hotspotUser']);
            }
        ]);

        $resellerId = $user->getEffectiveResellerId() ?? ($user->hasRole('reseller') ? $user->id : null);
        if ($resellerId) {
            $query->where(function ($q) use ($resellerId) {
                $q->where('reseller_id', $resellerId)
                  ->orWhere('created_by', $resellerId);
            });
        } elseif (!empty($user->branch_id)) {
            $query->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhereNull('branch_id');
            });
        } elseif (!$user->isSuperAdmin() && !$user->hasRole('administrator')) {
            // Non-admin without branch or reseller context cannot see global cross-branch data
            $query->whereRaw('1 = 0');
        }

        $customer = $query->findOrFail($id);

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
        $user = $request->user();
        $query = ServiceProfile::withoutGlobalScopes()->availableForUser($user);

        if ($user && $user->branch_id && method_exists($user, 'isSuperAdmin') && !$user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhereNull('branch_id');
            });
        }

        $records = $query->where(function ($q) {
            $q->whereNull('status')
              ->orWhere('status', '')
              ->orWhereIn(DB::raw('LOWER(status)'), ['active', 'enabled', '1', 'actived', 'aktif']);
        })->orderBy('base_price', 'asc')->get();

        $data = $records->map(function ($p) {
            $price = (float) ($p->base_price ?? $p->price ?? 0);
            $speed = null;
            if ($p->download_speed && $p->upload_speed) {
                $speed = "{$p->download_speed} / {$p->upload_speed}";
            } elseif ($p->download_speed) {
                $speed = "{$p->download_speed} Down";
            } elseif (!empty($p->speed_limit)) {
                $speed = $p->speed_limit;
            } else {
                $speed = "Up to 50 Mbps";
            }

            return [
                'id' => (int) $p->id,
                'name' => (string) $p->name,
                'price' => $price,
                'is_free' => (bool) $p->isFreePackage(),
                'speed_limit' => $speed,
                'category' => (string) ($p->service_type ?? $p->package_type ?? 'pppoe'),
                'billing_type' => (string) ($p->billing_cycle ?? 'monthly'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get active routers for NAS selection.
     */
    public function routers(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = \App\Models\ISP\Router::withoutGlobalScopes(['branch_isolation'])->active();

        if ($user && $user->branch_id && method_exists($user, 'isSuperAdmin') && !$user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhereNull('branch_id');
            });
        }

        $records = $query->orderBy('name', 'asc')->get();
        $data = $records->map(function ($r) {
            return [
                'id' => (int) $r->id,
                'name' => (string) $r->name,
                'ip_address' => (string) ($r->ip_address ?? ''),
                'status' => (string) ($r->status ?? 'online'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Create & provision new customer from mobile app.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // RBAC: Direct customer registration is restricted to Reseller, Administrator, Manager, or Sales/Registration
        if ($user && method_exists($user, 'isTechnician') && $user->isTechnician() && !$user->isSuperAdmin() && !$user->hasAnyRole(['administrator', 'manager', 'reseller', 'customer_service'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Teknisi tidak memiliki wewenang mendaftarkan master pelanggan baru.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'username' => 'required|string|max:100',
            'password' => 'required|string|min:3',
            'login_method' => 'nullable|string|in:username_and_password,username_only',
            'service_profile_id' => 'required|integer|exists:service_profiles,id',
            'router_id' => 'nullable|integer',
            'onu_id' => 'nullable|integer',
            'onu_serial' => 'nullable|string|max:100',
            'odp_id' => 'nullable|integer',
            'port_number' => 'nullable|integer',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'setup_fee' => 'nullable|numeric',
            'billing_cycle' => 'nullable|string|in:monthly,prepaid,postpaid',
            'static_ip' => 'nullable|string|max:45',
            'mac_address' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'activation_date' => 'nullable|string',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        if (($validated['login_method'] ?? '') === 'username_only') {
            $validated['password'] = $validated['username'];
        }

        if (!empty($validated['onu_serial']) && empty($validated['onu_id'])) {
            $onu = \App\Models\ISP\Onu::withoutGlobalScopes(['branch_isolation'])->where('serial_number', trim($validated['onu_serial']))->first();
            if ($onu) {
                $validated['onu_id'] = $onu->id;
            }
        }

        $ownerId = $user->getEffectiveResellerId() ?? ($user->hasRole('reseller') ? $user->id : null);

        $data = array_merge($validated, [
            'reseller_id' => $ownerId,
            'branch_id' => $user->branch_id,
            'status' => $validated['status'] ?? 'active',
            'billing_cycle' => $validated['billing_cycle'] ?? 'monthly',
            'setup_fee' => $validated['setup_fee'] ?? 0,
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
                $code = \App\Services\CRM\CustomerCodeGenerator::generate();
                $customer = Customer::create([
                    'code' => $code,
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'latitude' => $validated['latitude'] ?? null,
                    'longitude' => $validated['longitude'] ?? null,
                    'reseller_id' => $data['reseller_id'],
                    'branch_id' => $data['branch_id'],
                    'status' => $data['status'],
                    'created_by' => $user->id,
                    'notes' => $validated['notes'] ?? null,
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
