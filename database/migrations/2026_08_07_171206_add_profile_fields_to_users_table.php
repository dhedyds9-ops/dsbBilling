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
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->string('identity_number')->nullable()->after('avatar');
            $table->text('address')->nullable()->after('wilayah');
            $table->decimal('balance', 15, 2)->default(0)->after('is_active');
            $table->boolean('is_balance_active')->default(true)->after('balance');
            $table->boolean('is_topup_enabled')->default(false)->after('is_balance_active');
            $table->text('notes')->nullable()->after('is_topup_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar',
                'identity_number',
                'address',
                'balance',
                'is_balance_active',
                'is_topup_enabled',
                'notes'
            ]);
        });
    }
};
