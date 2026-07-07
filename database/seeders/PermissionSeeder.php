<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $networkModules = [
            'vendor',
            'tower',
            'pop',
            'olt',
            'odc',
            'odp',
            'splitter',
            'distribution-box',
            'onu',
            'router',
            'switch',
            'access-point',
            'nas-device',
            'ip-pool',
            'vlan',
            'network-interface',
            'dns-server',
            'radius-server',
        ];

        $permissions = [
            ['name' => 'service-profile.view', 'display_name' => 'Lihat Service Profile', 'description' => 'Melihat daftar Service Profile'],
            ['name' => 'service-profile.create', 'display_name' => 'Buat Service Profile', 'description' => 'Membuat Service Profile baru'],
            ['name' => 'service-profile.update', 'display_name' => 'Edit Service Profile', 'description' => 'Mengedit Service Profile'],
            ['name' => 'service-profile.delete', 'display_name' => 'Hapus Service Profile', 'description' => 'Menghapus Service Profile'],
            ['name' => 'service-profile.export', 'display_name' => 'Export Service Profile', 'description' => 'Export Service Profile ke Excel/PDF'],
            ['name' => 'service-profile.restore', 'display_name' => 'Restore Service Profile', 'description' => 'Restore Service Profile yang dihapus'],
        ];

        foreach ($networkModules as $module) {
            $moduleName = ucwords(str_replace('-', ' ', $module));
            $permissions[] = ['name' => "$module.view", 'display_name' => "Lihat $moduleName", 'description' => "Melihat daftar $moduleName"];
            $permissions[] = ['name' => "$module.create", 'display_name' => "Buat $moduleName", 'description' => "Membuat $moduleName baru"];
            $permissions[] = ['name' => "$module.update", 'display_name' => "Edit $moduleName", 'description' => "Mengedit $moduleName"];
            $permissions[] = ['name' => "$module.delete", 'display_name' => "Hapus $moduleName", 'description' => "Menghapus $moduleName"];
            $permissions[] = ['name' => "$module.export", 'display_name' => "Export $moduleName", 'description' => "Export $moduleName ke Excel/PDF"];
            $permissions[] = ['name' => "$module.restore", 'display_name' => "Restore $moduleName", 'description' => "Restore $moduleName yang dihapus"];
        }

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['name' => $perm['name']], $perm);
        }

        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdminRole->permissions()->sync(Permission::all());
        }
    }
}
