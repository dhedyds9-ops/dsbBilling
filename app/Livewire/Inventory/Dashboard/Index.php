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
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $warehouseId = '';
    public $branchId = '';

    public function mount()
    {
        try {
            $user = auth()->user();
            if ($user && $user->branch_id) {
                $this->branchId = $user->branch_id;
            }
        } catch (\Throwable $e) {
            Log::warning('Inventory Dashboard mount error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // 0. Warehouses & Branches
        $warehouses = collect();
        $branches = collect();
        try {
            $whQuery = Warehouse::query()->where('is_active', true);
            if ($this->branchId) {
                $whQuery->where('branch_id', $this->branchId);
            }
            $warehouses = $whQuery->orderBy('name')->get();
        } catch (\Throwable $e) {
            Log::warning('Error loading warehouses in dashboard: ' . $e->getMessage());
        }

        try {
            $branches = Branch::where('is_active', true)->orderBy('name')->get();
        } catch (\Throwable $e) {
            try {
                $branches = Branch::orderBy('name')->get();
            } catch (\Throwable) {
                $branches = collect();
            }
        }

        // 1. Total SKU
        $totalItems = 0;
        try {
            $totalItems = Item::query()->where('is_active', true)->count();
        } catch (\Throwable $e) {
            Log::warning('Error counting items: ' . $e->getMessage());
        }

        // 2. Total Stok Gudang
        $totalWarehouseStock = 0.0;
        try {
            $stockQuery = InventoryStock::query();
            if ($this->warehouseId) {
                $stockQuery->where('warehouse_id', $this->warehouseId);
            } elseif ($this->branchId) {
                $stockQuery->where('branch_id', $this->branchId);
            }
            $totalWarehouseStock = (float) $stockQuery->sum('quantity');
        } catch (\Throwable $e) {
            Log::warning('Error summing warehouse stock: ' . $e->getMessage());
        }

        // 3. Total Custody (dipegang teknisi & reseller)
        $totalInCustody = 0.0;
        try {
            $custodyQuery = InventoryCustody::query()->whereIn('status', ['in_custody', 'accepted', 'in_transit']);
            if ($this->branchId) {
                $custodyQuery->where('branch_id', $this->branchId);
            }
            $totalInCustody = (float) $custodyQuery->sum('available_quantity');
        } catch (\Throwable $e) {
            Log::warning('Error summing custody stock: ' . $e->getMessage());
        }

        // 4. Low stock items
        $lowStockItems = collect();
        $lowStockCount = 0;
        try {
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
            $lowStockCount = $lowStockQuery->count();
            $lowStockItems = $lowStockQuery->take(5);
        } catch (\Throwable $e) {
            Log::warning('Error querying low stock: ' . $e->getMessage());
        }

        // 5. Pending Requisitions & Returns
        $pendingReqCount = 0;
        try {
            $pendingRequisitions = InventoryRequisition::query()->where('status', 'pending');
            if ($this->branchId) {
                $pendingRequisitions->where('branch_id', $this->branchId);
            }
            $pendingReqCount = $pendingRequisitions->count();
        } catch (\Throwable $e) {
            Log::warning('Error counting requisitions: ' . $e->getMessage());
        }

        $pendingReturnCount = 0;
        try {
            $pendingReturns = InventoryReturn::query()->where('status', 'pending_qc');
            if ($this->branchId) {
                $pendingReturns->where('branch_id', $this->branchId);
            }
            $pendingReturnCount = $pendingReturns->count();
        } catch (\Throwable $e) {
            Log::warning('Error counting returns: ' . $e->getMessage());
        }

        // 6. Mutasi Terakhir
        $recentMutations = collect();
        try {
            $mutationsQuery = InventoryStockMutation::query()->with(['warehouse', 'item', 'actor'])
                ->latest('id');
            if ($this->warehouseId) {
                $mutationsQuery->where('warehouse_id', $this->warehouseId);
            } elseif ($this->branchId) {
                $mutationsQuery->where('branch_id', $this->branchId);
            }
            $recentMutations = $mutationsQuery->limit(10)->get();
        } catch (\Throwable $e) {
            Log::warning('Error getting mutations: ' . $e->getMessage());
        }

        // 7. Estimasi Nilai Stok
        $totalStockValue = 0.0;
        try {
            $totalStockValue = (float) (InventoryStock::query()
                ->withoutGlobalScopes()
                ->join('items', 'inventory_stocks.item_id', '=', 'items.id')
                ->whereNull('items.deleted_at')
                ->when($this->warehouseId, fn($q) => $q->where('inventory_stocks.warehouse_id', $this->warehouseId))
                ->when(!$this->warehouseId && $this->branchId, fn($q) => $q->where('inventory_stocks.branch_id', $this->branchId))
                ->selectRaw('SUM(inventory_stocks.quantity * COALESCE(items.cost_price, 0)) as total_val')
                ->value('total_val') ?? 0);
        } catch (\Throwable $e) {
            Log::warning('Error calculating stock value: ' . $e->getMessage());
        }

        return view('livewire.inventory.dashboard.index', [
            'warehouses' => $warehouses,
            'branches' => $branches,
            'totalItems' => $totalItems,
            'totalWarehouseStock' => $totalWarehouseStock,
            'totalInCustody' => $totalInCustody,
            'lowStockCount' => $lowStockCount,
            'lowStockItems' => $lowStockItems,
            'pendingReqCount' => $pendingReqCount,
            'pendingReturnCount' => $pendingReturnCount,
            'recentMutations' => $recentMutations,
            'totalStockValue' => $totalStockValue,
        ])->layout('layouts.app', ['title' => 'Dashboard Stok & Inventaris - dsBilling']);
    }
}
