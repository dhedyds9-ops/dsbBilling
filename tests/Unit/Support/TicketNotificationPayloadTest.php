<?php

namespace Tests\Unit\Support;

use App\Models\Support\Ticket;
use App\Services\Notifications\WhatsApp\WaTemplateEngine;
use PHPUnit\Framework\TestCase;

class TicketNotificationPayloadTest extends TestCase
{
    public function test_wa_template_engine_renders_network_object_ticket_correctly(): void
    {
        $engine = new WaTemplateEngine('dsBilling Enterprise', '0812-3456-7890');

        $data = [
            'ticket_code'       => 'TKT-B12D7D',
            'ticket_id'         => 100,
            'title'             => 'Photo closer, odp dan pengecekan kabel',
            'category'          => 'internet',
            'priority'          => 'medium',
            'is_network_object' => true,
            'object_name'       => 'Photo closer, odp dan pengecekan kabel',
            'customer_address'  => 'Jl. Raya Simpang Tiga KM 4',
            'contact_phone'     => '08123456789',
            'technician_name'   => 'MUHAMAD DEDI MULYADI',
            'description'       => 'Pengecekan kabel fiber optic dan foto closer sambungan tiang ODP.',
        ];

        $output = $engine->ticketCreated($data);

        $this->assertStringContainsString('🎫 *TIKET BARU: TKT-B12D7D*', $output);
        $this->assertStringContainsString('👷 Teknisi: *MUHAMAD DEDI MULYADI*', $output);
        $this->assertStringContainsString('🌐 Objek Jaringan: *Photo closer, odp dan pengecekan kabel*', $output);
        $this->assertStringContainsString('📞 PIC / Kontak: 08123456789', $output);
        $this->assertStringContainsString('📍 Alamat: Jl. Raya Simpang Tiga KM 4', $output);
        $this->assertStringContainsString('💬 Keluhan: Pengecekan kabel fiber optic dan foto closer sambungan tiang ODP.', $output);
        // Ensure "Pelanggan: Pelanggan" is NOT present
        $this->assertStringNotContainsString('👤 Pelanggan: Pelanggan', $output);
    }

    public function test_wa_template_engine_renders_customer_ticket_with_infra_details(): void
    {
        $engine = new WaTemplateEngine('dsBilling Enterprise', '0812-3456-7890');

        $data = [
            'ticket_code'       => 'TKT-123456',
            'ticket_id'         => 101,
            'title'             => 'Lampu LOS Merah',
            'category'          => 'internet',
            'priority'          => 'critical',
            'is_network_object' => false,
            'customer_name'     => 'Budi Santoso',
            'customer_code'     => 'CUST-00214',
            'contact_phone'     => '081298765432',
            'customer_address'  => 'Jl. Mawar No. 12',
            'technician_name'   => 'AHMAD GOFUR',
            'network_details'   => 'ODP: ODP-PWK-04 (Port 2) | OLT: OLT-ZTE-01',
            'device_details'    => 'Modem: ZTEGC85E2D31 | PPPoE: budi_net',
        ];

        $output = $engine->ticketCreated($data);

        $this->assertStringContainsString('👤 Pelanggan: *Budi Santoso (CUST-00214)*', $output);
        $this->assertStringContainsString('📞 No. HP: 081298765432', $output);
        $this->assertStringContainsString('📦 Objek / ODP: ODP-PWK-04 (Port 2) | OLT: OLT-ZTE-01', $output);
        $this->assertStringContainsString('📡 Perangkat: Modem: ZTEGC85E2D31 | PPPoE: budi_net', $output);
        $this->assertStringContainsString('⚡ Prioritas: 🔴 KRITIS', $output);
    }

    public function test_ticket_model_resolves_network_object_from_description(): void
    {
        $ticket = new Ticket();
        $ticket->id = 100;
        $ticket->title = 'Photo closer, odp dan pengecekan kabel';
        $ticket->category = 'internet';
        $ticket->priority = 'medium';
        $ticket->customer_id = null;
        $ticket->description = "[Objek Jaringan: Closer Simpang 3] Lokasi: Jl. Raya Simpang Tiga KM 4. PIC: 08123456789.\nPeriksa sambungan kabel FO.";

        $payload = $ticket->getNotificationPayload();

        $this->assertTrue($payload['is_network_object']);
        $this->assertEquals('Closer Simpang 3', $payload['object_name']);
        $this->assertEquals('Jl. Raya Simpang Tiga KM 4', $payload['customer_address']);
        $this->assertEquals('08123456789', $payload['contact_phone']);
        $this->assertStringContainsString('Periksa sambungan kabel FO.', $payload['description']);
    }
}
