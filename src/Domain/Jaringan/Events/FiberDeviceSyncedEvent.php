<?php

namespace Src\Domain\Jaringan\Events;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Events\DomainEvent;

class FiberDeviceSyncedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $eventId,
        public readonly string $deviceType,
        public readonly string $deviceId,
        public readonly string $userId,
    ) {
        parent::__construct();
    }

    public static function create(
        string $deviceType,
        string $deviceId,
        string $userId,
    ): self {
        return new self(
            (string) \Illuminate\Support\Str::uuid(),
            $deviceType,
            $deviceId,
            $userId
        );
    }

    public function getName(): string
    {
        return 'fiber.device.synced';
    }
}
