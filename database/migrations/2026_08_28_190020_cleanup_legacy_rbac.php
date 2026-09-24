<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $legacyRoles = ['operator', 'supervisor', 'staff', 'owner', 'super_admin', 'auditor', 'finance', 'treasurer', 'noc_operator'];
        $legacyJobFunctions = ['ADMINISTRATOR', 'CUSTOMER'];

        // 1. Detect existing legacy users by roles
        $usersWithLegacyRoles = User::whereHas('roles', function($q) use ($legacyRoles) {
            $q->whereIn('name', $legacyRoles);
        })->count();

        if ($usersWithLegacyRoles > 0) {
            throw new \Exception("Legacy user detected with legacy roles. Cleanup aborted. Manual migration required.");
        }

        // 2. Detect existing legacy users by JobFunction
        $usersWithLegacyJF = User::whereIn('job_function', $legacyJobFunctions)->count();

        if ($usersWithLegacyJF > 0) {
            throw new \Exception("Legacy user detected with legacy JobFunctions. Cleanup aborted. Manual migration required.");
        }

        // 3. Clean up roles
        $rolesToDelete = Role::whereIn('name', $legacyRoles)->get();
        foreach($rolesToDelete as $role) {
            // Delete role and its permissions association cleanly
            $role->delete();
        }

        // 4. Update any dangling job functions just to be perfectly clean
        DB::table('users')->whereIn('job_function', $legacyJobFunctions)->update(['job_function' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way migration. Cannot restore legacy roles without seeder.
    }
};
