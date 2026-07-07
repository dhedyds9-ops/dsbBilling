<?php

namespace Src\Domain\Inventory\ValueObjects;

use InvalidArgumentException;

readonly class MACAddress
{
    public function __construct(
        public string $value,
        public ?string $type = null // OUI, NIC, etc.
    ) {
        $normalized = self::normalize($value);
        if (!self::isValid($normalized)) {
            throw new InvalidArgumentException("Invalid MAC address: {$value}");
        }
        $this->value = $normalized;
    }

    public static function isValid(string $mac): bool
    {
        // Accept formats: XX:XX:XX:XX:XX:XX, XX-XX-XX-XX-XX-XX, XXXXXXXXXXXX
        return (bool) preg_match('/^([0-9A-Fa-f]{2}){6}$/', $mac);
    }

    public static function normalize(string $mac): string
    {
        return strtoupper(str_replace([':', '-', '.'], '', $mac));
    }

    public static function generate(): self
    {
        $mac = sprintf(
            '%02X:%02X:%02X:%02X:%02X:%02X',
            random_int(0, 255),
            random_int(0, 255),
            random_int(0, 255),
            random_int(0, 255),
            random_int(0, 255),
            random_int(0, 255)
        );
        return new self($mac);
    }

    public function getOUI(): string
    {
        return substr($this->normalize($this->value), 0, 6);
    }

    public function toString(string $separator = ':'): string
    {
        $normalized = self::normalize($this->value);
        return implode($separator, str_split($normalized, 2));
    }

    public function equals(self $other): bool
    {
        return $this->normalize($this->value) === $this->normalize($other->value);
    }

    private function normalize(string $value): string
    {
        return self::normalize($value);
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
