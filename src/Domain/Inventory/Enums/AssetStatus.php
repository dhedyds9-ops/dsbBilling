<?php

namespace Src\Domain\Inventory\Enums;

enum AssetStatus: string
{
    case PENDING = 'pending';
    case IN_STOCK = 'in_stock';
    case RESERVED = 'reserved';
    case ASSIGNED = 'assigned';
    case INSTALLED = 'installed';
    case IN_REPAIR = 'in_repair';
    case IN_MAINTENANCE = 'in_maintenance';
    case IN_TRANSIT = 'in_transit';
    case RETIRED = 'retired';
    case DISPOSED = 'disposed';
    case LOST = 'lost';
    case STOLEN = 'stolen';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::IN_STOCK => 'In Stock',
            self::RESERVED => 'Reserved',
            self::ASSIGNED => 'Assigned',
            self::INSTALLED => 'Installed',
            self::IN_REPAIR => 'In Repair',
            self::IN_MAINTENANCE => 'In Maintenance',
            self::IN_TRANSIT => 'In Transit',
            self::RETIRED => 'Retired',
            self::DISPOSED => 'Disposed',
            self::LOST => 'Lost',
            self::STOLEN => 'Stolen',
        };
    }

    public function canBeAssigned(): bool
    {
        return in_array($this, [self::IN_STOCK, self::RESERVED]);
    }

    public function canBeTransferred(): bool
    {
        return in_array($this, [self::IN_STOCK, self::ASSIGNED, self::INSTALLED]);
    }

    public function canBeRetired(): bool
    {
        return !in_array($this, [self::DISPOSED, self::RETIRED]);
    }

    public function isActive(): bool
    {
        return in_array($this, [self::IN_STOCK, self::RESERVED, self::ASSIGNED, self::INSTALLED]);
    }

    public function isInactive(): bool
    {
        return in_array($this, [self::RETIRED, self::DISPOSED, self::LOST, self::STOLEN]);
    }
}
