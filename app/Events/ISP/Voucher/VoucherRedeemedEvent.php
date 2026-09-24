<?php

namespace App\Events\ISP\Voucher;

use App\Models\ISP\HotspotUser;
use App\Models\ISP\Voucher;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoucherRedeemedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Voucher $voucher,
        public readonly ?HotspotUser $hotspotUser,
        public readonly ?int $redeemerUserId,
    ) {}
}
