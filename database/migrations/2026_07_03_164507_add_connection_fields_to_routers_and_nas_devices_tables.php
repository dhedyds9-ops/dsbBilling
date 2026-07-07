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
        Schema::table('routers', function (Blueprint $table) {
            $table->integer('api_port')->default(8728);
            $table->boolean('use_ssl')->default(false);
            $table->integer('timeout')->default(30);
            $table->string('routeros_version')->nullable();
            $table->timestamp('last_seen_at')->nullable();
        });

        Schema::table('nas_devices', function (Blueprint $table) {
            $table->integer('api_port')->default(8728);
            $table->boolean('use_ssl')->default(false);
            $table->integer('timeout')->default(30);
            $table->string('routeros_version')->nullable();
            $table->timestamp('last_seen_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropColumn(['api_port', 'use_ssl', 'timeout', 'routeros_version', 'last_seen_at']);
        });

        Schema::table('nas_devices', function (Blueprint $table) {
            $table->dropColumn(['api_port', 'use_ssl', 'timeout', 'routeros_version', 'last_seen_at']);
        });
    }
};
