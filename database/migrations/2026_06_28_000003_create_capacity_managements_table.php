<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capacity_managements', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('resource_type');
            $table->unsignedBigInteger('resource_id');
            $table->integer('total_capacity')->default(0);
            $table->integer('used_capacity')->default(0);
            $table->integer('available_capacity')->default(0);
            $table->integer('threshold_warning')->default(80);
            $table->integer('threshold_critical')->default(95);
            $table->timestamp('last_checked_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capacity_managements');
    }
};
