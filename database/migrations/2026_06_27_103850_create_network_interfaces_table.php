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
        Schema::create('network_interfaces', function (Blueprint $table) {
            $table->id();
            $table->morphs('device'); // Untuk router, switch, access point, dll
            $table->string('name')->nullable();
            $table->string('type')->nullable()->comment('Ethernet, Fiber, Wireless, etc');
            $table->string('mac_address')->nullable();
            $table->string('ip_address')->nullable();
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
        Schema::dropIfExists('network_interfaces');
    }
};
