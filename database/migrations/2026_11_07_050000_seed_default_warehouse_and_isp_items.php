<?php

use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\InventoryStockMutation;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Models\Master\Branch;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('warehouses') || !Schema::hasTable('items') || !Schema::hasTable('inventory_stocks')) {
            return;
        }

        // 1. Pastikan minimal ada 1 Gudang Utama (Kantor Pusat)
        $warehouse = Warehouse::first();
        if (!$warehouse) {
            $defaultBranch = Branch::first();
            $warehouse = Warehouse::create([
                'uuid' => (string) Str::uuid(),
                'code' => 'GUD-001',
                'name' => 'Gudang Pusat (Kantor Utama)',
                'type' => 'main',
                'branch_id' => $defaultBranch?->id,
                'address' => 'Kantor Pusat Operasional ISP',
                'city' => 'Pusat',
                'province' => 'Jawa Barat',
                'phone' => '081234567890',
                'email' => 'gudang@isp.net',
                'is_active' => true,
                'allows_returns' => true,
                'capacity' => 10000,
            ]);
        }

        // 2. Daftar 8 Master Barang Standar ISP
        $defaultItems = [
            [
                'sku' => 'MAT-CLAMP-S',
                'name' => 'S-Clamp / Klem Tarik Kabel Dropcore',
                'category' => 'Aksesoris',
                'unit' => 'pcs',
                'brand' => 'Universal',
                'model' => 'S-Hook Metal',
                'min_stock' => 30,
                'is_serialized' => false,
                'is_reusable' => false,
                'cost_price' => 3500,
                'selling_price' => 5000,
                'initial_stock' => 150,
            ],
            [
                'sku' => 'MAT-DC-1C',
                'name' => 'Kabel Dropcore Optik 1 Core (1000m)',
                'category' => 'Kabel',
                'unit' => 'meter',
                'brand' => 'Fiberhome',
                'model' => 'G657A1 1-Core LSZH',
                'min_stock' => 150,
                'is_serialized' => false,
                'is_reusable' => false,
                'cost_price' => 850,
                'selling_price' => 1200,
                'initial_stock' => 1000,
            ],
            [
                'sku' => 'MAT-PC-SC-1.5M',
                'name' => 'Patchcord Fiber SC/UPC - SC/UPC 1.5m',
                'category' => 'Kabel',
                'unit' => 'pcs',
                'brand' => 'FOC',
                'model' => 'Simplex Single Mode 1.5m',
                'min_stock' => 5,
                'is_serialized' => false,
                'is_reusable' => false,
                'cost_price' => 12000,
                'selling_price' => 18000,
                'initial_stock' => 30,
            ],
            [
                'sku' => 'MAT-ADAPT-SC',
                'name' => 'Adapter Fiber SC/UPC Simplex',
                'category' => 'Konektor',
                'unit' => 'pcs',
                'brand' => 'FOC',
                'model' => 'SC-SC Simplex Blue',
                'min_stock' => 10,
                'is_serialized' => false,
                'is_reusable' => false,
                'cost_price' => 4000,
                'selling_price' => 7000,
                'initial_stock' => 50,
            ],
            [
                'sku' => 'MAT-FC-SC',
                'name' => 'Fast Connector SC/UPC',
                'category' => 'Konektor',
                'unit' => 'pcs',
                'brand' => 'Ilsintech',
                'model' => 'Quick Assembly SC/UPC',
                'min_stock' => 20,
                'is_serialized' => false,
                'is_reusable' => false,
                'cost_price' => 6500,
                'selling_price' => 10000,
                'initial_stock' => 100,
            ],
            [
                'sku' => 'MAT-ROSET-1C',
                'name' => 'Roset Optik 1 Core / OTB Mini',
                'category' => 'Pasif',
                'unit' => 'pcs',
                'brand' => 'Universal',
                'model' => 'Indoor Mini Roset 1-Port',
                'min_stock' => 10,
                'is_serialized' => false,
                'is_reusable' => false,
                'cost_price' => 8000,
                'selling_price' => 15000,
                'initial_stock' => 50,
            ],
            [
                'sku' => 'DEV-ONT-ZTE-F609',
                'name' => 'ONT GPON ZTE F609 (Wi-Fi 2.4GHz)',
                'category' => 'ONU / ONT',
                'unit' => 'unit',
                'brand' => 'ZTE',
                'model' => 'F609 V3',
                'min_stock' => 2,
                'is_serialized' => true,
                'is_reusable' => true,
                'cost_price' => 145000,
                'selling_price' => 195000,
                'initial_stock' => 5,
            ],
            [
                'sku' => 'DEV-ONT-HW-8245H',
                'name' => 'ONT GPON Huawei HG8245H',
                'category' => 'ONU / ONT',
                'unit' => 'unit',
                'brand' => 'Huawei',
                'model' => 'EchoLife HG8245H',
                'min_stock' => 2,
                'is_serialized' => true,
                'is_reusable' => true,
                'cost_price' => 155000,
                'selling_price' => 210000,
                'initial_stock' => 5,
            ],
        ];

        foreach ($defaultItems as $d) {
            $initialStock = $d['initial_stock'];
            unset($d['initial_stock']);

            $item = Item::firstOrCreate(
                ['sku' => $d['sku']],
                array_merge($d, [
                    'uuid' => (string) Str::uuid(),
                    'is_active' => true,
                    'branch_id' => $warehouse->branch_id,
                ])
            );

            // 3. Catat saldo fisik di inventory_stocks
            $stock = InventoryStock::firstOrCreate(
                ['warehouse_id' => $warehouse->id, 'item_id' => $item->id],
                [
                    'quantity' => $initialStock,
                    'reserved_quantity' => 0,
                    'branch_id' => $warehouse->branch_id,
                ]
            );

            // 4. Catat mutasi ledger saldo awal jika belum ada mutasi
            $hasMutation = InventoryStockMutation::where('warehouse_id', $warehouse->id)
                ->where('item_id', $item->id)
                ->exists();

            if (!$hasMutation) {
                InventoryStockMutation::create([
                    'uuid' => (string) Str::uuid(),
                    'mutation_number' => InventoryStockMutation::generateMutationNumber(),
                    'warehouse_id' => $warehouse->id,
                    'item_id' => $item->id,
                    'type' => 'inbound',
                    'qty_in' => $initialStock,
                    'qty_out' => 0,
                    'balance_before' => 0,
                    'balance_after' => $initialStock,
                    'reference_type' => 'initial_stock',
                    'reference_id' => $warehouse->id,
                    'notes' => 'Saldo Awal Fisik Gudang Pusat Operasional',
                    'branch_id' => $warehouse->branch_id,
                    'created_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Tetap simpan data jika rollback partial
    }
};
