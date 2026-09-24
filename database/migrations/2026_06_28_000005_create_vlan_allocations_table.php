<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vlan_allocations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('service_instance_id')->constrained()->onDelete('cascade');
            $table->foreignId('vlan_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('vlan_tag')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('allocated_at')->nullable();
            $table->timestamp('deallocated_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vlan_allocations');
    }
};
