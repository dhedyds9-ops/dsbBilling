<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobilePartnerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_partner_endpoint_returns_partner_and_company_info(): void
    {
        Setting::setValue('company.partner_name', 'PT MSTORE DIGITAL NUSANTARA');
        Setting::setValue('company.partner_license_no', 'Izin ISP Kominfo RI No. 123/2026');
        Setting::setValue('company.partner_service_type', 'Dedicated Internet & FTTH Backbone');

        $response = $this->getJson('/api/mobile/partner');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'partner_name' => 'PT MSTORE DIGITAL NUSANTARA',
                    'partner_license_no' => 'Izin ISP Kominfo RI No. 123/2026',
                    'partner_service_type' => 'Dedicated Internet & FTTH Backbone',
                ],
            ]);
    }
}
