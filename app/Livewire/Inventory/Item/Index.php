<?php

namespace App\Livewire\Inventory\Item;

use App\Models\Inventory\Item;
use App\Models\Inventory\ItemSerial;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\InventoryStockMutation;
use App\Models\Master\Branch;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = '';
    public string $statusFilter = ''; // all, active, low_stock, serialized
    public string|int $perPage = 20;

    // Modal Form
    public bool $showFormModal = false;
    public ?int $itemId = null;
    public string $sku = '';
    public string $name = '';
    public string $category = 'ONU/ONT';
    public string $unit = 'unit';
    public string $brand = '';
    public string $model = '';
    public string $description = '';
    public int $min_stock = 5;
    public bool $is_serialized = true;
    public bool $is_reusable = true;
    public ?float $cost_price = null;
    public ?float $selling_price = null;
    public bool $is_active = true;
    public ?int $branch_id = null;

    // Modal Detail Serial & Tambah Unit
    public bool $showSerialsModal = false;
    public ?int $selectedItemIdForSerials = null;
    public string $selectedItemNameForSerials = '';
    public string $serialSearch = '';
    public bool $showAddSerialSection = false;
    public string $serialInputMode = 'single'; // 'single' or 'bulk'
    public ?int $serialWarehouseId = null;
    public string $newSerialNumber = '';
    public string $newMacAddress = '';
    public string $bulkSerials = '';
    public string $serialCondition = 'good'; // good, needs_check, damaged
    public string $serialNotes = '';
    public bool $autoSyncStock = true;

    // Modal Pasangkan ke Pelanggan (Assign Customer)
    public bool $showAssignCustomerModal = false;
    public ?int $assigningSerialId = null;
    public string $assigningSerialNumber = '';
    public ?int $assignCustomerId = null;
    public string $assignCustomerSearch = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'perPage' => ['except' => 20],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->sku = $this->generateSuggestedSku($this->category);
        $this->showFormModal = true;
    }

    public function openEditModal(int $id)
    {
        $item = Item::findOrFail($id);
        $this->itemId = $item->id;
        $this->sku = $item->sku ?? '';
        $this->name = $item->name ?? '';
        $this->category = $item->category ?? 'ONU/ONT';
        $this->unit = $item->unit ?? 'unit';
        $this->brand = $item->brand ?? '';
        $this->model = $item->model ?? '';
        $this->description = $item->description ?? '';
        $this->min_stock = (int) ($item->min_stock ?? 5);
        $this->is_serialized = (bool) $item->is_serialized;
        $this->is_reusable = (bool) $item->is_reusable;
        $this->cost_price = $item->cost_price ? (float) $item->cost_price : null;
        $this->selling_price = $item->selling_price ? (float) $item->selling_price : null;
        $this->is_active = (bool) $item->is_active;
        $this->branch_id = $item->branch_id;

        $this->showFormModal = true;
    }

    public function updatedCategory($val)
    {
        if (empty($this->itemId)) {
            $this->sku = $this->generateSuggestedSku($val);
            if (in_array($val, ['ONU/ONT', 'Router', 'OLT SFP'], true)) {
                $this->is_serialized = true;
                $this->unit = 'unit';
            } elseif ($val === 'Kabel Dropcore') {
                $this->is_serialized = false;
                $this->unit = 'meter';
            } else {
                $this->is_serialized = false;
                $this->unit = 'pcs';
            }
        }
    }

    private function generateSuggestedSku(string $category): string
    {
        $prefix = match ($category) {
            'ONU/ONT' => 'DEV-ONT-',
            'Router' => 'DEV-RTR-',
            'Kabel Dropcore' => 'MAT-DC-',
            'Fast Connector' => 'MAT-FC-',
            'Roset' => 'MAT-ROSET-',
            'Patchcord' => 'MAT-PC-',
            'Splitter' => 'MAT-SPLT-',
            'Clamp' => 'MAT-CLMP-',
            'Alat Kerja' => 'TOOL-',
            default => 'ITEM-',
        };

        $rand = strtoupper(substr(uniqid(), -4));
        return $prefix . $rand;
    }

    public function saveItem()
    {
        $this->validate([
            'sku' => 'required|string|max:80|unique:items,sku,' . ($this->itemId ?: 'NULL') . ',id',
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:80',
            'unit' => 'required|string|max:30',
            'min_stock' => 'required|integer|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'branch_id' => 'nullable|integer',
        ]);

        $data = [
            'sku' => trim($this->sku),
            'name' => trim($this->name),
            'category' => trim($this->category),
            'unit' => trim($this->unit),
            'brand' => trim($this->brand) ?: null,
            'model' => trim($this->model) ?: null,
            'description' => trim($this->description) ?: null,
            'min_stock' => $this->min_stock,
            'is_serialized' => $this->is_serialized,
            'is_reusable' => $this->is_reusable,
            'cost_price' => $this->cost_price,
            'selling_price' => $this->selling_price,
            'is_active' => $this->is_active,
            'branch_id' => $this->branch_id,
            'updated_by' => auth()->id(),
        ];

        if ($this->itemId) {
            $item = Item::findOrFail($this->itemId);
            $item->update($data);
            session()->flash('success', "Barang \"{$item->name}\" berhasil diperbarui.");
        } else {
            $data['created_by'] = auth()->id();
            Item::create($data);
            session()->flash('success', "Barang baru \"{$this->name}\" berhasil ditambahkan ke katalog.");
        }

        $this->showFormModal = false;
        $this->resetForm();
    }

    public function deleteItem(int $id)
    {
        $item = Item::findOrFail($id);
        $stockCount = $item->stocks()->sum('quantity');

        if ($stockCount > 0) {
            session()->flash('error', "Gagal menghapus! Barang \"{$item->name}\" masih memiliki stok ({$stockCount} {$item->unit}) di gudang.");
            return;
        }

        $item->delete();
        session()->flash('success', "Barang \"{$item->name}\" berhasil dihapus.");
    }

    public function openSerialsModal(int $itemId)
    {
        $item = Item::with('stocks')->findOrFail($itemId);
        $this->selectedItemIdForSerials = $item->id;
        $this->selectedItemNameForSerials = $item->name;
        $this->serialSearch = '';
        $this->showAddSerialSection = false;
        $this->serialInputMode = 'single';
        $this->newSerialNumber = '';
        $this->newMacAddress = '';
        $this->bulkSerials = '';
        $this->serialCondition = 'good';
        $this->serialNotes = '';

        // Default gudang: ambil dari stok gudang yang ada kuantitasnya, atau gudang aktif pertama
        $stockWithQty = $item->stocks()->where('quantity', '>', 0)->first();
        if ($stockWithQty) {
            $this->serialWarehouseId = $stockWithQty->warehouse_id;
        } else {
            $firstWarehouse = Warehouse::where('is_active', true)->first() ?? Warehouse::first();
            $this->serialWarehouseId = $firstWarehouse?->id;
        }

        $this->showSerialsModal = true;
    }

    public function toggleAddSerialSection()
    {
        $this->showAddSerialSection = !$this->showAddSerialSection;
    }

    public function saveSerial()
    {
        if (!$this->selectedItemIdForSerials) {
            session()->flash('serial_error', 'Pilih barang terlebih dahulu.');
            return;
        }

        $item = Item::findOrFail($this->selectedItemIdForSerials);

        if (!$this->serialWarehouseId) {
            session()->flash('serial_error', 'Gudang penyimpanan wajib dipilih.');
            return;
        }

        $warehouse = Warehouse::findOrFail($this->serialWarehouseId);

        if ($this->serialInputMode === 'single') {
            $sn = strtoupper(trim($this->newSerialNumber));
            if (empty($sn)) {
                session()->flash('serial_error', 'Serial Number tidak boleh kosong.');
                return;
            }

            // Cek apakah SN sudah ada di database untuk item ini
            $exists = ItemSerial::where('serial_number', $sn)->where('item_id', $item->id)->exists();
            if ($exists) {
                session()->flash('serial_error', "Serial Number '{$sn}' sudah terdaftar untuk barang ini.");
                return;
            }

            $mac = !empty($this->newMacAddress) ? strtoupper(trim($this->newMacAddress)) : null;

            DB::transaction(function () use ($item, $warehouse, $sn, $mac) {
                ItemSerial::create([
                    'item_id' => $item->id,
                    'warehouse_id' => $warehouse->id,
                    'branch_id' => $warehouse->branch_id ?? $item->branch_id,
                    'serial_number' => $sn,
                    'mac_address' => $mac,
                    'status' => 'in_warehouse',
                    'condition' => $this->serialCondition ?: 'good',
                    'notes' => $this->serialNotes ?: 'Didaftarkan langsung via Master Item',
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);

                if ($this->autoSyncStock) {
                    $this->reconcileWarehouseStock($warehouse->id, $item->id, "Pendaftaran Unit SN {$sn}");
                }
            });

            $this->newSerialNumber = '';
            $this->newMacAddress = '';
            $this->serialNotes = '';
            session()->flash('serial_success', "Berhasil menambahkan Serial Number '{$sn}' ke gudang {$warehouse->name}.");
        } else {
            // Mode Bulk / Multi-Line
            $raw = trim($this->bulkSerials);
            if (empty($raw)) {
                session()->flash('serial_error', 'Daftar Serial Number tidak boleh kosong.');
                return;
            }

            $lines = preg_split('/[\r\n]+/', $raw);
            $added = 0;
            $duplicates = 0;
            $skipped = 0;

            DB::transaction(function () use ($item, $warehouse, $lines, &$added, &$duplicates, &$skipped) {
                $seenInBatch = [];

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;

                    // Support format: "SN" atau "SN,MAC" atau "SN;MAC" atau "SN TAB MAC"
                    $parts = preg_split('/[,\t;]+/', $line);
                    $sn = strtoupper(trim($parts[0] ?? ''));
                    $mac = isset($parts[1]) && !empty(trim($parts[1])) ? strtoupper(trim($parts[1])) : null;

                    if (empty($sn)) {
                        $skipped++;
                        continue;
                    }

                    if (isset($seenInBatch[$sn])) {
                        $duplicates++;
                        continue;
                    }
                    $seenInBatch[$sn] = true;

                    // Cek duplikasi di database
                    if (ItemSerial::where('serial_number', $sn)->where('item_id', $item->id)->exists()) {
                        $duplicates++;
                        continue;
                    }

                    ItemSerial::create([
                        'item_id' => $item->id,
                        'warehouse_id' => $warehouse->id,
                        'branch_id' => $warehouse->branch_id ?? $item->branch_id,
                        'serial_number' => $sn,
                        'mac_address' => $mac,
                        'status' => 'in_warehouse',
                        'condition' => $this->serialCondition ?: 'good',
                        'notes' => $this->serialNotes ?: 'Didaftarkan massal via Master Item',
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);

                    $added++;
                }

                if ($this->autoSyncStock && $added > 0) {
                    $this->reconcileWarehouseStock($warehouse->id, $item->id, "Pendaftaran {$added} Unit SN Massal");
                }
            });

            $this->bulkSerials = '';
            $this->serialNotes = '';

            $msg = "Pendaftaran selesai: {$added} SN berhasil ditambahkan ke gudang {$warehouse->name}.";
            if ($duplicates > 0) {
                $msg .= " ({$duplicates} SN diabaikan karena sudah terdaftar).";
            }
            session()->flash('serial_success', $msg);
        }
    }

    private function reconcileWarehouseStock(int $warehouseId, int $itemId, string $actionReason): void
    {
        $physicalCount = ItemSerial::withoutGlobalScopes()
            ->where('warehouse_id', $warehouseId)
            ->where('item_id', $itemId)
            ->where('status', 'in_warehouse')
            ->count();

        $stock = InventoryStock::withoutGlobalScopes()
            ->where('warehouse_id', $warehouseId)
            ->where('item_id', $itemId)
            ->lockForUpdate()
            ->first();

        $currentQty = $stock ? (float) $stock->quantity : 0.0;

        if ($physicalCount > $currentQty) {
            $warehouse = Warehouse::withoutGlobalScopes()->find($warehouseId);
            $qtyDiff = $physicalCount - $currentQty;

            if ($stock) {
                $stock->update(['quantity' => $physicalCount]);
            } else {
                InventoryStock::create([
                    'warehouse_id' => $warehouseId,
                    'item_id' => $itemId,
                    'quantity' => $physicalCount,
                    'reserved_quantity' => 0,
                    'branch_id' => $warehouse?->branch_id,
                ]);
            }

            InventoryStockMutation::create([
                'warehouse_id' => $warehouseId,
                'item_id' => $itemId,
                'type' => 'inbound',
                'qty_in' => $qtyDiff,
                'qty_out' => 0,
                'balance_before' => $currentQty,
                'balance_after' => $physicalCount,
                'reference_type' => 'serial_registration',
                'reference_id' => null,
                'notes' => "Penyesuaian stok fisik: {$actionReason}",
                'actor_id' => auth()->id(),
                'branch_id' => $warehouse?->branch_id,
                'created_at' => now(),
            ]);
        }
    }

    public function deleteSerial(int $serialId)
    {
        $serial = ItemSerial::findOrFail($serialId);

        if ($serial->status !== 'in_warehouse') {
            session()->flash('serial_error', "Gagal menghapus! Serial '{$serial->serial_number}' tidak berstatus in_warehouse ({$serial->status}) atau sudah terpasang/didistribusikan.");
            return;
        }

        $sn = $serial->serial_number;
        $serial->delete();
        session()->flash('serial_success', "Serial Number '{$sn}' berhasil dihapus dari daftar.");
    }

    public function openAssignCustomerModal(int $serialId)
    {
        $serial = ItemSerial::findOrFail($serialId);
        $this->assigningSerialId = $serial->id;
        $this->assigningSerialNumber = $serial->serial_number;

        $customerId = $serial->customer_id;
        if (!$customerId && !empty($serial->serial_number)) {
            try {
                $onu = \App\Models\ISP\Onu::withoutGlobalScopes()
                    ->where('serial_number', $serial->serial_number)
                    ->first();
                if ($onu) {
                    $cs = \App\Models\Customer\CustomerService::withoutGlobalScopes()
                        ->where('onu_id', $onu->id)
                        ->latest('id')
                        ->first();
                    $customerId = $cs?->customer_id;
                }
            } catch (\Throwable $e) {
                // Abaikan kesalahan relasi opsional
            }
        }

        $this->assignCustomerId = $customerId;
        $this->assignCustomerSearch = '';
        $this->showAssignCustomerModal = true;
    }

    public function closeAssignCustomerModal()
    {
        $this->showAssignCustomerModal = false;
        $this->assigningSerialId = null;
        $this->assignCustomerId = null;
        $this->assignCustomerSearch = '';
    }

    public function saveAssignCustomer()
    {
        if (!$this->assigningSerialId || !$this->assignCustomerId) {
            session()->flash('serial_error', 'Silakan pilih pelanggan terlebih dahulu.');
            return;
        }

        $serial = ItemSerial::findOrFail($this->assigningSerialId);
        $customer = \App\Models\CRM\Customer::findOrFail($this->assignCustomerId);

        DB::transaction(function () use ($serial, $customer) {
            // 1. Update status serial menjadi installed
            $serial->update([
                'status' => 'installed',
                'customer_id' => $customer->id,
                'warehouse_id' => null,
                'updated_by' => auth()->id(),
            ]);

            // 2. Jika serial ini berasal dari custody teknisi/reseller, update catatan custody-nya
            $custody = \App\Models\Inventory\InventoryCustody::where('item_serial_id', $serial->id)
                ->where('status', 'in_custody')
                ->latest()
                ->first();

            if ($custody) {
                $custody->update([
                    'used_quantity' => $custody->quantity,
                    'status' => 'used',
                    'customer_id' => $customer->id,
                ]);
            }

            // 3. Jika ONU ada di data OLT, pasangkan juga ke layanan customer
            $onu = \App\Models\ISP\Onu::where('serial_number', $serial->serial_number)->first();
            if ($onu) {
                $service = \App\Models\Customer\CustomerService::where('customer_id', $customer->id)->latest('id')->first();
                if ($service) {
                    $service->update(['onu_id' => $onu->id]);
                }
            }
        });

        session()->flash('serial_success', "Unit SN {$serial->serial_number} berhasil ditandai TERPASANG pada pelanggan {$customer->name}!");
        $this->closeAssignCustomerModal();
    }

    private function resetForm()
    {
        $this->itemId = null;
        $this->sku = '';
        $this->name = '';
        $this->category = 'ONU/ONT';
        $this->unit = 'unit';
        $this->brand = '';
        $this->model = '';
        $this->description = '';
        $this->min_stock = 5;
        $this->is_serialized = true;
        $this->is_reusable = true;
        $this->cost_price = null;
        $this->selling_price = null;
        $this->is_active = true;
        $this->branch_id = null;
    }

    public function render()
    {
        $q = trim($this->search);

        $query = Item::with(['branch', 'stocks.warehouse'])
            ->when(!empty($q), function ($b) use ($q) {
                $b->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%")
                        ->orWhere('brand', 'like', "%{$q}%")
                        ->orWhere('model', 'like', "%{$q}%");
                });
            })
            ->when(!empty($this->categoryFilter), function ($b) {
                $b->where('category', $this->categoryFilter);
            })
            ->when(!empty($this->statusFilter), function ($b) {
                if ($this->statusFilter === 'active') {
                    $b->where('is_active', true);
                } elseif ($this->statusFilter === 'serialized') {
                    $b->where('is_serialized', true);
                } elseif ($this->statusFilter === 'low_stock') {
                    $b->whereHas('stocks', function ($sq) {
                        $sq->havingRaw('SUM(quantity) <= min_stock');
                    });
                }
            })
            ->orderBy('category')
            ->orderBy('name');

        $perPageInt = match ((string) $this->perPage) {
            '50' => 50,
            '100' => 100,
            'all' => max(1, (clone $query)->count()),
            default => 20,
        };

        $items = $query->paginate($perPageInt);

        // Data Serials jika modal serial aktif
        $serials = collect();
        $selectedItem = null;
        $totalStockForSelectedItem = 0;
        $totalSerialsInWarehouse = 0;

        if ($this->showSerialsModal && $this->selectedItemIdForSerials) {
            $selectedItem = Item::with('stocks.warehouse')->find($this->selectedItemIdForSerials);
            if ($selectedItem) {
                $totalStockForSelectedItem = (float) $selectedItem->stocks->sum('quantity');
                $totalSerialsInWarehouse = ItemSerial::where('item_id', $this->selectedItemIdForSerials)
                    ->where('status', 'in_warehouse')
                    ->count();
            }

            $sq = trim($this->serialSearch);
            $serials = ItemSerial::with(['warehouse', 'custodian', 'customer', 'onu.olt', 'onu.customerService.customer'])
                ->where('item_id', $this->selectedItemIdForSerials)
                ->when(!empty($sq), function ($b) use ($sq) {
                    $b->where(function ($sub) use ($sq) {
                        $sub->where('serial_number', 'like', "%{$sq}%")
                            ->orWhere('mac_address', 'like', "%{$sq}%");
                    });
                })
                ->latest('id')
                ->paginate(10, ['*'], 'serialPage');
        }

        $customersList = collect();
        if ($this->showAssignCustomerModal) {
            $cs = trim($this->assignCustomerSearch);
            $customersList = \App\Models\CRM\Customer::withoutGuests()
                ->select('id', 'name', 'phone')
                ->when(!empty($cs), function ($q) use ($cs) {
                    $q->where(function ($sub) use ($cs) {
                        $sub->where('name', 'like', "%{$cs}%")
                            ->orWhere('phone', 'like', "%{$cs}%");
                    });
                })
                ->orderBy('name')
                ->limit(50)
                ->get();

            if ($this->assignCustomerId && !$customersList->contains('id', $this->assignCustomerId)) {
                $selectedCust = \App\Models\CRM\Customer::withoutGuests()
                    ->select('id', 'name', 'phone')
                    ->find($this->assignCustomerId);
                if ($selectedCust) {
                    $customersList->prepend($selectedCust);
                }
            }
        }

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        $vendorBrands = \App\Models\ISP\Vendor::where('status', 'active')->orderBy('name')->pluck('name')->toArray();
        $existingBrands = Item::distinct()->whereNotNull('brand')->where('brand', '!=', '')->pluck('brand')->toArray();
        $brandList = collect(array_merge($vendorBrands, $existingBrands))
            ->map(fn($b) => trim($b))
            ->filter()
            ->unique(fn($b) => strtolower($b))
            ->sort()
            ->values()
            ->all();

        $categories = [
            'ONU/ONT',
            'Router',
            'Kabel Dropcore',
            'Fast Connector',
            'Roset',
            'Patchcord',
            'Splitter',
            'Clamp',
            'Tiang',
            'Alat Kerja',
            'Material Lain',
        ];

        return view('livewire.inventory.item.index', [
            'items' => $items,
            'serials' => $serials,
            'branches' => $branches,
            'warehouses' => $warehouses,
            'selectedItem' => $selectedItem,
            'totalStockForSelectedItem' => $totalStockForSelectedItem,
            'totalSerialsInWarehouse' => $totalSerialsInWarehouse,
            'brandList' => $brandList,
            'categories' => $categories,
            'itemId' => $this->itemId,
            'showFormModal' => $this->showFormModal,
            'showSerialsModal' => $this->showSerialsModal,
            'selectedItemNameForSerials' => $this->selectedItemNameForSerials,
            'serialSearch' => $this->serialSearch,
            'customersList' => $customersList,
            'showAssignCustomerModal' => $this->showAssignCustomerModal,
            'assigningSerialNumber' => $this->assigningSerialNumber,
            'assignCustomerId' => $this->assignCustomerId,
            'assignCustomerSearch' => $this->assignCustomerSearch,
        ])->layout('layouts.app', ['title' => 'Master Barang - dsBilling']);
    }
}
