<?php

declare(strict_types=1);

namespace App\Services\VoucherTemplate;

/**
 * Compatibility Layer untuk menerjemahkan template gaya Mikhmon/MixRadius
 * (Smarty PHP-style dengan $vs[], $_c[], dll) ke syntax template dsBilling
 * yang baru ({{ dot.path }} braces).
 *
 * Fitur utama:
 * - Variable mapping lengkap dengan peringatan eksplisit (tidak silent)
 * - Syntax mapping: {include}, {assign}, {if}, {foreach}
 * - Dual-mode: bisa translate source ATAU build legacy context langsung
 * - Deteksi otomatis apakah source adalah template legacy
 *
 * SAMPLE TEST (PHPDoc — before / after):
 *
 * KASUS 1: Variable sederhana
 *   Before: "Halo {$vs.code} harga {$vs.total}"
 *   After:  "Halo {{voucher.code}} harga {{voucher.price}}"
 *           + mapping_applied: [['from'=>'$vs.code','to'=>'voucher.code'], ...]
 *           + warnings: ["Variable legacy $vs.code dipetakan ke voucher.code", ...]
 *
 * KASUS 2: {assign} + {if}
 *   Before: "{assign var=price value=$vs.total}\n{if $price eq '2000'}Rp 2rb{/if}"
 *   After:  "{assign var=price value={{voucher.price}}}\n{if {{$price}} == 2000}Rp 2rb{/if}"
 *           + warnings tentang operator `eq` -> `==` dan string literal '2000' -> 2000
 *
 * KASUS 3: Foreach loop
 *   Before: "{foreach $v as $vs}No {$vs.code}{/foreach}"
 *   After:  "{foreach $vouchers as $v}No {{$v.code}}{/foreach}"
 *           + mapping_applied dengan context scope=loop untuk inner $vs.code
 *           + compatibility_notes: "Foreach variable $v lama (outer) otomatis dipetakan ke vouchers[]"
 *
 * KASUS 4: Company + Hotspot variable
 *   Before: "{$_c['CompanyName']} @ {$hotspotdns}"
 *   After:  "{{company.name}} @ {{hotspot.domain}}"
 *           + mapping_applied & warnings untuk kedua variable
 */
