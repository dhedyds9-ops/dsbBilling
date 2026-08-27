<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use App\Models\ISP\Router;
use App\Models\User;
use App\Services\ISP\RouterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_created_on_create(): void
    {
        $user = User::factory()->create();
        $service = app(RouterService::class);

        $data = [
            'code' => 'RTR-001',
            'name' => 'Router Test',
            'ip_address' => '192.168.1.1',
            'status' => 'active',
        ];

        $router = $service->create($data, $user);

        $auditLog = AuditLog::where([
            'auditable_type' => Router::class,
            'auditable_id' => $router->id,
            'event' => 'created',
        ])->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals($user->id, $auditLog->user_id);
    }

    public function test_audit_log_created_on_update(): void
    {
        $user = User::factory()->create();
        $service = app(RouterService::class);

        $router = Router::factory()->create(['created_by' => $user->id]);
        $service->update($router, ['name' => 'Updated'], $user);

        $auditLog = AuditLog::where([
            'auditable_type' => Router::class,
            'auditable_id' => $router->id,
            'event' => 'updated',
        ])->first();

        $this->assertNotNull($auditLog);
    }

    public function test_audit_log_created_on_delete(): void
    {
        $user = User::factory()->create();
        $service = app(RouterService::class);

        $router = Router::factory()->create(['created_by' => $user->id]);
        $service->delete($router, $user);

        $auditLog = AuditLog::where([
            'auditable_type' => Router::class,
            'auditable_id' => $router->id,
            'event' => 'deleted',
        ])->first();

        $this->assertNotNull($auditLog);
    }
}
