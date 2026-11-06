<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Dokumen Pemakaian Material Lapangan (Usages)
        if (!Schema::hasTable('inventory_usages')) {
            Schema::create('inventory_usages', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('usage_number', 50)->unique()->index();
                $table->unsignedBigInteger('technician_id')->index();
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->unsignedBigInteger('installation_id')->nullable()->index();
                $table->unsignedBigInteger('ticket_id')->nullable()->index();
                $table->string('work_order_type', 40)->default('psb')->index(); // psb, maintenance, relocation, other
                $table->date('usage_date')->index();
                $table->string('optical_power', 30)->nullable(); // contoh: -19.5 dBm
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('technician_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        // 2. Detail Item Terpakai (Usage Items)
        if (!Schema::hasTable('inventory_usage_items')) {
            Schema::create('inventory_usage_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('usage_id')->index();
                $table->unsignedBigInteger('custody_id')->nullable()->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->unsignedBigInteger('item_serial_id')->nullable()->index();
                $table->decimal('quantity', 12, 2)->default(1);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('usage_id')->references('id')->on('inventory_usages')->cascadeOnDelete();
                $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
            });
        }

        // 3. Dokumen Retur Material ke Gudang (Returns)
        if (!Schema::hasTable('inventory_returns')) {
            Schema::create('inventory_returns', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('return_number', 50)->unique()->index();
                $table->unsignedBigInteger('returner_id')->index(); // User yang mengembalikan (Teknisi / Reseller)
                $table->string('returner_type', 30)->default('technician')->index();
                $table->unsignedBigInteger('warehouse_id')->index(); // Gudang tujuan pengembalian
                $table->string('status', 30)->default('pending_qc')->index(); // pending_qc, accepted, rejected
                $table->unsignedBigInteger('inspected_by')->nullable()->index();
                $table->timestamp('inspected_at')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('returner_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
            });
        }

        // 4. Detail Item Retur & Hasil QC (Return Items)
        if (!Schema::hasTable('inventory_return_items')) {
            Schema::create('inventory_return_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('return_id')->index();
                $table->unsignedBigInteger('custody_id')->nullable()->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->unsignedBigInteger('item_serial_id')->nullable()->index();
                $table->decimal('quantity', 12, 2)->default(1);
                $table->string('condition', 30)->default('good'); // good, needs_check, damaged
                $table->string('action_taken', 30)->default('restock'); // restock, quarantine, scrap, rma
                $table->text('qc_notes')->nullable();
                $table->timestamps();

                $table->foreign('return_id')->references('id')->on('inventory_returns')->cascadeOnDelete();
                $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_return_items');
        Schema::dropIfExists('inventory_returns');
        Schema::dropIfExists('inventory_usage_items');
        Schema::dropIfExists('inventory_usages');
    }
};
