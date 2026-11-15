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
    public string|int $perPage = 20;

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

    // Serial Number Lookup & Checker
    public ?array $snLookupResult = null;
    public string $snLookupQuery = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'warehouseFilter' => ['except' => ''],
        'requisition_id' => ['except' => null],
        'perPage' => ['except' => 20],
    ];

    public function updatingPerPage()
    {
        $this->resetPage();
    }

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
        $firstWh = Warehouse::active()->first();
        if ($firstWh) {
            $this->warehouse_id = $firstWh->id;
        }
        $this->addItemRow();
        $this->showCreateModal = true;
    }

    public function openCreateFromRequisition(int $reqId)
    {
        if (! $this->ensureWorkforceAttendance()) {
            return;
        }

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
        $this->snLookupResult = null;
        $this->snLookupQuery = '';
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

    public function updatedRecipientId($val)
    {
        if ($val) {
            $user = User::withoutGlobalScopes()->find($val);
            if ($user) {
                if ($user->hasRole(['technician', 'teknisi']) || strtoupper($user->job_function ?? '') === 'TECHNICIAN') {
                    $this->recipient_type = 'technician';
                } elseif ($user->isReseller() || $user->hasRole(['reseller', 'Reseller'])) {
                    $this->recipient_type = 'reseller';
                } else {
                    $this->recipient_type = 'internal';
                }
            }
        }
    }

    public function addScannedSerial(int $itemIndex, string $serialNumber)
    {
        $serial = strtoupper(trim($serialNumber));
        if (empty($serial)) return;

        if (!isset($this->dispatchItems[$itemIndex])) return;

        $itemRow = &$this->dispatchItems[$itemIndex];
        $itemId = (int) ($itemRow['item_id'] ?? 0);
        $maxQty = (int) ($itemRow['quantity'] ?? 1);

        $currentSelected = (array) ($itemRow['selected_serials'] ?? []);
        $currentRaw = !empty($itemRow['serials_raw']) 
            ? array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $itemRow['serials_raw'])))) 
            : [];

        if (in_array($serial, $currentSelected) || in_array($serial, $currentRaw)) {
            session()->flash("serial_error_{$itemIndex}", "SN '{$serial}' sudah ada dalam daftar pilihan.");
            return;
        }

        // Cek apakah SN ini berstatus in_warehouse di gudang yang dipilih
        $existsInWarehouse = false;
        if ($this->warehouse_id && $itemId) {
            $existsInWarehouse = ItemSerial::where('item_id', $itemId)
                ->where('warehouse_id', $this->warehouse_id)
                ->where('status', 'in_warehouse')
                ->where('serial_number', $serial)
                ->exists();
        }

        if ($existsInWarehouse) {
            $currentSelected[] = $serial;
            $itemRow['selected_serials'] = array_values(array_unique($currentSelected));
            session()->flash("serial_success_{$itemIndex}", "SN '{$serial}' (dari gudang) berhasil dipilih.");
        } else {
            $currentRaw[] = $serial;
            $itemRow['serials_raw'] = implode("\n", array_values(array_unique($currentRaw)));
            session()->flash("serial_success_{$itemIndex}", "SN '{$serial}' ditambahkan sebagai scan langsung / registrasi baru.");
        }

        $totalNow = count($itemRow['selected_serials'] ?? []) + (!empty($itemRow['serials_raw']) ? count(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $itemRow['serials_raw'])))) : 0);
        if ($totalNow > $maxQty) {
            session()->flash("serial_warning_{$itemIndex}", "Perhatian: SN terpilih ({$totalNow}) melebihi kebutuhan unit ({$maxQty}).");
        }
    }

    public function removeSelectedSerial(int $itemIndex, string $serialNumber)
    {
        if (!isset($this->dispatchItems[$itemIndex])) return;

        $itemRow = &$this->dispatchItems[$itemIndex];

        // Hapus dari selected_serials jika ada
        if (!empty($itemRow['selected_serials'])) {
            $itemRow['selected_serials'] = array_values(array_filter(
                (array) $itemRow['selected_serials'],
                fn($sn) => $sn !== $serialNumber
            ));
        }

        // Hapus dari serials_raw jika ada
        if (!empty($itemRow['serials_raw'])) {
            $rawList = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $itemRow['serials_raw']))));
            $rawList = array_values(array_filter($rawList, fn($sn) => $sn !== $serialNumber));
            $itemRow['serials_raw'] = implode("\n", $rawList);
        }

        session()->flash("serial_success_{$itemIndex}", "SN '{$serialNumber}' telah dihapus dari daftar pilihan.");
    }

    public function lookupSerial(string $serialNumber = '')
    {
        $sn = strtoupper(trim($serialNumber ?: $this->snLookupQuery));
        if (empty($sn)) return;

        $this->snLookupQuery = $sn;
        $serial = ItemSerial::withoutGlobalScopes()
            ->with(['item', 'warehouse', 'custodian', 'customer', 'branch'])
            ->where('serial_number', $sn)
            ->first();

        if ($serial) {
            $this->snLookupResult = [
                'found' => true,
                'serial_number' => $serial->serial_number,
                'item_name' => $serial->item?->name ?? 'Perangkat',
                'status' => $serial->status,
                'status_label' => match($serial->status) {
                    'in_warehouse' => 'Tersedia di Gudang (' . ($serial->warehouse?->name ?? 'Gudang') . ')',
                    'in_custody_technician' => 'Dipegang Teknisi (' . ($serial->custodian?->name ?? '-') . ')',
                    'in_custody_reseller' => 'Dipegang Reseller (' . ($serial->custodian?->name ?? '-') . ')',
                    'installed' => 'Terpasang di Pelanggan (' . ($serial->customer?->name ?? '-') . ')',
                    'damaged' => 'Kondisi Rusak (Damaged)',
                    'returned_check' => 'Pemeriksaan Retur',
                    default => $serial->status,
                },
                'warehouse' => $serial->warehouse?->name ?? '-',
                'custodian' => $serial->custodian?->name ?? '-',
                'customer' => $serial->customer?->name ?? '-',
            ];
        } else {
            $this->snLookupResult = [
                'found' => false,
                'serial_number' => $sn,
                'message' => 'SN belum terdaftar di database. Anda dapat langsung mengeluarkannya, sistem akan otomatis mendaftarkannya ke sistem.',
            ];
        }
    }

    public function clearLookupResult()
    {
        $this->snLookupResult = null;
        $this->snLookupQuery = '';
    }

    public function saveDispatch(StockMutationService $mutationService)
    {
        if (! $this->ensureWorkforceAttendance()) {
            return;
        }

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

        // Validasi penerima dan tipe hak kepemilikan (custody)
        $recipientUser = User::withoutGlobalScopes()->find($this->recipient_id);
        if ($recipientUser) {
            if ($recipientUser->hasRole(['technician', 'teknisi']) || strtoupper($recipientUser->job_function ?? '') === 'TECHNICIAN') {
                $this->recipient_type = 'technician';
            } elseif ($recipientUser->isReseller() || $recipientUser->hasRole(['reseller', 'Reseller'])) {
                $this->recipient_type = 'reseller';
            } else {
                $this->recipient_type = 'internal';
            }
        }

        // Validasi serial numbers untuk barang berserial
        $itemsPayload = [];
        foreach ($this->dispatchItems as $idx => $row) {
            $item = Item::findOrFail($row['item_id']);
            $qty = (float) $row['quantity'];
            $serials = [];

            if ($item->is_serialized) {
                // Ambil gabungan dari selected_serials array dan serials_raw
                $selected = (array) ($row['selected_serials'] ?? []);
                $raw = !empty($row['serials_raw']) 
                    ? array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $row['serials_raw'])))) 
                    : [];
                $serials = array_values(array_unique(array_merge($selected, $raw)));

                if (count($serials) !== (int) $qty) {
                    $this->addError("dispatchItems.{$idx}.quantity", "Barang '{$item->name}' memerlukan " . (int)$qty . " Serial Number. Terpilih/diinput: " . count($serials));
                    return;
                }
            }

            // Validasi saldo stok fisik di gudang sebelum eksekusi
            $stock = \App\Models\Inventory\InventoryStock::where('warehouse_id', $this->warehouse_id)
                ->where('item_id', $item->id)
                ->first();
            $availableStock = $stock ? (float) $stock->quantity : 0.0;

            // Jika barang berserial, hitung juga jumlah unit fisik berstatus 'in_warehouse' di gudang ini
            if ($item->is_serialized) {
                $physicalSerialCount = (float) ItemSerial::where('item_id', $item->id)
                    ->where('warehouse_id', $this->warehouse_id)
                    ->where('status', 'in_warehouse')
                    ->count();
                $availableStock = max($availableStock, $physicalSerialCount);
            }

            if ($availableStock < $qty) {
                $this->addError("dispatchItems.{$idx}.quantity", "Stok '{$item->name}' di gudang tidak mencukupi (Tersedia: {$availableStock} {$item->unit}, diminta: {$qty} {$item->unit}). Silakan lakukan Barang Masuk (Inbound) terlebih dahulu.");
                return;
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
                    })
                    ->orWhereHas('custodies.itemSerial', function ($sq) use ($s) {
                        $sq->where('serial_number', 'like', $s);
                    });
            });
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

        $dispatches = $query->paginate($perPageInt);
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $availableItems = Item::active()->orderBy('name')->get();
        $customers = Customer::query()
            ->whereRaw("LOWER(COALESCE(name, '')) NOT LIKE 'guest%'")
            ->whereRaw("LOWER(COALESCE(code, '')) NOT LIKE 'gst-%'")
            ->where('status', '!=', 'inactive')
            ->orderBy('name')
            ->limit(100)
            ->get();

        // Penerima options (Teknisi, Reseller, Internal)
        $recipients = User::inventoryRecipients()->orderBy('name')->get();

        // Available serials & stocks per warehouse for selected items in modal
        $availableSerials = [];
        $warehouseStocks = [];
        if ($this->warehouse_id) {
            foreach ($this->dispatchItems as $row) {
                if (!empty($row['item_id'])) {
                    $itemId = (int) $row['item_id'];
                    $stk = \App\Models\Inventory\InventoryStock::where('warehouse_id', $this->warehouse_id)
                        ->where('item_id', $itemId)
                        ->first();
                    $qtyStock = $stk ? (float) $stk->quantity : 0.0;

                    if (!empty($row['is_serialized'])) {
                        $serialsInWh = ItemSerial::where('item_id', $itemId)
                            ->where('warehouse_id', $this->warehouse_id)
                            ->where('status', 'in_warehouse')
                            ->orderBy('serial_number')
                            ->get();
                        $availableSerials[$itemId] = $serialsInWh;
                        // Sinkronkan ketersediaan stok dengan jumlah serial fisik nyata di gudang
                        $qtyStock = max($qtyStock, (float) $serialsInWh->count());
                    }

                    $warehouseStocks[$itemId] = $qtyStock;
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
            'warehouseStocks' => $warehouseStocks,
            'selectedDispatch' => $selectedDispatch,
            'showCreateModal' => $this->showCreateModal,
            'showDetailModal' => $this->showDetailModal,
            'dispatchItems' => $this->dispatchItems,
            'distribution_method' => $this->distribution_method,
        ])->layout('layouts.app', ['title' => 'Pengeluaran Barang (Dispatch) - dsBilling']);
    }
}
