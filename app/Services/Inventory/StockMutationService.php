<?php

namespace App\Services\Inventory;

use App\Models\Inventory\InventoryInbound;
use App\Models\Inventory\InventoryInboundItem;
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
     * Batalkan penerimaan barang masuk (Rollback stok fisik, ledger mutasi, dan serial number)
     */
    public function cancelInbound(int $inboundId, ?string $reason = null, ?int $actorId = null): void
    {
        DB::transaction(function () use ($inboundId, $reason, $actorId) {
            $inbound = InventoryInbound::withoutGlobalScopes()->with('items')->findOrFail($inboundId);

            if ($inbound->status === 'cancelled') {
                throw new \InvalidArgumentException('Dokumen penerimaan ini sudah dibatalkan sebelumnya.');
            }

            foreach ($inbound->items as $inItem) {
                $item = Item::withoutGlobalScopes()->findOrFail($inItem->item_id);
                $qty = (float) $inItem->quantity;

                // 1. Cek saldo stok di gudang
                $stock = InventoryStock::withoutGlobalScopes()
                    ->where('warehouse_id', $inbound->warehouse_id)
                    ->where('item_id', $inItem->item_id)
                    ->lockForUpdate()
                    ->first();

                $currentQty = $stock ? (float) $stock->quantity : 0.0;
                $balanceBefore = $currentQty;
                $balanceAfter = max(0, $balanceBefore - $qty);

                if ($currentQty < $qty) {
                    \Illuminate\Support\Facades\Log::warning("cancelInbound: Saldo stok '{$item->name}' di gudang ({$currentQty} {$item->unit}) lebih kecil dari jumlah penerimaan ({$qty} {$item->unit}). Saldo diturunkan ke {$balanceAfter}.");
                }

                // 2. Jika barang berserial, periksa apakah ada serial yang sudah bergerak
                if ($item->is_serialized && !empty($inItem->serials)) {
                    $usedSerials = ItemSerial::withoutGlobalScopes()
                        ->where('item_id', $item->id)
                        ->whereIn('serial_number', $inItem->serials)
                        ->where('status', '!=', 'in_warehouse')
                        ->pluck('serial_number')
                        ->toArray();

                    if (!empty($usedSerials)) {
                        $list = implode(', ', array_slice($usedSerials, 0, 3));
                        throw new \RuntimeException("Tidak dapat membatalkan! Serial Number [{$list}] sudah berstatus di luar gudang (sedang dipegang teknisi atau terpasang ke pelanggan).");
                    }

                    // Hapus serial yang terdaftar dari inbound ini yang masih in_warehouse
                    ItemSerial::withoutGlobalScopes()
                        ->where('item_id', $item->id)
                        ->whereIn('serial_number', $inItem->serials)
                        ->where('status', 'in_warehouse')
                        ->delete();
                }

                // 3. Revert saldo fisik gudang
                if ($stock) {
                    $stock->update([
                        'quantity' => $balanceAfter,
                        'updated_at' => now(),
                    ]);
                }

                // 4. Catat mutasi pembatalan di ledger
                InventoryStockMutation::create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'mutation_number' => InventoryStockMutation::generateMutationNumber(),
                    'warehouse_id' => $inbound->warehouse_id,
                    'item_id' => $item->id,
                    'type' => 'adjustment_out',
                    'qty_in' => 0,
                    'qty_out' => min($qty, $balanceBefore),
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'reference_type' => 'inbound_cancellation',
                    'reference_id' => $inbound->id,
                    'notes' => 'Pembatalan Penerimaan ' . $inbound->inbound_number . ($reason ? " ($reason)" : ''),
                    'actor_id' => $actorId ?: auth()->id(),
                    'branch_id' => $inbound->branch_id,
                    'created_at' => now(),
                ]);
            }

            // Update status inbound menjadi cancelled
            $inbound->update([
                'status' => 'cancelled',
                'notes' => trim(($inbound->notes ? $inbound->notes . ' | ' : '') . 'DIBATALKAN: ' . ($reason ?: 'Kesalahan input penerimaan')),
            ]);
            $inbound->delete(); // Soft delete agar tidak merusak relasi namun tidak muncul di list aktif
        });
    }

    /**
     * Rollback saldo stok dan serial number lama untuk dokumen penerimaan yang sedang direvisi (diedit).
     * Dijalankan dalam transaksi yang sama saat edit sebelum items baru dicatat.
     */
    public function rollbackInboundForRevision(InventoryInbound $inbound, ?int $actorId = null): void
    {
        DB::transaction(function () use ($inbound, $actorId) {
            foreach ($inbound->items as $inItem) {
                $item = Item::withoutGlobalScopes()->find($inItem->item_id);
                if (!$item) continue;

                $qty = (float) $inItem->quantity;

                // 1. Cek saldo fisik di gudang
                $stock = InventoryStock::withoutGlobalScopes()
                    ->where('warehouse_id', $inbound->warehouse_id)
                    ->where('item_id', $inItem->item_id)
                    ->lockForUpdate()
                    ->first();

                $currentQty = $stock ? (float) $stock->quantity : 0.0;
                $balanceBefore = $currentQty;
                // Selama revisi, kurangi stok lama secara aman (jika saldo kurang dari qty akibat clean/opname, turunkan ke 0 tanpa crash)
                $balanceAfter = max(0, $balanceBefore - $qty);

                if ($stock) {
                    $stock->update([
                        'quantity' => $balanceAfter,
                        'updated_at' => now(),
                    ]);
                }

                // 2. Bersihkan serial numbers lama yang masih berstatus 'in_warehouse' agar tidak bentrok saat simpan ulang
                if ($item->is_serialized && !empty($inItem->serials)) {
                    ItemSerial::withoutGlobalScopes()
                        ->where('item_id', $item->id)
                        ->whereIn('serial_number', $inItem->serials)
                        ->where('status', 'in_warehouse')
                        ->delete();
                }

                // 3. Catat mutasi penyesuaian revisi di ledger
                if ($qty > 0) {
                    InventoryStockMutation::create([
                        'uuid' => (string) \Illuminate\Support\Str::uuid(),
                        'mutation_number' => InventoryStockMutation::generateMutationNumber(),
                        'warehouse_id' => $inbound->warehouse_id,
                        'item_id' => $item->id,
                        'type' => 'adjustment_out',
                        'qty_in' => 0,
                        'qty_out' => min($qty, $balanceBefore),
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceAfter,
                        'reference_type' => 'inbound_revision_rollback',
                        'reference_id' => $inbound->id,
                        'notes' => 'Rollback revisi penerimaan ' . $inbound->inbound_number,
                        'actor_id' => $actorId ?: auth()->id(),
                        'branch_id' => $inbound->branch_id,
                        'created_at' => now(),
                    ]);
                }
            }
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

            // Jika barang berserial, sinkronkan dengan jumlah unit fisik nyata di gudang (in_warehouse)
            if ($item->is_serialized) {
                $physicalSerialCount = (float) ItemSerial::withoutGlobalScopes()
                    ->where('warehouse_id', $warehouseId)
                    ->where('item_id', $itemId)
                    ->where('status', 'in_warehouse')
                    ->count();

                if ($balanceBefore < $physicalSerialCount) {
                    $balanceBefore = $physicalSerialCount;
                    if ($stock) {
                        $stock->update(['quantity' => $balanceBefore]);
                    } else {
                        $stock = InventoryStock::create([
                            'warehouse_id' => $warehouseId,
                            'item_id' => $itemId,
                            'quantity' => $balanceBefore,
                            'reserved_quantity' => 0,
                            'branch_id' => $wh->branch_id,
                        ]);
                    }
                }
            }

            // Cegah stok negatif!
            if ($balanceBefore < $quantity) {
                throw new \RuntimeException("Stok tidak mencukupi di gudang {$wh->name}. Tersedia: {$balanceBefore} {$item->unit}, dibutuhkan: {$quantity} {$item->unit}.");
            }

            $balanceAfter = $balanceBefore - $quantity;

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
        ?string $notes = null,
        ?float $quantity = null,
        ?int $customerId = null
    ): InventoryCustodyTransfer {
        return DB::transaction(function () use ($custodyId, $toUserId, $toUserType, $notes, $quantity, $customerId) {
            $custody = InventoryCustody::findOrFail($custodyId);

            if ($custody->status !== 'in_custody' || $custody->available_quantity <= 0) {
                throw new \RuntimeException('Barang ini tidak dapat diserahterimakan karena sudah tidak berada dalam penguasaan.');
            }

            $qtyToTransfer = $quantity ?? $custody->available_quantity;
            if ($qtyToTransfer <= 0 || $qtyToTransfer > $custody->available_quantity) {
                throw new \RuntimeException('Jumlah transfer tidak valid atau melebihi sisa barang yang tersedia.');
            }

            $notesText = $notes ?: 'Serah terima barang ke teknisi pelaksana';
            if ($customerId) {
                $cust = \App\Models\CRM\Customer::find($customerId);
                if ($cust) {
                    $notesText .= " [Pelanggan: {$cust->name}]";
                }
            }

            return InventoryCustodyTransfer::create([
                'custody_id' => $custody->id,
                'from_user_id' => $custody->holder_id,
                'from_user_type' => $custody->holder_type,
                'to_user_id' => $toUserId,
                'to_user_type' => $toUserType,
                'item_id' => $custody->item_id,
                'item_serial_id' => $custody->item_serial_id,
                'quantity' => $qtyToTransfer,
                'status' => 'pending_acceptance',
                'notes' => $notesText,
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

            // Jika barang non-serial dan diserahkan sebagian:
            if (!$sourceCustody->item_serial_id && $transfer->quantity < $sourceCustody->available_quantity) {
                // Kurangi kuantitas pada custody sumber
                $sourceCustody->decrement('quantity', $transfer->quantity);
            } else {
                // Seluruhnya diserahkan (atau barang berserial yang 1 unit)
                $sourceCustody->update([
                    'status' => 'handed_over',
                ]);
            }

            // Tentukan customer_id jika ditag di notes
            $targetCustomerId = $sourceCustody->customer_id;
            if (preg_match('/\[Pelanggan:\s*([^\]]+)\]/', $transfer->notes, $matches)) {
                $custName = trim($matches[1]);
                $cFound = \App\Models\CRM\Customer::where('name', $custName)->first();
                if ($cFound) {
                    $targetCustomerId = $cFound->id;
                }
            }

            // Buat custody baru untuk pemegang baru (Teknisi)
            InventoryCustody::create([
                'dispatch_id' => $sourceCustody->dispatch_id,
                'requisition_id' => $sourceCustody->requisition_id,
                'item_id' => $transfer->item_id,
                'item_serial_id' => $transfer->item_serial_id,
                'holder_id' => $transfer->to_user_id,
                'holder_type' => $transfer->to_user_type,
                'customer_id' => $targetCustomerId,
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
                        'customer_id' => $targetCustomerId,
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
     * Potong barang dari bagasi teknisi / reseller saat aktivasi PSB (Wizard)
     */
    public function recordPsbConsumption(
        int $technicianId,
        int $customerId,
        string $onuSn,
        float $dropcoreMeters = 0,
        int $fastConnectorQty = 0,
        ?string $opticalPower = null,
        ?int $installationId = null,
        ?int $ticketId = null,
        ?int $requisitionId = null,
        ?int $resellerId = null
    ): ?InventoryUsage {
        return DB::transaction(function () use (
            $technicianId,
            $customerId,
            $onuSn,
            $dropcoreMeters,
            $fastConnectorQty,
            $opticalPower,
            $installationId,
            $ticketId,
            $requisitionId,
            $resellerId
        ) {
            $techUser = \App\Models\User::find($technicianId);
            $branchId = $techUser?->branch_id;

            $usage = InventoryUsage::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'usage_number' => 'USG-PSB-' . strtoupper(\Illuminate\Support\Str::random(8)),
                'technician_id' => $technicianId,
                'customer_id' => $customerId,
                'installation_id' => $installationId,
                'ticket_id' => $ticketId,
                'work_order_type' => 'psb',
                'usage_date' => now()->toDateString(),
                'optical_power' => $opticalPower ?: null,
                'notes' => "Pemakaian material PSB via Wizard (SN: {$onuSn})",
                'branch_id' => $branchId,
            ]);

            $candidateHolderIds = array_filter(array_unique([$technicianId, $resellerId]));

            // 1. Potong Modem ONT berdasarkan Serial Number
            $snClean = strtoupper(trim($onuSn));
            if (!empty($snClean)) {
                $itemSerial = ItemSerial::where('serial_number', $snClean)->first();
                $custodyOnt = null;

                if ($itemSerial) {
                    $custodyOnt = InventoryCustody::where('item_serial_id', $itemSerial->id)
                        ->whereIn('holder_id', $candidateHolderIds)
                        ->where('status', 'in_custody')
                        ->first();
                }

                if (!$custodyOnt) {
                    $custodyOnt = InventoryCustody::whereIn('holder_id', $candidateHolderIds)
                        ->where('status', 'in_custody')
                        ->whereHas('item', function ($q) {
                            $q->where('name', 'like', '%ONU%')
                              ->orWhere('name', 'like', '%ONT%')
                              ->orWhere('name', 'like', '%Modem%')
                              ->orWhere('sku', 'like', '%ONU%')
                              ->orWhere('sku', 'like', '%ONT%');
                        })
                        ->whereRaw('(quantity - used_quantity - returned_quantity) >= 1')
                        ->first();
                }

                if ($custodyOnt) {
                    $newUsed = (float) $custodyOnt->used_quantity + 1;
                    $custodyOnt->update([
                        'used_quantity' => $newUsed,
                        'status' => ($newUsed + (float) $custodyOnt->returned_quantity >= (float) $custodyOnt->quantity) ? 'used' : 'in_custody',
                        'customer_id' => $customerId,
                        'installation_id' => $installationId,
                        'ticket_id' => $ticketId,
                    ]);

                    if ($itemSerial) {
                        $itemSerial->update([
                            'status' => 'installed',
                            'customer_id' => $customerId,
                            'installation_id' => $installationId,
                            'ticket_id' => $ticketId,
                            'updated_by' => $technicianId,
                        ]);
                    }

                    InventoryUsageItem::create([
                        'usage_id' => $usage->id,
                        'custody_id' => $custodyOnt->id,
                        'item_id' => $custodyOnt->item_id,
                        'item_serial_id' => $itemSerial?->id ?? $custodyOnt->item_serial_id,
                        'quantity' => 1,
                        'notes' => "Modem ONT SN: {$snClean}",
                    ]);
                }
            }

            // 2. Potong Dropcore
            if ($dropcoreMeters > 0) {
                $custodyDc = InventoryCustody::whereIn('holder_id', $candidateHolderIds)
                    ->where('status', 'in_custody')
                    ->whereHas('item', function ($q) {
                        $q->where('name', 'like', '%Dropcore%')
                          ->orWhere('name', 'like', '%Drop Core%')
                          ->orWhere('name', 'like', '%Kabel Optik%')
                          ->orWhere('unit', 'meter');
                    })
                    ->whereRaw('(quantity - used_quantity - returned_quantity) > 0')
                    ->first();

                if ($custodyDc) {
                    $newUsed = (float) $custodyDc->used_quantity + $dropcoreMeters;
                    $custodyDc->update([
                        'used_quantity' => $newUsed,
                        'status' => ($newUsed + (float) $custodyDc->returned_quantity >= (float) $custodyDc->quantity) ? 'used' : 'in_custody',
                        'customer_id' => $customerId,
                        'installation_id' => $installationId,
                    ]);

                    InventoryUsageItem::create([
                        'usage_id' => $usage->id,
                        'custody_id' => $custodyDc->id,
                        'item_id' => $custodyDc->item_id,
                        'quantity' => $dropcoreMeters,
                        'notes' => "Kabel Dropcore: {$dropcoreMeters} meter",
                    ]);
                }
            }

            // 3. Potong Fast Connector
            if ($fastConnectorQty > 0) {
                $custodyFc = InventoryCustody::whereIn('holder_id', $candidateHolderIds)
                    ->where('status', 'in_custody')
                    ->whereHas('item', function ($q) {
                        $q->where('name', 'like', '%Fast Connector%')
                          ->orWhere('name', 'like', '%Connector%')
                          ->orWhere('name', 'like', '%SC/UPC%')
                          ->orWhere('name', 'like', '%SOC%');
                    })
                    ->whereRaw('(quantity - used_quantity - returned_quantity) > 0')
                    ->first();

                if ($custodyFc) {
                    $newUsed = (float) $custodyFc->used_quantity + $fastConnectorQty;
                    $custodyFc->update([
                        'used_quantity' => $newUsed,
                        'status' => ($newUsed + (float) $custodyFc->returned_quantity >= (float) $custodyFc->quantity) ? 'used' : 'in_custody',
                        'customer_id' => $customerId,
                        'installation_id' => $installationId,
                    ]);

                    InventoryUsageItem::create([
                        'usage_id' => $usage->id,
                        'custody_id' => $custodyFc->id,
                        'item_id' => $custodyFc->item_id,
                        'quantity' => $fastConnectorQty,
                        'notes' => "Fast Connector: {$fastConnectorQty} pcs",
                    ]);
                }
            }

            // 4. Update Requisition jika ada
            if ($requisitionId) {
                $req = InventoryRequisition::find($requisitionId);
                if ($req && in_array($req->status, ['approved', 'dispatched'])) {
                    $req->update(['status' => 'completed']);
                }
            }

            return $usage;
        });
    }

    /**
     * Potong barang dari bagasi teknisi saat perbaikan tiket maintenance
     */
    public function recordTicketMaterialUsage(
        int $ticketId,
        int $technicianId,
        int $custodyId,
        float $quantity,
        ?string $notes = null
    ): ?InventoryUsage {
        return DB::transaction(function () use ($ticketId, $technicianId, $custodyId, $quantity, $notes) {
            $ticket = \App\Models\Support\Ticket::withoutGlobalScopes()->findOrFail($ticketId);
            $techUser = \App\Models\User::find($technicianId);
            $custody = InventoryCustody::with(['item', 'itemSerial'])
                ->where('id', $custodyId)
                ->where('holder_id', $technicianId)
                ->lockForUpdate()
                ->firstOrFail();

            $avail = (float) $custody->available_quantity;
            if ($quantity > $avail) {
                throw new \RuntimeException("Jumlah melebihi stok barang di bagasi Anda (Tersedia: {$avail} {$custody->item?->unit}).");
            }

            $usage = InventoryUsage::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'usage_number' => 'USG-TCK-' . strtoupper(\Illuminate\Support\Str::random(8)),
                'technician_id' => $technicianId,
                'customer_id' => $ticket->customer_id,
                'ticket_id' => $ticket->id,
                'work_order_type' => 'maintenance',
                'usage_date' => now()->toDateString(),
                'notes' => $notes ?: 'Pemakaian material perbaikan tiket #' . $ticket->id,
                'branch_id' => $techUser?->branch_id,
            ]);

            $newUsed = (float) $custody->used_quantity + $quantity;
            $custody->update([
                'used_quantity' => $newUsed,
                'status' => ($newUsed + (float) $custody->returned_quantity >= (float) $custody->quantity) ? 'used' : 'in_custody',
                'customer_id' => $ticket->customer_id,
                'ticket_id' => $ticket->id,
            ]);

            if ($custody->item_serial_id) {
                $serial = ItemSerial::find($custody->item_serial_id);
                if ($serial) {
                    $serial->update([
                        'status' => 'installed',
                        'customer_id' => $ticket->customer_id,
                        'ticket_id' => $ticket->id,
                        'updated_by' => $technicianId,
                    ]);
                }
            }

            InventoryUsageItem::create([
                'usage_id' => $usage->id,
                'custody_id' => $custody->id,
                'item_id' => $custody->item_id,
                'item_serial_id' => $custody->item_serial_id,
                'quantity' => $quantity,
                'notes' => $notes,
            ]);

            return $usage;
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

    /**
     * Mutasi stok terintegrasi untuk Ganti/Swap ONT di lapangan (Web & Android).
     * Memotong 1 unit ONT baru dari stok/custody teknisi dan mencatat retur ONT lama (good/damaged).
     */
    public function recordOntSwap(
        int $technicianId,
        string $oldSn,
        string $newSn,
        string $oldCondition = 'damaged',
        ?int $customerId = null,
        ?int $ticketId = null,
        ?int $workOrderId = null,
        ?int $supportMaterialId = null,
        ?string $oldModel = 'ONT GPON',
        ?string $newModel = 'ONT GPON',
        ?string $opticalPower = null,
        ?string $notes = null,
        bool $enforceStock = false
    ): array {
        return DB::transaction(function () use (
            $technicianId,
            $oldSn,
            $newSn,
            $oldCondition,
            $customerId,
            $ticketId,
            $workOrderId,
            $supportMaterialId,
            $oldModel,
            $newModel,
            $opticalPower,
            $notes,
            $enforceStock
        ) {
            $techUser = \App\Models\User::find($technicianId);
            $branchId = $techUser?->branch_id;
            $oldSnClean = strtoupper(trim($oldSn));
            $newSnClean = strtoupper(trim($newSn));
            $normalizedOldCond = in_array($oldCondition, ['good', 'berfungsi']) ? 'good' : 'damaged';

            $custodyDeducted = false;
            $supportMaterialDeducted = false;

            // 1. Validasi kepemilikan & status ItemSerial ONT Baru (dengan lockForUpdate)
            $newSerial = ItemSerial::withoutGlobalScopes()
                ->where('serial_number', $newSnClean)
                ->lockForUpdate()
                ->first();

            if ($newSerial) {
                if ($newSerial->status === 'installed' && (int) $newSerial->customer_id !== (int) $customerId) {
                    throw new \InvalidArgumentException("SN {$newSnClean} sudah terpasang pada pelanggan lain.");
                }
                if ($newSerial->status === 'damaged' || $newSerial->condition === 'damaged') {
                    throw new \InvalidArgumentException("SN {$newSnClean} tercatat dalam kondisi rusak (damaged) dan tidak dapat dipasang.");
                }
                if ($newSerial->status === 'in_warehouse') {
                    throw new \InvalidArgumentException("SN {$newSnClean} masih berada di gudang dan belum diserahkan ke stok teknisi.");
                }
                if ($newSerial->custodian_id !== null && (int) $newSerial->custodian_id !== (int) $technicianId) {
                    throw new \InvalidArgumentException("SN {$newSnClean} berada di stok/custody teknisi lain.");
                }
            }

            // Cari Custody ONT Baru milik teknisi
            $custodyOnt = null;
            if ($newSerial) {
                $custodyOnt = InventoryCustody::withoutGlobalScopes()
                    ->where('item_serial_id', $newSerial->id)
                    ->where('holder_id', $technicianId)
                    ->where('status', 'in_custody')
                    ->whereRaw('(quantity - used_quantity - returned_quantity) >= 1')
                    ->lockForUpdate()
                    ->first();
            }

            if (!$custodyOnt) {
                $custodyOnt = InventoryCustody::withoutGlobalScopes()
                    ->where('holder_id', $technicianId)
                    ->where('status', 'in_custody')
                    ->whereHas('item', function ($q) {
                        $q->where('name', 'like', '%ONU%')
                          ->orWhere('name', 'like', '%ONT%')
                          ->orWhere('name', 'like', '%Modem%')
                          ->orWhere('sku', 'like', '%ONU%')
                          ->orWhere('sku', 'like', '%ONT%');
                    })
                    ->whereRaw('(quantity - used_quantity - returned_quantity) >= 1')
                    ->lockForUpdate()
                    ->first();
            }

            // Cek apakah teknisi memang memiliki alokasi custody ONT (meskipun sudah habis / 0)
            $hasAnyOntCustodyHistory = InventoryCustody::withoutGlobalScopes()
                ->where('holder_id', $technicianId)
                ->whereHas('item', function ($q) {
                    $q->where('name', 'like', '%ONU%')
                      ->orWhere('name', 'like', '%ONT%')
                      ->orWhere('name', 'like', '%Modem%')
                      ->orWhere('sku', 'like', '%ONU%')
                      ->orWhere('sku', 'like', '%ONT%');
                })
                ->exists();

            if ($custodyOnt) {
                $newUsed = (float) $custodyOnt->used_quantity + 1;
                $custodyOnt->update([
                    'used_quantity' => $newUsed,
                    'status' => ($newUsed + (float) $custodyOnt->returned_quantity >= (float) $custodyOnt->quantity) ? 'used' : 'in_custody',
                    'customer_id' => $customerId,
                    'ticket_id' => $ticketId,
                ]);
                $custodyDeducted = true;

                $usage = InventoryUsage::create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'usage_number' => 'USG-SWP-' . strtoupper(\Illuminate\Support\Str::random(8)),
                    'technician_id' => $technicianId,
                    'customer_id' => $customerId,
                    'ticket_id' => $ticketId,
                    'work_order_type' => 'swap_ont',
                    'usage_date' => now()->toDateString(),
                    'optical_power' => $opticalPower,
                    'notes' => "Swap ONT [{$oldSnClean}] -> [{$newSnClean}]. " . ($notes ?? ''),
                    'branch_id' => $branchId,
                ]);

                InventoryUsageItem::create([
                    'usage_id' => $usage->id,
                    'custody_id' => $custodyOnt->id,
                    'item_id' => $custodyOnt->item_id,
                    'item_serial_id' => $newSerial?->id ?? $custodyOnt->item_serial_id,
                    'quantity' => 1,
                    'notes' => "Pemasangan ONT Baru SN: {$newSnClean}",
                ]);
            }

            // 2. Backward compatibility: support_maintenance_materials
            if (\Illuminate\Support\Facades\Schema::hasTable('support_maintenance_materials')) {
                $matQuery = DB::table('support_maintenance_materials')->lockForUpdate();
                if ($supportMaterialId) {
                    $mat = $matQuery->where('id', $supportMaterialId)->first();
                    if ((!$mat || (int) $mat->stock < 1) && !$custodyDeducted) {
                        throw new \InvalidArgumentException("Stok material ONT tidak mencukupi (Inventory Shortage).");
                    }
                } else {
                    $mat = $matQuery->where(function ($q) use ($newModel) {
                        if ($newModel) {
                            $q->where('name', 'like', "%{$newModel}%");
                        }
                        $q->orWhere('name', 'like', '%ONT%')
                          ->orWhere('sku', 'like', '%ONT%');
                    })->where('stock', '>', 0)->first();
                }

                if ($mat && (int) $mat->stock > 0) {
                    $newStock = max(0, ((int) $mat->stock) - 1);
                    DB::table('support_maintenance_materials')
                        ->where('id', $mat->id)
                        ->update([
                            'stock' => $newStock,
                            'updated_at' => now(),
                        ]);
                    $supportMaterialDeducted = true;

                    if (\Illuminate\Support\Facades\Schema::hasTable('support_maintenance_usages')) {
                        DB::table('support_maintenance_usages')->insert([
                            'material_id' => $mat->id,
                            'material_name' => "[SWAP ONT BARU] " . $mat->name . " (SN: {$newSnClean})",
                            'quantity' => 1,
                            'unit' => 'unit',
                            'technician_id' => $technicianId,
                            'technician_name' => $techUser?->name ?? 'Teknisi',
                            'ticket_id' => $ticketId,
                            'used_date' => now()->toDateString(),
                            'notes' => "Pemasangan ONT baru pengganti {$oldSnClean}. Redaman OPM: " . ($opticalPower ?? '-'),
                            'created_by' => $technicianId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Penegakan Inventory Shortage: Jika wajib stok (enforceStock) atau teknisi punya riwayat custody ONT tapi saldo 0
            if (($enforceStock || $hasAnyOntCustodyHistory || $newSerial) && !$custodyDeducted && !$supportMaterialDeducted) {
                throw new \InvalidArgumentException(
                    "Stok ONT di bagasi teknisi habis atau SN {$newSnClean} tidak tersedia di custody Anda (Inventory Shortage)."
                );
            }

            // Pastikan Item master default tersedia untuk pelacakan serial
            $defaultOntItem = $newSerial?->item
                ?? $custodyOnt?->item
                ?? Item::withoutGlobalScopes()
                    ->where(function ($q) {
                        $q->where('sku', 'like', '%ONT%')
                          ->orWhere('name', 'like', '%ONT%')
                          ->orWhere('name', 'like', '%ONU%');
                    })->first();

            if (!$defaultOntItem && \Illuminate\Support\Facades\Schema::hasTable('items')) {
                $defaultOntItem = Item::withoutGlobalScopes()->create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'sku' => 'ONT-GENERIC-UNIT',
                    'name' => $newModel ?: 'Modem ONT GPON',
                    'category' => 'Perangkat Aktif',
                    'unit' => 'unit',
                    'is_serialized' => true,
                    'is_active' => true,
                    'branch_id' => $branchId,
                ]);
            }

            if ($newSerial) {
                $newSerial->update([
                    'status' => 'installed',
                    'condition' => 'good',
                    'customer_id' => $customerId,
                    'ticket_id' => $ticketId,
                    'custodian_id' => null,
                    'custodian_type' => null,
                    'updated_by' => $technicianId,
                ]);
            } elseif ($defaultOntItem) {
                $newSerial = ItemSerial::withoutGlobalScopes()->create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'item_id' => $defaultOntItem->id,
                    'serial_number' => $newSnClean,
                    'customer_id' => $customerId,
                    'ticket_id' => $ticketId,
                    'status' => 'installed',
                    'condition' => 'good',
                    'branch_id' => $branchId,
                    'created_by' => $technicianId,
                    'updated_by' => $technicianId,
                ]);
            }

            // 3. Kelola ONT Lama (ItemSerial & Custody Retur)
            if ($oldSnClean !== '' && $oldSnClean !== 'ONT-LAMA') {
                $oldSerial = ItemSerial::withoutGlobalScopes()
                    ->where('serial_number', $oldSnClean)
                    ->lockForUpdate()
                    ->first();

                if (!$oldSerial && $defaultOntItem) {
                    $oldSerial = ItemSerial::withoutGlobalScopes()->create([
                        'uuid' => (string) \Illuminate\Support\Str::uuid(),
                        'item_id' => $defaultOntItem->id,
                        'serial_number' => $oldSnClean,
                        'status' => $normalizedOldCond === 'good' ? 'in_custody_technician' : 'damaged',
                        'condition' => $normalizedOldCond,
                        'customer_id' => null,
                        'custodian_id' => $technicianId,
                        'custodian_type' => 'technician',
                        'ticket_id' => $ticketId,
                        'branch_id' => $branchId,
                        'notes' => "Ditarik via Swap ONT digantikan oleh {$newSnClean}",
                        'created_by' => $technicianId,
                        'updated_by' => $technicianId,
                    ]);
                } elseif ($oldSerial) {
                    $oldSerial->update([
                        'status' => $normalizedOldCond === 'good' ? 'in_custody_technician' : 'damaged',
                        'condition' => $normalizedOldCond,
                        'customer_id' => null,
                        'custodian_id' => $technicianId,
                        'custodian_type' => 'technician',
                        'ticket_id' => $ticketId,
                        'notes' => "Ditarik via Swap ONT digantikan oleh {$newSnClean}",
                        'updated_by' => $technicianId,
                    ]);
                }

                if ($oldSerial) {
                    $oldCustody = InventoryCustody::withoutGlobalScopes()
                        ->where('holder_id', $technicianId)
                        ->where('item_serial_id', $oldSerial->id)
                        ->lockForUpdate()
                        ->first();

                    if ($normalizedOldCond === 'good') {
                        // Kembalikan ONT lama yang bagus ke InventoryCustody teknisi (+1 available)
                        if ($oldCustody) {
                            $oldCustody->update([
                                'used_quantity' => max(0, (float) $oldCustody->used_quantity - 1),
                                'status' => 'in_custody',
                                'notes' => "ONT Bekas Layak Pakai dari Swap ({$oldSnClean})",
                            ]);
                        } else {
                            InventoryCustody::create([
                                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                                'item_id' => $oldSerial->item_id,
                                'item_serial_id' => $oldSerial->id,
                                'holder_id' => $technicianId,
                                'holder_type' => 'technician',
                                'quantity' => 1,
                                'used_quantity' => 0,
                                'returned_quantity' => 0,
                                'status' => 'in_custody',
                                'branch_id' => $branchId,
                                'notes' => "ONT Bekas Layak Pakai dari Swap ({$oldSnClean})",
                            ]);
                        }
                    } elseif ($oldCustody) {
                        // Pastikan ONT rusak TIDAK masuk ke stok siap pakai
                        $oldCustody->update([
                            'status' => 'damaged',
                            'notes' => "ONT Rusak/Afkir dari Swap ({$oldSnClean})",
                        ]);
                    }
                }
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('support_maintenance_materials') && $normalizedOldCond === 'good') {
                $retMat = DB::table('support_maintenance_materials')
                    ->where(function ($q) use ($oldModel) {
                        if ($oldModel) {
                            $q->where('name', 'like', "%{$oldModel}%");
                        }
                        $q->orWhere('sku', 'like', '%ONT%');
                    })
                    ->lockForUpdate()
                    ->first();

                if ($retMat) {
                    DB::table('support_maintenance_materials')
                        ->where('id', $retMat->id)
                        ->increment('stock', 1);
                }
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('support_maintenance_usages')) {
                $condLabel = $normalizedOldCond === 'damaged' ? '[ONT RUSAK/AFKIR]' : '[ONT BEKAS RETUR]';
                DB::table('support_maintenance_usages')->insert([
                    'material_id' => null,
                    'material_name' => "{$condLabel} " . ($oldModel ?: 'ONT') . " (SN: {$oldSnClean})",
                    'quantity' => 1,
                    'unit' => 'unit',
                    'technician_id' => $technicianId,
                    'technician_name' => $techUser?->name ?? 'Teknisi',
                    'ticket_id' => $ticketId,
                    'used_date' => now()->toDateString(),
                    'notes' => $notes ?: "Retur Swap ONT lama {$oldSnClean}",
                    'created_by' => $technicianId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return [
                'custody_deducted' => $custodyDeducted,
                'support_material_deducted' => $supportMaterialDeducted,
                'old_condition' => $normalizedOldCond,
            ];
        });
    }

    /**
     * Mutasi stok terintegrasi untuk Cabut/Dismantle ONT di lapangan (Web & Android).
     * Jika kondisi 'good', menambah stok teknisi (+1). Jika 'damaged', dicatat sebagai barang rusak/afkir.
     */
    public function recordOntDismantle(
        int $technicianId,
        string $sn,
        string $condition = 'good',
        ?int $customerId = null,
        ?int $ticketId = null,
        ?int $workOrderId = null,
        ?string $modelName = 'ONT GPON',
        ?string $notes = null
    ): array {
        return DB::transaction(function () use (
            $technicianId,
            $sn,
            $condition,
            $customerId,
            $ticketId,
            $workOrderId,
            $modelName,
            $notes
        ) {
            $techUser = \App\Models\User::find($technicianId);
            $branchId = $techUser?->branch_id;
            $snClean = strtoupper(trim($sn));
            $normalizedCond = in_array($condition, ['good', 'berfungsi']) ? 'good' : 'damaged';
            $restocked = false;

            // 1. Update atau Buat ItemSerial & InventoryCustody di Enterprise Inventory
            $serial = ItemSerial::withoutGlobalScopes()
                ->where('serial_number', $snClean)
                ->lockForUpdate()
                ->first();

            if (!$serial && \Illuminate\Support\Facades\Schema::hasTable('items')) {
                $defaultOntItem = Item::withoutGlobalScopes()
                    ->where(function ($q) {
                        $q->where('sku', 'like', '%ONT%')
                          ->orWhere('name', 'like', '%ONT%')
                          ->orWhere('name', 'like', '%ONU%');
                    })->first();

                if (!$defaultOntItem) {
                    $defaultOntItem = Item::withoutGlobalScopes()->create([
                        'uuid' => (string) \Illuminate\Support\Str::uuid(),
                        'sku' => 'ONT-GENERIC-UNIT',
                        'name' => $modelName ?: 'Modem ONT GPON',
                        'category' => 'Perangkat Aktif',
                        'unit' => 'unit',
                        'is_serialized' => true,
                        'is_active' => true,
                        'branch_id' => $branchId,
                    ]);
                }

                $serial = ItemSerial::withoutGlobalScopes()->create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'item_id' => $defaultOntItem->id,
                    'serial_number' => $snClean,
                    'status' => $normalizedCond === 'good' ? 'in_custody_technician' : 'damaged',
                    'condition' => $normalizedCond,
                    'customer_id' => null,
                    'installation_id' => null,
                    'ticket_id' => $ticketId,
                    'custodian_id' => $technicianId,
                    'custodian_type' => 'technician',
                    'branch_id' => $branchId,
                    'notes' => $notes ?: "Ditarik (Dismantle) dalam kondisi {$normalizedCond}",
                    'created_by' => $technicianId,
                    'updated_by' => $technicianId,
                ]);
            } elseif ($serial) {
                $serial->update([
                    'status' => $normalizedCond === 'good' ? 'in_custody_technician' : 'damaged',
                    'condition' => $normalizedCond,
                    'customer_id' => null,
                    'installation_id' => null,
                    'ticket_id' => $ticketId,
                    'custodian_id' => $technicianId,
                    'custodian_type' => 'technician',
                    'notes' => $notes ?: "Ditarik (Dismantle) dalam kondisi {$normalizedCond}",
                    'updated_by' => $technicianId,
                ]);
            }

            if ($serial) {
                $existingCustody = InventoryCustody::withoutGlobalScopes()
                    ->where('holder_id', $technicianId)
                    ->where('item_serial_id', $serial->id)
                    ->lockForUpdate()
                    ->first();

                if ($normalizedCond === 'good') {
                    if ($existingCustody) {
                        $existingCustody->update([
                            'used_quantity' => max(0, (float) $existingCustody->used_quantity - 1),
                            'status' => 'in_custody',
                        ]);
                        $restocked = true;
                    } else {
                        InventoryCustody::create([
                            'uuid' => (string) \Illuminate\Support\Str::uuid(),
                            'item_id' => $serial->item_id,
                            'item_serial_id' => $serial->id,
                            'holder_id' => $technicianId,
                            'holder_type' => 'technician',
                            'quantity' => 1,
                            'used_quantity' => 0,
                            'returned_quantity' => 0,
                            'status' => 'in_custody',
                            'branch_id' => $branchId ?? $serial->branch_id,
                            'notes' => "Hasil cabutan (Dismantle) SN: {$snClean}",
                        ]);
                        $restocked = true;
                    }
                } elseif ($existingCustody) {
                    // Jika rusak, pastikan tidak masuk ke stok aktif siap pakai
                    $existingCustody->update([
                        'status' => 'damaged',
                        'notes' => "Hasil cabutan rusak (Dismantle) SN: {$snClean}",
                    ]);
                }
            }

            // 2. Update support_maintenance_materials (dengan lockForUpdate)
            if (\Illuminate\Support\Facades\Schema::hasTable('support_maintenance_materials')) {
                if ($normalizedCond === 'good') {
                    $existingMat = DB::table('support_maintenance_materials')
                        ->where(function ($q) use ($modelName) {
                            if ($modelName) {
                                $q->where('name', 'like', "%{$modelName}%");
                            }
                            $q->orWhere('sku', 'like', '%ONT%')
                              ->orWhere('name', 'like', '%ONT%');
                        })
                        ->lockForUpdate()
                        ->first();

                    if ($existingMat) {
                        DB::table('support_maintenance_materials')
                            ->where('id', $existingMat->id)
                            ->increment('stock', 1);
                        $restocked = true;
                    }
                }
            }

            // 3. Catat log penarikan di support_maintenance_usages
            if (\Illuminate\Support\Facades\Schema::hasTable('support_maintenance_usages')) {
                $tag = $normalizedCond === 'good' ? '[TARIKAN BAGUS/SIAP PAKAI]' : '[TARIKAN RUSAK/AFKIR]';
                DB::table('support_maintenance_usages')->insert([
                    'material_id' => null,
                    'material_name' => "{$tag} " . ($modelName ?: 'ONT GPON') . " (SN: {$snClean})",
                    'quantity' => 1,
                    'unit' => 'unit',
                    'technician_id' => $technicianId,
                    'technician_name' => $techUser?->name ?? 'Teknisi',
                    'ticket_id' => $ticketId,
                    'used_date' => now()->toDateString(),
                    'notes' => $notes ?: "Dismantle ONT {$snClean}",
                    'created_by' => $technicianId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return [
                'restocked' => $restocked,
                'condition' => $normalizedCond,
            ];
        });
    }
}
