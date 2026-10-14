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

    public function test_router_create_page_loads(): void
    {
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $role = \App\Models\Role::firstOrCreate(
            ['name' => 'administrator'],
            ['display_name' => 'Super Admin', 'description' => 'Full access']
        );
        $user = User::factory()->create();
        $user->roles()->attach($role);
        $this->actingAs($user);

        $response = $this->get(route('isp.routers.create'));

        $response->assertStatus(200);
    }

    public function test_router_edit_page_loads(): void
    {
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $role = \App\Models\Role::firstOrCreate(
            ['name' => 'administrator'],
            ['display_name' => 'Super Admin', 'description' => 'Full access']
        );
        $user = User::factory()->create();
        $user->roles()->attach($role);
        $this->actingAs($user);

        $router = \App\Models\ISP\Router::create([
            'name' => 'Test Router',
            'code' => 'RTR-TEST-01',
            'ip_address' => '192.168.88.1',
            'api_port' => 8728,
            'status' => 'active',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->get(route('isp.routers.edit', $router->id));

        $response->assertStatus(200);
    }
}
