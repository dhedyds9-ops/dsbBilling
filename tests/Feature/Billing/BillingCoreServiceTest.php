<?php

namespace Tests\Feature\Billing;

use App\Models\CRM\Customer;
use App\Models\User;
use App\Services\Billing\InvoiceService;
use App\Services\Billing\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Src\Domain\Billing\Events\InvoiceCreatedEvent;
use Src\Domain\Billing\Events\InvoicePaidEvent;
use Src\Domain\Billing\Events\PaymentReceivedEvent;
use Src\Domain\Billing\Events\PaymentVerifiedEvent;
use Tests\TestCase;

class BillingCoreServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->customer = Customer::create([
            'code' => 'CUST-001',
            'name' => 'Customer Test',
            'phone' => '081234567890',
            'email' => 'customer@example.com',
            'status' => 'active',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);
    }

    public function test_invoice_service_can_create_invoice_with_items(): void
    {
        Event::fake();

        /** @var InvoiceService $invoiceService */
        $invoiceService = app(InvoiceService::class);

        $items = [
            ['description' => 'Langganan Internet 100Mbps', 'quantity' => 1, 'unit_price' => 250000],
            ['description' => 'Biaya Installasi', 'quantity' => 1, 'unit_price' => 150000],
        ];

        $invoice = $invoiceService->createInvoice(
            customerId: $this->customer->id,
            userId: $this->admin->id,
            items: $items,
        );

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'customer_id' => $this->customer->id,
            'total_amount' => 400000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        $this->assertCount(2, $invoice->items);
        $this->assertEquals(250000, $invoice->items[0]->subtotal);
        $this->assertEquals(150000, $invoice->items[1]->subtotal);

        Event::assertDispatched(InvoiceCreatedEvent::class, function ($e) use ($invoice) {
            return $e->invoiceId === $invoice->uuid
                && $e->customerId === $this->customer->id;
        });
    }

    public function test_invoice_service_can_update_invoice(): void
    {
        Event::fake();

        /** @var InvoiceService $invoiceService */
        $invoiceService = app(InvoiceService::class);

        $initialItems = [
            ['description' => 'Item A', 'quantity' => 1, 'unit_price' => 100000],
        ];

        $invoice = $invoiceService->createInvoice(
            customerId: $this->customer->id,
            userId: $this->admin->id,
            items: $initialItems,
        );

        $updatedItems = [
            ['description' => 'Item A Updated', 'quantity' => 2, 'unit_price' => 100000],
            ['description' => 'Item B Baru', 'quantity' => 1, 'unit_price' => 50000],
        ];

        $updatedInvoice = $invoiceService->updateInvoice(
            invoice: $invoice,
            customerId: $this->customer->id,
            userId: $this->admin->id,
            items: $updatedItems,
        );

        $this->assertEquals(250000, $updatedInvoice->total_amount);
        $this->assertCount(2, $updatedInvoice->fresh()->items);
        $this->assertEquals('Item A Updated', $updatedInvoice->fresh()->items[0]->description);
        $this->assertEquals(200000, $updatedInvoice->fresh()->items[0]->subtotal);
    }

    public function test_payment_service_can_create_payment_and_apply_to_invoice(): void
    {
        Event::fake();

        /** @var InvoiceService $invoiceService */
        $invoiceService = app(InvoiceService::class);
        /** @var PaymentService $paymentService */
        $paymentService = app(PaymentService::class);

        $invoice = $invoiceService->createInvoice(
            customerId: $this->customer->id,
            userId: $this->admin->id,
            items: [
                ['description' => 'Tagihan Bulanan', 'quantity' => 1, 'unit_price' => 300000],
            ],
        );

        $payment = $paymentService->createPayment(
            customerId: $this->customer->id,
            amount: 300000,
            userId: $this->admin->id,
            invoiceIds: [$invoice->id],
            status: 'success',
            method: 'bank_transfer',
        );

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'customer_id' => $this->customer->id,
            'amount' => 300000,
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('invoice_payment', [
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
        ]);

        $invoice->refresh();
        $this->assertEquals(300000, $invoice->paid_amount);
        $this->assertEquals('paid', $invoice->status);

        Event::assertDispatched(PaymentReceivedEvent::class);
        Event::assertDispatched(PaymentVerifiedEvent::class);
        Event::assertDispatched(InvoicePaidEvent::class);
    }

    public function test_payment_service_partial_payment(): void
    {
        Event::fake();

        /** @var InvoiceService $invoiceService */
        $invoiceService = app(InvoiceService::class);
        /** @var PaymentService $paymentService */
        $paymentService = app(PaymentService::class);

        $invoice = $invoiceService->createInvoice(
            customerId: $this->customer->id,
            userId: $this->admin->id,
            items: [
                ['description' => 'Tagihan', 'quantity' => 1, 'unit_price' => 500000],
            ],
        );

        $payment = $paymentService->createPayment(
            customerId: $this->customer->id,
            amount: 200000,
            userId: $this->admin->id,
            invoiceIds: [$invoice->id],
            status: 'success',
        );

        $invoice->refresh();
        $this->assertEquals(200000, $invoice->paid_amount);
        $this->assertEquals('partial', $invoice->status);
    }

    public function test_payment_service_payment_pending_does_not_apply(): void
    {
        Event::fake();

        /** @var InvoiceService $invoiceService */
        $invoiceService = app(InvoiceService::class);
        /** @var PaymentService $paymentService */
        $paymentService = app(PaymentService::class);

        $invoice = $invoiceService->createInvoice(
            customerId: $this->customer->id,
            userId: $this->admin->id,
            items: [
                ['description' => 'Tagihan', 'quantity' => 1, 'unit_price' => 300000],
            ],
        );

        $paymentService->createPayment(
            customerId: $this->customer->id,
            amount: 300000,
            userId: $this->admin->id,
            invoiceIds: [$invoice->id],
            status: 'pending',
        );

        $invoice->refresh();
        $this->assertEquals(0, $invoice->paid_amount);
        $this->assertEquals('unpaid', $invoice->status);

        Event::assertDispatched(PaymentReceivedEvent::class);
        Event::assertNotDispatched(PaymentVerifiedEvent::class);
        Event::assertNotDispatched(InvoicePaidEvent::class);
    }

    public function test_customer_id_validation_must_reference_members_table(): void
    {
        Event::fake();

        /** @var InvoiceService $invoiceService */
        $invoiceService = app(InvoiceService::class);

        $invalidCustomerId = 999999;

        $this->expectException(\Exception::class);

        $invoiceService->createInvoice(
            customerId: $invalidCustomerId,
            userId: $this->admin->id,
            items: [
                ['description' => 'Test', 'quantity' => 1, 'unit_price' => 100000],
            ],
        );
    }
}
