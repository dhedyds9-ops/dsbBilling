<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('router_provisioning_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('router_id')->constrained('routers')->cascadeOnDelete();
            
            // Only store the hash for security
            $table->string('token_hash')->unique();
            
            // PENDING, GENERATED, BOOTSTRAPPED, REGISTERED, CONFIGURED, FAILED, EXPIRED, REVOKED
            $table->string('status', 30)->default('PENDING');
            
            // For troubleshooting
            $table->text('error_message')->nullable();
            
            // Lifecycle timestamps
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('router_provisioning_sessions');
    }
};
