<?php

namespace App\Services\CRM;

use App\Models\User;
use App\Models\CRM\Customer;

class CustomerCodeGenerator
{
    public static function generate(): string
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
            if (!is_string($existingCode) || !str_starts_with($existingCode, $prefix)) {
                continue;
            }
            $suffix = substr($existingCode, $prefixLen);
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
        $clean = $code ? trim($code) : '';
        if ($clean !== '' && !self::codeExists($clean)) {
            return $clean;
        }

        return self::generate();
    }

    /**
     * Cek apakah kode sudah dipakai di tabel customers (termasuk soft-deleted & lintas reseller/cabang)
     * atau di tabel users (customer_code / username).
     */
    public static function codeExists(string $code): bool
    {
        $lower = strtolower(trim($code));
        if ($lower === '') {
            return false;
        }

        $inUsers = User::withoutGlobalScopes()
            ->where(function ($q) use ($lower) {
                $q->whereRaw('LOWER(customer_code) = ?', [$lower])
                  ->orWhereRaw('LOWER(username) = ?', [$lower]);
            })
            ->exists();

        if ($inUsers) {
            return true;
        }

        return Customer::withoutGlobalScopes()
            ->withTrashed()
            ->whereRaw('LOWER(code) = ?', [$lower])
            ->exists();
    }
}
