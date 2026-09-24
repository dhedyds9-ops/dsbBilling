<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. invoices
        if (!Schema::hasColumn('invoices', 'reseller_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('reseller_id')->nullable()->after('customer_id')->constrained('users')->nullOnDelete();
            });

            DB::statement("
                UPDATE invoices 
                SET reseller_id = (
                    SELECT reseller_id FROM members WHERE members.id = invoices.customer_id
                )
            ");
        }

        // 2. payments
        if (!Schema::hasColumn('payments', 'reseller_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->foreignId('reseller_id')->nullable()->after('customer_id')->constrained('users')->nullOnDelete();
            });

            DB::statement("
                UPDATE payments 
                SET reseller_id = (
                    SELECT reseller_id FROM members WHERE members.id = payments.customer_id
                )
            ");
        }

        // 3. customer_services
        if (!Schema::hasColumn('customer_services', 'reseller_id')) {
            Schema::table('customer_services', function (Blueprint $table) {
                $table->foreignId('reseller_id')->nullable()->after('customer_id')->constrained('users')->nullOnDelete();
            });

            DB::statement("
                UPDATE customer_services 
                SET reseller_id = (
                    SELECT reseller_id FROM members WHERE members.id = customer_services.customer_id
                )
            ");
        }

        // 4. tickets
        if (!Schema::hasColumn('tickets', 'reseller_id')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->foreignId('reseller_id')->nullable()->after('customer_id')->constrained('users')->nullOnDelete();
            });

            DB::statement("
                UPDATE tickets 
                SET reseller_id = (
                    SELECT reseller_id FROM members WHERE members.id = tickets.customer_id
                )
            ");
        }
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['reseller_id']);
            $table->dropColumn('reseller_id');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['reseller_id']);
            $table->dropColumn('reseller_id');
        });
        Schema::table('customer_services', function (Blueprint $table) {
            $table->dropForeign(['reseller_id']);
            $table->dropColumn('reseller_id');
        });
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['reseller_id']);
            $table->dropColumn('reseller_id');
        });
    }
};
