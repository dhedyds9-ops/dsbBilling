<?php

namespace Tests\Unit;

use App\Services\Support\CustomerFieldSanitizer;
use PHPUnit\Framework\TestCase;

class CustomerFieldSanitizerEdgeCasesTest extends TestCase
{
    /**
     * Edge Case 1: Preserves leading zero on various standard Indonesian numbers.
     */
    public function test_leading_zero_preservation(): void
    {
        $this->assertEquals('081234567890', CustomerFieldSanitizer::cleanPhone('081234567890'));
        $this->assertEquals('081234567890', CustomerFieldSanitizer::cleanPhone('6281234567890'));
        $this->assertEquals('081234567890', CustomerFieldSanitizer::cleanPhone('81234567890'));
        $this->assertEquals('081234567890', CustomerFieldSanitizer::cleanPhone('0812-3456-7890'));
        $this->assertEquals('081234567890', CustomerFieldSanitizer::cleanPhone('0812 3456 7890'));
    }

    /**
     * Edge Case 2: Preserves international +62 format.
     */
    public function test_international_format_preservation(): void
    {
        $this->assertEquals('+6281234567890', CustomerFieldSanitizer::cleanPhone('+6281234567890'));
        $this->assertEquals('+6281234567890', CustomerFieldSanitizer::cleanPhone('+62 812-3456-7890'));
        $this->assertEquals('+6281234567890', CustomerFieldSanitizer::cleanPhone('+62 812 3456 7890'));
    }

    /**
     * Edge Case 3: House numbers, RT/RW, and 5-digit postal codes are preserved and not stripped.
     */
    public function test_street_address_with_numbers_and_rt_rw_and_postal_code(): void
    {
        $address = 'Jl. Melati No. 12 RT 03/RW 05, Kel. Cibodas, Kode Pos 14250';
        $cleaned = CustomerFieldSanitizer::cleanAddress($address);

        $this->assertEquals('Jl. Melati No. 12 RT 03/RW 05, Kel. Cibodas, Kode Pos 14250', $cleaned);

        $result = CustomerFieldSanitizer::sanitize(
            name: 'anda',
            phone: '081234567890',
            address: $address
        );

        $this->assertEquals('Jl. Melati No. 12 RT 03/RW 05, Kel. Cibodas, Kode Pos 14250', $result['address']);
        $this->assertEquals('081234567890', $result['phone']);
    }

    /**
     * Edge Case 4: Non-coordinate phrases like 'titik kumpul' or 'titik temu' must NOT be stripped.
     */
    public function test_non_coordinate_phrases_preserved(): void
    {
        $addr1 = 'Dekat titik kumpul warga RT 02/RW 04, Desa Sukamaju';
        $cleaned1 = CustomerFieldSanitizer::cleanAddress($addr1);
        $this->assertStringContainsString('titik kumpul warga RT 02/RW 04', $cleaned1);

        $addr2 = 'Titik temu pos ronda RT 01';
        $cleaned2 = CustomerFieldSanitizer::cleanAddress($addr2);
        $this->assertStringContainsString('Titik temu pos ronda RT 01', $cleaned2);
    }

    /**
     * Edge Case 5: Negative coordinates and boundary values are accurately extracted.
     */
    public function test_negative_coordinates_and_valid_ranges(): void
    {
        $coords1 = CustomerFieldSanitizer::extractCoordinates('Alamat: Kp. Melati | GPS: -6.123456, 106.789012');
        $this->assertEquals(-6.123456, $coords1['latitude']);
        $this->assertEquals(106.789012, $coords1['longitude']);

        $coords2 = CustomerFieldSanitizer::extractCoordinates('Titik: -7.123456, -110.456789');
        $this->assertEquals(-7.123456, $coords2['latitude']);
        $this->assertEquals(-110.456789, $coords2['longitude']);

        $result = CustomerFieldSanitizer::sanitize(
            name: 'anda',
            phone: '081234567890',
            address: 'Kp. Cibodas RT 02/RW 04 [Titik: -6.123456, 106.789012]'
        );
        $this->assertEquals(-6.123456, $result['latitude']);
        $this->assertEquals(106.789012, $result['longitude']);
        $this->assertEquals('Kp. Cibodas RT 02/RW 04', $result['address']);
    }

    /**
     * Edge Case 6: Invalid and out-of-range coordinates are rejected.
     */
    public function test_invalid_and_out_of_range_coordinates(): void
    {
        $coords1 = CustomerFieldSanitizer::extractCoordinates('Koordinat: 999.999, 999.999');
        $this->assertNull($coords1['latitude']);
        $this->assertNull($coords1['longitude']);

        $coords2 = CustomerFieldSanitizer::extractCoordinates('GPS: -95.123456, 200.123456');
        $this->assertNull($coords2['latitude']);
        $this->assertNull($coords2['longitude']);

        $coordsZero = CustomerFieldSanitizer::extractCoordinates('GPS: 0.00001, 0.00001');
        $this->assertNull($coordsZero['latitude']);
        $this->assertNull($coordsZero['longitude']);
    }

    /**
     * Edge Case 7: Null, empty, dashes, or whitespace fields.
     */
    public function test_empty_address_and_empty_phone(): void
    {
        $result = CustomerFieldSanitizer::sanitize(
            name: null,
            phone: '-',
            address: '   '
        );

        $this->assertEquals('Pelanggan Baru', $result['name']);
        $this->assertNull($result['phone']);
        $this->assertNull($result['whatsapp']);
        $this->assertEquals('Alamat belum tercatat', $result['address']);
        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertFalse($result['is_ambiguous']);
    }

    /**
     * Edge Case 8: Ambiguity detection when multiple different candidate phone numbers exist in unstructured text.
     */
    public function test_ambiguous_candidate_phone_numbers(): void
    {
        $result = CustomerFieldSanitizer::sanitize(
            name: 'anda',
            phone: null,
            address: 'Jl. Raya Kp. Baru 081234567890',
            notesOrDesc: 'Catatan kontak darurat: 089876543210'
        );

        $this->assertTrue($result['is_ambiguous']);
        $this->assertNotNull($result['phone']);
    }

    /**
     * Edge Case 9: Unambiguous single phone candidate extraction.
     */
    public function test_unambiguous_single_candidate(): void
    {
        $result = CustomerFieldSanitizer::sanitize(
            name: 'anda',
            phone: null,
            address: '081234567890 titik koordinat Kp. Cibodas, Desa Sukamaju'
        );

        $this->assertFalse($result['is_ambiguous']);
        $this->assertEquals('081234567890', $result['phone']);
        $this->assertEquals('Kp. Cibodas, Desa Sukamaju', $result['address']);
    }
}
