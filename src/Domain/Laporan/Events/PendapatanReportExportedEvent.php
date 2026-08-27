<?php

namespace Src\Domain\Laporan\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class PendapatanReportExportedEvent extends DomainEvent
{
    public function __construct(
        public readonly int $userId,
        public readonly string $format,
        public readonly array $filters,
        public readonly string $generatedAt,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'laporan.pendapatan.exported';
    }
}
