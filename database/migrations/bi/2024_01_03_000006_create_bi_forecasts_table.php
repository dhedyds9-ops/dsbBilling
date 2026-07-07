<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bi_forecasts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('metric_name');
            $table->string('model'); // linear_regression, exponential_smoothing, arima, moving_average
            $table->string('granularity'); // hourly, daily, weekly, monthly, quarterly, yearly
            $table->string('module');
            $table->integer('horizon_periods')->default(12);
            $table->float('confidence_level')->default(0.95);
            $table->json('historical_data')->nullable();
            $table->json('result')->nullable(); // {predictions, confidence_intervals}
            $table->json('model_parameters')->nullable();
            $table->json('validation_metrics')->nullable(); // {mae, mse, rmse, mape, accuracy}
            $table->json('prediction_period')->nullable();
            $table->string('status')->default('active');
            $table->uuid('created_by')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('metric_name');
            $table->index('model');
            $table->index('module');
            $table->index('status');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bi_forecasts');
    }
};
