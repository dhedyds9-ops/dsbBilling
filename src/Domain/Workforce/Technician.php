<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Enums\TechnicianStatus;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;
use Src\Domain\Workforce\ValueObjects\WorkingHour;

class Technician extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $userId,
        public string $name,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $employeeId = null,
        public TechnicianStatus $status = TechnicianStatus::OFFLINE,
        public ?GPSCoordinate $currentLocation = null,
        public ?WorkingHour $workingHour = null,
        public array $skills = [],
        public ?DateTimeImmutable $lastActiveAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public static function create(
        Uuid $userId,
        string $name,
        ?string $phone = null,
        ?string $email = null,
        ?string $employeeId = null,
    ): self {
        return new self(
            Uuid::random(),
            $userId,
            $name,
            $phone,
            $email,
            $employeeId,
        );
    }

    public function updateStatus(TechnicianStatus $status): void {
        $this->status = $status;
        $this->updatedAt = new DateTimeImmutable();
        $this->lastActiveAt = new DateTimeImmutable();
    }

    public function updateLocation(GPSCoordinate $coordinate): void {
        $this->currentLocation = $coordinate;
        $this->updatedAt = new DateTimeImmutable();
        $this->lastActiveAt = new DateTimeImmutable();
    }

    public function isAvailable(): bool {
        return $this->status === TechnicianStatus::AVAILABLE;
    }
}
