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
            ['bank_name' => 'Bank BCA', 'account_no' => '1234567890', 'account_holder' => 'PT dsBilling Indonesia', 'enabled' => true],
            ['bank_name' => 'Bank BRI', 'account_no' => '0001234567890', 'account_holder' => 'PT dsBilling Indonesia', 'enabled' => true],
            ['bank_name' => 'Bank Mandiri', 'account_no' => '123-00-1234567-8', 'account_holder' => 'PT dsBilling Indonesia', 'enabled' => true],
            ['bank_name' => 'Bank BNI', 'account_no' => '0123456789', 'account_holder' => 'PT dsBilling Indonesia', 'enabled' => true],
            ['bank_name' => 'BSI (Bank Syariah Indonesia)', 'account_no' => '7123456789', 'account_holder' => 'PT dsBilling Indonesia', 'enabled' => false],
        ],
    ];

    public array $ewalletManual = [
        'enabled' => true,
        'require_attachment' => true,
        'providers' => [
            ['name' => 'GoPay', 'number' => '081234567890', 'holder' => 'PT dsBilling Indonesia', 'enabled' => true],
            ['name' => 'OVO', 'number' => '081234567890', 'holder' => 'PT dsBilling Indonesia', 'enabled' => true],
            ['name' => 'DANA', 'number' => '081234567890', 'holder' => 'PT dsBilling Indonesia', 'enabled' => true],
            ['name' => 'ShopeePay', 'number' => '081234567890', 'holder' => 'PT dsBilling Indonesia', 'enabled' => true],
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

    public function save(): void
    {
        $this->validate();
        try {
            $this->savedStatus = 'saved';
            session()->flash('success', 'Konfigurasi Payment Gateway berhasil disimpan.');
        } catch (Throwable $e) {
            $this->savedStatus = 'error';
            session()->flash('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function addBankAccount(): void
    {
        $this->manualBank['accounts'][] = [
            'bank_name' => '',
            'account_no' => '',
            'account_holder' => '',
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
        $this->ewalletManual['providers'][] = [
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
                ? 'https://api.sandbox.midtrans.com/v1/balance'
                : 'https://api.midtrans.com/v1/balance';
            if (empty($key)) {
                $this->balanceInfo = 'Server key belum diisi.';
                return;
            }
            $resp = Http::timeout(10)
                ->withBasicAuth($key, '')
                ->get($url);
            $this->balanceInfo = $resp->successful()
                ? json_encode($resp->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                : ('HTTP ' . $resp->status() . ': ' . $resp->body());
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
            $resp = Http::timeout(10)
                ->withBasicAuth($key, '')
                ->get('https://api.xendit.co/balance');
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
            $resp = Http::timeout(10)
                ->withHeaders(['Authorization' => 'Bearer ' . $key])
                ->get($url);
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
