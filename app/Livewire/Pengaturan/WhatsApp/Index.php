<?php

namespace App\Livewire\Pengaturan\WhatsApp;

use App\Livewire\AdminComponent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Throwable;

class Index extends AdminComponent
{
    public string $activeModule = 'pengaturan';
    public string $activePage = 'whatsapp';

    public array $connection = [
        'provider' => 'fonnte',
        'api_token' => '',
        'base_url' => 'https://api.fonnte.com/send',
        'device_id' => '',
        'sender_number' => '',
        'admin_number' => '',
        'webhook_url' => '',
        'webhook_secret' => '',
        'use_webhook' => false,
        'enable_url_verification' => true,
        'delay_per_message' => 3,
        'max_retry' => 3,
        'mode' => 'production',
    ];

    public array $providers = [
        ['code' => 'fonnte', 'name' => 'Fonnte', 'type' => 'Device Gateway', 'default_base_url' => 'https://api.fonnte.com/send'],
        ['code' => 'wablas', 'name' => 'Wablas', 'type' => 'Device Gateway', 'default_base_url' => 'https://jogja.wablas.com/api/send-message'],
        ['code' => 'whacenter', 'name' => 'Whacenter', 'type' => 'Device Gateway', 'default_base_url' => 'https://app.whacenter.com/api/send'],
        ['code' => 'whatsapp_cloud', 'name' => 'WhatsApp Cloud API (Meta Official)', 'type' => 'Official', 'default_base_url' => 'https://graph.facebook.com/v18.0'],
        ['code' => 'qiscus', 'name' => 'Qiscus Omnichannel', 'type' => 'Official', 'default_base_url' => 'https://api.qiscus.com/api/v2'],
    ];

    public array $templates = [
        [
            'code' => 'INVOICE_UNPAID',
            'name' => 'Reminder Tagihan Belum Lunas',
            'content' => "Halo {{member_name}}, tagihan Anda periode {{invoice_period}} senilai Rp {{invoice_total}} (Jatuh Tempo: {{due_date}}) belum dibayar. Silakan lakukan pembayaran agar layanan tidak terisolir.\n\nMetode Bayar:{{payment_options}}\n\nTerima kasih - {{company_name}}",
            'enabled' => true,
            'buttons' => [],
        ],
        [
            'code' => 'PAYMENT_RECEIVED',
            'name' => 'Pembayaran Berhasil',
            'content' => "✅ *PEMBAYARAN DITERIMA*\n\nPelanggan: {{member_name}}\nNo. Invoice: #{{invoice_number}}\nPeriode: {{invoice_period}}\nJumlah: Rp {{amount}}\nWaktu: {{payment_time}}\n\nTerima kasih atas kepercayaan Anda bersama {{company_name}}. 🙏",
            'enabled' => true,
            'buttons' => [],
        ],
        [
            'code' => 'INVOICE_OVERDUE',
            'name' => 'Tagihan Jatuh Tempo (Terisolir)',
            'content' => "⚠️ *LAYANAN TERISOLIR* ⚠️\n\nHalo {{member_name}}, layanan internet Anda saat ini *TERISOLIR* karena tagihan periode {{invoice_period}} belum dibayar.\n\nTagihan: Rp {{invoice_total}}\nKlik untuk cek detail: {{customer_portal_url}}\n\nSegera lunas untuk reaktivasi otomatis.\n{{company_name}}",
            'enabled' => true,
            'buttons' => [],
        ],
        [
            'code' => 'SERVICE_ACTIVATED',
            'name' => 'Aktivasi Layanan Berhasil',
            'content' => "🎉 *LAYANAN AKTIF* 🎉\n\nSelamat {{member_name}}!\n\nLayanan internet Anda telah AKTIF.\n\n📦 Paket    : {{package_name}}\n💨 Speed     : {{package_speed}}\n🔑 Username  : {{pppoe_username}}\n🗓️ Aktif    : {{active_date}} s/d {{expire_date}}\n\nTerima kasih - {{company_name}}",
            'enabled' => true,
            'buttons' => [],
        ],
        [
            'code' => 'OUTAGE_ALERT',
            'name' => 'Notifikasi Gangguan Jaringan',
            'content' => "🚨 *GANGGUAN TERDETEKSI* 🚨\n\nWaktu: {{outage_time}}\nLokasi: {{outage_area}}\nPerangkat: {{router_name}}\nEstimasi Perbaikan: {{eta}}\n\nMohon maaf atas ketidaknyamanan ini.\nTim NOC {{company_name}}",
            'enabled' => true,
            'buttons' => [],
        ],
        [
            'code' => 'TICKET_OPENED',
            'name' => 'Tiket Support Dibuat',
            'content' => "📋 *TIKET DIBUAT* 📋\n\nNo. Tiket: #{{ticket_id}}\nPelapor: {{customer_name}}\nJudul: {{ticket_title}}\nPrioritas: {{ticket_priority}}\n\nTim kami akan segera menghubungi Anda.\n\nBest regards,\n{{company_name}} Support",
            'enabled' => true,
            'buttons' => [],
        ],
        [
            'code' => 'VOUCHER_SOLD',
            'name' => 'Voucher Terjual',
            'content' => "🎟️ *VOUCHER AKTIF* 🎟️\n\nKode Voucher: {{voucher_code}}\nPaket: {{package_name}}\nMasa Aktif: {{validity}}\nHarga: Rp {{price}}\n\nTerima kasih telah menggunakan layanan {{company_name}}!",
            'enabled' => true,
            'buttons' => [],
        ],
        [
            'code' => 'WELCOME_NEW_CUSTOMER',
            'name' => 'Selamat Datang Pelanggan Baru',
            'content' => "👋 *SELAMAT DATANG* 👋\n\nTerima kasih {{member_name}} telah bergabung bersama {{company_name}}!\n\n📋 No. Pelanggan: {{member_code}}\n📦 Paket: {{package_name}}\n💨 Kecepatan: {{package_speed}}\n💰 Harga: Rp {{package_price}} / {{billing_cycle}}\n🔑 Username: {{pppoe_username}}\n\nJika ada pertanyaan, hubungi CS kami.\n\n{{company_name}} - Internet Cepat & Stabil",
            'enabled' => true,
            'buttons' => [],
        ],
    ];

