<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('work_orders') && !Schema::hasColumn('work_orders', 'installation_id')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->unsignedBigInteger('installation_id')->nullable()->after('ticket_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('work_orders') && Schema::hasColumn('work_orders', 'installation_id')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->dropColumn('installation_id');
            });
        }
    }
};
