<?php

namespace Tests\Unit;

use App\Services\Support\CustomerFieldSanitizer;
use PHPUnit\Framework\TestCase;

class CustomerFieldSanitizerTest extends TestCase
{
    /** Test extraction from corrupted address string containing phone and 'titik koordinat'. */
    public function test_disentangles_phone_and_titik_koordinat_from_address()
    {
        $result = CustomerFieldSanitizer::sanitize(
            name: 'anda',
            phone: '-',
            address: '081234567890 titik koordinat Kp. Cibodas, Desa Sukamaju'
        );

        $this->assertEquals('anda', $result['name']);
        $this->assertEquals('081234567890', $result['phone']);
        $this->assertEquals('081234567890', $result['whatsapp']);
        $this->assertEquals('Kp. Cibodas, Desa Sukamaju', $result['address']);
        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
    }

    /** Test extraction when GPS coordinates are also embedded in the address string. */
    public function test_extracts_coordinates_and_cleans_address()
    {
        $result = CustomerFieldSanitizer::sanitize(
            name: 'anda',
            phone: null,
            address: '081234567890 titik koordinat -6.123456, 106.123456 Kp. Cibodas, Desa Sukamaju'
        );

        $this->assertEquals('081234567890', $result['phone']);
        $this->assertEquals('Kp. Cibodas, Desa Sukamaju', $result['address']);
        $this->assertEquals(-6.123456, $result['latitude']);
        $this->assertEquals(106.123456, $result['longitude']);
    }

    /** Test when structured Calon Pelanggan ticket description is passed. */
    public function test_parses_calon_pelanggan_description_format()
    {
        $desc = "[Calon Pelanggan: anda | Telp: 081234567890 | Alamat: Kp. Cibodas RT 02/RW 04, Desa Sukamaju | GPS: -6.123456, 106.123456]\nKeterangan tambahan";
        $result = CustomerFieldSanitizer::sanitize(
            name: null,
            phone: null,
            address: null,
            notesOrDesc: $desc
        );

        $this->assertEquals('anda', $result['name']);
        $this->assertEquals('081234567890', $result['phone']);
        $this->assertEquals('Kp. Cibodas RT 02/RW 04, Desa Sukamaju', $result['address']);
        $this->assertEquals(-6.123456, $result['latitude']);
        $this->assertEquals(106.123456, $result['longitude']);
    }

    /** Test ODP coordinate concatenation pattern is cleaned. */
    public function test_cleans_odp_coordinate_bracket_from_address()
    {
        $dirtyAddr = "Dekat ODP: ODP-01 [Titik: -6.123456, 106.123456] - Kp. Cibodas RT 02/RW 04";
        $result = CustomerFieldSanitizer::sanitize(
            name: 'Budi',
            phone: '081298765432',
            address: $dirtyAddr
        );

        $this->assertEquals('Budi', $result['name']);
        $this->assertEquals('081298765432', $result['phone']);
        $this->assertEquals('Dekat ODP: ODP-01 - Kp. Cibodas RT 02/RW 04', $result['address']);
        $this->assertEquals(-6.123456, $result['latitude']);
        $this->assertEquals(106.123456, $result['longitude']);
    }

    /** Test leading zero preservation on standard mobile numbers. */
    public function test_preserves_leading_zero_on_phone()
    {
        $this->assertEquals('081234567890', CustomerFieldSanitizer::cleanPhone('081234567890'));
        $this->assertEquals('081234567890', CustomerFieldSanitizer::cleanPhone('6281234567890'));
        $this->assertEquals('+6281234567890', CustomerFieldSanitizer::cleanPhone('+6281234567890'));
        $this->assertNull(CustomerFieldSanitizer::cleanPhone('-'));
        $this->assertNull(CustomerFieldSanitizer::cleanPhone(''));
        $this->assertNull(CustomerFieldSanitizer::cleanPhone(null));
    }
}
