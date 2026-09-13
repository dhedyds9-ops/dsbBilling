<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class NocPermissionsSeeder extends Seeder
{
    /**
     * NOC Permission hierarchy:
     *
     * NOC_VIEW (monitoring only, no device config)
     *   noc.view              — access NOC module
     *   noc.monitor           — view live monitoring data
     *   noc.alarms            — view & acknowledge alarms
     *   noc.provisioning.view — view provisioning pipelines
     *   noc.topology.view     — view network topology
     *
     * NOC_MANAGE (separate — device configuration, not assigned by default)
     *   noc.devices.manage    — modify device configuration
     *
     * Administrator bypasses all permission checks via hasPermission().
     */

    protected array $nocPermissions = [
        [
            'name'         => 'noc.view',
            'display_name' => 'NOC: View Module',
            'description'  => 'Access the NOC Monitoring module',
        ],
        [
            'name'         => 'noc.monitor',
            'display_name' => 'NOC: Monitor Devices',
            'description'  => 'View live network monitoring data (OLT, ONU, Router, PPPoE)',
        ],
        [
            'name'         => 'noc.alarms',
            'display_name' => 'NOC: View & Acknowledge Alarms',
            'description'  => 'View and acknowledge network alarms (cannot delete/modify alarm rules)',
        ],
        [
            'name'         => 'noc.provisioning.view',
            'display_name' => 'NOC: View Provisioning',
            'description'  => 'View provisioning pipelines and retry failed jobs via orchestrator',
        ],
        [
            'name'         => 'noc.topology.view',
            'display_name' => 'NOC: View Topology',
            'description'  => 'View network topology and customer impact analysis',
        ],
        [
            'name'         => 'noc.devices.manage',
            'display_name' => 'NOC: Manage Device Configuration',
            'description'  => 'Modify OLT/Router configuration. Monitoring != Configuration. Not assigned to NOC Operator by default.',
        ],
    ];

    public function run(): void
    {
        $viewPermissions = [];

        foreach ($this->nocPermissions as $permData) {
            $permission = Permission::firstOrCreate(
                ['name' => $permData['name']],
                [
                    'display_name' => $permData['display_name'],
                    'description'  => $permData['description'],
                ]
            );

            if ($permData['name'] !== 'noc.devices.manage') {
                $viewPermissions[] = $permission->id;
            }

            $this->command->info("Permission ensured: {$permData['name']}");
        }

        $adminRole = Role::where('name', 'administrator')->first();
        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching($viewPermissions);
            $this->command->info('NOC view permissions assigned to administrator role.');
        } else {
            $this->command->warn('Administrator role not found — permissions created but not assigned.');
        }

        // Enterprise roles yang levelnya staff/internal di-whitelist otomatis mendapatkan NOC view permissions.
        // Pakai ->get() + FOREACH loop agar SEMUA role yang match whitelist ke-sync,
        // BUKAN cuma ->first() yang hanya sync 1 role pertama.
        $nocRoles = Role::where(function ($q) {
            $q->where('name', 'noc_operator')
              ->orWhere('name', 'noc')
              ->orWhere('name', 'operator')
              ->orWhere('name', 'manager')
              ->orWhere('name', 'supervisor')
              ->orWhere('name', 'super_admin')
              ->orWhere('name', 'owner');
        })->get();

        if ($nocRoles->isNotEmpty()) {
            foreach ($nocRoles as $role) {
                $role->permissions()->syncWithoutDetaching($viewPermissions);
                $this->command->info("NOC view permissions assigned to '{$role->name}' role.");
            }
        } else {
            $this->command->info('No matching NOC roles found (noc_operator/noc/operator/manager/supervisor/super_admin/owner) — create one and assign noc.* permissions manually.');
        }

        $this->command->info('noc.devices.manage NOT auto-assigned to any role (requires explicit grant).');
    }
}
