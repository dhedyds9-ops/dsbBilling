<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('gps_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('technician_id');
            $table->double('latitude');
            $table->double('longitude');
            $table->double('speed')->nullable();
            $table->double('heading')->nullable();
            $table->double('altitude')->nullable();
            $table->timestamp('logged_at');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('gps_histories');
    }
};
