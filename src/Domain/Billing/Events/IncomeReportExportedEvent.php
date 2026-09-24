<?php

namespace Src\Domain\Billing\Events;

use Illuminate\Queue\SerializesModels;

class IncomeReportExportedEvent
{
    use SerializesModels;

    public function __construct(
        public string $exportType,
        public string $periodType,
        public array $filters,
        public int $exportedByUserId,
        public string $exportedAt,
    ) {}
}
