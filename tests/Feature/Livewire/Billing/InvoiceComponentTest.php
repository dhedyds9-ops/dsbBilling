<?php

namespace Tests\Feature\Livewire\Billing;

use App\Livewire\Billing\Invoice\Index as InvoiceIndex;
use App\Models\Billing\Invoice;
use App\Models\CRM\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_successfully()
    {
        $user = User::factory()->create();
        $role = \App\Models\Role::firstOrCreate(
            ['name' => 'administrator'],
            ['display_name' => 'Administrator', 'description' => 'Administrator Role']
        );
        $user->roles()->attach($role);

        Livewire::actingAs($user)
            ->test(InvoiceIndex::class)
            ->assertStatus(200);
    }

    public function test_can_search_invoices()
    {
        $user = User::factory()->create();
        $role = \App\Models\Role::firstOrCreate(
            ['name' => 'administrator'],
            ['display_name' => 'Administrator', 'description' => 'Administrator Role']
        );
        $user->roles()->attach($role);

        $customer = Customer::create([
            'code' => 'CUST-001',
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'status' => 'active'
        ]);

        Invoice::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-2026-SEARCHME',
            'status' => 'unpaid',
            'billing_period' => '2026-08',
            'issue_date' => now(),
            'due_date' => now()->addDays(7),
            'total_amount' => 100000,
        ]);

        Livewire::actingAs($user)
            ->test(InvoiceIndex::class)
            ->set('search', 'SEARCHME')
            ->assertSee('INV-2026-SEARCHME');
    }
}
