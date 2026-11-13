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
    public string|int $perPage = 20;

    // Modal Form Penerimaan Barang (Create & Edit Lengkap)
    public bool $showCreateModal = false;
    public ?int $editInboundId = null;
    public ?string $editInboundNumber = null;
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

    // Backward-compatible properties
    public bool $showEditModal = false;
    public string $edit_supplier_name = '';
    public string $edit_reference_doc = '';
    public string $edit_received_date = '';
    public string $edit_notes = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'warehouseFilter' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'perPage' => ['except' => 20],
    ];

    public function updatingPerPage()
    {
        $this->resetPage();
    }

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

    protected function ensureWorkforceAttendance(): bool
    {
        $user = auth()->user();
        if (! $user) return true;

        if ($user->isSuperAdmin() && empty($user->job_function) && ! $user->hasRole('admin_gudang')) {
            return true;
        }

        $isWorkforce = $user->hasRole(['admin_gudang', 'technician', 'noc', 'kasir', 'customer_service'])
            || in_array(strtoupper((string) ($user->job_function ?? '')), ['TECHNICIAN', 'NOC', 'GUDANG', 'KASIR', 'CS'])
            || str_contains(strtolower((string) ($user->job_title ?? '')), 'gudang')
            || str_contains(strtolower((string) ($user->job_title ?? '')), 'logistik');

        if (! $isWorkforce) return true;

        $hasCheckedIn = \App\Models\Workforce\Attendance::where('technician_id', $user->id)
            ->whereDate('date', \Carbon\Carbon::today())
            ->whereNotNull('checked_in_at')
            ->exists();

        if (! $hasCheckedIn) {
            $this->dispatch('open-attendance-prompt');
            $this->dispatch('toast', [
                'type' => 'warning',
                'message' => "Halo {$user->name}, Anda belum melakukan Absen Hadir hari ini. Silakan klik tombol Absen Masuk untuk memulai shift dan melayani transaksi.",
            ]);
            return false;
        }

        return true;
    }

    public function openCreateModal()
    {
        if (! $this->ensureWorkforceAttendance()) {
            return;
        }

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
        $this->editInboundId = null;
        $this->editInboundNumber = null;
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
        if (! $this->ensureWorkforceAttendance()) {
            return;
        }

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
                if ($this->editInboundId) {
                    $inbound = InventoryInbound::findOrFail($this->editInboundId);
                    if ($inbound->status === 'cancelled') {
                        throw new \RuntimeException('Dokumen yang telah dibatalkan tidak dapat diedit kembali.');
                    }

                    // 1. Rollback previous stock & serials secara aman untuk revisi
                    $mutationService->rollbackInboundForRevision($inbound, auth()->id());

                    // 2. Update inbound header & reactivate status
                    $inbound->update([
                        'warehouse_id' => $warehouse->id,
                        'branch_id' => $warehouse->branch_id,
                        'source_type' => $this->source_type,
                        'supplier_name' => $this->supplier_name ?: null,
                        'reference_doc' => $this->reference_doc ?: null,
                        'received_date' => $this->received_date,
                        'status' => 'received',
                        'notes' => $this->notes ?: null,
                        'updated_by' => auth()->id(),
                    ]);

                    // 3. Delete previous inbound items
                    $inbound->items()->delete();
                } else {
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
                }

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

            $msg = $this->editInboundId
                ? "Data penerimaan barang masuk {$this->editInboundNumber} dan saldo stok gudang berhasil diperbarui."
                : 'Penerimaan barang masuk berhasil disimpan dan stok gudang telah diperbarui.';
            session()->flash('success', $msg);
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

    public function openEditModal(int $id)
    {
        if (! $this->ensureWorkforceAttendance()) {
            return;
        }

        $inbound = InventoryInbound::with(['items.item'])->findOrFail($id);
        if ($inbound->status === 'cancelled') {
            session()->flash('error', 'Dokumen penerimaan yang sudah dibatalkan tidak dapat diedit.');
            return;
        }

        $this->resetCreateForm();
        $this->editInboundId = $inbound->id;
        $this->editInboundNumber = $inbound->inbound_number;
        $this->warehouse_id = $inbound->warehouse_id;
        $this->source_type = $inbound->source_type ?? 'supplier';
        $this->supplier_name = $inbound->supplier_name ?? '';
        $this->reference_doc = $inbound->reference_doc ?? '';
        $this->received_date = $inbound->received_date ? $inbound->received_date->format('Y-m-d') : date('Y-m-d');
        $this->notes = $inbound->notes ?? '';

        $this->inboundItems = [];
        foreach ($inbound->items as $inItem) {
            $rawSerials = !empty($inItem->serials) ? implode("\n", $inItem->serials) : '';
            $this->inboundItems[] = [
                'item_id' => $inItem->item_id,
                'quantity' => $inItem->quantity,
                'unit_cost' => $inItem->unit_cost ? (float) $inItem->unit_cost : null,
                'serials_raw' => $rawSerials,
                'is_serialized' => (bool) ($inItem->item?->is_serialized ?? false),
                'unit' => $inItem->item?->unit ?? 'unit',
            ];
        }

        if (empty($this->inboundItems)) {
            $this->addItemRow();
        }

        $this->showCreateModal = true;
        $this->showEditModal = false;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    public function cancelInbound(int $id, StockMutationService $mutationService)
    {
        try {
            $inbound = InventoryInbound::findOrFail($id);
            $inboundNo = $inbound->inbound_number;

            $mutationService->cancelInbound($id, 'Dibatalkan oleh ' . auth()->user()->name, auth()->id());

            session()->flash('success', "Dokumen penerimaan \"{$inboundNo}\" berhasil dibatalkan dan saldo stok fisik telah dikembalikan.");
            $this->closeDetailModal();
        } catch (\Throwable $e) {
            session()->flash('error', "Gagal membatalkan penerimaan: " . $e->getMessage());
        }
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

        $perPageInt = match ((string) $this->perPage) {
            '50' => 50,
            '100' => 100,
            'all' => max(1, (clone $query)->count()),
            default => 20,
        };

        $inbounds = $query->paginate($perPageInt);
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
