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
        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        
        Schema::table('customer_services', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_services', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        
        Schema::table('customer_services', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
