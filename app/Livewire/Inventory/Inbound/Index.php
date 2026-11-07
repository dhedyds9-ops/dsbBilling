<?php

namespace App\Livewire\Inventory\Inbound;

use App\Models\Inventory\InventoryInbound;
use App\Models\Inventory\InventoryInboundItem;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Services\Inventory\StockMutationService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $warehouseFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    // Modal Form Penerimaan Barang
    public bool $showCreateModal = false;
    public ?int $warehouse_id = null;
    public string $source_type = 'supplier';
    public string $supplier_name = '';
    public string $reference_doc = '';
    public string $received_date = '';
    public string $notes = '';
    public array $inboundItems = [];

    // Modal Detail
    public bool $showDetailModal = false;
    public ?int $selectedInboundId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'warehouseFilter' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function mount()
    {
        $this->received_date = date('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingWarehouseFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetCreateForm();
        $firstWarehouse = Warehouse::active()->first();
        if ($firstWarehouse) {
            $this->warehouse_id = $firstWarehouse->id;
        }
        $this->addItemRow();
        $this->showCreateModal = true;
    }

    public function resetCreateForm()
    {
        $this->warehouse_id = null;
        $this->source_type = 'supplier';
        $this->supplier_name = '';
        $this->reference_doc = '';
        $this->received_date = date('Y-m-d');
        $this->notes = '';
        $this->inboundItems = [];
        $this->resetErrorBag();
    }

    public function addItemRow()
    {
        $this->inboundItems[] = [
            'item_id' => '',
            'quantity' => 1,
            'unit_cost' => null,
            'serials_raw' => '',
            'is_serialized' => false,
            'unit' => 'unit',
        ];
    }

    public function removeItemRow(int $index)
    {
        if (isset($this->inboundItems[$index])) {
            unset($this->inboundItems[$index]);
            $this->inboundItems = array_values($this->inboundItems);
        }

        if (empty($this->inboundItems)) {
            $this->addItemRow();
        }
    }

    public function syncQuantityFromSerials(int $index): void
    {
        if (!isset($this->inboundItems[$index])) return;
        $raw = trim($this->inboundItems[$index]['serials_raw'] ?? '');
        $snList = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $raw))));
        if (!empty($snList)) {
            $this->inboundItems[$index]['quantity'] = count($snList);
        }
    }

    public function updatedInboundItems($value, $key)
    {
        // Mendeteksi perubahan item_id untuk mengisi unit dan status serial
        if (str_ends_with($key, '.item_id')) {
            $index = explode('.', $key)[0];
            $itemId = (int) $value;
            if ($itemId) {
                $item = Item::find($itemId);
                if ($item) {
                    $this->inboundItems[$index]['unit'] = $item->unit;
                    $this->inboundItems[$index]['is_serialized'] = (bool) $item->is_serialized;
                    $this->inboundItems[$index]['unit_cost'] = $item->cost_price ? (float) $item->cost_price : null;
                }
            } else {
                $this->inboundItems[$index]['unit'] = 'unit';
                $this->inboundItems[$index]['is_serialized'] = false;
                $this->inboundItems[$index]['unit_cost'] = null;
            }
        }
    }

    public function saveInbound(StockMutationService $mutationService)
    {
        $this->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'source_type' => 'required|string',
            'supplier_name' => 'nullable|string|max:200',
            'reference_doc' => 'nullable|string|max:100',
            'received_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'inboundItems' => 'required|array|min:1',
            'inboundItems.*.item_id' => 'required|exists:items,id',
            'inboundItems.*.quantity' => 'required|numeric|min:0.01',
            'inboundItems.*.unit_cost' => 'nullable|numeric|min:0',
        ], [
            'warehouse_id.required' => 'Gudang penerima wajib dipilih.',
            'inboundItems.required' => 'Minimal harus ada 1 barang yang dicatat.',
            'inboundItems.*.item_id.required' => 'Barang pada setiap baris wajib dipilih.',
            'inboundItems.*.quantity.min' => 'Jumlah barang harus lebih dari 0.',
        ]);

        // Validasi serial numbers untuk barang berserial
        foreach ($this->inboundItems as $idx => $row) {
            $item = Item::find($row['item_id']);
            if ($item && $item->is_serialized) {
                $rawSerials = trim($row['serials_raw'] ?? '');
                $snList = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $rawSerials))));
                $qty = (int) $row['quantity'];

                if (count($snList) !== $qty) {
                    $this->addError("inboundItems.{$idx}.serials_raw", "Barang '{$item->name}' memerlukan {$qty} Serial Number yang valid. Terdeteksi: " . count($snList) . " SN.");
                    return;
                }

                // Cek duplikasi di input
                if (count($snList) !== count(array_unique($snList))) {
                    $this->addError("inboundItems.{$idx}.serials_raw", "Terdapat Serial Number duplikat pada baris '{$item->name}'.");
                    return;
                }
            }
        }

        $warehouse = Warehouse::findOrFail($this->warehouse_id);

        try {
            DB::transaction(function () use ($mutationService, $warehouse) {
                $inbound = InventoryInbound::create([
                    'warehouse_id' => $warehouse->id,
                    'branch_id' => $warehouse->branch_id,
                    'source_type' => $this->source_type,
                    'supplier_name' => $this->supplier_name ?: null,
                    'reference_doc' => $this->reference_doc ?: null,
                    'received_date' => $this->received_date,
                    'status' => 'received',
                    'notes' => $this->notes ?: null,
                    'received_by' => auth()->id(),
                ]);

                foreach ($this->inboundItems as $row) {
                    $item = Item::findOrFail($row['item_id']);
                    $qty = (float) $row['quantity'];
                    $unitCost = !empty($row['unit_cost']) ? (float) $row['unit_cost'] : null;
                    $subtotal = $unitCost ? ($unitCost * $qty) : null;

                    $snList = [];
                    if ($item->is_serialized) {
                        $rawSerials = trim($row['serials_raw'] ?? '');
                        $snList = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $rawSerials))));
                    }

                    // Buat detail item inbound
                    $inboundItem = InventoryInboundItem::create([
                        'inbound_id' => $inbound->id,
                        'item_id' => $item->id,
                        'quantity' => $qty,
                        'unit_cost' => $unitCost,
                        'subtotal' => $subtotal,
                        'serials' => !empty($snList) ? $snList : null,
                        'notes' => null,
                    ]);

                    // Catat ke Mutasi Stok & update saldo fisik
                    $mutationService->recordInbound(
                        warehouseId: $warehouse->id,
                        itemId: $item->id,
                        quantity: $qty,
                        referenceType: 'inbound',
                        referenceId: $inbound->id,
                        notes: "Penerimaan No. {$inbound->inbound_number} dari " . ($this->supplier_name ?: 'Vendor'),
                        serials: $snList,
                        actorId: auth()->id()
                    );
                }
            });

            session()->flash('success', 'Penerimaan barang masuk berhasil disimpan dan stok gudang telah diperbarui.');
            $this->showCreateModal = false;
            $this->resetCreateForm();
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal memproses barang masuk: ' . $e->getMessage());
        }
    }

    public function showDetail(int $id)
    {
        $this->selectedInboundId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedInboundId = null;
    }

    public function render()
    {
        $query = InventoryInbound::with(['warehouse', 'receiver', 'items.item'])
            ->latest('received_date')
            ->latest('id');

        if (!empty($this->search)) {
            $s = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('inbound_number', 'like', $s)
                    ->orWhere('supplier_name', 'like', $s)
                    ->orWhere('reference_doc', 'like', $s)
                    ->orWhere('notes', 'like', $s);
            });
        }

        if (!empty($this->warehouseFilter)) {
            $query->where('warehouse_id', $this->warehouseFilter);
        }

        if (!empty($this->dateFrom)) {
            $query->whereDate('received_date', '>=', $this->dateFrom);
        }

        if (!empty($this->dateTo)) {
            $query->whereDate('received_date', '<=', $this->dateTo);
        }

        $inbounds = $query->paginate(15);
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $availableItems = Item::active()->orderBy('name')->get();

        $selectedInbound = null;
        if ($this->selectedInboundId) {
            $selectedInbound = InventoryInbound::with(['warehouse', 'receiver', 'items.item'])->find($this->selectedInboundId);
        }

        return view('livewire.inventory.inbound.index', [
            'inbounds' => $inbounds,
            'warehouses' => $warehouses,
            'availableItems' => $availableItems,
            'selectedInbound' => $selectedInbound,
            'showCreateModal' => $this->showCreateModal,
            'showDetailModal' => $this->showDetailModal,
            'inboundItems' => $this->inboundItems,
        ])->layout('layouts.app', ['title' => 'Barang Masuk (Inbound) - dsBilling']);
    }
}
