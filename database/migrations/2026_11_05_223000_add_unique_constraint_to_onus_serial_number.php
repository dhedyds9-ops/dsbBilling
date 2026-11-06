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
            // 1. Bersihkan string kosong atau whitespace menjadi NULL agar tidak dianggap duplikat oleh unique index
            DB::table('onus')->whereRaw("TRIM(COALESCE(serial_number, '')) = ''")->update(['serial_number' => null]);

            // 2. Cari semua serial_number yang duplikat (tidak null) dan selesaikan sebelum membuat unique index
            $duplicates = DB::table('onus')
                ->select('serial_number')
                ->whereNotNull('serial_number')
                ->groupBy('serial_number')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('serial_number');

            foreach ($duplicates as $sn) {
                // Pertahankan record dengan customer_service_id atau ID tertinggi
                $records = DB::table('onus')
                    ->where('serial_number', $sn)
                    ->orderByDesc('customer_service_id')
                    ->orderByDesc('id')
                    ->get();

                // Sisanya (duplikat) di-rename dengan suffix _dup_{id} agar tidak melanggar constraint
                $duplicatesToRename = $records->slice(1);
                foreach ($duplicatesToRename as $dup) {
                    DB::table('onus')
                        ->where('id', $dup->id)
                        ->update(['serial_number' => $sn . '_dup_' . $dup->id]);
                }
            }

            // 3. Pasang unique constraint dengan aman jika belum terpasang
            $isMysql = DB::connection()->getDriverName() === 'mysql';
            $indexExists = false;
            if ($isMysql) {
                $indexes = DB::select("SHOW INDEXES FROM onus WHERE Key_name = 'onus_serial_number_unique'");
                $indexExists = !empty($indexes);
            }

            if (!$indexExists) {
                try {
                    Schema::table('onus', function (Blueprint $table) {
                        $table->unique('serial_number', 'onus_serial_number_unique');
                    });
                } catch (\Throwable $e) {
                    // Jika index sudah terpasang sebagian, abaikan
                    if (!str_contains($e->getMessage(), 'Duplicate key name')) {
                        throw $e;
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
        if (Schema::hasTable('onus') && Schema::hasColumn('onus', 'serial_number')) {
            Schema::table('onus', function (Blueprint $table) {
                $table->dropUnique('onus_serial_number_unique');
            });
        }
    }
};
