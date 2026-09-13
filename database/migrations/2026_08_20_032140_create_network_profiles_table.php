<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('network_profiles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            
            // Tipe layanan (pppoe, hotspot, static)
            $table->enum('type', ['pppoe', 'hotspot', 'static'])->default('pppoe');
            
            // Optional: Mengikat ke Router tertentu jika VLAN-nya unik per-router
            $table->foreignId('router_id')->nullable()->constrained('routers')->nullOnDelete();
            
            // VLAN ID yang akan di-push ke ONU dan dibuat di MikroTik
            $table->integer('vlan_id')->nullable();
            
            // Optional: IP Pool reference jika IP di-assign oleh RADIUS pool tertentu
            $table->foreignId('ip_pool_id')->nullable()->constrained('ip_pools')->nullOnDelete();
            
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Add network_profile_id to customer_services table
        Schema::table('customer_services', function (Blueprint $table) {
            $table->foreignId('network_profile_id')->nullable()->after('service_profile_id')->constrained('network_profiles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customer_services', function (Blueprint $table) {
            $table->dropForeign(['network_profile_id']);
            $table->dropColumn('network_profile_id');
        });
        Schema::dropIfExists('network_profiles');
    }
};
