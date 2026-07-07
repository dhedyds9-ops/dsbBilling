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
        Schema::create('ip_pools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pop_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('network')->nullable()->comment('e.g., 192.168.1.0');
            $table->string('netmask')->nullable()->comment('e.g., 255.255.255.0');
            $table->string('gateway')->nullable();
            $table->string('dns_servers')->nullable();
            $table->string('start_ip')->nullable();
            $table->string('end_ip')->nullable();
            $table->integer('total_ips')->nullable()->default(0);
            $table->integer('used_ips')->nullable()->default(0);
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_pools');
    }
};
