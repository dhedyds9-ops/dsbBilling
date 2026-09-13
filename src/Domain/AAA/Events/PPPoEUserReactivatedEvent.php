<?php

namespace Src\Domain\AAA\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class PPPoEUserReactivatedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $pppoeUserId,
        public readonly int $customerServiceId,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'pppoe_user.reactivated';
    }
}
