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
        Schema::create('router_monitoring_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_online')->default(false);
            $table->string('identity')->nullable();
            $table->string('version')->nullable();
            $table->string('cpu')->nullable();
            $table->integer('cpu_load')->default(0);
            $table->bigInteger('free_memory')->default(0);
            $table->bigInteger('total_memory')->default(0);
            $table->string('uptime')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['router_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('router_monitoring_logs');
    }
};
