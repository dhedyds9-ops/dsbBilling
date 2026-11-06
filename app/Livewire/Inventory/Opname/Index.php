<?php

namespace App\Livewire\Inventory\Opname;

use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\InventoryStockOpname;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Services\Inventory\StockMutationService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $warehouseFilter = '';
    public $statusFilter = '';

    // Modal Create / Edit
    public $showCreateModal = false;
    public $selectedWarehouseId = '';
    public $opnameDate = '';
    public $notes = '';
    public $itemsData = [];

    // Modal Detail
    public $showDetailModal = false;
    public $selectedOpname = null;

    protected $rules = [
        'selectedWarehouseId' => 'required|exists:warehouses,id',
        'opnameDate' => 'required|date',
        'itemsData' => 'required|array|min:1',
        'itemsData.*.item_id' => 'required|exists:items,id',
        'itemsData.*.physical_quantity' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        $this->opnameDate = date('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->reset(['selectedWarehouseId', 'notes', 'itemsData']);
        $this->opnameDate = date('Y-m-d');
        $this->showCreateModal = true;
    }

    public function loadWarehouseItems()
    {
        if (!$this->selectedWarehouseId) {
            $this->itemsData = [];
            return;
        }

        $warehouse = Warehouse::find($this->selectedWarehouseId);
        if (!$warehouse) {
            return;
        }

        // Ambil semua item aktif beserta stok saat ini di gudang terpilih
        $items = Item::where('is_active', true)
            ->with(['stocks' => function ($q) {
                $q->where('warehouse_id', $this->selectedWarehouseId);
            }])
            ->get();

        $this->itemsData = [];
        foreach ($items as $item) {
            $stock = $item->stocks->first();
            $systemQty = $stock ? (float) $stock->quantity : 0.0;

            $this->itemsData[] = [
                'item_id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'unit' => $item->unit,
                'system_quantity' => $systemQty,
                'physical_quantity' => $systemQty, // default sama dengan sistem
                'difference_quantity' => 0.0,
                'adjustment_reason' => 'selisih_hitung',
                'notes' => '',
            ];
        }
    }

    public function updatePhysicalQuantity($index)
    {
        if (isset($this->itemsData[$index])) {
            $sys = (float) ($this->itemsData[$index]['system_quantity'] ?? 0);
            $phy = (float) ($this->itemsData[$index]['physical_quantity'] ?? 0);
            $this->itemsData[$index]['difference_quantity'] = $phy - $sys;
        }
    }

    public function saveOpname()
    {
        $this->validate();

        $wh = Warehouse::findOrFail($this->selectedWarehouseId);

        $opname = InventoryStockOpname::create([
            'warehouse_id' => $wh->id,
            'branch_id' => $wh->branch_id,
            'status' => 'draft',
            'opname_date' => $this->opnameDate,
            'notes' => $this->notes,
            'created_by' => auth()->id(),
        ]);

        foreach ($this->itemsData as $row) {
            $sys = (float) $row['system_quantity'];
            $phy = (float) $row['physical_quantity'];
            $diff = $phy - $sys;

            $opname->items()->create([
                'item_id' => $row['item_id'],
                'system_quantity' => $sys,
                'physical_quantity' => $phy,
                'difference_quantity' => $diff,
                'adjustment_reason' => $diff != 0 ? ($row['adjustment_reason'] ?? 'selisih_hitung') : null,
                'notes' => $row['notes'] ?? null,
            ]);
        }

        $this->showCreateModal = false;
        session()->flash('success', "Sesi Stock Opname {$opname->opname_number} berhasil dibuat sebagai draft.");
    }

    public function approveAndAdjust($opnameId)
    {
        $opname = InventoryStockOpname::with('items')->findOrFail($opnameId);

        if ($opname->status === 'completed') {
            session()->flash('error', 'Sesi stock opname ini sudah selesai dieksekusi.');
            return;
        }

        $mutationService = app(StockMutationService::class);

        \DB::transaction(function () use ($opname, $mutationService) {
            foreach ($opname->items as $item) {
                if ((float) $item->difference_quantity != 0) {
                    $mutationService->recordAdjustment(
                        warehouseId: $opname->warehouse_id,
                        itemId: $item->item_id,
                        physicalQty: (float) $item->physical_quantity,
                        reason: $item->adjustment_reason ?? 'Penyesuaian Opname',
                        referenceId: $opname->id,
                        actorId: auth()->id()
                    );
                }
            }

            $opname->update([
                'status' => 'completed',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        session()->flash('success', "Stock Opname {$opname->opname_number} disetujui & penyesuaian stok berhasil dicatat.");
    }

    public function viewDetail($opnameId)
    {
        $this->selectedOpname = InventoryStockOpname::with(['warehouse', 'creator', 'approver', 'items.item'])
            ->findOrFail($opnameId);
        $this->showDetailModal = true;
    }

    public function render()
    {
        $query = InventoryStockOpname::with(['warehouse', 'creator', 'items'])
            ->latest('id');

        if ($this->search) {
            $query->where('opname_number', 'like', "%{$this->search}%");
        }
        if ($this->warehouseFilter) {
            $query->where('warehouse_id', $this->warehouseFilter);
        }
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $opnames = $query->paginate(10);
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('livewire.inventory.opname.index', [
            'opnames' => $opnames,
            'warehouses' => $warehouses,
        ]);
    }
}
