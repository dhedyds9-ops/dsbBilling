<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('olts', function (Blueprint $table) {
            $table->string('latitude')->nullable()->after('status');
            $table->string('longitude')->nullable()->after('latitude');
            $table->string('address')->nullable()->after('longitude');
            $table->string('host')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('olts', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'address', 'host']);
        });
    }
};
