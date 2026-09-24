<?php

declare(strict_types=1);

namespace App\Services\VoucherTemplate;

use InvalidArgumentException;

/**
 * Registry untuk semua variabel template voucher beserta metadata.
 *
 * Menyediakan daftar variabel per kategori, sample value realistis (Indonesia),
 * dan method utilitas untuk keperluan UI builder, validasi, dan snippet insertion.
 */
class VariableRegistry
{
    public const CATEGORY_VOUCHER = 'voucher';
    public const CATEGORY_PACKAGE = 'package';
    public const CATEGORY_ROUTER = 'router';
    public const CATEGORY_OWNER = 'owner';
    public const CATEGORY_COMPANY = 'company';
    public const CATEGORY_CURRENCY = 'currency';
    public const CATEGORY_HOTSPOT = 'hotspot';
    public const CATEGORY_SYSTEM = 'system';

    /** @var array<string, array{label:string, sample:mixed, category:string}> */
    private static array $registry;

    /**
     * Mengembalikan seluruh registry variabel dalam bentuk flat array.
     *
     * Key berupa dot-path (mis. `voucher.code`), value berupa array asosiatif
     * yang berisi `label`, `sample`, dan `category`.
     *
     * @return array<string, array{label:string, sample:mixed, category:string}>
     */
    public static function all(): array
    {
        if (isset(self::$registry)) {
            return self::$registry;
        }

        self::$registry = [
            'voucher.code' => [
                'label' => 'Kode Voucher',
                'sample' => 'DEMO12345',
                'category' => self::CATEGORY_VOUCHER,
            ],
            'voucher.username' => [
                'label' => 'Username Hotspot',
                'sample' => 'voucher_demo_abc1234',
                'category' => self::CATEGORY_VOUCHER,
            ],
            'voucher.password' => [
                'label' => 'Password Hotspot',
                'sample' => '9A7B5C3K',
                'category' => self::CATEGORY_VOUCHER,
            ],
            'voucher.status' => [
                'label' => 'Status Voucher',
                'sample' => 'available',
                'category' => self::CATEGORY_VOUCHER,
            ],
            'voucher.type' => [
                'label' => 'Tipe Voucher',
                'sample' => 'hotspot',
                'category' => self::CATEGORY_VOUCHER,
            ],
            'voucher.timelimit' => [
                'label' => 'Batas Waktu Akses',
                'sample' => '03:00:00',
                'category' => self::CATEGORY_VOUCHER,
            ],
            'voucher.validity' => [
                'label' => 'Masa Berlaku',
                'sample' => '7 hari',
                'category' => self::CATEGORY_VOUCHER,
            ],
            'voucher.created_at' => [
                'label' => 'Tanggal Dibuat',
                'sample' => '2026-08-15 10:00:00',
                'category' => self::CATEGORY_VOUCHER,
            ],
            'voucher.expired_at' => [
                'label' => 'Tanggal Kedaluwarsa',
                'sample' => '2026-08-22 10:00:00',
                'category' => self::CATEGORY_VOUCHER,
            ],
            'voucher.price' => [
                'label' => 'Harga Voucher',
                'sample' => 5000,
                'category' => self::CATEGORY_VOUCHER,
            ],
            'voucher.login_url' => [
                'label' => 'URL Login Hotspot',
                'sample' => 'http://wifi.demo.id/login',
                'category' => self::CATEGORY_VOUCHER,
            ],
            'package.name' => [
                'label' => 'Nama Paket',
                'sample' => 'Paket 3 Jam Harian',
                'category' => self::CATEGORY_PACKAGE,
            ],
            'package.price' => [
                'label' => 'Harga Paket',
                'sample' => 5000,
                'category' => self::CATEGORY_PACKAGE,
            ],
            'package.speed' => [
                'label' => 'Kecepatan (Label)',
                'sample' => '10 Mbps',
                'category' => self::CATEGORY_PACKAGE,
            ],
            'package.download_speed' => [
                'label' => 'Kecepatan Download (kbps)',
                'sample' => 10240,
                'category' => self::CATEGORY_PACKAGE,
            ],
            'package.upload_speed' => [
                'label' => 'Kecepatan Upload (kbps)',
                'sample' => 2048,
                'category' => self::CATEGORY_PACKAGE,
            ],
            'package.duration' => [
                'label' => 'Durasi Paket',
                'sample' => '3 Jam',
                'category' => self::CATEGORY_PACKAGE,
            ],
            'package.quota' => [
                'label' => 'Kuota Data',
                'sample' => 'Unlimited',
                'category' => self::CATEGORY_PACKAGE,
            ],
            'router.name' => [
                'label' => 'Nama Router',
                'sample' => 'Router-Lobi-01',
                'category' => self::CATEGORY_ROUTER,
            ],
            'router.ip' => [
                'label' => 'IP Router',
                'sample' => '10.10.10.1',
                'category' => self::CATEGORY_ROUTER,
            ],
            'router.location' => [
                'label' => 'Lokasi Router',
                'sample' => 'Kantor Pusat Lantai 1',
                'category' => self::CATEGORY_ROUTER,
            ],
            'owner.name' => [
                'label' => 'Nama Owner / Reseller',
                'sample' => 'Owner Demo',
                'category' => self::CATEGORY_OWNER,
            ],
            'company.name' => [
                'label' => 'Nama Perusahaan / ISP',
                'sample' => 'Demo ISP Nusantara',
                'category' => self::CATEGORY_COMPANY,
            ],
            'company.logo' => [
                'label' => 'Logo Perusahaan (Path)',
                'sample' => 'https://ui-avatars.com/api/?name=Winets&background=ffffff&color=000000&size=100&font-size=0.4',
                'category' => self::CATEGORY_COMPANY,
            ],
            'company.phone' => [
                'label' => 'Telepon Kantor',
                'sample' => '021-5550123',
                'category' => self::CATEGORY_COMPANY,
            ],
            'company.whatsapp' => [
                'label' => 'Nomor WhatsApp',
                'sample' => '6281234567890',
                'category' => self::CATEGORY_COMPANY,
            ],
            'company.address' => [
                'label' => 'Alamat Kantor',
                'sample' => 'Jl. Raya Internet No. 42, Jakarta Selatan',
                'category' => self::CATEGORY_COMPANY,
            ],
            'company.website' => [
                'label' => 'Website Perusahaan',
                'sample' => 'https://demo-isp.co.id',
                'category' => self::CATEGORY_COMPANY,
            ],
            'currency.code' => [
                'label' => 'Kode Mata Uang (ISO)',
                'sample' => 'IDR',
                'category' => self::CATEGORY_CURRENCY,
            ],
            'currency.symbol' => [
                'label' => 'Simbol Mata Uang',
                'sample' => 'Rp',
                'category' => self::CATEGORY_CURRENCY,
            ],
            'currency.decimal_separator' => [
                'label' => 'Pemisah Desimal',
                'sample' => ',',
                'category' => self::CATEGORY_CURRENCY,
            ],
            'currency.thousand_separator' => [
                'label' => 'Pemisah Ribuan',
                'sample' => '.',
                'category' => self::CATEGORY_CURRENCY,
            ],
            'currency.decimals' => [
                'label' => 'Jumlah Digit Desimal',
                'sample' => 0,
                'category' => self::CATEGORY_CURRENCY,
            ],
            'hotspot.login_url' => [
                'label' => 'URL Login Hotspot',
                'sample' => 'http://wifi.demo.id/login',
                'category' => self::CATEGORY_HOTSPOT,
            ],
            'hotspot.domain' => [
                'label' => 'Domain Hotspot',
                'sample' => 'wifi.demo.id',
                'category' => self::CATEGORY_HOTSPOT,
            ],
            'system.generated_at' => [
                'label' => 'Waktu Cetak',
                'sample' => 'sekarang',
                'category' => self::CATEGORY_SYSTEM,
            ],
            'system.theme' => [
                'label' => 'Tema Template',
                'sample' => 'default',
                'category' => self::CATEGORY_SYSTEM,
            ],
        ];

        return self::$registry;
    }

