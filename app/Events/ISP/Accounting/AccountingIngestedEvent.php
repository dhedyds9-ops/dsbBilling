<?php

namespace App\Events\ISP\Accounting;

use App\Models\ISP\RadiusAccounting;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccountingIngestedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly RadiusAccounting $accounting,
        public readonly string $statusType,
        public readonly bool $isNewRecord,
    ) {}
}
