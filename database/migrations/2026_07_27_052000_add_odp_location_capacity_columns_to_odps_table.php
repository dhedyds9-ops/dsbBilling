<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('odps', function (Blueprint $table) {
            $table->unsignedBigInteger('olt_id')->nullable()->after('odc_id');
            $table->unsignedBigInteger('pon_port_id')->nullable()->after('olt_id');
            $table->unsignedBigInteger('splitter_id')->nullable()->after('pon_port_id');
            $table->unsignedBigInteger('parent_odp_id')->nullable()->after('splitter_id');
            $table->string('province', 50)->nullable()->after('address');
            $table->string('regency', 50)->nullable()->after('province');
            $table->string('district', 50)->nullable()->after('regency');
            $table->string('village', 50)->nullable()->after('district');
            $table->string('postal_code', 10)->nullable()->after('village');
            $table->unsignedSmallInteger('split_ratio')->nullable()->default(16)->after('longitude');
            $table->unsignedSmallInteger('used_port_count')->nullable()->default(0)->after('port_count');
            $table->unsignedSmallInteger('reserved_port_count')->nullable()->default(0)->after('used_port_count');
            $table->date('installation_date')->nullable()->after('status');
            $table->timestamp('last_maintenance_at')->nullable()->after('installation_date');
            $table->json('attributes')->nullable()->after('last_maintenance_at');

            $table->index(['olt_id', 'status']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::table('odps', function (Blueprint $table) {
            $cols = ['olt_id','pon_port_id','splitter_id','parent_odp_id','province','regency','district','village','postal_code','split_ratio','used_port_count','reserved_port_count','installation_date','last_maintenance_at','attributes'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('odps', $c)) {
                    $table->dropColumn($c);
                }
            }
            $table->dropIndex(['latitude','longitude']);
        });
    }
};
