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
        Schema::table('service_profiles', function (Blueprint $table) {
            // Multi Tenant
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            
            // Visibility
            $table->string('visibility')->default('private'); // private, shared, global
            
            // Validity Unit
            $table->string('validity_unit')->default('days'); // days, months, hours
            
            // Index untuk performa
            $table->index('visibility');
            $table->index('tenant_id');
            $table->index('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_profiles', function (Blueprint $table) {
            $table->dropColumn(['owner_id', 'tenant_id', 'branch_id', 'visibility', 'validity_unit']);
        });
    }
};
