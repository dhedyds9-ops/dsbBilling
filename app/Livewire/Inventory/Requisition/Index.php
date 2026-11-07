<?php

namespace App\Livewire\Inventory\Requisition;

use App\Models\CRM\Customer;
use App\Models\Inventory\InventoryRequisition;
use App\Models\Inventory\InventoryRequisitionItem;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = ''; // all, pending, approved, rejected, dispatched
    public string $warehouseFilter = '';

    // Modal Form Permintaan
    public bool $showCreateModal = false;
    public ?int $warehouse_id = null;
    public string $distribution_method = 'pickup_reseller'; // pickup_reseller (Metode A), direct_technician (Metode B)
    public ?int $customer_id = null;
    public string $work_order_type = 'psb'; // psb, maintenance, relocation, stock_topup
    public ?int $designated_technician_id = null;
    public string $notes = '';
    public array $requisitionItems = [];

    // Modal Approval / Rejection
    public bool $showApprovalModal = false;
    public ?int $selectedRequisitionId = null;
    public string $rejection_reason = '';

    // Modal Detail
    public bool $showDetailModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'warehouseFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
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
        $wh = Warehouse::active()->first();
        if ($wh) {
            $this->warehouse_id = $wh->id;
        }
        $this->addItemRow();
        $this->showCreateModal = true;
    }

    public function resetCreateForm()
    {
        $this->warehouse_id = null;
        $this->distribution_method = 'pickup_reseller';
        $this->customer_id = null;
        $this->work_order_type = 'psb';
        $this->designated_technician_id = null;
        $this->notes = '';
        $this->requisitionItems = [];
        $this->resetErrorBag();
    }

    public function addItemRow()
    {
        $this->requisitionItems[] = [
            'item_id' => '',
            'quantity' => 1,
            'unit' => 'unit',
            'notes' => '',
        ];
    }

    public function removeItemRow(int $index)
    {
        if (isset($this->requisitionItems[$index])) {
            unset($this->requisitionItems[$index]);
            $this->requisitionItems = array_values($this->requisitionItems);
        }

        if (empty($this->requisitionItems)) {
            $this->addItemRow();
        }
    }

    public function updatedRequisitionItems($value, $key)
    {
        if (str_ends_with($key, '.item_id')) {
            $index = explode('.', $key)[0];
            $itemId = (int) $value;
            if ($itemId) {
                $item = Item::find($itemId);
                $this->requisitionItems[$index]['unit'] = $item?->unit ?? 'unit';
            } else {
                $this->requisitionItems[$index]['unit'] = 'unit';
            }
        }
    }

    public function saveRequisition()
    {
        $this->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'distribution_method' => 'required|in:pickup_reseller,direct_technician',
            'designated_technician_id' => 'required|exists:users,id',
            'customer_id' => 'nullable|exists:customers,id',
            'work_order_type' => 'required|string',
            'notes' => 'nullable|string|max:1000',
            'requisitionItems' => 'required|array|min:1',
            'requisitionItems.*.item_id' => 'required|exists:items,id',
            'requisitionItems.*.quantity' => 'required|numeric|min:0.01',
        ], [
            'warehouse_id.required' => 'Gudang asal wajib dipilih.',
            'designated_technician_id.required' => 'Teknisi pelaksana wajib ditunjuk.',
            'requisitionItems.required' => 'Minimal harus ada 1 barang yang diminta.',
            'requisitionItems.*.item_id.required' => 'Barang pada setiap baris wajib dipilih.',
            'requisitionItems.*.quantity.min' => 'Jumlah barang harus lebih besar dari 0.',
        ]);

        $warehouse = Warehouse::findOrFail($this->warehouse_id);
        $user = auth()->user();

        $requesterType = 'internal';
        if ($user) {
            if ($user->isReseller()) {
                $requesterType = 'reseller';
            } elseif ($user->isTechnician()) {
                $requesterType = 'technician';
            }
        }

        try {
            DB::transaction(function () use ($warehouse, $requesterType, $user) {
                $req = InventoryRequisition::create([
                    'requester_id' => $user->id,
                    'requester_type' => $requesterType,
                    'warehouse_id' => $warehouse->id,
                    'branch_id' => $warehouse->branch_id,
                    'distribution_method' => $this->distribution_method,
                    'customer_id' => $this->customer_id,
                    'work_order_type' => $this->work_order_type,
                    'designated_technician_id' => $this->designated_technician_id,
                    'status' => 'pending',
                    'notes' => $this->notes ?: null,
                ]);

                foreach ($this->requisitionItems as $row) {
                    InventoryRequisitionItem::create([
                        'requisition_id' => $req->id,
                        'item_id' => $row['item_id'],
                        'quantity_requested' => (float) $row['quantity'],
                        'quantity_approved' => (float) $row['quantity'],
                        'notes' => $row['notes'] ?? null,
                    ]);
                }
            });

            session()->flash('success', 'Permintaan barang berhasil diajukan dan menunggu persetujuan gudang/manager.');
            $this->showCreateModal = false;
            $this->resetCreateForm();
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal mengajukan permintaan: ' . $e->getMessage());
        }
    }

    public function openApprovalModal(int $id)
    {
        $this->selectedRequisitionId = $id;
        $this->rejection_reason = '';
        $this->showApprovalModal = true;
    }

    public function approveRequisition()
    {
        if (!$this->selectedRequisitionId) return;

        $req = InventoryRequisition::findOrFail($this->selectedRequisitionId);
        $req->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        session()->flash('success', "Permintaan No. {$req->requisition_number} telah disetujui. Siap untuk pengeluaran barang (dispatch).");
        $this->showApprovalModal = false;
        $this->selectedRequisitionId = null;
    }

    public function rejectRequisition()
    {
        if (!$this->selectedRequisitionId) return;

        $this->validate([
            'rejection_reason' => 'required|string|min:5|max:500',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
            'rejection_reason.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $req = InventoryRequisition::findOrFail($this->selectedRequisitionId);
        $req->update([
            'status' => 'rejected',
            'rejection_reason' => $this->rejection_reason,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        session()->flash('success', "Permintaan No. {$req->requisition_number} telah ditolak.");
        $this->showApprovalModal = false;
        $this->selectedRequisitionId = null;
    }

    public function showDetail(int $id)
    {
        $this->selectedRequisitionId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedRequisitionId = null;
    }

    public function render()
    {
        $query = InventoryRequisition::with(['warehouse', 'requester', 'designatedTechnician', 'customer', 'items.item'])
            ->latest('id');

        // Reseller hanya melihat permintaannya sendiri
        if (auth()->user()?->isReseller()) {
            $query->where('requester_id', auth()->id());
        }

        if (!empty($this->search)) {
            $s = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('requisition_number', 'like', $s)
                    ->orWhere('notes', 'like', $s)
                    ->orWhereHas('customer', function ($cq) use ($s) {
                        $cq->where('name', 'like', $s)->orWhere('phone', 'like', $s);
                    });
            });
        }

        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        if (!empty($this->warehouseFilter)) {
            $query->where('warehouse_id', $this->warehouseFilter);
        }

        $requisitions = $query->paginate(15);
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $availableItems = Item::active()->orderBy('name')->get();
        $customers = Customer::orderBy('name')->limit(100)->get();
        $technicians = User::technicians()->orderBy('name')->get();

        $selectedRequisition = null;
        if ($this->selectedRequisitionId) {
            $selectedRequisition = InventoryRequisition::with(['warehouse', 'requester', 'designatedTechnician', 'customer', 'items.item', 'dispatches'])->find($this->selectedRequisitionId);
        }

        return view('livewire.inventory.requisition.index', [
            'requisitions' => $requisitions,
            'warehouses' => $warehouses,
            'availableItems' => $availableItems,
            'customers' => $customers,
            'technicians' => $technicians,
            'selectedRequisition' => $selectedRequisition,
        ])->layout('layouts.app', ['title' => 'Permintaan Barang - dsBilling']);
    }
}
