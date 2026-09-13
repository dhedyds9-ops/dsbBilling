<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('invoices')
            && Schema::hasColumn('invoices', 'items')
            && !Schema::hasColumn('invoices', 'item_details')
        ) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->renameColumn('items', 'item_details');
            });
            return;
        }

        if (Schema::hasTable('invoices') && !Schema::hasColumn('invoices', 'item_details')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->json('item_details')->nullable()->after('status');
            });
        }

        if (
            Schema::hasTable('invoices')
            && Schema::hasColumn('invoices', 'items')
            && Schema::hasColumn('invoices', 'item_details')
        ) {
            DB::table('invoices')
                ->whereNull('item_details')
                ->update([
                    'item_details' => DB::raw('items'),
                ]);
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('invoices')
            && Schema::hasColumn('invoices', 'item_details')
            && !Schema::hasColumn('invoices', 'items')
        ) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->renameColumn('item_details', 'items');
            });
        }
    }
};
