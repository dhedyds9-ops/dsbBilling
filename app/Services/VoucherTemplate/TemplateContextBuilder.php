<?php

declare(strict_types=1);

namespace App\Services\VoucherTemplate;

use App\Models\ISP\Router;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\Voucher;
use App\Models\User;
use DateTimeInterface;

/**
 * Bertanggung jawab membangun array context (key-value murni) untuk dicetak
 * ke template voucher, baik berupa data sample, data dari model asli,
 * maupun batch context untuk mencetak banyak voucher sekaligus.
 *
 * Semua method mengembalikan array scalar/string/int — tidak ada model
 * Eloquent, Container, atau Facade yang diekspos ke luar kelas ini.
 */
class TemplateContextBuilder
{
    /**
     * Membangun context penuh dengan sample value dari registry.
     *
     * Struktur nested: `voucher.*`, `package.*`, dst. Setiap node yang
     * disebutkan di VariableRegistry akan hadir di sini.
     *
     * @param array<string, mixed> $overrides Key dot-path dengan value override
     *                                        (mis. `['voucher.code' => 'CUSTOM01']`)
     * @return array<string, mixed> Nested array context
     */
    public function buildSampleContext(array $overrides = []): array
    {
        $context = [];

        foreach (VariableRegistry::all() as $dotPath => $meta) {
            $value = $overrides[$dotPath] ?? $meta['sample'];
            self::setByDotPath($context, $dotPath, $value);
        }

        return $context;
    }

    /**
     * Membangun context dari model asli (Laravel/Eloquent).
     *
     * Semua parameter boleh null; yang null akan diisi dengan fallback sample
     * dari VariableRegistry. Tidak ada relasi lazy-load atau DB query yang
     * dilakukan di method ini — hanya extract attribute yang sudah tersedia
     * dari instance model yang diberikan.
     *
     * @param Voucher|null           $voucher          Model voucher (opsional)
     * @param ServiceProfile|null    $serviceProfile   Model paket / service profile (opsional)
     * @param Router|null            $router           Model router (opsional)
     * @param User|null              $owner            Owner / reseller (opsional, saat ini tidak dipakai
     *                                                 untuk context public; reserved untuk pengembangan)
     * @param array<string, mixed>   $companySettings  Pengaturan perusahaan dari Setting::getGroup('company')
     *                                                 Key yang dikenal: `name`, `logo`, `phone`, `whatsapp`,
     *                                                 `address`, `website`.
     * @param string|null            $currencyCode     Kode mata uang (default `IDR`)
     *
     * @return array<string, mixed> Nested array context dengan scalar value saja
     */
    public function buildFromVoucherModels(
        ?Voucher $voucher = null,
        ?ServiceProfile $serviceProfile = null,
        ?Router $router = null,
        ?User $owner = null,
        array $companySettings = [],
        ?string $currencyCode = 'IDR',
    ): array {
        if ($serviceProfile === null && $voucher !== null) {
            $serviceProfile = $voucher->serviceProfile ?? $voucher->voucherPool?->serviceProfile;
        }

        $sample = $this->buildSampleContext();

        $sample['voucher'] = $this->extractVoucher($voucher, $serviceProfile, $sample['voucher']);
        $sample['package'] = $this->extractPackage($serviceProfile, $sample['package']);
        $sample['router'] = $this->extractRouter($router, $sample['router']);
        $sample['owner'] = $this->extractOwner($owner, $sample['owner'] ?? []);
        $sample['company'] = $this->extractCompany($companySettings, $sample['company']);
        $sample['currency'] = $this->buildCurrency($currencyCode ?? 'IDR', $sample['currency']);
        $sample['hotspot'] = $this->extractHotspot($router, $sample['hotspot']);
        $sample['system'] = $this->buildSystem($sample['system']);

        // Root-level aliases for legacy template compatibility
        $sample['username'] = $sample['voucher']['username'] ?? null;
        $sample['password'] = $sample['voucher']['password'] ?? null;
        $sample['code'] = $sample['voucher']['code'] ?? null;
        $sample['price'] = $sample['voucher']['price'] ?? null;
        $sample['timelimit'] = $sample['voucher']['timelimit'] ?? null;
        $sample['validity'] = $sample['voucher']['validity'] ?? null;
        $sample['duration'] = $sample['package']['duration'] ?? null;
        $sample['quota'] = $sample['package']['quota'] ?? null;

        return $sample;
    }

