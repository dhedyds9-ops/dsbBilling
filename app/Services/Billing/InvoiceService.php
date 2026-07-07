<?php

namespace App\Services\Billing;

use App\Models\Billing\Invoice;
use App\Models\Billing\InvoiceItem;
use App\Models\Customer\Contract;
use App\Repositories\Billing\InvoiceItemRepository;
use App\Repositories\Billing\InvoiceRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Src\Domain\Billing\Events\InvoiceCreatedEvent;
use Src\Domain\Billing\Events\InvoicePaidEvent;

class InvoiceService
{
    public function __construct(
        protected InvoiceRepository $invoiceRepository,
        protected InvoiceItemRepository $invoiceItemRepository,
    ) {}

    public function createInvoice(
        Contract $contract,
        int $userId,
        array $items = [],
        ?\DateTimeInterface $issueDate = null,
        ?\DateTimeInterface $dueDate = null,
    ): Invoice {
        return DB::transaction(function () use ($contract, $userId, $items, $issueDate, $dueDate) {
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT);
            
            $invoice = $this->invoiceRepository->create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => $contract->customer_id,
                'contract_id' => $contract->id,
                'invoice_number' => $invoiceNumber,
                'issue_date' => $issueDate ?? now(),
                'due_date' => $dueDate ?? now()->addDays(7),
                'total_amount' => 0,
                'paid_amount' => 0,
                'currency' => 'IDR',
                'status' => 'draft',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $totalAmount = 0;

            foreach ($items as $itemData) {
                $subtotal = ($itemData['quantity'] ?? 1) * ($itemData['unit_price'] ?? 0);
                $totalAmount += $subtotal;

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

            $invoice->update(['total_amount' => $totalAmount, 'status' => 'unpaid']);

            Event::dispatch(new InvoiceCreatedEvent(
                $invoice->uuid,
                $invoice->customer_id,
                $invoice->contract_id,
            ));

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
}
