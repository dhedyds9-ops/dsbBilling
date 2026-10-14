<?php

namespace Src\Domain\Inventory\Enums;

enum MovementType: string
{
    case RECEIVE = 'receive';
    case ISSUE = 'issue';
    case TRANSFER_IN = 'transfer_in';
    case TRANSFER_OUT = 'transfer_out';
    case ADJUSTMENT_IN = 'adjustment_in';
    case ADJUSTMENT_OUT = 'adjustment_out';
    case RETURN_IN = 'return_in';
    case RETURN_OUT = 'return_out';
    case REPAIR_IN = 'repair_in';
    case REPAIR_OUT = 'repair_out';
    case SCRAP = 'scrap';
    case SOLD = 'sold';

    public function label(): string
    {
        return match($this) {
            self::RECEIVE => 'Receive',
            self::ISSUE => 'Issue',
            self::TRANSFER_IN => 'Transfer In',
            self::TRANSFER_OUT => 'Transfer Out',
            self::ADJUSTMENT_IN => 'Adjustment In',
            self::ADJUSTMENT_OUT => 'Adjustment Out',
            self::RETURN_IN => 'Return In',
            self::RETURN_OUT => 'Return Out',
            self::REPAIR_IN => 'Repair In',
            self::REPAIR_OUT => 'Repair Out',
            self::SCRAP => 'Scrap',
            self::SOLD => 'Sold',
        };
    }

    public function isIncoming(): bool
    {
        return in_array($this, [
            self::RECEIVE,
            self::TRANSFER_IN,
            self::ADJUSTMENT_IN,
            self::RETURN_IN,
            self::REPAIR_IN,
        ]);
    }

    public function isOutgoing(): bool
    {
        return in_array($this, [
            self::ISSUE,
            self::TRANSFER_OUT,
            self::ADJUSTMENT_OUT,
            self::RETURN_OUT,
            self::REPAIR_OUT,
            self::SCRAP,
            self::SOLD,
        ]);
    }
}
