<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('technician_id');
            $table->date('date');
            $table->string('status')->default('absent');
            $table->double('check_in_latitude')->nullable();
            $table->double('check_in_longitude')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->double('check_out_latitude')->nullable();
            $table->double('check_out_longitude')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('attendances');
    }
};
