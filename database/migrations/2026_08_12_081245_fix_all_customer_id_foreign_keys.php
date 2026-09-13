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
        $tables = ['customer_services', 'invoices', 'payments', 'subscriptions', 'tickets'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Ignore errors if the key doesn't exist
                try {
                    $table->dropForeign(['customer_id']);
                } catch (\Exception $e) {}
            });
            
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if ($tableName === 'tickets') {
                    $table->foreign('customer_id')->references('id')->on('members')->onDelete('set null');
                } else {
                    $table->foreign('customer_id')->references('id')->on('members')->onDelete('cascade');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = ['customer_services', 'invoices', 'payments', 'subscriptions', 'tickets'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                try {
                    $table->dropForeign(['customer_id']);
                } catch (\Exception $e) {}
            });
            
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if ($tableName === 'tickets') {
                    $table->foreign('customer_id')->references('id')->on('users')->onDelete('set null');
                } else {
                    $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
                }
            });
        }
    }
};
