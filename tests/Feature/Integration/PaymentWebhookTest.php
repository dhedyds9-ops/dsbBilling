<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;
use App\Models\Billing\Invoice;
use App\Models\CRM\Customer;
use App\Models\Master\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_webhook_invalid_signature_token()
    {
        // 1. Arrange: setup env
        Config::set('app.key', 'base64:invalidkey...');
        
        $driver = 'midtrans';
        $invalidToken = 'invalid123';
        
        // 2. Act
        $response = $this->postJson("/api/payment/webhook/{$driver}/{$invalidToken}", []);

        // 3. Assert
        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'status' => 'invalid_callback_token'
        ]);
    }

    public function test_payment_webhook_unknown_driver()
    {
        // 1. Arrange: setup env
        $driver = 'unknown_driver';
        $token = md5(config('app.key') . $driver);
        
        // 2. Act
        $response = $this->postJson("/api/payment/webhook/{$driver}/{$token}", []);

        // 3. Assert
        $response->assertStatus(404);
        $response->assertJson([
            'ok' => false,
            'status' => 'unknown_driver'
        ]);
    }
}