    /**
     * Membangun batch context untuk mencetak banyak voucher dalam satu
     * halaman (untuk dipakai bersama `@foreach`).
     *
     * Struktur yang dihasilkan:
     * ```
     * [
     *   'vouchers' => [ [context per voucher], ... ],
     *   'package'  => ...,  // global
     *   'company'  => ...,  // global
     *   ...
     * ]
     * ```
     *
     * @param array<int, Voucher>    $vouchers         Daftar model voucher (bisa kosong)
     * @param ServiceProfile|null    $serviceProfile   Paket global (jika null, dari voucher pertama / sample)
     * @param Router|null            $router           Router global
     * @param User|null              $owner            Owner global
     * @param array<string, mixed>   $companySettings  Pengaturan perusahaan
     * @param string|null            $currencyCode     Mata uang
     *
     * @return array<string, mixed> Context dengan key `vouchers` (list) + globals
     */
    public function buildBatchContext(
        array $vouchers,
        ?ServiceProfile $serviceProfile = null,
        ?Router $router = null,
        ?User $owner = null,
        array $companySettings = [],
        ?string $currencyCode = 'IDR',
    ): array {
        $globals = $this->buildFromVoucherModels(
            null,
            $serviceProfile,
            $router,
            $owner,
            $companySettings,
            $currencyCode,
        );

        $builtVouchers = [];
        foreach ($vouchers as $idx => $voucher) {
            if (!($voucher instanceof Voucher)) {
                continue;
            }

            $profile = $serviceProfile;
            $routerUsed = $router;

            if ($profile === null && $voucher->serviceProfile !== null) {
                $profile = $voucher->serviceProfile;
            }

            if ($routerUsed === null && $voucher->nasDevice !== null && method_exists($voucher->nasDevice, 'router')) {
                $routerCandidate = $voucher->nasDevice->router;
                if ($routerCandidate instanceof Router) {
                    $routerUsed = $routerCandidate;
                }
            }

            $perVoucher = $this->buildFromVoucherModels(
                $voucher,
                $profile,
                $routerUsed,
                $owner,
                $companySettings,
                $currencyCode,
            );

            $builtVouchers[] = $perVoucher;
        }

        $globals['vouchers'] = $builtVouchers;

        return $globals;
    }

