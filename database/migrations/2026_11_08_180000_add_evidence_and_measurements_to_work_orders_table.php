<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('work_orders')) {
            Schema::table('work_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('work_orders', 'optical_rx_power')) {
                    $table->decimal('optical_rx_power', 8, 2)->nullable()->after('priority');
                }
                if (!Schema::hasColumn('work_orders', 'optical_tx_power')) {
                    $table->decimal('optical_tx_power', 8, 2)->nullable()->after('optical_rx_power');
                }
                if (!Schema::hasColumn('work_orders', 'customer_signature')) {
                    $table->longText('customer_signature')->nullable()->after('optical_tx_power');
                }
                if (!Schema::hasColumn('work_orders', 'evidence_photos')) {
                    $table->json('evidence_photos')->nullable()->after('customer_signature');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('work_orders')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $columns = ['optical_rx_power', 'optical_tx_power', 'customer_signature', 'evidence_photos'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('work_orders', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
