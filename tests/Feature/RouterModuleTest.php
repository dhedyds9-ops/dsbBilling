<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouterModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_router_index_page_loads(): void
    {
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $role = \App\Models\Role::firstOrCreate(
            ['name' => 'administrator'],
            ['display_name' => 'Super Admin', 'description' => 'Full access']
        );
        $user = User::factory()->create();
        $user->roles()->attach($role);
        $this->actingAs($user);

        $response = $this->get(route('isp.routers.index'));

        $response->assertStatus(200);
    }
}