    /**
     * Extract data voucher dari model ke array flat.
     *
     * @param Voucher|null       $voucher
     * @param ServiceProfile|null $profile
     * @param array<string, mixed> $fallback
     * @return array<string, mixed>
     */
    private function extractVoucher(?Voucher $voucher, ?ServiceProfile $profile, array $fallback): array
    {
        if ($voucher === null) {
            return $fallback;
        }

        $timelimit = $fallback['timelimit'];
        $validity = $fallback['validity'];
        $price = $fallback['price'];
        
        // Fix: Use the voucher's code as the default username if available. E-Vouchers rely on code.
        $username = $voucher ? ($voucher->code ?? $fallback['username']) : $fallback['username'];
        $password = $fallback['password'];
        $loginUrl = $fallback['login_url'];

        if ($profile === null && $voucher !== null) {
            $profile = $voucher->serviceProfile ?? $voucher->voucherPool?->serviceProfile;
        }

        $timelimit = null;
        $validity = null;

        if ($voucher !== null && !empty($voucher->validity_days) && (int) $voucher->validity_days > 0) {
            $validity = sprintf('%d Hari', (int) $voucher->validity_days);
        }

        if ($profile !== null) {
            if ($profile->radius_session_timeout !== null && (int) $profile->radius_session_timeout > 0) {
                $sec = (int) $profile->radius_session_timeout;
                $timelimit = sprintf('%02d:%02d:%02d', (int) ($sec / 3600), (int) (($sec % 3600) / 60), $sec % 60);
            } elseif (!empty($profile->session_timeout)) {
                $timelimit = $this->parseDurationString($profile->session_timeout);
            } elseif ($profile->duration_value !== null && (int) $profile->duration_value > 0) {
                $unit = strtolower((string) ($profile->duration_unit ?? 'hours'));
                $val = (int) $profile->duration_value;
                if ($unit === 'hours' || $unit === 'hour') {
                    $timelimit = sprintf('%02d:%02d:00', $val, 0);
                } elseif ($unit === 'days' || $unit === 'day') {
                    $timelimit = sprintf('%02d:00:00', $val * 24);
                }
            }

            if ($validity === null) {
                if (!empty($profile->validity_hours) && (int) $profile->validity_hours > 0) {
                    $validity = sprintf('%d Jam', (int) $profile->validity_hours);
                } elseif (!empty($profile->validity_days) && (int) $profile->validity_days > 0) {
                    $validity = sprintf('%d Hari', (int) $profile->validity_days);
                } elseif (!empty($profile->voucher_validity_after_activation)) {
                    $validity = $this->parseDurationString($profile->voucher_validity_after_activation);
                } elseif (!empty($profile->validity_unit) && (!empty($profile->validity_days) || !empty($profile->validity_hours))) {
                    $unit = strtolower((string) $profile->validity_unit);
                    $val = (int) ($profile->validity_days ?? $profile->validity_hours ?? 0);
                    if ($val > 0) {
                        $unitLabel = match ($unit) {
                            'days', 'day' => 'Hari',
                            'months', 'month' => 'Bulan',
                            'hours', 'hour' => 'Jam',
                            default => ucfirst($unit),
                        };
                        $validity = sprintf('%d %s', $val, $unitLabel);
                    }
                } elseif (!empty($profile->duration_value) && (int) $profile->duration_value > 0) {
                    $val = (int) $profile->duration_value;
                    $unit = strtolower((string) ($profile->duration_unit ?? 'hours'));
                    $unitLabel = match ($unit) {
                        'hours', 'hour' => 'Jam',
                        'days', 'day' => 'Hari',
                        default => ucfirst($unit),
                    };
                    $validity = sprintf('%d %s', $val, $unitLabel);
                } elseif (!empty($profile->radius_session_timeout)) {
                    $validity = $this->parseDurationString($profile->radius_session_timeout);
                } elseif (!empty($profile->session_timeout)) {
                    $validity = $this->parseDurationString($profile->session_timeout);
                }
            }
        }

        if ($timelimit === null) {
            $timelimit = $fallback['timelimit'] ?? '';
        }
        if ($validity === null) {
            $validity = $fallback['validity'] ?? '';
        }

        $rawPrice = null;
        if ($profile !== null) {
            if (!empty($profile->promo_price) && (float) $profile->promo_price > 0) {
                $rawPrice = $profile->promo_price;
            } elseif (!empty($profile->price) && (float) $profile->price > 0) {
                $rawPrice = $profile->price;
            } elseif (!empty($profile->base_price) && (float) $profile->base_price > 0) {
                $rawPrice = $profile->base_price;
            } elseif (!empty($profile->owner_price) && (float) $profile->owner_price > 0) {
                $rawPrice = $profile->owner_price;
            } elseif (!empty($profile->reseller_price) && (float) $profile->reseller_price > 0) {
                $rawPrice = $profile->reseller_price;
            }
        }
        $price = $rawPrice !== null ? (int) $rawPrice : (int) $fallback['price'];

        if ($voucher->hotspotUser !== null) {
            if (isset($voucher->hotspotUser->username) && $voucher->hotspotUser->username !== null && $voucher->hotspotUser->username !== '') {
                $username = (string) $voucher->hotspotUser->username;
            }
            if (isset($voucher->hotspotUser->password) && $voucher->hotspotUser->password !== null && $voucher->hotspotUser->password !== '') {
                $password = (string) $voucher->hotspotUser->password;
            }
        }

        if ($voucher->nasDevice !== null && isset($voucher->nasDevice->hostname)) {
            $host = (string) $voucher->nasDevice->hostname;
            if ($host !== '') {
                $scheme = parse_url($host, PHP_URL_SCHEME) === null ? 'http://' : '';
                $loginUrl = rtrim($scheme . $host, '/') . '/login';
            }
        }

        return [
            'code'       => (string) ($voucher->code ?? $fallback['code']),
            'username'   => $username,
            'password'   => $password,
            'status'     => (string) ($voucher->status ?? $fallback['status']),
            'type'       => (string) ($voucher->type ?? $fallback['type']),
            'timelimit'  => $timelimit,
            'validity'   => $validity,
            'created_at' => $this->formatDate($voucher->created_at, $fallback['created_at']),
            'expired_at' => $this->formatDate($voucher->expires_at ?? null, $fallback['expired_at']),
            'price'      => $price,
            'login_url'  => $loginUrl,
        ];
    }

