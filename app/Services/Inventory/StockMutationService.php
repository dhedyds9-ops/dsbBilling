<?php

namespace App\Services\Inventory;

use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\InventoryStockMutation;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemSerial;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Facades\DB;

class StockMutationService
{
    /**
     * Catat mutasi Barang Masuk (Inbound / Pengadaan / Saldo Awal)
     */
    public function recordInbound(
        int $warehouseId,
        int $itemId,
        float $quantity,
        string $referenceType,
        ?int $referenceId = null,
        ?string $notes = null,
        array $serials = [],
        ?int $actorId = null
    ): InventoryStockMutation {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Kuantitas barang masuk harus lebih besar dari 0.');
        }

        return DB::transaction(function () use (
            $warehouseId,
            $itemId,
            $quantity,
            $referenceType,
            $referenceId,
            $notes,
            $serials,
            $actorId
        ) {
            $wh = Warehouse::withoutGlobalScopes()->findOrFail($warehouseId);
            $item = Item::withoutGlobalScopes()->findOrFail($itemId);

            // Kunci baris saldo stok dengan lockForUpdate
            $stock = InventoryStock::withoutGlobalScopes()
                ->where('warehouse_id', $warehouseId)
                ->where('item_id', $itemId)
                ->lockForUpdate()
                ->first();

            $balanceBefore = $stock ? (float) $stock->quantity : 0.0;
            $balanceAfter = $balanceBefore + $quantity;

            if ($stock) {
                $stock->update([
                    'quantity' => $balanceAfter,
                    'updated_at' => now(),
                ]);
            } else {
                $stock = InventoryStock::create([
                    'warehouse_id' => $warehouseId,
                    'item_id' => $itemId,
                    'quantity' => $balanceAfter,
                    'reserved_quantity' => 0,
                    'branch_id' => $wh->branch_id,
                ]);
            }

            // Catat ledger mutasi stok (immutable)
            $mutation = InventoryStockMutation::create([
                'warehouse_id' => $warehouseId,
                'item_id' => $itemId,
                'type' => 'inbound',
                'qty_in' => $quantity,
                'qty_out' => 0,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes ?: 'Penerimaan barang masuk ke gudang',
                'actor_id' => $actorId ?: auth()->id(),
                'branch_id' => $wh->branch_id,
                'created_at' => now(),
            ]);

            // Jika barang berserial, daftarkan serial numbers
            if ($item->is_serialized && !empty($serials)) {
                foreach ($serials as $sn) {
                    $snClean = trim((string) $sn);
                    if (empty($snClean)) continue;

                    ItemSerial::updateOrCreate(
                        [
                            'item_id' => $itemId,
                            'serial_number' => $snClean,
                        ],
                        [
                            'warehouse_id' => $warehouseId,
                            'custodian_id' => null,
                            'custodian_type' => null,
                            'customer_id' => null,
                            'installation_id' => null,
                            'ticket_id' => null,
                            'status' => 'in_warehouse',
                            'condition' => 'good',
                            'branch_id' => $wh->branch_id,
                            'notes' => 'Penerimaan via ' . $mutation->mutation_number,
                            'updated_by' => $actorId ?: auth()->id(),
                        ]
                    );
                }
            }

            return $mutation;
        });
    }

    /**
     * Catat mutasi Barang Keluar (Dispatch / Pengeluaran ke Reseller/Teknisi)
     */
    public function recordOutbound(
        int $warehouseId,
        int $itemId,
        float $quantity,
        string $referenceType,
        ?int $referenceId = null,
        ?string $notes = null,
        array $serialIds = [],
        ?int $actorId = null
    ): InventoryStockMutation {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Kuantitas barang keluar harus lebih besar dari 0.');
        }

        return DB::transaction(function () use (
            $warehouseId,
            $itemId,
            $quantity,
            $referenceType,
            $referenceId,
            $notes,
            $serialIds,
            $actorId
        ) {
            $wh = Warehouse::withoutGlobalScopes()->findOrFail($warehouseId);
            $item = Item::withoutGlobalScopes()->findOrFail($itemId);

            // Kunci baris saldo stok dengan lockForUpdate
            $stock = InventoryStock::withoutGlobalScopes()
                ->where('warehouse_id', $warehouseId)
                ->where('item_id', $itemId)
                ->lockForUpdate()
                ->first();

            $balanceBefore = $stock ? (float) $stock->quantity : 0.0;

            // Cegah stok negatif!
            if ($balanceBefore < $quantity) {
                throw new \RuntimeException("Stok tidak mencukupi di gudang {$wh->name}. Tersedia: {$balanceBefore} {$item->unit}, dibutuhkan: {$quantity} {$item->unit}.");
            }

            $balanceAfter = $balanceBefore - $quantity;

            $stock->update([
                'quantity' => $balanceAfter,
                'updated_at' => now(),
            ]);

            // Catat ledger mutasi stok
            $mutation = InventoryStockMutation::create([
                'warehouse_id' => $warehouseId,
                'item_id' => $itemId,
                'type' => 'outbound_dispatch',
                'qty_in' => 0,
                'qty_out' => $quantity,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes ?: 'Pengeluaran barang dari gudang',
                'actor_id' => $actorId ?: auth()->id(),
                'branch_id' => $wh->branch_id,
                'created_at' => now(),
            ]);

            return $mutation;
        });
    }

    /**
     * Catat Penyesuaian Stok (Stock Adjustment / Opname)
     */
    public function recordAdjustment(
        int $warehouseId,
        int $itemId,
        float $physicalQty,
        string $reason,
        ?int $referenceId = null,
        ?int $actorId = null
    ): InventoryStockMutation {
        return DB::transaction(function () use (
            $warehouseId,
            $itemId,
            $physicalQty,
            $reason,
            $referenceId,
            $actorId
        ) {
            $wh = Warehouse::withoutGlobalScopes()->findOrFail($warehouseId);
            $item = Item::withoutGlobalScopes()->findOrFail($itemId);

            $stock = InventoryStock::withoutGlobalScopes()
                ->where('warehouse_id', $warehouseId)
                ->where('item_id', $itemId)
                ->lockForUpdate()
                ->first();

            $balanceBefore = $stock ? (float) $stock->quantity : 0.0;
            $diff = $physicalQty - $balanceBefore;

            if ($diff == 0) {
                throw new \InvalidArgumentException('Tidak ada selisih stok fisik dengan sistem.');
            }

            $type = $diff > 0 ? 'adjustment_in' : 'adjustment_out';
            $qtyIn = $diff > 0 ? abs($diff) : 0;
            $qtyOut = $diff < 0 ? abs($diff) : 0;

            if ($stock) {
                $stock->update([
                    'quantity' => $physicalQty,
                    'updated_at' => now(),
                ]);
            } else {
                $stock = InventoryStock::create([
                    'warehouse_id' => $warehouseId,
                    'item_id' => $itemId,
                    'quantity' => $physicalQty,
                    'reserved_quantity' => 0,
                    'branch_id' => $wh->branch_id,
                ]);
            }

            return InventoryStockMutation::create([
                'warehouse_id' => $warehouseId,
                'item_id' => $itemId,
                'type' => $type,
                'qty_in' => $qtyIn,
                'qty_out' => $qtyOut,
                'balance_before' => $balanceBefore,
                'balance_after' => $physicalQty,
                'reference_type' => 'StockOpname',
                'reference_id' => $referenceId,
                'notes' => 'Penyesuaian stok opname: ' . $reason,
                'actor_id' => $actorId ?: auth()->id(),
                'branch_id' => $wh->branch_id,
                'created_at' => now(),
            ]);
        });
    }
}
