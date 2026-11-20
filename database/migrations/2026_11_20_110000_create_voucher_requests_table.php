<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_no', 64)->unique();
            $table->unsignedBigInteger('reseller_id')->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->unsignedBigInteger('service_profile_id')->index();
            $table->unsignedBigInteger('voucher_template_id')->nullable()->index();
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('delivery_type', 32)->default('physical_print'); // physical_print, digital
            $table->string('payment_method', 32)->default('deposit'); // deposit, cash, tempo
            $table->string('payment_status', 32)->default('unpaid'); // unpaid, paid
            $table->string('status', 32)->default('pending')->index(); // pending, approved, printing, ready_for_pickup, completed, rejected, cancelled
            $table->unsignedBigInteger('batch_pool_id')->nullable()->index();
            $table->json('generated_voucher_ids')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable()->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('reseller_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('service_profile_id')->references('id')->on('service_profiles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_requests');
    }
};
