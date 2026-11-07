<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('online_sessions')) {
            if (!Schema::hasColumn('online_sessions', 'ip_address')) {
                Schema::table('online_sessions', function (Blueprint $table) {
                    $table->string('ip_address', 45)->nullable()->after('address')->index();
                });
            }

            // Sync existing address to ip_address if ip_address exists
            if (Schema::hasColumn('online_sessions', 'address') && Schema::hasColumn('online_sessions', 'ip_address')) {
                DB::table('online_sessions')
                    ->whereNotNull('address')
                    ->whereNull('ip_address')
                    ->update([
                        'ip_address' => DB::raw('address')
                    ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('online_sessions') && Schema::hasColumn('online_sessions', 'ip_address')) {
            Schema::table('online_sessions', function (Blueprint $table) {
                $table->dropColumn('ip_address');
            });
        }
    }
};
