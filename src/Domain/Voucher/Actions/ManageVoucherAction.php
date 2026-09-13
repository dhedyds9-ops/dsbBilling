<?php

namespace Src\Domain\Voucher\Actions;

use App\Models\ISP\Voucher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Src\Domain\Voucher\Repositories\VoucherRepositoryInterface;
use Src\Domain\Voucher\Support\VoucherAuditLogger;

class ManageVoucherAction
{
    public function __construct(
        private readonly VoucherRepositoryInterface $voucherRepository,
        private readonly VoucherAuditLogger $auditLogger,
    ) {}

    public function create(array $data, User $user): Voucher
    {
        return DB::transaction(function () use ($data, $user) {
            $payload = array_merge($data, [
                'uuid' => $data['uuid'] ?? (string) Str::uuid(),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $voucher = $this->voucherRepository->create($payload);

            $this->auditLogger->log($voucher, 'created', null, $voucher->toArray(), $user);

            return $voucher;
        });
    }

    public function delete(Voucher $voucher, User $user): void
    {
        DB::transaction(function () use ($voucher, $user) {
            $oldValues = $voucher->toArray();
            $voucher->update(['updated_by' => $user->id]);
            $voucher->delete();

            $this->auditLogger->log($voucher, 'deleted', $oldValues, null, $user);
        });
    }

    public function restore(Voucher $voucher, User $user): void
    {
        DB::transaction(function () use ($voucher, $user) {
            $voucher->update(['updated_by' => $user->id]);
            $voucher->restore();

            $this->auditLogger->log($voucher, 'restored', null, $voucher->toArray(), $user);
        });
    }

    public function bulkDelete(array $ids, User $user): int
    {
        return DB::transaction(function () use ($ids, $user) {
            $vouchers = $this->voucherRepository->query()
                ->whereIn('id', $ids)
                ->get();

            foreach ($vouchers as $voucher) {
                $this->delete($voucher, $user);
            }

            $this->auditLogger->log(null, 'bulk_delete', ['ids' => $ids], ['count' => $vouchers->count()], $user);

            return $vouchers->count();
        });
    }

    public function bulkRestore(array $ids, User $user): int
    {
        return DB::transaction(function () use ($ids, $user) {
            $vouchers = $this->voucherRepository->onlyTrashedQuery()
                ->whereIn('id', $ids)
                ->get();

            foreach ($vouchers as $voucher) {
                $this->restore($voucher, $user);
            }

            $this->auditLogger->log(null, 'bulk_restore', ['ids' => $ids], ['count' => $vouchers->count()], $user);

            return $vouchers->count();
        });
    }

    public function bulkSyncRouter(array $ids, User $user): int
    {
        return DB::transaction(function () use ($ids, $user) {
            $count = $this->voucherRepository->query()
                ->whereIn('id', $ids)
                ->update([
                    'updated_by' => $user->id,
                    'updated_at' => now(),
                ]);

            $this->auditLogger->log(null, 'bulk_sync_router', ['ids' => $ids], ['count' => $count], $user);

            return $count;
        });
    }
}
