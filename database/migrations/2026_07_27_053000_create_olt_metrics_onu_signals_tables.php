<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('olt_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('olt_id')->index();
            $table->unsignedSmallInteger('pon_port')->nullable()->index();
            $table->string('metric_key', 50)->index();
            $table->decimal('metric_value', 16, 6);
            $table->string('unit', 20)->nullable();
            $table->timestamp('measured_at')->index();

            $table->foreign('olt_id')->references('id')->on('olts')->cascadeOnDelete();
            $table->index(['olt_id', 'measured_at']);
            $table->index(['metric_key', 'measured_at']);
        });

        Schema::create('onu_signals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('onu_id')->index();
            $table->unsignedBigInteger('olt_id')->nullable()->index();
            $table->unsignedSmallInteger('pon_port')->nullable()->index();
            $table->decimal('rx_power_dbm', 6, 2)->nullable();
            $table->decimal('tx_power_dbm', 6, 2)->nullable();
            $table->decimal('snr_db', 5, 2)->nullable();
            $table->decimal('biterature_db', 5, 2)->nullable();
            $table->decimal('temperature', 6, 2)->nullable();
            $table->decimal('laser_bias_current', 8, 3)->nullable();
            $table->decimal('voltage_v', 5, 2)->nullable();
            $table->string('status', 20)->nullable()->index();
            $table->timestamp('measured_at')->index();

            $table->foreign('onu_id')->references('id')->on('onus')->cascadeOnDelete();
            $table->foreign('olt_id')->references('id')->on('olts')->nullOnDelete();
            $table->index(['onu_id', 'measured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onu_signals');
        Schema::dropIfExists('olt_metrics');
    }
};
