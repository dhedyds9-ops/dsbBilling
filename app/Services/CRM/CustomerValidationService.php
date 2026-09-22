<?php

namespace App\Services\CRM;

use App\Models\CRM\Customer;
use App\Models\User;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\HotspotUser;

/**
 * Layanan Validasi Terpusat untuk mencegah data duplikat
 * di seluruh sistem (User, Customer, PPPoE, Hotspot).
 */
class CustomerValidationService
{
    /**
     * Validasi data sebelum membuat pelanggan + layanan baru.
     * Lempar exception jika ada konflik data.
     */
    public static function validateBeforeCreate(array $data, ?int $excludeCustomerId = null, ?int $excludeUserId = null): void
    {
        $phone    = $data["phone"] ?? null;
        $email    = $data["email"] ?? null;
        $username = $data["username"] ?? null;
        $code     = $data["customer_code"] ?? null;

        // 1. Validasi Nomor HP
        if ($phone) {
            $clean = preg_replace("/[^0-9]/", "", $phone);
            $phone0  = $clean;
            $phone62 = $clean;
            if (str_starts_with($clean, "62"))     $phone0  = "0" . substr($clean, 2);
            elseif (str_starts_with($clean, "0"))  $phone62 = "62" . substr($clean, 1);

            // Cek di tabel users - jika pemiliknya bukan customer, tolak!
            $userQ = User::whereRaw("LOWER(whatsapp) IN (?, ?)", [$phone0, $phone62]);
            if ($excludeUserId) $userQ->where("id", "!=", $excludeUserId);
            $existingUser = $userQ->first();

            if ($existingUser && !$existingUser->hasRole("customer")) {
                throw new \InvalidArgumentException(
                    "Nomor HP \"{$phone}\" sudah terdaftar sebagai akun pengurus/admin ".
                    "({$existingUser->name}). Gunakan nomor HP yang berbeda."
                );
            }

            // Cek di tabel members/customers
            $custQ = Customer::where("phone", $phone)->orWhere("phone", $phone0)->orWhere("phone", $phone62);
            if ($excludeCustomerId) $custQ->where("id", "!=", $excludeCustomerId);
            if ($custQ->exists()) {
                throw new \InvalidArgumentException(
                    "Nomor HP \"{$phone}\" sudah terdaftar sebagai pelanggan lain. ".
                    "Satu nomor HP hanya boleh untuk satu pelanggan."
                );
            }
        }

        // 2. Validasi Email
        if ($email) {
            $lower = strtolower($email);
            $userQ = User::whereRaw("LOWER(email) = ?", [$lower]);
            if ($excludeUserId) $userQ->where("id", "!=", $excludeUserId);
            $existingUser = $userQ->first();

            if ($existingUser && !$existingUser->hasRole("customer")) {
                throw new \InvalidArgumentException(
                    "Email \"{$email}\" sudah digunakan oleh akun pengurus/admin ({$existingUser->name}). ".
                    "Gunakan email yang berbeda."
                );
            }

            $custQ = Customer::whereRaw("LOWER(email) = ?", [$lower]);
            if ($excludeCustomerId) $custQ->where("id", "!=", $excludeCustomerId);
            if ($custQ->exists()) {
                throw new \InvalidArgumentException("Email \"{$email}\" sudah terdaftar untuk pelanggan lain.");
            }
        }

        // 3. Validasi Username PPPoE/Hotspot
        if ($username) {
            $lower = strtolower($username);
            if (PPPoEUser::whereRaw("LOWER(username) = ?", [$lower])->exists()) {
                throw new \InvalidArgumentException("Username PPPoE \"{$username}\" sudah digunakan. Pilih username lain.");
            }
            if (HotspotUser::whereRaw("LOWER(username) = ?", [$lower])->exists()) {
                throw new \InvalidArgumentException("Username Hotspot \"{$username}\" sudah digunakan. Pilih username lain.");
            }
            if (User::whereRaw("LOWER(username) = ?", [$lower])->exists()) {
                throw new \InvalidArgumentException("Username \"{$username}\" sudah digunakan di sistem. Pilih username lain.");
            }
        }

        // 4. Validasi ID Pelanggan (code)
        if ($code) {
            $codeQ = Customer::whereRaw("LOWER(code) = ?", [strtolower($code)]);
            if ($excludeCustomerId) $codeQ->where("id", "!=", $excludeCustomerId);
            if ($codeQ->exists()) {
                throw new \InvalidArgumentException(
                    "ID Pelanggan \"{$code}\" sudah digunakan. Setiap pelanggan harus memiliki ID yang unik."
                );
            }
        }
    }
}
