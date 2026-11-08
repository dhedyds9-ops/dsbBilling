<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Odp;
use App\Models\ISP\Onu;
use App\Models\Workforce\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MobileNetworkApiController extends Controller
{
    /**
     * Search and list ONT/ONU devices with customer and ODP information.
     */
    public function devices(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Onu::query()->withoutGlobalScopes();

        // Scope by branch if technician has branch_id
        if (!empty($user->branch_id)) {
            $query->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhereNull('branch_id');
            });
        }

        // Search query
        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'LIKE', "%{$search}%")
                  ->orWhere('mac_address', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('model', 'LIKE', "%{$search}%");
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

        $devices = $query->with(['odp'])
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get()
            ->map(function (Onu $onu) {
                return $this->formatOnuDevice($onu);
            });

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

                    // Find or create CustomerService
                    $cs = CustomerService::withoutGlobalScopes()
                        ->where('customer_id', $customerId)
                        ->first();

                    if (!$cs) {
                        $cs = new CustomerService();
                        $cs->customer_id = $customerId;
                        $cs->uuid = (string) Str::uuid();
                        $cs->status = 'active';
                        $cs->branch_id = $user->branch_id;
                    }

                    $cs->onu_id = $onu->id;
                    $cs->tr069_status = 'provisioned';
                    $cs->save();
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
    public function diagnostics(string $serialNumber): JsonResponse
    {
        $sn = strtoupper(trim($serialNumber));
        $onu = Onu::withoutGlobalScopes()
            ->where('serial_number', $sn)
            ->with(['odp'])
            ->first();

        if (!$onu) {
            return response()->json([
                'success' => false,
                'message' => "Perangkat ONT dengan Serial Number '{$sn}' tidak ditemukan.",
            ], 404);
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
                'odp' => $onu->odp ? [
                    'id' => $onu->odp->id,
                    'code' => $onu->odp->code,
                    'name' => $onu->odp->name,
                ] : null,
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
}
