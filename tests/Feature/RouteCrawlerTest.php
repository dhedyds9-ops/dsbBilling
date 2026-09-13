<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RouteCrawlerTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_get_routes()
    {
        $this->seed();
        $user = User::whereHas('roles', fn($q) => $q->where('name', 'super_admin'))->first();
        if (!$user) {
            $user = User::factory()->create();
        }

        $routes = Route::getRoutes()->getRoutesByMethod()['GET'];
        
        $failures = [];
        $successes = 0;
        
        foreach ($routes as $route) {
            $uri = $route->uri();
            
            // Skip routes with parameters {id}, {token}, etc.
            if (strpos($uri, '{') !== false) {
                continue;
            }
            
            // Skip api routes, ignition, livewire internal
            if (strpos($uri, 'api/') === 0 || strpos($uri, '_ignition') === 0 || strpos($uri, 'livewire/') === 0 || strpos($uri, 'livewire-a1b63ad7') === 0 || strpos($uri, 'sanctum') === 0 || strpos($uri, 'storage/') === 0) {
                continue;
            }

            // Skip up and logout
            if (in_array($uri, ['up', 'logout'])) {
                continue;
            }

            $response = $this->actingAs($user)->get($uri);
            
            if ($response->status() >= 500) {
                $failures[] = "URI: {$uri} - Status: {$response->status()} - Exception: " . ($response->exception ? $response->exception->getMessage() : 'Unknown');
            } else {
                $successes++;
            }
        }
        
        if (count($failures) > 0) {
            $this->fail("Failed routes:\n" . implode("\n", $failures));
        }
        
        $this->assertTrue(true, "Successfully checked {$successes} routes.");
    }
}
