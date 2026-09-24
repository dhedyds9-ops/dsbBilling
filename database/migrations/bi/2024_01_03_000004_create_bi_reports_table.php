<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bi_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type'); // pdf, excel, csv, html
            $table->string('module');
            $table->string('status')->default('draft'); // draft, generating, ready, failed, scheduled
            $table->json('sections')->nullable(); // Array of {title, content, chart_type}
            $table->json('filters')->nullable();
            $table->json('parameters')->nullable();
            $table->string('generated_file_path')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->integer('execution_time')->nullable(); // milliseconds
            $table->json('schedule')->nullable(); // {cron_expression, recipients, next_run}
            $table->uuid('created_by')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('module');
            $table->index('status');
            $table->index('type');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bi_reports');
    }
};
