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
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('owner_settlement_price', 15, 2)->nullable()->after('subtotal');
            $table->decimal('branch_settlement_price', 15, 2)->nullable()->after('owner_settlement_price');
            $table->decimal('reseller_settlement_price', 15, 2)->nullable()->after('branch_settlement_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn([
                'owner_settlement_price',
                'branch_settlement_price',
                'reseller_settlement_price'
            ]);
        });
    }
};
