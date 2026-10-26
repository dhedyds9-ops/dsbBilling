<?php

namespace App\Enums;

enum JobFunction: string
{
    case NOC = 'NOC';
    case TECHNICIAN = 'TECHNICIAN';
    case PENGURUS = 'PENGURUS';
    case SALES = 'SALES';

    public function label(): string
    {
        return match($this) {
            self::NOC           => 'NOC',
            self::TECHNICIAN    => 'Technician',
            self::PENGURUS      => 'Pengurus',
            self::SALES         => 'Sales',
        };
    }

    public function detailedLabel(): string
    {
        return $this->label();
    }

    /**
     * Daftar posisi standar khusus ISP (dsBilling).
     * MStore GTWash hanya ada di aplikasi MStore dan TIDAK dimasukkan ke dsBilling.
     */
    public static function ispPositions(): array
    {
        return [
            'Teknisi Lapangan',
            'NOC / Monitoring',
            'Customer Service',
            'Kasir Billing',
            'Marketing Internet',
        ];
    }

    /**
     * Backward-compatibility alias.
     */
    public static function allPositions(): array
    {
        return self::ispPositions();
    }

    /**
     * Resolusi otomatis JobFunction berdasarkan nama posisi.
     */
    public static function resolveJobFunction(?string $position): self
    {
        $target = strtolower(trim((string) $position));

        if (str_contains($target, 'noc') || str_contains($target, 'monitoring') || str_contains($target, 'network')) {
            return self::NOC;
        }

        if (str_contains($target, 'teknisi') || str_contains($target, 'technician') || str_contains($target, 'lapangan') || str_contains($target, 'installer')) {
            return self::TECHNICIAN;
        }

        if (str_contains($target, 'sales') || str_contains($target, 'marketing') || str_contains($target, 'penjualan') || str_contains($target, 'promosi')) {
            return self::SALES;
        }

        return self::PENGURUS;
    }

    /**
     * Divisi pada dsBilling selalu Layanan Internet & Jaringan.
     */
    public static function resolveDivision(?string $position = null): string
    {
        return 'Layanan Internet & Jaringan';
    }
}
