<?php

namespace Src\Domain\Jaringan\Events;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Events\DomainEvent;

readonly class FiberDeviceSyncedEvent implements DomainEvent
{
    public function __construct(
        public string $eventId,
        public string $deviceType,
        public string $deviceId,
        public string $userId,
        public DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        string $deviceType,
        string $deviceId,
        string $userId,
    ): self {
        return new self(
            (string) \Illuminate\Support\Str::uuid(),
            $deviceType,
            $deviceId,
            $userId,
            new DateTimeImmutable(),
        );
    }

    public function getName(): string
    {
        return 'fiber.device.synced';
    }
}
