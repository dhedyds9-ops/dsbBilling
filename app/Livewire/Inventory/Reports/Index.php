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

        // 5. Laporan Rekonsiliasi Aset Jaringan vs Gudang (Neraca Stok Balance)
        $reconciliationData = [];
        $reconciliationSummary = [
            'total_inbound' => 0,
            'total_warehouse' => 0,
            'total_custody' => 0,
            'total_deployed' => 0,
            'total_delta' => 0,
            'network_totals' => [
                'odc' => 0,
                'odp' => 0,
                'closure' => 0,
                'splitter' => 0,
                'onu' => 0,
            ],
        ];

        if ($this->activeTab === 'network_reconciliation') {
            $items = Item::withoutGlobalScopes()
                ->where(function ($q) {
                    $q->whereIn('category', ['ODC', 'ODP', 'Joint Closure', 'Splitter'])
                      ->orWhere('name', 'like', '%ODC%')
                      ->orWhere('name', 'like', '%ODP%')
                      ->orWhere('name', 'like', '%Closure%')
                      ->orWhere('name', 'like', '%Splitter%')
                      ->orWhere('name', 'like', '%ONU%')
                      ->orWhere('name', 'like', '%ONT%');
                })
                ->orderBy('category')
                ->orderBy('name')
                ->get();

            $odcCount = \App\Models\ISP\Odc::withoutGlobalScopes()->where('status', 'active')->count();
            $odpCount = \App\Models\ISP\Odp::withoutGlobalScopes()->where('status', 'active')->count();
            $closureCount = \App\Models\ISP\JointClosure::withoutGlobalScopes()->where('status', 'active')->count();
            $splitterCount = \App\Models\ISP\Splitter::withoutGlobalScopes()->where('status', 'active')->count();
            $onuCount = \App\Models\ISP\Onu::withoutGlobalScopes()->count();

            $reconciliationSummary['network_totals'] = [
                'odc' => $odcCount,
                'odp' => $odpCount,
                'closure' => $closureCount,
                'splitter' => $splitterCount,
                'onu' => $onuCount,
            ];

            foreach ($items as $item) {
                // 1. Inbound (pengadaan)
                $inbound = (float) \App\Models\Inventory\InventoryStockMutation::withoutGlobalScopes()
                    ->where('item_id', $item->id)
                    ->whereIn('type', ['inbound_supplier', 'inbound_initial', 'inbound_transfer', 'inbound_return'])
                    ->sum('qty_in');

                // 2. Stok di gudang
                $warehouseStock = (float) InventoryStock::withoutGlobalScopes()
                    ->where('item_id', $item->id)
                    ->sum('quantity');

                // 3. Custody di tangan teknisi / tim
                $custodyStock = (float) (InventoryCustody::withoutGlobalScopes()
                    ->where('item_id', $item->id)
                    ->whereIn('status', ['in_custody', 'accepted', 'in_transit'])
                    ->selectRaw('SUM(quantity - used_quantity - returned_quantity) as remaining')
                    ->value('remaining') ?? 0.0);

                // 4. Terpasang di jaringan/pelanggan via usage
                $usageQty = (float) \App\Models\Inventory\InventoryUsageItem::withoutGlobalScopes()
                    ->where('item_id', $item->id)
                    ->sum('quantity');

                $activeNodes = 0;
                $cat = strtoupper($item->category ?? '');
                if (str_contains($cat, 'ODC') || str_contains(strtoupper($item->name), 'ODC')) {
                    $activeNodes = \App\Models\ISP\Odc::withoutGlobalScopes()->where('item_id', $item->id)->count();
                } elseif (str_contains($cat, 'ODP') || str_contains(strtoupper($item->name), 'ODP')) {
                    $activeNodes = \App\Models\ISP\Odp::withoutGlobalScopes()->where('item_id', $item->id)->count();
                } elseif (str_contains($cat, 'CLOSURE') || str_contains(strtoupper($item->name), 'CLOSURE')) {
                    $activeNodes = \App\Models\ISP\JointClosure::withoutGlobalScopes()->where('item_id', $item->id)->count();
                } elseif (str_contains($cat, 'SPLITTER') || str_contains(strtoupper($item->name), 'SPLITTER')) {
                    $activeNodes = \App\Models\ISP\Splitter::withoutGlobalScopes()->where('item_id', $item->id)->count();
                }

                $deployedQty = max($usageQty, $activeNodes);
                $totalTracked = $warehouseStock + $custodyStock + $deployedQty;
                $delta = $inbound > 0 ? ($inbound - $totalTracked) : 0;

                $reconciliationData[] = [
                    'item' => $item,
                    'inbound' => $inbound,
                    'warehouse' => $warehouseStock,
                    'custody' => $custodyStock,
                    'deployed' => $deployedQty,
                    'active_nodes' => $activeNodes,
                    'total_tracked' => $totalTracked,
                    'delta' => $delta,
                    'is_balanced' => abs($delta) < 0.001,
                ];

                $reconciliationSummary['total_inbound'] += $inbound;
                $reconciliationSummary['total_warehouse'] += $warehouseStock;
                $reconciliationSummary['total_custody'] += $custodyStock;
                $reconciliationSummary['total_deployed'] += $deployedQty;
                $reconciliationSummary['total_delta'] += $delta;
            }
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
            'reconciliationData' => $reconciliationData,
            'reconciliationSummary' => $reconciliationSummary,
            'activeTab' => $this->activeTab,
        ])->layout('layouts.app', ['title' => 'Laporan Inventaris - dsBilling']);
    }
}