    /**
     * Extract data paket / service profile.
     *
     * @param ServiceProfile|null $profile
     * @param array<string, mixed> $fallback
     * @return array<string, mixed>
     */
    private function extractPackage(?ServiceProfile $profile, array $fallback): array
    {
        if ($profile === null) {
            return $fallback;
        }

        $downloadKbps = (int) ($profile->download_speed ?? $fallback['download_speed']);
        $uploadKbps = (int) ($profile->upload_speed ?? $fallback['upload_speed']);
        
        $rawPrice = null;
        if (!empty($profile->promo_price) && (float) $profile->promo_price > 0) {
            $rawPrice = $profile->promo_price;
        } elseif (!empty($profile->price) && (float) $profile->price > 0) {
            $rawPrice = $profile->price;
        } elseif (!empty($profile->base_price) && (float) $profile->base_price > 0) {
            $rawPrice = $profile->base_price;
        } elseif (!empty($profile->owner_price) && (float) $profile->owner_price > 0) {
            $rawPrice = $profile->owner_price;
        } elseif (!empty($profile->reseller_price) && (float) $profile->reseller_price > 0) {
            $rawPrice = $profile->reseller_price;
        }
        $price = $rawPrice !== null ? (int) $rawPrice : (int) $fallback['price'];

        $speedLabel = $fallback['speed'];
        if ($downloadKbps > 0) {
            if ($downloadKbps >= 1024) {
                $speedLabel = sprintf('%d Mbps', (int) ceil($downloadKbps / 1024));
            } else {
                $speedLabel = sprintf('%d kbps', $downloadKbps);
            }
        }

        $duration = null;
        if (!empty($profile->duration_value) && (int) $profile->duration_value > 0) {
            $val = (int) $profile->duration_value;
            $unit = strtolower((string) ($profile->duration_unit ?? 'hours'));
            $unitLabel = match ($unit) {
                'hours', 'hour' => 'Jam',
                'days', 'day' => 'Hari',
                'minutes', 'minute' => 'Menit',
                'months', 'month' => 'Bulan',
                default => ucfirst($unit),
            };
            $duration = sprintf('%d %s', $val, $unitLabel);
        }

        if ($duration === null && !empty($profile->radius_session_timeout)) {
            $duration = $this->parseDurationString($profile->radius_session_timeout);
        }

        if ($duration === null && !empty($profile->session_timeout)) {
            $duration = $this->parseDurationString($profile->session_timeout);
        }

        if ($duration === null && !empty($profile->validity_hours) && (int) $profile->validity_hours > 0) {
            $duration = sprintf('%d Jam', (int) $profile->validity_hours);
        }

        if ($duration === null && !empty($profile->validity_days) && (int) $profile->validity_days > 0) {
            $duration = sprintf('%d Hari', (int) $profile->validity_days);
        }

        if ($duration === null && !empty($profile->voucher_validity_after_activation)) {
            $duration = $this->parseDurationString($profile->voucher_validity_after_activation);
        }

        if ($duration === null && !empty($profile->name)) {
            if (preg_match('/(\d+\s*(?:jam|hari|bulan|menit|hour|day))/i', (string) $profile->name, $matches)) {
                $duration = ucwords(strtolower($matches[1]));
            }
        }

        if ($duration === null) {
            $duration = $fallback['duration'] ?? '';
        }

        $quota = null;
        $qVal = $profile->quota_value ?? $profile->quota ?? null;
        if ($qVal !== null && (float) $qVal > 0) {
            $unit = strtoupper((string) ($profile->quota_unit ?? 'MB'));
            $quota = sprintf('%s %s', is_numeric($qVal) ? (int) $qVal : $qVal, $unit);
        } elseif ($profile->package_type !== null && (strtolower((string) $profile->package_type) === 'unlimited' || strtolower((string) $profile->package_type) === 'time_based')) {
            $quota = 'Unlimited';
        } else {
            $quota = 'Unlimited';
        }

        return [
            'name'           => (string) ($profile->name ?? $fallback['name'] ?? ''),
            'price'          => $price,
            'speed'          => $speedLabel,
            'download_speed' => $downloadKbps,
            'upload_speed'   => $uploadKbps,
            'duration'       => $duration,
            'quota'          => $quota,
        ];
    }

