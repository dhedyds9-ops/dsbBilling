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
    public string $typeFilter = ''; // all, dispatch_request, procurement_request
    public string $warehouseFilter = '';
    public string|int $perPage = 20;

    // Marketplace / Tabs
    public string $activeTab = 'list'; // 'list' (Riwayat) or 'catalog' (Katalog Marketplace Gudang)
    public string $catalogSearch = '';
    public string $catalogCategory = '';

    // Modal Form Permintaan
    public bool $showCreateModal = false;
    public string $requisition_type = 'dispatch_request'; // dispatch_request (Ready Stock) or procurement_request (Restock/Pengadaan)
    public string $urgency = 'normal'; // normal, urgent
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

    // Modal Tautkan Pengeluaran yang Sudah Ada (Link Existing Dispatch)
    public bool $showLinkDispatchModal = false;
    public ?int $linkingRequisitionId = null;
    public ?int $selectedExistingDispatchId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'warehouseFilter' => ['except' => ''],
        'activeTab' => ['except' => 'list'],
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

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingCatalogSearch()
    {
        $this->resetPage();
    }

    public function updatingCatalogCategory()
    {
        $this->resetPage();
    }

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
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

    public function openCreateForStock(int $itemId)
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

        $item = Item::find($itemId);
        $stockQty = 0;
        if ($item && $wh) {
            $stk = \App\Models\Inventory\InventoryStock::where('warehouse_id', $wh->id)->where('item_id', $item->id)->first();
            $stockQty = (float)($stk?->quantity ?? 0);
            if ($item->is_serialized) {
                $sc = (float) \App\Models\Inventory\ItemSerial::where('item_id', $item->id)
                    ->where('warehouse_id', $wh->id)
                    ->where('status', 'in_warehouse')
                    ->count();
                $stockQty = max($stockQty, $sc);
            }
        }

        $this->requisition_type = ($stockQty > 0) ? 'dispatch_request' : 'procurement_request';
        $this->requisitionItems = [
            [
                'item_id' => $itemId,
                'quantity' => 1,
                'unit' => $item?->unit ?? 'pcs',
                'notes' => '',
            ]
        ];

        if (auth()->user()?->isReseller()) {
            $this->work_order_type = 'stock_topup';
            $this->distribution_method = 'pickup_reseller';
        }

        $this->showCreateModal = true;
    }

    public function resetCreateForm()
    {
        $this->warehouse_id = null;
        $this->requisition_type = 'dispatch_request';
        $this->urgency = 'normal';
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

                if ($this->warehouse_id) {
                    $stk = \App\Models\Inventory\InventoryStock::where('warehouse_id', $this->warehouse_id)->where('item_id', $itemId)->first();
                    $qtyStock = (float)($stk?->quantity ?? 0.0);
                    if ($item && $item->is_serialized) {
                        $serialCount = (float) \App\Models\Inventory\ItemSerial::where('item_id', $itemId)
                            ->where('warehouse_id', $this->warehouse_id)
                            ->where('status', 'in_warehouse')
                            ->count();
                        $qtyStock = max($qtyStock, $serialCount);
                    }
                    if ($qtyStock <= 0) {
                        $this->requisition_type = 'procurement_request';
                    }
                }
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
            'requisition_type' => 'required|in:dispatch_request,procurement_request',
            'urgency' => 'required|in:normal,urgent',
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
            'requisition_type.required' => 'Jenis pengajuan wajib dipilih.',
            'work_order_type.required' => 'Jenis peruntukan wajib dipilih.',
            'requisitionItems.required' => 'Minimal harus ada 1 barang yang diminta.',
            'requisitionItems.*.item_id.required' => 'Barang pada setiap baris wajib dipilih.',
            'requisitionItems.*.quantity.min' => 'Jumlah barang harus lebih besar dari 0.',
        ]);

        $warehouse = Warehouse::findOrFail($this->warehouse_id);
        $user = auth()->user();

        // Validasi ketersediaan stok fisik jika tipe adalah dispatch_request (Pengambilan Barang Ready)
        if ($this->requisition_type === 'dispatch_request') {
            foreach ($this->requisitionItems as $line) {
                $itemId = (int)$line['item_id'];
                $itemObj = Item::find($itemId);
                $stk = \App\Models\Inventory\InventoryStock::where('warehouse_id', $warehouse->id)->where('item_id', $itemId)->first();
                $qtyStock = (float)($stk?->quantity ?? 0.0);
                if ($itemObj && $itemObj->is_serialized) {
                    $serialCount = (float) \App\Models\Inventory\ItemSerial::where('item_id', $itemId)
                        ->where('warehouse_id', $warehouse->id)
                        ->where('status', 'in_warehouse')
                        ->count();
                    $qtyStock = max($qtyStock, $serialCount);
                }

                if ($qtyStock < (float)$line['quantity']) {
                    $unit = $itemObj?->unit ?? 'unit';
                    $name = $itemObj?->name ?? 'Barang';
                    $this->addError('requisitionItems', "Stok fisik '{$name}' di {$warehouse->name} tidak mencukupi (Tersedia: {$qtyStock} {$unit}, diminta: {$line['quantity']} {$unit}). Silakan ubah jenis pengajuan menjadi 'Permohonan Pengadaan Barang Baru (Restock)' jika membutuhkan barang baru dari vendor.");
                    return;
                }
            }
        }

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
            $isProcurement = ($this->requisition_type === 'procurement_request');
            DB::transaction(function () use ($warehouse, $requesterType, $user, $designatedTechId, $customerId) {
                $req = InventoryRequisition::create([
                    'requester_id' => $user->id,
                    'requester_type' => $requesterType,
                    'requisition_type' => $this->requisition_type,
                    'urgency' => $this->urgency,
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

            $successMsg = $isProcurement
                ? 'Permohonan pengadaan barang (Restock) berhasil diajukan dan diteruskan ke Admin Gudang/Manajemen untuk pemesanan vendor.'
                : 'Permintaan pengambilan barang berhasil diajukan dan menunggu persetujuan/pengeluaran barang oleh Admin Gudang.';

            session()->flash('success', $successMsg);
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

        $isProcurement = ($req->requisition_type === 'procurement_request');
        $successMsg = $isProcurement
            ? "Permohonan Pengadaan No. {$req->requisition_number} telah disetujui. Silakan tindak lanjuti pembelian/inbound dari vendor."
            : "Permintaan No. {$req->requisition_number} telah disetujui. Siap untuk pengeluaran barang (dispatch).";

        session()->flash('success', $successMsg);
        $this->showApprovalModal = false;
        $this->selectedRequisitionId = null;
    }

    public function approveAndDispatch()
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

        $reqId = $req->id;
        $reqNo = $req->requisition_number;
        $this->showApprovalModal = false;
        $this->selectedRequisitionId = null;

        session()->flash('success', "Permintaan No. {$reqNo} telah disetujui. Membuka form pengeluaran barang (dispatch)...");

        return redirect()->route('inventory.dispatches.index', ['requisition_id' => $reqId]);
    }

    public function openLinkDispatchModal(int $reqId)
    {
        if (!$this->canUserManageWarehouse()) {
            abort(403, 'Anda tidak memiliki hak otorisasi.');
        }

        $this->linkingRequisitionId = $reqId;
        $this->selectedExistingDispatchId = null;
        $this->showLinkDispatchModal = true;
    }

    public function closeLinkDispatchModal()
    {
        $this->showLinkDispatchModal = false;
        $this->linkingRequisitionId = null;
        $this->selectedExistingDispatchId = null;
    }

    public function saveLinkDispatch()
    {
        if (!$this->canUserManageWarehouse()) {
            abort(403, 'Anda tidak memiliki hak otorisasi.');
        }

        $this->validate([
            'linkingRequisitionId' => 'required|exists:inventory_requisitions,id',
            'selectedExistingDispatchId' => 'required|exists:inventory_dispatches,id',
        ], [
            'selectedExistingDispatchId.required' => 'Silakan pilih Surat Jalan / Pengeluaran Barang yang akan ditautkan.',
        ]);

        $req = InventoryRequisition::with('items')->findOrFail($this->linkingRequisitionId);
        $dispatch = \App\Models\Inventory\InventoryDispatch::with('items')->findOrFail($this->selectedExistingDispatchId);

        DB::transaction(function () use ($req, $dispatch) {
            // 1. Tautkan dispatch ke requisition
            $dispatch->update(['requisition_id' => $req->id]);

            // 2. Tautkan custody yang dibuat oleh dispatch ini
            \App\Models\Inventory\InventoryCustody::where('dispatch_id', $dispatch->id)
                ->update(['requisition_id' => $req->id]);

            // 3. Update quantity_dispatched pada requisition items
            foreach ($dispatch->items as $dItem) {
                $reqItem = $req->items()->where('item_id', $dItem->item_id)->first();
                if ($reqItem) {
                    $reqItem->increment('quantity_dispatched', $dItem->quantity);
                }
            }

            // 4. Ubah status requisition menjadi dispatched
            $req->update(['status' => 'dispatched']);
        });

        session()->flash('success', "Permintaan No. {$req->requisition_number} berhasil ditautkan ke Surat Jalan No. {$dispatch->dispatch_number}! Status pengajuan kini TELAH DIKELUARKAN (Dispatched).");
        $this->closeLinkDispatchModal();
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

    public function exportExcel()
    {
        $filename = 'permintaan-barang-requisitions-' . date('Ymd-His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\Inventory\RequisitionExport($this->search, $this->statusFilter),
            $filename
        );
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

        if (!empty($this->typeFilter)) {
            $query->where('requisition_type', $this->typeFilter);
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

        $targetWarehouseId = $this->warehouse_id ?: ($warehouses->first()?->id ?? null);
        $catalogStockMap = [];
        if ($targetWarehouseId) {
            $stocks = \App\Models\Inventory\InventoryStock::where('warehouse_id', $targetWarehouseId)->get()->keyBy('item_id');
            $serials = \App\Models\Inventory\ItemSerial::where('warehouse_id', $targetWarehouseId)->where('status', 'in_warehouse')
                ->selectRaw('item_id, count(*) as count')->groupBy('item_id')->pluck('count', 'item_id');
            foreach ($availableItems as $ai) {
                $base = (float)($stocks[$ai->id]->quantity ?? 0);
                if ($ai->is_serialized) {
                    $serialCount = (float)($serials[$ai->id] ?? 0);
                    $base = max($base, $serialCount);
                }
                $catalogStockMap[$ai->id] = $base;
            }
        }

        // Query katalog untuk Marketplace
        $catalogItemsQuery = Item::active()->orderBy('name');
        if (!empty($this->catalogSearch)) {
            $cs = '%' . trim($this->catalogSearch) . '%';
            $catalogItemsQuery->where(function ($q) use ($cs) {
                $q->where('name', 'like', $cs)
                    ->orWhere('sku', 'like', $cs)
                    ->orWhere('brand', 'like', $cs)
                    ->orWhere('model', 'like', $cs);
            });
        }
        if (!empty($this->catalogCategory)) {
            $catalogItemsQuery->where('category', $this->catalogCategory);
        }
        $catalogItems = $catalogItemsQuery->get();
        $categories = Item::active()->whereNotNull('category')->distinct()->pluck('category')->sort()->values();

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

        // Hitung ketersediaan stok fisik di gudang sumber untuk barang yang dipilih
        $warehouseStocks = [];
        if ($this->warehouse_id) {
            foreach ($this->requisitionItems as $row) {
                if (!empty($row['item_id'])) {
                    $itemId = (int) $row['item_id'];
                    $itemObj = Item::find($itemId);
                    $stk = \App\Models\Inventory\InventoryStock::where('warehouse_id', $this->warehouse_id)
                        ->where('item_id', $itemId)
                        ->first();
                    $qtyStock = $stk ? (float) $stk->quantity : 0.0;

                    if ($itemObj && $itemObj->is_serialized) {
                        $physicalCount = (float) \App\Models\Inventory\ItemSerial::where('item_id', $itemId)
                            ->where('warehouse_id', $this->warehouse_id)
                            ->where('status', 'in_warehouse')
                            ->count();
                        $qtyStock = max($qtyStock, $physicalCount);
                    }

                    $warehouseStocks[$itemId] = $qtyStock;
                }
            }
        }

        // Available Dispatches untuk ditautkan (Reconciliation)
        $availableDispatchesToLink = collect();
        $linkingRequisition = null;
        if ($this->showLinkDispatchModal && $this->linkingRequisitionId) {
            $linkingRequisition = InventoryRequisition::with(['items.item', 'requester'])->find($this->linkingRequisitionId);
            if ($linkingRequisition) {
                $availableDispatchesToLink = \App\Models\Inventory\InventoryDispatch::with(['items.item', 'recipient'])
                    ->where(function ($q) use ($linkingRequisition) {
                        $q->whereNull('requisition_id')
                          ->orWhere('requisition_id', $linkingRequisition->id);
                    })
                    ->where(function ($q) use ($linkingRequisition) {
                        if ($linkingRequisition->requester_id) {
                            $q->where('recipient_id', $linkingRequisition->requester_id)
                              ->orWhere('recipient_id', $linkingRequisition->designated_technician_id);
                        }
                    })
                    ->latest('id')
                    ->limit(30)
                    ->get();

                if ($availableDispatchesToLink->isEmpty()) {
                    $availableDispatchesToLink = \App\Models\Inventory\InventoryDispatch::with(['items.item', 'recipient'])
                        ->whereNull('requisition_id')
                        ->latest('id')
                        ->limit(20)
                        ->get();
                }
            }
        }

        return view('livewire.inventory.requisition.index', [
            'requisitions' => $requisitions,
            'warehouses' => $warehouses,
            'availableItems' => $availableItems,
            'catalogItems' => $catalogItems,
            'catalogStockMap' => $catalogStockMap,
            'categories' => $categories,
            'targetWarehouseId' => $targetWarehouseId,
            'activeTab' => $this->activeTab,
            'requisition_type' => $this->requisition_type,
            'urgency' => $this->urgency,
            'warehouseStocks' => $warehouseStocks,
            'customers' => $customers,
            'technicians' => $technicians,
            'selectedRequisition' => $selectedRequisition,
            'showCreateModal' => $this->showCreateModal,
            'showApprovalModal' => $this->showApprovalModal,
            'showDetailModal' => $this->showDetailModal,
            'showLinkDispatchModal' => $this->showLinkDispatchModal,
            'linkingRequisitionId' => $this->linkingRequisitionId,
            'linkingRequisition' => $linkingRequisition,
            'availableDispatchesToLink' => $availableDispatchesToLink,
            'selectedExistingDispatchId' => $this->selectedExistingDispatchId,
            'requisitionItems' => $this->requisitionItems,
            'distribution_method' => $this->distribution_method,
            'canCreateRequisition' => $this->canUserCreateRequisition(),
            'canManageWarehouse' => $this->canUserManageWarehouse(),
            'isReseller' => (bool) $user?->isReseller(),
            'isTechnician' => (bool) $user?->isTechnician(),
        ])->layout('layouts.app', ['title' => 'Permintaan Barang - dsBilling']);
    }
}
