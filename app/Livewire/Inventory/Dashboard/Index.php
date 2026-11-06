<?php

namespace App\Livewire\Inventory\Dashboard;

use App\Models\Inventory\InventoryCustody;
use App\Models\Inventory\InventoryRequisition;
use App\Models\Inventory\InventoryReturn;
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

    public $warehouseId = '';
    public $branchId = '';

    public function mount()
    {
        $user = auth()->user();
        if ($user && $user->branch_id) {
            $this->branchId = $user->branch_id;
        }
    }

    public function render()
    {
        $whQuery = Warehouse::query()->where('is_active', true);
        if ($this->branchId) {
            $whQuery->where('branch_id', $this->branchId);
        }
        $warehouses = $whQuery->orderBy('name')->get();

        // 1. Total SKU
        $totalItems = Item::query()->where('is_active', true)->count();

        // 2. Total Stok Gudang
        $stockQuery = InventoryStock::query();
        if ($this->warehouseId) {
            $stockQuery->where('warehouse_id', $this->warehouseId);
        } elseif ($this->branchId) {
            $stockQuery->where('branch_id', $this->branchId);
        }
        $totalWarehouseStock = (float) $stockQuery->sum('quantity');

        // 3. Total Custody (dipegang teknisi & reseller)
        $custodyQuery = InventoryCustody::query()->whereIn('status', ['in_custody', 'accepted', 'in_transit']);
        if ($this->branchId) {
            $custodyQuery->where('branch_id', $this->branchId);
        }
        $totalInCustody = (float) $custodyQuery->sum('available_quantity');

        // 4. Low stock items
        $lowStockQuery = Item::query()->where('is_active', true)
            ->whereHas('stocks', function ($q) {
                if ($this->warehouseId) {
                    $q->where('warehouse_id', $this->warehouseId);
                } elseif ($this->branchId) {
                    $q->where('branch_id', $this->branchId);
                }
            })
            ->withSum(['stocks' => function ($q) {
                if ($this->warehouseId) {
                    $q->where('warehouse_id', $this->warehouseId);
                } elseif ($this->branchId) {
                    $q->where('branch_id', $this->branchId);
                }
            }], 'quantity')
            ->get()
            ->filter(function ($item) {
                return (float) ($item->stocks_sum_quantity ?? 0) <= (float) $item->min_stock;
            });

        // 5. Pending Requisitions & Returns
        $pendingRequisitions = InventoryRequisition::query()->where('status', 'pending');
        if ($this->branchId) {
            $pendingRequisitions->where('branch_id', $this->branchId);
        }
        $pendingReqCount = $pendingRequisitions->count();

        $pendingReturns = InventoryReturn::query()->where('status', 'pending_qc');
        if ($this->branchId) {
            $pendingReturns->where('branch_id', $this->branchId);
        }
        $pendingReturnCount = $pendingReturns->count();

        // 6. Mutasi Terakhir
        $mutationsQuery = InventoryStockMutation::query()->with(['warehouse', 'item', 'actor'])
            ->latest('id');
        if ($this->warehouseId) {
            $mutationsQuery->where('warehouse_id', $this->warehouseId);
        } elseif ($this->branchId) {
            $mutationsQuery->where('branch_id', $this->branchId);
        }
        $recentMutations = $mutationsQuery->limit(10)->get();

        // 7. Estimasi Nilai Stok
        $totalStockValue = InventoryStock::query()
            ->join('items', 'inventory_stocks.item_id', '=', 'items.id')
            ->when($this->warehouseId, fn($q) => $q->where('inventory_stocks.warehouse_id', $this->warehouseId))
            ->when(!$this->warehouseId && $this->branchId, fn($q) => $q->where('inventory_stocks.branch_id', $this->branchId))
            ->selectRaw('SUM(inventory_stocks.quantity * COALESCE(items.cost_price, 0)) as total_val')
            ->value('total_val') ?? 0;

        $branches = Branch::orderBy('name')->get();

        return view('livewire.inventory.dashboard.index', [
            'warehouses' => $warehouses,
            'branches' => $branches,
            'totalItems' => $totalItems,
            'totalWarehouseStock' => $totalWarehouseStock,
            'totalInCustody' => $totalInCustody,
            'lowStockCount' => $lowStockQuery->count(),
            'lowStockItems' => $lowStockQuery->take(5),
            'pendingReqCount' => $pendingReqCount,
            'pendingReturnCount' => $pendingReturnCount,
            'recentMutations' => $recentMutations,
            'totalStockValue' => $totalStockValue,
        ])->layout('layouts.app', ['title' => 'Dashboard Stok & Inventaris - dsBilling']);
    }
}
