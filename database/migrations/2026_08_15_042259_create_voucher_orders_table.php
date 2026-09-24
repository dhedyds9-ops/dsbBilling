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
        Schema::create('voucher_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('invoice_id')->unique()->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('service_profile_id')->constrained('service_profiles')->cascadeOnDelete();
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->nullOnDelete();
            
            $table->string('wa_number', 20);
            
            // Harga Snapshot
            $table->string('service_profile_name');
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->decimal('total_amount', 12, 2)->default(0);
            
            // Lifecycle
            $table->string('status', 30)->default('pending'); // pending, payment_processing, paid, voucher_generating, completed, failed, expired, cancelled
            
            // Payment tracking (opsional tapi berguna untuk debugging)
            $table->string('payment_reference')->nullable();
            
            // Hasil Kredensial Hotspot
            $table->string('voucher_username', 100)->nullable();
            $table->string('voucher_password', 100)->nullable();
            
            // Waktu & Log
            $table->timestamp('voucher_generated_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('failure_reason')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_orders');
    }
};
