<?php

namespace Src\Domain\FiberCapacity\Enums;

enum CapacityStatus: string
{
    case OPTIMAL = 'optimal';
    case WARNING = 'warning';
    case CRITICAL = 'critical';
    case FULL = 'full';
    case UNKNOWN = 'unknown';

    public function label(): string
    {
        return match($this) {
            self::OPTIMAL => 'Optimal',
            self::WARNING => 'Peringatan',
            self::CRITICAL => 'Kritis',
            self::FULL => 'Penuh',
            self::UNKNOWN => 'Tidak Diketahui',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::OPTIMAL => '#28a745',
            self::WARNING => '#ffc107',
            self::CRITICAL => '#dc3545',
            self::FULL => '#6c757d',
            self::UNKNOWN => '#6c757d',
        };
    }

    public static function fromPercentage(float $percentage): self
    {
        return match(true) {
            $percentage >= 90 => self::CRITICAL,
            $percentage >= 75 => self::WARNING,
            $percentage >= 0 => self::OPTIMAL,
            default => self::UNKNOWN,
        };
    }
}
