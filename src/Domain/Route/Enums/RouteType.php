<?php

namespace Src\Domain\Route\Enums;

enum RouteType: string
{
    case SHORTEST = 'shortest';
    case FASTEST = 'fastest';
    case ALTERNATIVE = 'alternative';
    case BACKUP = 'backup';
    case REDUNDANT = 'redundant';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match($this) {
            self::SHORTEST => 'Jalur Terpendek',
            self::FASTEST => 'Jalur Tercepat',
            self::ALTERNATIVE => 'Jalur Alternatif',
            self::BACKUP => 'Jalur Cadangan',
            self::REDUNDANT => 'Jalur Redundan',
            self::CUSTOM => 'Jalur Kustom',
        };
    }

    public function priority(): int
    {
        return match($this) {
            self::SHORTEST => 1,
            self::FASTEST => 2,
            self::BACKUP => 3,
            self::REDUNDANT => 4,
            self::ALTERNATIVE => 5,
            self::CUSTOM => 6,
        };
    }
}
