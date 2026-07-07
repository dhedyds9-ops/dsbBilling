<?php

namespace Src\Domain\Payment;

interface PaymentGatewayAdapterInterface
{
    public function createPayment(Payment $payment): array;
    public function checkPaymentStatus(string $transactionId): PaymentStatus;
    public function refundPayment(Payment $payment): bool;
}