    /**
     * Extract data router.
     *
     * @param Router|null $router
     * @param array<string, mixed> $fallback
     * @return array<string, mixed>
     */
    private function extractRouter(?Router $router, array $fallback): array
    {
        if ($router === null) {
            return $fallback;
        }

        $location = $fallback['location'];
        if (isset($router->pop) && method_exists($router->pop, 'getAttribute') && $router->pop->name !== null) {
            $location = (string) $router->pop->name;
        } elseif (!empty($router->description)) {
            $location = (string) $router->description;
        }

        return [
            'name'     => (string) ($router->name ?? $fallback['name']),
            'ip'       => (string) ($router->ip_address ?? $router->hostname ?? $fallback['ip']),
            'location' => $location,
        ];
    }

    private function extractOwner(?User $owner, array $fallback): array
    {
        if ($owner !== null && !empty($owner->name)) {
            return [
                'name' => (string) $owner->name,
            ];
        }

        if (auth()->check() && !empty(auth()->user()->name)) {
            return [
                'name' => (string) auth()->user()->name,
            ];
        }

        return [
            'name' => '',
        ];
    }

    /**
     * Extract setting perusahaan ke dalam bentuk yang seragam.
     *
     * @param array<string, mixed> $settings
     * @param array<string, mixed> $fallback
     * @return array<string, mixed>
     */
    private function extractCompany(array $settings, array $fallback): array
    {
        $logoPath = $settings['logo_url'] ?? $settings['logo'] ?? null;
        $logo = !empty($logoPath) ? (str_starts_with((string) $logoPath, 'http') ? $logoPath : asset($logoPath)) : '';

        return [
            'name'     => (string) ($settings['name'] ?? $fallback['name']),
            'logo'     => (string) $logo,
            'phone'    => (string) ($settings['phone'] ?? $fallback['phone']),
            'whatsapp' => (string) ($settings['whatsapp'] ?? $fallback['whatsapp']),
            'address'  => (string) ($settings['address'] ?? $fallback['address']),
            'website'  => (string) ($settings['website'] ?? $fallback['website']),
        ];
    }

    /**
     * Parse string durasi atau detik menjadi label ramah pengguna (misal 3 Jam, 1 Hari).
     */
    public function parseDurationString(mixed $val): ?string
    {
        if ($val === null || $val === '' || $val === 0 || $val === '0') {
            return null;
        }

        if (is_numeric($val)) {
            $sec = (int) $val;
            if ($sec >= 86400 && ($sec % 86400 === 0)) {
                return sprintf('%d Hari', (int) ($sec / 86400));
            }
            if ($sec >= 3600) {
                $hours = (int) ($sec / 3600);
                $mins = (int) (($sec % 3600) / 60);
                return $mins > 0 ? sprintf('%d Jam %d Menit', $hours, $mins) : sprintf('%d Jam', $hours);
            }
            if ($sec >= 60) {
                return sprintf('%d Menit', (int) ($sec / 60));
            }
            return sprintf('%d Detik', $sec);
        }

        $str = trim((string) $val);
        // Format H:i:s
        if (preg_match('/^(\d+):(\d+)(?::(\d+))?$/', $str, $matches)) {
            $h = (int) $matches[1];
            $m = (int) $matches[2];
            if ($h >= 24 && ($h % 24 === 0) && $m === 0) {
                return sprintf('%d Hari', (int) ($h / 24));
            }
            if ($h > 0 && $m > 0) {
                return sprintf('%d Jam %d Menit', $h, $m);
            }
            if ($h > 0) {
                return sprintf('%d Jam', $h);
            }
            if ($m > 0) {
                return sprintf('%d Menit', $m);
            }
        }

        // Format shorthand 3h, 1d, 30m, 12jam, 1hari
        if (preg_match('/^(\d+)\s*([a-zA-Z]+)$/', $str, $matches)) {
            $num = (int) $matches[1];
            $unit = strtolower($matches[2]);
            return match ($unit) {
                'd', 'day', 'days', 'hari', 'hri' => sprintf('%d Hari', $num),
                'h', 'hr', 'hrs', 'hour', 'hours', 'jam', 'jm' => sprintf('%d Jam', $num),
                'm', 'min', 'mins', 'minute', 'minutes', 'menit', 'mnt' => sprintf('%d Menit', $num),
                'w', 'week', 'weeks', 'minggu' => sprintf('%d Minggu', $num),
                'mo', 'month', 'months', 'bulan' => sprintf('%d Bulan', $num),
                default => sprintf('%d %s', $num, ucfirst($unit)),
            };
        }

        return $str;
    }

