<?php

namespace Src\Domain\Outage\Enums;

enum ImpactLevel: string
{
    case TOTAL = 'total';
    case PARTIAL = 'partial';
    case DEGRADED = 'degraded';
    case NONE = 'none';

    public function label(): string
    {
        return match($this) {
            self::TOTAL => 'Total',
            self::PARTIAL => 'Parsial',
            self::DEGRADED => 'Degradasi',
            self::NONE => 'Tidak Ada',
        };
    }

    public function weight(): int
    {
        return match($this) {
            self::TOTAL => 4,
            self::PARTIAL => 3,
            self::DEGRADED => 2,
            self::NONE => 1,
        };
    }

    public static function fromPercentage(int $upPercentage): self
    {
        return match(true) {
            $upPercentage == 0 => self::TOTAL,
            $upPercentage < 50 => self::PARTIAL,
            $upPercentage < 100 => self::DEGRADED,
            default => self::NONE,
        };
    }
}
