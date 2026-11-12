<?php

namespace Tests\Feature;

use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\Billing\Invoice;
use App\Models\Billing\InvoiceItem;
use App\Models\ISP\Onu;
use App\Models\Role;
use App\Models\Support\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function createCustomerWithUser(string $code, string $email, string $phone = '081234567890'): array
    {
        $role = Role::firstOrCreate(
            ['name' => 'customer'],
            ['display_name' => 'Pelanggan', 'guard_name' => 'web']
        );

        $user = User::create([
            'name' => "Customer {$code}",
            'email' => $email,
            'whatsapp' => $phone,
            'customer_code' => $code,
            'username' => $code,
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $customer = Customer::create([
            'user_id' => $user->id,
            'code' => $code,
            'name' => "Customer {$code}",
            'phone' => $phone,
            'email' => $email,
            'status' => 'active',
            'address' => 'Jl. Test No. 123',
        ]);

        return [$user, $customer];
    }

    public function test_customer_can_login_with_customer_code()
    {
        [$user, $customer] = $this->createCustomerWithUser('CUSTTEST01', 'cust01@example.com');

        $response = $this->postJson('/api/customer/auth/login', [
            'login' => 'CUSTTEST01',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login berhasil.',
            ])
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email', 'customer_code'],
                'customer' => ['id', 'code', 'name', 'status'],
            ]);
    }

    public function test_customer_login_fails_with_invalid_password()
    {
        [$user, $customer] = $this->createCustomerWithUser('CUSTTEST02', 'cust02@example.com');

        $response = $this->postJson('/api/customer/auth/login', [
            'login' => 'CUSTTEST02',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_authenticated_customer_can_access_dashboard()
    {
        [$user, $customer] = $this->createCustomerWithUser('CUSTTEST03', 'cust03@example.com');
        Sanctum::actingAs($user, ['customer']);

        $response = $this->getJson('/api/customer/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'customer' => [
                        'code' => 'CUSTTEST03',
                    ],
                ],
            ]);
    }

    public function test_anti_idor_customer_cannot_view_another_customers_invoice()
    {
        [$userA, $customerA] = $this->createCustomerWithUser('CUSTTESTA', 'custa@example.com');
        [$userB, $customerB] = $this->createCustomerWithUser('CUSTTESTB', 'custb@example.com');

        $invoiceB = Invoice::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'customer_id' => $customerB->id,
            'invoice_number' => 'INV-TEST-B-001',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'total_amount' => 150000,
            'paid_amount' => 0,
            'status' => 'unpaid',
            'currency' => 'IDR',
        ]);

        // Customer A attempts to access Customer B's invoice
        Sanctum::actingAs($userA, ['customer']);
        $response = $this->getJson("/api/customer/invoices/{$invoiceB->id}");

        // Must reject with 404 (IDOR Prevention)
        $response->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    public function test_anti_idor_customer_cannot_view_another_customers_ticket()
    {
        [$userA, $customerA] = $this->createCustomerWithUser('CUSTTESTA2', 'custa2@example.com');
        [$userB, $customerB] = $this->createCustomerWithUser('CUSTTESTB2', 'custb2@example.com');

        $ticketB = Ticket::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'customer_id' => $customerB->id,
            'title' => 'Gangguan Pelanggan B',
            'description' => 'Masalah teknis',
            'category' => 'Teknis',
            'priority' => 'high',
            'status' => 'open',
        ]);

        // Customer A attempts to read Customer B's ticket
        Sanctum::actingAs($userA, ['customer']);
        $response = $this->getJson("/api/customer/tickets/{$ticketB->id}");

        $response->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    public function test_customer_can_create_ticket()
    {
        [$user, $customer] = $this->createCustomerWithUser('CUSTTEST04', 'cust04@example.com');
        Sanctum::actingAs($user, ['customer']);

        $response = $this->postJson('/api/customer/tickets', [
            'title' => 'Internet Lambat',
            'description' => 'Kecepatan download turun drastis',
            'category' => 'Teknis',
            'priority' => 'medium',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('tickets', [
            'customer_id' => $customer->id,
            'title' => 'Internet Lambat',
        ]);
    }

    public function test_customer_can_get_contact_admin_info()
    {
        [$user, $customer] = $this->createCustomerWithUser('CUSTTEST05', 'cust05@example.com');
        Sanctum::actingAs($user, ['customer']);

        $response = $this->getJson('/api/customer/support/contact');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'company_name',
                    'whatsapp_url',
                    'phone',
                ],
            ]);
    }
}
