<?php

namespace Src\Domain\Voucher\Queries;

use App\Models\ISP\Voucher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Read-side projection untuk domain Voucher.
 * Tanggung jawab: prepare query (filter, scope, join projection) — TIDAK menulis.
 */
class VoucherListQuery
{
    public function getListQuery(?string $search = null, array $filters = [], bool $withTrashed = false): Builder
    {
        return $this->applyResellerScope(Voucher::query(), 'vouchers.reseller_id')
            ->leftJoin('service_profiles', 'vouchers.service_profile_id', '=', 'service_profiles.id')
            ->leftJoin('nas_devices', 'vouchers.nas_device_id', '=', 'nas_devices.id')
            ->leftJoin('users as resellers', 'vouchers.reseller_id', '=', 'resellers.id')
            ->select([
                'vouchers.*',
                'service_profiles.name as service_profile_name',
                'service_profiles.base_price as service_profile_base_price',
                'service_profiles.promo_price as service_profile_promo_price',
                'nas_devices.name as nas_name',
                'resellers.name as reseller_name',
            ])
            ->when($withTrashed, fn ($q) => $q->withTrashed())
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('vouchers.code', 'like', '%' . $search . '%')
                        ->orWhere('resellers.name', 'like', '%' . $search . '%')
                        ->orWhere('service_profiles.name', 'like', '%' . $search . '%')
                        ->orWhere('nas_devices.name', 'like', '%' . $search . '%');
                });
            })
            ->when(!empty($filters['status']), function ($q) use ($filters) {
                $q->where('vouchers.status', $filters['status']);
            })
            ->when(!empty($filters['voucher_pool_id']), function ($q) use ($filters) {
                $q->where('vouchers.voucher_pool_id', $filters['voucher_pool_id']);
            })
            ->when(!empty($filters['created_date']), function ($q) use ($filters) {
                $q->whereDate('vouchers.created_at', $filters['created_date']);
            });
    }

    public function getStatsForDashboard(): array
    {
        $statsQuery = $this->applyResellerScope(Voucher::query(), 'reseller_id');

        return [
            'total' => (clone $statsQuery)->count(),
            'available' => (clone $statsQuery)->where('status', 'available')->count(),
            'used' => (clone $statsQuery)->where('status', 'used')->count(),
            'expired' => (clone $statsQuery)->where('status', 'expired')->count(),
        ];
    }

    public function applyLifecycleTabScope(
        Builder $query,
        string $tab,
        string $statusColumn = 'status',
        string $expiresAtColumn = 'expires_at',
    ): Builder {
        switch ($tab) {
            case 'active':
                $query->where(function ($q) use ($statusColumn, $expiresAtColumn) {
                    $q->whereIn($statusColumn, ['available', 'active'])
                        ->orWhere(function ($qq) use ($statusColumn, $expiresAtColumn) {
                            $qq->where($statusColumn, 'used')
                                ->whereNotNull($expiresAtColumn)
                                ->where($expiresAtColumn, '>', now());
                        });
                });
                break;
            case 'used':
                $query->where($statusColumn, 'used');
                break;
            case 'expired':
                $query->where(function ($q) use ($statusColumn, $expiresAtColumn) {
                    $q->where($statusColumn, 'expired')
                        ->orWhere(function ($qq) use ($expiresAtColumn) {
                            $qq->whereNotNull($expiresAtColumn)
                                ->where($expiresAtColumn, '<=', now());
                        });
                });
                break;
            case 'all':
            default:
                break;
        }

        return $query;
    }

    public function summarizeLifecycleCounts(
        Builder $baseQuery,
        string $statusColumn = 'status',
        string $expiresAtColumn = 'expires_at',
    ): array {
        return [
            'all' => (clone $baseQuery)->count(),
            'active' => (clone $this->applyLifecycleTabScope(clone $baseQuery, 'active', $statusColumn, $expiresAtColumn))->count(),
            'used' => (clone $this->applyLifecycleTabScope(clone $baseQuery, 'used', $statusColumn, $expiresAtColumn))->count(),
            'expired' => (clone $this->applyLifecycleTabScope(clone $baseQuery, 'expired', $statusColumn, $expiresAtColumn))->count(),
        ];
    }

    private function applyResellerScope(Builder $query, string $ownerColumn): Builder
    {
        if (Auth::check() && Auth::user()->hasRole('reseller')) {
            $query->where($ownerColumn, Auth::id());
        }

        return $query;
    }
}
