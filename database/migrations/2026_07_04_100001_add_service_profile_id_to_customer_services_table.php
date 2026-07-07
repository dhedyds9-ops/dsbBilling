<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_services', function (Blueprint $table) {
            $table->foreignId('service_profile_id')->nullable()->after('service_id')->constrained('service_profiles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customer_services', function (Blueprint $table) {
            $table->dropForeign(['service_profile_id']);
            $table->dropColumn('service_profile_id');
        });
    }
};
