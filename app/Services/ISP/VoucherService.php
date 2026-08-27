<?php

namespace App\Services\ISP;

use App\Models\AuditLog;
use App\Models\ISP\Voucher;
use App\Models\ISP\VoucherPool;
use App\Models\User;
use App\Repositories\ISP\VoucherRepository;
use App\Repositories\ISP\VoucherPoolRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class VoucherService
{
    public function __construct(
        protected VoucherRepository $voucherRepository,
        protected VoucherPoolRepository $voucherPoolRepository,
    ) {}

    public function create(array $data, User $user): Model
    {
        return DB::transaction(function () use ($data, $user) {
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;

            $model = Voucher::create($data);

            $this->logAudit($model, 'created', null, $model->toArray(), $user);

            return $model;
        });
    }

    public function update(Model $model, array $data, User $user): Model
    {
        return DB::transaction(function () use ($model, $data, $user) {
            $oldValues = $model->toArray();
            $data['updated_by'] = $user->id;

            $model->update($data);

            $this->logAudit($model, 'updated', $oldValues, $model->toArray(), $user);

            return $model;
        });
    }

    public function delete(Model $model, User $user): void
    {
        DB::transaction(function () use ($model, $user) {
            $oldValues = $model->toArray();
            $model->update(['updated_by' => $user->id]);
            $model->delete();
            $this->logAudit($model, 'deleted', $oldValues, null, $user);
        });
    }

    public function restore(Model $model, User $user): void
    {
        DB::transaction(function () use ($model, $user) {
            $model->update(['updated_by' => $user->id]);
            $model->restore();
            $this->logAudit($model, 'restored', null, $model->toArray(), $user);
        });
    }

    public function bulkDelete(array $ids, User $user): void
    {
        DB::transaction(function () use ($ids, $user) {
            $models = Voucher::whereIn('id', $ids)->get();
            foreach ($models as $model) {
                $this->delete($model, $user);
            }
            $this->logAudit(null, 'bulk_delete', ['ids' => $ids], null, $user);
        });
    }

    public function bulkRestore(array $ids, User $user): void
    {
        DB::transaction(function () use ($ids, $user) {
            $models = Voucher::onlyTrashed()->whereIn('id', $ids)->get();
            foreach ($models as $model) {
                $this->restore($model, $user);
            }
            $this->logAudit(null, 'bulk_restore', ['ids' => $ids], null, $user);
        });
    }

    public function createVoucherPool(
        string $name,
        int $serviceProfileId,
        int $userId,
        ?string $description = null,
        ?string $prefix = null,
        int $length = 8,
        int $quota = 0,
        ?int $validityDays = null,
    ): VoucherPool {
        return DB::transaction(function () use (
            $name,
            $serviceProfileId,
            $userId,
            $description,
            $prefix,
            $length,
            $quota,
            $validityDays,
        ) {
            $voucherPool = $this->voucherPoolRepository->create([
                'uuid' => (string) Str::uuid(),
                'name' => $name,
                'description' => $description,
                'service_profile_id' => $serviceProfileId,
                'prefix' => $prefix,
                'length' => $length,
                'quota' => $quota,
                'validity_days' => $validityDays,
                'total_vouchers' => 0,
                'used_vouchers' => 0,
                'active_vouchers' => 0,
                'status' => 'active',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            // TODO: Dispatch domain event

            return $voucherPool;
        });
    }

    public function generateVouchers(int $voucherPoolId, int $count, int $userId): array
    {
        $pool = $this->voucherPoolRepository->find($voucherPoolId);
        if (!$pool) {
            throw new \InvalidArgumentException("Pool voucher #{$voucherPoolId} tidak ditemukan");
        }
        if ($pool->quota > 0 && ($pool->total_vouchers + $count) > $pool->quota) {
            $remaining = max(0, $pool->quota - $pool->total_vouchers);
            throw new \InvalidArgumentException("Quota pool terlampaui. Sisa quota: {$remaining}.");
        }

        $attrs = [
            'voucher_pool_id' => $pool->id,
            'service_profile_id' => $pool->service_profile_id,
            'prefix' => $pool->prefix,
            'length' => $pool->length,
            'validity_days' => $pool->validity_days,
        ];

        return $this->generateAdHocVouchers($attrs, $count, $userId);
    }

    public function generateAdHocVouchers(array $attrs, int $count, int $userId): array
    {
        if ($count <= 0 || $count > 10000) {
            throw new \InvalidArgumentException('Quantity voucher di luar range yang diijinkan (1-10000)');
        }

        return DB::transaction(function () use ($attrs, $count, $userId) {
            $pool = null;
            $poolId = $attrs['voucher_pool_id'] ?? null;

            if ($poolId) {
                $pool = $this->voucherPoolRepository->find($poolId);
            }
            if (!$pool && !empty($attrs['service_profile_id'])) {
                $pool = $this->findOrCreateDirectPool(
                    (int)$attrs['service_profile_id'],
                    (string)($attrs['type'] ?? 'hotspot'),
                    $userId,
                    (string)($attrs['prefix'] ?? ''),
                    (int)($attrs['length'] ?? 6),
                    (int)($attrs['validity_days'] ?? 0)
                );
                $poolId = $pool->id;
            }

            $type = (string)($attrs['type'] ?? 'hotspot');
            $nasDeviceId = $attrs['nas_device_id'] ?? null;
            $ownerId = $attrs['owner_id'] ?? null;
            $bindOnLogin = (bool)($attrs['bind_on_login'] ?? false);
            $feeSeller = (float)($attrs['fee_seller'] ?? 0);
            $loginMethod = (string)($attrs['login_method'] ?? 'voucher_code');
            $codeCombination = (string)($attrs['code_combination'] ?? 'uppercase_alphanumeric');
            $length = max(4, min(32, (int)($attrs['length'] ?? 6)));
            $prefix = (string)($attrs['prefix'] ?? '');
            $serviceProfileId = $pool?->service_profile_id ?? ($attrs['service_profile_id'] ?? null);
            $validityDays = $attrs['validity_days'] ?? null;
            $notes = $attrs['notes'] ?? null;

            if (!$serviceProfileId) {
                throw new \InvalidArgumentException('service_profile_id wajib diberikan untuk generate voucher.');
            }

            $chunks = array_chunk(range(0, $count - 1), 100);
            $created = collect();

            foreach ($chunks as $chunk) {
                $rows = [];
                $now = now();
                foreach ($chunk as $_) {
                    $tries = 0;
                    do {
                        $code = $this->generateCodeWithParams($prefix, $length, $codeCombination);
                        $tries++;
                    } while ($tries < 5 && Voucher::query()->where('code', $code)->exists());

                    $rows[] = [
                        'uuid' => (string)Str::uuid(),
                        'code' => $code,
                        'type' => $type,
                        'nas_device_id' => $nasDeviceId,
                        'owner_id' => $ownerId,
                        'service_profile_id' => $serviceProfileId,
                        'voucher_pool_id' => $poolId,
                        'bind_on_login' => $bindOnLogin,
                        'fee_seller' => $feeSeller,
                        'login_method' => $loginMethod,
                        'code_combination' => $codeCombination,
                        'validity_days' => $validityDays,
                        'status' => 'available',
                        'notes' => $notes,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                foreach ($rows as $row) {
                    $created->push(Voucher::query()->create($row));
                }
            }

            if ($pool) {
                $pool->increment('total_vouchers', $count);
            }

            try {
                event(new \App\Events\ISP\Voucher\VouchersGeneratedEvent(
                    vouchers: $created,
                    pool: $pool,
                    count: $count,
                    createdById: $userId,
                ));
            } catch (\Throwable $e) {
                Log::warning('VouchersGeneratedEvent dispatch failed', ['err' => $e->getMessage()]);
            }

            $user = \App\Models\User::find($userId);
            if ($user && $created->isNotEmpty()) {
                $this->logAudit(null, 'bulk_generate_vouchers', null, [
                    'count' => $count,
                    'pool_id' => $poolId,
                    'service_profile_id' => $serviceProfileId,
                    'first_code' => $created->first()->code,
                ], $user);
            }

            return $created->all();
        });
    }

    private function findOrCreateDirectPool(int $serviceProfileId, string $type, int $userId, string $prefix, int $length, int $validityDays): VoucherPool
    {
        $name = "Direct Create: Profile#{$serviceProfileId} {$type}";

        $pool = VoucherPool::query()
            ->where('service_profile_id', $serviceProfileId)
            ->where('name', $name)
            ->first();

        if ($pool) {
            return $pool;
        }

        return $this->voucherPoolRepository->create([
            'uuid' => (string)Str::uuid(),
            'name' => $name,
            'description' => "Auto-generated direct-create pool untuk service_profile #{$serviceProfileId} type {$type}",
            'service_profile_id' => $serviceProfileId,
            'prefix' => $prefix,
            'length' => $length,
            'quota' => 0,
            'validity_days' => $validityDays,
            'total_vouchers' => 0,
            'used_vouchers' => 0,
            'active_vouchers' => 0,
            'status' => 'active',
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    private function generateCodeWithParams(string $prefix, int $length, string $combination): string
    {
        switch ($combination) {
            case 'uppercase':
                $random = strtoupper(Str::random($length));
                break;
            case 'lowercase':
                $random = strtolower(Str::random($length));
                break;
            case 'numbers':
                $random = '';
                for ($j = 0; $j < $length; $j++) {
                    $random .= mt_rand(0, 9);
                }
                break;
            case 'alphanumeric':
                $random = Str::random($length);
                break;
            case 'uppercase_alphanumeric':
            default:
                $random = strtoupper(Str::random($length));
                break;
        }
        return $prefix . $random;
    }

    public function redeemVoucher(string $code, ?int $userId): ?Voucher
    {
        return app(\App\Services\ISP\Voucher\VoucherLifecycleService::class)->redeemVoucher($code, $userId);
    }

    protected function logAudit($model, string $event, ?array $oldValues, ?array $newValues, User $user): void
    {
        try {
            if ($model) {
                AuditLog::create([
                    'auditable_type' => get_class($model),
                    'auditable_id' => $model->id,
                    'event' => $event,
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'user_id' => $user->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            } else {
                AuditLog::create([
                    'event' => $event,
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'user_id' => $user->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Audit log failed: ' . $e->getMessage(), [
                'model' => $model ? get_class($model) : null,
                'id' => $model->id ?? null,
            ]);
        }
    }
}
