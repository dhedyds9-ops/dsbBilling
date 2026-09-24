<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radius_accounting', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('acct_session_id')->unique();
            $table->string('username');
            $table->string('nas_ip_address')->nullable();
            $table->string('nas_port_id')->nullable();
            $table->string('framed_ip_address')->nullable();
            $table->string('framed_protocol')->nullable();
            $table->timestamp('acct_start_time')->nullable();
            $table->timestamp('acct_stop_time')->nullable();
            $table->bigInteger('acct_input_octets')->default(0);
            $table->bigInteger('acct_output_octets')->default(0);
            $table->bigInteger('acct_input_packets')->default(0);
            $table->bigInteger('acct_output_packets')->default(0);
            $table->integer('acct_session_time')->default(0);
            $table->string('acct_terminate_cause')->nullable();
            $table->foreignId('pppoe_user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('hotspot_user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_service_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            
            $table->index('username');
            $table->index('acct_session_id');
            $table->index('acct_start_time');
            $table->index('customer_service_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radius_accounting');
    }
};
