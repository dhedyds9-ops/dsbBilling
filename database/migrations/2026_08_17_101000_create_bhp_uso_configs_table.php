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
        Schema::create('bhp_uso_configs', function (Blueprint $table) {
            $table->id();
            $table->string('period_name');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('calculation_basis')->default('RETAIL_REVENUE'); // 'RETAIL_REVENUE', 'RESELLER_MARGIN'
            $table->decimal('bhp_rate', 5, 4)->default(0.0050); // 0.5%
            $table->decimal('uso_rate', 5, 4)->default(0.0125); // 1.25%
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bhp_uso_configs');
    }
};
