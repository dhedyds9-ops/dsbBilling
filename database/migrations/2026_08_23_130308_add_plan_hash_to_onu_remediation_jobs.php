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
        Schema::table('onu_remediation_jobs', function (Blueprint $table) {
            $table->string('plan_hash')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('customer_service_id')->nullable()->constrained('customer_services')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onu_remediation_jobs', function (Blueprint $table) {
            //
        });
    }
};
