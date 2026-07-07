<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->foreignId('service_profile_id')->nullable()->after('code')->constrained('service_profiles')->nullOnDelete();
            $table->integer('validity_days')->nullable()->after('service_profile_id');
            $table->text('notes')->nullable()->after('validity_days');
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropForeign(['service_profile_id']);
            $table->dropColumn(['service_profile_id', 'validity_days', 'notes']);
        });
    }
};
