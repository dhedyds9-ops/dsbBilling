<?php

namespace Src\Domain\Inventory\Enums;

enum WarehouseType: string
{
    case MAIN = 'main';
    case BRANCH = 'branch';
    case VENDOR = 'vendor';
    case CUSTOMER = 'customer';
    case TECHNCIAN = 'technician';
    case STAGING = 'staging';
    case QUARANTINE = 'quarantine';
    case REPAIR = 'repair';

    public function label(): string
    {
        return match($this) {
            self::MAIN => 'Main Warehouse',
            self::BRANCH => 'Branch Warehouse',
            self::VENDOR => 'Vendor Warehouse',
            self::CUSTOMER => 'Customer Location',
            self::TECHNCIAN => 'Technician Stock',
            self::STAGING => 'Staging Area',
            self::QUARANTINE => 'Quarantine',
            self::REPAIR => 'Repair Center',
        };
    }

    public function isInternal(): bool
    {
        return in_array($this, [self::MAIN, self::BRANCH, self::STAGING, self::QUARANTINE, self::REPAIR]);
    }

    public function canReceiveStock(): bool
    {
        return in_array($this, [self::MAIN, self::BRANCH, self::STAGING]);
    }

    public function canIssueStock(): bool
    {
        return in_array($this, [self::MAIN, self::BRANCH, self::STAGING, self::TECHNCIAN]);
    }
}
