<?php

namespace App\Enums;

enum JobFunction: string
{
    case NOC = 'NOC';
    case TECHNICIAN = 'TECHNICIAN';
    case PENGURUS = 'PENGURUS';
    case SALES = 'SALES';

    public function label(): string
    {
        return match($this) {
            self::NOC           => 'NOC',
            self::TECHNICIAN    => 'Technician',
            self::PENGURUS      => 'Pengurus',
            self::SALES         => 'Sales',
        };
    }
}
