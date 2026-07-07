<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('route_optimizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('technician_id');
            $table->double('start_latitude');
            $table->double('start_longitude');
            $table->double('end_latitude');
            $table->double('end_longitude');
            $table->json('stops')->nullable();
            $table->double('total_distance');
            $table->integer('total_time');
            $table->string('optimization_method')->default('nearest_neighbor');
            $table->timestamp('calculated_at');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('route_optimizations');
    }
};
