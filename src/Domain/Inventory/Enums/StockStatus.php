<?php

namespace Src\Domain\Inventory\Enums;

enum StockStatus: string
{
    case AVAILABLE = 'available';
    case RESERVED = 'reserved';
    case QUARANTINE = 'quarantine';
    case DAMAGED = 'damaged';
    case EXPIRED = 'expired';
    case OBSOLETE = 'obsolete';

    public function label(): string
    {
        return match($this) {
            self::AVAILABLE => 'Available',
            self::RESERVED => 'Reserved',
            self::QUARANTINE => 'Quarantine',
            self::DAMAGED => 'Damaged',
            self::EXPIRED => 'Expired',
            self::OBSOLETE => 'Obsolete',
        };
    }

    public function canBeSold(): bool
    {
        return $this === self::AVAILABLE;
    }

    public function requiresInspection(): bool
    {
        return in_array($this, [self::QUARANTINE, self::DAMAGED, self::EXPIRED]);
    }
}
