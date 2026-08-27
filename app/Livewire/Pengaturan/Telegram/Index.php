<?php

namespace App\Livewire\Pengaturan\Telegram;

use App\Livewire\AdminComponent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Throwable;

class Index extends AdminComponent
{
    public string $activeModule = 'pengaturan';
    public string $activePage = 'telegram';

    public array $bot = [
        'bot_token' => '',
        'bot_username' => '',
        'webhook_url' => '',
        'webhook_secret' => '',
        'use_webhook' => false,
        'polling_interval' => 5,
        'parse_mode' => 'HTML',
    ];

    public array $chatIds = [
        ['id' => '-1001234567890', 'name' => 'Group Billing', 'type' => 'supergroup', 'enabled' => true, 'events' => 'billing,outage,alarm'],
        ['id' => '6281234567890', 'name' => 'Owner (WhatsApp mapped via bridge)', 'type' => 'private', 'enabled' => true, 'events' => 'daily_report'],
        ['id' => '123456789', 'name' => 'NOC Admin', 'type' => 'private', 'enabled' => true, 'events' => 'alarm,ticket'],
    ];

    public array $templates = [
        [
            'code' => 'INVOICE_UNPAID',
            'name' => 'Reminder Tagihan Belum Lunas',
            'content' => "Halo {{member_name}}, tagihan Anda periode {{invoice_period}} senilai Rp {{invoice_total}} (Jatuh Tempo: {{due_date}}) belum dibayar. Silakan lakukan pembayaran agar layanan tidak terisolir. Terima kasih.",
            'enabled' => true,
        ],
        [
            'code' => 'PAYMENT_RECEIVED',
            'name' => 'Pembayaran Berhasil',
            'content' => "Halo {{member_name}}, kami terima pembayaran tagihan #{{invoice_number}} senilai Rp {{amount}} pada {{payment_time}}. Terima kasih atas kepercayaannya bersama {{company_name}}.",
            'enabled' => true,
        ],
        [
            'code' => 'OUTAGE_ALERT',
            'name' => 'Notifikasi Gangguan Jaringan',
            'content' => "Peringatan Gangguan: {{router_name}} status DOWN sejak {{down_time}}. Impact: {{customer_affected}} pelanggan. Cek segera NOC.",
            'enabled' => true,
        ],
        [
            'code' => 'NOC_ALARM_CRITICAL',
            'name' => 'Alarm NOC Critical',
            'content' => "ALARM CRITICAL - {{alarm_time}}: {{alarm_message}}. Device: {{device_name}} ({{device_ip}}). {{additional_info}}",
            'enabled' => true,
        ],
        [
            'code' => 'TICKET_OPENED',
            'name' => 'Tiket Support Dibuat',
            'content' => "Tiket baru #{{ticket_id}} ({{ticket_priority}}): {{ticket_title}} untuk {{customer_name}}. Segera ditindaklanjuti.",
            'enabled' => true,
        ],
        [
            'code' => 'DAILY_FINANCE',
            'name' => 'Laporan Keuangan Harian',
            'content' => "Rekap Harian {{date}}: Income Rp {{daily_income}}, Expense Rp {{daily_expense}}, Net Rp {{daily_net}}. Tickets Open: {{tickets_open}}. User Online: {{online_users}}.",
            'enabled' => true,
        ],
    ];

