<?php

namespace Tests\Unit;

use App\Services\ACS\VendorCapabilityCatalog;
use PHPUnit\Framework\TestCase;

class AcsVendorCapabilityCatalogTest extends TestCase
{
    public function test_resolve_zte_f670l_profile(): void
    {
        $profile = VendorCapabilityCatalog::resolveProfile('ZTE Corporation', 'F670L', 'ZTEGC1234567');

        $this->assertNotNull($profile);
        $this->assertEquals('zte', $profile['vendor_key']);
        $this->assertEquals('F670L', $profile['matched_model']);
        $this->assertContains('2.4GHz', $profile['bands']);
        $this->assertContains('5GHz', $profile['bands']);
        $this->assertTrue($profile['optical']['swap_rx_tx']);
        $this->assertEquals(VendorCapabilityCatalog::STATUS_VERIFIED_WRITE, $profile['status']);
    }

    public function test_resolve_zte_f609_profile_is_single_band(): void
    {
        $profile = VendorCapabilityCatalog::resolveProfile('ZTE', 'F609', 'ZTEG99887766');

        $this->assertNotNull($profile);
        $this->assertEquals('zte', $profile['vendor_key']);
        $this->assertEquals('F609', $profile['matched_model']);
        $this->assertEquals(['2.4GHz'], $profile['bands']);
        $this->assertEquals(0, $profile['max_ssids_5']);
    }

    public function test_resolve_huawei_eg8141h5_profile(): void
    {
        $profile = VendorCapabilityCatalog::resolveProfile('Huawei Technologies Co., Ltd', 'EG8141H5', 'HWTC11223344');

        $this->assertNotNull($profile);
        $this->assertEquals('huawei', $profile['vendor_key']);
        $this->assertEquals('EG8141H5', $profile['matched_model']);
        $this->assertEquals('X_HW_VLAN', $profile['wan']['vlan_param']);
        $this->assertEquals('X_HW_ServiceList', $profile['wan']['service_list_param']);
    }

    public function test_resolve_fiberhome_hg6145f_dual_band(): void
    {
        $profile = VendorCapabilityCatalog::resolveProfile('FiberHome', 'HG6145F', 'FHTT55667788');

        $this->assertNotNull($profile);
        $this->assertEquals('fiberhome', $profile['vendor_key']);
        $this->assertEquals('HG6145F', $profile['matched_model']);
        $this->assertContains('2.4GHz', $profile['bands']);
        $this->assertContains('5GHz', $profile['bands']);
        $this->assertEquals('VLANIDMark', $profile['wan']['vlan_param']);
    }

    public function test_catalog_integrity_contains_all_core_vendors(): void
    {
        $catalog = VendorCapabilityCatalog::getCatalog();

        $this->assertArrayHasKey('zte', $catalog);
        $this->assertArrayHasKey('huawei', $catalog);
        $this->assertArrayHasKey('fiberhome', $catalog);
        $this->assertArrayHasKey('generic_tr181', $catalog);
    }
}
