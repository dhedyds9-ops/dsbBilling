<?php

namespace Src\Domain\Keuangan\Events;

use Illuminate\Queue\SerializesModels;

class TopupRejectedEvent
{
    use SerializesModels;

    public function __construct(
        public string $topupUuid,
        public int $resellerUserId,
        public float $amount,
        public int $rejectedByUserId,
        public string $reason,
    ) {}
}
