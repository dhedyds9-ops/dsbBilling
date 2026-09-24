<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sync_conflicts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('task_id');
            $table->uuid('user_id');
            $table->string('model_type');
            $table->uuid('model_id');
            $table->json('server_data');
            $table->json('client_data');
            $table->string('resolution_type')->default('server_wins');
            $table->json('resolved_data')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->uuid('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('sync_conflicts');
    }
};
