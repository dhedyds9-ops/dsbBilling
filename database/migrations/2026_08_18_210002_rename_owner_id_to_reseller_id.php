<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Rename owner_id → reseller_id di tabel members, pppoe_users, hotspot_users.
 *
 * ALASAN:
 *  - owner_id semantiknya ambigu (owner ISP? reseller? admin yang input?)
 *  - reseller_id lebih eksplisit: menunjukkan reseller bisnis yang menjadi pemilik customer
 *  - created_by tetap dipertahankan untuk audit trail (siapa yang input record)
 *
 * CONTOH SEMANTIK YANG BENAR SETELAH MIGRATION:
 *  created_by  = Manager Budi (yang menginput data)
 *  reseller_id = Reseller A (yang memiliki customer ini secara bisnis)
 *  branch_id   = Cabang Sukabumi
 *
 * FULLY REVERSIBLE.
 */
return new class extends Migration
{
    public function up(): void
    {
        // =============================================
        // 1. TABLE: members (CRM\Customer)
        // =============================================
        Schema::table('members', function (Blueprint $table) {
            // Drop FK lama jika ada
            if (Schema::hasColumn('members', 'owner_id')) {
                try {
                    $table->dropForeign(['owner_id']);
                } catch (\Exception $e) {
                    // FK mungkin tidak ada dengan nama default, coba nama lain
                    try {
                        $table->dropForeign('members_owner_id_foreign');
                    } catch (\Exception $e2) {
                        // Lanjutkan
                    }
                }

                $table->renameColumn('owner_id', 'reseller_id');

                // Tambah FK baru
                $table->foreign('reseller_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }

            // Tambah branch_id jika belum ada
            if (!Schema::hasColumn('members', 'branch_id')) {
                $table->foreignId('branch_id')
                    ->nullable()
                    ->after('reseller_id')
                    ->constrained('branches')
                    ->nullOnDelete();
            }
        });

        // =============================================
        // 2. TABLE: pppoe_users
        // =============================================
        Schema::table('pppoe_users', function (Blueprint $table) {
            if (Schema::hasColumn('pppoe_users', 'owner_id')) {
                try {
                    $table->dropForeign(['owner_id']);
                } catch (\Exception $e) {
                    try {
                        $table->dropForeign('pppoe_users_owner_id_foreign');
                    } catch (\Exception $e2) {}
                }

                $table->renameColumn('owner_id', 'reseller_id');

                $table->foreign('reseller_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }
        });

        // =============================================
        // 3. TABLE: hotspot_users
        // =============================================
        Schema::table('hotspot_users', function (Blueprint $table) {
            if (Schema::hasColumn('hotspot_users', 'owner_id')) {
                try {
                    $table->dropForeign(['owner_id']);
                } catch (\Exception $e) {
                    try {
                        $table->dropForeign('hotspot_users_owner_id_foreign');
                    } catch (\Exception $e2) {}
                }

                $table->renameColumn('owner_id', 'reseller_id');

                $table->foreign('reseller_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }
        });

        // =============================================
        // 4. TABLE: vouchers (owner_id → reseller_id)
        // =============================================
        Schema::table('vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('vouchers', 'owner_id')) {
                try {
                    $table->dropForeign(['owner_id']);
                } catch (\Exception $e) {
                    try {
                        $table->dropForeign('vouchers_owner_id_foreign');
                    } catch (\Exception $e2) {}
                }

                $table->renameColumn('owner_id', 'reseller_id');

                $table->foreign('reseller_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // REVERSE: Rename kembali reseller_id → owner_id

        Schema::table('members', function (Blueprint $table) {
            if (Schema::hasColumn('members', 'reseller_id')) {
                $table->dropForeign(['reseller_id']);
                $table->renameColumn('reseller_id', 'owner_id');
                $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
            }
            if (Schema::hasColumn('members', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
        });

        Schema::table('pppoe_users', function (Blueprint $table) {
            if (Schema::hasColumn('pppoe_users', 'reseller_id')) {
                $table->dropForeign(['reseller_id']);
                $table->renameColumn('reseller_id', 'owner_id');
                $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
            }
        });

        Schema::table('hotspot_users', function (Blueprint $table) {
            if (Schema::hasColumn('hotspot_users', 'reseller_id')) {
                $table->dropForeign(['reseller_id']);
                $table->renameColumn('reseller_id', 'owner_id');
                $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
            }
        });

        Schema::table('vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('vouchers', 'reseller_id')) {
                $table->dropForeign(['reseller_id']);
                $table->renameColumn('reseller_id', 'owner_id');
                $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }
};
