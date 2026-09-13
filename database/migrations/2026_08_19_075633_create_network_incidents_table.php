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
        Schema::create('network_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->index()->comment('odp_down, pon_down, router_down, acs_degraded');
            $table->string('reference_id', 100)->nullable()->comment('ID of ODP, PON Port, Router, etc');
            $table->integer('impacted_customers_count')->default(0);
            $table->string('status', 30)->default('active')->index();
            $table->text('description')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('network_incidents');
    }
};
