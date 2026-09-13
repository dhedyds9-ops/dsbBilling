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
        Schema::table('service_profiles', function (Blueprint $table) {
            $table->decimal('owner_price', 15, 2)->nullable()->after('base_price');
            $table->decimal('reseller_price', 15, 2)->nullable()->after('owner_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_profiles', function (Blueprint $table) {
            $table->dropColumn(['owner_price', 'reseller_price']);
        });
    }
};
