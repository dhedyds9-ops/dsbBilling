<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_profiles', function (Blueprint $table) {
            $table->string('pppoe_profile')->nullable()->after('mikrotik_profile');
            $table->string('hotspot_profile')->nullable()->after('pppoe_profile');
            $table->string('radius_group')->nullable()->after('hotspot_profile');
            $table->string('user_profile')->nullable()->after('radius_group');
            $table->string('fup_policy')->nullable()->after('user_profile');
            $table->decimal('tax_percentage', 5, 2)->default(11.00)->after('price'); // PPN
            $table->foreignId('vlan_id')->nullable()->after('subnet')->constrained('vlans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_profiles', function (Blueprint $table) {
            $table->dropForeign(['vlan_id']);
            $table->dropColumn([
                'pppoe_profile',
                'hotspot_profile',
                'radius_group',
                'user_profile',
                'fup_policy',
                'tax_percentage',
                'vlan_id'
            ]);
        });
    }
};
