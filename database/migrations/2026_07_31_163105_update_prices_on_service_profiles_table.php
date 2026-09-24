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
            $table->renameColumn('owner_price', 'owner_settlement_price');
            $table->renameColumn('reseller_price', 'reseller_settlement_price');
            $table->decimal('branch_settlement_price', 15, 2)->nullable()->after('owner_settlement_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_profiles', function (Blueprint $table) {
            $table->dropColumn('branch_settlement_price');
            $table->renameColumn('owner_settlement_price', 'owner_price');
            $table->renameColumn('reseller_settlement_price', 'reseller_price');
        });
    }
};
