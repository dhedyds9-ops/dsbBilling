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
        Schema::create('monitoring_configs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, float
            $table->string('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        // Insert default configs
        $configs = [
            ['key' => 'router_poll_interval', 'value' => '30', 'type' => 'integer', 'description' => 'Interval in seconds to poll routers'],
            ['key' => 'radius_poll_interval', 'value' => '60', 'type' => 'integer', 'description' => 'Interval in seconds to poll RADIUS'],
            ['key' => 'onu_poll_interval', 'value' => '300', 'type' => 'integer', 'description' => 'Interval in seconds to poll ONUs'],
            ['key' => 'olt_poll_interval', 'value' => '120', 'type' => 'integer', 'description' => 'Interval in seconds to poll OLTs'],
            ['key' => 'alarm_check_interval', 'value' => '60', 'type' => 'integer', 'description' => 'Interval in seconds to check alarms'],
            ['key' => 'cache_ttl', 'value' => '15', 'type' => 'integer', 'description' => 'Cache TTL in seconds for monitoring data'],
            ['key' => 'max_retry', 'value' => '3', 'type' => 'integer', 'description' => 'Maximum number of retries for device connections'],
            ['key' => 'connection_timeout', 'value' => '30', 'type' => 'integer', 'description' => 'Connection timeout in seconds'],
            ['key' => 'notification_delay', 'value' => '300', 'type' => 'integer', 'description' => 'Delay in seconds before sending notifications for non-critical alarms'],
            ['key' => 'data_retention_raw', 'value' => '7', 'type' => 'integer', 'description' => 'Days to retain raw monitoring data'],
            ['key' => 'data_retention_hourly', 'value' => '90', 'type' => 'integer', 'description' => 'Days to retain hourly aggregated data'],
            ['key' => 'data_retention_daily', 'value' => '730', 'type' => 'integer', 'description' => 'Days to retain daily aggregated data'],
        ];

        foreach ($configs as $config) {
            \App\Models\MonitoringConfig::create($config);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_configs');
    }
};
