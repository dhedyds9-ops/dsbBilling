<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bi_kpis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type'); // revenue, profit, arpu, mrr, customer_growth, churn_rate, etc.
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('module');
            $table->string('granularity'); // hourly, daily, weekly, monthly, quarterly, yearly
            $table->json('current_value')->nullable();
            $table->json('previous_period_value')->nullable();
            $table->json('same_period_last_year_value')->nullable();
            $table->json('thresholds')->nullable(); // {warning: 75, critical: 90}
            $table->json('targets')->nullable(); // Array of {target, effective_from}
            $table->json('breakdown')->nullable();
            $table->string('status')->default('active');
            $table->uuid('created_by')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('module');
            $table->index('granularity');
            $table->index('status');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bi_kpis');
    }
};
