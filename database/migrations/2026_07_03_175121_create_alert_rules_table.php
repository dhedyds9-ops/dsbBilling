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
        Schema::create('alert_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('source_type'); // e.g., App\Models\ISP\Router
            $table->string('parameter'); // e.g., cpu_load, memory_usage
            $table->string('operator'); // e.g., >, <, ==, !=
            $table->string('threshold');
            $table->string('level')->default('warning'); // info, warning, critical
            $table->boolean('is_active')->default(true);
            $table->integer('cooldown')->default(300); // seconds before alerting again
            $table->json('notification_channels')->nullable();
            $table->timestamps();
        });

        // Insert default rules
        $rules = [
            [
                'name' => 'High Router CPU',
                'source_type' => \App\Models\ISP\Router::class,
                'parameter' => 'cpu_load',
                'operator' => '>',
                'threshold' => '80',
                'level' => 'warning',
            ],
            [
                'name' => 'Critical Router CPU',
                'source_type' => \App\Models\ISP\Router::class,
                'parameter' => 'cpu_load',
                'operator' => '>',
                'threshold' => '95',
                'level' => 'critical',
            ],
            [
                'name' => 'High Router Memory',
                'source_type' => \App\Models\ISP\Router::class,
                'parameter' => 'memory_usage',
                'operator' => '>',
                'threshold' => '90',
                'level' => 'critical',
            ],
            [
                'name' => 'Low Router Health Score',
                'source_type' => \App\Models\ISP\Router::class,
                'parameter' => 'health_score',
                'operator' => '<',
                'threshold' => '70',
                'level' => 'critical',
            ],
            [
                'name' => 'Reduced Router Health Score',
                'source_type' => \App\Models\ISP\Router::class,
                'parameter' => 'health_score',
                'operator' => '<',
                'threshold' => '85',
                'level' => 'warning',
            ],
        ];

        foreach ($rules as $rule) {
            \App\Models\AlertRule::create($rule);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_rules');
    }
};
