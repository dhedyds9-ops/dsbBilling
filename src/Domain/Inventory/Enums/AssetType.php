<?php

namespace Src\Domain\Inventory\Enums;

enum AssetType: string
{
    case OLT = 'olt';
    case PON = 'pon';
    case ODC = 'odc';
    case ODP = 'odp';
    case ONU = 'onu';
    case FIBER_CABLE = 'fiber_cable';
    case PATCH_CORD = 'patch_cord';
    case PIGTAIL = 'pigtail';
    case SPLITTER = 'splitter';
    case ADAPTER = 'adapter';
    case Connector = 'connector';
    case CASSETTE = 'cassette';
    case CLOSURE = 'closure';
    case TERMINAL_BOX = 'terminal_box';
    case RACK = 'rack';
    case UPS = 'ups';
    case BATTERY = 'battery';
    case GENERATOR = 'generator';
    case AC = 'ac';
    case TOOLS = 'tools';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::OLT => 'Optical Line Termination',
            self::PON => 'PON Card',
            self::ODC => 'Optical Distribution Cabinet',
            self::ODP => 'Optical Distribution Point',
            self::ONU => 'Optical Network Unit',
            self::FIBER_CABLE => 'Fiber Cable',
            self::PATCH_CORD => 'Patch Cord',
            self::PIGTAIL => 'Pigtail',
            self::SPLITTER => 'Splitter',
            self::ADAPTER => 'Adapter',
            self::Connector => 'Connector',
            self::CASSETTE => 'Cassette',
            self::CLOSURE => 'Closure',
            self::TERMINAL_BOX => 'Terminal Box',
            self::RACK => 'Rack',
            self::UPS => 'UPS',
            self::BATTERY => 'Battery',
            self::GENERATOR => 'Generator',
            self::AC => 'Air Conditioner',
            self::TOOLS => 'Tools',
            self::OTHER => 'Other',
        };
    }

    public function requiresSerialNumber(): bool
    {
        return in_array($this, [
            self::OLT,
            self::PON,
            self::ODC,
            self::ODP,
            self::ONU,
        ]);
    }

    public function requiresMacAddress(): bool
    {
        return in_array($this, [self::OLT, self::ONU]);
    }

    public function isRackMountable(): bool
    {
        return in_array($this, [self::OLT, self::PON, self::ODC, self::RACK, self::UPS]);
    }
}
