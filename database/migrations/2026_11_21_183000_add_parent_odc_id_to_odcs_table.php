<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('odcs') && !Schema::hasColumn('odcs', 'parent_odc_id')) {
            Schema::table('odcs', function (Blueprint $table) {
                $table->unsignedBigInteger('parent_odc_id')->nullable()->after('olt_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('odcs') && Schema::hasColumn('odcs', 'parent_odc_id')) {
            Schema::table('odcs', function (Blueprint $table) {
                $table->dropIndex(['parent_odc_id']);
                $table->dropColumn('parent_odc_id');
            });
        }
    }
};
