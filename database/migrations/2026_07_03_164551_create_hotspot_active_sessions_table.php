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
        Schema::create('hotspot_active_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')->constrained()->cascadeOnDelete();
            $table->string('user')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('address')->nullable();
            $table->string('server')->nullable();
            $table->string('login_by')->nullable();
            $table->string('uptime')->nullable();
            $table->bigInteger('bytes_in')->default(0);
            $table->bigInteger('bytes_out')->default(0);
            $table->timestamp('session_started_at')->nullable();
            $table->timestamps();
            
            $table->index(['router_id', 'user']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotspot_active_sessions');
    }
};
