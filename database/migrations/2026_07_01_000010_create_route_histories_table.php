<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('route_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('technician_id');
            $table->uuid('assignment_id');
            $table->double('start_latitude');
            $table->double('start_longitude');
            $table->double('end_latitude');
            $table->double('end_longitude');
            $table->double('distance');
            $table->integer('travel_time');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->json('waypoints')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('route_histories');
    }
};
