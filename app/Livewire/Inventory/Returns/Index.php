<?php

namespace App\Livewire\Inventory\Returns;

use App\Models\Inventory\InventoryCustody;
use App\Models\Inventory\InventoryReturn;
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
    public string $statusFilter = ''; // pending_qc, accepted, rejected
    public string $warehouseFilter = '';

    // Modal Form Pengajuan Retur
    public bool $showCreateModal = false;
    public ?int $returner_id = null;
    public string $returner_type = 'technician'; // technician, reseller
    public ?int $warehouse_id = null;
    public string $notes = '';
    public array $returnItems = [];

    // Modal QC Inspection (Pengurus Gudang)
    public bool $showQCModal = false;
    public ?int $selectedReturnId = null;
    public array $qcInspectionItems = [];

    // Modal Detail
    public bool $showDetailModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'warehouseFilter' => ['except' => ''],
    ];

    public function mount()
    {
        $user = auth()->user();
        if ($user && ($user->isTechnician() || $user->isReseller())) {
            $this->returner_id = $user->id;
            $this->returner_type = $user->isReseller() ? 'reseller' : 'technician';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetCreateForm();
        $wh = Warehouse::active()->first();
        if ($wh) {
            $this->warehouse_id = $wh->id;
        }

        $user = auth()->user();
        if ($user && ($user->isTechnician() || $user->isReseller())) {
            $this->returner_id = $user->id;
            $this->returner_type = $user->isReseller() ? 'reseller' : 'technician';
        } else {
            $firstUser = User::inventoryRecipients()->first();
            $this->returner_id = $firstUser?->id;
            $this->returner_type = $firstUser?->isReseller() ? 'reseller' : 'technician';
        }

        $this->addItemRow();
        $this->showCreateModal = true;
    }

    public function resetCreateForm()
    {
        $this->notes = '';
        $this->returnItems = [];
        $this->resetErrorBag();
    }

    public function addItemRow()
    {
        $this->returnItems[] = [
            'custody_id' => '',
            'item_id' => '',
            'item_serial_id' => null,
            'quantity' => 1,
            'unit' => 'unit',
            'max_available' => 1,
            'condition' => 'good',
            'action_taken' => 'restock',
            'qc_notes' => '',
        ];
    }

    public function removeItemRow(int $index)
    {
        if (isset($this->returnItems[$index])) {
            unset($this->returnItems[$index]);
            $this->returnItems = array_values($this->returnItems);
        }
        if (empty($this->returnItems)) {
            $this->addItemRow();
        }
    }

    public function updatedReturnerId()
    {
        $this->returnItems = [];
        $this->addItemRow();
    }

    public function updatedReturnItems($value, $key)
    {
        if (str_ends_with($key, '.custody_id')) {
            $index = explode('.', $key)[0];
            $custodyId = (int) $value;
            if ($custodyId) {
                $custody = InventoryCustody::with(['item', 'itemSerial'])->find($custodyId);
                if ($custody) {
                    $this->returnItems[$index]['item_id'] = $custody->item_id;
                    $this->returnItems[$index]['item_serial_id'] = $custody->item_serial_id;
                    $this->returnItems[$index]['unit'] = $custody->item?->unit ?? 'unit';
                    $this->returnItems[$index]['max_available'] = $custody->available_quantity;
                    $this->returnItems[$index]['quantity'] = $custody->itemSerial ? 1 : min(1, $custody->available_quantity);
                }
            }
        }
    }

    public function saveReturn()
    {
        $this->validate([
            'returner_id' => 'required|exists:users,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'returnItems' => 'required|array|min:1',
            'returnItems.*.item_id' => 'required|exists:items,id',
            'returnItems.*.quantity' => 'required|numeric|min:0.01',
        ], [
            'warehouse_id.required' => 'Gudang tujuan pengembalian wajib dipilih.',
            'returnItems.required' => 'Minimal 1 item material harus dikembalikan.',
            'returnItems.*.quantity.min' => 'Kuantitas harus lebih dari 0.',
        ]);

        $wh = Warehouse::findOrFail($this->warehouse_id);

        try {
            DB::transaction(function () use ($wh) {
                $ret = InventoryReturn::create([
                    'returner_id' => $this->returner_id,
                    'returner_type' => $this->returner_type,
                    'warehouse_id' => $wh->id,
                    'status' => 'pending_qc',
                    'notes' => $this->notes ?: null,
                    'branch_id' => $wh->branch_id,
                ]);

                foreach ($this->returnItems as $row) {
                    $ret->items()->create([
                        'custody_id' => $row['custody_id'] ?: null,
                        'item_id' => $row['item_id'],
                        'item_serial_id' => $row['item_serial_id'] ?: null,
                        'quantity' => (float) $row['quantity'],
                        'condition' => $row['condition'] ?? 'good',
                        'action_taken' => $row['action_taken'] ?? 'restock',
                        'qc_notes' => $row['qc_notes'] ?? null,
                    ]);
                }
            });

            session()->flash('success', 'Pengajuan retur barang berhasil dibuat dan menunggu verifikasi/inspeksi QC Pengurus Gudang.');
            $this->showCreateModal = false;
            $this->resetCreateForm();
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal mengajukan retur: ' . $e->getMessage());
        }
    }

    public function openQCModal(int $id)
    {
        $this->selectedReturnId = $id;
        $ret = InventoryReturn::with('items.item', 'items.itemSerial')->findOrFail($id);

        $this->qcInspectionItems = [];
        foreach ($ret->items as $it) {
            $this->qcInspectionItems[] = [
                'id' => $it->id,
                'custody_id' => $it->custody_id,
                'item_id' => $it->item_id,
                'item_name' => $it->item?->name,
                'item_serial_id' => $it->item_serial_id,
                'serial_number' => $it->itemSerial?->serial_number,
                'quantity' => (float) $it->quantity,
                'unit' => $it->item?->unit ?? 'unit',
                'condition' => $it->condition ?? 'good',
                'action_taken' => $it->action_taken ?? 'restock',
                'qc_notes' => $it->qc_notes ?? '',
            ];
        }

        $this->showQCModal = true;
    }

    public function processQC(StockMutationService $service)
    {
        if (!$this->selectedReturnId) return;

        $ret = InventoryReturn::findOrFail($this->selectedReturnId);

        try {
            $service->processReturnQC($ret, $this->qcInspectionItems, auth()->id());
            session()->flash('success', "Inspeksi QC selesai. Barang layak pakai telah ditambahkan kembali ke stok gudang {$ret->warehouse->name}.");
            $this->showQCModal = false;
            $this->selectedReturnId = null;
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal memproses QC: ' . $e->getMessage());
        }
    }

    public function showDetail(int $id)
    {
        $this->selectedReturnId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedReturnId = null;
    }

    public function render()
    {
        $user = auth()->user();

        $query = InventoryReturn::with(['returner', 'warehouse', 'inspector', 'items.item', 'items.itemSerial'])
            ->latest('id');

        if ($user && ($user->hasRole('technician') || $user->hasRole('reseller'))) {
            $query->where('returner_id', $user->id);
        }

        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        if (!empty($this->warehouseFilter)) {
            $query->where('warehouse_id', $this->warehouseFilter);
        }

        if (!empty($this->search)) {
            $s = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('return_number', 'like', $s)
                    ->orWhere('notes', 'like', $s)
                    ->orWhereHas('returner', function ($rq) use ($s) {
                        $rq->where('name', 'like', $s);
                    });
            });
        }

        $returns = $query->paginate(15);
        $warehouses = Warehouse::active()->orderBy('name')->get();

        $returners = User::inventoryRecipients()->orderBy('name')->get();

        // Material di tangan user yang dipilih di modal
        $availableCustodies = collect();
        if ($this->returner_id) {
            $availableCustodies = InventoryCustody::with(['item', 'itemSerial'])
                ->where('holder_id', $this->returner_id)
                ->activeCustody()
                ->get();
        }

        $selectedReturn = null;
        if ($this->selectedReturnId) {
            $selectedReturn = InventoryReturn::with(['returner', 'warehouse', 'inspector', 'items.item', 'items.itemSerial'])->find($this->selectedReturnId);
        }

        return view('livewire.inventory.returns.index', [
            'returns' => $returns,
            'warehouses' => $warehouses,
            'returners' => $returners,
            'availableCustodies' => $availableCustodies,
            'selectedReturn' => $selectedReturn,
            'showCreateModal' => $this->showCreateModal,
            'showQCModal' => $this->showQCModal,
            'showDetailModal' => $this->showDetailModal,
            'selectedReturnId' => $this->selectedReturnId,
            'returnItems' => $this->returnItems,
            'qcInspectionItems' => $this->qcInspectionItems,
            'returner_id' => $this->returner_id,
        ])->layout('layouts.app', ['title' => 'Retur & Pengembalian Material - dsBilling']);
    }
}
