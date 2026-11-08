<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryCustody;
use App\Models\Inventory\InventoryCustodyTransfer;
use App\Models\Inventory\InventoryRequisition;
use App\Models\Inventory\InventoryRequisitionItem;
use App\Models\Inventory\InventoryReturn;
use App\Models\Inventory\InventoryReturnItem;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MobileInventoryApiController extends Controller
{
    /**
     * Check if user has warehouse administration or manager privileges.
     */
    protected function isWarehouseOrManager(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->isWarehouseAdmin()
            || $user->hasRole(['manager', 'administrator', 'admin_gudang', 'superadmin'])
            || in_array(strtoupper($user->job_function ?? ''), ['MANAGER', 'ADMINISTRATOR', 'WAREHOUSE', 'GUDANG']);
    }

    /**
     * Get inventory items currently in custody of the authenticated technician (Phase 2B).
     */
    public function myStock(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = InventoryCustody::with(['item', 'itemSerial'])
            ->where('holder_id', $user->id)
            ->whereRaw('(quantity - used_quantity - returned_quantity) > 0')
            ->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $search = '%' . trim((string)$request->input('search')) . '%';
            $query->whereHas('item', function ($iq) use ($search) {
                $iq->where('name', 'like', $search)
                    ->orWhere('sku', 'like', $search)
                    ->orWhere('category', 'like', $search);
            });
        }

        $custodies = $query->get();

        $formatted = $custodies->map(function (InventoryCustody $c) {
            $item = $c->item;
            $avail = max(0, (float)$c->quantity - (float)$c->used_quantity - (float)$c->returned_quantity);

            return [
                'id' => $c->id,
                'uuid' => $c->uuid,
                'item_id' => $c->item_id,
                'item_name' => $item?->name ?? 'Material #' . $c->item_id,
                'sku' => $item?->sku ?? '-',
                'category' => $item?->category ?? 'General',
                'unit' => $item?->unit ?? 'pcs',
                'quantity' => (float)$c->quantity,
                'used_quantity' => (float)$c->used_quantity,
                'returned_quantity' => (float)$c->returned_quantity,
                'available_quantity' => $avail,
                'is_serialized' => (bool)($item?->is_serialized || $c->item_serial_id),
                'serial_number' => $c->itemSerial?->serial_number,
                'status' => $c->status,
                'notes' => $c->notes,
                'created_at' => $c->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted,
            'summary' => [
                'total_items' => $custodies->count(),
                'total_units' => $formatted->sum('available_quantity'),
                'serialized_items' => $formatted->where('is_serialized', true)->count(),
            ],
        ]);
    }

    /**
     * Catalog of items in the warehouse available for requisition.
     */
    public function items(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Item::query()
            ->where('is_active', true)
            ->orderBy('name');

        if ($user->branch_id && !$user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                    ->orWhereNull('branch_id');
            });
        }

        if ($request->filled('search')) {
            $search = '%' . trim((string)$request->input('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('sku', 'like', $search)
                    ->orWhere('category', 'like', $search);
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $items = $query->limit(100)->get();

        $formatted = $items->map(fn(Item $i) => [
            'id' => $i->id,
            'uuid' => $i->uuid,
            'name' => $i->name,
            'sku' => $i->sku,
            'category' => $i->category ?? 'General',
            'unit' => $i->unit ?? 'pcs',
            'brand' => $i->brand,
            'model' => $i->model,
            'is_serialized' => (bool)$i->is_serialized,
            'cost_price' => (float)$i->cost_price,
        ]);

        return response()->json([
            'success' => true,
            'data' => $formatted,
        ]);
    }

    /**
     * Submit a material requisition from field staff/technician to warehouse.
     */
    public function createRequisition(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.1',
            'items.*.notes' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
            'warehouse_id' => 'nullable|integer',
        ]);

        $warehouseId = $request->input('warehouse_id');
        if (!$warehouseId) {
            $warehouse = Warehouse::where('branch_id', $user->branch_id)->first()
                ?? Warehouse::first();

            if (!$warehouse) {
                // Ensure at least one warehouse exists for the branch/company
                $warehouse = Warehouse::create([
                    'uuid' => (string) Str::uuid(),
                    'code' => 'WH-MAIN',
                    'name' => 'Gudang Utama Cabang',
                    'branch_id' => $user->branch_id,
                    'is_active' => true,
                ]);
            }
            $warehouseId = $warehouse->id;
        }

        DB::beginTransaction();
        try {
            $requisition = InventoryRequisition::create([
                'uuid' => (string) Str::uuid(),
                'requester_id' => $user->id,
                'requester_type' => 'technician',
                'warehouse_id' => $warehouseId,
                'branch_id' => $user->branch_id,
                'distribution_method' => 'direct_technician',
                'status' => 'pending',
                'notes' => $request->input('notes'),
            ]);

            foreach ($request->input('items') as $line) {
                InventoryRequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'item_id' => (int)$line['item_id'],
                    'quantity_requested' => (float)$line['quantity'],
                    'notes' => $line['notes'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Permintaan material {$requisition->requisition_number} berhasil diajukan.",
                'data' => [
                    'id' => $requisition->id,
                    'requisition_number' => $requisition->requisition_number,
                    'status' => $requisition->status,
                    'items_count' => count($request->input('items')),
                    'created_at' => $requisition->created_at?->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat permintaan material: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List requisitions for current user or branch.
     */
    public function requisitions(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = InventoryRequisition::with(['items.item', 'requester', 'approver'])
            ->orderBy('id', 'desc');

        if (!$this->isWarehouseOrManager($user)) {
            $query->where('requester_id', $user->id);
        } elseif ($user->branch_id && !$user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                    ->orWhereNull('branch_id');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $list = $query->limit(50)->get();

        $formatted = $list->map(fn(InventoryRequisition $r) => [
            'id' => $r->id,
            'uuid' => $r->uuid,
            'requisition_number' => $r->requisition_number,
            'status' => $r->status,
            'requester_name' => $r->requester?->name,
            'approver_name' => $r->approver?->name,
            'items_count' => $r->items->count(),
            'notes' => $r->notes,
            'created_at' => $r->created_at?->toIso8601String(),
            'approved_at' => $r->approved_at?->toIso8601String(),
            'items' => $r->items->map(fn($item) => [
                'id' => $item->id,
                'item_id' => $item->item_id,
                'item_name' => $item->item?->name,
                'quantity_requested' => (float)$item->quantity_requested,
                'quantity_approved' => (float)($item->quantity_approved ?? $item->quantity_requested),
            ]),
        ]);

        return response()->json([
            'success' => true,
            'data' => $formatted,
        ]);
    }

    /**
     * Approve material requisition and grant custody to technician (Manager / Warehouse only).
     */
    public function approveRequisition(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$this->isWarehouseOrManager($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Admin Gudang atau Manager yang berwenang menyetujui permintaan material.',
            ], 403);
        }

        $requisition = InventoryRequisition::with('items')->findOrFail($id);

        if ($requisition->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan ini sudah disetujui sebelumnya.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $requisition->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            // Automatically grant/dispatch custody to requester technician
            foreach ($requisition->items as $reqItem) {
                $reqItem->update([
                    'quantity_approved' => $reqItem->quantity_requested,
                    'quantity_dispatched' => $reqItem->quantity_requested,
                ]);

                InventoryCustody::create([
                    'uuid' => (string) Str::uuid(),
                    'requisition_id' => $requisition->id,
                    'item_id' => $reqItem->item_id,
                    'holder_id' => $requisition->requester_id,
                    'holder_type' => 'technician',
                    'quantity' => $reqItem->quantity_requested,
                    'used_quantity' => 0,
                    'returned_quantity' => 0,
                    'status' => 'in_custody',
                    'branch_id' => $requisition->branch_id,
                    'notes' => "Diterima dari Requisition {$requisition->requisition_number}",
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Permintaan material {$requisition->requisition_number} disetujui. Stok telah ditambahkan ke custody teknisi.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui permintaan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handover / transfer custody material to another technician.
     */
    public function transferCustody(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'custody_id' => 'required|integer|exists:inventory_custodies,id',
            'to_user_id' => 'required|integer|exists:users,id|different:' . $user->id,
            'quantity' => 'required|numeric|min:0.1',
            'notes' => 'nullable|string|max:255',
        ]);

        $custody = InventoryCustody::findOrFail($request->input('custody_id'));

        if ((int)$custody->holder_id !== (int)$user->id && !$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Material ini bukan dalam penguasaan akun Anda.',
            ], 403);
        }

        $avail = max(0, (float)$custody->quantity - (float)$custody->used_quantity - (float)$custody->returned_quantity);
        $transferQty = (float)$request->input('quantity');

        if ($transferQty > $avail) {
            return response()->json([
                'success' => false,
                'message' => "Jumlah transfer ($transferQty) melebihi stok tersedia ($avail).",
            ], 422);
        }

        $targetUser = User::findOrFail($request->input('to_user_id'));

        DB::beginTransaction();
        try {
            // Deduct from current holder
            $custody->quantity = (float)$custody->quantity - $transferQty;
            if ($custody->quantity <= 0) {
                $custody->status = 'handed_over';
            }
            $custody->save();

            // Grant to target user
            $targetCustody = InventoryCustody::create([
                'uuid' => (string) Str::uuid(),
                'item_id' => $custody->item_id,
                'item_serial_id' => $custody->item_serial_id,
                'holder_id' => $targetUser->id,
                'holder_type' => 'technician',
                'quantity' => $transferQty,
                'used_quantity' => 0,
                'returned_quantity' => 0,
                'status' => 'in_custody',
                'branch_id' => $targetUser->branch_id,
                'notes' => "Serah terima dari {$user->name}: " . ($request->input('notes') ?? ''),
            ]);

            // Record transfer log
            InventoryCustodyTransfer::create([
                'uuid' => (string) Str::uuid(),
                'custody_id' => $custody->id,
                'from_user_id' => $user->id,
                'from_user_type' => 'technician',
                'to_user_id' => $targetUser->id,
                'to_user_type' => 'technician',
                'item_id' => $custody->item_id,
                'item_serial_id' => $custody->item_serial_id,
                'quantity' => $transferQty,
                'status' => 'accepted',
                'notes' => $request->input('notes'),
                'accepted_at' => now(),
                'branch_id' => $user->branch_id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Material sebanyak $transferQty berhasil diserahterimakan kepada {$targetUser->name}.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses serah terima material: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit return of material (leftover, damaged, or broken) back to warehouse.
     */
    public function returnItems(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'custody_id' => 'required|integer|exists:inventory_custodies,id',
            'quantity' => 'required|numeric|min:0.1',
            'condition' => 'required|string|in:good,damaged,needs_check',
            'notes' => 'nullable|string|max:500',
        ]);

        $custody = InventoryCustody::with('item')->findOrFail($request->input('custody_id'));

        if ((int)$custody->holder_id !== (int)$user->id && !$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Material ini bukan dalam penguasaan akun Anda.',
            ], 403);
        }

        $avail = max(0, (float)$custody->quantity - (float)$custody->used_quantity - (float)$custody->returned_quantity);
        $returnQty = (float)$request->input('quantity');

        if ($returnQty > $avail) {
            return response()->json([
                'success' => false,
                'message' => "Jumlah retur ($returnQty) melebihi stok tersedia ($avail).",
            ], 422);
        }

        $warehouse = Warehouse::where('branch_id', $user->branch_id)->first() ?? Warehouse::first();
        if (!$warehouse) {
            $warehouse = Warehouse::create([
                'uuid' => (string) Str::uuid(),
                'code' => 'WH-MAIN',
                'name' => 'Gudang Utama Cabang',
                'branch_id' => $user->branch_id,
                'is_active' => true,
            ]);
        }

        DB::beginTransaction();
        try {
            $returnDoc = InventoryReturn::create([
                'uuid' => (string) Str::uuid(),
                'returner_id' => $user->id,
                'returner_type' => 'technician',
                'warehouse_id' => $warehouse->id,
                'status' => 'pending_qc',
                'notes' => $request->input('notes'),
                'branch_id' => $user->branch_id,
            ]);

            InventoryReturnItem::create([
                'return_id' => $returnDoc->id,
                'custody_id' => $custody->id,
                'item_id' => $custody->item_id,
                'item_serial_id' => $custody->item_serial_id,
                'quantity' => $returnQty,
                'condition' => $request->input('condition'),
                'action_taken' => $request->input('condition') === 'damaged' ? 'quarantine' : 'restock',
                'qc_notes' => $request->input('notes'),
            ]);

            // Update custody returned quantity
            $custody->returned_quantity = (float)$custody->returned_quantity + $returnQty;
            $newAvail = (float)$custody->quantity - (float)$custody->used_quantity - (float)$custody->returned_quantity;
            if ($newAvail <= 0) {
                $custody->status = $request->input('condition') === 'damaged' ? 'lost_damaged' : 'returned';
            }
            $custody->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Pengembalian barang {$returnDoc->return_number} berhasil didaftarkan ke gudang.",
                'data' => [
                    'return_number' => $returnDoc->return_number,
                    'quantity_returned' => $returnQty,
                    'condition' => $request->input('condition'),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pengembalian barang: ' . $e->getMessage(),
            ], 500);
        }
    }
}
