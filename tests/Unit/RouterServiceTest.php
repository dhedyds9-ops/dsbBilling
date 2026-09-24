<?php

namespace Tests\Unit;

use App\Models\ISP\Router;
use App\Models\User;
use App\Services\ISP\RouterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouterServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RouterService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(RouterService::class);
    }

    public function test_create_router(): void
    {
        $user = User::factory()->create();

        $data = [
            'code' => 'RTR-001',
            'name' => 'Router Test',
            'ip_address' => '192.168.1.1',
            'status' => 'active',
        ];

        $router = $this->service->create($data, $user);

        $this->assertInstanceOf(Router::class, $router);
        $this->assertEquals('RTR-001', $router->code);
        $this->assertEquals($user->id, $router->created_by);
        $this->assertEquals($user->id, $router->updated_by);
    }

    public function test_update_router(): void
    {
        $user = User::factory()->create();
        $router = Router::factory()->create(['created_by' => $user->id]);

        $updatedData = [
            'name' => 'Router Updated',
        ];

        $updatedRouter = $this->service->update($router, $updatedData, $user);

        $this->assertEquals('Router Updated', $updatedRouter->name);
        $this->assertEquals($user->id, $updatedRouter->updated_by);
    }

    public function test_delete_router(): void
    {
        $user = User::factory()->create();
        $router = Router::factory()->create(['created_by' => $user->id]);

        $this->service->delete($router, $user);

        $this->assertSoftDeleted($router);
    }

    public function test_restore_router(): void
    {
        $user = User::factory()->create();
        $router = Router::factory()->create(['created_by' => $user->id]);
        $router->delete();

        $this->service->restore($router, $user);

        $this->assertNotSoftDeleted($router);
    }
}