class LegacyCompatibilityLayer
{
    /**
     * Mapping variable LEGACY -> BARU beserta keterangan.
     * Key = pola legacy (tanpa prefix $), value = [to, note, type]
     *
     * @var array<string, array{to:string, note:string, type:string}>
     */
    private const VARIABLE_MAPPING = [
        // ===== $vs[] / voucher =====
        "vs['code']" => ['to' => 'voucher.code', 'note' => 'Kode voucher utama', 'type' => 'vs'],
        'vs["code"]' => ['to' => 'voucher.code', 'note' => 'Kode voucher utama (quote ganda)', 'type' => 'vs'],
        'vs[code]' => ['to' => 'voucher.code', 'note' => 'Kode voucher utama (tanpa quote)', 'type' => 'vs'],
        "vs['secret']" => ['to' => 'voucher.password', 'note' => 'Password hotspot alias secret', 'type' => 'vs'],
        'vs["secret"]' => ['to' => 'voucher.password', 'note' => 'Password hotspot alias secret (quote ganda)', 'type' => 'vs'],
        'vs[secret]' => ['to' => 'voucher.password', 'note' => 'Password hotspot alias secret (tanpa quote)', 'type' => 'vs'],
        "vs['username']" => ['to' => 'voucher.username', 'note' => 'Username hotspot voucher', 'type' => 'vs'],
        'vs["username"]' => ['to' => 'voucher.username', 'note' => 'Username hotspot voucher (quote ganda)', 'type' => 'vs'],
        'vs[username]' => ['to' => 'voucher.username', 'note' => 'Username hotspot voucher (tanpa quote)', 'type' => 'vs'],
        "vs['total']" => ['to' => 'voucher.price', 'note' => 'Total harga = harga voucher', 'type' => 'vs'],
        'vs["total"]' => ['to' => 'voucher.price', 'note' => 'Total harga = harga voucher (quote ganda)', 'type' => 'vs'],
        'vs[total]' => ['to' => 'voucher.price', 'note' => 'Total harga = harga voucher (tanpa quote)', 'type' => 'vs'],
        "vs['price']" => ['to' => 'voucher.price', 'note' => 'Harga voucher langsung', 'type' => 'vs'],
        'vs["price"]' => ['to' => 'voucher.price', 'note' => 'Harga voucher langsung (quote ganda)', 'type' => 'vs'],
        'vs[price]' => ['to' => 'voucher.price', 'note' => 'Harga voucher langsung (tanpa quote)', 'type' => 'vs'],
        "vs['timelimit']" => ['to' => 'voucher.timelimit', 'note' => 'Batas waktu akses voucher', 'type' => 'vs'],
        'vs["timelimit"]' => ['to' => 'voucher.timelimit', 'note' => 'Batas waktu akses voucher (quote ganda)', 'type' => 'vs'],
        'vs[timelimit]' => ['to' => 'voucher.timelimit', 'note' => 'Batas waktu akses voucher (tanpa quote)', 'type' => 'vs'],
        "vs['validperiod']" => ['to' => 'voucher.validity', 'note' => 'Masa berlaku = validity', 'type' => 'vs'],
        'vs["validperiod"]' => ['to' => 'voucher.validity', 'note' => 'Masa berlaku = validity (quote ganda)', 'type' => 'vs'],
        'vs[validperiod]' => ['to' => 'voucher.validity', 'note' => 'Masa berlaku = validity (tanpa quote)', 'type' => 'vs'],
        "vs['validity']" => ['to' => 'voucher.validity', 'note' => 'Masa berlaku langsung', 'type' => 'vs'],
        'vs["validity"]' => ['to' => 'voucher.validity', 'note' => 'Masa berlaku langsung (quote ganda)', 'type' => 'vs'],
        'vs[validity]' => ['to' => 'voucher.validity', 'note' => 'Masa berlaku langsung (tanpa quote)', 'type' => 'vs'],
        "vs['created_date']" => ['to' => 'voucher.created_at', 'note' => 'Tanggal dibuat (alias created_date -> created_at)', 'type' => 'vs'],
        'vs["created_date"]' => ['to' => 'voucher.created_at', 'note' => 'Tanggal dibuat (alias created_date -> created_at quote ganda)', 'type' => 'vs'],
        'vs[created_date]' => ['to' => 'voucher.created_at', 'note' => 'Tanggal dibuat (alias created_date -> created_at tanpa quote)', 'type' => 'vs'],
        "vs['created_at']" => ['to' => 'voucher.created_at', 'note' => 'Tanggal dibuat langsung', 'type' => 'vs'],
        'vs["created_at"]' => ['to' => 'voucher.created_at', 'note' => 'Tanggal dibuat langsung (quote ganda)', 'type' => 'vs'],
        'vs[created_at]' => ['to' => 'voucher.created_at', 'note' => 'Tanggal dibuat langsung (tanpa quote)', 'type' => 'vs'],
        "vs['expired_date']" => ['to' => 'voucher.expired_at', 'note' => 'Tanggal kedaluwarsa (alias expired_date -> expired_at)', 'type' => 'vs'],
        'vs["expired_date"]' => ['to' => 'voucher.expired_at', 'note' => 'Tanggal kedaluwarsa (alias expired_date -> expired_at quote ganda)', 'type' => 'vs'],
        'vs[expired_date]' => ['to' => 'voucher.expired_at', 'note' => 'Tanggal kedaluwarsa (alias expired_date -> expired_at tanpa quote)', 'type' => 'vs'],
        "vs['expires_at']" => ['to' => 'voucher.expired_at', 'note' => 'Tanggal kedaluwarsa (alias expires_at -> expired_at)', 'type' => 'vs'],
        'vs["expires_at"]' => ['to' => 'voucher.expired_at', 'note' => 'Tanggal kedaluwarsa (alias expires_at -> expired_at quote ganda)', 'type' => 'vs'],
        'vs[expires_at]' => ['to' => 'voucher.expired_at', 'note' => 'Tanggal kedaluwarsa (alias expires_at -> expired_at tanpa quote)', 'type' => 'vs'],
        "vs['owner_name']" => ['to' => 'owner.name', 'note' => 'Nama pemilik voucher (perlu dicek apakah owner tersedia di context)', 'type' => 'vs'],
        'vs["owner_name"]' => ['to' => 'owner.name', 'note' => 'Nama pemilik voucher (quote ganda, perlu dicek)', 'type' => 'vs'],
        'vs[owner_name]' => ['to' => 'owner.name', 'note' => 'Nama pemilik voucher (tanpa quote, perlu dicek)', 'type' => 'vs'],
        "vs['plan_name']" => ['to' => 'package.name', 'note' => 'Nama paket = package.name', 'type' => 'vs'],
        'vs["plan_name"]' => ['to' => 'package.name', 'note' => 'Nama paket = package.name (quote ganda)', 'type' => 'vs'],
        'vs[plan_name]' => ['to' => 'package.name', 'note' => 'Nama paket = package.name (tanpa quote)', 'type' => 'vs'],
        "vs['package_name']" => ['to' => 'package.name', 'note' => 'Nama paket langsung', 'type' => 'vs'],
        'vs["package_name"]' => ['to' => 'package.name', 'note' => 'Nama paket langsung (quote ganda)', 'type' => 'vs'],
        'vs[package_name]' => ['to' => 'package.name', 'note' => 'Nama paket langsung (tanpa quote)', 'type' => 'vs'],
        "vs['plan_price']" => ['to' => 'package.price', 'note' => 'Harga paket = package.price', 'type' => 'vs'],
        'vs["plan_price"]' => ['to' => 'package.price', 'note' => 'Harga paket = package.price (quote ganda)', 'type' => 'vs'],
        'vs[plan_price]' => ['to' => 'package.price', 'note' => 'Harga paket = package.price (tanpa quote)', 'type' => 'vs'],
        "vs['bandwidth']" => ['to' => 'package.speed', 'note' => 'Bandwidth = package.speed (label kecepatan)', 'type' => 'vs'],
        'vs["bandwidth"]' => ['to' => 'package.speed', 'note' => 'Bandwidth = package.speed (quote ganda)', 'type' => 'vs'],
        'vs[bandwidth]' => ['to' => 'package.speed', 'note' => 'Bandwidth = package.speed (tanpa quote)', 'type' => 'vs'],
        "vs['router_name']" => ['to' => 'router.name', 'note' => 'Nama router', 'type' => 'vs'],
        'vs["router_name"]' => ['to' => 'router.name', 'note' => 'Nama router (quote ganda)', 'type' => 'vs'],
        'vs[router_name]' => ['to' => 'router.name', 'note' => 'Nama router (tanpa quote)', 'type' => 'vs'],
        "vs['router']" => ['to' => 'router.name', 'note' => 'Nama router (singkat)', 'type' => 'vs'],
        'vs["router"]' => ['to' => 'router.name', 'note' => 'Nama router (singkat quote ganda)', 'type' => 'vs'],
        'vs[router]' => ['to' => 'router.name', 'note' => 'Nama router (singkat tanpa quote)', 'type' => 'vs'],
        "vs['status']" => ['to' => 'voucher.status', 'note' => 'Status voucher', 'type' => 'vs'],
        'vs["status"]' => ['to' => 'voucher.status', 'note' => 'Status voucher (quote ganda)', 'type' => 'vs'],
        'vs[status]' => ['to' => 'voucher.status', 'note' => 'Status voucher (tanpa quote)', 'type' => 'vs'],
        "vs['type']" => ['to' => 'voucher.type', 'note' => 'Tipe voucher', 'type' => 'vs'],
        'vs["type"]' => ['to' => 'voucher.type', 'note' => 'Tipe voucher (quote ganda)', 'type' => 'vs'],
        'vs[type]' => ['to' => 'voucher.type', 'note' => 'Tipe voucher (tanpa quote)', 'type' => 'vs'],
        "vs['nas']" => ['to' => 'router.name', 'note' => 'NAS = nama router', 'type' => 'vs'],
        'vs["nas"]' => ['to' => 'router.name', 'note' => 'NAS = nama router (quote ganda)', 'type' => 'vs'],
        'vs[nas]' => ['to' => 'router.name', 'note' => 'NAS = nama router (tanpa quote)', 'type' => 'vs'],
        "vs['notes']" => ['to' => '—', 'note' => 'FALLBACK: variable notes tidak ada di registry baru, diganti dengan em-dash "—". Perlu dicek manual jika ada catatan penting.', 'type' => 'vs'],
        'vs["notes"]' => ['to' => '—', 'note' => 'FALLBACK: variable notes tidak ada di registry baru, diganti dengan em-dash "—" (quote ganda). Perlu dicek manual.', 'type' => 'vs'],
        'vs[notes]' => ['to' => '—', 'note' => 'FALLBACK: variable notes tidak ada di registry baru, diganti dengan em-dash "—" (tanpa quote). Perlu dicek manual.', 'type' => 'vs'],

        // ===== Object style $vs->code (dengan warning) =====
        'vs->code' => ['to' => 'voucher.code', 'note' => 'WARNING: Object style $vs->code ditemukan, sebaiknya pakai array style $vs[code]', 'type' => 'vs_object'],
        'vs->secret' => ['to' => 'voucher.password', 'note' => 'WARNING: Object style $vs->secret ditemukan', 'type' => 'vs_object'],
        'vs->username' => ['to' => 'voucher.username', 'note' => 'WARNING: Object style $vs->username ditemukan', 'type' => 'vs_object'],
        'vs->total' => ['to' => 'voucher.price', 'note' => 'WARNING: Object style $vs->total ditemukan', 'type' => 'vs_object'],
        'vs->price' => ['to' => 'voucher.price', 'note' => 'WARNING: Object style $vs->price ditemukan', 'type' => 'vs_object'],
        'vs->timelimit' => ['to' => 'voucher.timelimit', 'note' => 'WARNING: Object style $vs->timelimit ditemukan', 'type' => 'vs_object'],
        'vs->validperiod' => ['to' => 'voucher.validity', 'note' => 'WARNING: Object style $vs->validperiod ditemukan', 'type' => 'vs_object'],
        'vs->validity' => ['to' => 'voucher.validity', 'note' => 'WARNING: Object style $vs->validity ditemukan', 'type' => 'vs_object'],
        'vs->created_date' => ['to' => 'voucher.created_at', 'note' => 'WARNING: Object style $vs->created_date ditemukan', 'type' => 'vs_object'],
        'vs->created_at' => ['to' => 'voucher.created_at', 'note' => 'WARNING: Object style $vs->created_at ditemukan', 'type' => 'vs_object'],
        'vs->expired_date' => ['to' => 'voucher.expired_at', 'note' => 'WARNING: Object style $vs->expired_date ditemukan', 'type' => 'vs_object'],
        'vs->expires_at' => ['to' => 'voucher.expired_at', 'note' => 'WARNING: Object style $vs->expires_at ditemukan', 'type' => 'vs_object'],
        'vs->owner_name' => ['to' => 'owner.name', 'note' => 'WARNING: Object style $vs->owner_name ditemukan', 'type' => 'vs_object'],
        'vs->plan_name' => ['to' => 'package.name', 'note' => 'WARNING: Object style $vs->plan_name ditemukan', 'type' => 'vs_object'],
        'vs->package_name' => ['to' => 'package.name', 'note' => 'WARNING: Object style $vs->package_name ditemukan', 'type' => 'vs_object'],
        'vs->plan_price' => ['to' => 'package.price', 'note' => 'WARNING: Object style $vs->plan_price ditemukan', 'type' => 'vs_object'],
        'vs->bandwidth' => ['to' => 'package.speed', 'note' => 'WARNING: Object style $vs->bandwidth ditemukan', 'type' => 'vs_object'],
        'vs->router_name' => ['to' => 'router.name', 'note' => 'WARNING: Object style $vs->router_name ditemukan', 'type' => 'vs_object'],
        'vs->router' => ['to' => 'router.name', 'note' => 'WARNING: Object style $vs->router ditemukan', 'type' => 'vs_object'],
        'vs->status' => ['to' => 'voucher.status', 'note' => 'WARNING: Object style $vs->status ditemukan', 'type' => 'vs_object'],
        'vs->type' => ['to' => 'voucher.type', 'note' => 'WARNING: Object style $vs->type ditemukan', 'type' => 'vs_object'],
        'vs->nas' => ['to' => 'router.name', 'note' => 'WARNING: Object style $vs->nas ditemukan', 'type' => 'vs_object'],
        'vs->notes' => ['to' => '—', 'note' => 'WARNING: Object style $vs->notes ditemukan + FALLBACK ke em-dash', 'type' => 'vs_object'],

        // Dot style $vs.code (Smarty short)
        'vs.code' => ['to' => 'voucher.code', 'note' => 'Smarty dot-style $vs.code', 'type' => 'vs_dot'],
        'vs.secret' => ['to' => 'voucher.password', 'note' => 'Smarty dot-style $vs.secret', 'type' => 'vs_dot'],
        'vs.username' => ['to' => 'voucher.username', 'note' => 'Smarty dot-style $vs.username', 'type' => 'vs_dot'],
        'vs.total' => ['to' => 'voucher.price', 'note' => 'Smarty dot-style $vs.total', 'type' => 'vs_dot'],
        'vs.price' => ['to' => 'voucher.price', 'note' => 'Smarty dot-style $vs.price', 'type' => 'vs_dot'],
        'vs.timelimit' => ['to' => 'voucher.timelimit', 'note' => 'Smarty dot-style $vs.timelimit', 'type' => 'vs_dot'],
        'vs.validperiod' => ['to' => 'voucher.validity', 'note' => 'Smarty dot-style $vs.validperiod', 'type' => 'vs_dot'],
        'vs.validity' => ['to' => 'voucher.validity', 'note' => 'Smarty dot-style $vs.validity', 'type' => 'vs_dot'],
        'vs.created_date' => ['to' => 'voucher.created_at', 'note' => 'Smarty dot-style $vs.created_date', 'type' => 'vs_dot'],
        'vs.created_at' => ['to' => 'voucher.created_at', 'note' => 'Smarty dot-style $vs.created_at', 'type' => 'vs_dot'],
        'vs.expired_date' => ['to' => 'voucher.expired_at', 'note' => 'Smarty dot-style $vs.expired_date', 'type' => 'vs_dot'],
        'vs.expires_at' => ['to' => 'voucher.expired_at', 'note' => 'Smarty dot-style $vs.expires_at', 'type' => 'vs_dot'],
        'vs.owner_name' => ['to' => 'owner.name', 'note' => 'Smarty dot-style $vs.owner_name', 'type' => 'vs_dot'],
        'vs.plan_name' => ['to' => 'package.name', 'note' => 'Smarty dot-style $vs.plan_name', 'type' => 'vs_dot'],
        'vs.package_name' => ['to' => 'package.name', 'note' => 'Smarty dot-style $vs.package_name', 'type' => 'vs_dot'],
        'vs.plan_price' => ['to' => 'package.price', 'note' => 'Smarty dot-style $vs.plan_price', 'type' => 'vs_dot'],
        'vs.bandwidth' => ['to' => 'package.speed', 'note' => 'Smarty dot-style $vs.bandwidth', 'type' => 'vs_dot'],
        'vs.router_name' => ['to' => 'router.name', 'note' => 'Smarty dot-style $vs.router_name', 'type' => 'vs_dot'],
        'vs.router' => ['to' => 'router.name', 'note' => 'Smarty dot-style $vs.router', 'type' => 'vs_dot'],
        'vs.status' => ['to' => 'voucher.status', 'note' => 'Smarty dot-style $vs.status', 'type' => 'vs_dot'],
        'vs.type' => ['to' => 'voucher.type', 'note' => 'Smarty dot-style $vs.type', 'type' => 'vs_dot'],
        'vs.nas' => ['to' => 'router.name', 'note' => 'Smarty dot-style $vs.nas', 'type' => 'vs_dot'],
        'vs.notes' => ['to' => '—', 'note' => 'Smarty dot-style $vs.notes FALLBACK ke em-dash', 'type' => 'vs_dot'],

        // ===== $_c[] / company + currency =====
        "_c['CompanyName']" => ['to' => 'company.name', 'note' => 'Nama perusahaan (PascalCase Mikhmon)', 'type' => '_c'],
        '_c["CompanyName"]' => ['to' => 'company.name', 'note' => 'Nama perusahaan (PascalCase quote ganda)', 'type' => '_c'],
        '_c[CompanyName]' => ['to' => 'company.name', 'note' => 'Nama perusahaan (PascalCase tanpa quote)', 'type' => '_c'],
        "_c['company_name']" => ['to' => 'company.name', 'note' => 'Nama perusahaan (snake_case)', 'type' => '_c'],
        '_c["company_name"]' => ['to' => 'company.name', 'note' => 'Nama perusahaan (snake_case quote ganda)', 'type' => '_c'],
        '_c[company_name]' => ['to' => 'company.name', 'note' => 'Nama perusahaan (snake_case tanpa quote)', 'type' => '_c'],
        "_c['currency_code']" => ['to' => 'currency.code', 'note' => 'Kode mata uang (ISO)', 'type' => '_c'],
        '_c["currency_code"]' => ['to' => 'currency.code', 'note' => 'Kode mata uang (ISO quote ganda)', 'type' => '_c'],
        '_c[currency_code]' => ['to' => 'currency.code', 'note' => 'Kode mata uang (ISO tanpa quote)', 'type' => '_c'],
        "_c['currency']" => ['to' => 'currency.code', 'note' => 'Mata uang (singkat) = currency.code', 'type' => '_c'],
        '_c["currency"]' => ['to' => 'currency.code', 'note' => 'Mata uang (singkat quote ganda)', 'type' => '_c'],
        '_c[currency]' => ['to' => 'currency.code', 'note' => 'Mata uang (singkat tanpa quote)', 'type' => '_c'],
        "_c['dec_point']" => ['to' => 'currency.decimal_separator', 'note' => 'Pemisah desimal (Mikhmon = dec_point)', 'type' => '_c'],
        '_c["dec_point"]' => ['to' => 'currency.decimal_separator', 'note' => 'Pemisah desimal (quote ganda)', 'type' => '_c'],
        '_c[dec_point]' => ['to' => 'currency.decimal_separator', 'note' => 'Pemisah desimal (tanpa quote)', 'type' => '_c'],
        "_c['thousands_sep']" => ['to' => 'currency.thousand_separator', 'note' => 'Pemisah ribuan (Mikhmon = thousands_sep -> thousand_separator)', 'type' => '_c'],
        '_c["thousands_sep"]' => ['to' => 'currency.thousand_separator', 'note' => 'Pemisah ribuan (quote ganda)', 'type' => '_c'],
        '_c[thousands_sep]' => ['to' => 'currency.thousand_separator', 'note' => 'Pemisah ribuan (tanpa quote)', 'type' => '_c'],
        "_c['Address']" => ['to' => 'company.address', 'note' => 'Alamat perusahaan (PascalCase)', 'type' => '_c'],
        '_c["Address"]' => ['to' => 'company.address', 'note' => 'Alamat perusahaan (PascalCase quote ganda)', 'type' => '_c'],
        '_c[Address]' => ['to' => 'company.address', 'note' => 'Alamat perusahaan (PascalCase tanpa quote)', 'type' => '_c'],
        "_c['Phone']" => ['to' => 'company.phone', 'note' => 'Telepon perusahaan (PascalCase)', 'type' => '_c'],
        '_c["Phone"]' => ['to' => 'company.phone', 'note' => 'Telepon perusahaan (PascalCase quote ganda)', 'type' => '_c'],
        '_c[Phone]' => ['to' => 'company.phone', 'note' => 'Telepon perusahaan (PascalCase tanpa quote)', 'type' => '_c'],
        "_c['Whatsapp']" => ['to' => 'company.whatsapp', 'note' => 'WhatsApp perusahaan (PascalCase)', 'type' => '_c'],
        '_c["Whatsapp"]' => ['to' => 'company.whatsapp', 'note' => 'WhatsApp perusahaan (PascalCase quote ganda)', 'type' => '_c'],
        '_c[Whatsapp]' => ['to' => 'company.whatsapp', 'note' => 'WhatsApp perusahaan (PascalCase tanpa quote)', 'type' => '_c'],
        "_c['Website']" => ['to' => 'company.website', 'note' => 'Website perusahaan (PascalCase)', 'type' => '_c'],
        '_c["Website"]' => ['to' => 'company.website', 'note' => 'Website perusahaan (PascalCase quote ganda)', 'type' => '_c'],
        '_c[Website]' => ['to' => 'company.website', 'note' => 'Website perusahaan (PascalCase tanpa quote)', 'type' => '_c'],
        "_c['Logo']" => ['to' => 'company.logo', 'note' => 'Logo perusahaan (PascalCase)', 'type' => '_c'],
        '_c["Logo"]' => ['to' => 'company.logo', 'note' => 'Logo perusahaan (PascalCase quote ganda)', 'type' => '_c'],
        '_c[Logo]' => ['to' => 'company.logo', 'note' => 'Logo perusahaan (PascalCase tanpa quote)', 'type' => '_c'],

        // ===== Global variables (no array) =====
        'hotspotdns' => ['to' => 'hotspot.domain', 'note' => 'Domain hotspot DNS', 'type' => 'global'],
        'hotspoturl' => ['to' => 'hotspot.login_url', 'note' => 'URL login hotspot', 'type' => 'global'],
        'hotspot_login' => ['to' => 'hotspot.login_url', 'note' => 'URL login hotspot (alias hotspot_login)', 'type' => 'global'],
        '_theme' => ['to' => 'system.theme', 'note' => 'Tema sistem/template', 'type' => 'global'],
        'generated_at' => ['to' => 'system.generated_at', 'note' => 'Waktu generate/cetak template', 'type' => 'global'],
    ];

