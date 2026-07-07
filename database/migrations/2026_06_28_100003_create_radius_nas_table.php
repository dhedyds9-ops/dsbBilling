<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radius_nas', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('nas_name');
            $table->string('nas_ip_address');
            $table->string('nas_secret');
            $table->string('nas_type')->default('mikrotik');
            $table->integer('nas_port')->default(1812);
            $table->string('community')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            
            $table->index('nas_ip_address');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radius_nas');
    }
};
