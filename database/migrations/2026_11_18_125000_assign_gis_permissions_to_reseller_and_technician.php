<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Role;
use App\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan permissions inti ada
        $gisView = Permission::firstOrCreate(['name' => 'gis.view'], ['display_name' => 'Lihat Peta Jaringan', 'guard_name' => 'web']);
        $odpView = Permission::firstOrCreate(['name' => 'odp.view'], ['display_name' => 'Lihat ODP', 'guard_name' => 'web']);
        $odcView = Permission::firstOrCreate(['name' => 'odc.view'], ['display_name' => 'Lihat ODC', 'guard_name' => 'web']);
        $oltView = Permission::firstOrCreate(['name' => 'olt.view'], ['display_name' => 'Lihat OLT', 'guard_name' => 'web']);

        // Assign ke Role Reseller
        $resellerRole = Role::where('name', 'reseller')->first();
        if ($resellerRole) {
            $resellerRole->permissions()->syncWithoutDetaching([$gisView->id, $odpView->id, $odcView->id, $oltView->id]);
        }

        // Assign ke Role Technician
        $techRole = Role::where('name', 'technician')->first();
        if ($techRole) {
            $techRole->permissions()->syncWithoutDetaching([$gisView->id, $odpView->id, $odcView->id, $oltView->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe keep
    }
};
