<?php

namespace Src\Domain\AI\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class ConversationStartedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $conversationId,
        public readonly string $module,
        public readonly ?string $userId = null
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'ai.conversation.started';
    }
}
