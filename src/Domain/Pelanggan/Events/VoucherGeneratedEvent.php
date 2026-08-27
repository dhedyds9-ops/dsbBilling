<?php

namespace Src\Domain\Pelanggan\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class VoucherGeneratedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Collection $vouchers,
        public readonly int $count,
        public readonly int $createdByUserId,
        public readonly ?int $serviceProfileId = null,
        public readonly ?int $routerId = null,
        public readonly ?array $params = [],
    ) {}
}
