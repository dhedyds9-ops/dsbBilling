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
        if (Schema::hasTable('expenses') && !Schema::hasColumn('expenses', 'reseller_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->unsignedBigInteger('reseller_id')->nullable()->after('category')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'reseller_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn('reseller_id');
            });
        }
    }
};
