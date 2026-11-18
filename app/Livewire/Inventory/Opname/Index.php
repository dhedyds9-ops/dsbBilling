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
    public string|int $perPage = 20;

    protected $queryString = [
        'search' => ['except' => ''],
        'warehouseFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'perPage' => ['except' => 20],
    ];

    public function updatingPerPage()
    {
        $this->resetPage();
    }

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
        $this->ensureOpnameTablesExist();
        $this->opnameDate = date('Y-m-d');
    }

    protected function ensureOpnameTablesExist(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('inventory_stock_opnames')) {
                \Illuminate\Support\Facades\Schema::create('inventory_stock_opnames', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->uuid('uuid')->nullable()->unique();
                    $table->string('opname_number', 50)->unique()->index();
                    $table->unsignedBigInteger('warehouse_id')->index();
                    $table->unsignedBigInteger('branch_id')->nullable()->index();
                    $table->string('status', 30)->default('draft')->index();
                    $table->date('opname_date')->index();
                    $table->text('notes')->nullable();
                    $table->unsignedBigInteger('created_by')->nullable()->index();
                    $table->unsignedBigInteger('approved_by')->nullable()->index();
                    $table->timestamp('approved_at')->nullable();
                    $table->timestamps();
                });
            } else {
                \Illuminate\Support\Facades\Schema::table('inventory_stock_opnames', function (\Illuminate\Database\Schema\Blueprint $table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('inventory_stock_opnames', 'uuid')) {
                        $table->uuid('uuid')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('inventory_stock_opnames', 'branch_id')) {
                        $table->unsignedBigInteger('branch_id')->nullable()->index();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('inventory_stock_opnames', 'status')) {
                        $table->string('status', 30)->default('draft')->index();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('inventory_stock_opnames', 'opname_date')) {
                        $table->date('opname_date')->nullable()->index();
                    }
                });
            }

            if (!\Illuminate\Support\Facades\Schema::hasTable('inventory_stock_opname_items')) {
                \Illuminate\Support\Facades\Schema::create('inventory_stock_opname_items', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('opname_id')->index();
                    $table->unsignedBigInteger('item_id')->index();
                    $table->decimal('system_quantity', 12, 2)->default(0);
                    $table->decimal('physical_quantity', 12, 2)->default(0);
                    $table->decimal('difference_quantity', 12, 2)->default(0);
                    $table->string('adjustment_reason', 100)->nullable();
                    $table->text('notes')->nullable();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Auto-create inventory_stock_opnames error: ' . $e->getMessage());
        }
    }

    public function exportExcel()
    {
        $filename = 'stok-opname-' . date('Ymd-His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\Inventory\OpnameExport($this->search, $this->warehouseFilter, $this->statusFilter),
            $filename
        );
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
        $this->ensureOpnameTablesExist();

        try {
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

            $perPageInt = match ((string) $this->perPage) {
                '50' => 50,
                '100' => 100,
                'all' => max(1, (clone $query)->count()),
                default => 20,
            };

            $opnames = $query->paginate($perPageInt);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Opname render error: ' . $e->getMessage());
            $opnames = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        $warehouses = collect();
        try {
            $whQuery = Warehouse::query();
            if (\Illuminate\Support\Facades\Schema::hasColumn('warehouses', 'is_active')) {
                $whQuery->where('is_active', true);
            }
            $warehouses = $whQuery->orderBy('name')->get();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Opname warehouses load error: ' . $e->getMessage());
        }

        return view('livewire.inventory.opname.index', [
            'opnames' => $opnames,
            'warehouses' => $warehouses,
            'showCreateModal' => $this->showCreateModal,
            'showDetailModal' => $this->showDetailModal,
            'itemsData' => $this->itemsData,
            'selectedOpname' => $this->selectedOpname,
        ])->layout('layouts.app', ['title' => 'Stok Opname - dsBilling']);
    }
}
