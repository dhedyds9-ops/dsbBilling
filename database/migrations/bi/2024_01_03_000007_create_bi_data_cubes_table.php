<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bi_data_cubes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('table'); // Source table name
            $table->text('description')->nullable();
            $table->string('module');
            $table->json('measures')->nullable(); // Array of {field, function, alias}
            $table->json('dimensions')->nullable(); // Array of {name, label, data_type}
            $table->json('filters')->nullable();
            $table->json('pre_aggregations')->nullable();
            $table->json('last_refreshed_data')->nullable();
            $table->timestamp('last_refreshed_at')->nullable();
            $table->json('statistics')->nullable();
            $table->string('status')->default('active');
            $table->uuid('created_by')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('table');
            $table->index('module');
            $table->index('status');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bi_data_cubes');
    }
};
