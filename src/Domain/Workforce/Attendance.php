<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Enums\AttendanceStatus;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;

class Attendance extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $technicianId,
        public readonly DateTimeImmutable $date,
        public AttendanceStatus $status,
        public readonly ?GPSCoordinate $checkInLocation = null,
        public readonly ?DateTimeImmutable $checkedInAt = null,
        public readonly ?GPSCoordinate $checkOutLocation = null,
        public readonly ?DateTimeImmutable $checkedOutAt = null,
        public ?string $notes = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public static function create(
        Uuid $technicianId,
        DateTimeImmutable $date,
    ): self {
        return new self(
            Uuid::random(),
            $technicianId,
            $date,
            AttendanceStatus::ABSENT
        );
    }

    public function checkIn(GPSCoordinate $location, DateTimeImmutable $checkedInAt): void {
        $this->checkInLocation = $location;
        $this->checkedInAt = $checkedInAt;
        $this->status = AttendanceStatus::CHECKED_IN;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function checkOut(GPSCoordinate $location, DateTimeImmutable $checkedOutAt): void {
        $this->checkOutLocation = $location;
        $this->checkedOutAt = $checkedOutAt;
        $this->status = AttendanceStatus::CHECKED_OUT;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markLate(): void {
        $this->status = AttendanceStatus::LATE;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markEarlyLeave(): void {
        $this->status = AttendanceStatus::EARLY_LEAVE;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addNotes(string $notes): void {
        $this->notes = $notes;
        $this->updatedAt = new DateTimeImmutable();
    }
}
