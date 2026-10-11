<?php

namespace Src\Domain\Voucher\Actions;

use App\Models\ISP\Voucher;
use App\Models\User;
use App\Repositories\ISP\VoucherPoolRepository;
use App\Services\ISP\HotspotService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Domain\Voucher\Support\VoucherAuditLogger;
use Throwable;

class ExpireVoucherAction
{
    public function __construct(
        private readonly VoucherPoolRepository $voucherPoolRepository,
        private readonly HotspotService $hotspotService,
        private readonly VoucherAuditLogger $auditLogger,
    ) {}

    public function execute(Voucher $voucher, ?int $userId = null): Voucher
    {
        return DB::transaction(function () use ($voucher, $userId) {
            $oldStatus = $voucher->status;

            if ($oldStatus === 'expired') {
                return $voucher;
            }

            $resolvedUserId = $userId ?? User::query()->value('id') ?? 1;

            if ($voucher->hotspot_user_id) {
                try {
                    $this->hotspotService->terminateHotspotUser($voucher->hotspot_user_id, $resolvedUserId);
                } catch (Throwable $e) {
                    Log::warning('Voucher expire: terminateHotspotUser non-fatal', [
                        'voucher_id' => $voucher->id,
                        'err' => $e->getMessage(),
                    ]);
                }
            }

            $voucher->update([
                'status' => 'expired',
                'updated_by' => $resolvedUserId,
            ]);

            if ($voucher->voucher_pool_id) {
                $pool = $this->voucherPoolRepository->find($voucher->voucher_pool_id);
                if ($pool && (int) $pool->active_vouchers > 0) {
                    $pool->decrement('active_vouchers');
                }
            }

            $this->auditLogger->log($voucher, 'expired', [
                'old_status' => $oldStatus,
            ], $voucher->toArray(), User::find($resolvedUserId));

            return $voucher;
        });
    }
}
