<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_services', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_services', 'network_profile_id')) {
                $table->foreignId('network_profile_id')->nullable()->after('service_profile_id')->constrained('network_profiles')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_services', function (Blueprint $table) {
            if (Schema::hasColumn('customer_services', 'network_profile_id')) {
                $table->dropForeign(['network_profile_id']);
                $table->dropColumn('network_profile_id');
            }
        });
    }
};
