<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function columnExists(string $table, string $col): bool
    {
        try {
            return Schema::hasColumn($table, $col);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function up(): void
    {
        Schema::table('radius_nas', function (Blueprint $table) {
            if (!$this->columnExists('radius_nas', 'nas_device_id')) {
                $table->foreignId('nas_device_id')
                    ->nullable()
                    ->after('nas_ip_address')
                    ->constrained('nas_devices')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('radius_nas', function (Blueprint $table) {
            try {
                $rows = DB::select("PRAGMA foreign_key_list(radius_nas)");
                $hasFk = false;
                foreach ($rows as $r) {
                    if (($r->from ?? '') === 'nas_device_id') {
                        $hasFk = true;
                        break;
                    }
                }
                if ($hasFk) {
                    $table->dropForeign(['nas_device_id']);
                }
            } catch (\Throwable $e) {
                try {
                    $table->dropForeign(['nas_device_id']);
                } catch (\Throwable $e2) {
                }
            }
            if ($this->columnExists('radius_nas', 'nas_device_id')) {
                try {
                    $table->dropColumn('nas_device_id');
                } catch (\Throwable $e) {
                }
            }
        });
    }
};