    /**
     * Mengambil daftar variabel yang dikelompokkan ke kategori tertentu.
     *
     * @param string $cat Nama kategori (lihat konstanta CATEGORY_*)
     * @return array<string, array{label:string, sample:mixed, category:string}>
     *
     * @throws InvalidArgumentException Jika kategori tidak ditemukan
     */
    public static function byCategory(string $cat): array
    {
        $allowed = self::categories();
        if (!in_array($cat, $allowed, true)) {
            throw new InvalidArgumentException(sprintf(
                'Kategori variabel tidak valid: "%s". Pilihan: %s',
                $cat,
                implode(', ', $allowed),
            ));
        }

        $result = [];
        foreach (self::all() as $dotPath => $meta) {
            if ($meta['category'] === $cat) {
                $result[$dotPath] = $meta;
            }
        }

        return $result;
    }

    /**
     * Daftar kategori yang tersedia di registry.
     *
     * @return string[]
     */
    public static function categories(): array
    {
        return [
            self::CATEGORY_VOUCHER,
            self::CATEGORY_PACKAGE,
            self::CATEGORY_ROUTER,
            self::CATEGORY_OWNER,
            self::CATEGORY_COMPANY,
            self::CATEGORY_CURRENCY,
            self::CATEGORY_HOTSPOT,
            self::CATEGORY_SYSTEM,
        ];
    }

