<?php

namespace Src\Domain\AI\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class ConversationCompletedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $conversationId,
        public readonly int $messageCount,
        public readonly int $durationSeconds
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'ai.conversation.completed';
    }
}
