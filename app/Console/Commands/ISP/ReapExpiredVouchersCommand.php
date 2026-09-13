<?php

namespace App\Console\Commands\ISP;

use App\Models\ISP\Voucher;
use Illuminate\Console\Command;
use Src\Domain\Voucher\Actions\ExpireVoucherAction;

class ReapExpiredVouchersCommand extends Command
{
    protected $signature = 'voucher:reap-expired {--dry-run}';
    protected $description = 'Scan voucher status used + expires_at < now() → mark expired, terminate hotspot user, decrement active counter';

    public function __construct(
        private readonly ExpireVoucherAction $expireVoucher,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $dry = (bool)$this->option('dry-run');

        $query = Voucher::query()
            ->whereIn('status', ['used', 'active'])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now());

        $count = $query->count();
        $this->info("Voucher expired ditemukan: {$count}");

        if ($dry) {
            return self::SUCCESS;
        }

        $affected = 0;
        $query->chunkById(200, function ($vouchers) use (&$affected) {
            foreach ($vouchers as $v) {
                try {
                    $this->expireVoucher->execute($v);
                    $affected++;
                } catch (\Throwable $e) {
                    $this->error("Expire gagal voucher #{$v->id} ({$v->code}): {$e->getMessage()}");
                }
            }
        });

        $this->info("Berhasil expire {$affected} voucher.");

        return self::SUCCESS;
    }
}
