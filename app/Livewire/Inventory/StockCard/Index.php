<?php

namespace App\Livewire\Inventory\StockCard;

use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\InventoryStockMutation;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $warehouseId = null;
    public ?int $itemId = null;
    public string $typeFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    protected $queryString = [
        'warehouseId' => ['except' => null],
        'itemId' => ['except' => null],
        'typeFilter' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function mount()
    {
        // Default range: awal bulan berjalan s/d hari ini
        if (empty($this->dateFrom)) {
            $this->dateFrom = date('Y-m-01');
        }
        if (empty($this->dateTo)) {
            $this->dateTo = date('Y-m-d');
        }

        // Default warehouse pertama
        if (empty($this->warehouseId)) {
            $firstWh = Warehouse::active()->first();
            $this->warehouseId = $firstWh?->id;
        }

        // Default item pertama
        if (empty($this->itemId)) {
            $firstItem = Item::active()->first();
            $this->itemId = $firstItem?->id;
        }
    }

    public function updatedWarehouseId()
    {
        $this->resetPage();
    }

    public function updatedItemId()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function render()
    {
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $items = Item::active()->orderBy('name')->get();

        $selectedWarehouse = $this->warehouseId ? Warehouse::find($this->warehouseId) : null;
        $selectedItem = $this->itemId ? Item::find($this->itemId) : null;

        $openingBalance = 0.0;
        $totalIn = 0.0;
        $totalOut = 0.0;
        $currentPhysicalStock = 0.0;

        $mutations = collect();

        if ($this->warehouseId && $this->itemId) {
            // Saldo Awal: Total mutasi sebelum dateFrom
            $openingIn = (float) InventoryStockMutation::where('warehouse_id', $this->warehouseId)
                ->where('item_id', $this->itemId)
                ->whereDate('created_at', '<', $this->dateFrom)
                ->sum('qty_in');

            $openingOut = (float) InventoryStockMutation::where('warehouse_id', $this->warehouseId)
                ->where('item_id', $this->itemId)
                ->whereDate('created_at', '<', $this->dateFrom)
                ->sum('qty_out');

            $openingBalance = $openingIn - $openingOut;

            // Total mutasi dalam periode
            $periodQuery = InventoryStockMutation::where('warehouse_id', $this->warehouseId)
                ->where('item_id', $this->itemId)
                ->whereDate('created_at', '>=', $this->dateFrom)
                ->whereDate('created_at', '<=', $this->dateTo);

            $totalIn = (float) (clone $periodQuery)->sum('qty_in');
            $totalOut = (float) (clone $periodQuery)->sum('qty_out');

            // Saldo fisik terkini di database
            $stockRecord = InventoryStock::where('warehouse_id', $this->warehouseId)
                ->where('item_id', $this->itemId)
                ->first();
            $currentPhysicalStock = $stockRecord ? (float) $stockRecord->quantity : 0.0;

            // Query daftar mutasi untuk tabel (urut kronologis)
            $mutQuery = InventoryStockMutation::with(['actor', 'itemSerial'])
                ->where('warehouse_id', $this->warehouseId)
                ->where('item_id', $this->itemId)
                ->whereDate('created_at', '>=', $this->dateFrom)
                ->whereDate('created_at', '<=', $this->dateTo);

            if (!empty($this->typeFilter)) {
                $mutQuery->where('type', $this->typeFilter);
            }

            $mutations = $mutQuery->orderBy('created_at', 'asc')->orderBy('id', 'asc')->paginate(25);
        }

        return view('livewire.inventory.stock-card.index', [
            'warehouses' => $warehouses,
            'items' => $items,
            'selectedWarehouse' => $selectedWarehouse,
            'selectedItem' => $selectedItem,
            'openingBalance' => $openingBalance,
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'currentPhysicalStock' => $currentPhysicalStock,
            'mutations' => $mutations,
        ])->layout('layouts.app', ['title' => 'Kartu Stok Material - dsBilling']);
    }
}
