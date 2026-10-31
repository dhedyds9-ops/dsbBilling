<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assets')) {
            Schema::table('assets', function (Blueprint $table) {
                if (!Schema::hasColumn('assets', 'latitude')) {
                    $table->string('latitude', 50)->nullable()->after('status');
                }
                if (!Schema::hasColumn('assets', 'longitude')) {
                    $table->string('longitude', 50)->nullable()->after('latitude');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assets')) {
            Schema::table('assets', function (Blueprint $table) {
                if (Schema::hasColumn('assets', 'latitude')) {
                    $table->dropColumn('latitude');
                }
                if (Schema::hasColumn('assets', 'longitude')) {
                    $table->dropColumn('longitude');
                }
            });
        }
    }
};
