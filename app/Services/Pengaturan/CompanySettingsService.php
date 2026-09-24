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
        'legal_name' => 'string',
        'npwp' => 'string',
        'nib' => 'string',
        'siup' => 'string',
        'sppl' => 'string',
        'address' => 'text',
        'rt' => 'string',
        'rw' => 'string',
        'village' => 'string',
        'district' => 'string',
        'city' => 'string',
        'province' => 'string',
        'postal_code' => 'string',
        'phone' => 'string',
        'mobile' => 'string',
        'email' => 'string',
        'website' => 'string',
        'billing_email' => 'string',
        'support_email' => 'string',
        'ceo_name' => 'string',
        'ceo_nik' => 'string',
        'director_name' => 'string',
        'finance_name' => 'string',
        'finance_email' => 'string',
        'head_noc_name' => 'string',
        'established_date' => 'string',
        'operational_hours' => 'string',
        'bank_1_name' => 'string',
        'bank_1_account' => 'string',
        'bank_1_holder' => 'string',
        'bank_2_name' => 'string',
        'bank_2_account' => 'string',
        'bank_2_holder' => 'string',
        'bank_3_name' => 'string',
        'bank_3_account' => 'string',
        'bank_3_holder' => 'string',
        'tax_office' => 'string',
        'signature_name' => 'string',
        'signature_title' => 'string',
        'signature_text' => 'text',
        'owner_name' => 'string',
        'owner_title' => 'string',
        'signature_url' => 'string',
        'logo_url' => 'string',
        'stamp_url' => 'string',
        'partner_name' => 'string',
        'partner_legal_name' => 'string',
        'partner_npwp' => 'string',
        'partner_address' => 'text',
        'partner_rt' => 'string',
        'partner_rw' => 'string',
        'partner_village' => 'string',
        'partner_district' => 'string',
        'partner_city' => 'string',
        'partner_province' => 'string',
        'partner_postal_code' => 'string',
        'partner_phone' => 'string',
        'partner_mobile' => 'string',
        'partner_email' => 'string',
        'partner_website' => 'string',
        'partner_logo_url' => 'string',
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
            'legal_name' => '',
            'npwp' => '',
            'nib' => '',
            'siup' => '',
            'sppl' => '',
            'address' => '',
            'rt' => '',
            'rw' => '',
            'village' => '',
            'district' => '',
            'city' => '',
            'province' => '',
            'postal_code' => '',
            'phone' => '',
            'mobile' => '',
            'email' => '',
            'website' => '',
            'billing_email' => '',
            'support_email' => '',
            'ceo_name' => '',
            'ceo_nik' => '',
            'director_name' => '',
            'finance_name' => '',
            'finance_email' => '',
            'head_noc_name' => '',
            'established_date' => '',
            'operational_hours' => 'Senin - Jumat 08:00 - 17:00',
            'bank_1_name' => '',
            'bank_1_account' => '',
            'bank_1_holder' => '',
            'bank_2_name' => '',
            'bank_2_account' => '',
            'bank_2_holder' => '',
            'bank_3_name' => '',
            'bank_3_account' => '',
            'bank_3_holder' => '',
            'tax_office' => '',
            'signature_name' => '',
            'signature_title' => '',
            'signature_text' => '',
            'owner_name' => '',
            'owner_title' => 'Direktur Utama',
            'signature_url' => '',
            'logo_url' => '',
            'stamp_url' => '',
            'partner_name' => '',
            'partner_legal_name' => '',
            'partner_npwp' => '',
            'partner_address' => '',
            'partner_rt' => '',
            'partner_rw' => '',
            'partner_village' => '',
            'partner_district' => '',
            'partner_city' => '',
            'partner_province' => '',
            'partner_postal_code' => '',
            'partner_phone' => '',
            'partner_mobile' => '',
            'partner_email' => '',
            'partner_website' => '',
            'partner_logo_url' => '',
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

    public static function formatAddressLine(array $companyData, string $prefix = ''): string
    {
        $parts = [];
        $addr = trim($companyData[$prefix . 'address'] ?? '');
        if ($addr !== '') {
            $parts[] = $addr;
        }
        $rt = trim($companyData[$prefix . 'rt'] ?? '');
        $rw = trim($companyData[$prefix . 'rw'] ?? '');
        if ($rt !== '' || $rw !== '') {
            $parts[] = 'RT ' . ($rt ?: '-') . ' / RW ' . ($rw ?: '-');
        }
        foreach (['village', 'district', 'city', 'province'] as $k) {
            $val = trim($companyData[$prefix . $k] ?? '');
            if ($val !== '') {
                $parts[] = $val;
            }
        }
        $pos = trim($companyData[$prefix . 'postal_code'] ?? '');
        if ($pos !== '') {
            $parts[] = 'Kode Pos ' . $pos;
        }
        return implode(', ', $parts);
    }

    public static function shouldShowPartner(array $companyData): bool
    {
        $trim = static function (mixed $v): string {
            return is_string($v) ? trim($v) : '';
        };
        return $trim($companyData['partner_name'] ?? '') !== ''
            || $trim($companyData['partner_logo_url'] ?? '') !== ''
            || $trim($companyData['partner_mobile'] ?? '') !== ''
            || $trim($companyData['partner_phone'] ?? '') !== ''
            || $trim($companyData['partner_email'] ?? '') !== ''
            || $trim($companyData['partner_address'] ?? '') !== ''
            || $trim($companyData['partner_legal_name'] ?? '') !== ''
            || $trim($companyData['partner_npwp'] ?? '') !== '';
    }

    public function getInvoiceDefaults(): array
    {
        $all = $this->getAll();
        return [
            'openingText' => !empty($all['invoice_opening_text'])
                ? $all['invoice_opening_text']
                : 'Terima kasih telah mempercayakan layanan kami. Berikut adalah rincian tagihan Anda:',
            'footerText' => !empty($all['invoice_footer_text'])
                ? $all['invoice_footer_text']
                : 'Pembayaran dapat dilakukan via transfer bank atau e-wallet yang tertera. Mohon sertakan nomor invoice sebagai referensi.',
            'termsText' => !empty($all['terms_and_conditions'])
                ? $all['terms_and_conditions']
                : "1. Tagihan harus dibayar paling lambat tanggal jatuh tempo.\n2. Keterlambatan pembayaran dapat mengakibatkan penangguhan layanan.\n3. Keluhan tagihan disertakan bukti pembayaran yang sah.",
        ];
    }
}
