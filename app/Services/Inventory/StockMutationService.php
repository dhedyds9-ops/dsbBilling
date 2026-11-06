<?php

namespace App\Services\Inventory;

use App\Models\Inventory\InventoryCustody;
use App\Models\Inventory\InventoryCustodyTransfer;
use App\Models\Inventory\InventoryDispatch;
use App\Models\Inventory\InventoryDispatchItem;
use App\Models\Inventory\InventoryRequisition;
use App\Models\Inventory\InventoryRequisitionItem;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\InventoryStockMutation;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemSerial;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\InventoryUsage;
use App\Models\Inventory\InventoryUsageItem;
use App\Models\Inventory\InventoryReturn;
use App\Models\Inventory\InventoryReturnItem;
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
     * Catat Pengeluaran Barang (Dispatch) lengkap dengan pembuatan Custody Tracking
     */
    public function recordDispatch(
        InventoryDispatch $dispatch,
        array $itemsData,
        ?int $actorId = null
    ): void {
        DB::transaction(function () use ($dispatch, $itemsData, $actorId) {
            foreach ($itemsData as $row) {
                $itemId = (int) $row['item_id'];
                $quantity = (float) $row['quantity'];
                $serials = $row['serials'] ?? [];

                $item = Item::withoutGlobalScopes()->findOrFail($itemId);

                // 1. Kurangi stok fisik di gudang & catat ledger mutasi
                $this->recordOutbound(
                    warehouseId: $dispatch->warehouse_id,
                    itemId: $itemId,
                    quantity: $quantity,
                    referenceType: 'dispatch',
                    referenceId: $dispatch->id,
                    notes: "Pengeluaran No. {$dispatch->dispatch_number} ke {$dispatch->recipient->name} ({$dispatch->recipient_type})",
                    actorId: $actorId ?: auth()->id()
                );

                // 2. Simpan detail item dispatch
                InventoryDispatchItem::create([
                    'dispatch_id' => $dispatch->id,
                    'item_id' => $itemId,
                    'quantity' => $quantity,
                    'serials' => !empty($serials) ? $serials : null,
                    'notes' => $row['notes'] ?? null,
                ]);

                // 3. Status kepemilikan sementara (Custody)
                $custodyStatus = $dispatch->recipient_type === 'reseller'
                    ? 'in_custody_reseller'
                    : 'in_custody_technician';

                if ($item->is_serialized && !empty($serials)) {
                    foreach ($serials as $sn) {
                        $snClean = trim((string) $sn);
                        if (empty($snClean)) continue;

                        $serial = ItemSerial::where('item_id', $itemId)
                            ->where('serial_number', $snClean)
                            ->first();

                        if (!$serial) {
                            $serial = ItemSerial::create([
                                'item_id' => $itemId,
                                'serial_number' => $snClean,
                                'warehouse_id' => $dispatch->warehouse_id,
                                'branch_id' => $dispatch->branch_id,
                                'status' => 'in_warehouse',
                                'condition' => 'good',
                            ]);
                        }

                        // Update serial unit ke status custody
                        $serial->update([
                            'warehouse_id' => null, // keluar dari gudang
                            'custodian_id' => $dispatch->recipient_id,
                            'custodian_type' => $dispatch->recipient_type,
                            'customer_id' => $dispatch->customer_id,
                            'installation_id' => $dispatch->installation_id,
                            'ticket_id' => $dispatch->ticket_id,
                            'status' => $custodyStatus,
                            'updated_by' => $actorId ?: auth()->id(),
                        ]);

                        // Catat ke custody tracking per unit serial
                        InventoryCustody::create([
                            'dispatch_id' => $dispatch->id,
                            'requisition_id' => $dispatch->requisition_id,
                            'item_id' => $itemId,
                            'item_serial_id' => $serial->id,
                            'holder_id' => $dispatch->recipient_id,
                            'holder_type' => $dispatch->recipient_type,
                            'customer_id' => $dispatch->customer_id,
                            'installation_id' => $dispatch->installation_id,
                            'ticket_id' => $dispatch->ticket_id,
                            'quantity' => 1,
                            'used_quantity' => 0,
                            'returned_quantity' => 0,
                            'status' => 'in_custody',
                            'branch_id' => $dispatch->branch_id,
                            'notes' => "SN: {$snClean} diterima via {$dispatch->dispatch_number}",
                        ]);
                    }
                } else {
                    // Barang non-serial (kabel dropcore, konektor, adaptor, dll.)
                    InventoryCustody::create([
                        'dispatch_id' => $dispatch->id,
                        'requisition_id' => $dispatch->requisition_id,
                        'item_id' => $itemId,
                        'item_serial_id' => null,
                        'holder_id' => $dispatch->recipient_id,
                        'holder_type' => $dispatch->recipient_type,
                        'customer_id' => $dispatch->customer_id,
                        'installation_id' => $dispatch->installation_id,
                        'ticket_id' => $dispatch->ticket_id,
                        'quantity' => $quantity,
                        'used_quantity' => 0,
                        'returned_quantity' => 0,
                        'status' => 'in_custody',
                        'branch_id' => $dispatch->branch_id,
                        'notes' => "Diterima via {$dispatch->dispatch_number}",
                    ]);
                }

                // Update requisition items if attached
                if ($dispatch->requisition_id) {
                    $reqItem = InventoryRequisitionItem::where('requisition_id', $dispatch->requisition_id)
                        ->where('item_id', $itemId)
                        ->first();
                    if ($reqItem) {
                        $reqItem->increment('quantity_dispatched', $quantity);
                    }
                }
            }

            // Update status requisition jika sudah terpenuhi
            if ($dispatch->requisition_id) {
                $req = InventoryRequisition::find($dispatch->requisition_id);
                if ($req) {
                    $allDispatched = true;
                    foreach ($req->items as $ri) {
                        $approvedQty = $ri->quantity_approved ?? $ri->quantity_requested;
                        if ($ri->quantity_dispatched < $approvedQty) {
                            $allDispatched = false;
                            break;
                        }
                    }
                    if ($allDispatched) {
                        $req->update(['status' => 'dispatched']);
                    }
                }
            }
        });
    }

    /**
     * Serah Terima Barang dari Reseller ke Teknisi (Metode A langkah 11)
     */
    public function handoverCustody(
        int $custodyId,
        int $toUserId,
        string $toUserType = 'technician',
        ?string $notes = null
    ): InventoryCustodyTransfer {
        return DB::transaction(function () use ($custodyId, $toUserId, $toUserType, $notes) {
            $custody = InventoryCustody::findOrFail($custodyId);

            if ($custody->status !== 'in_custody' || $custody->available_quantity <= 0) {
                throw new \RuntimeException('Barang ini tidak dapat diserahterimakan karena sudah tidak berada dalam penguasaan.');
            }

            return InventoryCustodyTransfer::create([
                'custody_id' => $custody->id,
                'from_user_id' => $custody->holder_id,
                'from_user_type' => $custody->holder_type,
                'to_user_id' => $toUserId,
                'to_user_type' => $toUserType,
                'item_id' => $custody->item_id,
                'item_serial_id' => $custody->item_serial_id,
                'quantity' => $custody->available_quantity,
                'status' => 'pending_acceptance',
                'notes' => $notes ?: 'Serah terima barang ke teknisi pelaksana',
                'branch_id' => $custody->branch_id,
            ]);
        });
    }

    /**
     * Konfirmasi Penerimaan Serah Terima oleh Teknisi (Metode A langkah 12)
     */
    public function acceptHandover(int $transferId, int $acceptedByUserId): void
    {
        DB::transaction(function () use ($transferId, $acceptedByUserId) {
            $transfer = InventoryCustodyTransfer::findOrFail($transferId);

            if ($transfer->status !== 'pending_acceptance') {
                throw new \RuntimeException('Transfer serah terima ini sudah diproses sebelumnya.');
            }

            if ($transfer->to_user_id !== $acceptedByUserId && !auth()->user()?->hasRole('administrator')) {
                throw new \RuntimeException('Anda tidak memiliki wewenang untuk menerima serah terima barang ini.');
            }

            $sourceCustody = $transfer->custody;

            // Update transfer
            $transfer->update([
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            // Update status custody lama menjadi 'handed_over'
            $sourceCustody->update([
                'status' => 'handed_over',
            ]);

            // Buat custody baru untuk pemegang baru (Teknisi)
            InventoryCustody::create([
                'dispatch_id' => $sourceCustody->dispatch_id,
                'requisition_id' => $sourceCustody->requisition_id,
                'item_id' => $transfer->item_id,
                'item_serial_id' => $transfer->item_serial_id,
                'holder_id' => $transfer->to_user_id,
                'holder_type' => $transfer->to_user_type,
                'customer_id' => $sourceCustody->customer_id,
                'installation_id' => $sourceCustody->installation_id,
                'ticket_id' => $sourceCustody->ticket_id,
                'quantity' => $transfer->quantity,
                'used_quantity' => 0,
                'returned_quantity' => 0,
                'status' => 'in_custody',
                'branch_id' => $transfer->branch_id,
                'notes' => "Diterima via serah terima No. {$transfer->transfer_number} dari {$transfer->fromUser->name}",
            ]);

            // Jika barang berserial, perbarui pemegang pada ItemSerial
            if ($transfer->item_serial_id) {
                $serial = ItemSerial::find($transfer->item_serial_id);
                if ($serial) {
                    $serial->update([
                        'custodian_id' => $transfer->to_user_id,
                        'custodian_type' => $transfer->to_user_type,
                        'status' => 'in_custody_technician',
                        'updated_by' => $acceptedByUserId,
                    ]);
                }
            }
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

    /**
     * Catat Pemakaian Aktual Barang di Pelanggan (PSB / Maintenance)
     */
    public function recordUsage(
        InventoryUsage $usage,
        array $itemsData,
        ?int $actorId = null
    ): void {
        DB::transaction(function () use ($usage, $itemsData, $actorId) {
            foreach ($itemsData as $row) {
                $itemId = (int) $row['item_id'];
                $quantity = (float) $row['quantity'];
                $custodyId = $row['custody_id'] ?? null;
                $itemSerialId = !empty($row['item_serial_id']) ? (int) $row['item_serial_id'] : null;
                $serials = $row['serials'] ?? [];

                // Jika custody_id tidak diberikan langsung, cari custody aktif milik teknisi untuk item ini
                if (!$custodyId && $usage->technician_id) {
                    $activeCustody = InventoryCustody::where('holder_id', $usage->technician_id)
                        ->where('item_id', $itemId)
                        ->whereIn('status', ['in_custody', 'accepted', 'in_transit'])
                        ->first();
                    if ($activeCustody) {
                        $custodyId = $activeCustody->id;
                    }
                }

                if ($custodyId) {
                    $custody = InventoryCustody::findOrFail($custodyId);
                    $newUsed = (float) $custody->used_quantity + $quantity;
                    $custody->update([
                        'used_quantity' => $newUsed,
                        'available_quantity' => max(0, (float) $custody->quantity - $newUsed - (float) $custody->returned_quantity),
                        'status' => ($newUsed + (float) $custody->returned_quantity >= (float) $custody->quantity) ? 'used' : 'in_custody',
                        'customer_id' => $usage->customer_id ?: $custody->customer_id,
                        'installation_id' => $usage->installation_id ?: $custody->installation_id,
                        'ticket_id' => $usage->ticket_id ?: $custody->ticket_id,
                    ]);
                }

                // Handle serial number jika diberikan via array serials atau item_serial_id
                if (!empty($serials)) {
                    foreach ($serials as $sn) {
                        $serial = ItemSerial::where('serial_number', $sn)->first();
                        if ($serial) {
                            $itemSerialId = $serial->id;
                            $serial->update([
                                'status' => 'installed',
                                'customer_id' => $usage->customer_id,
                                'installation_id' => $usage->installation_id,
                                'ticket_id' => $usage->ticket_id,
                                'updated_by' => $actorId ?: auth()->id(),
                            ]);
                        }
                    }
                } elseif ($itemSerialId) {
                    $serial = ItemSerial::find($itemSerialId);
                    if ($serial) {
                        $serial->update([
                            'status' => 'installed',
                            'customer_id' => $usage->customer_id,
                            'installation_id' => $usage->installation_id,
                            'ticket_id' => $usage->ticket_id,
                            'updated_by' => $actorId ?: auth()->id(),
                        ]);
                    }
                }

                InventoryUsageItem::create([
                    'usage_id' => $usage->id,
                    'custody_id' => $custodyId,
                    'item_id' => $itemId,
                    'item_serial_id' => $itemSerialId,
                    'quantity' => $quantity,
                    'notes' => $row['notes'] ?? null,
                ]);
            }
        });
    }

    /**
     * Proses Pengembalian Barang & Inspeksi QC di Gudang
     */
    public function processReturnQC(
        InventoryReturn $return,
        array $qcItems,
        int $inspectorId
    ): void {
        DB::transaction(function () use ($return, $qcItems, $inspectorId) {
            $warehouse = Warehouse::withoutGlobalScopes()->findOrFail($return->warehouse_id);

            foreach ($qcItems as $row) {
                $returnItemId = $row['return_item_id'] ?? null;
                $returnItem = $returnItemId ? InventoryReturnItem::find($returnItemId) : null;

                $itemId = (int) ($row['item_id'] ?? ($returnItem ? $returnItem->item_id : 0));
                $quantity = (float) ($row['quantity'] ?? ($returnItem ? $returnItem->quantity : 1));
                $condition = $row['condition'] ?? ($returnItem ? $returnItem->condition : 'good');
                $actionTaken = $row['action_taken'] ?? ($condition === 'good' ? 'restock' : 'quarantine');
                $custodyId = $row['custody_id'] ?? ($returnItem ? $returnItem->custody_id : null);
                $itemSerialId = !empty($row['item_serial_id']) ? (int) $row['item_serial_id'] : ($returnItem ? $returnItem->item_serial_id : null);

                // Auto resolve custody if null
                if (!$custodyId && $return->returner_id) {
                    $activeCustody = InventoryCustody::where('holder_id', $return->returner_id)
                        ->where('item_id', $itemId)
                        ->first();
                    if ($activeCustody) {
                        $custodyId = $activeCustody->id;
                    }
                }

                if ($custodyId) {
                    $custody = InventoryCustody::findOrFail($custodyId);
                    $newRet = (float) $custody->returned_quantity + $quantity;
                    $custody->update([
                        'returned_quantity' => $newRet,
                        'available_quantity' => max(0, (float) $custody->quantity - (float) $custody->used_quantity - $newRet),
                        'status' => ((float) $custody->used_quantity + $newRet >= (float) $custody->quantity) ? 'returned' : 'in_custody',
                    ]);
                }

                // If serial not directly on returnItem, check if any ItemSerial is attached to this returnItem
                if (!$itemSerialId && $returnItem) {
                    $attachedSerial = ItemSerial::where('return_item_id', $returnItem->id)->first();
                    if ($attachedSerial) {
                        $itemSerialId = $attachedSerial->id;
                    }
                }

                if ($condition === 'good' && $actionTaken === 'restock') {
                    $stock = InventoryStock::withoutGlobalScopes()
                        ->where('warehouse_id', $warehouse->id)
                        ->where('item_id', $itemId)
                        ->lockForUpdate()
                        ->first();

                    $balanceBefore = $stock ? (float) $stock->quantity : 0.0;
                    $balanceAfter = $balanceBefore + $quantity;

                    if ($stock) {
                        $stock->update(['quantity' => $balanceAfter, 'updated_at' => now()]);
                    } else {
                        InventoryStock::create([
                            'warehouse_id' => $warehouse->id,
                            'item_id' => $itemId,
                            'quantity' => $balanceAfter,
                            'reserved_quantity' => 0,
                            'branch_id' => $warehouse->branch_id,
                        ]);
                    }

                    InventoryStockMutation::create([
                        'warehouse_id' => $warehouse->id,
                        'item_id' => $itemId,
                        'type' => 'return_good',
                        'qty_in' => $quantity,
                        'qty_out' => 0,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceAfter,
                        'reference_type' => 'return',
                        'reference_id' => $return->id,
                        'notes' => "Pengembalian barang sisa/baik No. {$return->return_number}",
                        'actor_id' => $inspectorId,
                        'branch_id' => $warehouse->branch_id,
                        'created_at' => now(),
                    ]);

                    if ($itemSerialId) {
                        $serial = ItemSerial::find($itemSerialId);
                        if ($serial) {
                            $serial->update([
                                'warehouse_id' => $warehouse->id,
                                'custodian_id' => null,
                                'custodian_type' => null,
                                'status' => 'in_warehouse',
                                'condition' => 'good',
                                'updated_by' => $inspectorId,
                            ]);
                        }
                    }
                } else {
                    if ($itemSerialId) {
                        $serial = ItemSerial::find($itemSerialId);
                        if ($serial) {
                            $serial->update([
                                'warehouse_id' => null,
                                'custodian_id' => null,
                                'custodian_type' => null,
                                'status' => $condition === 'damaged' ? 'damaged' : 'returned_check',
                                'condition' => $condition,
                                'updated_by' => $inspectorId,
                            ]);
                        }
                    }

                    InventoryStockMutation::create([
                        'warehouse_id' => $warehouse->id,
                        'item_id' => $itemId,
                        'type' => 'return_damaged',
                        'qty_in' => 0,
                        'qty_out' => 0,
                        'balance_before' => 0,
                        'balance_after' => 0,
                        'reference_type' => 'return',
                        'reference_id' => $return->id,
                        'notes' => "Retur barang rusak/klaim QC No. {$return->return_number} (Kondisi: {$condition})",
                        'actor_id' => $inspectorId,
                        'branch_id' => $warehouse->branch_id,
                        'created_at' => now(),
                    ]);
                }

                if ($returnItem) {
                    $returnItem->update([
                        'custody_id' => $custodyId,
                        'item_serial_id' => $itemSerialId,
                        'condition' => $condition,
                        'action_taken' => $actionTaken,
                        'qc_notes' => $row['qc_notes'] ?? null,
                    ]);
                } else {
                    InventoryReturnItem::create([
                        'return_id' => $return->id,
                        'custody_id' => $custodyId,
                        'item_id' => $itemId,
                        'item_serial_id' => $itemSerialId,
                        'quantity' => $quantity,
                        'condition' => $condition,
                        'action_taken' => $actionTaken,
                        'qc_notes' => $row['qc_notes'] ?? null,
                    ]);
                }
            }

            $return->update([
                'status' => 'restocked',
                'inspected_by' => $inspectorId,
                'inspected_at' => now(),
            ]);
        });
    }
}
