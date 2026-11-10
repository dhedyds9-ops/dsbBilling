<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Odp;
use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\ISP\PonPort;
use App\Models\Workforce\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MobileNetworkApiController extends Controller
{
    /**
     * List OLTs accessible to the authenticated staff/reseller.
     * GET /api/mobile/network/olts
     */
    public function olts(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Olt::query()->withoutGlobalScopes();

        $effectiveResellerId = $user->getEffectiveResellerId();
        if ($effectiveResellerId || $user->hasRole('reseller')) {
            $query->forReseller($user);
        } elseif (!empty($user->branch_id)) {
            $query->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhereNull('branch_id');
            });
        }

        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('ip_address', 'LIKE', "%{$search}%")
                  ->orWhere('model', 'LIKE', "%{$search}%");
            });
        }

        $olts = $query->withCount([
            'ponPorts',
            'onus' => function ($q) use ($effectiveResellerId) {
                if ($effectiveResellerId) {
                    \App\Services\Auth\ResellerScopeHelper::applyOnuOwnership($q, $effectiveResellerId, 'onus');
                }
            },
        ])->get()->map(function (Olt $olt) use ($effectiveResellerId) {
            $onusQuery = Onu::withoutGlobalScopes()->where('olt_id', $olt->id);
            if ($effectiveResellerId) {
                \App\Services\Auth\ResellerScopeHelper::applyOnuOwnership($onusQuery, $effectiveResellerId, 'onus');
            }
            $onlineCount = (clone $onusQuery)->where('status', 'online')->count();

            return [
                'id' => $olt->id,
                'name' => $olt->name,
                'code' => $olt->code,
                'ip_address' => $olt->ip_address,
                'brand' => $olt->brand ?? $olt->vendor,
                'model' => $olt->model,
                'status' => $olt->status ?? 'active',
                'pon_ports_count' => $olt->pon_ports_count,
                'total_onus_count' => $olt->onus_count,
                'online_onus_count' => $onlineCount,
                'branch_id' => $olt->branch_id,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar OLT berhasil dimuat.',
            'count' => $olts->count(),
            'data' => $olts,
        ]);
    }

    /**
     * Show detail of single OLT with its PON ports.
     * GET /api/mobile/network/olts/{id}
     */
    public function showOlt(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $query = Olt::withoutGlobalScopes()->where('id', $id);

        $effectiveResellerId = $user->getEffectiveResellerId();
        if ($effectiveResellerId || $user->hasRole('reseller')) {
            $query->forReseller($user);
        } elseif (!empty($user->branch_id)) {
            $query->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhereNull('branch_id');
            });
        }

        $olt = $query->with(['ponPorts'])->first();
        if (!$olt) {
            return response()->json([
                'success' => false,
                'message' => 'OLT tidak ditemukan atau berada di luar cakupan akses Anda.',
            ], 404);
        }

        $ports = $olt->ponPorts->map(function ($port) use ($effectiveResellerId) {
            $onuQuery = Onu::withoutGlobalScopes()->where('pon_port_id', $port->id);
            if ($effectiveResellerId) {
                \App\Services\Auth\ResellerScopeHelper::applyOnuOwnership($onuQuery, $effectiveResellerId, 'onus');
            }
            $totalOnus = (clone $onuQuery)->count();
            $onlineOnus = (clone $onuQuery)->where('status', 'online')->count();

            return [
                'id' => $port->id,
                'port_number' => $port->port_number,
                'port_index' => $port->port_index,
                'name' => $port->name ?? "PON {$port->port_number}",
                'status' => $port->status ?? 'active',
                'total_onus' => $totalOnus,
                'online_onus' => $onlineOnus,
                'max_onus' => $port->max_onus ?? 128,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Detail OLT berhasil dimuat.',
            'data' => [
                'id' => $olt->id,
                'name' => $olt->name,
                'code' => $olt->code,
                'ip_address' => $olt->ip_address,
                'brand' => $olt->brand ?? $olt->vendor,
                'model' => $olt->model,
                'status' => $olt->status ?? 'active',
                'ports' => $ports,
            ],
        ]);
    }

    /**
     * Get PON ports of an OLT.
     * GET /api/mobile/network/olts/{id}/pon-ports
     */
    public function ponPorts(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $query = Olt::withoutGlobalScopes()->where('id', $id);

        $effectiveResellerId = $user->getEffectiveResellerId();
        if ($effectiveResellerId || $user->hasRole('reseller')) {
            $query->forReseller($user);
        } elseif (!empty($user->branch_id)) {
            $query->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhereNull('branch_id');
            });
        }

        $olt = $query->first();
        if (!$olt) {
            return response()->json([
                'success' => false,
                'message' => 'OLT tidak ditemukan atau berada di luar cakupan akses Anda.',
            ], 404);
        }

        $ports = $olt->ponPorts()->orderBy('port_number')->get()->map(function ($port) use ($effectiveResellerId) {
            $onuQuery = Onu::withoutGlobalScopes()->where('pon_port_id', $port->id);
            if ($effectiveResellerId) {
                \App\Services\Auth\ResellerScopeHelper::applyOnuOwnership($onuQuery, $effectiveResellerId, 'onus');
            }
            $totalOnus = (clone $onuQuery)->count();
            $onlineOnus = (clone $onuQuery)->where('status', 'online')->count();

            return [
                'id' => $port->id,
                'olt_id' => $port->olt_id,
                'port_number' => $port->port_number,
                'port_index' => $port->port_index,
                'name' => $port->name ?? "PON {$port->port_number}",
                'status' => $port->status ?? 'active',
                'total_onus' => $totalOnus,
                'online_onus' => $onlineOnus,
                'max_onus' => $port->max_onus ?? 128,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar PON port berhasil dimuat.',
            'count' => $ports->count(),
            'data' => $ports,
        ]);
    }

    /**
     * Poll and synchronize OLT to discover newly connected ONUs.
     * POST /api/mobile/network/olts/{id}/sync
     */
    public function syncOlt(Request $request, int $id): JsonResponse
    {
        $olt = \App\Models\ISP\Olt::withoutGlobalScopes()->find($id);
        if (!$olt) {
            return response()->json([
                'success' => false,
                'message' => 'Perangkat OLT tidak ditemukan.',
            ], 404);
        }

        $lockKey = "rate_limit_olt_sync_{$olt->id}";
        if (\Illuminate\Support\Facades\Cache::has($lockKey)) {
            return response()->json([
                'success' => false,
                'message' => "Sinkronisasi OLT {$olt->name} sedang cooldown. Silakan tunggu 15 detik.",
            ], 429);
        }
        \Illuminate\Support\Facades\Cache::put($lockKey, true, 15);

        try {
            $poller = app(\App\Services\ISP\OltPollingService::class);
            $res = $poller->pollOlt($olt);

            if (isset($res['success']) && !$res['success']) {
                throw new \Exception($res['error'] ?? $res['message'] ?? 'Gagal menghubungi perangkat OLT.');
            }

            $recentOnus = Onu::withoutGlobalScopes()
                ->where('olt_id', $olt->id)
                ->latest('updated_at')
                ->limit(10)
                ->get()
                ->map(fn($o) => $this->formatOnuDevice($o));

            return response()->json([
                'success' => true,
                'message' => "✅ Sinkronisasi OLT {$olt->name} berhasil.",
                'data' => $recentOnus,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "Gagal sinkronisasi OLT {$olt->name}: " . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search and list ONT/ONU devices with customer and ODP information.
     */
    public function devices(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Onu::query()->withoutGlobalScopes();

        // Scope by reseller if user is a reseller, otherwise by branch
        $effectiveResellerId = $user ? $user->getEffectiveResellerId() : null;
        if ($effectiveResellerId) {
            $query->where(function ($q) use ($effectiveResellerId) {
                \App\Services\Auth\ResellerScopeHelper::applyOnuOwnership($q, $effectiveResellerId, 'onus');
            });
        } elseif ($user && !empty($user->branch_id)) {
            $query->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhereNull('branch_id');
            });
        }

        // Multi-candidate search query (prefix matching HWTC, ZTEG, customer, etc.)
        if ($search = trim((string) $request->input('q', ''))) {
            $upper = strtoupper($search);
            $candidates = [$search, $upper];
            if (str_starts_with($upper, 'HWTC')) {
                $candidates[] = '48575443' . substr($upper, 4);
            } elseif (str_starts_with($upper, '48575443')) {
                $candidates[] = 'HWTC' . substr($upper, 8);
            } elseif (str_starts_with($upper, 'ZTEG')) {
                $candidates[] = '5A544547' . substr($upper, 4);
            } elseif (str_starts_with($upper, '5A544547')) {
                $candidates[] = 'ZTEG' . substr($upper, 8);
            }

            $query->where(function ($q) use ($search, $candidates) {
                foreach ($candidates as $cand) {
                    $q->orWhere('serial_number', 'LIKE', "%{$cand}%")
                      ->orWhere('mac_address', 'LIKE', "%{$cand}%");
                }
                $q->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('model', 'LIKE', "%{$search}%")
                  ->orWhereHas('customerService', function ($csq) use ($search) {
                      $csq->withoutGlobalScopes()->whereHas('customer', function ($cq) use ($search) {
                          $cq->withoutGlobalScopes()->where('name', 'LIKE', "%{$search}%")
                             ->orWhere('phone', 'LIKE', "%{$search}%")
                             ->orWhere('code', 'LIKE', "%{$search}%");
                      })
                      ->orWhereHas('pppoeUser', function ($pu) use ($search) {
                          $pu->withoutGlobalScopes()->where('username', 'LIKE', "%{$search}%");
                      });
                  });
            });
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // ODP filter
        if ($odpId = $request->input('odp_id')) {
            $query->where('odp_id', $odpId);
        }

        // OLT filter
        if ($oltId = $request->input('olt_id')) {
            $query->where('olt_id', $oltId);
        }

        // PON Port filter
        if ($ponPortId = $request->input('pon_port_id')) {
            $query->where('pon_port_id', $ponPortId);
        }

        $devices = $query->with(['odp', 'olt', 'ponPort'])
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get()
            ->map(function (Onu $onu) {
                return $this->formatOnuDevice($onu);
            });

        // Fallback: search ACSDevice table if empty and search query provided
        if ($devices->isEmpty() && !empty($search)) {
            $acsMatches = \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                ->with(['olt', 'customerService.customer'])
                ->where(function ($aq) use ($search, $candidates) {
                    foreach ($candidates as $cand) {
                        $aq->orWhere('serial_number', 'LIKE', "%{$cand}%")
                           ->orWhere('mac_address', 'LIKE', "%{$cand}%");
                    }
                    $aq->orWhere('model', 'LIKE', "%{$search}%");
                })
                ->limit(20)
                ->get()
                ->map(function ($ad) {
                    $rx = $ad->signal !== null ? (float) $ad->signal : null;
                    return [
                        'id' => $ad->id,
                        'serial_number' => $ad->serial_number,
                        'mac_address' => $ad->mac_address,
                        'model' => $ad->model ?: 'ONT',
                        'name' => $ad->model,
                        'code' => $ad->serial_number,
                        'status' => $ad->status ?: 'offline',
                        'provision_status' => 'provisioned',
                        'rx_power_dbm' => $rx,
                        'tx_power_dbm' => null,
                        'optical_quality' => $rx !== null ? ($rx >= -23.0 ? 'Optimal' : ($rx < -26.0 ? 'Loss/Kritis' : 'Toleransi')) : 'Belum Diukur',
                        'wifi_ssid' => $ad->wifi_ssid ?? 'dsBilling_WiFi',
                        'odp_id' => null,
                        'odp_code' => null,
                        'odp_name' => null,
                        'olt_id' => $ad->olt_id,
                        'olt_name' => $ad->olt?->name ?? 'OLT',
                        'olt_code' => $ad->olt?->code,
                        'pon_port_id' => null,
                        'pon_port_number' => null,
                        'customer_name' => $ad->customerService?->customer?->name,
                        'customer_code' => $ad->customerService?->customer?->code,
                        'last_seen_at' => $ad->last_inform ? $ad->last_inform->format('Y-m-d H:i:s') : null,
                    ];
                });

            if ($acsMatches->isNotEmpty()) {
                $devices = $acsMatches;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar perangkat berhasil dimuat.',
            'count' => $devices->count(),
            'data' => $devices,
        ]);
    }

    /**
     * Get device detail by Serial Number or ID.
     */
    public function showDevice(string $identifier): JsonResponse
    {
        $onu = Onu::withoutGlobalScopes()
            ->where(function ($q) use ($identifier) {
                $q->where('serial_number', $identifier)
                  ->orWhere('mac_address', $identifier)
                  ->orWhere('id', is_numeric($identifier) ? (int) $identifier : 0);
            })
            ->with(['odp'])
            ->first();

        if (!$onu) {
            return response()->json([
                'success' => false,
                'message' => "Perangkat dengan identifier '{$identifier}' tidak ditemukan.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatOnuDevice($onu, true),
        ]);
    }

    /**
     * Search and list ODPs (Optical Distribution Points).
     * Supports GPS radius proximity calculation when lat/lng are supplied.
     */
    public function odps(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Odp::query()->withoutGlobalScopes();

        if (!empty($user->branch_id)) {
            $query->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhereNull('branch_id');
            });
        }

        // Search text
        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%");
            });
        }

        // Optional status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        $odps = $query->orderBy('name')
            ->limit(60)
            ->get()
            ->map(function (Odp $odp) use ($lat, $lng) {
                $totalPorts = (int) ($odp->port_count ?: 8);
                $usedPorts = (int) ($odp->used_port_count ?: 0);
                $available = max(0, $totalPorts - $usedPorts);

                $distanceMeters = null;
                if (is_numeric($lat) && is_numeric($lng) && !empty($odp->latitude) && !empty($odp->longitude)) {
                    $distanceMeters = $this->calculateDistanceMeters(
                        (float) $lat, (float) $lng,
                        (float) $odp->latitude, (float) $odp->longitude
                    );
                }

                return [
                    'id' => $odp->id,
                    'code' => $odp->code,
                    'name' => $odp->name ?: $odp->code,
                    'address' => $odp->address ?: 'Lokasi ODP',
                    'latitude' => $odp->latitude ? (float) $odp->latitude : null,
                    'longitude' => $odp->longitude ? (float) $odp->longitude : null,
                    'port_count' => $totalPorts,
                    'used_port_count' => $usedPorts,
                    'available_ports' => $available,
                    'status' => $odp->status ?: 'active',
                    'distance_meters' => $distanceMeters !== null ? round($distanceMeters) : null,
                    'is_full' => $available <= 0,
                ];
            });

        // If coordinates provided, sort by nearest distance
        if (is_numeric($lat) && is_numeric($lng)) {
            $odps = $odps->sortBy(function ($item) {
                return $item['distance_meters'] ?? PHP_INT_MAX;
            })->values();
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar ODP berhasil dimuat.',
            'count' => $odps->count(),
            'data' => $odps,
        ]);
    }

    /**
     * Pair and provision an ONT/ONU to a Work Order / Customer.
     */
    public function provisionOnt(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'serial_number' => 'required|string|min:4|max:50',
            'mac_address' => 'nullable|string|max:30',
            'model' => 'nullable|string|max:50',
            'work_order_id' => 'nullable|integer',
            'customer_id' => 'nullable|integer',
            'odp_id' => 'nullable|integer',
            'odp_port' => 'nullable|integer|min:1|max:64',
            'wifi_ssid' => 'nullable|string|max:32',
            'wifi_password' => 'nullable|string|min:8|max:64',
        ]);

        $user = $request->user();
        $sn = strtoupper(trim($validated['serial_number']));

        return DB::transaction(function () use ($validated, $user, $sn) {
            // 1. Find or create ONU record
            $onu = Onu::withoutGlobalScopes()->where('serial_number', $sn)->first();

            if (!$onu) {
                $onu = new Onu();
                $onu->serial_number = $sn;
                $onu->code = 'ONU-' . substr(preg_replace('/[^A-Z0-9]/', '', $sn), -6);
                $onu->name = ($validated['model'] ?? 'ONT') . ' ' . substr($sn, -6);
            }

            // Update attributes
            if (!empty($validated['mac_address'])) {
                $onu->mac_address = strtoupper(trim($validated['mac_address']));
            }
            if (!empty($validated['model'])) {
                $onu->model = trim($validated['model']);
            }
            if (!empty($validated['odp_id'])) {
                $onu->odp_id = (int) $validated['odp_id'];
            }
            if (!empty($validated['wifi_ssid'])) {
                $onu->wifi_ssid = trim($validated['wifi_ssid']);
            }
            if (!empty($validated['wifi_password'])) {
                $onu->wifi_password = trim($validated['wifi_password']);
            }

            $onu->branch_id = $user->branch_id ?? $onu->branch_id;
            $onu->created_by = $onu->created_by ?? $user->id;
            $onu->status = 'active';
            $onu->provision_status = 'provisioned';
            $onu->provisioned_at = now();
            $onu->updated_by = $user->id;
            $onu->save();

            // 2. If ODP assigned, update ODP used ports
            $odpInfo = null;
            if (!empty($validated['odp_id'])) {
                $odp = Odp::withoutGlobalScopes()->find($validated['odp_id']);
                if ($odp) {
                    $activeOnusCount = Onu::withoutGlobalScopes()->where('odp_id', $odp->id)->count();
                    $odp->used_port_count = max($odp->used_port_count, $activeOnusCount);
                    $odp->save();

                    $odpInfo = [
                        'id' => $odp->id,
                        'code' => $odp->code,
                        'name' => $odp->name,
                        'port' => $validated['odp_port'] ?? null,
                    ];
                }
            }

            // 3. If Work Order is referenced
            $workOrderInfo = null;
            $customerId = $validated['customer_id'] ?? null;

            if (!empty($validated['work_order_id'])) {
                $wo = WorkOrder::withoutGlobalScopes()->find($validated['work_order_id']);
                if ($wo) {
                    if (!$customerId && $wo->customer_id) {
                        $customerId = $wo->customer_id;
                    }

                    $prevNotes = $wo->notes ? $wo->notes . "\n" : '';
                    $odpDesc = $odpInfo ? " di ODP {$odpInfo['code']}" : '';
                    $wo->notes = $prevNotes . "ONT [{$sn}]{$odpDesc} dipasangkan oleh {$user->name} pada " . now()->format('d/m/Y H:i');

                    // If order was assigned/pending, advance to in_progress
                    if (in_array($wo->status, ['assigned', 'pending'])) {
                        $wo->status = 'in_progress';
                        if (!$wo->started_at) {
                            $wo->started_at = now();
                        }
                    }
                    $wo->save();

                    $workOrderInfo = [
                        'id' => $wo->id,
                        'title' => $wo->title,
                        'status' => $wo->status,
                    ];
                }
            }

            // 4. Link to CustomerService if customer is identified
            $customerInfo = null;
            if ($customerId) {
                $customer = Customer::withoutGlobalScopes()->find($customerId) 
                         ?? \App\Models\User::withoutGlobalScopes()->find($customerId);

                if ($customer) {
                    $customerInfo = [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'code' => $customer->code ?? $customer->customer_code ?? null,
                    ];

                    // Find or associate CustomerService
                    $cs = CustomerService::withoutGlobalScopes()
                        ->where('customer_id', $customerId)
                        ->first();

                    if ($cs) {
                        $cs->onu_id = $onu->id;
                        $cs->tr069_status = 'provisioned';
                        $cs->save();
                    } else {
                        // Attempt to link to contract or existing service if present
                        try {
                            $newCs = new CustomerService();
                            $newCs->customer_id = $customerId;
                            $newCs->uuid = (string) Str::uuid();
                            $newCs->status = 'active';
                            $newCs->branch_id = $user->branch_id;
                            $newCs->onu_id = $onu->id;
                            $newCs->tr069_status = 'provisioned';
                            $newCs->username = 'onu_' . strtolower(substr($sn, -6));
                            $newCs->password = bcrypt(Str::random(12));
                            $newCs->contract_id = \DB::table('contracts')->where('customer_id', $customerId)->value('id') ?? 1;
                            $newCs->service_id = \DB::table('services')->value('id') ?? 1;
                            $newCs->save();
                        } catch (\Throwable $e) {
                            \Log::info("Provision ONT: CustomerService link deferred: " . $e->getMessage());
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "ONT SN {$sn} berhasil dipasangkan dan diprovisioning!",
                'data' => [
                    'onu' => $this->formatOnuDevice($onu),
                    'odp' => $odpInfo,
                    'customer' => $customerInfo,
                    'work_order' => $workOrderInfo,
                ],
            ]);
        });
    }

    /**
     * Get live device diagnostics (Optical RX/TX power, online status, GenieACS link).
     */
    public function diagnostics(Request $request, string $serialNumber): JsonResponse
    {
        $sn = strtoupper(trim($serialNumber));
        $onu = Onu::withoutGlobalScopes()
            ->where('serial_number', $sn)
            ->orWhere('mac_address', $sn)
            ->with(['odp', 'olt', 'ponPort'])
            ->first();

        // Fallback: search ACSDevice if not registered in Onu
        if (!$onu) {
            $ad = \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                ->where('serial_number', 'like', "%{$sn}%")
                ->orWhere('mac_address', 'like', "%{$sn}%")
                ->with(['olt', 'customerService.customer'])
                ->first();

            if ($ad) {
                $rx = $ad->signal !== null ? (float) $ad->signal : null;
                $quality = 'Belum Diukur';
                if ($rx !== null) {
                    if ($rx >= -23.0 && $rx <= -14.0) {
                        $quality = 'Optimal';
                    } elseif ($rx < -26.0 || $rx > -8.0) {
                        $quality = 'Loss/Kritis';
                    } else {
                        $quality = 'Waspada/Toleransi';
                    }
                }
                return response()->json([
                    'success' => true,
                    'data' => [
                        'serial_number' => $ad->serial_number,
                        'mac_address' => $ad->mac_address,
                        'model' => $ad->model ?: 'ONT Terminal',
                        'is_online' => strtolower((string) $ad->status) === 'online',
                        'status' => $ad->status ?: 'unknown',
                        'rx_power_dbm' => $rx,
                        'tx_power_dbm' => null,
                        'optical_quality' => $quality,
                        'last_seen_at' => $ad->last_inform ? $ad->last_inform->format('Y-m-d H:i:s') : null,
                        'wifi_ssid' => $ad->wifi_ssid ?? 'dsBilling_WiFi',
                        'olt' => $ad->olt ? [
                            'id' => $ad->olt->id,
                            'name' => $ad->olt->name,
                            'code' => $ad->olt->code,
                        ] : null,
                        'pon_port' => null,
                        'odp' => null,
                        'customer' => $ad->customerService?->customer ? [
                            'name' => $ad->customerService->customer->name,
                            'code' => $ad->customerService->customer->code,
                            'phone' => $ad->customerService->customer->phone,
                            'address' => $ad->customerService->customer->address,
                        ] : null,
                        'pppoe_username' => $ad->customerService?->username,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "Perangkat ONT dengan Serial Number '{$sn}' tidak ditemukan di OLT maupun ACS.",
            ], 404);
        }

        $user = $request->user();
        if ($user) {
            $effectiveResellerId = $user->getEffectiveResellerId() ?: ($user->hasRole('reseller') ? $user->id : null);
            if ($effectiveResellerId) {
                $allowed = Onu::withoutGlobalScopes()
                    ->where('id', $onu->id)
                    ->where(function ($q) use ($effectiveResellerId) {
                        \App\Services\Auth\ResellerScopeHelper::applyOnuOwnership($q, $effectiveResellerId, 'onus');
                    })
                    ->exists();

                if (!$allowed) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Perangkat ini berada di luar cakupan izin akun Reseller Anda.',
                    ], 403);
                }
            }
        }

        $rx = $onu->rx_power_dbm !== null ? (float) $onu->rx_power_dbm : null;
        $tx = $onu->tx_power_dbm !== null ? (float) $onu->tx_power_dbm : null;

        $quality = 'Belum Diukur';
        if ($rx !== null) {
            if ($rx >= -23.0 && $rx <= -14.0) {
                $quality = 'Optimal';
            } elseif ($rx < -26.0 || $rx > -8.0) {
                $quality = 'Loss/Kritis';
            } else {
                $quality = 'Waspada/Toleransi';
            }
        }

        $isOnline = in_array(strtolower((string) $onu->status), ['online', 'active']) 
            && ($onu->last_seen_at ? now()->diffInMinutes($onu->last_seen_at) < 30 : false);

        // Fetch customer info connected to this ONU
        $cs = CustomerService::withoutGlobalScopes()
            ->where('onu_id', $onu->id)
            ->with(['customer', 'serviceProfile', 'pppoeUser'])
            ->first();

        try {
            \App\Models\AuditLog::create([
                'user_id' => $user?->id,
                'action' => 'onu_diagnostics',
                'description' => "Diagnostik perangkat ONT {$sn} via mobile API.",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'data' => [
                'serial_number' => $onu->serial_number,
                'mac_address' => $onu->mac_address,
                'model' => $onu->model ?: 'ONT Terminal',
                'is_online' => $isOnline,
                'status' => $onu->status ?: 'unknown',
                'rx_power_dbm' => $rx,
                'tx_power_dbm' => $tx,
                'optical_quality' => $quality,
                'last_seen_at' => $onu->last_seen_at ? $onu->last_seen_at->format('Y-m-d H:i:s') : null,
                'wifi_ssid' => $onu->wifi_ssid,
                'olt' => $onu->olt ? [
                    'id' => $onu->olt->id,
                    'name' => $onu->olt->name,
                    'code' => $onu->olt->code,
                ] : null,
                'pon_port' => $onu->ponPort ? [
                    'id' => $onu->ponPort->id,
                    'port_number' => $onu->ponPort->port_number,
                ] : null,
                'odp' => $onu->odp ? [
                    'id' => $onu->odp->id,
                    'code' => $onu->odp->code,
                    'name' => $onu->odp->name,
                ] : null,
                'customer' => $cs?->customer ? [
                    'name' => $cs->customer->name,
                    'code' => $cs->customer->code,
                    'phone' => $cs->customer->phone,
                    'address' => $cs->customer->address,
                ] : null,
                'pppoe_username' => $cs?->username,
                'service_profile_name' => $cs?->serviceProfile?->name,
            ],
        ]);
    }

    /**
     * Helper to format ONU model into mobile JSON response.
     */
    private function formatOnuDevice(Onu $onu, bool $withDetails = false): array
    {
        $rx = $onu->rx_power_dbm !== null ? (float) $onu->rx_power_dbm : null;
        $tx = $onu->tx_power_dbm !== null ? (float) $onu->tx_power_dbm : null;

        $qual = 'Belum Diukur';
        if ($rx !== null) {
            if ($rx >= -23.0 && $rx <= -14.0) {
                $qual = 'Optimal';
            } elseif ($rx < -26.0 || $rx > -8.0) {
                $qual = 'Loss/Kritis';
            } else {
                $qual = 'Toleransi';
            }
        }

        // Try finding connected customer
        $cs = CustomerService::withoutGlobalScopes()
            ->where('onu_id', $onu->id)
            ->first();

        $customerName = null;
        $customerCode = null;
        if ($cs && $cs->customer_id) {
            $cust = Customer::withoutGlobalScopes()->find($cs->customer_id)
                 ?? \App\Models\User::withoutGlobalScopes()->find($cs->customer_id);
            if ($cust) {
                $customerName = $cust->name;
                $customerCode = $cust->code ?? $cust->customer_code ?? null;
            }
        }

        $res = [
            'id' => $onu->id,
            'serial_number' => $onu->serial_number,
            'mac_address' => $onu->mac_address,
            'model' => $onu->model ?: 'ONT',
            'name' => $onu->name,
            'code' => $onu->code,
            'status' => $onu->status ?: 'offline',
            'provision_status' => $onu->provision_status ?: 'unprovisioned',
            'rx_power_dbm' => $rx,
            'tx_power_dbm' => $tx,
            'optical_quality' => $qual,
            'wifi_ssid' => $onu->wifi_ssid,
            'odp_id' => $onu->odp_id,
            'odp_code' => $onu->odp?->code,
            'odp_name' => $onu->odp?->name,
            'olt_id' => $onu->olt_id,
            'olt_name' => $onu->olt?->name,
            'olt_code' => $onu->olt?->code,
            'pon_port_id' => $onu->pon_port_id,
            'pon_port_number' => $onu->ponPort?->port_number,
            'customer_name' => $customerName,
            'customer_code' => $customerCode,
            'last_seen_at' => $onu->last_seen_at ? $onu->last_seen_at->format('Y-m-d H:i:s') : null,
        ];

        return $res;
    }

    /**
     * Haversine formula to calculate distance in meters between two GPS coordinates.
     */
    private function calculateDistanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * One-click ONT Swap for replacing damaged/defective customer ONT in field.
     * Mirrors Web Portal Swap\Index.php.
     *
     * POST /api/mobile/network/devices/swap-ont
     */
    public function swapOnt(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'old_serial_number' => 'required|string',
            'new_serial_number' => 'required|string',
            'new_mac_address' => 'nullable|string',
            'new_model' => 'nullable|string',
            'work_order_id' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user();
        $oldSn = strtoupper(trim($validated['old_serial_number']));
        $newSn = strtoupper(trim($validated['new_serial_number']));

        return DB::transaction(function () use ($validated, $user, $oldSn, $newSn) {
            $oldOnu = Onu::withoutGlobalScopes()->where('serial_number', $oldSn)->firstOrFail();

            // Find or create new ONU
            $newOnu = Onu::withoutGlobalScopes()->where('serial_number', $newSn)->first();
            if (!$newOnu) {
                $newOnu = new Onu();
                $newOnu->serial_number = $newSn;
                $newOnu->code = 'ONU-' . substr(preg_replace('/[^A-Z0-9]/', '', $newSn), -6);
                $newOnu->name = ($validated['new_model'] ?? 'ONT') . ' ' . substr($newSn, -6);
            }

            // Inherit configurations from old ONU
            $newOnu->mac_address = !empty($validated['new_mac_address']) ? strtoupper(trim($validated['new_mac_address'])) : $newOnu->mac_address;
            $newOnu->model = $validated['new_model'] ?? $oldOnu->model;
            $newOnu->odp_id = $oldOnu->odp_id;
            $newOnu->wifi_ssid = $oldOnu->wifi_ssid;
            $newOnu->wifi_password = $oldOnu->wifi_password;
            $newOnu->status = 'active';
            $newOnu->provision_status = 'provisioned';
            $newOnu->branch_id = $oldOnu->branch_id ?? $user->branch_id;
            $newOnu->created_by = $oldOnu->created_by ?? $user->id;
            $newOnu->updated_by = $user->id;
            $newOnu->save();

            // Update old ONU status
            $oldOnu->status = 'replaced';
            $oldOnu->provision_status = 'unprovisioned';
            $oldOnu->description = ($oldOnu->description ? $oldOnu->description . "\n" : '') . "[SWAP " . now()->format('Y-m-d H:i') . "] Digantikan oleh {$newSn} oleh {$user->name}. " . ($validated['notes'] ?? '');
            $oldOnu->save();

            // Re-bind CustomerService from old to new ONU
            $cs = CustomerService::withoutGlobalScopes()->where('onu_id', $oldOnu->id)->first();
            if ($cs) {
                $cs->onu_id = $newOnu->id;
                $cs->save();
            }

            return response()->json([
                'success' => true,
                'message' => "Penggantian ONT berhasil: [{$oldSn}] -> [{$newSn}]",
                'data' => [
                    'old_onu' => [
                        'serial_number' => $oldOnu->serial_number,
                        'status' => $oldOnu->status,
                    ],
                    'new_onu' => $this->formatOnuDevice($newOnu),
                    'customer_service_id' => $cs?->id,
                ],
            ]);
        });
    }

    /**
     * Dismantle ONT/ONU and equipment upon service termination.
     * Mirrors Web Portal Dismantle\Index.php.
     *
     * POST /api/mobile/network/devices/dismantle
     */
    public function dismantle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'serial_number' => 'required|string',
            'dismantle_reason' => 'nullable|string',
            'device_condition' => 'required|in:good,damaged',
            'checklist' => 'nullable|array',
            'notes' => 'nullable|string',
            'work_order_id' => 'nullable|integer',
        ]);

        $user = $request->user();
        $sn = strtoupper(trim($validated['serial_number']));

        return DB::transaction(function () use ($validated, $user, $sn) {
            $onu = Onu::withoutGlobalScopes()->where('serial_number', $sn)->firstOrFail();

            // Unbind CustomerService
            $cs = CustomerService::withoutGlobalScopes()->where('onu_id', $onu->id)->first();
            if ($cs) {
                $cs->onu_id = null;
                $cs->status = 'suspended';
                $cs->save();
            }

            $onu->status = $validated['device_condition'] === 'good' ? 'dismantled_good' : 'dismantled_damaged';
            $onu->odp_id = null;
            $onu->description = ($onu->description ? $onu->description . "\n" : '') . "[DISMANTLE " . now()->format('Y-m-d H:i') . "] Ditarik oleh {$user->name}. Alasan: " . ($validated['dismantle_reason'] ?? 'Berhenti Berlangganan');
            $onu->save();

            return response()->json([
                'success' => true,
                'message' => "Perangkat ONT {$sn} berhasil ditarik (Dismantle).",
                'data' => [
                    'serial_number' => $onu->serial_number,
                    'status' => $onu->status,
                    'device_condition' => $validated['device_condition'],
                    'checklist' => $validated['checklist'] ?? [],
                    'customer_id' => $cs?->customer_id,
                ],
            ]);
        });
    }
}
