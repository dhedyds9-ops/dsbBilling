<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code')->unique();
            $table->foreignId('voucher_pool_id')->constrained()->onDelete('cascade');
            $table->foreignId('hotspot_user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('available'); // available, used, expired
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            
            $table->index('code');
            $table->index('status');
            $table->index('voucher_pool_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
