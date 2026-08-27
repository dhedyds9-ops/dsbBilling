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
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('isp.routers.index'));

        $response->assertStatus(200);
    }
}
