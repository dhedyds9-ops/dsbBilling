<?php

namespace Src\Domain\Billing;

use Src\Domain\SharedKernel\ValueObjects\Money;

readonly class InvoiceItem
{
    public function __construct(
        public string $description,
        public int $quantity,
        public Money $unitPrice,
        public Money $total,
        public ?string $serviceId = null,
        public ?string $productId = null
    ) {}
}
