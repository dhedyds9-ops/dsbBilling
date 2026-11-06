<?php

namespace App\Livewire\Inventory\Reports;

use App\Models\Inventory\InventoryCustody;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\InventoryStockMutation;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Models\Master\Branch;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $activeTab = 'stock_position'; // stock_position, mutations, custody

    // Filters for Stock Position
    public $warehouseId = '';
    public $categoryFilter = '';

    // Filters for Mutations
    public $startDate = '';
    public $endDate = '';
    public $mutationWarehouseId = '';
    public $mutationType = '';

    // Filters for Custody
    public $custodyHolderType = ''; // technician, reseller
    public $custodyStatus = ''; // in_custody, used, returned

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function render()
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $categories = Item::distinct()->pluck('category')->filter()->values();

        // 1. Laporan Posisi Stok
        $stockData = [];
        if ($this->activeTab === 'stock_position') {
            $stockQuery = InventoryStock::with(['item', 'warehouse'])
                ->when($this->warehouseId, fn($q) => $q->where('warehouse_id', $this->warehouseId))
                ->when($this->categoryFilter, fn($q) => $q->whereHas('item', fn($i) => $i->where('category', $this->categoryFilter)))
                ->paginate(15);
            $stockData = $stockQuery;
        }

        // 2. Laporan Mutasi
        $mutationData = [];
        if ($this->activeTab === 'mutations') {
            $mutQuery = InventoryStockMutation::with(['warehouse', 'item', 'actor'])
                ->when($this->startDate, fn($q) => $q->whereDate('created_at', '>=', $this->startDate))
                ->when($this->endDate, fn($q) => $q->whereDate('created_at', '<=', $this->endDate))
                ->when($this->mutationWarehouseId, fn($q) => $q->where('warehouse_id', $this->mutationWarehouseId))
                ->when($this->mutationType, fn($q) => $q->where('type', $this->mutationType))
                ->latest('id')
                ->paginate(15);
            $mutationData = $mutQuery;
        }

        // 3. Laporan Custody Tim
        $custodyData = [];
        if ($this->activeTab === 'custody') {
            $custQuery = InventoryCustody::with(['item', 'holder', 'branch'])
                ->when($this->custodyHolderType, fn($q) => $q->where('holder_type', $this->custodyHolderType))
                ->when($this->custodyStatus, fn($q) => $q->where('status', $this->custodyStatus))
                ->latest('id')
                ->paginate(15);
            $custodyData = $custQuery;
        }

        return view('livewire.inventory.reports.index', [
            'warehouses' => $warehouses,
            'categories' => $categories,
            'stockData' => $stockData,
            'mutationData' => $mutationData,
            'custodyData' => $custodyData,
        ])->layout('layouts.app', ['title' => 'Laporan Inventaris - dsBilling']);
    }
}
