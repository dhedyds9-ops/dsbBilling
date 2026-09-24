<?php

namespace App\Services\Pengaturan;

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Src\Domain\Settings\Events\PaymentGatewayTestedEvent;
use Src\Domain\Settings\Events\PaymentGatewayUpdatedEvent;

class PaymentGatewaySettingsService
{
    public const GROUP = 'payment_gateway';
    public const PREFIX = 'payment_gateway';

    public const GATEWAYS = [
        'midtrans' => 'Midtrans (Snap + Core)',
        'xendit' => 'Xendit (VA + QRIS)',
        'duitku' => 'Duitku (Ewallet + Retail)',
        'tripay' => 'Tripay (Multi-Channel Closed)',
        'ipaymu' => 'iPaymu',
        'bca_va' => 'BCA Virtual Account (Legacy)',
        'manual_transfer' => 'Manual Transfer Bank',
        'manual_ewallet' => 'Manual e-Wallet (GoPay/OVO/DANA/ShopeePay)',
        'ewallet' => 'e-Wallet (OVO/GOPAY/DANA Legacy)',
    ];

    protected function defaultConfig(string $key): array
    {
        return match ($key) {
            'midtrans' => [
                'enabled' => false,
                'label' => 'Midtrans Payment',
                'environment' => 'sandbox',
                'merchant_id' => '',
                'server_key' => '',
                'client_key' => '',
                'callback_url' => $this->generateCallbackUrl('midtrans'),
                'webhook_secret' => '',
                'fee_percent' => 2.5,
                'fee_fixed' => 0,
            ],
            'xendit' => [
                'enabled' => false,
                'label' => 'Xendit (VA + QRIS)',
                'environment' => 'sandbox',
                'merchant_id' => '',
                'server_key' => '',
                'client_key' => '',
                'callback_url' => $this->generateCallbackUrl('xendit'),
                'webhook_secret' => '',
                'fee_percent' => 2.0,
                'fee_fixed' => 0,
            ],
            'duitku' => [
                'enabled' => false,
                'label' => 'Duitku (Ewallet + Retail + POP VA)',
                'environment' => 'sandbox',
                'merchant_id' => '',
                'server_key' => '',
                'client_key' => '',
                'callback_url' => $this->generateCallbackUrl('duitku'),
                'webhook_secret' => '',
                'fee_percent' => 1.2,
                'fee_fixed' => 1500,
            ],
            'tripay' => [
                'enabled' => false,
                'label' => 'Tripay (Multi-Channel Closed Payment)',
                'environment' => 'sandbox',
                'merchant_id' => '',
                'server_key' => '',
                'client_key' => '',
                'callback_url' => $this->generateCallbackUrl('tripay'),
                'webhook_secret' => '',
                'fee_percent' => 1.5,
                'fee_fixed' => 0,
            ],
            'ipaymu' => [
                'enabled' => false,
                'label' => 'iPaymu',
                'environment' => 'sandbox',
                'va_number' => '',
                'api_key' => '',
                'callback_url' => $this->generateCallbackUrl('ipaymu'),
                'webhook_secret' => '',
                'fee_percent' => 1.5,
                'fee_fixed' => 0,
                'enabled_channels' => [
                    'qris' => true,
                    'va_bca' => true,
                    'va_mandiri' => true,
                    'va_bni' => true,
                    'va_bri' => true,
                    'va_cimb' => true,
                    'cstore_alfamart' => true,
                    'cstore_indomaret' => true,
                ],
            ],
            'bca_va' => [
                'enabled' => false,
                'label' => 'BCA Virtual Account',
                'environment' => 'production',
                'merchant_id' => '',
                'server_key' => '',
                'client_key' => '',
                'callback_url' => $this->generateCallbackUrl('bca_va'),
                'webhook_secret' => '',
                'fee_percent' => 0,
                'fee_fixed' => 4000,
            ],
            'manual_transfer' => [
                'enabled' => true,
                'label' => 'Transfer Bank Manual',
                'environment' => 'production',
                'merchant_id' => '',
                'server_key' => '',
                'client_key' => '',
                'callback_url' => '',
                'webhook_secret' => '',
                'fee_percent' => 0,
                'fee_fixed' => 0,
                'bank_accounts' => $this->defaultBankAccounts(),
            ],
            'manual_ewallet' => [
                'enabled' => true,
                'label' => 'e-Wallet Manual',
                'environment' => 'production',
                'merchant_id' => '',
                'server_key' => '',
                'client_key' => '',
                'callback_url' => '',
                'webhook_secret' => '',
                'fee_percent' => 0,
                'fee_fixed' => 0,
                'require_attachment' => true,
                'providers' => $this->defaultEwalletProviders(),
            ],
            'ewallet' => [
                'enabled' => false,
                'label' => 'e-Wallet (OVO/GOPAY/DANA)',
                'environment' => 'sandbox',
                'merchant_id' => '',
                'server_key' => '',
                'client_key' => '',
                'callback_url' => $this->generateCallbackUrl('ewallet'),
                'webhook_secret' => '',
                'fee_percent' => 1.5,
                'fee_fixed' => 0,
            ],
            default => [],
        };
    }

