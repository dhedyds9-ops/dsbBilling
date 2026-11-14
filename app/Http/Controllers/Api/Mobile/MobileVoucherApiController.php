<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ISP\Voucher;
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
        $activeCount = (clone $query)->where('status', 'active')->count();
        $usedCount = (clone $query)->where('status', 'used')->count();
        $unusedCount = (clone $query)->where('status', 'unused')->count();
        $expiredCount = (clone $query)->where('status', 'expired')->count();
        $totalRevenue = (float) (clone $query)->whereIn('status', ['used', 'active'])->sum('fee_seller');

        // Status Filter
        $status = strtolower(trim((string) $request->input('status', 'all')));
        if ($status !== 'all' && in_array($status, ['active', 'used', 'unused', 'expired'])) {
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
                $price = (float) ($v->serviceProfile?->price ?? 5000);
                $feeSeller = (float) ($v->fee_seller ?? ($price * 0.15));

                return [
                    'id' => $v->id,
                    'code' => $v->code,
                    'service_profile_id' => $v->service_profile_id,
                    'service_profile_name' => $profileName,
                    'price' => $price,
                    'fee_seller' => $feeSeller,
                    'status' => $v->status ?? 'unused',
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
}
