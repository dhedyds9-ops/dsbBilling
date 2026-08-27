<?php

namespace App\Services\Pengaturan;

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cache;
use Src\Domain\Settings\Events\CompanySettingsUpdatedEvent;

class CompanySettingsService
{
    public const GROUP = 'company';
    public const PREFIX = 'company';

    public const KEYS = [
        'name' => 'string',
        'npwp' => 'string',
        'nib' => 'string',
        'address' => 'text',
        'province' => 'string',
        'city' => 'string',
        'district' => 'string',
        'village' => 'string',
        'postal_code' => 'string',
        'phone' => 'string',
        'email' => 'string',
        'website' => 'string',
        'owner_name' => 'string',
        'owner_title' => 'string',
        'signature_url' => 'string',
        'logo_url' => 'string',
        'stamp_url' => 'string',
        'invoice_opening_text' => 'text',
        'invoice_footer_text' => 'text',
        'terms_and_conditions' => 'text',
    ];

    public function getAll(): array
    {
        $stored = Setting::getGroup(self::PREFIX);
        $defaults = $this->getDefaults();
        $result = [];
        foreach (array_keys(self::KEYS) as $key) {
            $result[$key] = $stored[$key] ?? $defaults[$key] ?? '';
        }
        return $result;
    }

    protected function getDefaults(): array
    {
        return [
            'name' => '',
            'npwp' => '',
            'nib' => '',
            'address' => '',
            'province' => '',
            'city' => '',
            'district' => '',
            'village' => '',
            'postal_code' => '',
            'phone' => '',
            'email' => '',
            'website' => '',
            'owner_name' => '',
            'owner_title' => 'Direktur Utama',
            'signature_url' => '',
            'logo_url' => '',
            'stamp_url' => '',
            'invoice_opening_text' => 'Terima kasih telah mempercayakan layanan kami. Berikut adalah rincian tagihan Anda:',
            'invoice_footer_text' => 'Pembayaran dapat dilakukan via transfer bank atau e-wallet yang tertera. Mohon sertakan nomor invoice sebagai referensi.',
            'terms_and_conditions' => "1. Tagihan harus dibayar paling lambat tanggal jatuh tempo.\n2. Keterlambatan pembayaran dapat mengakibatkan penangguhan layanan.\n3. Keluhan tagihan disertakan bukti pembayaran yang sah.",
        ];
    }

    public function save(array $data): array
    {
        $existing = $this->getAll();
        $changedKeys = [];

        foreach (self::KEYS as $key => $type) {
            if (!array_key_exists($key, $data)) continue;
            $value = $data[$key] ?? '';
            if (is_string($value)) {
                $value = trim($value);
                if ($key === 'invoice_opening_text') $value = mb_substr($value, 0, 500);
            }
            $settingKey = self::PREFIX . '.' . $key;
            Setting::setValue($settingKey, $value, $type, self::GROUP);
            if (($existing[$key] ?? '') !== $value) {
                $changedKeys[] = $key;
            }
        }

        $userId = Auth::id() ?? 0;
        if (count($changedKeys) > 0) {
            Event::dispatch(new CompanySettingsUpdatedEvent(
                userId: $userId,
                changedKeys: $changedKeys,
                updatedAt: now()->toIso8601String(),
            ));
        }

        Cache::forget(Setting::CACHE_KEY);

        return [
            'success' => true,
            'changed_keys' => $changedKeys,
            'saved_at' => now()->format('d/m/Y H:i:s'),
        ];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Setting::getValue(self::PREFIX . '.' . $key, $default);
    }

    public function validate(array $data): array
    {
        $errors = [];
        if (empty($data['name'] ?? '')) {
            $errors['company.name'] = 'Nama Perusahaan wajib diisi.';
        }
        if (!empty($data['email'] ?? '') && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['company.email'] = 'Format email tidak valid.';
        }
        if (!empty($data['website'] ?? '')) {
            $url = $data['website'];
            if (!str_starts_with($url, 'http')) $url = 'https://' . $url;
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $errors['company.website'] = 'Format website tidak valid.';
            }
        }
        if (!empty($data['postal_code'] ?? '') && !preg_match('/^[0-9]{5}$/', $data['postal_code'])) {
            $errors['company.postal_code'] = 'Kode POS harus 5 digit angka.';
        }
        if (!empty($data['phone'] ?? '') && !preg_match('/^[0-9+\-\s]{6,20}$/', $data['phone'])) {
            $errors['company.phone'] = 'Format nomor telepon tidak valid.';
        }
        if (mb_strlen($data['invoice_opening_text'] ?? '') > 500) {
            $errors['company.invoice_opening_text'] = 'Teks pembuka maksimal 500 karakter.';
        }
        return $errors;
    }
}
