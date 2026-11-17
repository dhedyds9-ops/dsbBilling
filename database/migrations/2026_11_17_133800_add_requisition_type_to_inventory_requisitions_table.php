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
        if (Schema::hasTable('inventory_requisitions')) {
            Schema::table('inventory_requisitions', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_requisitions', 'requisition_type')) {
                    $table->string('requisition_type', 30)->default('dispatch_request')->index()->after('requisition_number');
                }
                if (!Schema::hasColumn('inventory_requisitions', 'urgency')) {
                    $table->string('urgency', 20)->default('normal')->after('requisition_type');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inventory_requisitions')) {
            Schema::table('inventory_requisitions', function (Blueprint $table) {
                if (Schema::hasColumn('inventory_requisitions', 'urgency')) {
                    $table->dropColumn('urgency');
                }
                if (Schema::hasColumn('inventory_requisitions', 'requisition_type')) {
                    $table->dropColumn('requisition_type');
                }
            });
        }
    }
};
