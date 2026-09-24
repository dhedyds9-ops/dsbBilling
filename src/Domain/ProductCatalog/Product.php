<?php

namespace Src\Domain\ProductCatalog;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Money;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

enum ProductType: string
{
    case HARDWARE = 'hardware';
    case SOFTWARE = 'software';
    case SERVICE_ADDON = 'service_addon';
}

class Product extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public string $name,
        public ?string $description = null,
        public ProductType $type,
        public Money $price,
        public int $stock = 0,
        public array $attributes = [],
        public bool $isActive = true
    ) {}

    public static function create(
        Uuid $id,
        string $name,
        ?string $description,
        ProductType $type,
        Money $price,
        int $stock = 0,
        array $attributes = []
    ): self {
        return new self($id, $name, $description, $type, $price, $stock, $attributes);
    }

    public function updateStock(int $quantity): void
    {
        $this->stock = max(0, $this->stock + $quantity);
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
