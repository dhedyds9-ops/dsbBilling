<?php

namespace Src\Domain\Jaringan\Events;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Events\DomainEvent;

readonly class RouterAlertEvent implements DomainEvent
{
    public function __construct(
        public string $eventId,
        public string $routerId,
        public string $routerName,
        public string $alertType,
        public string $severity,
        public ?string $message,
        public DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        string $routerId,
        string $routerName,
        string $alertType,
        string $severity,
        ?string $message,
    ): self {
        return new self(
            (string) \Illuminate\Support\Str::uuid(),
            $routerId,
            $routerName,
            $alertType,
            $severity,
            $message,
            new DateTimeImmutable(),
        );
    }

    public function getName(): string
    {
        return 'router.alert';
    }
}