    public string $testPhone = '';
    public string $testMessage = 'Halo! Ini adalah pesan test dari dsBilling Enterprise WhatsApp Gateway. Jika Anda menerima pesan ini, berarti koneksi WA Gateway Anda sudah berfungsi dengan baik. 👍';
    public ?string $testResult = null;
    public ?string $deviceStatus = null;
    public string $savedStatus = '';

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'pengaturan';
        $this->activePage = 'whatsapp';
    }

    public function authorizeAccess(): void
    {
        if (!Auth::check()) abort(403);
    }

    public function boot(): void
    {
        $this->authorizeAccess();
    }

    public function rules(): array
    {
        return [
            'connection.provider' => ['required', 'string', 'max:50'],
            'connection.api_token' => ['required', 'string', 'max:255'],
            'connection.base_url' => ['required', 'url', 'max:500'],
            'connection.device_id' => ['nullable', 'string', 'max:100'],
            'connection.sender_number' => ['nullable', 'string', 'max:20'],
            'connection.admin_number' => ['nullable', 'string', 'max:20'],
            'connection.webhook_url' => ['nullable', 'url', 'max:500'],
            'connection.webhook_secret' => ['nullable', 'string', 'max:255'],
            'connection.delay_per_message' => ['required', 'integer', 'min:1', 'max:60'],
            'connection.max_retry' => ['required', 'integer', 'min:1', 'max:10'],
            'connection.mode' => ['required', 'in:production,sandbox'],
        ];
    }

    public function updatedConnectionProvider(string $code): void
    {
        foreach ($this->providers as $p) {
            if ($p['code'] === $code) {
                $this->connection['base_url'] = $p['default_base_url'];
                break;
            }
        }
    }

    public function save(): void
    {
        $this->validate();
        try {
            $this->savedStatus = 'saved';
            session()->flash('success', 'Konfigurasi WhatsApp berhasil disimpan.');
        } catch (Throwable $e) {
            $this->savedStatus = 'error';
            session()->flash('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function checkDeviceStatus(): void
    {
        $this->deviceStatus = null;
        try {
            $status = match ($this->connection['provider']) {
                'fonnte' => Http::timeout(8)
                    ->withHeaders(['Authorization' => $this->connection['api_token']])
                    ->get('https://api.fonnte.com/device'),
                default => null,
            };

            if ($status && $status->successful()) {
                $data = $status->json();
                $this->deviceStatus = is_array($data) ? json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : (string)$status->body();
            } else {
                $this->deviceStatus = 'Device tidak terhubung atau device_id salah / API token tidak merespon.';
            }
        } catch (Throwable $e) {
            $this->deviceStatus = 'Error: ' . $e->getMessage();
        }
    }

    public function sendTest(): void
    {
        $this->validate([
            'testPhone' => ['required', 'string', 'max:20'],
            'testMessage' => ['required', 'string', 'max:1000'],
        ]);

        $this->testResult = null;
        try {
            $phone = preg_replace('/[^0-9]/', '', $this->testPhone);
            if (str_starts_with($phone, '0')) $phone = '62' . substr($phone, 1);

            $payload = match ($this->connection['provider']) {
                'fonnte' => [
                    'target' => $phone,
                    'message' => $this->testMessage,
                    'url' => '',
                    'delay' => $this->connection['delay_per_message'],
                ],
                'wablas' => [
                    'phone' => $phone,
                    'message' => $this->testMessage,
                    'secret' => false,
                    'spintax' => false,
                ],
                'whacenter' => [
                    'device_id' => $this->connection['device_id'],
                    'number' => $phone,
                    'message' => $this->testMessage,
                ],
                default => [],
            };

            $headers = match ($this->connection['provider']) {
                    'fonnte' => ['Authorization' => $this->connection['api_token']],
                    'wablas' => ['Wablas-Api-Key' => $this->connection['api_token']],
                    'whacenter' => [],
                    default => [],
                };

            $response = Http::timeout(15)
                ->withHeaders($headers)
                ->asForm()
                ->post($this->connection['base_url'], $payload);

            if ($response->successful()) {
                $this->testResult = json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                session()->flash('success', 'Test message berhasil dikirim.');
            } else {
                $this->testResult = 'HTTP ' . $response->status() . ': ' . $response->body();
                session()->flash('error', 'Gagal kirim test message. Lihat detail di Test Result.');
            }
        } catch (Throwable $e) {
            $this->testResult = 'Exception: ' . $e->getMessage();
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pengaturan.whatsapp.index');
    }
}
