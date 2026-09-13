<?php

namespace Database\Seeders;

use App\Enums\UserPermission;
use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Definisikan core roles
        $rolesData = [
            [
                'name'         => UserRole::Administrator->value,
                'display_name' => 'Administrator',
                'description'  => 'Akses penuh ke seluruh sistem',
            ],
            [
                'name'         => UserRole::Manager->value,
                'display_name' => 'Manager',
                'description'  => 'Akses operasional dan support (staff internal)',
            ],
            [
                'name'         => UserRole::Reseller->value,
                'display_name' => 'Reseller',
                'description'  => 'Akses terbatas ke Reseller Portal untuk mengelola customer sendiri',
            ],
            [
                'name'         => UserRole::Customer->value,
                'display_name' => 'Customer',
                'description'  => 'Akses ke Customer Portal',
            ],
        ];

        foreach ($rolesData as $roleData) {
            Role::updateOrCreate(['name' => $roleData['name']], $roleData);
        }

        // 2. Definisikan base permissions
        $permissionsData = [
            ['name' => UserPermission::SettingsUpdate->value, 'display_name' => 'Kelola Pengaturan', 'description' => 'Akses menu pengaturan aplikasi'],
            ['name' => UserPermission::UserUpdate->value, 'display_name' => 'Kelola Pengguna', 'description' => 'Akses menu pengguna dan roles'],
            ['name' => UserPermission::FinanceSettlement->value, 'display_name' => 'Settlement & Keuangan', 'description' => 'Akses menu keuangan dan persetujuan pengeluaran'],
            ['name' => UserPermission::TicketAssign->value, 'display_name' => 'Assign Tiket', 'description' => 'Hak untuk assign tiket ke teknisi/manager lain'],
            // Technician & NOC Portal Access
            ['name' => UserPermission::TechnicianPortal->value, 'display_name' => 'Akses Portal Teknisi', 'description' => 'Akses halaman instalasi dan provisioning teknisi'],
            ['name' => UserPermission::NocPortal->value, 'display_name' => 'Akses Portal NOC', 'description' => 'Akses halaman operasi jaringan khusus NOC'],

            // Granular ONU Permissions (Phase 7.5.4)
            ['name' => UserPermission::OnuView->value, 'display_name' => 'View ONU', 'description' => 'Melihat data dan status ONU'],
            ['name' => UserPermission::OnuDiagnose->value, 'display_name' => 'Diagnose ONU', 'description' => 'Menjalankan diagnostik dan melihat optik'],
            ['name' => UserPermission::OnuReboot->value, 'display_name' => 'Reboot ONU', 'description' => 'Melakukan restart perangkat ONU'],
            ['name' => UserPermission::OnuConfigureWifi->value, 'display_name' => 'Configure WiFi', 'description' => 'Mengubah nama dan password WiFi'],
            ['name' => UserPermission::OnuConfigureWan->value, 'display_name' => 'Configure WAN', 'description' => 'Melakukan push konfigurasi WAN ke ONU'],
            ['name' => UserPermission::OnuFirmwareUpdate->value, 'display_name' => 'Firmware Update', 'description' => 'Melakukan upgrade firmware ONU'],
            ['name' => UserPermission::OnuFirmwareDowngrade->value, 'display_name' => 'Firmware Downgrade', 'description' => 'Melakukan downgrade firmware (HIGH RISK)'],
            ['name' => UserPermission::OnuFactoryReset->value, 'display_name' => 'Factory Reset', 'description' => 'Mengembalikan ONU ke pengaturan pabrik (HIGH RISK)'],
            ['name' => UserPermission::OnuRemediationView->value, 'display_name' => 'View Remediation', 'description' => 'Melihat status remediasi ONU'],
            ['name' => UserPermission::OnuRemediationExecute->value, 'display_name' => 'Execute Remediation', 'description' => 'Menjalankan task remediasi low-risk'],
            ['name' => UserPermission::OnuRemediationApprove->value, 'display_name' => 'Approve Remediation', 'description' => 'Menyetujui plan remediasi'],
            ['name' => UserPermission::OnuRemediationReconcile->value, 'display_name' => 'Reconcile UNKNOWN Remediation', 'description' => 'Hak khusus untuk me-resolve job yang UNKNOWN (High Risk)'],
            ['name' => UserPermission::OnuUnlockExecute->value, 'display_name' => 'Execute Unlock', 'description' => 'Menjalankan unlock (Telnet/Bridge) pada ONU'],
            ['name' => UserPermission::OnuUnlockApprove->value, 'display_name' => 'Approve Unlock', 'description' => 'Menyetujui request unlock (HIGH RISK)'],

            // Network & service profile legacy permissions
            ['name' => 'service-profile.view', 'display_name' => 'Lihat Service Profile', 'description' => 'Melihat Daftar Service Profile'],
            ['name' => 'service-profile.create', 'display_name' => 'Buat Service Profile', 'description' => 'Membuat Service Profile baru'],
            ['name' => 'service-profile.update', 'display_name' => 'Edit Service Profile', 'description' => 'Mengedit Service Profile'],
            ['name' => 'service-profile.delete', 'display_name' => 'Hapus Service Profile', 'description' => 'Menghapus Service Profile'],
        ];

        $networkModules = [
            'vendor', 'tower', 'pop', 'olt', 'odc', 'odp', 'splitter',
            'distribution-box', 'onu', 'router', 'switch', 'access-point',
            'nas-device', 'ip-pool', 'vlan', 'network-interface', 'dns-server', 'radius-server',
        ];

        foreach ($networkModules as $module) {
            $moduleName = ucwords(str_replace('-', ' ', $module));
            $permissionsData[] = ['name' => "$module.view", 'display_name' => "Lihat $moduleName", 'description' => "Melihat daftar $moduleName"];
            $permissionsData[] = ['name' => "$module.create", 'display_name' => "Buat $moduleName", 'description' => "Membuat $moduleName baru"];
            $permissionsData[] = ['name' => "$module.update", 'display_name' => "Edit $moduleName", 'description' => "Mengedit $moduleName"];
            $permissionsData[] = ['name' => "$module.delete", 'display_name' => "Hapus $moduleName", 'description' => "Menghapus $moduleName"];
        }

        foreach ($permissionsData as $perm) {
            Permission::updateOrCreate(['name' => $perm['name']], $perm);
        }

        // 3. Assign all permissions to Administrator
        $adminRole = Role::where('name', UserRole::Administrator->value)->first();
        if ($adminRole) {
            $adminRole->permissions()->sync(Permission::all());
        }

        // 4. Assign specific permissions to Manager
        $managerRole = Role::where('name', UserRole::Manager->value)->first();
        if ($managerRole) {
            $managerPermissions = Permission::whereIn('name', [
                UserPermission::TicketAssign->value,
                'service-profile.view',
            ])->orWhere('name', 'like', '%.view')->get();
            $managerRole->permissions()->sync($managerPermissions);
        }
    }
}
