<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Invoices table performance indexes
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                // Check and add indexes
                $table->index('status', 'idx_invoices_status');
                $table->index('due_date', 'idx_invoices_due_date');
                $table->index(['status', 'due_date'], 'idx_invoices_status_due');
                $table->index(['customer_id', 'status'], 'idx_invoices_cust_status');
                if (Schema::hasColumn('invoices', 'reseller_id')) {
                    $table->index(['reseller_id', 'status'], 'idx_invoices_reseller_status');
                }
            });
        }

        // 2. Customers table performance indexes
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->index('status', 'idx_customers_status');
                if (Schema::hasColumn('customers', 'phone')) {
                    $table->index('phone', 'idx_customers_phone');
                }
                if (Schema::hasColumn('customers', 'reseller_id')) {
                    $table->index(['reseller_id', 'status'], 'idx_customers_reseller_status');
                }
            });
        }

        // 3. Customer Services table performance indexes
        if (Schema::hasTable('customer_services')) {
            Schema::table('customer_services', function (Blueprint $table) {
                $table->index(['customer_id', 'status'], 'idx_cust_services_cust_status');
                if (Schema::hasColumn('customer_services', 'service_profile_id')) {
                    $table->index('service_profile_id', 'idx_cust_services_profile');
                }
            });
        }

        // 4. Vouchers table performance indexes
        if (Schema::hasTable('vouchers')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->index(['status', 'updated_at'], 'idx_vouchers_status_updated');
            });
        }

        // 5. Payments table performance indexes
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (Schema::hasColumn('payments', 'payment_date')) {
                    $table->index('payment_date', 'idx_payments_payment_date');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropIndex('idx_invoices_status');
                $table->dropIndex('idx_invoices_due_date');
                $table->dropIndex('idx_invoices_status_due');
                $table->dropIndex('idx_invoices_cust_status');
                if (Schema::hasColumn('invoices', 'reseller_id')) {
                    $table->dropIndex('idx_invoices_reseller_status');
                }
            });
        }

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropIndex('idx_customers_status');
                if (Schema::hasColumn('customers', 'phone')) {
                    $table->dropIndex('idx_customers_phone');
                }
                if (Schema::hasColumn('customers', 'reseller_id')) {
                    $table->dropIndex('idx_customers_reseller_status');
                }
            });
        }

        if (Schema::hasTable('customer_services')) {
            Schema::table('customer_services', function (Blueprint $table) {
                $table->dropIndex('idx_cust_services_cust_status');
                if (Schema::hasColumn('customer_services', 'service_profile_id')) {
                    $table->dropIndex('idx_cust_services_profile');
                }
            });
        }

        if (Schema::hasTable('vouchers')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropIndex('idx_vouchers_status_updated');
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (Schema::hasColumn('payments', 'payment_date')) {
                    $table->dropIndex('idx_payments_payment_date');
                }
            });
        }
    }
};
