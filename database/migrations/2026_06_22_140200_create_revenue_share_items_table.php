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
        Schema::create('revenue_share_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('revenue_share_batches')->onDelete('cascade');
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->decimal('member_revenue', 15, 2)->default(0);
            $table->decimal('percentage', 8, 5)->default(0);
            $table->decimal('expense_share', 15, 2)->default(0);
            $table->decimal('net_share', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['batch_id', 'member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenue_share_items');
    }
};
