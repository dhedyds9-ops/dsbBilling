<?php

namespace Src\Domain\Inventory\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;

readonly class WarrantyPeriod
{
    public function __construct(
        public DateTimeImmutable $startDate,
        public DateTimeImmutable $endDate
    ) {
        if ($endDate < $startDate) {
            throw new InvalidArgumentException("End date must be after start date");
        }
    }

    public static function fromMonths(int $months, ?DateTimeImmutable $startDate = null): self
    {
        $start = $startDate ?? new DateTimeImmutable();
        $end = $start->modify("+{$months} months");
        return new self($start, $end);
    }

    public static function fromYears(int $years, ?DateTimeImmutable $startDate = null): self
    {
        return self::fromMonths($years * 12, $startDate);
    }

    public function isExpired(): bool
    {
        return $this->endDate < new DateTimeImmutable();
    }

    public function isActive(): bool
    {
        $now = new DateTimeImmutable();
        return $this->startDate <= $now && $this->endDate >= $now;
    }

    public function getRemainingDays(): int
    {
        $now = new DateTimeImmutable();
        if ($this->isExpired()) {
            return 0;
        }
        return $now->diff($this->endDate)->days;
    }

    public function getDurationMonths(): int
    {
        return $this->startDate->diff($this->endDate)->m + ($this->startDate->diff($this->endDate)->y * 12);
    }

    public function equals(self $other): bool
    {
        return $this->startDate == $other->startDate && $this->endDate == $other->endDate;
    }
}
