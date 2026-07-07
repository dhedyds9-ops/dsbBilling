<?php

namespace Src\Domain\Analytics\Enums;

enum HeatmapLayer: string
{
    case COVERAGE = 'coverage';
    case CAPACITY = 'capacity';
    case CUSTOMERS = 'customers';
    case GROWTH = 'growth';
    case CONGESTION = 'congestion';

    public function label(): string
    {
        return match($this) {
            self::COVERAGE => 'Coverage Layer',
            self::CAPACITY => 'Capacity Layer',
            self::CUSTOMERS => 'Customer Layer',
            self::GROWTH => 'Growth Layer',
            self::CONGESTION => 'Congestion Layer',
        };
    }

    public function colorScale(): array
    {
        return match($this) {
            self::COVERAGE => ['#ff0000', '#ffff00', '#00ff00'],
            self::CAPACITY => ['#00ff00', '#ffff00', '#ff0000'],
            self::CUSTOMERS => ['#ffffff', '#ffaa00', '#ff5500'],
            self::GROWTH => ['#ffffff', '#00ff00', '#006600'],
            self::CONGESTION => ['#00ff00', '#ffff00', '#ff0000'],
        };
    }
}
