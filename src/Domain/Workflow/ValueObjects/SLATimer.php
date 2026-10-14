<?php

namespace Src\Domain\Workflow\ValueObjects;

readonly class SLATimer
{
    public function __construct(
        public ?int $hours = null,
        public ?string $pauseOnWeekend = null,
        public array $businessHours = [],
        public ?string $escalationAction = null
    ) {}

    public static function fromHours(int $hours): self
    {
        return new self(hours: $hours);
    }

    public static function withBusinessHours(array $start, array $end, bool $pauseOnWeekend = true): self
    {
        return new self(
            hours: null,
            pauseOnWeekend: $pauseOnWeekend,
            businessHours: ['start' => $start, 'end' => $end]
        );
    }

    public function isPausedOnWeekend(): bool
    {
        return $this->pauseOnWeekend === true;
    }

    public function calculateDeadline(\DateTimeImmutable $startTime): \DateTimeImmutable
    {
        if ($this->hours === null) {
            return $startTime->modify('+1 day'); // Default 24 hours
        }

        return $startTime->modify("+{$this->hours} hours");
    }

    public function isExpired(\DateTimeImmutable $deadline): bool
    {
        return new \DateTimeImmutable() > $deadline;
    }

    public function getRemainingHours(\DateTimeImmutable $deadline): float
    {
        $now = new \DateTimeImmutable();
        if ($now > $deadline) {
            return 0;
        }
        
        $diff = $now->diff($deadline);
        return ($diff->days * 24) + $diff->h + ($diff->i / 60);
    }

    public function toArray(): array
    {
        return [
            'hours' => $this->hours,
            'pause_on_weekend' => $this->pauseOnWeekend,
            'business_hours' => $this->businessHours,
            'escalation_action' => $this->escalationAction,
        ];
    }
}
