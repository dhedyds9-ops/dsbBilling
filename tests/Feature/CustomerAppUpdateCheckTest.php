<?php

namespace Tests\Feature;

use Tests\TestCase;

class CustomerAppUpdateCheckTest extends TestCase
{
    public function test_customer_app_update_check_detects_v1_1_7()
    {
        $response = $this->getJson('/api/customer/app/check-update?version_code=10106');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'app' => 'customer',
                    'latest_version_code' => 10107,
                    'latest_version_name' => '1.1.7',
                    'update_available' => true,
                ],
            ]);
    }
}
