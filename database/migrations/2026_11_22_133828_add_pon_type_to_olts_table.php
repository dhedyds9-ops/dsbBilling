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
        if (Schema::hasTable('olts') && !Schema::hasColumn('olts', 'pon_type')) {
            Schema::table('olts', function (Blueprint $table) {
                $table->string('pon_type', 32)->default('auto')->nullable()->after('model');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('olts') && Schema::hasColumn('olts', 'pon_type')) {
            Schema::table('olts', function (Blueprint $table) {
                $table->dropColumn('pon_type');
            });
        }
    }
};
