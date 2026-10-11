<?php

namespace Src\Domain\Voucher\Actions;

use App\Events\ISP\Voucher\VouchersGeneratedEvent;
use App\Models\ISP\VoucherPool;
use App\Models\User;
use App\Repositories\ISP\VoucherPoolRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Src\Domain\Voucher\Repositories\VoucherRepositoryInterface;
use Src\Domain\Voucher\Support\VoucherAuditLogger;

class GenerateVoucherAction
{
    public function __construct(
        private readonly VoucherRepositoryInterface $voucherRepository,
        private readonly VoucherPoolRepository $voucherPoolRepository,
        private readonly VoucherAuditLogger $auditLogger,
    ) {}

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
            return $this->voucherPoolRepository->create([
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

        return $this->generateAdHocVouchers([
            'voucher_pool_id' => $pool->id,
            'service_profile_id' => $pool->service_profile_id,
            'prefix' => $pool->prefix,
            'length' => $pool->length,
            'validity_days' => $pool->validity_days,
        ], $count, $userId);
    }

    public function generateAdHocVouchers(array $attrs, int $count, int $userId): array
    {
        if ($count <= 0 || $count > 5000) {
            throw new \InvalidArgumentException('Quantity voucher di luar range yang diijinkan (1-5000)');
        }

        return DB::transaction(function () use ($attrs, $count, $userId) {
            $pool = null;
            $poolId = $attrs['voucher_pool_id'] ?? null;

            if ($poolId) {
                $pool = $this->voucherPoolRepository->find((int) $poolId);
            }

            if (!$pool && !empty($attrs['service_profile_id'])) {
                $pool = $this->findOrCreateDirectPool(
                    serviceProfileId: (int) $attrs['service_profile_id'],
                    type: (string) ($attrs['type'] ?? 'hotspot'),
                    userId: $userId,
                    prefix: (string) ($attrs['prefix'] ?? ''),
                    length: (int) ($attrs['length'] ?? 6),
                    validityDays: (int) ($attrs['validity_days'] ?? 0),
                );

                $poolId = $pool->id;
            }

            $type = (string) ($attrs['type'] ?? 'hotspot');
            $nasDeviceId = $attrs['nas_device_id'] ?? null;
            $resellerId = $attrs['reseller_id'] ?? null;
            $bindOnLogin = (bool) ($attrs['bind_on_login'] ?? false);
            $feeSeller = (float) ($attrs['fee_seller'] ?? 0);
            $loginMethod = (string) ($attrs['login_method'] ?? 'voucher_code');
            $codeCombination = (string) ($attrs['code_combination'] ?? 'uppercase_alphanumeric');
            $length = max(4, min(32, (int) ($attrs['length'] ?? 6)));
            $prefix = (string) ($attrs['prefix'] ?? '');
            $serviceProfileId = $pool?->service_profile_id ?? ($attrs['service_profile_id'] ?? null);
            $validityDays = $attrs['validity_days'] ?? null;
            $notes = $attrs['notes'] ?? null;

            if (!$serviceProfileId) {
                throw new \InvalidArgumentException('service_profile_id wajib diberikan untuk generate voucher.');
            }

            $created = collect();
            $chunks = array_chunk(range(0, $count - 1), 100);

            foreach ($chunks as $chunk) {
                foreach ($chunk as $_) {
                    $code = $this->generateUniqueCode($prefix, $length, $codeCombination);

                    $created->push($this->voucherRepository->create([
                        'uuid' => (string) Str::uuid(),
                        'code' => $code,
                        'type' => $type,
                        'nas_device_id' => $nasDeviceId,
                        'reseller_id' => $resellerId,
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
                    ]));
                }
            }

            if ($pool) {
                $pool->increment('total_vouchers', $count);
            }

            $this->dispatchGeneratedEvent($created, $pool, $count, $userId);
            $this->logBulkGenerate($created, $poolId, (int) $serviceProfileId, $count, $userId);

            return $created->all();
        });
    }

    private function findOrCreateDirectPool(
        int $serviceProfileId,
        string $type,
        int $userId,
        string $prefix,
        int $length,
        int $validityDays,
    ): VoucherPool {
        $name = "Direct Create: Profile#{$serviceProfileId} {$type} (" . now()->format('Y-m-d H:i:s') . ")";

        return $this->voucherPoolRepository->create([
            'uuid' => (string) Str::uuid(),
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

    private function generateUniqueCode(string $prefix, int $length, string $combination): string
    {
        $tries = 0;

        do {
            $code = $this->generateCodeWithParams($prefix, $length, $combination);
            $tries++;
        } while ($tries < 10 && $this->voucherRepository->existsByCode($code));

        if ($this->voucherRepository->existsByCode($code)) {
            throw new \RuntimeException('Gagal menghasilkan kode voucher unik setelah beberapa percobaan.');
        }

        return $code;
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

    private function dispatchGeneratedEvent(Collection $created, ?VoucherPool $pool, int $count, int $userId): void
    {
        try {
            event(new VouchersGeneratedEvent(
                vouchers: $created,
                pool: $pool,
                count: $count,
                createdById: $userId,
            ));
        } catch (\Throwable $e) {
            Log::warning('VouchersGeneratedEvent dispatch failed', ['err' => $e->getMessage()]);
        }
    }

    private function logBulkGenerate(Collection $created, ?int $poolId, int $serviceProfileId, int $count, int $userId): void
    {
        $user = User::find($userId);

        if (!$user || $created->isEmpty()) {
            return;
        }

        $this->auditLogger->log(null, 'bulk_generate_vouchers', null, [
            'count' => $count,
            'pool_id' => $poolId,
            'service_profile_id' => $serviceProfileId,
            'first_code' => $created->first()?->code,
        ], $user);
    }
}
