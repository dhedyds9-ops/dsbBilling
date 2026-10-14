<?php

namespace Src\Domain\Keuangan\Events;

use Illuminate\Queue\SerializesModels;

class ExpenseCreatedEvent
{
    use SerializesModels;

    public function __construct(
        public string $expenseUuid,
        public string $code,
        public float $amount,
        public string $category,
        public int $requestedByUserId,
    ) {}
}
