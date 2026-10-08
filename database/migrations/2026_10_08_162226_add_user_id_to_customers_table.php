<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('customers', 'user_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('customers', 'user_id')) {
            Schema::table('customers', function (Blueprint $table) {
                // If the constraint exists, dropping column in SQLite might be tricky depending on version, 
                // but Laravel handles it by rebuilding the table under the hood if necessary.
                $table->dropColumn('user_id');
            });
        }
    }
};
