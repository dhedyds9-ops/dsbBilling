<?php

namespace App\Services\Pelanggan;

use App\Models\ISP\HotspotUser;
use App\Models\ISP\PPPoEUser;
use App\Repositories\ISP\HotspotUserRepository;
use App\Repositories\ISP\PPPoEUserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Src\Domain\Pelanggan\Events\UserReactivatedEvent;

class IsolirService
{
    public function __construct(
        protected PPPoEUserRepository $pppoeRepository,
        protected HotspotUserRepository $hotspotRepository,
    ) {}

    protected function unionBaseQuery(?string $search, array $filters): Builder
    {
        $pppoe = PPPoEUser::query()
            ->leftJoin('customer_services as cs_p', 'pppoe_users.customer_service_id', '=', 'cs_p.id')
            ->leftJoin('members as m_p', 'cs_p.customer_id', '=', 'm_p.id')
            ->leftJoin('service_profiles as sp_p', 'pppoe_users.service_profile_id', '=', 'sp_p.id')
            ->leftJoin('billing_subscriptions as sub_p', 'cs_p.id', '=', 'sub_p.customer_service_id')
            ->leftJoin('invoices as inv_p', function ($j) {
                $j->on('sub_p.customer_id', '=', 'inv_p.customer_id')
                  ->whereIn('inv_p.status', ['pending', 'overdue', 'partial']);
            })
            ->where('pppoe_users.status', 'suspended')
            ->select([
                DB::raw("'pppoe' as source_type"),
                'pppoe_users.id as id',
                DB::raw("CONCAT('P-', pppoe_users.id) as display_id"),
                'pppoe_users.username',
                'm_p.name as customer_name',
                'm_p.phone as customer_phone',
                'm_p.id as customer_id',
                'sp_p.name as package_name',
                DB::raw("NULL as router_name"),
                'pppoe_users.status',
                DB::raw("'Non pembayaran / Billing' as alasan"),
                'pppoe_users.suspended_at as since_isolir',
                'inv_p.due_date as last_due_date',
                'pppoe_users.service_profile_id as package_id',
                DB::raw("NULL as router_id"),
                'pppoe_users.created_by as sales_id',
                'pppoe_users.created_by as reseller_id',
                DB::raw("NULL as wilayah_id"),
                'pppoe_users.suspended_at',
            ]);

        $hotspot = HotspotUser::query()
            ->leftJoin('customer_services as cs_h', 'hotspot_users.customer_service_id', '=', 'cs_h.id')
            ->leftJoin('members as m_h', 'cs_h.customer_id', '=', 'm_h.id')
            ->leftJoin('service_profiles as sp_h', 'hotspot_users.service_profile_id', '=', 'sp_h.id')
            ->leftJoin('billing_subscriptions as sub_h', 'cs_h.id', '=', 'sub_h.customer_service_id')
            ->leftJoin('invoices as inv_h', function ($j) {
                $j->on('sub_h.customer_id', '=', 'inv_h.customer_id')
                  ->whereIn('inv_h.status', ['pending', 'overdue', 'partial']);
            })
            ->where('hotspot_users.status', 'suspended')
            ->select([
                DB::raw("'hotspot' as source_type"),
                'hotspot_users.id as id',
                DB::raw("CONCAT('H-', hotspot_users.id) as display_id"),
                'hotspot_users.username',
                'm_h.name as customer_name',
                'm_h.phone as customer_phone',
                'm_h.id as customer_id',
                'sp_h.name as package_name',
                DB::raw("NULL as router_name"),
                'hotspot_users.status',
                DB::raw("'Non pembayaran / Billing' as alasan"),
                'hotspot_users.suspended_at as since_isolir',
                'inv_h.due_date as last_due_date',
                'hotspot_users.service_profile_id as package_id',
                DB::raw("NULL as router_id"),
                'hotspot_users.created_by as sales_id',
                'hotspot_users.created_by as reseller_id',
                DB::raw("NULL as wilayah_id"),
                'hotspot_users.suspended_at',
            ]);

        $pppoe = $this->applyFilters($pppoe, $search, $filters);
        $hotspot = $this->applyFilters($hotspot, $search, $filters);

        return $pppoe->unionAll($hotspot);
    }

