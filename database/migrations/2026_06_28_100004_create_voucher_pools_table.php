<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_pools', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('service_profile_id')->constrained()->onDelete('cascade');
            $table->string('prefix')->nullable();
            $table->integer('length')->default(8);
            $table->integer('quota')->default(0);
            $table->integer('validity_days')->nullable();
            $table->integer('total_vouchers')->default(0);
            $table->integer('used_vouchers')->default(0);
            $table->integer('active_vouchers')->default(0);
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_pools');
    }
};
