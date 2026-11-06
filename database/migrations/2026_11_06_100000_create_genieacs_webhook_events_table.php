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
        Schema::create('genieacs_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 128)->unique();
            $table->string('device_id', 128)->index();
            $table->string('serial_number', 64)->nullable()->index();
            $table->string('event_type', 32)->default('inform');
            $table->json('event_codes')->nullable();
            $table->string('manufacturer', 64)->nullable();
            $table->string('product_class', 64)->nullable();
            $table->string('software_version', 64)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('last_inform_at')->nullable();
            $table->timestamp('received_at');
            $table->string('processing_status', 32)->default('received')->index();
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedSmallInteger('retry_count')->default(0);
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['device_id', 'last_inform_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('genieacs_webhook_events');
    }
};
