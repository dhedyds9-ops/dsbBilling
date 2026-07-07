<?php

namespace Src\Domain\Tenant\ValueObjects;

class LicenseKey
{
    public function __construct(
        public readonly string $key,
        public readonly string $signature,
        public readonly string $algorithm = 'RSA256'
    ) {}

    public function isValid(): bool
    {
        return !empty($this->key) && !empty($this->signature);
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'signature' => $this->signature,
            'algorithm' => $this->algorithm,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            key: $data['key'] ?? '',
            signature: $data['signature'] ?? '',
            algorithm: $data['algorithm'] ?? 'RSA256'
        );
    }
}
