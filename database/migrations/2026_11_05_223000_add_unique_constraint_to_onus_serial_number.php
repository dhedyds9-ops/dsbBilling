<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('onus') && Schema::hasColumn('onus', 'serial_number')) {
            // Bersihkan string kosong menjadi NULL agar tidak dianggap duplikat oleh unique index
            DB::table('onus')->where('serial_number', '')->update(['serial_number' => null]);

            Schema::table('onus', function (Blueprint $table) {
                $table->unique('serial_number', 'onus_serial_number_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('onus') && Schema::hasColumn('onus', 'serial_number')) {
            Schema::table('onus', function (Blueprint $table) {
                $table->dropUnique('onus_serial_number_unique');
            });
        }
    }
};
