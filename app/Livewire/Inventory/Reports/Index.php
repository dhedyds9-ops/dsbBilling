<?php

namespace App\Livewire\Inventory\Reports;

use App\Models\Inventory\InventoryCustody;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\InventoryStockMutation;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Models\Master\Branch;
use App\Services\Auth\UserQueryService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $activeTab = 'stock_position'; // stock_position, mutations, custody, reseller_usage

    public string|int $perPage = 20;

    protected $queryString = [
        'activeTab' => ['except' => 'stock_position'],
        'perPage' => ['except' => 20],
        'page' => ['except' => 1],
    ];

    public function updatingPerPage()
    {
        $this->resetPage();
    }

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

    // Filters for Reseller Material Usage
    public $resellerIdFilter = '';
    public $resellerStartDate = '';
    public $resellerEndDate = '';
    public $resellerCategoryFilter = '';
    public $resellerSearch = '';
    public $priceBasis = 'cost_price'; // cost_price (HPP) atau selling_price (Harga Jual / Tagihan)

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');

        $this->resellerStartDate = now()->startOfMonth()->format('Y-m-d');
        $this->resellerEndDate = now()->format('Y-m-d');
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function exportExcel()
    {
        if ($this->activeTab === 'stock_position') {
            $filename = 'laporan-posisi-stok-' . date('Ymd-His') . '.xlsx';
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\Inventory\StockPositionExport($this->warehouseId, $this->categoryFilter),
                $filename
            );
        }

        if ($this->activeTab === 'custody') {
            $filename = 'laporan-custody-material-' . date('Ymd-His') . '.xlsx';
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\Inventory\CustodyExport('', '', $this->custodyStatus),
                $filename
            );
        }

        if ($this->activeTab === 'reseller_usage') {
            $filename = 'laporan-pemakaian-reseller-' . ($this->resellerStartDate ?: 'all') . '-sd-' . ($this->resellerEndDate ?: 'now') . '.xlsx';
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\Inventory\ResellerMaterialUsageExport(
                    $this->resellerIdFilter ? (int) $this->resellerIdFilter : null,
                    $this->resellerStartDate,
                    $this->resellerEndDate,
                    $this->priceBasis,
                    $this->resellerCategoryFilter
                ),
                $filename
            );
        }

        // mutations
        $whId = $this->mutationWarehouseId ?: Warehouse::where('is_active', true)->value('id');
        $itemId = Item::active()->value('id');
        $filename = 'laporan-mutasi-stok-' . date('Ymd-His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\Inventory\StockCardExport(
                $whId,
                $itemId,
                $this->mutationType,
                $this->startDate,
                $this->endDate
            ),
            $filename
        );
    }

    public function render()
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $categories = Item::distinct()->pluck('category')->filter()->values();
        $resellers = app(UserQueryService::class)->getResellers(false);

        // 1. Laporan Posisi Stok
        $stockData = [];
        if ($this->activeTab === 'stock_position') {
            $stockQuery = InventoryStock::with(['item', 'warehouse'])
                ->when($this->warehouseId, fn($q) => $q->where('warehouse_id', $this->warehouseId))
                ->when($this->categoryFilter, fn($q) => $q->whereHas('item', fn($i) => $i->filterCategory($this->categoryFilter)));

            $perPageInt = match ((string) $this->perPage) {
                '50' => 50,
                '100' => 100,
                'all' => max(1, (clone $stockQuery)->count()),
                default => 20,
            };

            $stockData = $stockQuery->paginate($perPageInt);
        }

        // 2. Laporan Mutasi
        $mutationData = [];
        if ($this->activeTab === 'mutations') {
            $mutQuery = InventoryStockMutation::with(['warehouse', 'item', 'actor'])
                ->when($this->startDate, fn($q) => $q->whereDate('created_at', '>=', $this->startDate))
                ->when($this->endDate, fn($q) => $q->whereDate('created_at', '<=', $this->endDate))
                ->when($this->mutationWarehouseId, fn($q) => $q->where('warehouse_id', $this->mutationWarehouseId))
                ->when($this->mutationType, fn($q) => $q->where('type', $this->mutationType))
                ->latest('id');

            $perPageInt = match ((string) $this->perPage) {
                '50' => 50,
                '100' => 100,
                'all' => max(1, (clone $mutQuery)->count()),
                default => 20,
            };

            $mutationData = $mutQuery->paginate($perPageInt);
        }

        // 3. Laporan Custody Tim
        $custodyData = [];
        if ($this->activeTab === 'custody') {
            $custQuery = InventoryCustody::with(['item', 'holder', 'branch'])
                ->when($this->custodyHolderType, fn($q) => $q->where('holder_type', $this->custodyHolderType))
                ->when($this->custodyStatus, fn($q) => $q->where('status', $this->custodyStatus))
                ->latest('id');

            $perPageInt = match ((string) $this->perPage) {
                '50' => 50,
                '100' => 100,
                'all' => max(1, (clone $custQuery)->count()),
                default => 20,
            };

            $custodyData = $custQuery->paginate($perPageInt);
        }

        // 4. Laporan Pemakaian Material Reseller & Finansial
        $resellerUsageData = [];
        $resellerSummary = [
            'total_diambil' => 0,
            'total_terpakai' => 0,
            'total_diretur' => 0,
            'total_sisa' => 0,
            'nominal_terpakai' => 0,
            'nominal_sisa' => 0,
            'nominal_total' => 0,
        ];

        if ($this->activeTab === 'reseller_usage') {
            $resellerQuery = InventoryCustody::with(['item', 'itemSerial', 'holder', 'customer'])
                ->where('holder_type', 'reseller')
                ->when($this->resellerIdFilter, fn($q) => $q->where('holder_id', $this->resellerIdFilter))
                ->when($this->resellerStartDate, fn($q) => $q->whereDate('created_at', '>=', $this->resellerStartDate))
                ->when($this->resellerEndDate, fn($q) => $q->whereDate('created_at', '<=', $this->resellerEndDate))
                ->when($this->resellerCategoryFilter, fn($q) => $q->whereHas('item', fn($i) => $i->where('category', $this->resellerCategoryFilter)))
                ->when($this->resellerSearch, function ($q) {
                    $s = '%' . trim($this->resellerSearch) . '%';
                    $q->where(function ($sub) use ($s) {
                        $sub->whereHas('item', fn($i) => $i->where('name', 'like', $s)->orWhere('sku', 'like', $s))
                            ->orWhereHas('holder', fn($h) => $h->where('name', 'like', $s))
                            ->orWhereHas('itemSerial', fn($is) => $is->where('serial_number', 'like', $s));
                    });
                })
                ->latest('id');

            // Hitung agregat KPI dari seluruh row yang terfilter
            $allRows = (clone $resellerQuery)->get();
            $basis = $this->priceBasis;

            foreach ($allRows as $r) {
                $price = (float) ($basis === 'selling_price' ? ($r->item?->selling_price ?? 0) : ($r->item?->cost_price ?? 0));
                $qty = (float) $r->quantity;
                $used = (float) $r->used_quantity;
                $ret = (float) $r->returned_quantity;
                $avail = max(0, $qty - $used - $ret);

                $resellerSummary['total_diambil'] += $qty;
                $resellerSummary['total_terpakai'] += $used;
                $resellerSummary['total_diretur'] += $ret;
                $resellerSummary['total_sisa'] += $avail;
                $resellerSummary['nominal_terpakai'] += ($used * $price);
                $resellerSummary['nominal_sisa'] += ($avail * $price);
                $resellerSummary['nominal_total'] += ($qty * $price);
            }

            $perPageInt = match ((string) $this->perPage) {
                '50' => 50,
                '100' => 100,
                'all' => max(1, (clone $resellerQuery)->count()),
                default => 20,
            };

            $resellerUsageData = $resellerQuery->paginate($perPageInt);
        }

        return view('livewire.inventory.reports.index', [
            'warehouses' => $warehouses,
            'categories' => $categories,
            'resellers' => $resellers,
            'stockData' => $stockData,
            'mutationData' => $mutationData,
            'custodyData' => $custodyData,
            'resellerUsageData' => $resellerUsageData,
            'resellerSummary' => $resellerSummary,
            'activeTab' => $this->activeTab,
        ])->layout('layouts.app', ['title' => 'Laporan Inventaris - dsBilling']);
    }
}
