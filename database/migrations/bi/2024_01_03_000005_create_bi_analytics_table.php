<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bi_analytics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type'); // revenue, customer, network, service
            $table->string('module');
            $table->json('time_range')->nullable();
            $table->json('metrics')->nullable(); // Array of {name, aggregation, label}
            $table->json('dimensions')->nullable(); // Array of {field, label}
            $table->json('segments')->nullable(); // Array of {name, conditions}
            $table->json('comparisons')->nullable(); // Array of {name, time_range, label}
            $table->json('metadata')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('module');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bi_analytics');
    }
};
