<?php

namespace App\Services\Auth;

use App\Enums\UserPermission;
use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\Role;

class PermissionRegistry
{
    /**
     * Definisi modul sistem dan pengelompokannya untuk UI Matriks Hak Akses.
     * Jika $roleName === 'reseller', hanya mengembalikan modul & izin yang berlaku di Portal Reseller.
     */
    public static function getCategorizedPermissions(?string $roleName = null): array
    {
        if ($roleName === 'reseller') {
            return self::getResellerCategorizedPermissions();
        }

        return self::getAllCategorizedPermissions();
    }

    /**
     * Definisi matriks izin khusus role Reseller (Portal Reseller).
     * Membatasi izin hanya pada fitur yang nyata ada di Portal Reseller (27 izin).
     */
    public static function getResellerCategorizedPermissions(): array
    {
        return [
            'crm_customer' => [
                'title' => 'Pelanggan & CRM Mitra',
                'icon' => 'people',
                'description' => 'Akses pengelolaan data pelanggan milik reseller.',
                'modules' => [
                    'customer' => [
                        'title' => 'Data Pelanggan (Customer)',
                        'actions' => ['view', 'create', 'update', 'delete', 'export'],
                    ],
                ],
            ],
            'services_aaa' => [
                'title' => 'Layanan Internet & Voucher',
                'icon' => 'wifi',
                'description' => 'Akses layanan PPPoE, Hotspot, dan pencetakan voucher internet.',
                'modules' => [
                    'pppoe-user' => [
                        'title' => 'Pelanggan PPPoE',
                        'actions' => ['view', 'create', 'update'],
                    ],
                    'hotspot-user' => [
                        'title' => 'Pelanggan Hotspot',
                        'actions' => ['view', 'create', 'update'],
                    ],
                    'voucher' => [
                        'title' => 'Voucher Hotspot & Cetak',
                        'actions' => ['view', 'create', 'print'],
                    ],
                ],
            ],
            'billing_finance' => [
                'title' => 'Tagihan & Pembayaran Mitra',
                'icon' => 'receipt_long',
                'description' => 'Akses penagihan invoice pelanggan dan input pembayaran tagihan.',
                'modules' => [
                    'invoice' => [
                        'title' => 'Tagihan (Invoice)',
                        'actions' => ['view', 'payment', 'export'],
                    ],
                    'payment' => [
                        'title' => 'Riwayat Pembayaran',
                        'actions' => ['view'],
                    ],
                ],
            ],
            'network_infra' => [
                'title' => 'Jaringan & Perangkat Fiber',
                'icon' => 'hub',
                'description' => 'Monitoring status perangkat OLT, ODC, dan ODP yang ditugaskan ke reseller.',
                'modules' => [
                    'olt' => [
                        'title' => 'Data OLT Fiber',
                        'actions' => ['view'],
                    ],
                    'odc' => [
                        'title' => 'Data ODC Fiber',
                        'actions' => ['view'],
                    ],
                    'odp' => [
                        'title' => 'Data ODP Fiber',
                        'actions' => ['view'],
                    ],
                ],
            ],
            'spatial_gis' => [
                'title' => 'Peta Satelit & Wilayah (GIS)',
                'icon' => 'public',
                'description' => 'Peta persebaran jaringan fiber dan lokasi ODP di wilayah reseller.',
                'modules' => [
                    'gis' => [
                        'title' => 'Peta Satelit GIS',
                        'actions' => ['view'],
                    ],
                ],
            ],
            'deposit_finance' => [
                'title' => 'Saldo Deposit & Keuangan Mitra',
                'icon' => 'account_balance_wallet',
                'description' => 'Akses saldo deposit, mutasi dompet, top up saldo, dan rekapitulasi penjualan.',
                'modules' => [
                    'finance' => [
                        'title' => 'Saldo & Mutasi Dompet Mitra',
                        'actions' => ['view', 'settlement', 'report'],
                    ],
                    'report' => [
                        'title' => 'Laporan Penjualan & Pendapatan',
                        'actions' => ['view', 'export'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Semua modul & izin sistem lengkap (193 izin untuk Admin & Manager).
     */
    public static function getAllCategorizedPermissions(): array
    {
        return [
            'crm_customer' => [
                'title' => 'Pelanggan & CRM',
                'icon' => 'people',
                'description' => 'Akses pengelolaan pelanggan, mitra reseller, dan cabang.',
                'modules' => [
                    'customer' => 'Pelanggan (Customer)',
                    'reseller' => 'Reseller & Sub-Staff',
                    'branch'   => 'Kantor Cabang',
                ],
            ],
            'billing_finance' => [
                'title' => 'Tagihan & Keuangan',
                'icon' => 'receipt_long',
                'description' => 'Akses pembuatan tagihan, penerimaan pembayaran, dan laporan keuangan.',
                'modules' => [
                    'invoice' => 'Tagihan (Invoice)',
                    'payment' => 'Pembayaran (Payment)',
                    'finance' => 'Kas & Keuangan (Laba Rugi)',
                    'report'  => 'Laporan & Rekapitulasi',
                ],
            ],
            'services_aaa' => [
                'title' => 'Layanan Internet & Paket',
                'icon' => 'wifi',
                'description' => 'Akses PPPoE, Hotspot, voucher internet, dan profil paket.',
                'modules' => [
                    'pppoe-user'       => 'Pelanggan PPPoE',
                    'hotspot-user'     => 'Pelanggan Hotspot',
                    'voucher'          => 'Voucher Hotspot',
                    'voucher-template' => 'Template Voucher',
                    'service-profile'  => 'Profil Paket Layanan',
                    'network-profile'  => 'Profil Jaringan',
                    'ip-pool'          => 'Alokasi IP Pool',
                ],
            ],
            'network_infra' => [
                'title' => 'Infrastruktur Jaringan & OLT',
                'icon' => 'hub',
                'description' => 'Akses perangkat core, OLT, ODC, ODP, POP, router, dan topologi.',
                'modules' => [
                    'router'            => 'Router Core / MikroTik',
                    'olt'               => 'Perangkat OLT Fiber',
                    'odc'               => 'Perangkat ODC',
                    'odp'               => 'Perangkat ODP',
                    'pop'               => 'Data POP',
                    'tower'             => 'Tower Pemancar',
                    'nas-device'        => 'Perangkat NAS',
                    'switch'            => 'Switch Jaringan',
                    'access-point'      => 'Access Point',
                    'splitter'          => 'Optical Splitter',
                    'distribution-box'  => 'Distribution Box',
                    'vendor'            => 'Data Vendor / Rekanan',
                    'vlan'              => 'VLAN Jaringan',
                    'network-interface' => 'Interface Jaringan',
                    'dns-server'        => 'DNS Server',
                    'radius-server'     => 'RADIUS Server',
                    'radius'            => 'Radius Service',
                    'network'           => 'Manajemen Jaringan Global',
                    'monitoring'        => 'Monitoring Status Jaringan',
                ],
            ],
            'genieacs_onu' => [
                'title' => 'GenieACS & Perangkat ONU',
                'icon' => 'router',
                'description' => 'Akses status ONU, reboot, diagnosa optik, ganti WiFi, remediasi & unlock.',
                'modules' => [
                    'onu' => 'Manajemen ONU (GenieACS)',
                ],
            ],
            'noc_monitoring' => [
                'title' => 'NOC (Network Operation Center)',
                'icon' => 'monitor_heart',
                'description' => 'Akses portal monitoring NOC, alarm real-time, dan topologi.',
                'modules' => [
                    'noc' => 'NOC Portal & Live Alarms',
                ],
            ],
            'spatial_gis' => [
                'title' => 'GIS & Pemetaan Spasial',
                'icon' => 'public',
                'description' => 'Peta persebaran jaringan fiber optik, coverage area, dan ODP locator.',
                'modules' => [
                    'gis' => 'Sistem Informasi Geografis (GIS)',
                ],
            ],
            'helpdesk_ticket' => [
                'title' => 'Support & Tiket Masalah',
                'icon' => 'support_agent',
                'description' => 'Pengelolaan tiket bantuan pelanggan, penugasan teknisi, dan eskalasi.',
                'modules' => [
                    'ticket' => 'Tiket Pengaduan (Support)',
                ],
            ],
            'workforce_tech' => [
                'title' => 'Kepegawaian & Teknisi Lapangan',
                'icon' => 'engineering',
                'description' => 'Portal teknisi, pekerjaan instalasi/maintenance, QC checklist, dan absensi.',
                'modules' => [
                    'workforce'  => 'Pekerjaan Teknisi & QC',
                    'technician' => 'Portal Teknisi Mandiri',
                ],
            ],
            'system_admin' => [
                'title' => 'Sistem & Pengaturan',
                'icon' => 'settings',
                'description' => 'Konfigurasi identitas perusahaan, manajemen akun staf, dan hak akses.',
                'modules' => [
                    'user'       => 'Manajemen Pengguna (User)',
                    'role'       => 'Manajemen Role & Hak Akses',
                    'settings'   => 'Pengaturan Sistem',
                    'pengaturan' => 'Identitas Perusahaan',
                ],
            ],
        ];
    }

    /**
     * Label aksi yang ramah pengguna.
     */
    public static function formatActionLabel(string $action): string
    {
        return match (strtolower($action)) {
            'view'               => 'Lihat Data',
            'create'             => 'Tambah Data',
            'update'             => 'Edit / Perbarui',
            'delete'             => 'Hapus Data',
            'export'             => 'Export Data',
            'import'             => 'Import Data',
            'restore'            => 'Restore Data',
            'manage'             => 'Kelola Penuh',
            'payment'            => 'Proses Bayar',
            'report'             => 'Lihat Laporan',
            'settlement'         => 'Settlement Kas',
            'print'              => 'Cetak Dokumen',
            'assign'             => 'Assign Petugas',
            'portal'             => 'Akses Portal',
            'diagnose'           => 'Diagnosa Optik',
            'reboot'             => 'Reboot Perangkat',
            'configure.wifi'     => 'Ubah WiFi (SSID/Pass)',
            'configure.wan'      => 'Konfigurasi WAN',
            'firmware.update'    => 'Upgrade Firmware',
            'firmware.downgrade' => 'Downgrade Firmware',
            'factory_reset'      => 'Reset Pabrik',
            'remediation.view'       => 'Lihat Remediasi',
            'remediation.execute'    => 'Jalankan Remediasi',
            'remediation.approve'    => 'Setujui Remediasi',
            'remediation.reconcile'  => 'Rekonsiliasi Status',
            'unlock.execute'         => 'Jalankan Unlock',
            'unlock.approve'         => 'Setujui Unlock',
            'olt.view'           => 'Monitor OLT',
            'onu.view'           => 'Monitor ONU',
            'onu.manage'         => 'Operasi ONU',
            'router.view'        => 'Monitor Router',
            'pppoe.view'         => 'Sesi PPPoE Aktif',
            'alarms.view'        => 'Lihat Alarms',
            'alarms.manage'      => 'Tindakan Alarms',
            'provisioning.view'  => 'Monitor Provisioning',
            'topology.view'      => 'Peta Topologi NOC',
            'jobs.view'          => 'Lihat Tugas WO',
            'jobs.execute'       => 'Kerjakan Tugas WO',
            'qc.view'            => 'Lihat Checklist QC',
            'qc.execute'         => 'Eksekusi Checklist QC',
            'material.view'      => 'Lihat Penggunaan Material',
            'material.execute'   => 'Input Penggunaan Material',
            'attendance.execute' => 'Eksekusi Absensi',
            'perusahaan'         => 'Ubah Identitas Perusahaan',
            default              => ucwords(str_replace(['.', '-', '_'], ' ', $action)),
        };
    }

    /**
     * Sinkronisasikan seluruh permission dari Enum UserPermission dan modul jaringan ke database.
     */
    public static function syncAllPermissionsToDatabase(): int
    {
        $permissions = [];

        // 1. Dari Enum UserPermission
        foreach (UserPermission::cases() as $case) {
            $name = $case->value;
            $parts = explode('.', $name, 2);
            $module = $parts[0] ?? 'system';
            $action = $parts[1] ?? 'view';

            $permissions[$name] = [
                'name'         => $name,
                'display_name' => self::formatActionLabel($action) . ' (' . ucwords(str_replace('-', ' ', $module)) . ')',
                'description'  => 'Hak untuk ' . strtolower(self::formatActionLabel($action)) . ' pada modul ' . str_replace('-', ' ', $module),
            ];
        }

        // 2. Modul infrastruktur tambahan (jika ada yang dibuat dari seeder)
        $networkModules = [
            'vendor', 'tower', 'pop', 'olt', 'odc', 'odp', 'splitter',
            'distribution-box', 'onu', 'router', 'switch', 'access-point',
            'nas-device', 'ip-pool', 'vlan', 'network-interface', 'dns-server', 'radius-server',
        ];

        foreach ($networkModules as $module) {
            foreach (['view', 'create', 'update', 'delete'] as $act) {
                $permName = "{$module}.{$act}";
                if (!isset($permissions[$permName])) {
                    $modTitle = ucwords(str_replace('-', ' ', $module));
                    $actTitle = self::formatActionLabel($act);
                    $permissions[$permName] = [
                        'name'         => $permName,
                        'display_name' => "{$actTitle} ({$modTitle})",
                        'description'  => "Hak untuk " . strtolower($actTitle) . " pada modul {$modTitle}",
                    ];
                }
            }
        }

        $count = 0;
        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['name' => $perm['name']], $perm);
            $count++;
        }

        // Pastikan Administrator mendapatkan semua permissions
        $adminRole = Role::where('name', UserRole::Administrator->value)->first();
        if ($adminRole) {
            $adminRole->permissions()->sync(Permission::all());
        }

        return $count;
    }
}
