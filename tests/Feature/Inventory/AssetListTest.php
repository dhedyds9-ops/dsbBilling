<?php

namespace Tests\Feature\Inventory;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetListTest extends TestCase
{
    use RefreshDatabase;

    public function test_asset_list_page_renders_successfully(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator', 'guard_name' => 'web']);
        $admin = User::factory()->create([
            'email' => 'admin_asset@dsbilling.test',
            'job_function' => 'ADMINISTRATOR',
            'is_active' => true,
        ]);
        $admin->roles()->attach($adminRole->id);

        $response = $this->actingAs($admin)->get('/inventory/assets');
        $response->assertStatus(200);
    }
}
