<?php

namespace Src\Domain\Inventory\ValueObjects;

readonly class QRCode
{
    public function __construct(
        public string $value,
        public int $size = 300,
        public string $errorCorrection = 'M'
    ) {
        if (empty($value)) {
            throw new \InvalidArgumentException("QR Code value cannot be empty");
        }
    }

    public static function forAsset(AssetCode $assetCode, SerialNumber $serial): self
    {
        $data = json_encode([
            'asset' => $assetCode->toString(),
            'serial' => $serial->value,
            'timestamp' => time()
        ]);
        return new self($data);
    }

    public static function generate(string $data): self
    {
        return new self($data);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
