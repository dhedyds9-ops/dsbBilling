<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            // Add fields from MixRadius
            if (!Schema::hasColumn('vouchers', 'type')) {
                $table->string('type')->default('hotspot'); // hotspot, pppoe
            }
            if (!Schema::hasColumn('vouchers', 'nas_device_id')) {
                $table->foreignId('nas_device_id')->nullable()->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('vouchers', 'owner_id')) {
                $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('vouchers', 'bind_on_login')) {
                $table->boolean('bind_on_login')->default(false);
            }
            if (!Schema::hasColumn('vouchers', 'fee_seller')) {
                $table->decimal('fee_seller', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('vouchers', 'login_method')) {
                $table->string('login_method')->default('voucher_code'); // voucher_code, username_password
            }
            if (!Schema::hasColumn('vouchers', 'code_combination')) {
                $table->string('code_combination')->default('uppercase_alphanumeric'); // uppercase, lowercase, alphanumeric, numbers
            }
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'nas_device_id',
                'customer_id',
                'bind_on_login',
                'fee_seller',
                'login_method',
                'code_combination',
            ]);
        });
    }
};
