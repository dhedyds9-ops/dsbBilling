<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Exports\Templates\PPPoEUserTemplateExport;
use App\Exports\Templates\HotspotUserTemplateExport;
use App\Exports\Templates\VoucherTemplateExport;
use App\Exports\Templates\ServiceProfileTemplateExport;
use App\Exports\HotspotUserExport;
use App\Exports\VoucherExport;
use App\Exports\PPPoEUserExport;
use App\Exports\ServiceProfileExport;

class ExportImportTemplateTest extends TestCase
{
    public function test_pppoe_template_export_structure(): void
    {
        $template = new PPPoEUserTemplateExport();
        $headings = $template->headings();
        $array = $template->array();

        $this->assertContains('username', $headings);
        $this->assertContains('nama_pelanggan', $headings);
        $this->assertContains('paket_langganan', $headings);
        $this->assertNotEmpty($array);
        $this->assertArrayHasKey('username', $array[0]);
    }

    public function test_hotspot_user_template_export_structure(): void
    {
        $template = new HotspotUserTemplateExport();
        $headings = $template->headings();
        $array = $template->array();

        $this->assertContains('username', $headings);
        $this->assertContains('service_profile', $headings);
        $this->assertContains('customer', $headings);
        $this->assertNotEmpty($array);
        $this->assertArrayHasKey('username', $array[0]);
        $this->assertArrayHasKey('service_profile', $array[0]);
    }

    public function test_voucher_template_export_structure(): void
    {
        $template = new VoucherTemplateExport();
        $headings = $template->headings();
        $array = $template->array();

        $this->assertContains('kode_voucher', $headings);
        $this->assertContains('service_profile', $headings);
        $this->assertContains('voucher_pool', $headings);
        $this->assertNotEmpty($array);
        $this->assertArrayHasKey('kode_voucher', $array[0]);
    }

    public function test_service_profile_template_export_structure(): void
    {
        $template = new ServiceProfileTemplateExport();
        $headings = $template->headings();
        $array = $template->array();

        $this->assertContains('nama_paket', $headings);
        $this->assertContains('download_speed', $headings);
        $this->assertContains('upload_speed', $headings);
        $this->assertNotEmpty($array);
        $this->assertArrayHasKey('nama_paket', $array[0]);
    }

    public function test_exports_accept_filters(): void
    {
        $hotspotExport = new HotspotUserExport(null, ['status' => 'active', 'search' => 'test']);
        $this->assertInstanceOf(HotspotUserExport::class, $hotspotExport);

        $voucherExport = new VoucherExport(null, ['status' => 'available']);
        $this->assertInstanceOf(VoucherExport::class, $voucherExport);

        $pppoeExport = new PPPoEUserExport(null, ['status' => 'active']);
        $this->assertInstanceOf(PPPoEUserExport::class, $pppoeExport);

        $profileExport = new ServiceProfileExport(null, ['service_type' => 'hotspot']);
        $this->assertInstanceOf(ServiceProfileExport::class, $profileExport);
    }
}
