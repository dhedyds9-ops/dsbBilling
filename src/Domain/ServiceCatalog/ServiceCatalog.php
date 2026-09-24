<?php

namespace Src\Domain\ServiceCatalog;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class ServiceCatalog extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public string $name,
        public ?string $description = null,
        public bool $isActive = true
    ) {}

    public static function create(Uuid $id, string $name, ?string $description = null): self
    {
        return new self($id, $name, $description);
    }

    public function update(string $name, ?string $description = null): void
    {
        $this->name = $name;
        $this->description = $description;
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
