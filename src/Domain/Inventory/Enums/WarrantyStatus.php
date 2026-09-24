<?php

namespace Src\Domain\Inventory\Enums;

enum WarrantyStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case VOID = 'void';
    case TRANSFERRED = 'transferred';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::EXPIRED => 'Expired',
            self::VOID => 'Void',
            self::TRANSFERRED => 'Transferred',
        };
    }

    public function canClaim(): bool
    {
        return $this === self::ACTIVE;
    }

    public function requiresNotification(): bool
    {
        return $this === self::ACTIVE;
    }
}
