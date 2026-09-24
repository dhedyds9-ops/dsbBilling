<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bi_dashboards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('module'); // CRM, Finance, Billing, NOC, Inventory
            $table->string('status')->default('draft'); // draft, published, archived, shared
            $table->json('widgets')->nullable(); // Array of widget configurations
            $table->json('filters')->nullable();
            $table->json('variables')->nullable();
            $table->integer('version')->default(1);
            $table->uuid('created_by')->nullable();
            $table->timestamp('effective_from')->nullable();
            $table->timestamp('effective_to')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('module');
            $table->index('status');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bi_dashboards');
    }
};
