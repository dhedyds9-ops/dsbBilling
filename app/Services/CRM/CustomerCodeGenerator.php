<?php

namespace App\Services\CRM;

use App\Models\User;
use App\Models\CRM\Customer;
use Illuminate\Support\Facades\Cache;

class CustomerCodeGenerator
{
    /**
     * Generate customer code unik baru dengan prefix Ymd + 2 digit sequence (contoh: 2026111801).
     * Dilengkapi atomic lock untuk mencegah race condition pada concurrent requests.
     */
    public static function generate(): string
    {
        try {
            return Cache::lock('customer_code_generator_lock', 10)->block(3, function () {
                return self::doGenerate();
            });
        } catch (\Throwable) {
            return self::doGenerate();
        }
    }

    /**
     * Algoritma kalkulasi sequence maksimal dan pencarian sequence berikutnya yang tersedia.
     */
    public static function doGenerate(): string
    {
        $prefix = date("Ymd");
        $prefixLen = strlen($prefix);

        // Ambil seluruh kode dengan prefix hari ini secara global (tanpa terhalang HasResellerScope / HasBranchScope)
        $customerCodes = Customer::withoutGlobalScopes()
            ->withTrashed()
            ->where('code', 'like', $prefix . '%')
            ->pluck('code');

        $userCodes = User::withoutGlobalScopes()
            ->where('customer_code', 'like', $prefix . '%')
            ->pluck('customer_code');

        $usernames = User::withoutGlobalScopes()
            ->where('username', 'like', $prefix . '%')
            ->pluck('username');

        $maxSequence = 0;

        foreach ($customerCodes->concat($userCodes)->concat($usernames) as $existingCode) {
            $codeStr = trim((string) $existingCode);
            if ($codeStr === '' || !str_starts_with($codeStr, $prefix)) {
                continue;
            }
            $suffix = substr($codeStr, $prefixLen);
            if ($suffix !== '' && ctype_digit($suffix)) {
                $seq = (int) $suffix;
                if ($seq > $maxSequence) {
                    $maxSequence = $seq;
                }
            }
        }

        $count = $maxSequence + 1;

        do {
            $candidate = $prefix . str_pad((string) $count, 2, "0", STR_PAD_LEFT);
            $exists = self::codeExists($candidate);
            if ($exists) {
                $count++;
            }
        } while ($exists);

        return $candidate;
    }

    /**
     * Pastikan kode yang diberikan belum terpakai secara global.
     * Jika kosong atau sudah dipakai, otomatis buat kode baru berikutnya.
     */
    public static function ensureUnique(?string $code = null): string
    {
        $clean = $code ? trim((string) $code) : '';
        if ($clean !== '' && !self::codeExists($clean)) {
            return $clean;
        }

        return self::generate();
    }

    /**
     * Cek apakah kode sudah dipakai di tabel customers (termasuk soft-deleted & lintas reseller/cabang)
     * atau di tabel users (customer_code / username).
     * Menggunakan query index-friendly (where) sebelum fallback case-insensitive (LOWER).
     */
    public static function codeExists(string $code): bool
    {
        $clean = trim((string) $code);
        if ($clean === '') {
            return false;
        }

        $lower = strtolower($clean);

        $inUsers = User::withoutGlobalScopes()
            ->where(function ($q) use ($clean, $lower) {
                $q->where('customer_code', $clean)
                  ->orWhere('username', $clean)
                  ->orWhereRaw('LOWER(customer_code) = ?', [$lower])
                  ->orWhereRaw('LOWER(username) = ?', [$lower]);
            })
            ->exists();

        if ($inUsers) {
            return true;
        }

        return Customer::withoutGlobalScopes()
            ->withTrashed()
            ->where(function ($q) use ($clean, $lower) {
                $q->where('code', $clean)
                  ->orWhereRaw('LOWER(code) = ?', [$lower]);
            })
            ->exists();
    }
}
