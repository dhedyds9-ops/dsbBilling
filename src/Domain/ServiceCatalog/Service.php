<?php

namespace Src\Domain\ServiceCatalog;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Money;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

enum ServiceType: string
{
    case PPPOE = 'pppoe';
    case HOTSPOT = 'hotspot';
    case STATIC_IP = 'static_ip';
    case VOUCHER = 'voucher';
}

class Service extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $serviceCatalogId,
        public string $name,
        public ?string $description = null,
        public ServiceType $type,
        public Money $price,
        public array $attributes = [],
        public bool $isActive = true
    ) {}

    public static function create(
        Uuid $id,
        Uuid $serviceCatalogId,
        string $name,
        ?string $description,
        ServiceType $type,
        Money $price,
        array $attributes = []
    ): self {
        return new self($id, $serviceCatalogId, $name, $description, $type, $price, $attributes);
    }

    public function update(
        string $name,
        ?string $description,
        ServiceType $type,
        Money $price,
        array $attributes = []
    ): void {
        $this->name = $name;
        $this->description = $description;
        $this->type = $type;
        $this->price = $price;
        $this->attributes = $attributes;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }
}
