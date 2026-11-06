<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Permintaan Barang (Requisitions)
        if (!Schema::hasTable('inventory_requisitions')) {
            Schema::create('inventory_requisitions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('requisition_number', 50)->unique()->index();
                $table->unsignedBigInteger('requester_id')->index(); // User yang meminta (Reseller/Teknisi)
                $table->string('requester_type', 30)->default('reseller')->index();
                $table->unsignedBigInteger('warehouse_id')->index(); // Gudang sumber barang
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->string('distribution_method', 30)->default('pickup_reseller'); // pickup_reseller (Metode A) atau direct_technician (Metode B)
                $table->unsignedBigInteger('customer_id')->nullable()->index(); // Pelanggan tujuan
                $table->string('work_order_type', 40)->nullable()->index(); // psb, maintenance, relocation, dismantle, stock_topup
                $table->unsignedBigInteger('installation_id')->nullable()->index();
                $table->unsignedBigInteger('ticket_id')->nullable()->index();
                $table->unsignedBigInteger('designated_technician_id')->nullable()->index(); // Teknisi pelaksana
                $table->string('status', 30)->default('pending')->index(); // pending, approved, rejected, dispatched, cancelled
                $table->unsignedBigInteger('approved_by')->nullable()->index();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
                $table->foreign('requester_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        // 2. Detail Permintaan Barang (Requisition Items)
        if (!Schema::hasTable('inventory_requisition_items')) {
            Schema::create('inventory_requisition_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('requisition_id')->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->decimal('quantity_requested', 12, 2)->default(1);
                $table->decimal('quantity_approved', 12, 2)->nullable();
                $table->decimal('quantity_dispatched', 12, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('requisition_id')->references('id')->on('inventory_requisitions')->cascadeOnDelete();
                $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
            });
        }

        // 3. Pengeluaran Barang dari Gudang (Dispatches)
        if (!Schema::hasTable('inventory_dispatches')) {
            Schema::create('inventory_dispatches', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('dispatch_number', 50)->unique()->index();
                $table->unsignedBigInteger('requisition_id')->nullable()->index();
                $table->unsignedBigInteger('warehouse_id')->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->string('distribution_method', 30)->default('pickup_reseller'); // pickup_reseller (Metode A) atau direct_technician (Metode B)
                $table->unsignedBigInteger('recipient_id')->index(); // Reseller atau Teknisi penerima fisik
                $table->string('recipient_type', 30)->default('reseller')->index(); // reseller / technician
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->unsignedBigInteger('installation_id')->nullable()->index();
                $table->unsignedBigInteger('ticket_id')->nullable()->index();
                $table->string('status', 30)->default('dispatched')->index(); // dispatched, received_by_recipient, cancelled
                $table->unsignedBigInteger('dispatched_by')->nullable()->index();
                $table->timestamp('dispatched_at')->index();
                $table->timestamp('received_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
                $table->foreign('recipient_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        // 4. Detail Barang Keluar (Dispatch Items)
        if (!Schema::hasTable('inventory_dispatch_items')) {
            Schema::create('inventory_dispatch_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('dispatch_id')->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->decimal('quantity', 12, 2)->default(1);
                $table->json('serials')->nullable(); // Array SN yang dikeluarkan
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('dispatch_id')->references('id')->on('inventory_dispatches')->cascadeOnDelete();
                $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
            });
        }

        // 5. Custody Tracking (Barang Titipan Sementara di Tangan Reseller / Teknisi)
        if (!Schema::hasTable('inventory_custodies')) {
            Schema::create('inventory_custodies', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->unsignedBigInteger('dispatch_id')->nullable()->index();
                $table->unsignedBigInteger('requisition_id')->nullable()->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->unsignedBigInteger('item_serial_id')->nullable()->index();
                $table->unsignedBigInteger('holder_id')->index(); // Reseller / Teknisi pemegang fisik
                $table->string('holder_type', 30)->default('reseller')->index(); // reseller / technician
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->unsignedBigInteger('installation_id')->nullable()->index();
                $table->unsignedBigInteger('ticket_id')->nullable()->index();
                $table->decimal('quantity', 12, 2)->default(1);
                $table->decimal('used_quantity', 12, 2)->default(0);
                $table->decimal('returned_quantity', 12, 2)->default(0);
                $table->string('status', 30)->default('in_custody')->index(); // in_custody, handed_over, used, returned, lost_damaged
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
                $table->foreign('holder_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        // 6. Serah Terima Antar Pemegang (Custody Transfer: Reseller -> Teknisi pada Metode A)
        if (!Schema::hasTable('inventory_custody_transfers')) {
            Schema::create('inventory_custody_transfers', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('transfer_number', 50)->unique()->index();
                $table->unsignedBigInteger('custody_id')->index();
                $table->unsignedBigInteger('from_user_id')->index(); // Reseller Andi
                $table->string('from_user_type', 30)->default('reseller');
                $table->unsignedBigInteger('to_user_id')->index(); // Teknisi Rudi
                $table->string('to_user_type', 30)->default('technician');
                $table->unsignedBigInteger('item_id')->index();
                $table->unsignedBigInteger('item_serial_id')->nullable()->index();
                $table->decimal('quantity', 12, 2)->default(1);
                $table->string('status', 30)->default('pending_acceptance')->index(); // pending_acceptance, accepted, rejected
                $table->text('notes')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->timestamps();

                $table->foreign('custody_id')->references('id')->on('inventory_custodies')->cascadeOnDelete();
                $table->foreign('from_user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('to_user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_custody_transfers');
        Schema::dropIfExists('inventory_custodies');
        Schema::dropIfExists('inventory_dispatch_items');
        Schema::dropIfExists('inventory_dispatches');
        Schema::dropIfExists('inventory_requisition_items');
        Schema::dropIfExists('inventory_requisitions');
    }
};
