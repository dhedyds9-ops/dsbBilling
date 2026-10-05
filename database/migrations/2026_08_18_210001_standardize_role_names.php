<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Standardisasi role names.
 *
 * Mapping (reversible):
 *  super_admin  → administrator
 *  admin        → administrator (jika ada)
 *  operator     → manager
 *  supervisor   → manager
 *  finance      → manager
 *  treasurer    → manager
 *  auditor      → manager
 *  staff        → manager (jika ada)
 *
 * TIDAK diubah otomatis (perlu audit bisnis manual):
 *  owner   → tetap, audit dulu
 *  sales   → tetap, audit dulu
 *  seller  → tetap, audit dulu
 *
 * DIPERTAHANKAN (valid roles):
 *  manager    → tetap
 *  reseller   → tetap
 *  customer   → tetap (customer portal)
 *
 * CATATAN:
 *  - Migration ini TIDAK menghapus role lama jika masih ada user yang memakainya
 *  - Gunakan safe upsert: insert 'administrator' jika belum ada, lalu migrate user
 *  - Fully reversible via down() method
 */
return new class extends Migration
{
    /**
     * Mapping: old role name => new role name
     */
    private array $mapping = [
        'super_admin' => 'administrator',
        'admin'       => 'administrator',
        'operator'    => 'manager',
        'supervisor'  => 'manager',
        'finance'     => 'manager',
        'treasurer'   => 'manager',
        'auditor'     => 'manager',
        'staff'       => 'manager',
    ];

    public function up(): void
    {
        // CREATE BACKUP TABLES FOR REVERSIBILITY
        if (!Schema::hasTable('role_user_backup_20260818')) {
            DB::statement('CREATE TABLE role_user_backup_20260818 AS SELECT * FROM role_user');
        }
        if (!Schema::hasTable('permission_role_backup_20260818')) {
            DB::statement('CREATE TABLE permission_role_backup_20260818 AS SELECT * FROM permission_role');
        }

        // Step 1: Pastikan role 'administrator' ada (insert jika belum)
        $adminRole = DB::table('roles')->where('name', 'administrator')->first();
        if (!$adminRole) {
            DB::table('roles')->insert([
                'name'         => 'administrator',
                'display_name' => 'Administrator',
                'description'  => 'Akses penuh sistem ISP. Menggantikan super_admin.',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // Step 2: Untuk setiap mapping, migrate user dari role lama ke role baru
        foreach ($this->mapping as $oldRoleName => $newRoleName) {
            $oldRole = DB::table('roles')->where('name', $oldRoleName)->first();
            $newRole = DB::table('roles')->where('name', $newRoleName)->first();

            if (!$oldRole || !$newRole) {
                continue; // Role tidak ada, skip
            }

            // Ambil semua user_id yang memiliki role lama
            $userIds = DB::table('role_user')
                ->where('role_id', $oldRole->id)
                ->pluck('user_id');

            foreach ($userIds as $userId) {
                // Cek apakah user sudah punya role baru
                $alreadyHasNewRole = DB::table('role_user')
                    ->where('user_id', $userId)
                    ->where('role_id', $newRole->id)
                    ->exists();

                if (!$alreadyHasNewRole) {
                    // Tambah user ke role baru
                    DB::table('role_user')->insert([
                        'user_id'    => $userId,
                        'role_id'    => $newRole->id,
                    ]);
                }

                // Hapus user dari role lama
                DB::table('role_user')
                    ->where('user_id', $userId)
                    ->where('role_id', $oldRole->id)
                    ->delete();
            }

            // Copy permissions dari role lama ke role baru (jika belum ada)
            $oldPermissionIds = DB::table('permission_role')
                ->where('role_id', $oldRole->id)
                ->pluck('permission_id');

            foreach ($oldPermissionIds as $permId) {
                $alreadyHas = DB::table('permission_role')
                    ->where('role_id', $newRole->id)
                    ->where('permission_id', $permId)
                    ->exists();

                if (!$alreadyHas) {
                    DB::table('permission_role')->insert([
                        'role_id'       => $newRole->id,
                        'permission_id' => $permId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('role_user_backup_20260818')) {
            DB::table('role_user')->truncate();
            DB::statement('INSERT INTO role_user SELECT * FROM role_user_backup_20260818');
            Schema::dropIfExists('role_user_backup_20260818');
        }

        if (Schema::hasTable('permission_role_backup_20260818')) {
            DB::table('permission_role')->truncate();
            DB::statement('INSERT INTO permission_role SELECT * FROM permission_role_backup_20260818');
            Schema::dropIfExists('permission_role_backup_20260818');
        }
    }
};
