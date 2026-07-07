<?php

namespace Src\Domain\Analytics\Enums;

enum TimeRange: string
{
    case HOUR = 'hour';
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case QUARTER = 'quarter';
    case YEAR = 'year';

    public function label(): string
    {
        return match($this) {
            self::HOUR => 'Per Jam',
            self::DAY => 'Harian',
            self::WEEK => 'Mingguan',
            self::MONTH => 'Bulanan',
            self::QUARTER => 'Kuartalan',
            self::YEAR => 'Tahunan',
        };
    }

    public function seconds(): int
    {
        return match($this) {
            self::HOUR => 3600,
            self::DAY => 86400,
            self::WEEK => 604800,
            self::MONTH => 2592000,
            self::QUARTER => 7776000,
            self::YEAR => 31536000,
        };
    }
}
