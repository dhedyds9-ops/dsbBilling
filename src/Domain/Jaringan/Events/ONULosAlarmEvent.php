<?php

namespace Src\Domain\Jaringan\Events;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Events\DomainEvent;

readonly class ONULosAlarmEvent implements DomainEvent
{
    public function __construct(
        public string $eventId,
        public string $onuId,
        public string $onuSerialNumber,
        public string $oltId,
        public string $severity,
        public ?string $customerId,
        public DateTimeImmutable $losSince,
        public DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        string $onuId,
        string $onuSerialNumber,
        string $oltId,
        string $severity,
        ?string $customerId,
        DateTimeImmutable $losSince,
    ): self {
        return new self(
            (string) \Illuminate\Support\Str::uuid(),
            $onuId,
            $onuSerialNumber,
            $oltId,
            $severity,
            $customerId,
            $losSince,
            new DateTimeImmutable(),
        );
    }

    public function getName(): string
    {
        return 'onu.los.alarm';
    }
}
