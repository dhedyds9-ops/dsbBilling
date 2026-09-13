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
    }

    public array $general = [
        'mode' => 'sandbox',
        'success_url' => '/payment/success',
        'pending_url' => '/payment/pending',
        'error_url' => '/payment/error',
        'webhook_base_url' => '',
        'default_expiry_hours' => 24,
        'auto_confirm_payment' => true,
        'send_receipt_on_success' => true,
        'minimum_topup_min_amount' => 10000,
        'invoice_prefix' => 'INV-',
    ];

    public array $midtrans = [
        'enabled' => true,
        'server_key_sandbox' => '',
        'server_key_production' => '',
        'client_key_sandbox' => '',
        'client_key_production' => '',
        'merchant_id' => '',
        'enable_3ds' => true,
        'enabled_channels' => [
            'bank_transfer' => true,
            'credit_card' => true,
            'ewallet' => true,
            'qris' => true,
            'direct_debit' => false,
            'store' => true,
            'cardless_credit' => false,
        ],
        'custom_expiry' => true,
        'expiry_unit' => 'hour',
        'expiry_duration' => 24,
    ];

    public array $xendit = [
        'enabled' => false,
        'secret_key_sandbox' => '',
        'secret_key_production' => '',
        'public_key_sandbox' => '',
        'public_key_production' => '',
        'webhook_token' => '',
        'enabled_channels' => [
            'virtual_account' => true,
            'ewallet' => true,
            'qr_codes' => true,
            'retail_outlet' => true,
            'direct_debit' => false,
            'paylater' => false,
            'card_payment' => false,
        ],
        'va_banks' => [
            'BCA' => true,
            'BNI' => true,
            'BRI' => true,
            'Mandiri' => true,
            'BSI' => true,
            'Permata' => true,
            'CIMB' => false,
            'BTN' => false,
            'Danamon' => false,
        ],
        'ewallet_providers' => [
            'OVO' => true,
            'DANA' => true,
            'LinkAja' => true,
            'ShopeePay' => true,
            'GoPay' => true,
            'JeniusPay' => false,
            'PayPal' => false,
        ],
        'qris_provider' => 'QRIS',
        'retail_providers' => [
            'Alfamart' => true,
            'Indomaret' => true,
            'Lawson' => false,
        ],
    ];

    public array $duitku = [
        'enabled' => false,
        'merchant_code' => '',
        'api_key_sandbox' => '',
        'api_key_production' => '',
        'callback_url' => '',
        'return_url' => '',
        'expiry_period' => 1440,
        'enabled_channels' => [
            'VC' => true,
            'M2' => true,
            'BT' => true,
            'B1' => true,
            'I1' => true,
            'VA' => true,
            'FT' => false,
        ],
    ];

    public array $tripay = [
        'enabled' => false,
        'merchant_code' => '',
        'api_key_sandbox' => '',
        'api_key_production' => '',
        'private_key' => '',
        'expiry_hours' => 24,
        'enabled_channels' => [
            'BRIVA' => true,
            'BNIVA' => true,
            'BCAVA' => true,
            'MANDIRIVA' => true,
            'ALFAMART' => true,
            'INDOMARET' => true,
            'QRIS' => true,
            'OVO' => true,
            'DANA' => true,
            'SHOPEEPAY' => true,
            'GOPAY' => true,
        ],
    ];

    public array $manualBank = [
        'enabled' => true,
        'auto_approve_manual' => false,
        'require_attachment' => true,
        'max_wait_hours' => 48,
        'accounts' => [
            ['id' => 1, 'bank_name' => 'Bank BCA', 'account_number' => '1234567890', 'account_holder' => 'PT dsBilling Indonesia', 'branch' => '', 'enabled' => true],
            ['id' => 2, 'bank_name' => 'Bank BRI', 'account_number' => '0001234567890', 'account_holder' => 'PT dsBilling Indonesia', 'branch' => '', 'enabled' => true],
            ['id' => 3, 'bank_name' => 'Bank Mandiri', 'account_number' => '123-00-1234567-8', 'account_holder' => 'PT dsBilling Indonesia', 'branch' => '', 'enabled' => true],
            ['id' => 4, 'bank_name' => 'Bank BNI', 'account_number' => '0123456789', 'account_holder' => 'PT dsBilling Indonesia', 'branch' => '', 'enabled' => true],
            ['id' => 5, 'bank_name' => 'BSI (Bank Syariah Indonesia)', 'account_number' => '7123456789', 'account_holder' => 'PT dsBilling Indonesia', 'branch' => '', 'enabled' => false],
        ],
    ];

    public array $ewalletManual = [
        'enabled' => true,
        'require_attachment' => true,
        'providers' => [
            ['id' => 1, 'name' => 'GoPay', 'number' => '081234567890', 'holder' => 'PT dsBilling Indonesia', 'enabled' => true],
            ['id' => 2, 'name' => 'OVO', 'number' => '081234567890', 'holder' => 'PT dsBilling Indonesia', 'enabled' => true],
            ['id' => 3, 'name' => 'DANA', 'number' => '081234567890', 'holder' => 'PT dsBilling Indonesia', 'enabled' => true],
            ['id' => 4, 'name' => 'ShopeePay', 'number' => '081234567890', 'holder' => 'PT dsBilling Indonesia', 'enabled' => true],
        ],
    ];

    public string $testAmount = '10000';
    public ?string $testResult = null;
    public ?string $balanceInfo = null;
    public string $savedStatus = '';

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'pengaturan';
        $this->activePage = 'payment-gateway';

        $svc = app(\App\Services\Pengaturan\PaymentGatewaySettingsService::class);
        $all = $svc->getAll();
        if (isset($all['midtrans'])) $this->midtrans = array_merge($this->midtrans, $all['midtrans']);
        if (isset($all['xendit'])) $this->xendit = array_merge($this->xendit, $all['xendit']);
        if (isset($all['duitku'])) $this->duitku = array_merge($this->duitku, $all['duitku']);
        if (isset($all['tripay'])) $this->tripay = array_merge($this->tripay, $all['tripay']);
        if (isset($all['manual_transfer'])) {
            $this->manualBank['accounts'] = $all['manual_transfer']['bank_accounts'] ?? [];
            $this->manualBank['enabled'] = $all['manual_transfer']['enabled'] ?? true;
        }
        if (isset($all['manual_ewallet'])) {
            $this->ewalletManual['providers'] = $all['manual_ewallet']['providers'] ?? [];
            $this->ewalletManual['enabled'] = $all['manual_ewallet']['enabled'] ?? true;
            $this->ewalletManual['require_attachment'] = $all['manual_ewallet']['require_attachment'] ?? true;
        }

        $this->general['mode'] = \App\Models\Setting::getValue('payment_gateway.general.mode', 'sandbox');
        $this->general['invoice_prefix'] = \App\Models\Setting::getValue('payment_gateway.general.invoice_prefix', 'INV-');
        $this->general['default_expiry_hours'] = \App\Models\Setting::getValue('payment_gateway.general.default_expiry_hours', 24);
        $this->general['success_url'] = \App\Models\Setting::getValue('payment_gateway.general.success_url', '/payment/success');
        $this->general['pending_url'] = \App\Models\Setting::getValue('payment_gateway.general.pending_url', '/payment/pending');
        $this->general['error_url'] = \App\Models\Setting::getValue('payment_gateway.general.error_url', '/payment/error');
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
            'general.mode' => ['required', 'in:sandbox,production'],
            'general.success_url' => ['required', 'string', 'max:255'],
            'general.pending_url' => ['required', 'string', 'max:255'],
            'general.error_url' => ['required', 'string', 'max:255'],
            'general.webhook_base_url' => ['nullable', 'url', 'max:500'],
            'general.default_expiry_hours' => ['required', 'integer', 'min:1', 'max:720'],
            'general.invoice_prefix' => ['required', 'string', 'max:20'],
            'midtrans.merchant_id' => ['nullable', 'string', 'max:50'],
            'midtrans.server_key_sandbox' => ['nullable', 'string', 'max:255'],
            'midtrans.server_key_production' => ['nullable', 'string', 'max:255'],
            'xendit.secret_key_sandbox' => ['nullable', 'string', 'max:255'],
            'xendit.secret_key_production' => ['nullable', 'string', 'max:255'],
            'duitku.merchant_code' => ['nullable', 'string', 'max:50'],
            'duitku.api_key_sandbox' => ['nullable', 'string', 'max:255'],
            'tripay.merchant_code' => ['nullable', 'string', 'max:50'],
            'tripay.api_key_sandbox' => ['nullable', 'string', 'max:255'],
            'tripay.private_key' => ['nullable', 'string', 'max:255'],
            'testAmount' => ['nullable', 'numeric', 'min:10000', 'max:1000000000'],
        ];
    }

    public function save(\App\Services\Pengaturan\PaymentGatewaySettingsService $svc): void
    {
        $this->validate();
        try {
            $svc->save('midtrans', $this->midtrans);
            $svc->save('xendit', $this->xendit);
            $svc->save('duitku', $this->duitku);
            $svc->save('tripay', $this->tripay);
            $svc->save('manual_transfer', [
                'enabled' => $this->manualBank['enabled'] ?? true,
                'bank_accounts' => $this->manualBank['accounts'] ?? []
            ]);
            $svc->save('manual_ewallet', [
                'enabled' => $this->ewalletManual['enabled'] ?? true,
                'require_attachment' => $this->ewalletManual['require_attachment'] ?? true,
                'providers' => $this->ewalletManual['providers'] ?? [],
            ]);

            \App\Models\Setting::setValue('payment_gateway.general.mode', $this->general['mode'], 'string', 'payment_gateway');
            \App\Models\Setting::setValue('payment_gateway.general.invoice_prefix', $this->general['invoice_prefix'], 'string', 'payment_gateway');
            \App\Models\Setting::setValue('payment_gateway.general.default_expiry_hours', $this->general['default_expiry_hours'], 'integer', 'payment_gateway');
            \App\Models\Setting::setValue('payment_gateway.general.success_url', $this->general['success_url'], 'string', 'payment_gateway');
            \App\Models\Setting::setValue('payment_gateway.general.pending_url', $this->general['pending_url'], 'string', 'payment_gateway');
            \App\Models\Setting::setValue('payment_gateway.general.error_url', $this->general['error_url'], 'string', 'payment_gateway');

            $this->savedStatus = 'saved';
            session()->flash('success', 'Konfigurasi Payment Gateway berhasil disimpan.');
        } catch (Throwable $e) {
            $this->savedStatus = 'error';
            session()->flash('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function addBankAccount(): void
    {
        $nextId = (count($this->manualBank['accounts'] ?? []) + 1);
        $this->manualBank['accounts'][] = [
            'id' => $nextId,
            'bank_name' => '',
            'account_number' => '',
            'account_holder' => '',
            'branch' => '',
            'enabled' => true,
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
        $nextId = (count($this->ewalletManual['providers'] ?? []) + 1);
        $this->ewalletManual['providers'][] = [
            'id' => $nextId,
            'name' => '',
            'number' => '',
            'holder' => '',
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
            if (empty($key)) {
                $this->balanceInfo = 'Server key belum diisi.';
                return;
            }
            $http = Http::timeout(10)->withBasicAuth($key, '')->acceptJson();
            if (app()->isLocal()) $http->withoutVerifying();
            $resp = $http->get($url);
            
            if ($resp->status() === 401) {
                $this->balanceInfo = 'Koneksi Gagal: HTTP 401 Unauthorized (Server Key salah).';
            } elseif ($resp->status() === 404) {
                // Midtrans returns 404 for missing transaction, which means Auth was actually SUCCESSFUL.
                $this->balanceInfo = 'Koneksi Berhasil! Server Key valid (Environment: ' . $this->general['mode'] . ').';
            } else {
                $this->balanceInfo = 'HTTP ' . $resp->status() . ': ' . $resp->body();
            }
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
            if (empty($key)) {
                $this->balanceInfo = 'Secret key belum diisi.';
                return;
            }
            $http = Http::timeout(10)->withBasicAuth($key, '');
            if (app()->isLocal()) $http->withoutVerifying();
            $resp = $http->get('https://api.xendit.co/balance');
            $this->balanceInfo = $resp->successful()
                ? json_encode($resp->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                : ('HTTP ' . $resp->status() . ': ' . $resp->body());
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
            if (empty($key)) {
                $this->balanceInfo = 'API key belum diisi.';
                return;
            }
            $http = Http::timeout(10)->withHeaders(['Authorization' => 'Bearer ' . $key]);
            if (app()->isLocal()) $http->withoutVerifying();
            $resp = $http->get($url);
            $this->balanceInfo = $resp->successful()
                ? json_encode($resp->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                : ('HTTP ' . $resp->status() . ': ' . $resp->body());
        } catch (Throwable $e) {
            $this->balanceInfo = 'Error: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.pengaturan.payment-gateway.index');
    }
}
