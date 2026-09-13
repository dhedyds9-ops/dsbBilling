<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel-tabel yang harus diisolasi per tenant (branch).
     */
    protected array $tables = [
        'customers',
        'customer_services',
        'invoices',
        'payments',
        'routers',
        'onus',
        'olts',
        'radius_servers',
        'network_profiles',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $blueprint) use ($table) {
                    if (!Schema::hasColumn($table, 'branch_id')) {
                        $blueprint->unsignedBigInteger('branch_id')->nullable();
                        $blueprint->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $blueprint) use ($table) {
                    if (Schema::hasColumn($table, 'branch_id')) {
                        // DB engine specific syntax may complain about dropping foreign key without name, 
                        // but Laravel usually handles array based dropForeign well if conventions are met.
                        $blueprint->dropForeign([$table . '_branch_id_foreign']);
                        $blueprint->dropColumn('branch_id');
                    }
                });
            }
        }
    }
};
