<?php

namespace Src\Domain\Analytics\Enums;

enum ExpansionPriority: string
{
    case CRITICAL = 'critical';
    case HIGH = 'high';
    case MEDIUM = 'medium';
    case LOW = 'low';

    public function label(): string
    {
        return match($this) {
            self::CRITICAL => 'Kritis',
            self::HIGH => 'Tinggi',
            self::MEDIUM => 'Sedang',
            self::LOW => 'Rendah',
        };
    }

    public function score(): int
    {
        return match($this) {
            self::CRITICAL => 4,
            self::HIGH => 3,
            self::MEDIUM => 2,
            self::LOW => 1,
        };
    }
}
