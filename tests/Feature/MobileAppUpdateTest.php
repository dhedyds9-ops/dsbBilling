<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileAppUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_check_app_update_unauthenticated(): void
    {
        $response = $this->getJson('/api/mobile/app/check-update?version_code=1');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'platform',
                    'current_version_code',
                    'latest_version_code',
                    'latest_version_name',
                    'minimum_supported_code',
                    'update_available',
                    'update_required',
                    'download_url',
                    'release_notes',
                    'checked_at',
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals(1, $data['current_version_code']);
        $this->assertTrue($data['update_available']);
        $this->assertFalse($data['update_required']);
    }

    public function test_reports_update_required_when_below_minimum_supported(): void
    {
        config(['mobile.minimum_supported_code' => 3]);
        config(['mobile.latest_version_code' => 5]);

        $response = $this->getJson('/api/mobile/app/check-update?version_code=2');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertTrue($data['update_available']);
        $this->assertTrue($data['update_required']);
    }

    public function test_reports_no_update_when_on_latest_version(): void
    {
        config(['mobile.latest_version_code' => 2]);

        $response = $this->getJson('/api/mobile/app/check-update?version_code=2');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertFalse($data['update_available']);
        $this->assertFalse($data['update_required']);
    }

    public function test_version_check_alias_works(): void
    {
        $response = $this->getJson('/api/mobile/version/check?version_code=1');

        $response->assertOk()
            ->assertJsonPath('success', true);
    }
}
