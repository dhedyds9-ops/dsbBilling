<?php

namespace Src\Domain\Pelanggan\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class VoucherSyncedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Collection $voucherIds,
        public readonly int $syncedByUserId,
        public readonly int $successCount,
        public readonly int $failedCount,
        public readonly ?\DateTimeInterface $syncedAt = null,
    ) {}
}
