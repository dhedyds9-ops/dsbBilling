<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workforce\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_login_with_valid_credentials_succeeds()
    {
        $user = User::factory()->create([
            'email' => 'tech_mobile_' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'job_function' => 'TECHNICIAN',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/mobile/login', [
            'email' => $user->email,
            'password' => 'Secret123!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => 'TECHNICIAN',
                ],
            ]);

        $this->assertNotEmpty($response->json('token'));
        $this->assertContains('workforce.execute', $response->json('user.permissions'));
    }

    public function test_staff_login_with_invalid_password_returns_401()
    {
        $user = User::factory()->create([
            'email' => 'tech_fail_' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/mobile/login', [
            'email' => $user->email,
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_inactive_staff_is_rejected_with_403()
    {
        $user = User::factory()->create([
            'email' => 'inactive_' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/mobile/login', [
            'email' => $user->email,
            'password' => 'Secret123!',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_customer_is_rejected_from_staff_app()
    {
        $customer = User::factory()->create([
            'email' => 'cust_' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'job_function' => 'CUSTOMER',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/mobile/login', [
            'email' => $customer->email,
            'password' => 'Secret123!',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_reseller_can_login_to_staff_app_with_reseller_role()
    {
        $reseller = User::factory()->create([
            'email' => 'reseller_' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'job_function' => 'RESELLER',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/mobile/login', [
            'email' => $reseller->email,
            'password' => 'Secret123!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user' => [
                    'role' => 'RESELLER',
                ],
            ]);
        $this->assertNotEmpty($response->json('token'));
        $this->assertContains('customer.create', $response->json('user.permissions'));
        $this->assertContains('inventory.custody', $response->json('user.permissions'));
        $this->assertContains('acs.view', $response->json('user.permissions'));
    }

    public function test_logout_revokes_token()
    {
        $user = User::factory()->create([
            'email' => 'logout_' . uniqid() . '@example.com',
            'job_function' => 'TECHNICIAN',
            'is_active' => true,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/mobile/logout');
        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_cs_role_cannot_access_acs_devices()
    {
        $csUser = User::factory()->create([
            'email' => 'cs_' . uniqid() . '@example.com',
            'job_function' => 'CUSTOMER_SERVICE',
            'is_active' => true,
        ]);

        Sanctum::actingAs($csUser, ['*']);

        $response = $this->getJson('/api/mobile/acs/devices');
        $response->assertStatus(403)
            ->assertJson(['success' => false]);
    }

    public function test_technician_cannot_modify_spk_assigned_to_another_technician()
    {
        $tech1 = User::factory()->create([
            'email' => 'tech1_' . uniqid() . '@example.com',
            'job_function' => 'TECHNICIAN',
            'is_active' => true,
        ]);

        $tech2 = User::factory()->create([
            'email' => 'tech2_' . uniqid() . '@example.com',
            'job_function' => 'TECHNICIAN',
            'is_active' => true,
        ]);

        $workOrder = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'SPK Tech 2',
            'assigned_to' => $tech2->id,
            'status' => 'pending',
        ]);

        // Tech 1 attempts to complete Tech 2's SPK
        Sanctum::actingAs($tech1, ['*']);
        $response = $this->postJson("/api/mobile/workforce/work-orders/{$workOrder->id}/complete");

        $response->assertStatus(403)
            ->assertJson(['success' => false]);
    }

    public function test_authenticated_staff_can_generate_sso_token_and_auto_login()
    {
        $user = User::factory()->create([
            'email' => 'sso_user_' . uniqid() . '@example.com',
            'job_function' => 'RESELLER',
            'is_active' => true,
        ]);

        Sanctum::actingAs($user, ['*']);

        // 1. Generate SSO Token
        $response = $this->postJson('/api/mobile/auth/sso-token');
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'target_path' => '/reseller-portal/dashboard',
            ]);

        $ssoToken = $response->json('sso_token');
        $this->assertNotEmpty($ssoToken);
        $this->assertStringContainsString('/sso/login?token=' . $ssoToken, $response->json('redirect_url'));

        // 2. Consume SSO Token via Web GET
        $webResponse = $this->get('/sso/login?token=' . $ssoToken);
        $webResponse->assertRedirect('/reseller-portal/dashboard');
        $this->assertAuthenticatedAs($user, 'web');

        // 3. Replay attack: Single-use token must be invalidated
        $this->app['auth']->guard('web')->logout();
        $replayResponse = $this->get('/sso/login?token=' . $ssoToken);
        $replayResponse->assertRedirect(route('login'));
        $this->assertGuest('web');
    }

    public function test_authenticated_technician_can_generate_sso_token_and_auto_login_to_technician_portal()
    {
        $user = User::factory()->create([
            'email' => 'tech_sso_' . uniqid() . '@example.com',
            'job_function' => 'TECHNICIAN',
            'is_active' => true,
        ]);

        Sanctum::actingAs($user, ['*']);

        // 1. Generate SSO Token for Technician
        $response = $this->postJson('/api/mobile/auth/sso-token');
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'target_path' => '/technician-portal/dashboard',
            ]);

        $ssoToken = $response->json('sso_token');
        $this->assertNotEmpty($ssoToken);
        $this->assertStringContainsString('/sso/login?token=' . $ssoToken, $response->json('redirect_url'));

        // 2. Consume SSO Token via Web GET
        $webResponse = $this->get('/sso/login?token=' . $ssoToken);
        $webResponse->assertRedirect('/technician-portal/dashboard');
        $this->assertAuthenticatedAs($user, 'web');

        // 3. Accessing /technician-portal/dashboard must return 200 (not 404)
        $portalResponse = $this->actingAs($user)->get('/technician-portal/dashboard');
        $portalResponse->assertStatus(200);

        // 4. Accessing legacy /isp/dashboard must redirect to technician dashboard (not 404)
        $legacyResponse = $this->actingAs($user)->get('/isp/dashboard');
        $legacyResponse->assertRedirect(route('technician.dashboard'));
    }
}