    public string $testChatId = '';
    public string $testMessage = 'Ini adalah test message dari dsBilling Enterprise via Telegram Bot API.';
    public ?string $testResult = null;
    public ?string $botInfo = null;
    public ?string $webhookInfo = null;
    public string $savedStatus = '';

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'pengaturan';
        $this->activePage = 'telegram';
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
            'bot.bot_token' => ['required', 'string', 'max:120'],
            'bot.bot_username' => ['nullable', 'string', 'max:60'],
            'bot.webhook_url' => ['nullable', 'url', 'max:255'],
            'bot.polling_interval' => ['required', 'integer', 'min:1', 'max:60'],
        ];
    }

    public function save(): void
    {
        try {
            $valid = $this->validate();
            $this->savedStatus = 'success';
            session()->flash('success', 'Konfigurasi Telegram Bot disimpan.');
        } catch (Throwable $e) {
            $this->savedStatus = 'error';
            $this->addError('bot', 'Gagal simpan: ' . $e->getMessage());
        }
    }

    public function getBotInfo(): void
    {
        try {
            if (empty($this->bot['bot_token'])) {
                $this->botInfo = 'Isi dulu Bot Token (dari @BotFather).';
                return;
            }
            $resp = Http::timeout(5)->get('https://api.telegram.org/bot' . $this->bot['bot_token'] . '/getMe');
            if ($resp->successful() && ($resp->json('ok') ?? false)) {
                $u = $resp->json('result') ?? [];
                $this->botInfo = "Bot Aktif: @" . ($u['username'] ?? '?') . " (ID: " . ($u['id'] ?? '?') . ") - " . ($u['first_name'] ?? '');
                if (empty($this->bot['bot_username'])) $this->bot['bot_username'] = (string)($u['username'] ?? '');
            } else {
                $this->botInfo = 'Gagal ambil info bot: ' . substr($resp->body(), 0, 200);
            }
        } catch (Throwable $e) {
            $this->botInfo = 'ERROR: ' . $e->getMessage();
        }
    }

    public function setWebhook(): void
    {
        try {
            if (empty($this->bot['bot_token']) || empty($this->bot['webhook_url'])) {
                $this->webhookInfo = 'Bot Token & Webhook URL wajib diisi.';
                return;
            }
            $url = 'https://api.telegram.org/bot' . $this->bot['bot_token'] . '/setWebhook';
            $data = ['url' => $this->bot['webhook_url']];
            if (!empty($this->bot['webhook_secret'])) $data['secret_token'] = $this->bot['webhook_secret'];
            $resp = Http::timeout(5)->post($url, $data);
            if ($resp->successful()) {
                $this->webhookInfo = 'Webhook SET: ' . substr($resp->body(), 0, 200);
            } else {
                $this->webhookInfo = 'Gagal set webhook: ' . substr($resp->body(), 0, 200);
            }
        } catch (Throwable $e) {
            $this->webhookInfo = 'ERROR: ' . $e->getMessage();
        }
    }

    public function deleteWebhook(): void
    {
        try {
            if (empty($this->bot['bot_token'])) return;
            $resp = Http::timeout(5)->get('https://api.telegram.org/bot' . $this->bot['bot_token'] . '/deleteWebhook');
            $this->webhookInfo = 'Webhook DELETE: ' . ($resp->body() ?? '');
        } catch (Throwable $e) {
            $this->webhookInfo = 'ERROR: ' . $e->getMessage();
        }
    }

    public function sendTest(): void
    {
        try {
            if (empty($this->bot['bot_token']) || empty($this->testChatId)) {
                $this->testResult = 'Isi Bot Token dan Chat ID terlebih dahulu.';
                return;
            }
            $resp = Http::timeout(5)->post('https://api.telegram.org/bot' . $this->bot['bot_token'] . '/sendMessage', [
                'chat_id' => $this->testChatId,
                'text' => $this->testMessage,
                'parse_mode' => $this->bot['parse_mode'] ?: 'HTML',
            ]);
            if ($resp->successful() && ($resp->json('ok') ?? false)) {
                $r = $resp->json('result') ?? [];
                $this->testResult = 'OK: Message #' . ($r['message_id'] ?? '?') . ' terkirim ke ' . $this->testChatId . '.';
            } else {
                $this->testResult = 'Gagal kirim: ' . substr($resp->body(), 0, 250);
            }
        } catch (Throwable $e) {
            $this->testResult = 'ERROR: ' . $e->getMessage();
        }
    }

    public function addChatId(): void
    {
        $this->chatIds[] = ['id' => '', 'name' => 'Baru', 'type' => 'private', 'enabled' => true, 'events' => ''];
    }

    public function removeChatId(int $i): void
    {
        if (isset($this->chatIds[$i])) unset($this->chatIds[$i]);
        $this->chatIds = array_values($this->chatIds);
    }

    public function render()
    {
        return view('livewire.pengaturan.telegram.index');
    }
}
