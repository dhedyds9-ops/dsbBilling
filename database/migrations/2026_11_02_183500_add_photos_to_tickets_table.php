<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                if (!Schema::hasColumn('tickets', 'photos')) {
                    $table->json('photos')->nullable()->after('resolution_notes');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                if (Schema::hasColumn('tickets', 'photos')) {
                    $table->dropColumn('photos');
                }
            });
        }
    }
};