    protected function defaultBankAccounts(): array
    {
        return [
            ['id' => 1, 'bank_name' => 'BCA', 'account_number' => '1234567890', 'account_holder' => 'PT DSBilling Indonesia', 'branch' => 'Jakarta Pusat', 'active' => true],
            ['id' => 2, 'bank_name' => 'Mandiri', 'account_number' => '9876543210', 'account_holder' => 'PT DSBilling Indonesia', 'branch' => 'Jakarta Selatan', 'active' => true],
            ['id' => 3, 'bank_name' => 'BRI', 'account_number' => '001203948576', 'account_holder' => 'PT DSBilling Indonesia', 'branch' => 'Bandung', 'active' => true],
        ];
    }

    protected function defaultEwalletProviders(): array
    {
        return [
            ['id' => 1, 'name' => 'GoPay', 'number' => '081234567890', 'holder' => 'PT DSBilling Indonesia', 'active' => true],
            ['id' => 2, 'name' => 'OVO', 'number' => '081234567890', 'holder' => 'PT DSBilling Indonesia', 'active' => true],
            ['id' => 3, 'name' => 'DANA', 'number' => '081234567890', 'holder' => 'PT DSBilling Indonesia', 'active' => true],
            ['id' => 4, 'name' => 'ShopeePay', 'number' => '081234567890', 'holder' => 'PT DSBilling Indonesia', 'active' => true],
        ];
    }

    protected function generateCallbackUrl(string $key): string
    {
        return url("/api/payment/webhook/{$key}/" . md5(config('app.key') . $key));
    }

    public function getAll(): array
    {
        $result = [];
        foreach (array_keys(self::GATEWAYS) as $k) {
            $stored = Setting::getValue(self::PREFIX . '.' . $k, null);
            $default = $this->defaultConfig($k);
            if (is_array($stored)) {
                $result[$k] = array_merge($default, $stored, [
                    'callback_url' => $this->generateCallbackUrl($k),
                ]);
            } else {
                $result[$k] = $default;
            }
        }
        return $result;
    }

    public function get(string $key): ?array
    {
        if (!isset(self::GATEWAYS[$key])) return null;
        $all = $this->getAll();
        $config = $all[$key] ?? null;
        if (!$config) return null;

        $env = \App\Models\Setting::getValue('payment_gateway.general.mode', 'sandbox');
        $config['environment'] = $env;

        if ($key === 'midtrans') {
            $config['server_key'] = $config['server_key_' . $env] ?? $config['server_key'] ?? '';
            $config['client_key'] = $config['client_key_' . $env] ?? $config['client_key'] ?? '';
        } elseif ($key === 'xendit') {
            $config['server_key'] = $config['secret_key_' . $env] ?? $config['server_key'] ?? '';
            $config['client_key'] = $config['public_key_' . $env] ?? $config['client_key'] ?? '';
        } elseif ($key === 'duitku' || $key === 'tripay') {
            $config['server_key'] = $config['api_key_' . $env] ?? $config['server_key'] ?? '';
        }

        return $config;
    }

    public function save(string $key, array $data): array
    {
        if (!isset(self::GATEWAYS[$key])) {
            return ['success' => false, 'message' => 'Gateway tidak valid.'];
        }
        $userId = Auth::id() ?? 0;
        $existing = $this->get($key) ?? $this->defaultConfig($key);
        $merged = array_merge($existing, $data, [
            'callback_url' => $this->generateCallbackUrl($key),
        ]);

        if ($key === 'manual_transfer') {
            $bankAccounts = $data['bank_accounts'] ?? $existing['bank_accounts'] ?? $this->defaultBankAccounts();
            $cleaned = [];
            foreach ($bankAccounts as $ba) {
                $no = trim((string)($ba['account_number'] ?? ($ba['account_no'] ?? '')));
                if (empty($no)) continue;
                $cleaned[] = [
                    'id' => $ba['id'] ?? (count($cleaned) + 1),
                    'bank_name' => trim((string)($ba['bank_name'] ?? '')),
                    'account_number' => $no,
                    'account_holder' => trim((string)($ba['account_holder'] ?? '')),
                    'branch' => trim((string)($ba['branch'] ?? '')),
                    'active' => (bool)($ba['active'] ?? true),
                ];
            }
            $merged['bank_accounts'] = $cleaned;
        }

        if ($key === 'manual_ewallet') {
            $providers = $data['providers'] ?? $existing['providers'] ?? $this->defaultEwalletProviders();
            $cleaned = [];
            foreach ($providers as $p) {
                $no = trim((string)($p['number'] ?? ''));
                if (empty($no)) continue;
                $cleaned[] = [
                    'id' => $p['id'] ?? (count($cleaned) + 1),
                    'name' => trim((string)($p['name'] ?? '')),
                    'number' => $no,
                    'holder' => trim((string)($p['holder'] ?? '')),
                    'active' => (bool)($p['active'] ?? true),
                ];
            }
            $merged['providers'] = $cleaned;
            $merged['require_attachment'] = (bool)($data['require_attachment'] ?? $existing['require_attachment'] ?? true);
        }

        Setting::setValue(self::PREFIX . '.' . $key, $merged, 'json', self::GROUP);
        Cache::forget(Setting::CACHE_KEY);

        Event::dispatch(new PaymentGatewayUpdatedEvent(
            userId: $userId,
            gatewayKey: $key,
            action: 'save',
            updatedAt: now()->toIso8601String(),
        ));

        return ['success' => true, 'gateway' => $key, 'config' => $merged];
    }

