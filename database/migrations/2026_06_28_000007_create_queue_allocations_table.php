<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_allocations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('service_instance_id')->constrained()->onDelete('cascade');
            $table->string('queue_name')->nullable();
            $table->string('queue_id')->nullable();
            $table->integer('priority')->nullable();
            $table->bigInteger('download_limit')->nullable();
            $table->bigInteger('upload_limit')->nullable();
            $table->bigInteger('burst_limit')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('allocated_at')->nullable();
            $table->timestamp('deallocated_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_allocations');
    }
};
