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
    public string|int $perPage = 20;

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
        'perPage' => ['except' => 20],
        'page' => ['except' => 1],
    ];

    public function updatingPerPage()
    {
        $this->resetPage();
    }

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

    public function canUserCreateRequisition(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Administrator dan Admin Gudang TIDAK membuat permintaan barang
        if ($user->isWarehouseAdmin()) return false;
        if ($user->hasRole('administrator') && !$user->isReseller() && !$user->isTechnician()) return false;

        // Reseller dan Teknisi BISA membuat permintaan barang
        return $user->isReseller() || $user->isTechnician();
    }

    public function canUserManageWarehouse(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Reseller atau Teknisi murni TIDAK BOLEH memproses approval/reject/dispatch
        if ($user->isReseller()) return false;
        if ($user->isTechnician() && !$user->isWarehouseAdmin()) return false;

        return $user->isWarehouseAdmin() || $user->hasRole('manager') || $user->isSuperAdmin();
    }

    public function openCreateModal()
    {
        if (!$this->canUserCreateRequisition()) {
            session()->flash('error', 'Administrator dan Admin Gudang tidak diperkenankan membuat pengajuan permintaan baru.');
            return;
        }

        $this->resetCreateForm();
        $wh = Warehouse::active()->first();
        if ($wh) {
            $this->warehouse_id = $wh->id;
        }

        // Default untuk reseller adalah pengisian stok umum
        if (auth()->user()?->isReseller()) {
            $this->work_order_type = 'stock_topup';
            $this->distribution_method = 'pickup_reseller';
        }

        $this->addItemRow();
        $this->showCreateModal = true;
    }

    public function resetCreateForm()
    {
        $this->warehouse_id = null;
        $this->distribution_method = 'pickup_reseller';
        $this->customer_id = null;
        $this->work_order_type = auth()->user()?->isReseller() ? 'stock_topup' : 'psb';
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

    public function updatedWorkOrderType($value)
    {
        // Jika jenis permintaan adalah Pengisian Stok Reseller, tidak mewajibkan teknisi / pelanggan
        if ($value === 'stock_topup') {
            $this->distribution_method = 'pickup_reseller';
            $this->customer_id = null;
            $this->designated_technician_id = null;
        }
    }

    public function saveRequisition()
    {
        if (!$this->canUserCreateRequisition()) {
            abort(403, 'Administrator dan Admin Gudang tidak diperkenankan membuat pengajuan permintaan baru.');
        }

        $this->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'distribution_method' => 'required|in:pickup_reseller,direct_technician',
            'work_order_type' => 'required|in:stock_topup,psb,maintenance,other',
            'designated_technician_id' => 'nullable|exists:users,id',
            'customer_id' => 'nullable|exists:customers,id',
            'notes' => 'nullable|string|max:1000',
            'requisitionItems' => 'required|array|min:1',
            'requisitionItems.*.item_id' => 'required|exists:items,id',
            'requisitionItems.*.quantity' => 'required|numeric|min:0.01',
        ], [
            'warehouse_id.required' => 'Gudang asal wajib dipilih.',
            'work_order_type.required' => 'Jenis pengajuan wajib dipilih.',
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

        // Jika pengisian stok reseller, pastikan teknisi & pelanggan kosong
        $designatedTechId = ($this->work_order_type === 'stock_topup') ? null : $this->designated_technician_id;
        $customerId = ($this->work_order_type === 'stock_topup') ? null : $this->customer_id;

        try {
            DB::transaction(function () use ($warehouse, $requesterType, $user, $designatedTechId, $customerId) {
                $req = InventoryRequisition::create([
                    'requester_id' => $user->id,
                    'requester_type' => $requesterType,
                    'warehouse_id' => $warehouse->id,
                    'branch_id' => $warehouse->branch_id,
                    'distribution_method' => $this->distribution_method,
                    'customer_id' => $customerId,
                    'work_order_type' => $this->work_order_type,
                    'designated_technician_id' => $designatedTechId,
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
        if (!$this->canUserManageWarehouse()) {
            abort(403, 'Anda tidak memiliki hak otorisasi untuk memproses persetujuan permintaan barang.');
        }

        $req = InventoryRequisition::findOrFail($id);
        if ($req->requester_id === auth()->id()) {
            abort(403, 'Anda tidak dapat memproses persetujuan atas permintaan yang Anda ajukan sendiri.');
        }

        $this->selectedRequisitionId = $id;
        $this->rejection_reason = '';
        $this->showApprovalModal = true;
    }

    public function approveRequisition()
    {
        if (!$this->selectedRequisitionId) return;

        if (!$this->canUserManageWarehouse()) {
            abort(403, 'Anda tidak memiliki hak otorisasi untuk memproses persetujuan permintaan barang.');
        }

        $req = InventoryRequisition::findOrFail($this->selectedRequisitionId);
        if ($req->requester_id === auth()->id()) {
            abort(403, 'Anda tidak dapat menyetujui permintaan yang Anda ajukan sendiri.');
        }

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

        if (!$this->canUserManageWarehouse()) {
            abort(403, 'Anda tidak memiliki hak otorisasi untuk memproses persetujuan permintaan barang.');
        }

        $this->validate([
            'rejection_reason' => 'required|string|min:5|max:500',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
            'rejection_reason.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $req = InventoryRequisition::findOrFail($this->selectedRequisitionId);
        if ($req->requester_id === auth()->id()) {
            abort(403, 'Anda tidak dapat menolak permintaan yang Anda ajukan sendiri.');
        }

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

    public function confirmReceipt(int $requisitionId)
    {
        $req = InventoryRequisition::with('dispatches')->findOrFail($requisitionId);
        $user = auth()->user();

        // Hanya pemohon atau teknisi yang ditunjuk yang dapat konfirmasi terima barang
        if ($req->requester_id !== $user->id && $req->designated_technician_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403, 'Anda tidak berwenang mengonfirmasi penerimaan barang untuk permintaan ini.');
        }

        foreach ($req->dispatches as $dispatch) {
            if ($dispatch->status === 'dispatched') {
                $dispatch->update([
                    'status' => 'received',
                    'received_at' => now(),
                ]);
            }
        }

        $req->update(['status' => 'received']);

        session()->flash('success', "Konfirmasi penerimaan barang untuk Permintaan No. {$req->requisition_number} berhasil. Stok telah resmi berada dalam penguasaan Anda.");
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
        $user = auth()->user();

        $query = InventoryRequisition::with(['warehouse', 'requester', 'designatedTechnician', 'customer', 'items.item'])
            ->latest('id');

        // Reseller hanya melihat permintaannya sendiri
        if ($user && $user->isReseller()) {
            $query->where('requester_id', $user->id);
        } elseif ($user && $user->isTechnician() && !$this->canUserManageWarehouse()) {
            // Teknisi melihat permintaannya sendiri atau penugasan dirinya
            $query->where(function ($q) use ($user) {
                $q->where('requester_id', $user->id)
                  ->orWhere('designated_technician_id', $user->id);
            });
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

        $perPageInt = match ((string) $this->perPage) {
            '50' => 50,
            '100' => 100,
            'all' => max(1, (clone $query)->count()),
            default => 20,
        };

        $requisitions = $query->paginate($perPageInt);
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $availableItems = Item::active()->orderBy('name')->get();
        $customers = Customer::query()
            ->whereRaw("LOWER(COALESCE(name, '')) NOT LIKE 'guest%'")
            ->whereRaw("LOWER(COALESCE(code, '')) NOT LIKE 'gst-%'")
            ->where('status', '!=', 'inactive')
            ->with(['customerServices.serviceProfile'])
            ->orderBy('name')
            ->get();
        $technicians = User::getEligibleTechnicians();

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
            'showCreateModal' => $this->showCreateModal,
            'showApprovalModal' => $this->showApprovalModal,
            'showDetailModal' => $this->showDetailModal,
            'requisitionItems' => $this->requisitionItems,
            'distribution_method' => $this->distribution_method,
            'canCreateRequisition' => $this->canUserCreateRequisition(),
            'canManageWarehouse' => $this->canUserManageWarehouse(),
            'isReseller' => (bool) $user?->isReseller(),
            'isTechnician' => (bool) $user?->isTechnician(),
        ])->layout('layouts.app', ['title' => 'Permintaan Barang - dsBilling']);
    }
}
