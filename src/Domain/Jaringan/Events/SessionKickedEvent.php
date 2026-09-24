<?php

namespace Src\Domain\Jaringan\Events;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Events\DomainEvent;

readonly class SessionKickedEvent implements DomainEvent
{
    public function __construct(
        public string $eventId,
        public string $sessionId,
        public string $sessionType,
        public string $username,
        public string $routerId,
        public string $userId,
        public DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        string $sessionId,
        string $sessionType,
        string $username,
        string $routerId,
        string $userId,
    ): self {
        return new self(
            (string) \Illuminate\Support\Str::uuid(),
            $sessionId,
            $sessionType,
            $username,
            $routerId,
            $userId,
            new DateTimeImmutable(),
        );
    }

    public function getName(): string
    {
        return 'session.kicked';
    }
}
