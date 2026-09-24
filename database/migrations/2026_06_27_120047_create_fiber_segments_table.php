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
        Schema::create('fiber_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiber_core_id')->constrained()->onDelete('cascade');
            $table->morphs('start_device');
            $table->morphs('end_device');
            $table->text('description')->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('loss', 5, 2)->nullable();
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiber_segments');
    }
};
