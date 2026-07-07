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
        Schema::create('alarms', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('level')->default('warning'); // info, warning, critical
            $table->string('source_type'); // e.g., App\Models\ISP\Router
            $table->unsignedBigInteger('source_id');
            $table->string('source_name')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('resolved_at')->nullable();
            $table->string('status')->default('open'); // open, acknowledged, resolved, closed
            $table->unsignedBigInteger('acknowledged_by')->nullable();
            $table->text('acknowledged_note')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
            
            $table->index(['source_type', 'source_id']);
            $table->index(['level', 'status']);
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alarms');
    }
};
