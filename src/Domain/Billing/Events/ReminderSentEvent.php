<?php

namespace Src\Domain\Billing\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class ReminderSentEvent extends DomainEvent
{
    public function __construct(
        public readonly string $invoiceId,
        public readonly int $customerId,
        public readonly string $reminderType,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'reminder.sent';
    }
}
