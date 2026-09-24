<?php

namespace Src\Domain\Billing\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class InvoiceBatchGeneratedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $tahun,
        public readonly int $bulan,
        public readonly Collection $invoiceIds,
        public readonly int $generatedByUserId,
        public readonly int $totalGenerated,
        public readonly float $totalAmount,
        public readonly ?\DateTimeInterface $generatedAt = null,
    ) {}
}
