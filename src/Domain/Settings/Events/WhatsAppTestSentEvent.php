<?php

namespace Src\Domain\Settings\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class WhatsAppTestSentEvent extends DomainEvent
{
    public function __construct(
        public readonly int $userId,
        public readonly string $phone,
        public readonly bool $success,
        public readonly ?string $message,
        public readonly string $sentAt,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'settings.whatsapp.test_sent';
    }
}
