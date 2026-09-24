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
        Schema::create('ppp_active_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('service')->nullable();
            $table->string('caller_id')->nullable();
            $table->string('address')->nullable();
            $table->string('uptime')->nullable();
            $table->bigInteger('bytes_in')->default(0);
            $table->bigInteger('bytes_out')->default(0);
            $table->bigInteger('packets_in')->default(0);
            $table->bigInteger('packets_out')->default(0);
            $table->string('rate_up')->nullable();
            $table->string('rate_down')->nullable();
            $table->timestamp('session_started_at')->nullable();
            $table->timestamps();
            
            $table->index(['router_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppp_active_sessions');
    }
};
