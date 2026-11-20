<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\Voucher;
use App\Models\ISP\VoucherRequest;
use App\Services\ISP\VoucherRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileVoucherApiController extends Controller
{
    /**
     * List vouchers with summary and filters for Reseller / Staff.
     * GET /api/mobile/vouchers
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $ownerId = $user->getEffectiveResellerId() ?? ($user->hasRole('reseller') ? $user->id : null);

        $query = Voucher::withoutGlobalScopes()
            ->with(['serviceProfile', 'reseller']);

        if ($ownerId) {
            $query->where(function ($q) use ($ownerId) {
                $q->where('reseller_id', $ownerId)
                  ->orWhere('created_by', $ownerId);
            });
        } elseif ($user->branch_id && !$user->isSuperAdmin()) {
            $query->where('branch_id', $user->branch_id);
        }

        // Stats calculation
        $totalVouchers = (clone $query)->count();
        $activeCount = (clone $query)->where('status', 'available')->count();
        $usedCount = (clone $query)->where('status', 'used')->count();
        $unusedCount = (clone $query)->where('status', 'unused')->count();
        $expiredCount = (clone $query)->where('status', 'expired')->count();
        $totalRevenue = (float) (clone $query)->whereIn('status', ['used', 'available'])->sum('fee_seller');

        // Status Filter
        $status = strtolower(trim((string) $request->input('status', 'all')));
        if ($status !== 'all' && in_array($status, ['available', 'active', 'used', 'unused', 'expired'])) {
            $query->where('status', $status);
        }

        // Search Filter
        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('notes', 'LIKE', "%{$search}%")
                  ->orWhereHas('serviceProfile', fn($sp) => $sp->where('name', 'LIKE', "%{$search}%"));
            });
        }

        $vouchers = $query->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($v) {
                $profileName = $v->serviceProfile?->name ?? 'Paket Voucher';
                $price = (float) ($v->serviceProfile?->promo_price ?: ($v->serviceProfile?->base_price ?: 5000));
                $feeSeller = (float) ($v->fee_seller ?? ($price * 0.15));

                return [
                    'id' => $v->id,
                    'code' => $v->code,
                    'service_profile_id' => $v->service_profile_id,
                    'service_profile_name' => $profileName,
                    'price' => $price,
                    'fee_seller' => $feeSeller,
                    'status' => $v->status ?? 'available',
                    'validity_days' => $v->validity_days ?? 1,
                    'activated_at' => $v->activated_at?->format('d M Y H:i'),
                    'expires_at' => $v->expires_at?->format('d M Y H:i'),
                    'created_at' => $v->created_at?->format('d M Y H:i'),
                    'notes' => $v->notes ?? '',
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Data voucher berhasil dimuat.',
            'summary' => [
                'total_vouchers' => $totalVouchers,
                'total_revenue' => $totalRevenue,
                'active_count' => $activeCount,
                'used_count' => $usedCount,
                'unused_count' => $unusedCount,
                'expired_count' => $expiredCount,
            ],
            'count' => $vouchers->count(),
            'data' => $vouchers,
        ]);
    }

    /**
     * List packages available for voucher request.
     * GET /api/mobile/vouchers/packages
     */
    public function packages(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = ServiceProfile::withoutGlobalScope('reseller_isolation')
            ->where(function ($q) {
                $q->where(DB::raw('LOWER(COALESCE(service_type, ""))'), 'voucher')
                  ->orWhere('service_type', 'LIKE', '%voucher%')
                  ->orWhere('name', 'LIKE', '%voucher%')
                  ->orWhereHas('serviceProfileType', function ($tq) {
                      $tq->where(DB::raw('LOWER(COALESCE(code, ""))'), 'voucher')
                         ->orWhere(DB::raw('LOWER(COALESCE(name, ""))'), 'like', '%voucher%');
                  });
            })
            ->where(function ($q) {
                $q->where(DB::raw('LOWER(COALESCE(service_type, ""))'), '!=', 'pppoe')
                  ->where(DB::raw('LOWER(COALESCE(service_type, ""))'), '!=', 'hotspot');
            })
            ->where(function ($q) {
                $q->where('status', 'active')
                  ->orWhere('status', 'ACTIVE')
                  ->orWhereNull('status');
            });

        if ($user->branch_id && !$user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->whereNull('branch_id')
                  ->orWhere('branch_id', $user->branch_id);
            });
        }

        $packages = $query->orderBy('name')
            ->get()
            ->map(function (ServiceProfile $sp) {
                $retailPrice = (float) ($sp->promo_price ?: ($sp->base_price ?: ($sp->price ?: 0)));
                $costPrice = (float) ($sp->reseller_price ?: ($sp->cost_price ?: $retailPrice));

                return [
                    'id' => $sp->id,
                    'name' => $sp->name,
                    'code' => $sp->code,
                    'service_type' => $sp->service_type,
                    'unit_price' => $retailPrice,
                    'cost_price' => $costPrice,
                    'margin' => max(0, $retailPrice - $costPrice),
                    'validity_days' => (int) ($sp->validity_days ?: 1),
                    'validity_hours' => (int) ($sp->validity_hours ?: 0),
                    'quota_value' => (float) ($sp->quota_value ?: 0),
                    'quota_unit' => $sp->quota_unit ?: 'MB',
                    'download_speed' => (int) ($sp->download_speed ?: 0),
                    'upload_speed' => (int) ($sp->upload_speed ?: 0),
                    'description' => $sp->description ?: '',
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Daftar paket voucher berhasil dimuat.',
            'data' => $packages,
        ]);
    }

    /**
     * List voucher requests for the authenticated reseller.
     * GET /api/mobile/vouchers/requests
     */
    public function requests(Request $request): JsonResponse
    {
        $user = $request->user();
        $resellerId = $user->getEffectiveResellerId() ?? $user->id;

        $query = VoucherRequest::withoutGlobalScopes()
            ->where('reseller_id', $resellerId)
            ->with(['serviceProfile', 'voucherTemplate', 'approvedBy']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $requests = $query->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(function (VoucherRequest $vr) {
                return [
                    'id' => $vr->id,
                    'request_no' => $vr->request_no,
                    'service_profile_id' => $vr->service_profile_id,
                    'service_profile_name' => $vr->serviceProfile?->name ?? 'Paket',
                    'quantity' => $vr->quantity,
                    'unit_price' => (float) $vr->unit_price,
                    'cost_price' => (float) $vr->cost_price,
                    'total_amount' => (float) $vr->total_amount,
                    'estimated_profit' => $vr->estimated_profit,
                    'delivery_type' => $vr->delivery_type,
                    'delivery_type_label' => $vr->delivery_type_label,
                    'payment_method' => $vr->payment_method,
                    'payment_status' => $vr->payment_status,
                    'status' => $vr->status,
                    'status_label' => $vr->status_label,
                    'status_color' => $vr->status_color,
                    'rejection_reason' => $vr->rejection_reason,
                    'notes' => $vr->notes,
                    'created_at' => $vr->created_at?->format('d M Y H:i'),
                    'approved_at' => $vr->approved_at?->format('d M Y H:i'),
                    'completed_at' => $vr->completed_at?->format('d M Y H:i'),
                    'has_generated_vouchers' => !empty($vr->generated_voucher_ids),
                    'voucher_count' => is_array($vr->generated_voucher_ids) ? count($vr->generated_voucher_ids) : 0,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Daftar riwayat permintaan voucher berhasil dimuat.',
            'count' => $requests->count(),
            'data' => $requests,
        ]);
    }

    /**
     * Create a new voucher request from mobile.
     * POST /api/mobile/vouchers/requests
     */
    public function storeRequest(Request $request, VoucherRequestService $service): JsonResponse
    {
        $validated = $request->validate([
            'service_profile_id' => 'required|exists:service_profiles,id',
            'quantity' => 'required|integer|min:1|max:5000',
            'delivery_type' => 'nullable|in:physical_print,digital',
            'payment_method' => 'nullable|in:deposit,cash,tempo',
            'voucher_template_id' => 'nullable|exists:voucher_templates,id',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $voucherRequest = $service->createRequest($validated, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Permintaan voucher berhasil diajukan ke admin.',
                'data' => [
                    'id' => $voucherRequest->id,
                    'request_no' => $voucherRequest->request_no,
                    'quantity' => $voucherRequest->quantity,
                    'total_amount' => (float) $voucherRequest->total_amount,
                    'status' => $voucherRequest->status,
                    'status_label' => $voucherRequest->status_label,
                ],
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat permintaan voucher: ' . $e->getMessage(),
            ], 422);
        }
    }
}

