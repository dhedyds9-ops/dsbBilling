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
            $table->string('latitude')->nullable()->after('address');
            $table->string('longitude')->nullable()->after('latitude');
        });

        Schema::table('onus', function (Blueprint $table) {
            $table->string('latitude')->nullable()->after('password');
            $table->string('longitude')->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members_and_onus', function (Blueprint $table) {
            //
        });
    }
};
