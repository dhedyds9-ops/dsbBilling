<?php

namespace Src\Domain\Outage\Enums;

enum OutageSeverity: string
{
    case CRITICAL = 'critical';
    case MAJOR = 'major';
    case MINOR = 'minor';
    case COSMETIC = 'cosmetic';

    public function label(): string
    {
        return match($this) {
            self::CRITICAL => 'Kritis',
            self::MAJOR => 'Mayor',
            self::MINOR => 'Minor',
            self::COSMETIC => 'Kosmetik',
        };
    }

    public function priority(): int
    {
        return match($this) {
            self::CRITICAL => 1,
            self::MAJOR => 2,
            self::MINOR => 3,
            self::COSMETIC => 4,
        };
    }

    public function slaDeadline(): int
    {
        return match($this) {
            self::CRITICAL => 4,
            self::MAJOR => 8,
            self::MINOR => 24,
            self::COSMETIC => 72,
        };
    }

    public static function fromAffectedCount(int $count): self
    {
        return match(true) {
            $count >= 100 => self::CRITICAL,
            $count >= 50 => self::MAJOR,
            $count >= 10 => self::MINOR,
            default => self::COSMETIC,
        };
    }
}