    protected function applyFilters(Builder $query, ?string $search, array $filters): Builder
    {
        if ($search) {
            $query->where(function ($sq) use ($search) {
                $sq->where('pppoe_users.username', 'like', "%{$search}%")
                   ->orWhere('hotspot_users.username', 'like', "%{$search}%")
                   ->orWhere('m_p.name', 'like', "%{$search}%")
                   ->orWhere('m_h.name', 'like', "%{$search}%")
                   ->orWhere('sp_p.name', 'like', "%{$search}%")
                   ->orWhere('sp_h.name', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['package_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('sp_p.id', $filters['package_id'])
                  ->orWhere('sp_h.id', $filters['package_id']);
            });
        }
        if (!empty($filters['start_date'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereDate('pppoe_users.suspended_at', '>=', $filters['start_date'])
                  ->orWhereDate('hotspot_users.suspended_at', '>=', $filters['start_date']);
            });
        }
        if (!empty($filters['end_date'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereDate('pppoe_users.suspended_at', '<=', $filters['end_date'])
                  ->orWhereDate('hotspot_users.suspended_at', '<=', $filters['end_date']);
            });
        }

        return $query;
    }

    public function list(
        ?string $search,
        array $filters,
        string $sort,
        string $dir,
        int $page,
        int $perpage
    ): LengthAwarePaginator {
        $union = $this->unionBaseQuery($search, $filters);

        $sortCol = match ($sort) {
            'username' => 'username',
            'customer_name' => 'customer_name',
            'package_name' => 'package_name',
            'status' => 'status',
            'alasan' => 'alasan',
            'since_isolir' => 'since_isolir',
            'last_due_date' => 'last_due_date',
            default => 'since_isolir',
        };
        $sortDir = strtolower($dir) === 'asc' ? 'asc' : 'desc';

        $wrapped = DB::query()->from(DB::raw("({$union->toSql()}) as iso"))
            ->mergeBindings($union->getQuery())
            ->orderBy($sortCol, $sortDir);

        return $wrapped->paginate($perpage, ['*'], 'page', $page);
    }

    public function summary(): array
    {
        $total = PPPoEUser::where('status', 'suspended')->count()
            + HotspotUser::where('status', 'suspended')->count();

        $today = PPPoEUser::whereDate('suspended_at', today())->count()
            + HotspotUser::whereDate('suspended_at', today())->count();

        $longestPppoe = PPPoEUser::where('status', 'suspended')->min('suspended_at');
        $longestHotspot = HotspotUser::where('status', 'suspended')->min('suspended_at');
        $longest = collect([$longestPppoe, $longestHotspot])->filter()->min();
        $terlama = $longest ? now()->diffInDays($longest) . ' hari' : '-';

        return [
            'total' => $total,
            'today' => $today,
            'terlama' => $terlama,
        ];
    }

    public function bulkActivate(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = 0;
            foreach ($ids as $displayId) {
                [$type, $id] = $this->parseDisplayId($displayId);
                if ($type && $id && $this->reactivateUserInternal($type, $id)) {
                    $count++;
                }
            }
            return $count;
        });
    }

    protected function parseDisplayId(mixed $raw): array
    {
        $s = (string)$raw;
        if (str_starts_with($s, 'P-')) {
            return ['pppoe', (int) substr($s, 2)];
        }
        if (str_starts_with($s, 'H-')) {
            return ['hotspot', (int) substr($s, 2)];
        }
        if (is_numeric($s)) {
            return ['pppoe', (int)$s];
        }
        return [null, null];
    }

    public function reactivateUser(int|string $id): bool
    {
        return DB::transaction(function () use ($id) {
            [$type, $realId] = $this->parseDisplayId($id);
            if (!$type || !$realId) {
                return false;
            }
            return $this->reactivateUserInternal($type, $realId);
        });
    }

    protected function reactivateUserInternal(string $type, int $id): bool
    {
        $userId = auth()->id() ?? 0;

        try {
            if ($type === 'pppoe') {
                $user = PPPoEUser::find($id);
                if (!$user) {
                    return false;
                }
                $user->update([
                    'status' => 'active',
                    'suspended_at' => null,
                    'updated_by' => $userId,
                ]);
                $customerId = $user->customer?->id ?? null;
            } else {
                $user = HotspotUser::find($id);
                if (!$user) {
                    return false;
                }
                $user->update([
                    'status' => 'active',
                    'suspended_at' => null,
                    'updated_by' => $userId,
                ]);
                $customerId = $user->customer?->id ?? null;
            }

            try {
                Event::dispatch(new UserReactivatedEvent(
                    userId: $id,
                    userType: $type,
                    customerId: $customerId,
                    reactivatedBy: $userId,
                ));
            } catch (\Throwable $e) {
                Log::warning('UserReactivatedEvent dispatch failed', ['err' => $e->getMessage()]);
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Reactivate user failed', ['type' => $type, 'id' => $id, 'err' => $e->getMessage()]);
            return false;
        }
    }

    public function sendWaBulk(array $ids): int
    {
        $count = 0;
        $users = collect($ids)->map(fn($raw) => $this->parseDisplayId($raw))
            ->filter(fn($pair) => $pair[0] !== null);

        foreach ($users as [$type, $id]) {
            $phone = null;
            if ($type === 'pppoe') {
                $u = PPPoEUser::with('customer')->find($id);
                $phone = $u?->customer?->phone ?? null;
            } else {
                $u = HotspotUser::with('customer')->find($id);
                $phone = $u?->customer?->phone ?? null;
            }
            if ($phone) {
                $count++;
            }
        }
        return $count;
    }

    public function exportCsv($rows): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filename = 'isolir_' . date('Ymd_His') . '.csv';
        $path = storage_path('app/private/' . $filename);

        $handle = fopen($path, 'w+');
        fputcsv($handle, [
            'Tipe', 'Username', 'Nama Pelanggan', 'Paket', 'Status',
            'Alasan', 'Sejak Isolir', 'Jatuh Tempo Terakhir',
        ]);

        foreach ($rows as $r) {
            fputcsv($handle, [
                strtoupper($r->source_type ?? '-'),
                $r->username,
                $r->customer_name,
                $r->package_name,
                $r->status,
                $r->alasan,
                $r->since_isolir ? (is_string($r->since_isolir) ? $r->since_isolir : \Illuminate\Support\Carbon::parse($r->since_isolir)->format('d/m/Y H:i')) : '',
                $r->last_due_date ? (is_string($r->last_due_date) ? $r->last_due_date : \Illuminate\Support\Carbon::parse($r->last_due_date)->format('d/m/Y')) : '',
            ]);
        }
        fclose($handle);

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }
}
