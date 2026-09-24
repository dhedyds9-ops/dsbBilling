<?php

namespace Tests\Unit\Domain\Billing;

use Tests\TestCase;
use App\Models\Billing\Invoice;
use App\Models\Billing\InvoiceItem;
use App\Models\CRM\Customer;
use App\Models\Master\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_total_amount_is_calculated_correctly()
    {
        // 1. Arrange: Create a customer and invoice
        $customer = Customer::create([
            'code' => 'CUST-001',
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'status' => 'active'
        ]);
        
        $invoice = Invoice::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-202608-001',
            'status' => 'unpaid',
            'due_date' => now()->addDays(7),
            'issue_date' => now(),
            'total_amount' => 0,
        ]);

        // Add 2 items: 100k and 50k
        InvoiceItem::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'invoice_id' => $invoice->id,
            'description' => 'Paket Internet 20Mbps',
            'unit_price' => 100000,
            'quantity' => 1,
            'subtotal' => 100000,
        ]);

        InvoiceItem::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'invoice_id' => $invoice->id,
            'description' => 'Sewa Router',
            'unit_price' => 50000,
            'quantity' => 1,
            'subtotal' => 50000,
        ]);

        // 2. Act: Calculate totals
        $invoice->total_amount = $invoice->items()->sum('subtotal');
        $invoice->save();

        // 3. Assert
        $this->assertEquals(150000, $invoice->total_amount);
    }
}
