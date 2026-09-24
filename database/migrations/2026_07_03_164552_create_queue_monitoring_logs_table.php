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
        Schema::create('queue_monitoring_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')->constrained()->cascadeOnDelete();
            $table->string('queue_name')->nullable();
            $table->string('target')->nullable();
            $table->string('max_limit')->nullable();
            $table->string('burst_limit')->nullable();
            $table->string('limit_at')->nullable();
            $table->bigInteger('bytes_in')->default(0);
            $table->bigInteger('bytes_out')->default(0);
            $table->bigInteger('packets_in')->default(0);
            $table->bigInteger('packets_out')->default(0);
            $table->string('rate_up')->nullable();
            $table->string('rate_down')->nullable();
            $table->timestamps();
            
            $table->index(['router_id', 'queue_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_monitoring_logs');
    }
};