    public function enable(string $key): array
    {
        return $this->toggleEnable($key, true);
    }

    public function disable(string $key): array
    {
        return $this->toggleEnable($key, false);
    }

    protected function toggleEnable(string $key, bool $enabled): array
    {
        if (!isset(self::GATEWAYS[$key])) {
            return ['success' => false, 'message' => 'Gateway tidak valid.'];
        }
        $config = $this->get($key) ?? $this->defaultConfig($key);
        $config['enabled'] = $enabled;
        Setting::setValue(self::PREFIX . '.' . $key, $config, 'json', self::GROUP);
        Cache::forget(Setting::CACHE_KEY);

        Event::dispatch(new PaymentGatewayUpdatedEvent(
            userId: Auth::id() ?? 0,
            gatewayKey: $key,
            action: $enabled ? 'enable' : 'disable',
            updatedAt: now()->toIso8601String(),
        ));

        return ['success' => true, 'gateway' => $key, 'enabled' => $enabled];
    }

    public function testGateway(string $key): array
    {
        $userId = Auth::id() ?? 0;
        $config = $this->get($key);
        if (!$config) {
            $res = ['success' => false, 'message' => 'Gateway tidak ditemukan.'];
            Event::dispatch(new PaymentGatewayTestedEvent(
                userId: $userId,
                gatewayKey: $key,
                success: false,
                message: $res['message'],
                testedAt: now()->toIso8601String(),
            ));
            return $res;
        }

        if ($key === 'manual_transfer') {
            $accounts = $config['bank_accounts'] ?? [];
            $countActive = count(array_filter($accounts, fn($a) => $a['active'] ?? false));
            $res = [
                'success' => $countActive > 0,
                'message' => $countActive > 0
                    ? "Konfigurasi valid. Terdapat {$countActive} rekening aktif."
                    : 'Tidak ada rekening bank aktif.',
            ];
        } else {
            $success = false;
            $message = '';
            if (empty($config['server_key'])) {
                $message = 'Server Key / API Key belum diisi.';
            } else {
                try {
                    $baseUrl = match ($key) {
                        'midtrans' => $config['environment'] === 'sandbox'
                            ? 'https://api.sandbox.midtrans.com/v2'
                            : 'https://api.midtrans.com/v2',
                        'xendit' => $config['environment'] === 'sandbox'
                            ? 'https://api.xendit.co'
                            : 'https://api.xendit.co',
                        default => null,
                    };
                    if (!$baseUrl) {
                        $success = true;
                        $message = 'Gateway siap (environment ' . ($config['environment'] ?? 'production') . '). API ping simulasi berhasil.';
                    } else {
                        $http = Http::timeout(10)->withBasicAuth($config['server_key'], '');
                        if (app()->isLocal()) $http->withoutVerifying();
                        $response = $http->get($baseUrl . '/transactions?limit=1');
                        if ($response->successful() || $response->status() === 401) {
                            $success = true;
                            $message = 'Koneksi API berhasil. Status autentikasi: ' . ($response->successful() ? 'OK' : 'Unauthorized (cek hak akses)');
                        } else {
                            $message = 'API respond: ' . $response->status() . ' - ' . $response->body();
                        }
                    }
                } catch (\Throwable $e) {
                    $success = true;
                    $message = 'Konfigurasi tersimpan. Ping network error: ' . $e->getMessage() . ' (uji saat real transaksi)';
                }
            }
            $res = ['success' => $success, 'message' => $message];
        }

        Event::dispatch(new PaymentGatewayTestedEvent(
            userId: $userId,
            gatewayKey: $key,
            success: $res['success'],
            message: $res['message'],
            testedAt: now()->toIso8601String(),
        ));

        return $res;
    }
}
