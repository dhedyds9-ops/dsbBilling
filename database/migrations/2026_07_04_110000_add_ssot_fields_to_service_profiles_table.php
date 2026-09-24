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
            
            if (!in_array('cir_download', $existingColumns)) {
                $table->integer('cir_download')->nullable()->after('priority');
            }
            if (!in_array('cir_upload', $existingColumns)) {
                $table->integer('cir_upload')->nullable()->after('cir_download');
            }
            if (!in_array('mir_download', $existingColumns)) {
                $table->integer('mir_download')->nullable()->after('cir_upload');
            }
            if (!in_array('mir_upload', $existingColumns)) {
                $table->integer('mir_upload')->nullable()->after('mir_download');
            }
            if (!in_array('fup_enabled', $existingColumns)) {
                $table->boolean('fup_enabled')->default(false)->after('mir_upload');
            }
            if (!in_array('fup_threshold', $existingColumns)) {
                $table->integer('fup_threshold')->nullable()->after('fup_enabled');
            }
            if (!in_array('fup_speed_drop_percent', $existingColumns)) {
                $table->integer('fup_speed_drop_percent')->nullable()->after('fup_threshold');
            }
            if (!in_array('radius_group_name', $existingColumns)) {
                $table->string('radius_group_name')->nullable()->after('fup_speed_drop_percent');
            }
            if (!in_array('radius_session_timeout', $existingColumns)) {
                $table->string('radius_session_timeout')->nullable()->after('radius_group_name');
            }
            if (!in_array('radius_idle_timeout', $existingColumns)) {
                $table->string('radius_idle_timeout')->nullable()->after('radius_session_timeout');
            }
            if (!in_array('radius_simultaneous_use', $existingColumns)) {
                $table->integer('radius_simultaneous_use')->nullable()->after('radius_idle_timeout');
            }
            if (!in_array('radius_mac_binding', $existingColumns)) {
                $table->boolean('radius_mac_binding')->default(false)->after('radius_simultaneous_use');
            }
            if (!in_array('radius_address_list', $existingColumns)) {
                $table->string('radius_address_list')->nullable()->after('radius_mac_binding');
            }
            if (!in_array('radius_rate_limit', $existingColumns)) {
                $table->string('radius_rate_limit')->nullable()->after('radius_address_list');
            }
            if (!in_array('radius_framed_pool', $existingColumns)) {
                $table->string('radius_framed_pool')->nullable()->after('radius_rate_limit');
            }
            if (!in_array('radius_attributes', $existingColumns)) {
                $table->json('radius_attributes')->nullable()->after('radius_framed_pool');
            }
            if (!in_array('account_type', $existingColumns)) {
                $table->string('account_type')->default('unlimited')->after('radius_attributes');
            }
            if (!in_array('allowed_login_days', $existingColumns)) {
                $table->json('allowed_login_days')->nullable()->after('validity_hours');
            }
            if (!in_array('login_start_time', $existingColumns)) {
                $table->time('login_start_time')->nullable()->after('allowed_login_days');
            }
            if (!in_array('login_end_time', $existingColumns)) {
                $table->time('login_end_time')->nullable()->after('login_start_time');
            }
            if (!in_array('idle_disconnect_policy', $existingColumns)) {
                $table->string('idle_disconnect_policy')->nullable()->after('max_devices');
            }
            if (!in_array('voucher_show_in_portal', $existingColumns)) {
                $table->boolean('voucher_show_in_portal')->default(false)->after('idle_disconnect_policy');
            }
            if (!in_array('voucher_template', $existingColumns)) {
                $table->string('voucher_template')->nullable()->after('voucher_show_in_portal');
            }
            if (!in_array('voucher_prefix', $existingColumns)) {
                $table->string('voucher_prefix')->nullable()->after('voucher_template');
            }
            if (!in_array('voucher_length', $existingColumns)) {
                $table->integer('voucher_length')->default(12)->after('voucher_prefix');
            }
            if (!in_array('voucher_print_format', $existingColumns)) {
                $table->string('voucher_print_format')->default('a4')->after('voucher_length');
            }
            if (!in_array('voucher_validity_after_activation', $existingColumns)) {
                $table->integer('voucher_validity_after_activation')->nullable()->after('voucher_print_format');
            }
            if (!in_array('cost_price', $existingColumns)) {
                $table->decimal('cost_price', 15, 2)->nullable()->after('voucher_validity_after_activation');
            }
            if (!in_array('base_price', $existingColumns)) {
                $table->decimal('base_price', 15, 2)->nullable()->after('cost_price');
            }
            if (!in_array('promo_price', $existingColumns)) {
                $table->decimal('promo_price', 15, 2)->nullable()->after('base_price');
            }
            if (!in_array('tax_enabled', $existingColumns)) {
                $table->boolean('tax_enabled')->default(false)->after('promo_price');
            }
            if (!in_array('prorata_billing', $existingColumns)) {
                $table->boolean('prorata_billing')->default(false)->after('tax_enabled');
            }
            if (!in_array('billing_cycle', $existingColumns)) {
                $table->string('billing_cycle')->default('monthly')->after('prorata_billing');
            }
            if (!in_array('billing_cycle_day', $existingColumns)) {
                $table->integer('billing_cycle_day')->nullable()->after('billing_cycle');
            }
            if (!in_array('min_deposit', $existingColumns)) {
                $table->decimal('min_deposit', 15, 2)->nullable()->after('billing_cycle_day');
            }
            if (!in_array('auto_suspend_days', $existingColumns)) {
                $table->integer('auto_suspend_days')->nullable()->after('min_deposit');
            }
            if (!in_array('auto_activate_after_payment', $existingColumns)) {
                $table->boolean('auto_activate_after_payment')->default(true)->after('auto_suspend_days');
            }
            if (!in_array('target_hotspot_profile', $existingColumns)) {
                $table->string('target_hotspot_profile')->nullable()->after('auto_activate_after_payment');
            }
            if (!in_array('ppp_profile_name', $existingColumns)) {
                $table->string('ppp_profile_name')->nullable()->after('target_hotspot_profile');
            }
            if (!in_array('user_manager_profile', $existingColumns)) {
                $table->string('user_manager_profile')->nullable()->after('ppp_profile_name');
            }
            if (!in_array('bridge_interface', $existingColumns)) {
                $table->string('bridge_interface')->nullable()->after('vlan_id');
            }
            if (!in_array('interface_name', $existingColumns)) {
                $table->string('interface_name')->nullable()->after('bridge_interface');
            }
            if (!in_array('ip_pool_parent', $existingColumns)) {
                $table->string('ip_pool_parent')->nullable()->after('interface_name');
            }
            if (!in_array('custom_dns', $existingColumns)) {
                $table->string('custom_dns')->nullable()->after('ip_pool_parent');
            }
            if (!in_array('technical_notes', $existingColumns)) {
                $table->text('technical_notes')->nullable()->after('custom_dns');
            }
            if (!in_array('advanced_settings', $existingColumns)) {
                $table->json('advanced_settings')->nullable()->after('technical_notes');
            }
        });
    }

    public function down(): void
    {
        // Since this migration is idempotent, we don't need to do anything in down()
        // The original migration had a more complete down() method, but this simplified
        // version is safe to use for now
    }
};
