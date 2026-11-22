<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan item_id ke tabel odcs
        if (Schema::hasTable('odcs') && !Schema::hasColumn('odcs', 'item_id')) {
            Schema::table('odcs', function (Blueprint $table) {
                $table->unsignedBigInteger('item_id')->nullable()->index();
            });
        }

        // 2. Tambahkan item_id ke tabel odps
        if (Schema::hasTable('odps') && !Schema::hasColumn('odps', 'item_id')) {
            Schema::table('odps', function (Blueprint $table) {
                $table->unsignedBigInteger('item_id')->nullable()->index();
            });
        }

        // 3. Tambahkan item_id ke tabel joint_closures
        if (Schema::hasTable('joint_closures') && !Schema::hasColumn('joint_closures', 'item_id')) {
            Schema::table('joint_closures', function (Blueprint $table) {
                $table->unsignedBigInteger('item_id')->nullable()->index();
            });
        }

        // 4. Tambahkan item_id ke tabel splitters
        if (Schema::hasTable('splitters') && !Schema::hasColumn('splitters', 'item_id')) {
            Schema::table('splitters', function (Blueprint $table) {
                $table->unsignedBigInteger('item_id')->nullable()->index();
            });
        }

        // 5. Tambahkan kolom pendukung di inventory_usages jika belum ada
        if (Schema::hasTable('inventory_usages')) {
            Schema::table('inventory_usages', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_usages', 'odc_id')) {
                    $table->unsignedBigInteger('odc_id')->nullable()->index();
                }
                if (!Schema::hasColumn('inventory_usages', 'odp_id')) {
                    $table->unsignedBigInteger('odp_id')->nullable()->index();
                }
                if (!Schema::hasColumn('inventory_usages', 'joint_closure_id')) {
                    $table->unsignedBigInteger('joint_closure_id')->nullable()->index();
                }
                if (!Schema::hasColumn('inventory_usages', 'splitter_id')) {
                    $table->unsignedBigInteger('splitter_id')->nullable()->index();
                }
            });
        }

        // 6. Seed Master Barang Standar Infrastruktur Jaringan (ODC, ODP, Closure, Splitter)
        if (Schema::hasTable('items')) {
            $defaultNetworkItems = [
                [
                    'sku' => 'BOX-ODC-24',
                    'name' => 'Box ODC Kabinet 24 Port Outdoor',
                    'category' => 'ODC',
                    'unit' => 'unit',
                    'brand' => 'Fiberhome',
                    'model' => 'ODC-24P-OUTDOOR',
                    'description' => 'Kabinet distribusi optik luar ruangan 24 port',
                    'min_stock' => 2,
                    'cost_price' => 1250000,
                    'selling_price' => 1500000,
                ],
                [
                    'sku' => 'BOX-ODC-48',
                    'name' => 'Box ODC Kabinet 48 Port Outdoor',
                    'category' => 'ODC',
                    'unit' => 'unit',
                    'brand' => 'Fiberhome',
                    'model' => 'ODC-48P-OUTDOOR',
                    'description' => 'Kabinet distribusi optik luar ruangan 48 port',
                    'min_stock' => 1,
                    'cost_price' => 1850000,
                    'selling_price' => 2200000,
                ],
                [
                    'sku' => 'BOX-ODP-8',
                    'name' => 'Box ODP 8 Port Outdoor (IP65)',
                    'category' => 'ODP',
                    'unit' => 'unit',
                    'brand' => 'ZTE',
                    'model' => 'ODP-8P-SOLID',
                    'description' => 'Kotak distribusi tiang 8 port dropcore',
                    'min_stock' => 5,
                    'cost_price' => 95000,
                    'selling_price' => 135000,
                ],
                [
                    'sku' => 'BOX-ODP-16',
                    'name' => 'Box ODP 16 Port Outdoor (IP65)',
                    'category' => 'ODP',
                    'unit' => 'unit',
                    'brand' => 'ZTE',
                    'model' => 'ODP-16P-SOLID',
                    'description' => 'Kotak distribusi tiang 16 port dropcore',
                    'min_stock' => 5,
                    'cost_price' => 140000,
                    'selling_price' => 185000,
                ],
                [
                    'sku' => 'BOX-JC-DOME-24',
                    'name' => 'Joint Closure Dome 24 Core Vertikal',
                    'category' => 'Joint Closure',
                    'unit' => 'unit',
                    'brand' => 'Universal',
                    'model' => 'JC-DOME-24C',
                    'description' => 'Kubah sambungan kabel optik tiang 24 core',
                    'min_stock' => 3,
                    'cost_price' => 120000,
                    'selling_price' => 165000,
                ],
                [
                    'sku' => 'BOX-JC-INLINE-24',
                    'name' => 'Joint Closure Inline 24 Core Horizontal',
                    'category' => 'Joint Closure',
                    'unit' => 'unit',
                    'brand' => 'Universal',
                    'model' => 'JC-INLINE-24C',
                    'description' => 'Kotak sambungan jalur kabel udara 24 core',
                    'min_stock' => 3,
                    'cost_price' => 110000,
                    'selling_price' => 150000,
                ],
                [
                    'sku' => 'SPL-PLC-1-8',
                    'name' => 'Splitter PLC 1:8 Cassette Box SC/UPC',
                    'category' => 'Splitter',
                    'unit' => 'unit',
                    'brand' => 'FOC',
                    'model' => 'PLC-1x8-SC/UPC',
                    'description' => 'Splitter modular 1 ke 8 redaman 10.5 dB',
                    'min_stock' => 10,
                    'cost_price' => 45000,
                    'selling_price' => 65000,
                ],
                [
                    'sku' => 'SPL-PLC-1-4',
                    'name' => 'Splitter PLC 1:4 Cassette Box SC/UPC',
                    'category' => 'Splitter',
                    'unit' => 'unit',
                    'brand' => 'FOC',
                    'model' => 'PLC-1x4-SC/UPC',
                    'description' => 'Splitter modular 1 ke 4 redaman 7.2 dB',
                    'min_stock' => 5,
                    'cost_price' => 38000,
                    'selling_price' => 55000,
                ],
                [
                    'sku' => 'SPL-PLC-1-16',
                    'name' => 'Splitter PLC 1:16 Cassette Box SC/UPC',
                    'category' => 'Splitter',
                    'unit' => 'unit',
                    'brand' => 'FOC',
                    'model' => 'PLC-1x16-SC/UPC',
                    'description' => 'Splitter modular 1 ke 16 redaman 13.7 dB',
                    'min_stock' => 5,
                    'cost_price' => 85000,
                    'selling_price' => 120000,
                ],
                [
                    'sku' => 'SPL-FBT-1-2-50',
                    'name' => 'Splitter Ratio 1:2 (50:50) SC/UPC',
                    'category' => 'Splitter',
                    'unit' => 'unit',
                    'brand' => 'FOC',
                    'model' => 'FBT-1x2-50:50',
                    'description' => 'Splitter rasio 50:50 redaman 3.4 dB',
                    'min_stock' => 5,
                    'cost_price' => 30000,
                    'selling_price' => 45000,
                ],
            ];

            foreach ($defaultNetworkItems as $itemData) {
                if (!DB::table('items')->where('sku', $itemData['sku'])->exists()) {
                    DB::table('items')->insert(array_merge($itemData, [
                        'uuid' => (string) Str::uuid(),
                        'is_serialized' => false,
                        'is_reusable' => false,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive
    }
};
