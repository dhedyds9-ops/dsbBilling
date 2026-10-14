<?php

namespace Src\Domain\Outage\Enums;

enum NodeType: string
{
    case OLT = 'olt';
    case PON_PORT = 'pon_port';
    case ODC = 'odc';
    case FIBER_CABLE = 'fiber_cable';
    case FIBER_CORE = 'fiber_core';
    case ODP = 'odp';
    case SPLITTER = 'splitter';
    case SPLITTER_PORT = 'splitter_port';
    case ONU = 'onu';
    case ROUTER = 'router';
    case SWITCH = 'switch';
    case ACCESS_POINT = 'access_point';

    public function label(): string
    {
        return match($this) {
            self::OLT => 'OLT',
            self::PON_PORT => 'Port PON',
            self::ODC => 'ODC',
            self::FIBER_CABLE => 'Kabel Fiber',
            self::FIBER_CORE => 'Core Fiber',
            self::ODP => 'ODP',
            self::SPLITTER => 'Splitter',
            self::SPLITTER_PORT => 'Port Splitter',
            self::ONU => 'ONU',
            self::ROUTER => 'Router',
            self::SWITCH => 'Switch',
            self::ACCESS_POINT => 'Access Point',
        };
    }

    public function tier(): int
    {
        return match($this) {
            self::OLT => 1,
            self::PON_PORT => 2,
            self::ODC => 3,
            self::FIBER_CABLE, self::FIBER_CORE => 4,
            self::ODP => 5,
            self::SPLITTER, self::SPLITTER_PORT => 6,
            self::ONU => 7,
            self::ROUTER, self::SWITCH, self::ACCESS_POINT => 8,
        };
    }
}
