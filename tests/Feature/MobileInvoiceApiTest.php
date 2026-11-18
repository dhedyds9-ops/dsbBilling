<?php

namespace Tests\Feature;

use App\Models\Billing\Invoice;
use App\Models\CRM\Customer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MobileInvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_reseller_can_list_and_pay_invoice_cash(): void
    {
        $role = Role::firstOrCreate(['name' => 'reseller'], ['display_name' => 'Reseller', 'guard_name' => 'web']);
        $reseller = User::factory()->create(['is_active' => true]);
        $reseller->roles()->attach($role->id);

        $customer = Customer::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Dedi Surya Test',
            'code' => '2026110401',
            'phone' => '081234567890',
            'email' => 'dedi@test.com',
            'address' => 'Jl Test No 1',
            'status' => 'active',
            'reseller_id' => $reseller->id,
        ]);

        $invoice = Invoice::create([
            'uuid' => (string) Str::uuid(),
            'invoice_number' => 'INV-TEST-0001',
            'customer_id' => $customer->id,
            'reseller_id' => $reseller->id,
            'issue_date' => now(),
            'due_date' => now()->addDays(7),
            'total_amount' => 150000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        // 1. Reseller lists invoices
        $response = $this->actingAs($reseller, 'sanctum')
            ->getJson('/api/mobile/invoices');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.invoice_number', 'INV-TEST-0001')
            ->assertJsonPath('data.0.status', 'unpaid');

        // 2. Customer specific invoices endpoint
        $custResponse = $this->actingAs($reseller, 'sanctum')
            ->getJson("/api/mobile/customers/{$customer->id}/invoices");

        $custResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.invoice_number', 'INV-TEST-0001');

        // 3. Pay cash
        $payResponse = $this->actingAs($reseller, 'sanctum')
            ->postJson("/api/mobile/invoices/{$invoice->id}/pay-cash", [
                'amount' => 150000,
            ]);

        $payResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals('paid', $invoice->fresh()->status);
    }

    public function test_technician_cannot_list_or_pay_invoices(): void
    {
        $role = Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Teknisi', 'guard_name' => 'web']);
        $technician = User::factory()->create(['is_active' => true]);
        $technician->roles()->attach($role->id);

        $customer = Customer::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Customer Test',
            'code' => '2026110402',
            'phone' => '081234567891',
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'uuid' => (string) Str::uuid(),
            'invoice_number' => 'INV-TEST-0002',
            'customer_id' => $customer->id,
            'issue_date' => now(),
            'due_date' => now()->addDays(7),
            'total_amount' => 100000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        // 1. Technician cannot list invoices
        $response = $this->actingAs($technician, 'sanctum')
            ->getJson('/api/mobile/invoices');
        $response->assertStatus(403)
            ->assertJsonPath('success', false);

        // 2. Technician cannot view customer invoices
        $custResponse = $this->actingAs($technician, 'sanctum')
            ->getJson("/api/mobile/customers/{$customer->id}/invoices");
        $custResponse->assertStatus(403)
            ->assertJsonPath('success', false);

        // 3. Technician cannot pay invoice cash
        $payResponse = $this->actingAs($technician, 'sanctum')
            ->postJson("/api/mobile/invoices/{$invoice->id}/pay-cash", [
                'amount' => 100000,
            ]);
        $payResponse->assertStatus(403)
            ->assertJsonPath('success', false);
    }
}
