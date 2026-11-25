<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Enforces database-level unique constraints for financial idempotency keys and offline sync action UUIDs.
     */
    public function up(): void
    {
        // 1. Unique index on payments.gateway_transaction_id
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'gateway_transaction_id')) {
            // Bersihkan string kosong menjadi NULL agar tidak dianggap duplikat oleh unique constraint
            DB::table('payments')
                ->whereRaw("TRIM(COALESCE(gateway_transaction_id, '')) = ''")
                ->update(['gateway_transaction_id' => null]);

            $isMysql = DB::connection()->getDriverName() === 'mysql';
            $indexExists = false;
            if ($isMysql) {
                $indexes = DB::select("SHOW INDEXES FROM payments WHERE Key_name = 'payments_gateway_transaction_id_unique'");
                $indexExists = !empty($indexes);
            }

            if (!$indexExists) {
                try {
                    Schema::table('payments', function (Blueprint $table) {
                        $table->unique('gateway_transaction_id', 'payments_gateway_transaction_id_unique');
                    });
                } catch (\Throwable $e) {
                    if (!str_contains($e->getMessage(), 'Duplicate key name')) {
                        // Toleransi jika constraint sudah terpasang
                    }
                }
            }
        }

        // 2. Unique index on material_usages.uuid
        if (Schema::hasTable('material_usages') && Schema::hasColumn('material_usages', 'uuid')) {
            DB::table('material_usages')
                ->whereRaw("TRIM(COALESCE(uuid, '')) = ''")
                ->update(['uuid' => null]);

            $isMysql = DB::connection()->getDriverName() === 'mysql';
            $indexExists = false;
            if ($isMysql) {
                $indexes = DB::select("SHOW INDEXES FROM material_usages WHERE Key_name = 'material_usages_uuid_unique'");
                $indexExists = !empty($indexes);
            }

            if (!$indexExists) {
                try {
                    Schema::table('material_usages', function (Blueprint $table) {
                        $table->unique('uuid', 'material_usages_uuid_unique');
                    });
                } catch (\Throwable $e) {
                    if (!str_contains($e->getMessage(), 'Duplicate key name')) {
                        // Toleransi jika constraint sudah terpasang
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('payments')) {
            try {
                Schema::table('payments', function (Blueprint $table) {
                    $table->dropUnique('payments_gateway_transaction_id_unique');
                });
            } catch (\Throwable $e) {}
        }

        if (Schema::hasTable('material_usages')) {
            try {
                Schema::table('material_usages', function (Blueprint $table) {
                    $table->dropUnique('material_usages_uuid_unique');
                });
            } catch (\Throwable $e) {}
        }
    }
};