    /**
     * Mapping operator Smarty -> dsBilling (PHP-style).
     *
     * @var array<string, string>
     */
    private const OPERATOR_MAPPING = [
        ' eq ' => ' == ',
        ' ne ' => ' != ',
        ' lt ' => ' < ',
        ' gt ' => ' > ',
        ' le ' => ' <= ',
        ' ge ' => ' >= ',
        ' AND ' => ' && ',
        ' OR ' => ' || ',
        ' NOT ' => ' !',
        ' and ' => ' && ',
        ' or ' => ' || ',
        ' not ' => ' !',
    ];

    /**
     * Mapping file include legacy -> baru.
     *
     * @var array<string, array{to:string, warning:string}>
     */
    private const INCLUDE_MAPPING = [
        'rad-template-header.tpl' => [
            'to' => 'header',
            'warning' => 'Include "rad-template-header.tpl" dipetakan ke "header.template" — pastikan konten header sudah di-embed ke template kode utama atau file partials header tersedia di partials/header',
        ],
        'rad-template-footer.tpl' => [
            'to' => 'footer',
            'warning' => 'Include "rad-template-footer.tpl" dipetakan ke "footer.template" — pastikan konten footer sudah di-embed ke template kode utama atau file partials footer tersedia di partials/footer',
        ],
    ];

