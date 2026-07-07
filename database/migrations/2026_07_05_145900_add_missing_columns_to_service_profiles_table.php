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
        Schema::table('service_profiles', function (Blueprint $table) {
            $existingColumns = Schema::getColumnListing('service_profiles');
            
            // Add service_type column first (this was missing)
            if (!in_array('service_type', $existingColumns)) {
                $table->string('service_type')->default('pppoe')->after('description');
            }
            
            // Add burst columns
            if (!in_array('burst_limit_download', $existingColumns)) {
                $table->integer('burst_limit_download')->nullable()->after('upload_speed');
            }
            if (!in_array('burst_limit_upload', $existingColumns)) {
                $table->integer('burst_limit_upload')->nullable()->after('burst_limit_download');
            }
            if (!in_array('burst_threshold_download', $existingColumns)) {
                $table->integer('burst_threshold_download')->nullable()->after('burst_limit_upload');
            }
            if (!in_array('burst_threshold_upload', $existingColumns)) {
                $table->integer('burst_threshold_upload')->nullable()->after('burst_threshold_download');
            }
            if (!in_array('burst_time_download', $existingColumns)) {
                $table->integer('burst_time_download')->nullable()->after('burst_threshold_upload');
            }
            if (!in_array('burst_time_upload', $existingColumns)) {
                $table->integer('burst_time_upload')->nullable()->after('burst_time_download');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_profiles', function (Blueprint $table) {
            $existingColumns = Schema::getColumnListing('service_profiles');
            
            if (in_array('service_type', $existingColumns)) {
                $table->dropColumn('service_type');
            }
            if (in_array('burst_limit_download', $existingColumns)) {
                $table->dropColumn('burst_limit_download');
            }
            if (in_array('burst_limit_upload', $existingColumns)) {
                $table->dropColumn('burst_limit_upload');
            }
            if (in_array('burst_threshold_download', $existingColumns)) {
                $table->dropColumn('burst_threshold_download');
            }
            if (in_array('burst_threshold_upload', $existingColumns)) {
                $table->dropColumn('burst_threshold_upload');
            }
            if (in_array('burst_time_download', $existingColumns)) {
                $table->dropColumn('burst_time_download');
            }
            if (in_array('burst_time_upload', $existingColumns)) {
                $table->dropColumn('burst_time_upload');
            }
        });
    }
};
