<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('odps') && !Schema::hasColumn('odps', 'odc_port')) {
            Schema::table('odps', function (Blueprint $table) {
                $table->unsignedSmallInteger('odc_port')->nullable()->after('odc_id');
            });
        }

        if (Schema::hasTable('splitters') && !Schema::hasColumn('splitters', 'odc_id')) {
            Schema::table('splitters', function (Blueprint $table) {
                $table->foreignId('odc_id')->nullable()->after('odp_id')->constrained('odcs')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('odps') && Schema::hasColumn('odps', 'odc_port')) {
            Schema::table('odps', function (Blueprint $table) {
                $table->dropColumn('odc_port');
            });
        }

        if (Schema::hasTable('splitters') && Schema::hasColumn('splitters', 'odc_id')) {
            Schema::table('splitters', function (Blueprint $table) {
                $table->dropForeign(['odc_id']);
                $table->dropColumn('odc_id');
            });
        }
    }
};
