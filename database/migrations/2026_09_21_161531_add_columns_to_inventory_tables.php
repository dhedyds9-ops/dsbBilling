<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->softDeletes();
        });
        
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn(['name', 'code', 'deleted_at']);
        });
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->dropColumn(['name', 'code', 'deleted_at']);
        });
    }
};
