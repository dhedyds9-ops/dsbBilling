<?php

namespace Src\Domain\AI\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class MessageSentEvent extends DomainEvent
{
    public function __construct(
        public readonly string $conversationId,
        public readonly string $messageId,
        public readonly string $role,
        public readonly string $content
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'ai.message.sent';
    }
}
