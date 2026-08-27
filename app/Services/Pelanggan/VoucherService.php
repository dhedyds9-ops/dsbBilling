<?php

namespace App\Services\Pelanggan;

use App\Models\ISP\Voucher;
use App\Repositories\ISP\VoucherRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Src\Domain\Pelanggan\Events\VoucherGeneratedEvent;
use Src\Domain\Pelanggan\Events\VoucherSyncedEvent;

class VoucherService
{
    public function __construct(
        protected VoucherRepository $voucherRepository,
    ) {}

    protected function getBaseQuery(string $tab, ?string $search, array $filters): Builder
    {
        $query = Voucher::query()
            ->leftJoin('hotspot_users', 'vouchers.hotspot_user_id', '=', 'hotspot_users.id')
            ->leftJoin('customer_services', 'hotspot_users.customer_service_id', '=', 'customer_services.id')
            ->leftJoin('members', 'customer_services.customer_id', '=', 'members.id')
            ->leftJoin('service_profiles', 'vouchers.service_profile_id', '=', 'service_profiles.id')
            ->leftJoin('nas_devices', 'vouchers.nas_device_id', '=', 'nas_devices.id')
            ->select([
                'vouchers.id',
                'vouchers.code',
                'vouchers.status',
                'vouchers.type',
                'vouchers.created_at',
                'vouchers.activated_at as redeemed_at',
                'vouchers.owner_id',
                'vouchers.created_by',
                'vouchers.nas_device_id as router_id',
                'vouchers.service_profile_id as package_id',
                'members.name as customer_name',
                'service_profiles.name as package_name',
                'service_profiles.validity_days as duration',
                'service_profiles.base_price as price',
                'nas_devices.name as router_name',
            ]);

        switch ($tab) {
            case 'active':
                $query->where(function ($q) {
                    $q->whereIn('vouchers.status', ['available', 'active'])
                      ->orWhere(function ($qq) {
                          $qq->where('vouchers.status', 'used')
                             ->whereNotNull('vouchers.expires_at')
                             ->where('vouchers.expires_at', '>', now());
                      });
                });
                break;
            case 'used':
                $query->where('vouchers.status', 'used');
                break;
            case 'expired':
                $query->where(function ($q) {
                    $q->where('vouchers.status', 'expired')
                      ->orWhere(function ($qq) {
                          $qq->whereNotNull('vouchers.expires_at')
                             ->where('vouchers.expires_at', '<=', now());
                      });
                });
                break;
            case 'all':
            default:
                break;
        }

        if ($search) {
            $query->where(function ($sq) use ($search) {
                $sq->where('vouchers.code', 'like', "%{$search}%")
                   ->orWhere('members.name', 'like', "%{$search}%")
                   ->orWhere('service_profiles.name', 'like', "%{$search}%")
                   ->orWhere('nas_devices.name', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['router_id'])) {
            $query->where('vouchers.nas_device_id', $filters['router_id']);
        }
        if (!empty($filters['package_id'])) {
            $query->where('vouchers.service_profile_id', $filters['package_id']);
        }
        if (!empty($filters['sales_id'])) {
            $query->where('vouchers.created_by', $filters['sales_id']);
        }
        if (!empty($filters['reseller_id'])) {
            $query->where('vouchers.owner_id', $filters['reseller_id']);
        }
        if (!empty($filters['wilayah_id'])) {
            $query->whereExists(function ($sq) use ($filters) {
                $sq->select(DB::raw(1))
                   ->from('nas_devices as nd')
                   ->leftJoin('pops', 'nd.pop_id', '=', 'pops.id')
                   ->whereRaw('nd.id = vouchers.nas_device_id')
                   ->where('pops.id', $filters['wilayah_id']);
            });
        }
        if (!empty($filters['type'])) {
            if ($filters['type'] === 'evoucher') {
                $query->where('vouchers.type', 'evoucher');
            } else {
                $query->where(function ($qq) {
                    $qq->where('vouchers.type', 'reguler')
                       ->orWhereNull('vouchers.type')
                       ->orWhereNotIn('vouchers.type', ['evoucher']);
                });
            }
        }

        return $query;
    }

    public function list(
        string $tab,
        ?string $search,
        array $filters,
        string $sort,
        string $dir,
        int $page,
        int $perpage
    ): LengthAwarePaginator {
        $query = $this->getBaseQuery($tab, $search, $filters);

        $allowedSorts = [
            'code', 'customer_name', 'package_name', 'status',
            'duration', 'price', 'router_name', 'created_at', 'redeemed_at',
        ];

        $sortColumn = in_array($sort, $allowedSorts, true) ? $sort : 'created_at';
        $sortDir = strtolower($dir) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortColumn, $sortDir);

        return $query->paginate($perpage, ['*'], 'page', $page);
    }

    public function summaryCounts(): array
    {
        $totalAll = Voucher::count();
        $active = Voucher::where(function ($q) {
            $q->whereIn('status', ['available', 'active'])
              ->orWhere(function ($qq) {
                  $qq->where('status', 'used')
                     ->whereNotNull('expires_at')
                     ->where('expires_at', '>', now());
              });
        })->count();
        $used = Voucher::where('status', 'used')->count();
        $expired = Voucher::where(function ($q) {
            $q->where('status', 'expired')
              ->orWhere(function ($qq) {
                  $qq->whereNotNull('expires_at')
                     ->where('expires_at', '<=', now());
              });
        })->count();

        return [
            'all' => $totalAll,
            'active' => $active,
            'used' => $used,
            'expired' => $expired,
        ];
    }

    public function bulkSyncRouter(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $success = 0;
            $failed = 0;
            $userId = auth()->id() ?? 0;
            $vouchers = Voucher::whereIn('id', $ids)->get();

            foreach ($vouchers as $voucher) {
                try {
                    $voucher->update(['updated_by' => $userId]);
                    $success++;
                } catch (\Throwable $e) {
                    Log::warning('Voucher sync router failed', ['id' => $voucher->id, 'err' => $e->getMessage()]);
                    $failed++;
                }
            }

            try {
                Event::dispatch(new VoucherSyncedEvent(
                    voucherIds: collect($ids),
                    syncedByUserId: $userId,
                    successCount: $success,
                    failedCount: $failed,
                ));
            } catch (\Throwable $e) {
                Log::warning('VoucherSyncedEvent dispatch failed', ['err' => $e->getMessage()]);
            }

            return $success;
        });
    }

    public function bulkDisable(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $userId = auth()->id() ?? 0;
            $count = Voucher::whereIn('id', $ids)
                ->update([
                    'status' => 'disabled',
                    'updated_by' => $userId,
                ]);
            return $count;
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Voucher::whereIn('id', $ids)->delete();
            return $count;
        });
    }

    public function exportCsv($rows): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filename = 'vouchers_' . date('Ymd_His') . '.csv';
        $path = storage_path('app/private/' . $filename);

        $handle = fopen($path, 'w+');
        fputcsv($handle, [
            'Kode Voucher', 'Nama Pelanggan', 'Paket', 'Router', 'Harga',
            'Durasi (hari)', 'Status', 'Dibuat', 'Diredeem',
        ]);

        foreach ($rows as $r) {
            fputcsv($handle, [
                $r->code,
                $r->customer_name,
                $r->package_name,
                $r->router_name,
                $r->price ? 'Rp ' . number_format($r->price, 0, ',', '.') : '',
                $r->duration,
                $r->status,
                $r->created_at ? $r->created_at->format('d/m/Y H:i') : '',
                $r->redeemed_at ? (is_string($r->redeemed_at) ? $r->redeemed_at : $r->redeemed_at->format('d/m/Y H:i')) : '',
            ]);
        }
        fclose($handle);

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    public function generateVouchers(array $params): array
    {
        $count = (int)($params['count'] ?? 1);
        if ($count <= 0 || $count > 10000) {
            throw new \InvalidArgumentException('Jumlah voucher tidak valid (1-10000).');
        }
        $serviceProfileId = (int)($params['package_id'] ?? 0);
        if (!$serviceProfileId) {
            throw new \InvalidArgumentException('Paket wajib dipilih.');
        }
        $userId = auth()->id() ?? 0;

        $voucherService = app(\App\Services\ISP\VoucherService::class);
        $vouchers = $voucherService->generateAdHocVouchers(
            attrs: [
                'service_profile_id' => $serviceProfileId,
                'nas_device_id' => $params['router_id'] ?? null,
                'owner_id' => $params['reseller_id'] ?? null,
                'type' => $params['type'] ?? 'reguler',
                'length' => $params['length'] ?? 8,
                'prefix' => $params['prefix'] ?? '',
                'validity_days' => $params['validity_days'] ?? null,
                'notes' => $params['notes'] ?? null,
            ],
            count: $count,
            userId: $userId,
        );

        try {
            Event::dispatch(new VoucherGeneratedEvent(
                vouchers: collect($vouchers),
                count: $count,
                createdByUserId: $userId,
                serviceProfileId: $serviceProfileId,
                routerId: $params['router_id'] ?? null,
                params: $params,
            ));
        } catch (\Throwable $e) {
            Log::warning('VoucherGeneratedEvent dispatch failed', ['err' => $e->getMessage()]);
        }

        return $vouchers;
    }
}
