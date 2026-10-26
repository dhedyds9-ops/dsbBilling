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
        return match($this) {
            self::NOC           => 'NOC / Monitoring Jaringan',
            self::TECHNICIAN    => 'Teknisi Lapangan & Instalasi',
            self::PENGURUS      => 'Pengurus / CS / Kasir / Administrasi',
            self::SALES         => 'Sales & Marketing',
        };
    }

    /**
     * Pengelompokan posisi berdasarkan Divisi (diselaraskan dengan MStore SSOT).
     */
    public static function groupedPositions(): array
    {
        return [
            'Layanan Internet & Jaringan' => [
                'Teknisi Lapangan',
                'NOC / Monitoring',
                'Customer Service',
                'Kasir Billing',
                'Marketing Internet',
            ],
            'MStore GTWash' => [
                'Kasir Toko',
                'Operator Cuci',
                'Staff Gudang',
                'Admin & Keuangan',
            ],
        ];
    }

    /**
     * Daftar flat semua posisi standar.
     */
    public static function allPositions(): array
    {
        $all = [];
        foreach (self::groupedPositions() as $positions) {
            $all = array_merge($all, $positions);
        }
        return array_values(array_unique($all));
    }

    /**
     * Resolusi otomatis JobFunction berdasarkan nama posisi.
     */
    public static function resolveJobFunction(?string $position): self
    {
        $target = strtolower(trim((string) $position));

        if (str_contains($target, 'noc') || str_contains($target, 'monitoring') || str_contains($target, 'network engineer')) {
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
     * Resolusi otomatis Divisi bisnis berdasarkan nama posisi.
     */
    public static function resolveDivision(?string $position): string
    {
        $gtWashKeywords = [
            'kasir toko',
            'operator cuci',
            'staff gudang',
            'admin & keuangan',
            'wash',
            'atk',
            'cuci',
            'gudang'
        ];

        $target = strtolower(trim((string) $position));
        foreach ($gtWashKeywords as $kw) {
            if (str_contains($target, $kw)) {
                return 'MStore GTWash';
            }
        }

        return 'Layanan Internet & Jaringan';
    }
}
