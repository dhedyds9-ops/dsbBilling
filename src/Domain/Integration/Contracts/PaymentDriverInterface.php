<?php

namespace Src\Domain\Integration\Contracts;

interface PaymentDriverInterface
{
    public function connect(): bool;
    public function disconnect(): void;
    public function createInvoice(array $data): array;
    public function getInvoiceStatus(string $invoiceId): ?array;
    public function cancelInvoice(string $invoiceId): bool;
    public function verifyPayment(string $paymentId): ?array;
    public function getStatus(): array;
    public function getHealth(): array;
}
