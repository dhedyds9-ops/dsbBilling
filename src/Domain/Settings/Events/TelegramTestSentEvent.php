<?php

namespace Src\Domain\Settings\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class TelegramTestSentEvent extends DomainEvent
{
    public function __construct(
        public readonly int $userId,
        public readonly string $chatId,
        public readonly bool $success,
        public readonly ?string $message,
        public readonly string $sentAt,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'settings.telegram.test_sent';
    }
}