    /**
     * Menjalankan seluruh pipeline translate: syntax + variable.
     *
     * @param string $legacySource Source template gaya Mikhmon/MixRadius.
     *
     * @return array{
     *     source: string,
     *     mapping_applied: list<array{from:string, to:string, scope?:string, note?:string}>,
     *     warnings: list<string>,
     *     unknown_tokens: list<string>,
     *     compatibility_notes: list<string>
     * }
     */
    public function translate(string $legacySource): array
    {
        $source = $legacySource;
        $mappingApplied = [];
        $warnings = [];
        $unknownTokens = [];
        $compatibilityNotes = [];

        $inForeachScope = false;
        $foreachVarOuter = null;
        $foreachVarInner = null;

        // ---- 1. Handle foreach scoping & mapping ----
        // Cari pola: {foreach $v as $vs} ... {/foreach}
        $source = preg_replace_callback(
            '/\{foreach\s+\$([a-zA-Z_][a-zA-Z0-9_]*)\s+as\s+\$([a-zA-Z_][a-zA-Z0-9_]*)\}/i',
            function (array $m) use (&$mappingApplied, &$warnings, &$compatibilityNotes, &$inForeachScope, &$foreachVarOuter, &$foreachVarInner): string {
                $outer = $m[1]; // biasanya 'v'
                $inner = $m[2]; // biasanya 'vs'
                $inForeachScope = true;
                $foreachVarOuter = $outer;
                $foreachVarInner = $inner;

                $from = sprintf('$%s as $%s', $outer, $inner);
                $to = '$vouchers as $v';

                $mappingApplied[] = [
                    'from' => $from,
                    'to' => $to,
                    'scope' => 'foreach_declaration',
                    'note' => sprintf(
                        'Foreach outer $%s -> $vouchers[] (array of voucher), inner $%s -> $v (current voucher dalam loop)',
                        $outer,
                        $inner
                    ),
                ];

                $warnings[] = sprintf(
                    'FOREACH REWRITTEN: {foreach $%s as $%s} menjadi {foreach $vouchers as $v}. Pastikan semua referensi inner $%s[...] dalam loop juga di-mapping ke $v.xxx secara otomatis.',
                    $outer,
                    $inner,
                    $inner
                );

                $compatibilityNotes[] = sprintf(
                    'Perhatikan foreach variable $%s lama (outer loop) di luar loop otomatis dipetakan ke voucher pertama jika hanya render single voucher.',
                    $outer
                );

                return '{foreach $vouchers as $v}';
            },
            $source
        );

        // ---- 2. Handle {include file="xxx.tpl"} ----
        $source = preg_replace_callback(
            '/\{include\s+file\s*=\s*["\']([^"\']+\.tpl)["\'][^}]*\}/i',
            function (array $m) use (&$mappingApplied, &$warnings): string {
                $file = $m[1];
                $fullMatch = $m[0];

                if (isset(self::INCLUDE_MAPPING[$file])) {
                    $cfg = self::INCLUDE_MAPPING[$file];
                    $mappingApplied[] = [
                        'from' => sprintf('{include file="%s"}', $file),
                        'to' => sprintf('{include file="%s"}', $cfg['to']),
                        'note' => $cfg['warning'],
                    ];
                    $warnings[] = $cfg['warning'];
                    return sprintf('{include file="%s"}', $cfg['to']);
                }

                // Unsupported include file
                $warnings[] = sprintf(
                    'Unsupported include file "%s", skip translate. Include ini akan tetap di source ASLI dan perlu dicek manual.',
                    $file
                );
                return $fullMatch;
            },
            $source
        );

        // ---- 3. Handle {assign var=x value=...} ----
        $source = preg_replace_callback(
            '/\{assign\s+var\s*=\s*([a-zA-Z_][a-zA-Z0-9_]*)\s+value\s*=\s*([^}]+)\}/i',
            function (array $m) use (&$mappingApplied, &$warnings): string {
                $varName = $m[1];
                $valueExpr = trim($m[2]);

                $mappingApplied[] = [
                    'from' => sprintf('{assign var=%s value=%s}', $varName, $valueExpr),
                    'to' => sprintf('{assign var=%s value=<translated_value>}', $varName),
                    'note' => 'Assign variable value akan diterjemahkan di pass variable mapping',
                ];

                return sprintf('{assign var=%s value=__VALUE_%s__}', $varName, md5($valueExpr));
            },
            $source
        );

        // Simpan semua assign value untuk di-restore nanti
        $assignValues = [];
        if (preg_match_all('/\{assign\s+var\s*=\s*([a-zA-Z_][a-zA-Z0-9_]*)\s+value\s*=\s*__VALUE_([a-f0-9]+)__\}/i', $source, $assignMatches, PREG_SET_ORDER)) {
            foreach ($assignMatches as $am) {
                $assignValues[$am[2]] = $this->translateExpression($am[1], $legacySource, $mappingApplied, $warnings, $unknownTokens, $inForeachScope, $foreachVarInner ?? null);
            }
        }

        // ---- 4. Handle {if ...} expressions ----
        $source = preg_replace_callback(
            '/\{if\s+([^}]+)\}/i',
            function (array $m) use (&$mappingApplied, &$warnings, &$unknownTokens, $inForeachScope, $foreachVarInner): string {
                $expr = trim($m[1]);
                $translatedExpr = $this->translateIfExpression($expr, $mappingApplied, $warnings, $unknownTokens, $inForeachScope, $foreachVarInner ?? null);
                return sprintf('{if %s}', $translatedExpr);
            },
            $source
        );

        // ---- 5. Utama: scan dan replace semua variable template token ----
        // Urutan: dari yang PALING SPESIFIK (dengan quote & bracket) ke yang umum (dot style),
        // supaya tidak partial match.
        $sortedMapping = self::VARIABLE_MAPPING;
        // Sortir berdasarkan panjang key (DESC) supaya pola panjang dicocokkan terlebih dahulu
        uksort($sortedMapping, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        foreach ($sortedMapping as $legacyPattern => $mapping) {
            $legacyWithDollar = '$' . $legacyPattern;
            $newPath = $mapping['to'];
            $note = $mapping['note'];

            // Regex: cari token ini di source.
            // Token template bisa muncul sebagai:
            //   {$vs.code}    — dalam kurung kurawal
            //   $vs.code      — langsung di text (Smarty short)
            //   $vs['code']   — dalam {} atau expression
            // Untuk menghindari replace di tengah literal HTML attribute value,
            // kita batasi: token harus di dalam {} ATAU diawali dengan batas kata + $.
            //
            // Strategy: gunakan 2 pass:
            //   Pass A: yang jelas ada di dalam { ... }  (kurung kurawal Smarty)
            //   Pass B: yang muncul sebagai {$xxx}      (short-tag)
            //   Pass C: yang muncul sebagai $xxx        (expression context, biasanya dalam if/assign/foreach)

            // Pass A: di dalam { ... } — ekstrak dan replace di sana secara terpisah
            // Ini akan ditangkap oleh pass B/C karena token juga ada $ prefix.

            // Pass B: pola {$X} (short tag Smarty)
            $patternB = '/\{\s*\$' . preg_quote($legacyPattern, '/') . '\s*\}/';
            if (preg_match_all($patternB, $source, $allMatches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
                foreach (array_reverse($allMatches) as $matchInfo) {
                    $fullStr = $matchInfo[0][0];
                    $offset = $matchInfo[0][1];
                    $replacement = $newPath === '—' ? '—' : '{{' . $newPath . '}}';

                    $mappingApplied[] = [
                        'from' => $fullStr,
                        'to' => $replacement,
                        'scope' => $inForeachScope && $this->isVsInnerRef($legacyPattern, $foreachVarInner ?? 'vs') ? 'loop' : 'global',
                        'note' => $note,
                    ];

                    if ($newPath === '—') {
                        $warnings[] = sprintf(
                            'FALLBACK REPLACEMENT: %s -> %s. Variable legacy tidak ada padanan langsung. %s',
                            $legacyWithDollar,
                            '— (em-dash)',
                            $note
                        );
                    } elseif (str_contains($note, 'WARNING:') || str_contains($note, 'FALLBACK:')) {
                        $warnings[] = sprintf(
                            '%s: Variable legacy %s dipetakan ke %s. %s',
                            $mapping['type'] ?? 'compat',
                            $legacyWithDollar,
                            $newPath,
                            $note
                        );
                    } else {
                        $warnings[] = sprintf(
                            'Variable legacy %s dipetakan ke %s. %s',
                            $legacyWithDollar,
                            $newPath,
                            $note
                        );
                    }

                    $source = substr_replace($source, $replacement, $offset, strlen($fullStr));
                }
            }

            // Pass C: pola $X di luar {} (expression context: if, assign, foreach).
            // Perlu word boundary sebelum $.
            $patternC = '/(?<![a-zA-Z0-9_])\$' . preg_quote($legacyPattern, '/') . '(?![a-zA-Z0-9_\[\>\'"\-])/';
            if (preg_match_all($patternC, $source, $allMatches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
                foreach (array_reverse($allMatches) as $matchInfo) {
                    $fullStr = $matchInfo[0][0];
                    $offset = $matchInfo[0][1];

                    if ($newPath === '—') {
                        $replacement = "'—'";
                    } else {
                        // Expression context (bukan echo): gunakan {{}} atau tergantung konteks
                        // Jika dalam {} maka cukup $var, tapi di sini kita convert ke dot-path braces
                        $replacement = '{{' . $newPath . '}}';
                    }

                    $mappingApplied[] = [
                        'from' => $fullStr,
                        'to' => $replacement,
                        'scope' => $inForeachScope && $this->isVsInnerRef($legacyPattern, $foreachVarInner ?? 'vs') ? 'loop' : 'global',
                        'note' => $note . ' (konteks expression)',
                    ];

                    if ($newPath === '—') {
                        $warnings[] = sprintf(
                            'FALLBACK REPLACEMENT (expr): %s -> %s',
                            $fullStr,
                            "'—'"
                        );
                    } else {
                        $warnings[] = sprintf(
                            'Variable legacy (expr) %s dipetakan ke %s',
                            $fullStr,
                            $newPath
                        );
                    }

                    $source = substr_replace($source, $replacement, $offset, strlen($fullStr));
                }
            }
        }

        // ---- 6. Scoping foreach: jika inner loop tadinya $vs maka $v.code di dalam loop ----
        // Kita perlu: di dalam {foreach $vouchers as $v} ... {/foreach}
        // replace semua pola {{voucher.X}} -> {{$v.X}}
        // Ini karena tadinya $vs[X] (inner) -> voucher.X tapi harusnya $v.X (current loop item)
        if ($foreachVarInner !== null) {
            $source = preg_replace_callback(
                '/\{foreach\s+\$vouchers\s+as\s+\$v\}(.*?)\{\/foreach\}/is',
                function (array $m) use (&$mappingApplied, &$warnings): string {
                    $innerBody = $m[1];
                    $originalInner = $innerBody;

                    $innerBody = preg_replace(
                        '/\{\{voucher\.([a-zA-Z_][a-zA-Z0-9_]*)\}\}/',
                        '{{$v.$1}}',
                        $innerBody
                    );

                    if ($innerBody !== $originalInner) {
                        if (preg_match_all('/\{\{voucher\.([a-zA-Z_][a-zA-Z0-9_]*)\}\}/', $originalInner, $vm)) {
                            foreach ($vm[1] as $field) {
                                $mappingApplied[] = [
                                    'from' => sprintf('{{voucher.%s}} (dalam foreach, tadinya inner $vs.%s)', $field, $field),
                                    'to' => sprintf('{{$v.%s}}', $field),
                                    'scope' => 'loop',
                                    'note' => sprintf(
                                        'Inner loop variable: reference voucher.%s dalam foreach otomatis diubah ke $v.%s (current item)',
                                        $field,
                                        $field
                                    ),
                                ];
                                $warnings[] = sprintf(
                                    'LOOP SCOPE ADJUST: voucher.%s -> $v.%s (dalam foreach loop scope)',
                                    $field,
                                    $field
                                );
                            }
                        }
                    }

                    return '{foreach $vouchers as $v}' . $innerBody . '{/foreach}';
                },
                $source
            );
        }

        // ---- 7. Restore assign value yang sudah ditranslate ----
        foreach ($assignValues as $hash => $translatedValue) {
            $placeholder = '__VALUE_' . $hash . '__';
            $source = str_replace($placeholder, $translatedValue, $source);
        }

        // ---- 8. DETECT UNKNOWN TOKENS: cari pola legacy yang masih tersisa ----
        // Pola: $vs[XXX], $_c[XXX], $vs->XXX, $vs.XXX, $hotspotdns, dll.
        $unknownPatterns = [
            '/\$vs\s*\[\s*["\']?[a-zA-Z_][a-zA-Z0-9_]*["\']?\s*\]/',       // $vs['x'], $vs["x"], $vs[x]
            '/\$_c\s*\[\s*["\']?[a-zA-Z_][a-zA-Z0-9_]*["\']?\s*\]/',       // $_c['X'], $_c["X"], $_c[X]
            '/\$vs\s*->\s*[a-zA-Z_][a-zA-Z0-9_]*/',                          // $vs->x
            '/\$vs\s*\.\s*[a-zA-Z_][a-zA-Z0-9_]*/',                          // $vs.x
            '/(?<![a-zA-Z0-9_])\$hotspotdns(?![a-zA-Z0-9_])/',
            '/(?<![a-zA-Z0-9_])\$hotspoturl(?![a-zA-Z0-9_])/',
            '/(?<![a-zA-Z0-9_])\$hotspot_login(?![a-zA-Z0-9_])/',
            '/(?<![a-zA-Z0-9_])\$_theme(?![a-zA-Z0-9_])/',
            '/(?<![a-zA-Z0-9_])\$generated_at(?![a-zA-Z0-9_])/',
        ];

        foreach ($unknownPatterns as $up) {
            if (preg_match_all($up, $source, $uMatches)) {
                foreach ($uMatches[0] as $token) {
                    if (!in_array($token, $unknownTokens, true)) {
                        $unknownTokens[] = $token;
                        $warnings[] = sprintf(
                            'UNKNOWN TOKEN (tidak di-mapping): %s. Token ini dibiarkan di source ASLI, silakan dicek manual.',
                            $token
                        );
                    }
                }
            }
        }

        // ---- 9. Deteksi syntax if lama yang mungkin belum ter-convert sempurna ----
        if (preg_match_all('/\{if\s+[^}]*(?:eq|ne|lt|gt|le|ge|AND|OR|NOT|and|or|not)[^}]*\}/i', $source, $leftoverIf)) {
            foreach ($leftoverIf[0] as $badIf) {
                $warnings[] = sprintf(
                    'Perlu dicek manual: Operator Smarty mungkin masih tersisa di %s',
                    $badIf
                );
            }
        }

        $compatibilityNotes[] = 'Template hasil translate direkomendasikan dicek manual di Preview sebelum disimpan permanen.';
        $compatibilityNotes[] = 'Jika ada unknown_tokens, mohon mapping manual atau buat custom variable di VariableRegistry.';
        $compatibilityNotes[] = 'Untuk dual-mode render (pakai template lama langsung tanpa translate), gunakan method buildLegacyContext + mode legacy=true di renderer.';

        return [
            'source' => $source,
            'mapping_applied' => $mappingApplied,
            'warnings' => $warnings,
            'unknown_tokens' => array_values(array_unique($unknownTokens)),
            'compatibility_notes' => $compatibilityNotes,
        ];
    }

    /**
     * Membangun context LEGACY dari context BARU (TemplateContextBuilder format).
     *
     * Input contoh (new context):
     *   [
     *     'voucher' => ['code'=>'DEMO1', 'password'=>'abc', ...],
     *     'vouchers' => [ ['code'=>'...'], ['code'=>'...'] ],
     *     'package' => ['name'=>'Paket A', 'price'=>5000, ...],
     *     'company' => ['name'=>'Demo ISP', 'address'=>'...', ...],
     *     ...
     *   ]
     *
     * Output (legacy context):
     *   [
     *     'vs' => ['code'=>..., 'secret'=>..., 'username'=>..., 'total'=>..., 'price'=>..., ...],
     *     '_c' => ['CompanyName'=>..., 'company_name'=>..., 'currency_code'=>..., ...],
     *     'hotspotdns' => '...',
     *     'hotspoturl' => '...',
     *     'hotspot_login' => '...',
     *     '_theme' => '...',
     *     'generated_at' => '...',
     *     'v' => voucher_pertama  (single, untuk backward compat loop),
     *   ]
     *
     * @param array<string, mixed> $newContext Context dari TemplateContextBuilder.
     * @return array<string, mixed> Context LEGACY siap pakai di renderer legacy mode.
     */
    public function buildLegacyContext(array $newContext): array
    {
        $voucher = $newContext['voucher'] ?? [];
        $vouchers = $newContext['vouchers'] ?? (is_array($voucher) && isset($voucher['code']) ? [$voucher] : []);
        $package = $newContext['package'] ?? [];
        $router = $newContext['router'] ?? [];
        $owner = $newContext['owner'] ?? [];
        $company = $newContext['company'] ?? [];
        $currency = $newContext['currency'] ?? [];
        $hotspot = $newContext['hotspot'] ?? [];
        $system = $newContext['system'] ?? [];

        $firstVoucher = $vouchers[0] ?? $voucher;

        $vs = [
            'code' => $voucher['code'] ?? null,
            'secret' => $voucher['password'] ?? null,
            'username' => $voucher['username'] ?? null,
            'total' => $voucher['price'] ?? null,
            'price' => $voucher['price'] ?? null,
            'timelimit' => $voucher['timelimit'] ?? null,
            'validperiod' => $voucher['validity'] ?? null,
            'validity' => $voucher['validity'] ?? null,
            'created_date' => $voucher['created_at'] ?? null,
            'created_at' => $voucher['created_at'] ?? null,
            'expired_date' => $voucher['expired_at'] ?? null,
            'expires_at' => $voucher['expired_at'] ?? null,
            'owner_name' => $owner['name'] ?? null,
            'plan_name' => $package['name'] ?? null,
            'package_name' => $package['name'] ?? null,
            'plan_price' => $package['price'] ?? null,
            'bandwidth' => $package['speed'] ?? null,
            'router_name' => $router['name'] ?? null,
            'router' => $router['name'] ?? null,
            'status' => $voucher['status'] ?? null,
            'type' => $voucher['type'] ?? null,
            'nas' => $router['name'] ?? null,
            'notes' => null,
        ];

        $_c = [
            'CompanyName' => $company['name'] ?? null,
            'company_name' => $company['name'] ?? null,
            'currency_code' => $currency['code'] ?? null,
            'currency' => $currency['code'] ?? null,
            'dec_point' => $currency['decimal_separator'] ?? null,
            'thousands_sep' => $currency['thousand_separator'] ?? null,
            'Address' => $company['address'] ?? null,
            'Phone' => $company['phone'] ?? null,
            'Whatsapp' => $company['whatsapp'] ?? null,
            'Website' => $company['website'] ?? null,
            'Logo' => $company['logo'] ?? null,
        ];

        return [
            'vs' => $vs,
            '_c' => $_c,
            'hotspotdns' => $hotspot['domain'] ?? null,
            'hotspoturl' => $hotspot['login_url'] ?? null,
            'hotspot_login' => $hotspot['login_url'] ?? null,
            '_theme' => $system['theme'] ?? null,
            'generated_at' => $system['generated_at'] ?? null,
            'v' => $firstVoucher,
            'vouchers' => $vouchers,
        ];
    }

    /**
     * Mendeteksi apakah source template menggunakan syntax Mikhmon lama.
     *
     * Pattern yang dideteksi:
     *   - $vs[...] atau $vs->x atau $vs.x
     *   - $_c[...]
     *   - $hotspotdns | $hotspoturl | $hotspot_login | $_theme | $generated_at
     *   - {include file="xxx.tpl"}
     */
    public function detectLegacySyntax(string $source): bool
    {
        $patterns = [
            '/\$vs\s*\[/',                                     // $vs[
            '/\$_c\s*\[/',                                     // $_c[
            '/\$vs\s*->\s*[a-zA-Z_]/',                          // $vs->x
            '/\$vs\s*\.\s*[a-zA-Z_]/',                          // $vs.x
            '/(?<![a-zA-Z0-9_])\$hotspotdns(?![a-zA-Z0-9_])/',
            '/(?<![a-zA-Z0-9_])\$hotspoturl(?![a-zA-Z0-9_])/',
            '/(?<![a-zA-Z0-9_])\$hotspot_login(?![a-zA-Z0-9_])/',
            '/(?<![a-zA-Z0-9_])\$_theme(?![a-zA-Z0-9_])/',
            '/(?<![a-zA-Z0-9_])\$generated_at(?![a-zA-Z0-9_])/',
            '/\{include\s+file\s*=\s*["\'][^"\']+\.tpl["\']/i', // {include file="xxx.tpl"}
        ];

        foreach ($patterns as $p) {
            if (preg_match($p, $source)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mengembalikan mapping table untuk ditampilkan di UI sebagai panduan admin.
     *
     * @return list<array{legacy:string, baru:string, kategori:string, catatan:string}>
     */
    public function getMappingTable(): array
    {
        $table = [];
        $seen = [];

        foreach (self::VARIABLE_MAPPING as $pattern => $cfg) {
            $to = $cfg['to'];
            $type = $cfg['type'];
            $key = $type . '|' . $to;

            // Hanya tampilkan satu representatif per padanan (pilih yang paling readable)
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $legacyDisplay = '$' . $pattern;
            $kategori = match ($type) {
                'vs', 'vs_object', 'vs_dot' => 'Voucher ($vs)',
                '_c' => 'Perusahaan & Mata Uang ($_c)',
                'global' => 'Global',
                default => 'Lainnya',
            };

            $table[] = [
                'legacy' => $legacyDisplay,
                'baru' => $to,
                'kategori' => $kategori,
                'catatan' => $cfg['note'],
            ];
        }

        return $table;
    }

    // ================================================================
    // INTERNAL HELPERS
    // ================================================================

    /**
     * Menentukan apakah legacy pattern adalah referensi $vs inner (dalam foreach).
     */
    private function isVsInnerRef(string $legacyPattern, ?string $innerVar): bool
    {
        if ($innerVar === null) {
            return false;
        }

        if ($innerVar === 'vs') {
            return str_starts_with($legacyPattern, 'vs');
        }

        return str_starts_with($legacyPattern, $innerVar);
    }

    /**
     * Menerjemahkan expression dalam tag {if ...}:
     *   - mapping variable
     *   - mapping operator (eq -> ==, dll)
     *   - menormalisasi literal
     *
     * @param string $expr
     * @param array<array{from:string,to:string,scope?:string,note?:string}> $mappingApplied
     * @param list<string> $warnings
     * @param list<string> $unknownTokens
     */
    private function translateIfExpression(
        string $expr,
        array &$mappingApplied,
        array &$warnings,
        array &$unknownTokens,
        bool $inForeachScope,
        ?string $foreachVarInner
    ): string {
        $result = $expr;

        // 1. Replace operator
        foreach (self::OPERATOR_MAPPING as $smartyOp => $newOp) {
            $count = 0;
            $result = str_ireplace($smartyOp, $newOp, $result, $count);
            if ($count > 0) {
                $displayOp = trim($smartyOp);
                $displayNew = trim($newOp);
                $mappingApplied[] = [
                    'from' => sprintf('operator "%s"', $displayOp),
                    'to' => sprintf('operator "%s"', $displayNew),
                    'note' => sprintf('Operator Smarty %s diganti ke PHP-style %s', $displayOp, $displayNew),
                ];
                $warnings[] = sprintf(
                    'OPERATOR REPLACEMENT: %s -> %s di dalam {if} expression',
                    $displayOp,
                    $displayNew
                );
            }
        }

        // 2. Replace variable legacy di expression.
        // Urut mapping dari panjang ke pendek.
        $sorted = self::VARIABLE_MAPPING;
        uksort($sorted, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        foreach ($sorted as $legacyPattern => $mapping) {
            $pattern = '/(?<![a-zA-Z0-9_])\$' . preg_quote($legacyPattern, '/') . '(?![a-zA-Z0-9_\[\>\'"\-])/';
            if (preg_match_all($pattern, $result, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
                foreach (array_reverse($matches) as $mi) {
                    $fullStr = $mi[0][0];
                    $offset = $mi[0][1];
                    $newPath = $mapping['to'];

                    if ($newPath === '—') {
                        $replacement = "'—'";
                    } else {
                        // Dalam expression if: cukup gunakan nama var dot-path
                        $replacement = $newPath;
                    }

                    $mappingApplied[] = [
                        'from' => $fullStr,
                        'to' => $replacement,
                        'scope' => $inForeachScope && $this->isVsInnerRef($legacyPattern, $foreachVarInner) ? 'loop' : 'if_expression',
                        'note' => $mapping['note'] . ' (konteks {if})',
                    ];

                    if ($newPath === '—') {
                        $warnings[] = sprintf('FALLBACK di {if}: %s -> %s', $fullStr, "'—'");
                    } else {
                        $warnings[] = sprintf('Variable legacy di {if}: %s -> %s', $fullStr, $newPath);
                    }

                    $result = substr_replace($result, $replacement, $offset, strlen($fullStr));
                }
            }
        }

        // 3. Normalisasi angka yang di-quoted: '2000' -> 2000
        if (preg_match_all("/==\s*'(\d+)'\s*/", $result, $numMatches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            foreach (array_reverse($numMatches) as $nm) {
                $full = $nm[0][0];
                $num = $nm[1][0];
                $off = $nm[0][1];
                $replacement = "== $num ";
                $result = substr_replace($result, $replacement, $off, strlen($full));
                $warnings[] = sprintf(
                    'LITERAL NORMALIZATION: String angka \'%s\' diubah ke integer %s di {if}',
                    $num,
                    $num
                );
            }
        }
        if (preg_match_all('/==\s*"(\d+)"\s*/', $result, $numMatches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            foreach (array_reverse($numMatches) as $nm) {
                $full = $nm[0][0];
                $num = $nm[1][0];
                $off = $nm[0][1];
                $replacement = "== $num ";
                $result = substr_replace($result, $replacement, $off, strlen($full));
                $warnings[] = sprintf(
                    'LITERAL NORMALIZATION: String angka "%s" diubah ke integer %s di {if}',
                    $num,
                    $num
                );
            }
        }

        // 4. Deteksi unknown tokens sisa di expression
        if (preg_match_all('/\$vs\s*\[\s*["\']?[a-zA-Z_][a-zA-Z0-9_]*["\']?\s*\]/', $result, $rem)) {
            foreach ($rem[0] as $t) {
                if (!in_array($t, $unknownTokens, true)) {
                    $unknownTokens[] = $t;
                    $warnings[] = sprintf('UNKNOWN TOKEN di {if}: %s — perlu dicek manual', $t);
                }
            }
        }
        if (preg_match_all('/\$_c\s*\[\s*["\']?[a-zA-Z_][a-zA-Z0-9_]*["\']?\s*\]/', $result, $rem)) {
            foreach ($rem[0] as $t) {
                if (!in_array($t, $unknownTokens, true)) {
                    $unknownTokens[] = $t;
                    $warnings[] = sprintf('UNKNOWN TOKEN di {if}: %s — perlu dicek manual', $t);
                }
            }
        }

        return trim($result);
    }

    /**
     * Helper untuk translate value expression di tag {assign}.
     *
     * @param array<array{from:string,to:string,scope?:string,note?:string}> $mappingApplied
     * @param list<string> $warnings
     * @param list<string> $unknownTokens
     */
    private function translateExpression(
        string $varName,
        string $fullLegacySource,
        array &$mappingApplied,
        array &$warnings,
        array &$unknownTokens,
        bool $inForeachScope,
        ?string $foreachVarInner
    ): string {
        // Cari kembali original assign dari source legacy
        $originalValue = null;
        if (preg_match(
            '/\{assign\s+var\s*=\s*' . preg_quote($varName, '/') . '\s+value\s*=\s*([^}]+)\}/i',
            $fullLegacySource,
            $om
        )) {
            $originalValue = trim($om[1]);
        }

        if ($originalValue === null) {
            return "{{$varName}}";
        }

        // Apply variable mapping ke original value expression
        $result = $originalValue;
        $sorted = self::VARIABLE_MAPPING;
        uksort($sorted, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        foreach ($sorted as $legacyPattern => $mapping) {
            $pattern = '/(?<![a-zA-Z0-9_])\$' . preg_quote($legacyPattern, '/') . '(?![a-zA-Z0-9_\[\>\'"\-])/';
            if (preg_match_all($pattern, $result, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
                foreach (array_reverse($matches) as $mi) {
                    $fullStr = $mi[0][0];
                    $offset = $mi[0][1];
                    $newPath = $mapping['to'];

                    if ($newPath === '—') {
                        $replacement = "'—'";
                    } else {
                        $replacement = '{{' . $newPath . '}}';
                    }

                    $mappingApplied[] = [
                        'from' => sprintf('{assign var=%s value=%s}', $varName, $fullStr),
                        'to' => sprintf('{assign var=%s value=%s}', $varName, $replacement),
                        'scope' => 'assign_value',
                        'note' => $mapping['note'],
                    ];

                    $warnings[] = sprintf(
                        'ASSIGN VALUE MAPPING: $%s value %s -> %s',
                        $varName,
                        $fullStr,
                        $replacement
                    );

                    $result = substr_replace($result, $replacement, $offset, strlen($fullStr));
                }
            }
        }

        return $result;
    }
}
