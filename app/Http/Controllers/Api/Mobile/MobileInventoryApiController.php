<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\Inventory\InventoryCustody;
use App\Models\Inventory\InventoryCustodyTransfer;
use App\Models\Inventory\InventoryRequisition;
use App\Models\Inventory\InventoryRequisitionItem;
use App\Models\Inventory\InventoryReturn;
use App\Models\Inventory\InventoryReturnItem;
use App\Models\Inventory\InventoryUsage;
use App\Models\Inventory\InventoryUsageItem;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Models\ISP\Onu;
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

        // Also query assigned assets/tools for the technician and format as compatible inventory items for Android APK
        $tools = \App\Models\Inventory\Asset::with(['category', 'warehouse'])
            ->where('assigned_to_id', $user->id)
            ->where('status', 'in_use')
            ->orderBy('id', 'desc')
            ->get();

        $toolCustodyItems = $tools->map(function (\App\Models\Inventory\Asset $a) {
            $categoryName = $a->category?->name ?? 'Alat Kerja & Perkakas';
            $condLabel = match ($a->condition) {
                'good' => 'Kondisi Baik',
                'fair' => 'Cukup',
                'damaged' => 'Rusak Ringan',
                'maintenance' => 'Perlu Servis',
                default => 'Baik',
            };

            return [
                'id' => (int) (800000 + $a->id),
                'uuid' => (string) ($a->code ?: 'TOOL-' . $a->id),
                'item_id' => (int) $a->id,
                'item_name' => (string) ($a->name . ' [' . ($a->code ?: 'ASET') . ']'),
                'sku' => (string) ($a->code ?: 'TOOL'),
                'category' => (string) $categoryName,
                'unit' => 'unit',
                'quantity' => 1.0,
                'used_quantity' => 0.0,
                'returned_quantity' => 0.0,
                'available_quantity' => 1.0,
                'is_serialized' => !empty($a->serial_number),
                'serial_number' => $a->serial_number ?: null,
                'status' => 'in_use',
                'notes' => 'Alat Kerja Operasional: ' . $condLabel . ($a->description ? ' (' . $a->description . ')' : ''),
                'created_at' => $a->assigned_at?->toIso8601String() ?? $a->created_at?->toIso8601String(),
            ];
        });

        $combinedData = $formatted->concat($toolCustodyItems)->values();

        $formattedTools = $tools->map(function (\App\Models\Inventory\Asset $a) {
            return [
                'id' => $a->id,
                'code' => $a->code,
                'name' => $a->name,
                'type' => $a->type ?: 'tool',
                'category' => $a->category?->name ?? 'Alat Kerja',
                'condition' => $a->condition ?: 'good',
                'condition_label' => match ($a->condition) {
                    'good' => 'Kondisi Baik',
                    'fair' => 'Cukup',
                    'damaged' => 'Rusak Ringan',
                    'maintenance' => 'Perlu Servis',
                    default => 'Baik',
                },
                'serial_number' => $a->serial_number ?: '-',
                'status' => $a->status,
                'warehouse_name' => $a->warehouse?->name ?? 'Gudang Pusat',
                'assigned_at' => $a->assigned_at?->toIso8601String(),
                'is_reusable' => true,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $combinedData,
            'tools' => $formattedTools,
            'summary' => [
                'total_items' => $combinedData->count(),
                'total_units' => $combinedData->sum('available_quantity'),
                'serialized_items' => $combinedData->where('is_serialized', true)->count(),
                'total_tools' => $formattedTools->count(),
            ],
        ]);
    }

    /**
     * Get tools & equipment currently assigned to the authenticated technician.
     */
    public function myTools(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = \App\Models\Inventory\Asset::with(['category', 'warehouse'])
            ->where('assigned_to_id', $user->id)
            ->where('status', 'in_use')
            ->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $search = '%' . trim((string)$request->input('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('code', 'like', $search)
                    ->orWhere('serial_number', 'like', $search);
            });
        }

        $assets = $query->get();

        $formatted = $assets->map(function (\App\Models\Inventory\Asset $a) {
            return [
                'id' => $a->id,
                'code' => $a->code,
                'name' => $a->name,
                'type' => $a->type ?: 'tool',
                'category' => $a->category?->name ?? 'Alat Kerja',
                'condition' => $a->condition ?: 'good',
                'condition_label' => match ($a->condition) {
                    'good' => 'Kondisi Baik',
                    'fair' => 'Cukup',
                    'damaged' => 'Rusak Ringan',
                    'maintenance' => 'Perlu Servis',
                    default => 'Baik',
                },
                'serial_number' => $a->serial_number ?: '-',
                'status' => $a->status,
                'warehouse_name' => $a->warehouse?->name ?? 'Gudang Pusat',
                'description' => $a->description,
                'assigned_at' => $a->assigned_at?->toIso8601String(),
                'is_reusable' => true,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted,
            'summary' => [
                'total_tools' => $formatted->count(),
                'good_condition' => $formatted->where('condition', 'good')->count(),
                'needs_repair' => $formatted->whereIn('condition', ['damaged', 'maintenance'])->count(),
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

        $perPage = strtolower((string)$request->input('per_page', '20'));
        if ($perPage === 'all' || (int)$perPage <= 0) {
            $items = $query->get();
        } else {
            $limit = match ($perPage) {
                '50' => 50,
                '100' => 100,
                default => 20,
            };
            $items = $query->limit($limit)->get();
        }

        $warehouse = Warehouse::where('branch_id', $user->branch_id)->where('is_active', true)->first()
            ?? Warehouse::where('is_active', true)->first();
        $whId = $warehouse?->id;

        $formatted = $items->map(function (Item $i) use ($whId) {
            $stockQty = 0.0;
            if ($whId) {
                if ($i->is_serialized) {
                    $serialCount = (float) \App\Models\Inventory\ItemSerial::where('item_id', $i->id)
                        ->where('warehouse_id', $whId)
                        ->where('status', 'in_warehouse')
                        ->count();
                    $stk = \App\Models\Inventory\InventoryStock::where('warehouse_id', $whId)->where('item_id', $i->id)->first();
                    $stockQty = max((float) ($stk?->quantity ?? 0.0), $serialCount);
                } else {
                    $stk = \App\Models\Inventory\InventoryStock::where('warehouse_id', $whId)->where('item_id', $i->id)->first();
                    $stockQty = (float) ($stk?->quantity ?? 0.0);
                }
            }

            return [
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
                'warehouse_id' => $whId,
                'available_stock' => $stockQty,
                'is_available' => $stockQty > 0,
            ];
        });

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
            'requisition_type' => 'nullable|string|in:dispatch_request,procurement_request',
            'urgency' => 'nullable|string|in:normal,urgent,scheduled',
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

        // Cek stok fisik jika tipe adalah dispatch_request (pengambilan langsung)
        $reqType = $request->input('requisition_type');
        if (!$reqType) {
            // Auto-detect: Jika salah satu barang stoknya 0, arahkan ke procurement_request
            $hasOutOfStock = false;
            foreach ($request->input('items') as $line) {
                $itemId = (int)$line['item_id'];
                $itemObj = Item::find($itemId);
                $stk = \App\Models\Inventory\InventoryStock::where('warehouse_id', $warehouseId)->where('item_id', $itemId)->first();
                $qtyStock = (float)($stk?->quantity ?? 0.0);
                if ($itemObj && $itemObj->is_serialized) {
                    $serialCount = (float) \App\Models\Inventory\ItemSerial::where('item_id', $itemId)
                        ->where('warehouse_id', $warehouseId)
                        ->where('status', 'in_warehouse')
                        ->count();
                    $qtyStock = max($qtyStock, $serialCount);
                }
                if ($qtyStock < (float)$line['quantity']) {
                    $hasOutOfStock = true;
                    break;
                }
            }
            $reqType = $hasOutOfStock ? 'procurement_request' : 'dispatch_request';
        }

        DB::beginTransaction();
        try {
            $isProcurement = ($reqType === 'procurement_request');
            $requesterType = $user->isReseller() ? 'reseller' : 'technician';

            $requisition = InventoryRequisition::create([
                'uuid' => (string) Str::uuid(),
                'requisition_type' => $reqType,
                'urgency' => $request->input('urgency', 'normal'),
                'requester_id' => $user->id,
                'requester_type' => $requesterType,
                'warehouse_id' => $warehouseId,
                'branch_id' => $user->branch_id,
                'distribution_method' => $user->isReseller() ? 'pickup_reseller' : 'direct_technician',
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

            $label = $isProcurement
                ? "Permohonan pengadaan material (Restock)"
                : "Permintaan pengambilan material";

            return response()->json([
                'success' => true,
                'message' => "{$label} {$requisition->requisition_number} berhasil diajukan ke admin gudang.",
                'data' => [
                    'id' => $requisition->id,
                    'requisition_number' => $requisition->requisition_number,
                    'requisition_type' => $requisition->requisition_type,
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

        if ($request->filled('requisition_type')) {
            $query->where('requisition_type', $request->input('requisition_type'));
        }

        $list = $query->limit(50)->get();

        $formatted = $list->map(fn(InventoryRequisition $r) => [
            'id' => $r->id,
            'uuid' => $r->uuid,
            'requisition_number' => $r->requisition_number,
            'requisition_type' => $r->requisition_type ?? 'dispatch_request',
            'requisition_type_label' => ($r->requisition_type === 'procurement_request' ? 'Permohonan Pengadaan' : 'Pengambilan Barang'),
            'urgency' => $r->urgency ?? 'normal',
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

    /**
     * Catat pemakaian material untuk perbaikan/penggantian alat non-serial (Adaptor, Fatcore/Fast Connector, Patchcord, Kabel Dropcore).
     * Dapat dilakukan oleh Teknisi maupun Reseller langsung dari stok tangan (custody).
     * Mendukung pencarian pelanggan langsung via Serial Number (SN) modem yang sedang terpasang di lokasi.
     */
    public function recordCustomerUsage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'serial_number' => 'nullable|string|max:100',
            'onu_sn' => 'nullable|string|max:100',
            'customer_id' => 'nullable|integer',
            'item_id' => 'nullable|integer',
            'material_type' => 'nullable|string|max:50',
            'quantity' => 'nullable|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
            'ticket_id' => 'nullable|integer',
        ]);

        $user = $request->user();
        $customer = null;
        $snClean = strtoupper(trim((string) ($request->input('serial_number') ?? $request->input('onu_sn') ?? '')));

        if ($snClean !== '') {
            $onu = Onu::withoutGlobalScopes()->where('serial_number', $snClean)->first();
            if ($onu) {
                $service = CustomerService::withoutGlobalScopes()->where('onu_id', $onu->id)->latest('id')->first();
                if ($service && $service->customer_id) {
                    $customer = Customer::withoutGlobalScopes()->find($service->customer_id);
                }
                if (!$customer && $onu->customer_id) {
                    $customer = Customer::withoutGlobalScopes()->find($onu->customer_id);
                }
            }
            if (!$customer) {
                $itemSerial = \App\Models\Inventory\ItemSerial::withoutGlobalScopes()->where('serial_number', $snClean)->first();
                if ($itemSerial && $itemSerial->customer_id) {
                    $customer = Customer::withoutGlobalScopes()->find($itemSerial->customer_id);
                }
            }
        }

        if (!$customer && !empty($validated['customer_id'])) {
            $customer = Customer::withoutGlobalScopes()->find((int) $validated['customer_id']);
        }

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => $snClean !== ''
                    ? "Perangkat ONT dengan SN {$snClean} tidak terhubung ke pelanggan aktif manapun."
                    : 'ID Pelanggan atau SN Modem ONT wajib diisi.',
            ], 422);
        }

        // Scope check untuk Reseller: hanya boleh pelanggan binaan sendiri
        if ($user->isReseller()) {
            $effectiveResellerId = $user->getEffectiveResellerId();
            if ($customer->reseller_id && (int) $customer->reseller_id !== (int) $effectiveResellerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Pelanggan bukan binaan reseller Anda.',
                ], 403);
            }
        }

        // Resolusi material_type jika item_id tidak dikirim langsung (misal pilih 'adaptor' / 'fatcore' / 'patchcord')
        $targetItemId = !empty($validated['item_id']) ? (int) $validated['item_id'] : null;
        if (!$targetItemId && !empty($validated['material_type'])) {
            $matType = strtolower(trim($validated['material_type']));
            $custodyMatch = InventoryCustody::withoutGlobalScopes()
                ->where('holder_id', $user->id)
                ->where('status', 'in_custody')
                ->whereHas('item', function ($q) use ($matType) {
                    $q->where(function ($sub) use ($matType) {
                        if ($matType === 'adaptor') {
                            $sub->where('name', 'like', '%Adaptor%')->orWhere('sku', 'like', '%ADP%');
                        } elseif (in_array($matType, ['fatcore', 'fast_connector', 'connector', 'fc'])) {
                            $sub->where('name', 'like', '%Fast Connector%')->orWhere('name', 'like', '%Fatcore%')->orWhere('name', 'like', '%Konektor%');
                        } elseif (in_array($matType, ['patchcord', 'dropcore', 'kabel', 'dc'])) {
                            $sub->where('name', 'like', '%Patchcord%')->orWhere('name', 'like', '%Dropcore%')->orWhere('name', 'like', '%Drop Core%');
                        }
                    });
                })
                ->whereRaw('(quantity - COALESCE(used_quantity, 0) - COALESCE(returned_quantity, 0)) > 0')
                ->first();
            if ($custodyMatch) {
                $targetItemId = $custodyMatch->item_id;
            }
        }

        if (!$targetItemId) {
            return response()->json([
                'success' => false,
                'message' => 'Material atau jenis barang (Adaptor / Fatcore / Patchcord) tidak ditemukan atau belum dipilih.',
            ], 422);
        }

        // Cek stok custody di tangan pemohon (teknisi / reseller)
        $qtyNeeded = !empty($validated['quantity']) ? (float) $validated['quantity'] : 1.0;
        $custody = InventoryCustody::withoutGlobalScopes()
            ->where('holder_id', $user->id)
            ->where('item_id', $targetItemId)
            ->where('status', 'in_custody')
            ->whereRaw('(quantity - COALESCE(used_quantity, 0) - COALESCE(returned_quantity, 0)) >= ' . (float) $qtyNeeded)
            ->lockForUpdate()
            ->first();

        if (!$custody) {
            return response()->json([
                'success' => false,
                'message' => 'Stok material tidak mencukupi di tangan (custody) Anda.',
            ], 422);
        }

        $item = $custody->item ?? Item::withoutGlobalScopes()->find($targetItemId);

        return DB::transaction(function () use ($user, $customer, $custody, $item, $qtyNeeded, $validated, $snClean) {
            // 1. Potong stok custody
            $newUsed = (float) $custody->used_quantity + $qtyNeeded;
            $isFullyUsed = ($newUsed + (float) $custody->returned_quantity) >= (float) $custody->quantity;
            $custody->update([
                'used_quantity' => $newUsed,
                'status' => $isFullyUsed ? 'used' : 'in_custody',
                'customer_id' => $customer->id,
            ]);

            // 2. Buat record InventoryUsage
            $snTag = $snClean !== '' ? " [SN Modem: {$snClean}]" : '';
            $usage = InventoryUsage::create([
                'uuid' => (string) Str::uuid(),
                'usage_number' => 'USG-REP-' . strtoupper(Str::random(8)),
                'technician_id' => $user->id,
                'customer_id' => $customer->id,
                'ticket_id' => $validated['ticket_id'] ?? null,
                'work_order_type' => 'repair_replacement',
                'usage_date' => now()->toDateString(),
                'notes' => ($validated['notes'] ?? 'Penggantian material perbaikan') . "{$snTag} oleh {$user->name}",
                'branch_id' => $customer->branch_id ?? $user->branch_id,
            ]);

            // 3. Buat InventoryUsageItem
            $usageItem = InventoryUsageItem::create([
                'usage_id' => $usage->id,
                'custody_id' => $custody->id,
                'item_id' => $item?->id ?? $targetItemId,
                'item_serial_id' => $custody->item_serial_id,
                'quantity' => $qtyNeeded,
                'notes' => ($validated['notes'] ?? 'Penggantian material perbaikan') . $snTag,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Material '{$item?->name}' ({$qtyNeeded} {$item?->unit}) berhasil dicatat untuk pelanggan {$customer->name}.",
                'data' => [
                    'usage_number' => $usage->usage_number,
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'serial_number' => $snClean ?: null,
                    'item_id' => $item?->id,
                    'item_name' => $item?->name,
                    'quantity_used' => $qtyNeeded,
                    'remaining_custody' => max(0, (float) $custody->quantity - $newUsed - (float) $custody->returned_quantity),
                ],
            ]);
        });
    }
}
