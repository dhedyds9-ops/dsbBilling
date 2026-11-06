<?php

namespace App\Livewire\Inventory\Dispatch;

use App\Models\CRM\Customer;
use App\Models\Inventory\InventoryDispatch;
use App\Models\Inventory\InventoryRequisition;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemSerial;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use App\Services\Inventory\StockMutationService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $warehouseFilter = '';
    public ?int $requisition_id = null; // query parameter from requisition page

    // Modal Form Dispatch
    public bool $showCreateModal = false;
    public ?int $warehouse_id = null;
    public string $distribution_method = 'pickup_reseller';
    public ?int $recipient_id = null;
    public string $recipient_type = 'reseller'; // reseller, technician
    public ?int $customer_id = null;
    public ?int $selected_requisition_id = null;
    public string $notes = '';
    public array $dispatchItems = [];

    // Modal Detail
    public bool $showDetailModal = false;
    public ?int $selectedDispatchId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'warehouseFilter' => ['except' => ''],
        'requisition_id' => ['except' => null],
    ];

    public function mount()
    {
        if ($this->requisition_id) {
            $this->openCreateFromRequisition($this->requisition_id);
        }
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
        $firstWh = Warehouse::active()->first();
        if ($firstWh) {
            $this->warehouse_id = $firstWh->id;
        }
        $this->addItemRow();
        $this->showCreateModal = true;
    }

    public function openCreateFromRequisition(int $reqId)
    {
        $this->resetCreateForm();
        $req = InventoryRequisition::with(['items.item', 'requester', 'designatedTechnician'])->find($reqId);
        if (!$req) return;

        $this->selected_requisition_id = $req->id;
        $this->warehouse_id = $req->warehouse_id;
        $this->distribution_method = $req->distribution_method;
        $this->customer_id = $req->customer_id;

        // Tentukan penerima fisik barang berdasarkan Metode A atau B
        if ($req->distribution_method === 'pickup_reseller') {
            $this->recipient_id = $req->requester_id;
            $this->recipient_type = $req->requester_type;
        } else {
            $this->recipient_id = $req->designated_technician_id;
            $this->recipient_type = 'technician';
        }

        $this->dispatchItems = [];
        foreach ($req->items as $ri) {
            $approved = $ri->quantity_approved ?? $ri->quantity_requested;
            $remaining = max(0, $approved - $ri->quantity_dispatched);
            if ($remaining > 0) {
                $this->dispatchItems[] = [
                    'item_id' => $ri->item_id,
                    'quantity' => $remaining,
                    'unit' => $ri->item?->unit ?? 'unit',
                    'is_serialized' => (bool) $ri->item?->is_serialized,
                    'selected_serials' => [],
                    'serials_raw' => '',
                    'notes' => '',
                ];
            }
        }

        if (empty($this->dispatchItems)) {
            $this->addItemRow();
        }

        $this->showCreateModal = true;
    }

    public function resetCreateForm()
    {
        $this->warehouse_id = null;
        $this->distribution_method = 'pickup_reseller';
        $this->recipient_id = null;
        $this->recipient_type = 'reseller';
        $this->customer_id = null;
        $this->selected_requisition_id = null;
        $this->notes = '';
        $this->dispatchItems = [];
        $this->resetErrorBag();
    }

    public function addItemRow()
    {
        $this->dispatchItems[] = [
            'item_id' => '',
            'quantity' => 1,
            'unit' => 'unit',
            'is_serialized' => false,
            'selected_serials' => [],
            'serials_raw' => '',
            'notes' => '',
        ];
    }

    public function removeItemRow(int $index)
    {
        if (isset($this->dispatchItems[$index])) {
            unset($this->dispatchItems[$index]);
            $this->dispatchItems = array_values($this->dispatchItems);
        }
        if (empty($this->dispatchItems)) {
            $this->addItemRow();
        }
    }

    public function updatedDispatchItems($value, $key)
    {
        if (str_ends_with($key, '.item_id')) {
            $index = explode('.', $key)[0];
            $itemId = (int) $value;
            if ($itemId) {
                $item = Item::find($itemId);
                $this->dispatchItems[$index]['unit'] = $item?->unit ?? 'unit';
                $this->dispatchItems[$index]['is_serialized'] = (bool) $item?->is_serialized;
                $this->dispatchItems[$index]['selected_serials'] = [];
            } else {
                $this->dispatchItems[$index]['unit'] = 'unit';
                $this->dispatchItems[$index]['is_serialized'] = false;
            }
        }
    }

    public function updatedDistributionMethod($val)
    {
        if ($val === 'pickup_reseller') {
            $this->recipient_type = 'reseller';
        } else {
            $this->recipient_type = 'technician';
        }
        $this->recipient_id = null;
    }

    public function saveDispatch(StockMutationService $mutationService)
    {
        $this->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'recipient_id' => 'required|exists:users,id',
            'recipient_type' => 'required|in:reseller,technician,internal',
            'distribution_method' => 'required|in:pickup_reseller,direct_technician',
            'customer_id' => 'nullable|exists:customers,id',
            'dispatchItems' => 'required|array|min:1',
            'dispatchItems.*.item_id' => 'required|exists:items,id',
            'dispatchItems.*.quantity' => 'required|numeric|min:0.01',
        ], [
            'warehouse_id.required' => 'Gudang asal barang wajib dipilih.',
            'recipient_id.required' => 'Penerima barang wajib dipilih.',
            'dispatchItems.required' => 'Minimal 1 item barang harus dikeluarkan.',
            'dispatchItems.*.item_id.required' => 'Barang wajib dipilih.',
            'dispatchItems.*.quantity.min' => 'Jumlah barang harus lebih besar dari 0.',
        ]);

        // Validasi serial numbers untuk barang berserial
        $itemsPayload = [];
        foreach ($this->dispatchItems as $idx => $row) {
            $item = Item::findOrFail($row['item_id']);
            $qty = (float) $row['quantity'];
            $serials = [];

            if ($item->is_serialized) {
                // Ambil dari selected_serials array atau serials_raw
                if (!empty($row['selected_serials'])) {
                    $serials = $row['selected_serials'];
                } elseif (!empty($row['serials_raw'])) {
                    $serials = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $row['serials_raw']))));
                }

                if (count($serials) !== (int) $qty) {
                    $this->addError("dispatchItems.{$idx}.quantity", "Barang '{$item->name}' memerlukan " . (int)$qty . " Serial Number. Terpilih: " . count($serials));
                    return;
                }
            }

            $itemsPayload[] = [
                'item_id' => $item->id,
                'quantity' => $qty,
                'serials' => $serials,
                'notes' => $row['notes'] ?? null,
            ];
        }

        $warehouse = Warehouse::findOrFail($this->warehouse_id);

        try {
            DB::transaction(function () use ($warehouse, $itemsPayload, $mutationService) {
                $dispatch = InventoryDispatch::create([
                    'requisition_id' => $this->selected_requisition_id,
                    'warehouse_id' => $warehouse->id,
                    'branch_id' => $warehouse->branch_id,
                    'distribution_method' => $this->distribution_method,
                    'recipient_id' => $this->recipient_id,
                    'recipient_type' => $this->recipient_type,
                    'customer_id' => $this->customer_id,
                    'status' => 'dispatched',
                    'dispatched_by' => auth()->id(),
                    'dispatched_at' => now(),
                    'notes' => $this->notes ?: null,
                ]);

                // Eksekusi mutasi keluar & catat custody tracking
                $mutationService->recordDispatch($dispatch, $itemsPayload, auth()->id());
            });

            session()->flash('success', 'Barang berhasil dikeluarkan dari gudang dan dicatat dalam kepemilikan sementara (Custody Tracking).');
            $this->showCreateModal = false;
            $this->resetCreateForm();
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal memproses pengeluaran barang: ' . $e->getMessage());
        }
    }

    public function showDetail(int $id)
    {
        $this->selectedDispatchId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedDispatchId = null;
    }

    public function render()
    {
        $query = InventoryDispatch::with(['warehouse', 'recipient', 'dispatcher', 'customer', 'items.item'])
            ->latest('id');

        if (!empty($this->search)) {
            $s = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('dispatch_number', 'like', $s)
                    ->orWhere('notes', 'like', $s)
                    ->orWhereHas('recipient', function ($rq) use ($s) {
                        $rq->where('name', 'like', $s);
                    });
            });
        }

        if (!empty($this->warehouseFilter)) {
            $query->where('warehouse_id', $this->warehouseFilter);
        }

        $dispatches = $query->paginate(15);
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $availableItems = Item::active()->orderBy('name')->get();
        $customers = Customer::orderBy('name')->limit(100)->get();

        // Penerima options
        $recipients = User::where(function ($q) {
            $q->whereHas('roles', function ($rq) {
                $rq->whereIn('name', ['reseller', 'technician']);
            });
        })->orderBy('name')->get();

        // Available serials per warehouse for selected items in modal
        $availableSerials = [];
        if ($this->warehouse_id) {
            foreach ($this->dispatchItems as $row) {
                if (!empty($row['item_id']) && !empty($row['is_serialized'])) {
                    $itemId = (int) $row['item_id'];
                    $availableSerials[$itemId] = ItemSerial::where('item_id', $itemId)
                        ->where('warehouse_id', $this->warehouse_id)
                        ->where('status', 'in_warehouse')
                        ->orderBy('serial_number')
                        ->get();
                }
            }
        }

        $selectedDispatch = null;
        if ($this->selectedDispatchId) {
            $selectedDispatch = InventoryDispatch::with(['warehouse', 'recipient', 'dispatcher', 'customer', 'items.item', 'custodies.itemSerial'])->find($this->selectedDispatchId);
        }

        return view('livewire.inventory.dispatch.index', [
            'dispatches' => $dispatches,
            'warehouses' => $warehouses,
            'availableItems' => $availableItems,
            'customers' => $customers,
            'recipients' => $recipients,
            'availableSerials' => $availableSerials,
            'selectedDispatch' => $selectedDispatch,
        ])->layout('layouts.app', ['title' => 'Pengeluaran Barang (Dispatch) - dsBilling']);
    }
}
