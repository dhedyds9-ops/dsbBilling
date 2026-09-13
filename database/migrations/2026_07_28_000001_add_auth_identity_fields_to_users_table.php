<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'customer_code')) {
                $table->string('customer_code', 32)->nullable()->unique()->after('uuid');
            }
            if (!Schema::hasColumn('users', 'pppoe_username')) {
                $table->string('pppoe_username', 64)->nullable()->unique()->after('customer_code');
            }
            if (!Schema::hasColumn('users', 'onu_sn')) {
                $table->string('onu_sn', 64)->nullable()->index()->after('pppoe_username');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('remember_token')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_active')) $table->dropColumn('is_active');
            if (Schema::hasColumn('users', 'onu_sn')) $table->dropColumn('onu_sn');
            if (Schema::hasColumn('users', 'pppoe_username')) {
                $table->dropUnique(['pppoe_username']);
                $table->dropColumn('pppoe_username');
            }
            if (Schema::hasColumn('users', 'customer_code')) {
                $table->dropUnique(['customer_code']);
                $table->dropColumn('customer_code');
            }
        });
    }
};
