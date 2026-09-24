<?php

namespace Src\Domain\Workforce\ValueObjects;

readonly class WorkingHour {
    public function __construct(
        public \DateTimeImmutable $startTime,
        public \DateTimeImmutable $endTime,
        public bool $isWorkingDay = true,
    ) {
        if ($startTime >= $endTime) {
            throw new \InvalidArgumentException("Start time must be before end time");
        }
    }

    public function toArray(): array {
        return [
            'start_time' => $this->startTime->format('H:i'),
            'end_time' => $this->endTime->format('H:i'),
            'is_working_day' => $this->isWorkingDay,
        ];
    }
}
