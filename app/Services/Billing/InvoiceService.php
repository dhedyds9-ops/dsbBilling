<?php

namespace App\Services\Billing;

use App\Models\Billing\Invoice;
use App\Repositories\Billing\InvoiceItemRepository;
use App\Repositories\Billing\InvoiceRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Src\Domain\Billing\Events\InvoiceCreatedEvent;
use Src\Domain\Billing\Events\InvoicePaidEvent;
use Src\Domain\Billing\Events\InvoiceOverdueEvent;

class InvoiceService
{
    public function __construct(
        protected InvoiceRepository $invoiceRepository,
        protected InvoiceItemRepository $invoiceItemRepository,
    ) {}

    public function createInvoice(
        int $customerId,
        int $userId,
        array $items = [],
        ?\DateTimeInterface $issueDate = null,
        ?\DateTimeInterface $dueDate = null,
        ?string $invoiceNumber = null,
        ?int $contractId = null,
        string $currency = 'IDR',
        string $status = 'unpaid',
    ): Invoice {
        return DB::transaction(function () use (
            $customerId, $userId, $items, $issueDate, $dueDate,
            $invoiceNumber, $contractId, $currency, $status
        ) {
            $resolvedInvoiceNumber = $invoiceNumber ?? 'INV-' . date('Ymd') . '-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT);

            $totalAmount = 0;
            foreach ($items as $itemData) {
                $totalAmount += ($itemData['quantity'] ?? 1) * ($itemData['unit_price'] ?? 0);
            }

            $invoice = $this->invoiceRepository->create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => $customerId,
                'contract_id' => $contractId,
                'invoice_number' => $resolvedInvoiceNumber,
                'issue_date' => $issueDate ?? now(),
                'due_date' => $dueDate ?? now()->addDays(7),
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'currency' => $currency,
                'status' => $status,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            foreach ($items as $itemData) {
                $subtotal = ($itemData['quantity'] ?? 1) * ($itemData['unit_price'] ?? 0);

                $this->invoiceItemRepository->create([
                    'uuid' => (string) Str::uuid(),
                    'invoice_id' => $invoice->id,
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'] ?? 1,
                    'unit_price' => $itemData['unit_price'] ?? 0,
                    'subtotal' => $subtotal,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            Event::dispatch(new InvoiceCreatedEvent(
                $invoice->uuid,
                $invoice->customer_id,
                $invoice->contract_id ?? 0,
            ));

            return $invoice;
        });
    }

    public function updateInvoice(
        Invoice $invoice,
        int $customerId,
        int $userId,
        array $items = [],
        ?\DateTimeInterface $issueDate = null,
        ?\DateTimeInterface $dueDate = null,
        ?string $invoiceNumber = null,
        ?int $contractId = null,
        ?string $currency = null,
        ?string $status = null,
    ): Invoice {
        return DB::transaction(function () use (
            $invoice, $customerId, $userId, $items, $issueDate, $dueDate,
            $invoiceNumber, $contractId, $currency, $status
        ) {
            $totalAmount = 0;
            foreach ($items as $itemData) {
                $totalAmount += ($itemData['quantity'] ?? 1) * ($itemData['unit_price'] ?? 0);
            }

            $invoice->update([
                'customer_id' => $customerId,
                'contract_id' => $contractId ?? $invoice->contract_id,
                'invoice_number' => $invoiceNumber ?? $invoice->invoice_number,
                'issue_date' => $issueDate ?? $invoice->issue_date,
                'due_date' => $dueDate ?? $invoice->due_date,
                'total_amount' => $totalAmount,
                'currency' => $currency ?? $invoice->currency,
                'status' => $status ?? $invoice->status,
                'updated_by' => $userId,
            ]);

            $invoice->items()->delete();

            foreach ($items as $itemData) {
                $subtotal = ($itemData['quantity'] ?? 1) * ($itemData['unit_price'] ?? 0);

                $this->invoiceItemRepository->create([
                    'uuid' => (string) Str::uuid(),
                    'invoice_id' => $invoice->id,
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'] ?? 1,
                    'unit_price' => $itemData['unit_price'] ?? 0,
                    'subtotal' => $subtotal,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            return $invoice;
        });
    }

    public function applyPayment(int $invoiceId, float $amount, int $userId): Invoice
    {
        return DB::transaction(function () use ($invoiceId, $amount, $userId) {
            $invoice = $this->invoiceRepository->find($invoiceId);

            $newPaidAmount = $invoice->paid_amount + $amount;
            $status = $newPaidAmount >= $invoice->total_amount ? 'paid' : 'partial';

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'status' => $status,
                'updated_by' => $userId,
            ]);

            Event::dispatch(new InvoicePaidEvent(
                $invoice->uuid,
                $invoice->customer_id,
                $amount,
            ));

            return $invoice;
        });
    }

    public function markAsOverdue(Invoice $invoice, int $userId): Invoice
    {
        if ($invoice->status !== 'overdue') {
            $invoice->update([
                'status' => 'overdue',
                'updated_by' => $userId,
            ]);

            Event::dispatch(new InvoiceOverdueEvent(
                $invoice->uuid,
                $invoice->customer_id,
                $invoice->total_amount,
            ));
        }

        return $invoice;
    }
}
