<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MobileAuthProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Technician', 'guard_name' => 'web']);

        $this->staffUser = User::factory()->create([
            'name' => 'Budi Teknisi',
            'username' => 'buditek',
            'email' => 'budi@dsbilling.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $this->staffUser->roles()->attach($role->id);
    }

    public function test_authenticated_staff_can_change_password(): void
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')
            ->postJson('/api/mobile/auth/change-password', [
                'current_password' => 'password123',
                'password' => 'newSecretPass2026',
                'password_confirmation' => 'newSecretPass2026',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Kata sandi berhasil diperbarui.',
            ]);

        $this->staffUser->refresh();
        $this->assertTrue(Hash::check('newSecretPass2026', $this->staffUser->password));
    }

    public function test_change_password_fails_if_current_password_is_incorrect(): void
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')
            ->postJson('/api/mobile/auth/change-password', [
                'current_password' => 'wrongpass',
                'password' => 'newSecretPass2026',
                'password_confirmation' => 'newSecretPass2026',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Kata sandi saat ini yang Anda masukkan salah.',
            ]);
    }

    public function test_change_password_fails_if_confirmation_mismatches(): void
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')
            ->postJson('/api/mobile/auth/change-password', [
                'current_password' => 'password123',
                'password' => 'newSecretPass2026',
                'password_confirmation' => 'differentPass',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_authenticated_staff_can_update_profile_and_username(): void
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')
            ->postJson('/api/mobile/auth/update-profile', [
                'name' => 'Budi Suryadi Updated',
                'username' => 'budisuryadi_new',
                'phone' => '081299887766',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Budi Suryadi Updated',
                    'username' => 'budisuryadi_new',
                ],
            ]);

        $this->staffUser->refresh();
        $this->assertEquals('Budi Suryadi Updated', $this->staffUser->name);
        $this->assertEquals('budisuryadi_new', $this->staffUser->username);
    }

    public function test_update_profile_fails_if_username_already_taken(): void
    {
        User::factory()->create([
            'username' => 'taken_username',
            'email' => 'other@dsbilling.test',
        ]);

        $response = $this->actingAs($this->staffUser, 'sanctum')
            ->postJson('/api/mobile/auth/update-profile', [
                'username' => 'taken_username',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->postJson('/api/mobile/auth/change-password', [
            'current_password' => 'password123',
            'password' => 'newpass123',
        ]);

        $response->assertStatus(401);
    }
}
