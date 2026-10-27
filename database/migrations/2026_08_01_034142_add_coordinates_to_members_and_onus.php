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
        Schema::table('members', function (Blueprint $table) {
            if (!Schema::hasColumn('members', 'latitude')) {
                $table->string('latitude')->nullable()->after('address');
            }
            if (!Schema::hasColumn('members', 'longitude')) {
                $table->string('longitude')->nullable()->after('latitude');
            }
        });

        Schema::table('onus', function (Blueprint $table) {
            if (!Schema::hasColumn('onus', 'latitude')) {
                $table->string('latitude')->nullable();
            }
            if (!Schema::hasColumn('onus', 'longitude')) {
                $table->string('longitude')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('onus', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
