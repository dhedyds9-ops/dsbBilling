<?php

namespace Src\Domain\BusinessIntelligence\ValueObjects;

use DateTimeImmutable;
use DateInterval;

class TimeRange
{
    public function __construct(
        public readonly DateTimeImmutable $startDate,
        public readonly DateTimeImmutable $endDate
    ) {
        if ($startDate > $endDate) {
            throw new \InvalidArgumentException('Start date must be before or equal to end date');
        }
    }

    public static function create(DateTimeImmutable $startDate, DateTimeImmutable $endDate): self
    {
        return new self($startDate, $endDate);
    }

    public static function today(): self
    {
        $today = new DateTimeImmutable('today');
        return new self($today, $today);
    }

    public static function thisWeek(): self
    {
        $today = new DateTimeImmutable('today');
        $startOfWeek = $today->modify('monday this week');
        $endOfWeek = $today->modify('sunday this week');
        return new self($startOfWeek, $endOfWeek);
    }

    public static function thisMonth(): self
    {
        $today = new DateTimeImmutable('today');
        $startOfMonth = $today->modify('first day of this month');
        $endOfMonth = $today->modify('last day of this month');
        return new self($startOfMonth, $endOfMonth);
    }

    public static function thisQuarter(): self
    {
        $today = new DateTimeImmutable('today');
        $quarter = (int) ceil($today->format('n') / 3);
        $startOfQuarter = new DateTimeImmutable($today->format('Y') . '-' . (($quarter - 1) * 3 + 1) . '-01');
        $endOfQuarter = $startOfQuarter->modify('+3 months -1 day');
        return new self($startOfQuarter, $endOfQuarter);
    }

    public static function thisYear(): self
    {
        $today = new DateTimeImmutable('today');
        $startOfYear = new DateTimeImmutable($today->format('Y') . '-01-01');
        $endOfYear = new DateTimeImmutable($today->format('Y') . '-12-31');
        return new self($startOfYear, $endOfYear);
    }

    public static function lastNDays(int $days): self
    {
        $today = new DateTimeImmutable('today');
        $startDate = $today->modify("-{$days} days");
        return new self($startDate, $today);
    }

    public static function lastNMonths(int $months): self
    {
        $today = new DateTimeImmutable('today');
        $endDate = $today->modify('last day of this month');
        $startDate = $today->modify("-{$months} months")->modify('first day of this month');
        return new self($startDate, $endDate);
    }

    public function getDays(): int
    {
        return $this->startDate->diff($this->endDate)->days + 1;
    }

    public function getMonths(): int
    {
        return (int) $this->startDate->diff($this->endDate)->format('%m') + 1;
    }

    public function includes(DateTimeImmutable $date): bool
    {
        return $date >= $this->startDate && $date <= $this->endDate;
    }

    public function overlaps(TimeRange $other): bool
    {
        return $this->startDate <= $other->endDate && $this->endDate >= $other->startDate;
    }

    public function toArray(): array
    {
        return [
            'start_date' => $this->startDate->format('Y-m-d'),
            'end_date' => $this->endDate->format('Y-m-d'),
        ];
    }
}
