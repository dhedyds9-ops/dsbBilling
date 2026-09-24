<?php

namespace App\Events\ISP\Voucher;

use App\Models\ISP\Voucher;
use App\Models\ISP\VoucherPool;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class VouchersGeneratedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Collection $vouchers,
        public readonly ?VoucherPool $pool,
        public readonly int $count,
        public readonly ?int $createdById,
    ) {}
}