    /**
     * Membangun section currency berdasarkan kode ISO.
     *
     * Saat ini hanya mendukung `IDR`; kode lain fallback ke sample default.
     *
     * @param string $code
     * @param array<string, mixed> $fallback
     * @return array<string, mixed>
     */
    private function buildCurrency(string $code, array $fallback): array
    {
        $presets = [
            'IDR' => [
                'code'              => 'IDR',
                'symbol'            => 'Rp',
                'decimal_separator' => ',',
                'thousand_separator' => '.',
                'decimals'          => 0,
            ],
            'USD' => [
                'code'              => 'USD',
                'symbol'            => '$',
                'decimal_separator' => '.',
                'thousand_separator' => ',',
                'decimals'          => 2,
            ],
            'SGD' => [
                'code'              => 'SGD',
                'symbol'            => 'S$',
                'decimal_separator' => '.',
                'thousand_separator' => ',',
                'decimals'          => 2,
            ],
            'MYR' => [
                'code'              => 'MYR',
                'symbol'            => 'RM',
                'decimal_separator' => '.',
                'thousand_separator' => ',',
                'decimals'          => 2,
            ],
        ];

        return $presets[strtoupper($code)] ?? $fallback;
    }

    /**
     * Extract section hotspot (login URL & domain) dari router jika ada.
     *
     * @param Router|null $router
     * @param array<string, mixed> $fallback
     * @return array<string, mixed>
     */
    private function extractHotspot(?Router $router, array $fallback): array
    {
        $loginUrl = $fallback['login_url'];
        $domain = $fallback['domain'];

        if ($router !== null) {
            $host = (string) ($router->hostname ?? $router->ip_address ?? '');
            if ($host !== '') {
                $domain = $host;
                $scheme = parse_url($host, PHP_URL_SCHEME) === null ? 'http://' : '';
                $loginUrl = rtrim($scheme . $host, '/') . '/login';
            }
        }

        return [
            'login_url' => $loginUrl,
            'domain'    => $domain,
        ];
    }

    /**
     * Isi field system — saat ini hanya `generated_at` dan `theme`.
     *
     * @param array<string, mixed> $fallback
     * @return array<string, mixed>
     */
    private function buildSystem(array $fallback): array
    {
        return [
            'generated_at' => date('Y-m-d H:i:s'),
            'theme'        => (string) ($fallback['theme'] ?? 'default'),
        ];
    }

    /**
     * Helper format date (DateTimeInterface|string|null) ke string `Y-m-d H:i:s`.
     */
    private function formatDate(mixed $value, string $fallback): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_string($value) && $value !== '') {
            return $value;
        }

        return $fallback;
    }

    /**
     * Set nested array value berdasarkan dot-path.
     *
     * Contoh: `setByDotPath($arr, 'voucher.code', 'X')` akan membuat
     * `$arr['voucher']['code'] = 'X'`.
     *
     * @param array<string, mixed> $array
     * @param string $path
     * @param mixed $value
     */
    private static function setByDotPath(array &$array, string $path, mixed $value): void
    {
        $keys = explode('.', $path);
        $current = &$array;

        $lastKey = array_pop($keys);
        foreach ($keys as $key) {
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }

        $current[$lastKey] = $value;
    }
}
