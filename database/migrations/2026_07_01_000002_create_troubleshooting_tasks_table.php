<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('troubleshooting_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('work_order_id');
            $table->uuid('assignment_id');
            $table->uuid('customer_id');
            $table->uuid('ticket_id');
            $table->string('status')->default('not_started');
            $table->string('type')->default('troubleshooting');
            $table->text('notes')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('qc_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('troubleshooting_tasks');
    }
};
