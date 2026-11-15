<?php

namespace App\Console\Commands\Inventory;

use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemSerial;
use App\Models\Inventory\Warehouse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncSerializedStockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:sync-serialized-stocks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi saldo stok (inventory_stocks) dengan jumlah fisik serial aktif (status: in_warehouse) untuk barang berserial';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai rekonsiliasi stok barang berserial...');

        // Ambil semua serial yang statusnya 'in_warehouse' dan memiliki warehouse_id
        $serialsGrouped = ItemSerial::withoutGlobalScopes()
            ->select('warehouse_id', 'item_id', DB::raw('count(*) as total_serials'))
            ->whereNotNull('warehouse_id')
            ->where('status', 'in_warehouse')
            ->groupBy('warehouse_id', 'item_id')
            ->get();

        $syncedCount = 0;

        foreach ($serialsGrouped as $group) {
            $warehouseId = $group->warehouse_id;
            $itemId = $group->item_id;
            $physicalCount = (float) $group->total_serials;

            $item = Item::withoutGlobalScopes()->find($itemId);
            $warehouse = Warehouse::withoutGlobalScopes()->find($warehouseId);

            if (!$item || !$warehouse) {
                continue;
            }

            $stock = InventoryStock::withoutGlobalScopes()
                ->where('warehouse_id', $warehouseId)
                ->where('item_id', $itemId)
                ->first();

            $currentStockQty = $stock ? (float) $stock->quantity : 0.0;

            if ($currentStockQty < $physicalCount) {
                if ($stock) {
                    $stock->update(['quantity' => $physicalCount]);
                } else {
                    InventoryStock::create([
                        'warehouse_id' => $warehouseId,
                        'item_id' => $itemId,
                        'quantity' => $physicalCount,
                        'reserved_quantity' => 0,
                        'branch_id' => $warehouse->branch_id,
                    ]);
                }

                $this->line("<fg=green>✓ Rekonsiliasi Sukses:</fg=green> {$item->name} di {$warehouse->name} (Saldo: {$currentStockQty} -> {$physicalCount} unit)");
                $syncedCount++;
            }
        }

        if ($syncedCount === 0) {
            $this->info('Semua saldo stok barang berserial sudah sinkron dan akurat.');
        } else {
            $this->info("Total {$syncedCount} item berhasil disinkronkan dengan jumlah fisik Serial Number.");
        }

        return Command::SUCCESS;
    }
}
