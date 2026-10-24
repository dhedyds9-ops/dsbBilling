<?php

namespace App\Services\CRM;

use App\Models\CRM\Customer;
use App\Models\User;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\HotspotUser;

/**
 * Layanan Validasi Terpusat untuk mencegah data duplikat & bentrok
 * di seluruh sistem (User, Customer, PPPoE, Hotspot).
 */
class CustomerValidationService
{
    /**
     * Normalisasi nomor HP ke format angka murni serta variasi awalan 0 dan 62.
     */
    public static function normalizePhone(?string $phone): array
    {
        if (!$phone) {
            return ['', '', ''];
        }

        $clean = preg_replace('/[^0-9]/', '', $phone);
        $phone0 = $clean;
        $phone62 = $clean;

        if (str_starts_with($clean, '62')) {
            $phone0 = '0' . substr($clean, 2);
        } elseif (str_starts_with($clean, '0')) {
            $phone62 = '62' . substr($clean, 1);
        }

        return [$clean, $phone0, $phone62];
    }

    /**
     * Validasi nomor HP sebelum create atau update.
     * Lempar exception jika nomor HP sudah terdaftar.
     */
    public static function validatePhone(?string $phone, ?int $excludeCustomerId = null, ?int $excludeUserId = null): void
    {
        if (empty($phone)) {
            return;
        }

        [$clean, $phone0, $phone62] = self::normalizePhone($phone);
        if (empty($clean)) {
            return;
        }

        // 1. Cek di tabel customers
        $custQ = Customer::withoutGlobalScopes()
            ->where(function($q) use ($phone, $phone0, $phone62) {
                $q->where('phone', $phone)
                  ->orWhere('phone', $phone0)
                  ->orWhere('phone', $phone62);
            });

        if ($excludeCustomerId) {
            $custQ->where('id', '!=', $excludeCustomerId);
        }

        $existingCust = $custQ->first();
        if ($existingCust) {
            $code = $existingCust->display_id ?? $existingCust->code ?? 'ID-' . $existingCust->id;
            throw new \InvalidArgumentException(
                "Nomor HP '{$phone}' sudah terdaftar di database milik pelanggan '{$existingCust->name}' (ID: {$code}). " .
                "Satu nomor HP hanya boleh untuk satu pelanggan."
            );
        }

        // 2. Cek di tabel users (whatsapp)
        $userQ = User::where(function($q) use ($phone0, $phone62) {
            $q->whereRaw('LOWER(whatsapp) IN (?, ?)', [$phone0, $phone62]);
        });

        if ($excludeUserId) {
            $userQ->where('id', '!=', $excludeUserId);
        }

        $existingUser = $userQ->first();
        if ($existingUser) {
            $cust = Customer::withoutGlobalScopes()->where('user_id', $existingUser->id)->first();
            $ownerName = $cust ? $cust->name : $existingUser->name;
            $ownerId = $cust ? ($cust->display_id ?? $cust->code) : ($existingUser->username ?? $existingUser->email);
            throw new \InvalidArgumentException(
                "Nomor HP '{$phone}' sudah terdaftar sebagai akun '{$ownerName}' ({$ownerId}). " .
                "Mohon gunakan nomor HP yang berbeda."
            );
        }
    }

    /**
     * Validasi username lintas PPPoE, Hotspot, dan User Portal.
     * Lempar exception jika username sudah dipakai di salah satu tempat.
     */
    public static function validateUsername(
        ?string $username,
        ?int $excludePPPoEId = null,
        ?int $excludeHotspotId = null,
        ?int $excludeUserId = null
    ): void {
        if (empty($username)) {
            return;
        }

        $lower = strtolower(trim($username));

        // 1. Cek PPPoE Users
        $pppoeQ = PPPoEUser::withoutGlobalScopes()->whereRaw('LOWER(username) = ?', [$lower]);
        if ($excludePPPoEId) {
            $pppoeQ->where('id', '!=', $excludePPPoEId);
        }
        if ($pppoeQ->exists()) {
            throw new \InvalidArgumentException("Username '{$username}' sudah digunakan sebagai akun PPPoE. Mohon pilih username lain.");
        }

        // 2. Cek Hotspot Users
        $hotspotQ = HotspotUser::withoutGlobalScopes()->whereRaw('LOWER(username) = ?', [$lower]);
        if ($excludeHotspotId) {
            $hotspotQ->where('id', '!=', $excludeHotspotId);
        }
        if ($hotspotQ->exists()) {
            throw new \InvalidArgumentException("Username '{$username}' sudah digunakan sebagai akun Hotspot. Mohon pilih username lain.");
        }

        // 3. Cek User Portal Login
        $userQ = User::whereRaw('LOWER(username) = ?', [$lower]);
        if ($excludeUserId) {
            $userQ->where('id', '!=', $excludeUserId);
        }
        if ($userQ->exists()) {
            throw new \InvalidArgumentException("Username '{$username}' sudah digunakan sebagai akun login user / portal. Mohon pilih username lain.");
        }
    }

    /**
     * Validasi lengkap sebelum membuat pelanggan + layanan baru.
     */
    public static function validateBeforeCreate(array $data, ?int $excludeCustomerId = null, ?int $excludeUserId = null): void
    {
        $phone    = $data['phone'] ?? null;
        $username = $data['username'] ?? ($data['pppoe_username'] ?? ($data['hotspot_username'] ?? null));
        $email    = $data['email'] ?? null;
        $code     = $data['customer_code'] ?? null;

        // 1. Validasi Nomor HP
        self::validatePhone($phone, $excludeCustomerId, $excludeUserId);

        // 2. Validasi Username Lintas Layanan
        self::validateUsername($username, null, null, $excludeUserId);

        // 3. Validasi Email (jika diisi)
        if ($email) {
            $lower = strtolower(trim($email));
            $userQ = User::whereRaw('LOWER(email) = ?', [$lower]);
            if ($excludeUserId) $userQ->where('id', '!=', $excludeUserId);
            $existingUser = $userQ->first();

            if ($existingUser && !$existingUser->hasRole('customer')) {
                throw new \InvalidArgumentException(
                    "Email '{$email}' sudah digunakan oleh akun pengurus/admin ({$existingUser->name}). " .
                    "Gunakan email yang berbeda."
                );
            }

            $custQ = Customer::withoutGlobalScopes()->whereRaw('LOWER(email) = ?', [$lower]);
            if ($excludeCustomerId) $custQ->where('id', '!=', $excludeCustomerId);
            if ($custQ->exists()) {
                throw new \InvalidArgumentException("Email '{$email}' sudah terdaftar untuk pelanggan lain.");
            }
        }

        // 4. Validasi ID Pelanggan (code) jika diisi manual
        if ($code) {
            $codeQ = Customer::withoutGlobalScopes()->whereRaw('LOWER(code) = ?', [strtolower(trim($code))]);
            if ($excludeCustomerId) $codeQ->where('id', '!=', $excludeCustomerId);
            if ($codeQ->exists()) {
                throw new \InvalidArgumentException("ID Pelanggan '{$code}' sudah digunakan. Setiap pelanggan harus memiliki ID yang unik.");
            }
        }
    }
}
