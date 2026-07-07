<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_profiles', function (Blueprint $table) {
            $existingColumns = Schema::getColumnListing('service_profiles');
            
            if (!in_array('package_type', $existingColumns)) {
                // Just add it without after() to be safe, or after a column that exists
                $table->string('package_type')->default('unlimited');
            }
            
            if (!in_array('duration_value', $existingColumns)) {
                $table->unsignedInteger('duration_value')->nullable();
            }
            
            if (!in_array('duration_unit', $existingColumns)) {
                $table->string('duration_unit')->default('hours');
            }
            
            if (!in_array('quota_value', $existingColumns)) {
                $table->unsignedInteger('quota_value')->nullable();
            }
            
            if (!in_array('quota_unit', $existingColumns)) {
                $table->string('quota_unit')->default('GB');
            }
            
            if (!in_array('owner_price', $existingColumns)) {
                $table->decimal('owner_price', 15, 2)->nullable();
            }
            
            if (!in_array('reseller_price', $existingColumns)) {
                $table->decimal('reseller_price', 15, 2)->nullable();
            }
            
            if (!in_array('is_free', $existingColumns)) {
                $table->boolean('is_free')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_profiles', function (Blueprint $table) {
            $existingColumns = Schema::getColumnListing('service_profiles');
            
            $columnsToDrop = [
                'package_type',
                'duration_value',
                'duration_unit',
                'quota_value',
                'quota_unit',
                'owner_price',
                'reseller_price',
                'is_free',
            ];
            
            foreach ($columnsToDrop as $column) {
                if (in_array($column, $existingColumns)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
