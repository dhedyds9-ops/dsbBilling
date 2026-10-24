<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // Rekap absensi otomatis per periode
            $table->unsignedSmallInteger('work_days')->default(0)->after('notes')
                ->comment('Total hari kerja dalam periode');
            $table->unsignedSmallInteger('present_days')->default(0)->after('work_days')
                ->comment('Hari hadir (checked_in + checked_out)');
            $table->unsignedSmallInteger('late_days')->default(0)->after('present_days')
                ->comment('Hari terlambat');
            $table->unsignedSmallInteger('sick_days')->default(0)->after('late_days')
                ->comment('Hari sakit');
            $table->unsignedSmallInteger('permission_days')->default(0)->after('sick_days')
                ->comment('Hari izin');
            $table->unsignedSmallInteger('absent_days')->default(0)->after('permission_days')
                ->comment('Hari alpa / tidak hadir');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn([
                'work_days', 'present_days', 'late_days',
                'sick_days', 'permission_days', 'absent_days',
            ]);
        });
    }
};
