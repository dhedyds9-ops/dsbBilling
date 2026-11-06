<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. inventory_stock_opnames
        if (!Schema::hasTable('inventory_stock_opnames')) {
            Schema::create('inventory_stock_opnames', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('opname_number', 50)->unique()->index();
                $table->unsignedBigInteger('warehouse_id')->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->string('status', 30)->default('draft')->index(); // draft, in_progress, completed, cancelled
                $table->date('opname_date')->index();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->unsignedBigInteger('approved_by')->nullable()->index();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
            });
        }

        // 2. inventory_stock_opname_items
        if (!Schema::hasTable('inventory_stock_opname_items')) {
            Schema::create('inventory_stock_opname_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('opname_id')->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->decimal('system_quantity', 12, 2)->default(0);
                $table->decimal('physical_quantity', 12, 2)->default(0);
                $table->decimal('difference_quantity', 12, 2)->default(0); // physical - system
                $table->string('adjustment_reason', 100)->nullable(); // rusak, hilang, selisih hitung, dll
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('opname_id')->references('id')->on('inventory_stock_opnames')->cascadeOnDelete();
                $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stock_opname_items');
        Schema::dropIfExists('inventory_stock_opnames');
    }
};
