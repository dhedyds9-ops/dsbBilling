<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bi_widgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('dashboard_id')->nullable();
            $table->string('name');
            $table->string('type'); // line_chart, bar_chart, pie_chart, gauge, number, table, heatmap
            $table->json('data_source')->nullable(); // {table, fields}
            $table->json('dimensions')->nullable(); // Array of {field, alias}
            $table->json('measures')->nullable(); // Array of {field, function, alias}
            $table->json('filters')->nullable();
            $table->json('chart_config')->nullable();
            $table->json('formatting')->nullable();
            $table->integer('refresh_interval')->default(300); // seconds
            $table->uuid('created_by')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('dashboard_id')->references('id')->on('bi_dashboards')->onDelete('cascade');
            $table->index('dashboard_id');
            $table->index('type');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bi_widgets');
    }
};
