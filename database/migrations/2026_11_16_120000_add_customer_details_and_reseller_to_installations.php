<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installations', function (Blueprint $table) {
            if (!Schema::hasColumn('installations', 'customer_phone')) {
                $table->string('customer_phone', 50)->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('installations', 'installation_address')) {
                $table->text('installation_address')->nullable()->after('customer_phone');
            }
            if (!Schema::hasColumn('installations', 'reseller_id')) {
                $table->unsignedBigInteger('reseller_id')->nullable()->after('customer_id');
            }
            if (!Schema::hasColumn('installations', 'package_name')) {
                $table->string('package_name')->nullable()->after('installation_address');
            }
            if (!Schema::hasColumn('installations', 'package_id')) {
                $table->unsignedBigInteger('package_id')->nullable()->after('package_name');
            }
            if (!Schema::hasColumn('installations', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('pop_id');
            }
            if (!Schema::hasColumn('installations', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('installations', function (Blueprint $table) {
            $cols = [];
            foreach (['customer_phone', 'installation_address', 'reseller_id', 'package_name', 'package_id', 'latitude', 'longitude'] as $col) {
                if (Schema::hasColumn('installations', $col)) {
                    $cols[] = $col;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
