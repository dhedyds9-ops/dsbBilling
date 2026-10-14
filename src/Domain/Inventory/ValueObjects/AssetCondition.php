<?php

namespace Src\Domain\Inventory\ValueObjects;

use InvalidArgumentException;

enum AssetCondition: string
{
    case NEW = 'new';
    case GOOD = 'good';
    case FAIR = 'fair';
    case POOR = 'poor';
    case DAMAGED = 'damaged';
    case SCRAP = 'scrap';

    public function label(): string
    {
        return match($this) {
            self::NEW => 'Baru',
            self::GOOD => 'Baik',
            self::FAIR => 'Cukup',
            self::POOR => 'Buruk',
            self::DAMAGED => 'Rusak',
            self::SCRAP => 'Sampah',
        };
    }

    public function canBeAssigned(): bool
    {
        return in_array($this, [self::NEW, self::GOOD, self::FAIR]);
    }

    public function requiresMaintenance(): bool
    {
        return in_array($this, [self::POOR, self::DAMAGED]);
    }

    public function depreciationRate(): float
    {
        return match($this) {
            self::NEW => 1.0,
            self::GOOD => 0.8,
            self::FAIR => 0.6,
            self::POOR => 0.3,
            self::DAMAGED => 0.1,
            self::SCRAP => 0.0,
        };
    }
}
