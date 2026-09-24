<?php

namespace App\Livewire\Pengaturan\PaymentGateway;

use App\Livewire\AdminComponent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Throwable;

class Index extends AdminComponent
{
    public string $activeModule = 'pengaturan';
    public string $activePage = 'payment-gateway';
    public string $activeTab = 'midtrans';

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->savedStatus = '';
        $this->balanceInfo = null;
    }

    public array $general = [
        'mode'                    => 'sandbox',
        'success_url'             => '/payment/success',
        'pending_url'             => '/payment/pending',
        'error_url'               => '/payment/error',
        'webhook_base_url'        => '',
        'default_expiry_hours'    => 24,
        'auto_confirm_payment'    => true,
        'send_receipt_on_success' => true,
        'minimum_topup_min_amount'=> 10000,
        'invoice_prefix'          => 'INV-',
    ];

    public array $midtrans = [
        'enabled'             => true,
        'server_key_sandbox'  => '',
        'server_key_production'=> '',
        'client_key_sandbox'  => '',
        'client_key_production'=> '',
        'merchant_id'         => '',
        'enable_3ds'          => true,
        'enabled_channels'    => [
            'bank_transfer'   => true,
            'credit_card'     => true,
            'ewallet'         => true,
            'qris'            => true,
            'direct_debit'    => false,
            'store'           => true,
            'cardless_credit' => false,
        ],
        'custom_expiry'   => true,
        'expiry_unit'     => 'hour',
        'expiry_duration' => 24,
    ];

    // ✅ Xendit: corrected webhook field name (webhook_verification_token, bukan webhook_token)
    public array $xendit = [
        'enabled'                   => false,
        'secret_key_sandbox'        => '',
        'secret_key_production'     => '',
        'public_key_sandbox'        => '',
        'public_key_production'     => '',
        'webhook_verification_token'=> '',
        'enabled_channels'          => [
            'virtual_account' => true,
            'ewallet'         => true,
            'qr_codes'        => true,
            'retail_outlet'   => true,
            'direct_debit'    => false,
            'paylater'        => false,
            'card_payment'    => false,
        ],
        'va_banks' => [
            'BCA'               => true,
            'BNI'               => true,
            'BRI'               => true,
            'Mandiri'           => true,
            'BSI'               => true,
            'Permata'           => true,
            'CIMB'              => false,
            'BTN'               => false,
            'Danamon'           => false,
            'Sahabat Sampoerna' => false,
        ],
        'ewallet_providers' => [
            'OVO'       => true,
            'DANA'      => true,
            'LinkAja'   => true,
            'ShopeePay' => true,
            'GoPay'     => true,
            'JeniusPay' => false,
            'PayPal'    => false,
        ],
        'qris_provider'    => 'QRIS',
        'retail_providers' => [
            'Alfamart'  => true,
            'Indomaret' => true,
        ],
    ];

    // ✅ Duitku: kode channel diperbaiki & dilengkapi sesuai dokumentasi resmi Duitku
    public array $duitku = [
        'enabled'          => false,
        'merchant_code'    => '',
        'api_key_sandbox'  => '',
        'api_key_production'=> '',
        'callback_url'     => '',
        'return_url'       => '',
        'expiry_period'    => 1440,
        'enabled_channels' => [
            'VC'  => true,   // Kartu Kredit (Visa/MasterCard/JCB)
            'M2'  => true,   // Mandiri Virtual Account
            'BT'  => true,   // Permata Virtual Account
            'B1'  => true,   // CIMB Niaga Virtual Account
            'I1'  => true,   // BNI Virtual Account
            'VA'  => true,   // Maybank Virtual Account
            'A1'  => true,   // Alfamart ✅ ditambah
            'DN'  => true,   // DANA ✅ ditambah
            'OV'  => true,   // OVO ✅ ditambah
            'SFP' => true,   // ShopeePay ✅ ditambah
            'LA'  => false,  // LinkAja ✅ ditambah
            'GR'  => false,  // GrabPay ✅ ditambah
            'NQ'  => false,  // DOKU Wallet ✅ ditambah
            'QR'  => true,   // QRIS ✅ ditambah
            'FT'  => false,  // Pegadaian/Pos Indonesia
        ],
    ];

    // ✅ Tripay: kode channel dilengkapi (PERMATAVA, CIMBVA, MUAMALATVA, ALFAMIDI)
    public array $tripay = [
        'enabled'           => false,
        'merchant_code'     => '',
        'api_key_sandbox'   => '',
        'api_key_production'=> '',
        'private_key'       => '',
        'expiry_hours'      => 24,
        'enabled_channels'  => [
            'QRIS'       => true,
            'BRIVA'      => true,   // BRI Virtual Account
            'BNIVA'      => true,   // BNI Virtual Account
            'BCAVA'      => true,   // BCA Virtual Account
            'MANDIRIVA'  => true,   // Mandiri Virtual Account
            'PERMATAVA'  => false,  // Permata Virtual Account ✅ ditambah
            'CIMBVA'     => false,  // CIMB Niaga Virtual Account ✅ ditambah
            'MUAMALATVA' => false,  // Muamalat Virtual Account ✅ ditambah
            'ALFAMART'   => true,
            'INDOMARET'  => true,
            'SHOPEEPAY'  => true,
            'DANA'       => true,
            'OVO'        => true,
            'ALFAMIDI'   => false,  // ✅ ditambah
        ],
    ];

    public array $ipaymu = [
        'enabled'           => false,
        'va_number'         => '',
        'api_key_sandbox'   => '',
        'api_key_production'=> '',
        'enabled_channels'  => [
            'qris'             => true,
            'va_bca'           => true,
            'va_mandiri'       => true,
            'va_bni'           => true,
            'va_bri'           => true,
            'va_cimb'          => true,
            'cstore_alfamart'  => true,
            'cstore_indomaret' => true,
        ],
    ];

    public array $manualBank = [
        'enabled'            => true,
        'auto_approve_manual'=> false,
        'require_attachment' => true,
        'max_wait_hours'     => 48,
        'accounts'           => [
            ['id' => 1, 'bank_name' => 'Bank BCA',                    'account_number' => '1234567890',      'account_holder' => 'PT dsBilling Indonesia', 'branch' => '', 'enabled' => true],
            ['id' => 2, 'bank_name' => 'Bank BRI',                    'account_number' => '0001234567890',   'account_holder' => 'PT dsBilling Indonesia', 'branch' => '', 'enabled' => true],
            ['id' => 3, 'bank_name' => 'Bank Mandiri',                'account_number' => '123-00-1234567-8','account_holder' => 'PT dsBilling Indonesia', 'branch' => '', 'enabled' => true],
            ['id' => 4, 'bank_name' => 'Bank BNI',                    'account_number' => '0123456789',      'account_holder' => 'PT dsBilling Indonesia', 'branch' => '', 'enabled' => true],
            ['id' => 5, 'bank_name' => 'BSI (Bank Syariah Indonesia)','account_number' => '7123456789',      'account_holder' => 'PT dsBilling Indonesia', 'branch' => '', 'enabled' => false],
        ],
    ];

    public array $ewalletManual = [
        'enabled'            => true,
        'require_attachment' => true,
        'providers'          => [
            ['id' => 1, 'name' => 'GoPay',     'number' => '081234567890', 'holder' => 'PT dsBilling Indonesia', 'enabled' => true],
            ['id' => 2, 'name' => 'OVO',       'number' => '081234567890', 'holder' => 'PT dsBilling Indonesia', 'enabled' => true],
            ['id' => 3, 'name' => 'DANA',      'number' => '081234567890', 'holder' => 'PT dsBilling Indonesia', 'enabled' => true],
            ['id' => 4, 'name' => 'ShopeePay', 'number' => '081234567890', 'holder' => 'PT dsBilling Indonesia', 'enabled' => true],
        ],
    ];

    public ?string $balanceInfo = null;
    public string $savedStatus = '';

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'pengaturan';
        $this->activePage   = 'payment-gateway';

        $svc = app(\App\Services\Pengaturan\PaymentGatewaySettingsService::class);
        $all = $svc->getAll();

        if (isset($all['midtrans'])) {
            $this->midtrans = array_merge($this->midtrans, $all['midtrans']);
        }
        if (isset($all['xendit'])) {
            // Migrasi nama field lama webhook_token → webhook_verification_token
            if (isset($all['xendit']['webhook_token']) && !isset($all['xendit']['webhook_verification_token'])) {
                $all['xendit']['webhook_verification_token'] = $all['xendit']['webhook_token'];
                unset($all['xendit']['webhook_token']);
            }
            $this->xendit = array_merge($this->xendit, $all['xendit']);
        }
        if (isset($all['duitku'])) {
            $this->duitku = array_merge($this->duitku, $all['duitku']);
        }
        if (isset($all['tripay'])) {
            $this->tripay = array_merge($this->tripay, $all['tripay']);
        }
        if (isset($all['ipaymu'])) {
            $this->ipaymu = array_merge($this->ipaymu, $all['ipaymu']);
        }
        if (isset($all['manual_transfer'])) {
            $this->manualBank['accounts']           = $all['manual_transfer']['bank_accounts'] ?? [];
            $this->manualBank['enabled']            = $all['manual_transfer']['enabled'] ?? true;
            $this->manualBank['auto_approve_manual'] = $all['manual_transfer']['auto_approve_manual'] ?? false;
            $this->manualBank['require_attachment']  = $all['manual_transfer']['require_attachment'] ?? true;
            $this->manualBank['max_wait_hours']      = $all['manual_transfer']['max_wait_hours'] ?? 48;
        }
        if (isset($all['manual_ewallet'])) {
            $this->ewalletManual['providers']          = $all['manual_ewallet']['providers'] ?? [];
            $this->ewalletManual['enabled']            = $all['manual_ewallet']['enabled'] ?? true;
            $this->ewalletManual['require_attachment'] = $all['manual_ewallet']['require_attachment'] ?? true;
        }

        $this->general['mode']                    = \App\Models\Setting::getValue('payment_gateway.general.mode', 'sandbox');
        $this->general['invoice_prefix']          = \App\Models\Setting::getValue('payment_gateway.general.invoice_prefix', 'INV-');
        $this->general['default_expiry_hours']    = \App\Models\Setting::getValue('payment_gateway.general.default_expiry_hours', 24);
        $this->general['success_url']             = \App\Models\Setting::getValue('payment_gateway.general.success_url', '/payment/success');
        $this->general['pending_url']             = \App\Models\Setting::getValue('payment_gateway.general.pending_url', '/payment/pending');
        $this->general['error_url']               = \App\Models\Setting::getValue('payment_gateway.general.error_url', '/payment/error');
        $this->general['auto_confirm_payment']    = \App\Models\Setting::getValue('payment_gateway.general.auto_confirm_payment', true);
        $this->general['send_receipt_on_success'] = \App\Models\Setting::getValue('payment_gateway.general.send_receipt_on_success', true);
        $this->general['minimum_topup_min_amount']= \App\Models\Setting::getValue('payment_gateway.general.minimum_topup_min_amount', 10000);
        $this->general['webhook_base_url']        = \App\Models\Setting::getValue('payment_gateway.general.webhook_base_url', '');
    }

    public function authorizeAccess(): void
    {
        if (!Auth::check()) abort(403);
    }

    public function boot(): void
    {
        $this->authorizeAccess();
    }

    // ─── Per-tab Save Methods ────────────────────────────────────────────────

    public function saveMidtrans(\App\Services\Pengaturan\PaymentGatewaySettingsService $svc): void
    {
        try {
            $svc->save('midtrans', $this->midtrans);
            $this->savedStatus = 'saved';
            session()->flash('success', 'Konfigurasi Midtrans berhasil disimpan.');
        } catch (Throwable $e) {
            $this->savedStatus = 'error';
            session()->flash('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function saveXendit(\App\Services\Pengaturan\PaymentGatewaySettingsService $svc): void
    {
        try {
            $svc->save('xendit', $this->xendit);
            $this->savedStatus = 'saved';
            session()->flash('success', 'Konfigurasi Xendit berhasil disimpan.');
        } catch (Throwable $e) {
            $this->savedStatus = 'error';
            session()->flash('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function saveDuitku(\App\Services\Pengaturan\PaymentGatewaySettingsService $svc): void
    {
        try {
            $svc->save('duitku', $this->duitku);
            $this->savedStatus = 'saved';
            session()->flash('success', 'Konfigurasi Duitku berhasil disimpan.');
        } catch (Throwable $e) {
            $this->savedStatus = 'error';
            session()->flash('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function saveTripay(\App\Services\Pengaturan\PaymentGatewaySettingsService $svc): void
    {
        try {
            $svc->save('tripay', $this->tripay);
            $this->savedStatus = 'saved';
            session()->flash('success', 'Konfigurasi Tripay berhasil disimpan.');
        } catch (Throwable $e) {
            $this->savedStatus = 'error';
            session()->flash('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function saveIpaymu(\App\Services\Pengaturan\PaymentGatewaySettingsService $svc): void
    {
        try {
            $svc->save('ipaymu', $this->ipaymu);
            $this->savedStatus = 'saved';
            session()->flash('success', 'Konfigurasi iPaymu berhasil disimpan.');
        } catch (Throwable $e) {
            $this->savedStatus = 'error';
            session()->flash('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function saveManualTransfer(\App\Services\Pengaturan\PaymentGatewaySettingsService $svc): void
    {
        try {
            $svc->save('manual_transfer', [
                'enabled'             => $this->manualBank['enabled'] ?? true,
                'auto_approve_manual' => $this->manualBank['auto_approve_manual'] ?? false,
                'require_attachment'  => $this->manualBank['require_attachment'] ?? true,
                'max_wait_hours'      => $this->manualBank['max_wait_hours'] ?? 48,
                'bank_accounts'       => $this->manualBank['accounts'] ?? [],
            ]);
            $this->savedStatus = 'saved';
            session()->flash('success', 'Rekening bank manual berhasil disimpan.');
        } catch (Throwable $e) {
            $this->savedStatus = 'error';
            session()->flash('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function saveEwallet(\App\Services\Pengaturan\PaymentGatewaySettingsService $svc): void
    {
        try {
            $svc->save('manual_ewallet', [
                'enabled'            => $this->ewalletManual['enabled'] ?? true,
                'require_attachment' => $this->ewalletManual['require_attachment'] ?? true,
                'providers'          => $this->ewalletManual['providers'] ?? [],
            ]);
            $this->savedStatus = 'saved';
            session()->flash('success', 'e-Wallet manual berhasil disimpan.');
        } catch (Throwable $e) {
            $this->savedStatus = 'error';
            session()->flash('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function saveGeneral(\App\Services\Pengaturan\PaymentGatewaySettingsService $svc): void
    {
        $this->validate([
            'general.mode'                => ['required', 'in:sandbox,production'],
            'general.success_url'         => ['required', 'string', 'max:255'],
            'general.pending_url'         => ['required', 'string', 'max:255'],
            'general.error_url'           => ['required', 'string', 'max:255'],
            'general.webhook_base_url'    => ['nullable', 'url', 'max:500'],
            'general.default_expiry_hours'=> ['required', 'integer', 'min:1', 'max:720'],
            'general.invoice_prefix'      => ['required', 'string', 'max:20'],
        ]);
        try {
            \App\Models\Setting::setValue('payment_gateway.general.mode', $this->general['mode'], 'string', 'payment_gateway');
            \App\Models\Setting::setValue('payment_gateway.general.invoice_prefix', $this->general['invoice_prefix'], 'string', 'payment_gateway');
            \App\Models\Setting::setValue('payment_gateway.general.default_expiry_hours', $this->general['default_expiry_hours'], 'integer', 'payment_gateway');
            \App\Models\Setting::setValue('payment_gateway.general.success_url', $this->general['success_url'], 'string', 'payment_gateway');
            \App\Models\Setting::setValue('payment_gateway.general.pending_url', $this->general['pending_url'], 'string', 'payment_gateway');
            \App\Models\Setting::setValue('payment_gateway.general.error_url', $this->general['error_url'], 'string', 'payment_gateway');
            \App\Models\Setting::setValue('payment_gateway.general.webhook_base_url', $this->general['webhook_base_url'] ?? '', 'string', 'payment_gateway');
            \App\Models\Setting::setValue('payment_gateway.general.auto_confirm_payment', $this->general['auto_confirm_payment'] ?? true, 'boolean', 'payment_gateway');
            \App\Models\Setting::setValue('payment_gateway.general.send_receipt_on_success', $this->general['send_receipt_on_success'] ?? true, 'boolean', 'payment_gateway');
            \App\Models\Setting::setValue('payment_gateway.general.minimum_topup_min_amount', $this->general['minimum_topup_min_amount'] ?? 10000, 'integer', 'payment_gateway');
            $this->savedStatus = 'saved';
            session()->flash('success', 'Pengaturan umum berhasil disimpan.');
        } catch (Throwable $e) {
            $this->savedStatus = 'error';
            session()->flash('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // ─── Manual List Operations ──────────────────────────────────────────────

    public function addBankAccount(): void
    {
        $this->manualBank['accounts'][] = [
            'id'             => count($this->manualBank['accounts'] ?? []) + 1,
            'bank_name'      => '',
            'account_number' => '',
            'account_holder' => '',
            'branch'         => '',
            'enabled'        => true,
        ];
    }

    public function removeBankAccount(int $index): void
    {
        if (isset($this->manualBank['accounts'][$index])) {
            unset($this->manualBank['accounts'][$index]);
            $this->manualBank['accounts'] = array_values($this->manualBank['accounts']);
        }
    }

    public function addEwallet(): void
    {
        $this->ewalletManual['providers'][] = [
            'id'      => count($this->ewalletManual['providers'] ?? []) + 1,
            'name'    => '',
            'number'  => '',
            'holder'  => '',
            'enabled' => true,
        ];
    }

    public function removeEwallet(int $index): void
    {
        if (isset($this->ewalletManual['providers'][$index])) {
            unset($this->ewalletManual['providers'][$index]);
            $this->ewalletManual['providers'] = array_values($this->ewalletManual['providers']);
        }
    }

    // ─── Test Connections ────────────────────────────────────────────────────

    public function testMidtransBalance(): void
    {
        $this->balanceInfo = null;
        try {
            $key = $this->general['mode'] === 'sandbox'
                ? $this->midtrans['server_key_sandbox']
                : $this->midtrans['server_key_production'];
            $url = $this->general['mode'] === 'sandbox'
                ? 'https://api.sandbox.midtrans.com/v2/DSB-TEST-PING/status'
                : 'https://api.midtrans.com/v2/DSB-TEST-PING/status';
            if (empty($key)) { $this->balanceInfo = '⚠ Server key belum diisi.'; return; }
            $resp = Http::timeout(10)->withBasicAuth($key, '')->acceptJson()->get($url);
            $this->balanceInfo = match(true) {
                $resp->status() === 401 => '❌ HTTP 401 Unauthorized — Server Key salah atau mode tidak sesuai (' . $this->general['mode'] . ').',
                $resp->status() === 404 => '✅ Koneksi Berhasil! Server Key valid (Mode: ' . strtoupper($this->general['mode']) . ').',
                default                 => 'HTTP ' . $resp->status() . ': ' . $resp->body(),
            };
        } catch (Throwable $e) {
            $this->balanceInfo = 'Error: ' . $e->getMessage();
        }
    }

    public function testXenditBalance(): void
    {
        $this->balanceInfo = null;
        try {
            $key = $this->general['mode'] === 'sandbox'
                ? $this->xendit['secret_key_sandbox']
                : $this->xendit['secret_key_production'];
            if (empty($key)) { $this->balanceInfo = '⚠ Secret key belum diisi.'; return; }
            $resp = Http::timeout(10)->withBasicAuth($key, '')->get('https://api.xendit.co/balance');
            $this->balanceInfo = $resp->successful()
                ? '✅ OK — ' . json_encode($resp->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                : '❌ HTTP ' . $resp->status() . ': ' . $resp->body();
        } catch (Throwable $e) {
            $this->balanceInfo = 'Error: ' . $e->getMessage();
        }
    }

    public function testTripayBalance(): void
    {
        $this->balanceInfo = null;
        try {
            $key = $this->general['mode'] === 'sandbox'
                ? $this->tripay['api_key_sandbox']
                : $this->tripay['api_key_production'];
            $url = $this->general['mode'] === 'sandbox'
                ? 'https://tripay.co.id/api-sandbox/merchant/balance'
                : 'https://tripay.co.id/api/merchant/balance';
            if (empty($key)) { $this->balanceInfo = '⚠ API key belum diisi.'; return; }
            $resp = Http::timeout(10)->withHeaders(['Authorization' => 'Bearer ' . $key])->get($url);
            $this->balanceInfo = $resp->successful()
                ? '✅ OK — ' . json_encode($resp->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                : '❌ HTTP ' . $resp->status() . ': ' . $resp->body();
        } catch (Throwable $e) {
            $this->balanceInfo = 'Error: ' . $e->getMessage();
        }
    }

    public function testIpaymuBalance(): void
    {
        $this->balanceInfo = null;
        try {
            $key = $this->general['mode'] === 'sandbox'
                ? $this->ipaymu['api_key_sandbox']
                : $this->ipaymu['api_key_production'];
            
            if (empty($key) || empty($this->ipaymu['va_number'])) { 
                $this->balanceInfo = '⚠️ API key / VA Number belum diisi.'; 
                return; 
            }
            // iPaymu requires signature generation to hit their API. 
            // We just show a placeholder since hitting their balance API requires full signature calculation.
            $this->balanceInfo = 'ℹ️ Fitur cek koneksi iPaymu akan diimplementasikan bersama signature generation.';
        } catch (Throwable $e) {
            $this->balanceInfo = 'Error: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.pengaturan.payment-gateway.index');
    }
}
