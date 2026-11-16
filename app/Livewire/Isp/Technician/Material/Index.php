<?php

namespace App\Livewire\Isp\Technician\Material;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemSerial;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\InventoryCustody;
use App\Models\Inventory\InventoryUsage;
use App\Models\Inventory\InventoryUsageItem;
use App\Models\Inventory\InventoryReturn;
use App\Models\Inventory\InventoryReturnItem;
use App\Models\Inventory\InventoryRequisition;
use App\Models\Inventory\InventoryRequisitionItem;
use App\Models\Support\Ticket;

#[Layout('layouts.technician-app')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $activeTab = 'stok'; // stok, gudang, pemakaian, permintaan

    public string $search = '';

    // Modal Minta Barang dari Gudang (Requisition)
    public bool $showRequestModal = false;
    public ?int $reqItemId = null;
    public string $reqMaterialName = '';
    public int $reqQuantity = 1;
    public string $reqUnit = 'pcs';
    public string $reqReason = '';
    public string $reqWorkOrderType = 'stock_topup';

    // Modal Catat Pemakaian Cepat (Usage)
    public bool $showUsageModal = false;
    public ?int $selectedCustodyId = null;
    public string $usageMaterialName = '';
    public string $usageSerial = '';
    public int $usageQuantity = 1;
    public float $usageMaxQuantity = 1;
    public string $usageUnit = 'pcs';
    public ?int $usageTicketId = null;
    public string $usageNotes = '';

    // Modal Retur & Lapor Rusak / Hilang (Return)
    public bool $showReturnModal = false;
    public ?int $returnCustodyId = null;
    public string $returnAction = 'retur'; // 'retur', 'rusak', 'hilang'
    public string $returnMaterialName = '';
    public string $returnSerial = '';
    public int $returnQuantity = 1;
    public float $returnMaxQuantity = 1;
    public string $returnUnit = 'pcs';
    public string $returnReason = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function switchTab(string $tab)
    {
        $this->activeTab = in_array($tab, ['stok', 'gudang', 'pemakaian', 'permintaan'], true) ? $tab : 'stok';
        $this->resetPage();
    }

    /**
     * Buka Modal Permintaan Barang ke Gudang (Requisition)
     */
    public function openRequestModal(?int $itemId = null)
    {
        $this->reqItemId = $itemId;
        $this->reqQuantity = 1;
        $this->reqReason = '';
        $this->reqWorkOrderType = 'stock_topup';

        if ($itemId) {
            $item = Item::find($itemId);
            if ($item) {
                $this->reqMaterialName = $item->name;
                $this->reqUnit = $item->unit ?: 'pcs';
            }
        } else {
            $this->reqMaterialName = '';
            $this->reqUnit = 'pcs';
        }

        $this->showRequestModal = true;
    }

    /**
     * Submit Permintaan Barang Resmi (Requisition) ke Admin Gudang
     */
    public function submitMaterialRequest()
    {
        $this->validate([
            'reqItemId' => 'required|integer|exists:items,id',
            'reqQuantity' => 'required|integer|min:1',
            'reqReason' => 'nullable|string|max:500',
            'reqWorkOrderType' => 'required|string|in:stock_topup,psb,maintenance',
        ]);

        $user = Auth::user();

        try {
            DB::transaction(function () use ($user) {
                $warehouse = Warehouse::where('branch_id', $user->branch_id)->where('is_active', true)->first()
                    ?? Warehouse::where('is_active', true)->first();

                if (!$warehouse) {
                    throw new \RuntimeException('Tidak ada gudang aktif yang terdaftar di sistem.');
                }

                $item = Item::findOrFail($this->reqItemId);

                $requisition = InventoryRequisition::create([
                    'uuid' => (string) Str::uuid(),
                    'requester_id' => $user->id,
                    'requester_type' => 'technician',
                    'warehouse_id' => $warehouse->id,
                    'branch_id' => $user->branch_id,
                    'distribution_method' => 'direct_technician',
                    'designated_technician_id' => $user->id,
                    'work_order_type' => $this->reqWorkOrderType,
                    'status' => 'pending',
                    'notes' => $this->reqReason ?: 'Permintaan material teknisi via Portal Teknisi',
                ]);

                InventoryRequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'item_id' => $item->id,
                    'quantity_requested' => $this->reqQuantity,
                    'quantity_approved' => 0,
                    'quantity_dispatched' => 0,
                    'notes' => $this->reqReason ?: "Permintaan {$item->name}",
                ]);
            });

            session()->flash('success', "Permintaan barang \"{$this->reqMaterialName}\" ({$this->reqQuantity} {$this->reqUnit}) berhasil diajukan! Menunggu persetujuan Admin Gudang.");
            $this->showRequestModal = false;
            $this->activeTab = 'permintaan';
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal mengajukan permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Buka Modal Catat Pemakaian Barang dari Custody Teknisi
     */
    public function openRecordUsageModal(int $custodyId)
    {
        $custody = InventoryCustody::with(['item', 'itemSerial'])->find($custodyId);
        if (!$custody) return;

        $this->selectedCustodyId = $custody->id;
        $this->usageMaterialName = $custody->item?->name ?? 'Material #' . $custody->item_id;
        $this->usageSerial = $custody->itemSerial?->serial_number ?? '';
        $this->usageMaxQuantity = (float) $custody->available_quantity;
        $this->usageQuantity = 1;
        $this->usageUnit = $custody->item?->unit ?: 'pcs';
        $this->usageTicketId = null;
        $this->usageNotes = '';
        $this->showUsageModal = true;
    }

    /**
     * Simpan Pemakaian Material Lapangan
     */
    public function saveUsage()
    {
        $this->validate([
            'selectedCustodyId' => 'required|integer|exists:inventory_custodies,id',
            'usageQuantity' => 'required|numeric|min:0.01',
            'usageNotes' => 'nullable|string|max:500',
            'usageTicketId' => 'nullable|integer|exists:support_tickets,id',
        ]);

        $user = Auth::user();

        try {
            DB::transaction(function () use ($user) {
                $custody = InventoryCustody::with(['item', 'itemSerial'])
                    ->where('id', $this->selectedCustodyId)
                    ->where('holder_id', $user->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $avail = (float) $custody->available_quantity;
                if ($this->usageQuantity > $avail) {
                    throw new \RuntimeException("Jumlah melebihi sisa barang di bagasi Anda (Tersedia: {$avail} {$custody->item?->unit}).");
                }

                $usage = InventoryUsage::create([
                    'technician_id' => $user->id,
                    'customer_id' => $this->usageTicketId ? Ticket::find($this->usageTicketId)?->customer_id : null,
                    'ticket_id' => $this->usageTicketId ?: null,
                    'work_order_type' => 'maintenance',
                    'usage_date' => now()->toDateString(),
                    'branch_id' => $user->branch_id,
                    'notes' => trim($this->usageNotes) ?: 'Pemakaian lapangan oleh teknisi via Portal Teknisi',
                ]);

                $newUsed = (float) $custody->used_quantity + (float) $this->usageQuantity;
                $newAvail = max(0, (float) $custody->quantity - $newUsed - (float) $custody->returned_quantity);

                $custody->update([
                    'used_quantity' => $newUsed,
                    'status' => ($newAvail <= 0) ? 'used' : 'in_custody',
                    'ticket_id' => $this->usageTicketId ?: $custody->ticket_id,
                ]);

                if ($custody->item_serial_id) {
                    $serial = ItemSerial::find($custody->item_serial_id);
                    if ($serial) {
                        $serial->update([
                            'status' => 'installed',
                            'ticket_id' => $this->usageTicketId ?: $serial->ticket_id,
                            'customer_id' => $usage->customer_id ?: $serial->customer_id,
                            'updated_by' => $user->id,
                        ]);
                    }
                }

                InventoryUsageItem::create([
                    'usage_id' => $usage->id,
                    'custody_id' => $custody->id,
                    'item_id' => $custody->item_id,
                    'item_serial_id' => $custody->item_serial_id,
                    'quantity' => $this->usageQuantity,
                    'notes' => $this->usageNotes ?: null,
                ]);
            });

            session()->flash('success', "Pemakaian {$this->usageQuantity} {$this->usageUnit} \"{$this->usageMaterialName}\" berhasil dicatat dan stok bagasi telah terpotong.");
            $this->showUsageModal = false;
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    /**
     * Buka Modal Retur / Pengembalian Barang
     */
    public function openReturnModal(int $custodyId, string $action = 'retur')
    {
        $custody = InventoryCustody::with(['item', 'itemSerial'])->find($custodyId);
        if (!$custody) return;

        $this->returnCustodyId = $custody->id;
        $this->returnAction = in_array($action, ['retur', 'rusak', 'hilang'], true) ? $action : 'retur';
        $this->returnMaterialName = $custody->item?->name ?? 'Material #' . $custody->item_id;
        $this->returnSerial = $custody->itemSerial?->serial_number ?? '';
        $this->returnMaxQuantity = (float) $custody->available_quantity;
        $this->returnQuantity = 1;
        $this->returnUnit = $custody->item?->unit ?: 'pcs';
        $this->returnReason = '';
        $this->showReturnModal = true;
    }

    /**
     * Submit Pengembalian Barang (Return) ke Gudang
     */
    public function submitReturn()
    {
        $this->validate([
            'returnCustodyId' => 'required|integer|exists:inventory_custodies,id',
            'returnQuantity' => 'required|numeric|min:0.01',
            'returnAction' => 'required|string|in:retur,rusak,hilang',
            'returnReason' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        try {
            $returnDoc = null;
            DB::transaction(function () use ($user, &$returnDoc) {
                $custody = InventoryCustody::with(['item', 'itemSerial'])
                    ->where('id', $this->returnCustodyId)
                    ->where('holder_id', $user->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $avail = (float) $custody->available_quantity;
                if ($this->returnQuantity > $avail) {
                    throw new \RuntimeException("Jumlah retur melebihi sisa barang di bagasi Anda (Tersedia: {$avail} {$custody->item?->unit}).");
                }

                $warehouse = Warehouse::where('branch_id', $user->branch_id)->where('is_active', true)->first()
                    ?? Warehouse::where('is_active', true)->first();

                $returnDoc = InventoryReturn::create([
                    'returner_id' => $user->id,
                    'returner_type' => 'technician',
                    'warehouse_id' => $warehouse ? $warehouse->id : 1,
                    'branch_id' => $user->branch_id,
                    'status' => 'pending',
                    'notes' => "Pengembalian barang oleh {$user->name}: " . ($this->returnReason ?: 'Selesai tugas/kelebihan stok bagasi'),
                ]);

                InventoryReturnItem::create([
                    'return_id' => $returnDoc->id,
                    'custody_id' => $custody->id,
                    'item_id' => $custody->item_id,
                    'item_serial_id' => $custody->item_serial_id,
                    'quantity' => $this->returnQuantity,
                    'condition' => match($this->returnAction) {
                        'retur' => 'good',
                        'rusak' => 'damaged',
                        'hilang' => 'unusable',
                        default => 'good',
                    },
                    'action_taken' => match($this->returnAction) {
                        'retur' => 'restock',
                        'rusak' => 'repair',
                        'hilang' => 'scrap',
                        default => 'restock',
                    },
                    'qc_notes' => $this->returnReason ?: null,
                ]);

                $newReturned = (float) $custody->returned_quantity + (float) $this->returnQuantity;
                $newAvail = max(0, (float) $custody->quantity - (float) $custody->used_quantity - $newReturned);

                $custody->update([
                    'returned_quantity' => $newReturned,
                    'status' => ($newAvail <= 0) ? 'returned' : 'in_custody',
                ]);
            });

            $label = match($this->returnAction) {
                'retur' => 'Pengembalian barang ke gudang',
                'rusak' => 'Laporan barang rusak',
                'hilang' => 'Laporan barang hilang',
            };

            session()->flash('success', "{$label} ({$this->returnQuantity} {$this->returnUnit} \"{$this->returnMaterialName}\") berhasil diajukan dengan No. {$returnDoc->return_number}! Silakan serahkan fisik barang ke Admin Gudang.");
            $this->showReturnModal = false;
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $user = Auth::user();
        $q = trim($this->search);

        // 1. DATA STOK BAGASI SAYA (Real Enterprise Custody)
        $custodiesQuery = InventoryCustody::with(['item', 'itemSerial'])
            ->where('holder_id', $user->id)
            ->whereRaw('(quantity - used_quantity - returned_quantity) > 0')
            ->when(!empty($q), function ($b) use ($q) {
                $b->where(function ($sub) use ($q) {
                    $sub->whereHas('item', function ($iq) use ($q) {
                        $iq->where('name', 'like', "%{$q}%")
                           ->orWhere('sku', 'like', "%{$q}%")
                           ->orWhere('category', 'like', "%{$q}%");
                    })->orWhereHas('itemSerial', function ($sq) use ($q) {
                        $sq->where('serial_number', 'like', "%{$q}%");
                    });
                });
            })
            ->latest('id');

        $materials = $custodiesQuery->get();

        // 2. DATA KETERSEDIAAN GUDANG KANTOR (Real Enterprise Stocks & Catalog)
        $activeWarehouse = Warehouse::where('branch_id', $user->branch_id)->where('is_active', true)->first()
            ?? Warehouse::where('is_active', true)->first();

        $warehouseId = $activeWarehouse?->id;

        $itemsQuery = Item::query()
            ->where('is_active', true)
            ->when(!empty($q), function ($b) use ($q) {
                $b->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                       ->orWhere('sku', 'like', "%{$q}%")
                       ->orWhere('category', 'like', "%{$q}%")
                       ->orWhere('brand', 'like', "%{$q}%");
                });
            })
            ->orderBy('category')
            ->orderBy('name');

        $rawItems = $itemsQuery->get();

        // Hitung real stock per item di gudang ini
        $warehouseMaterials = $rawItems->map(function ($item) use ($warehouseId, $activeWarehouse) {
            $stockQty = 0.0;
            if ($warehouseId) {
                if ($item->is_serialized) {
                    $serialCount = (float) ItemSerial::where('item_id', $item->id)
                        ->where('warehouse_id', $warehouseId)
                        ->where('status', 'in_warehouse')
                        ->count();
                    $stk = InventoryStock::where('warehouse_id', $warehouseId)->where('item_id', $item->id)->first();
                    $stockQty = max((float) ($stk?->quantity ?? 0.0), $serialCount);
                } else {
                    $stk = InventoryStock::where('warehouse_id', $warehouseId)->where('item_id', $item->id)->first();
                    $stockQty = (float) ($stk?->quantity ?? 0.0);
                }
            }

            return (object) [
                'id' => $item->id,
                'sku' => $item->sku,
                'name' => $item->name,
                'category' => $item->category ?? 'Material',
                'unit' => $item->unit ?: 'pcs',
                'stock' => $stockQty,
                'min_stock' => $item->min_stock ?? 5,
                'location' => $activeWarehouse?->name ?? 'Gudang Utama',
                'is_serialized' => (bool) $item->is_serialized,
            ];
        });

        // 3. RIWAYAT PEMAKAIAN SAYA (Real InventoryUsage)
        $myUsages = InventoryUsage::with(['items.item', 'items.itemSerial', 'ticket.customer'])
            ->where('technician_id', $user->id)
            ->when(!empty($q), function ($b) use ($q) {
                $b->where(function ($sub) use ($q) {
                    $sub->where('usage_number', 'like', "%{$q}%")
                       ->orWhere('notes', 'like', "%{$q}%")
                       ->orWhereHas('items.item', function ($iq) use ($q) {
                           $iq->where('name', 'like', "%{$q}%");
                       });
                });
            })
            ->latest('id')
            ->paginate(15);

        // 4. RIWAYAT PERMINTAAN SAYA (Real InventoryRequisition)
        $myRequisitions = InventoryRequisition::with(['items.item', 'warehouse'])
            ->where('requester_id', $user->id)
            ->when(!empty($q), function ($b) use ($q) {
                $b->where(function ($sub) use ($q) {
                    $sub->where('requisition_number', 'like', "%{$q}%")
                       ->orWhere('notes', 'like', "%{$q}%");
                });
            })
            ->latest('id')
            ->paginate(15);

        // Tiket Aktif Teknisi untuk Dropdown Pemakaian
        $activeTickets = Ticket::withoutGlobalScopes()
            ->where(function ($sq) use ($user) {
                $sq->where('assigned_to', $user->id)
                   ->orWhereNull('assigned_to');
            })
            ->whereIn('status', ['open', 'in_progress'])
            ->latest('id')
            ->take(20)
            ->get();

        return view('livewire.isp.technician.material.index', [
            'materials' => $materials,
            'warehouseMaterials' => $warehouseMaterials,
            'activeWarehouse' => $activeWarehouse,
            'myUsages' => $myUsages,
            'myRequisitions' => $myRequisitions,
            'activeTickets' => $activeTickets,
            'activeTab' => $this->activeTab,
            'search' => $this->search,
            'showRequestModal' => $this->showRequestModal,
            'reqItemId' => $this->reqItemId,
            'reqMaterialName' => $this->reqMaterialName,
            'reqQuantity' => $this->reqQuantity,
            'reqUnit' => $this->reqUnit,
            'reqReason' => $this->reqReason,
            'reqWorkOrderType' => $this->reqWorkOrderType,
            'showUsageModal' => $this->showUsageModal,
            'selectedCustodyId' => $this->selectedCustodyId,
            'usageMaterialName' => $this->usageMaterialName,
            'usageSerial' => $this->usageSerial,
            'usageQuantity' => $this->usageQuantity,
            'usageMaxQuantity' => $this->usageMaxQuantity,
            'usageUnit' => $this->usageUnit,
            'usageTicketId' => $this->usageTicketId,
            'usageNotes' => $this->usageNotes,
            'showReturnModal' => $this->showReturnModal,
            'returnCustodyId' => $this->returnCustodyId,
            'returnAction' => $this->returnAction,
            'returnMaterialName' => $this->returnMaterialName,
            'returnSerial' => $this->returnSerial,
            'returnQuantity' => $this->returnQuantity,
            'returnMaxQuantity' => $this->returnMaxQuantity,
            'returnUnit' => $this->returnUnit,
            'returnReason' => $this->returnReason,
        ]);
    }
}
