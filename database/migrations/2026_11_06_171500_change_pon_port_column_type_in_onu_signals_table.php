<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('onu_signals')) {
            Schema::table('onu_signals', function (Blueprint $table) {
                $table->unsignedBigInteger('pon_port')->nullable()->change();
            });
        }

        if (Schema::hasTable('olt_metrics')) {
            Schema::table('olt_metrics', function (Blueprint $table) {
                $table->unsignedBigInteger('pon_port')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('onu_signals')) {
            Schema::table('onu_signals', function (Blueprint $table) {
                $table->unsignedSmallInteger('pon_port')->nullable()->change();
            });
        }

        if (Schema::hasTable('olt_metrics')) {
            Schema::table('olt_metrics', function (Blueprint $table) {
                $table->unsignedSmallInteger('pon_port')->nullable()->change();
            });
        }
    }
};
