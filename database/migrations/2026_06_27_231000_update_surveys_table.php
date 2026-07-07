<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->foreignId('splitter_id')->nullable()->after('odp_id')->constrained()->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable()->after('status');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->text('address')->nullable()->after('longitude');
            $table->string('port')->nullable()->after('address');
            $table->decimal('distance', 10, 2)->nullable()->after('port');
            $table->decimal('cable_estimation', 10, 2)->nullable()->after('distance');
            $table->json('material_estimation')->nullable()->after('cable_estimation');
            $table->json('photos_location')->nullable()->after('material_estimation');
            $table->json('photos_odp')->nullable()->after('photos_location');
            $table->dropColumn('photos');
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropForeign(['splitter_id']);
            $table->dropColumn([
                'splitter_id',
                'latitude',
                'longitude',
                'address',
                'port',
                'distance',
                'cable_estimation',
                'material_estimation',
                'photos_location',
                'photos_odp',
            ]);
            $table->json('photos')->nullable();
        });
    }
};
