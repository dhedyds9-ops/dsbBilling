<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_services', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_services', 'reactivation_status')) {
                $table->string('reactivation_status', 30)->nullable()->after('status')
                      ->comment('Tracks payment reconciliation: pending, success, failed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_services', function (Blueprint $table) {
            if (Schema::hasColumn('customer_services', 'reactivation_status')) {
                $table->dropColumn('reactivation_status');
            }
        });
    }
};