    /**
     * Memeriksa apakah variabel dengan dot-path tertentu terdaftar.
     *
     * @param string $dotPath Path variabel dalam bentuk dot (mis. `voucher.code`)
     */
    public static function exists(string $dotPath): bool
    {
        return array_key_exists($dotPath, self::all());
    }

    /**
     * Mengambil sample value untuk variabel dengan dot-path tertentu.
     *
     * @param string $dotPath Path variabel dalam bentuk dot
     * @return mixed Sample value sesuai tipe variabel (string, int, dll.)
     *
     * @throws InvalidArgumentException Jika dot-path tidak ditemukan
     */
    public static function getSampleValue(string $dotPath): mixed
    {
        $all = self::all();
        if (!isset($all[$dotPath])) {
            throw new InvalidArgumentException(sprintf(
                'Variabel tidak ditemukan di registry: "%s"',
                $dotPath,
            ));
        }

        return $all[$dotPath]['sample'];
    }

    /**
     * Menghasilkan snippet string untuk disisipkan ke template editor.
     *
     * Format yang didukung:
     * - `braces` : Twig/Blade style `{{voucher.code}}` (default)
     * - `legacy` : Array PHP style `$vs['code']` (hanya bagian terakhir path)
     *
     * @param string $dotPath Path variabel dalam bentuk dot
     * @param string $format  Format snippet: `braces` atau `legacy`
     *
     * @throws InvalidArgumentException Jika dot-path tidak terdaftar atau format tidak didukung
     */
    public static function getInsertSnippet(string $dotPath, string $format = 'braces'): string
    {
        if (!self::exists($dotPath)) {
            throw new InvalidArgumentException(sprintf(
                'Variabel tidak ditemukan di registry: "%s"',
                $dotPath,
            ));
        }

        return match ($format) {
            'braces' => sprintf('{{%s}}', $dotPath),
            'legacy' => sprintf('$vs[\'%s\']', basename(str_replace('.', '/', $dotPath))),
            default => throw new InvalidArgumentException(sprintf(
                'Format snippet tidak didukung: "%s". Pilihan: braces, legacy',
                $format,
            )),
        };
    }
}
