<?php

namespace App\Livewire\Isp\Technician\Material;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\InventoryStockMutation;
use App\Models\Inventory\InventoryCustody;
use App\Models\Inventory\InventoryUsage;
use App\Models\Inventory\InventoryUsageItem;
use App\Models\Inventory\InventoryReturn;
use App\Models\Inventory\InventoryReturnItem;
use App\Models\Inventory\InventoryRequisition;
use App\Models\Inventory\InventoryRequisitionItem;

#[Layout('layouts.technician-app')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $activeTab = 'stok'; // stok, gudang, pemakaian

    public string $search = '';

    // Modal Ambil Barang dari Gudang
    public bool $showTakeModal = false;
    public ?int $takeWarehouseMaterialId = null;
    public int $takeQuantity = 1;
    public string $takeNotes = '';

    // Modal Catat Pemakaian Cepat
    public bool $showUsageModal = false;
    public ?int $selectedMaterialId = null;
    public string $usageMaterialName = '';
    public int $usageQuantity = 1;
    public string $usageUnit = 'pcs';
    public ?int $usageTicketId = null;
    public string $usageNotes = '';

    // Modal Minta Barang Tambahan
    public bool $showRequestModal = false;
    public string $reqMaterialName = '';
    public int $reqQuantity = 1;
    public string $reqUnit = 'pcs';
    public string $reqReason = '';

    // Modal Retur & Lapor Rusak / Hilang
    public bool $showReturnModal = false;
    public string $returnAction = 'retur'; // 'retur', 'rusak', 'hilang'
    public ?int $returnMaterialId = null;
    public string $returnMaterialName = '';
    public int $returnQuantity = 1;
    public string $returnUnit = 'pcs';
    public string $returnSerial = '';
    public string $returnReason = '';

    public function mount()
    {
        $this->ensureDefaultMaterialsExist();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function switchTab(string $tab)
    {
        $this->activeTab = in_array($tab, ['stok', 'gudang', 'pemakaian'], true) ? $tab : 'stok';
        $this->resetPage();
    }

    private function ensureDefaultMaterialsExist(): void
    {
        if (!Schema::hasTable('support_maintenance_materials')) {
            return;
        }

        try {
            // Pastikan jika ada data default, lokasinya adalah Gudang Utama (bukan langsung di tangan teknisi)
            if (Schema::hasColumn('support_maintenance_materials', 'technician_id')) {
                DB::table('support_maintenance_materials')
                    ->whereNull('technician_id')
                    ->where('location', 'like', '%Bagasi%')
                    ->update(['location' => 'Gudang Utama']);
            }

            $count = DB::table('support_maintenance_materials')->whereNull('technician_id')->count();
            if ($count === 0) {
                $now = now();
                $defaults = [
                    ['sku' => 'MAT-DC-1C', 'name' => 'Kabel Dropcore Optik 1 Core (1000m)', 'category' => 'Kabel', 'stock' => 1000, 'min_stock' => 150, 'unit' => 'meter', 'location' => 'Gudang Utama', 'technician_id' => null],
                    ['sku' => 'MAT-FC-SC', 'name' => 'Fast Connector SC/UPC', 'category' => 'Konektor', 'stock' => 100, 'min_stock' => 20, 'unit' => 'pcs', 'location' => 'Gudang Utama', 'technician_id' => null],
                    ['sku' => 'MAT-ROSET-1C', 'name' => 'Roset Optik 1 Core / OTB Mini', 'category' => 'Pasif', 'stock' => 50, 'min_stock' => 10, 'unit' => 'pcs', 'location' => 'Gudang Utama', 'technician_id' => null],
                    ['sku' => 'MAT-PC-SC-1.5M', 'name' => 'Patchcord Fiber SC/UPC - SC/UPC 1.5m', 'category' => 'Kabel', 'stock' => 30, 'min_stock' => 5, 'unit' => 'pcs', 'location' => 'Gudang Utama', 'technician_id' => null],
                    ['sku' => 'MAT-CLAMP-S', 'name' => 'S-Clamp / Klem Tarik Kabel Dropcore', 'category' => 'Aksesoris', 'stock' => 150, 'min_stock' => 30, 'unit' => 'pcs', 'location' => 'Gudang Utama', 'technician_id' => null],
                    ['sku' => 'MAT-ADAPT-SC', 'name' => 'Adapter Fiber SC/UPC Simplex', 'category' => 'Konektor', 'stock' => 50, 'min_stock' => 10, 'unit' => 'pcs', 'location' => 'Gudang Utama', 'technician_id' => null],
                    ['sku' => 'DEV-ONT-ZTE-F609', 'name' => 'ONT GPON ZTE F609 (Wi-Fi 2.4GHz)', 'category' => 'Perangkat Aktif', 'stock' => 5, 'min_stock' => 2, 'unit' => 'unit', 'location' => 'Gudang Utama', 'technician_id' => null],
                    ['sku' => 'DEV-ONT-HW-8245H', 'name' => 'ONT GPON Huawei HG8245H', 'category' => 'Perangkat Aktif', 'stock' => 5, 'min_stock' => 2, 'unit' => 'unit', 'location' => 'Gudang Utama', 'technician_id' => null],
                ];

                foreach ($defaults as $item) {
                    DB::table('support_maintenance_materials')->insert(array_merge($item, [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]));
                }
            }
        } catch (\Throwable) {}
    }

    public function openTakeModal(?int $warehouseMaterialId = null)
    {
        $this->takeWarehouseMaterialId = $warehouseMaterialId;
        $this->takeQuantity = 1;
        $this->takeNotes = '';
        $this->showTakeModal = true;
    }

    public function submitTake()
    {
        $this->validate([
            'takeWarehouseMaterialId' => 'required|integer',
            'takeQuantity' => 'required|integer|min:1',
            'takeNotes' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        try {
            DB::transaction(function () use ($user) {
                // 1. Kunci baris material gudang kantor
                $whMat = DB::table('support_maintenance_materials')
                    ->where('id', $this->takeWarehouseMaterialId)
                    ->whereNull('technician_id')
                    ->lockForUpdate()
                    ->first();

                if (!$whMat) {
                    throw new \RuntimeException('Material di gudang kantor tidak ditemukan.');
                }

                if ((int) $whMat->stock < $this->takeQuantity) {
                    throw new \RuntimeException("Stok gudang tidak mencukupi (tersedia: {$whMat->stock} {$whMat->unit}).");
                }

                // 2. Kurangi stok di gudang kantor
                DB::table('support_maintenance_materials')
                    ->where('id', $whMat->id)
                    ->update([
                        'stock' => (int) $whMat->stock - $this->takeQuantity,
                        'updated_at' => now(),
                    ]);

                // 3. Tambahkan ke stok bagasi teknisi (cari berdasarkan SKU atau buat baru)
                $myMat = DB::table('support_maintenance_materials')
                    ->where('technician_id', $user->id)
                    ->where(function ($q) use ($whMat) {
                        if ($whMat->sku) {
                            $q->where('sku', $whMat->sku);
                        } else {
                            $q->where('name', $whMat->name);
                        }
                    })
                    ->first();

                if ($myMat) {
                    DB::table('support_maintenance_materials')
                        ->where('id', $myMat->id)
                        ->update([
                            'stock' => (int) $myMat->stock + $this->takeQuantity,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('support_maintenance_materials')->insert([
                        'sku' => $whMat->sku,
                        'name' => $whMat->name,
                        'category' => $whMat->category,
                        'stock' => $this->takeQuantity,
                        'min_stock' => $whMat->min_stock ?? 5,
                        'unit' => $whMat->unit ?: 'pcs',
                        'location' => 'Bagasi Teknisi (' . $user->name . ')',
                        'technician_id' => $user->id,
                        'notes' => 'Diambil dari Gudang Kantor',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // 4. Catat mutasi / transfer ke usage log
                DB::table('support_maintenance_usages')->insert([
                    'material_id' => $whMat->id,
                    'material_name' => '[AMBIL GUDANG] ' . $whMat->name,
                    'quantity' => $this->takeQuantity,
                    'unit' => $whMat->unit ?: 'pcs',
                    'technician_id' => $user->id,
                    'technician_name' => $user->name,
                    'used_date' => now()->toDateString(),
                    'notes' => 'Pengambilan material dari Gudang Kantor ke Bagasi Teknisi' . ($this->takeNotes ? ' (' . $this->takeNotes . ')' : ''),
                    'created_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 5. Sinkronisasi dengan Enterprise Inventory & Custody (SSOT)
                try {
                    $item = Item::where('sku', $whMat->sku)->orWhere('name', $whMat->name)->first();
                    if ($item) {
                        $wh = Warehouse::where('is_active', true)->first();
                        $whId = $wh ? $wh->id : 1;

                        // Kurangi stok di inventory_stocks
                        $stock = InventoryStock::where('warehouse_id', $whId)->where('item_id', $item->id)->first();
                        $balBefore = $stock ? (float)$stock->quantity : 0;
                        $balAfter = max(0, $balBefore - $this->takeQuantity);
                        if ($stock) {
                            $stock->quantity = $balAfter;
                            $stock->save();
                        }

                        // Catat mutasi persediaan resmi
                        InventoryStockMutation::create([
                            'company_id' => $user->company_id ?? 1,
                            'branch_id' => $user->branch_id,
                            'warehouse_id' => $whId,
                            'item_id' => $item->id,
                            'type' => 'outbound_dispatch',
                            'qty_in' => 0,
                            'qty_out' => $this->takeQuantity,
                            'balance_before' => $balBefore,
                            'balance_after' => $balAfter,
                            'reference_type' => 'technician_take',
                            'notes' => 'Diambil langsung oleh teknisi ' . $user->name . ' via Portal Teknisi' . ($this->takeNotes ? ' (' . $this->takeNotes . ')' : ''),
                            'created_by' => $user->id,
                        ]);

                        // Catat / Update Custody Teknisi
                        $custody = InventoryCustody::where('holder_type', 'technician')
                            ->where('holder_id', $user->id)
                            ->where('item_id', $item->id)
                            ->where('status', 'in_custody')
                            ->first();

                        if ($custody) {
                            $custody->quantity = (float)$custody->quantity + $this->takeQuantity;
                            $custody->save();
                        } else {
                            InventoryCustody::create([
                                'branch_id' => $user->branch_id,
                                'item_id' => $item->id,
                                'holder_type' => 'technician',
                                'holder_id' => $user->id,
                                'quantity' => $this->takeQuantity,
                                'used_quantity' => 0,
                                'returned_quantity' => 0,
                                'status' => 'in_custody',
                                'notes' => 'Pengambilan mandiri via Portal Teknisi',
                            ]);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('Error syncing take to Enterprise Inventory: ' . $e->getMessage());
                }
            });

            session()->flash('success', "Berhasil mengambil {$this->takeQuantity} barang dari Gudang Kantor ke Bagasi Anda!");
            $this->showTakeModal = false;
            $this->activeTab = 'stok';
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function openRecordUsageModal(int $materialId)
    {
        $mat = DB::table('support_maintenance_materials')->where('id', $materialId)->first();
        if (!$mat) return;

        $this->selectedMaterialId = $mat->id;
        $this->usageMaterialName = $mat->name;
        $this->usageUnit = $mat->unit ?: 'pcs';
        $this->usageQuantity = 1;
        $this->usageNotes = '';
        $this->usageTicketId = null;
        $this->showUsageModal = true;
    }

    public function saveUsage()
    {
        $this->validate([
            'selectedMaterialId' => 'required|integer',
            'usageQuantity' => 'required|integer|min:1',
            'usageNotes' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        try {
            DB::transaction(function () use ($user) {
                $mat = DB::table('support_maintenance_materials')
                    ->where('id', $this->selectedMaterialId)
                    ->lockForUpdate()
                    ->first();

                if (!$mat) {
                    throw new \RuntimeException('Data material tidak ditemukan.');
                }

                if ((int) $mat->stock < $this->usageQuantity) {
                    throw new \RuntimeException("Stok tidak mencukupi (sisa: {$mat->stock} {$mat->unit}).");
                }

                // Potong stok material
                DB::table('support_maintenance_materials')
                    ->where('id', $mat->id)
                    ->update([
                        'stock' => (int) $mat->stock - $this->usageQuantity,
                        'updated_at' => now(),
                    ]);

                // Catat ke usage history
                DB::table('support_maintenance_usages')->insert([
                    'material_id' => $mat->id,
                    'material_name' => $mat->name,
                    'quantity' => $this->usageQuantity,
                    'unit' => $mat->unit ?: 'pcs',
                    'technician_id' => $user->id,
                    'technician_name' => $user->name,
                    'ticket_id' => $this->usageTicketId ?: null,
                    'used_date' => now()->toDateString(),
                    'notes' => trim($this->usageNotes) ?: 'Pemakaian lapangan oleh teknisi',
                    'created_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Sinkronisasi dengan Enterprise Inventory Usages & Custody
                try {
                    $item = Item::where('sku', $mat->sku)->orWhere('name', $mat->name)->first();
                    if ($item) {
                        $custody = InventoryCustody::where('holder_type', 'technician')
                            ->where('holder_id', $user->id)
                            ->where('item_id', $item->id)
                            ->where('status', 'in_custody')
                            ->first();

                        if ($custody) {
                            $custody->used_quantity = (float)$custody->used_quantity + $this->usageQuantity;
                            $custody->save();
                        }

                        $usage = InventoryUsage::create([
                            'uuid' => (string) Str::uuid(),
                            'company_id' => $user->company_id ?? 1,
                            'branch_id' => $user->branch_id,
                            'ticket_id' => $this->usageTicketId ?: null,
                            'work_order_type' => 'maintenance',
                            'used_by' => $user->id,
                            'usage_date' => now()->toDateString(),
                            'optical_power_dbm' => null,
                            'notes' => trim($this->usageNotes) ?: 'Pemakaian lapangan oleh teknisi via Portal Teknisi',
                        ]);

                        InventoryUsageItem::create([
                            'usage_id' => $usage->id,
                            'custody_id' => $custody?->id,
                            'item_id' => $item->id,
                            'quantity' => $this->usageQuantity,
                            'unit' => $mat->unit ?: 'pcs',
                            'notes' => $this->usageNotes,
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Error syncing usage to Enterprise Inventory: ' . $e->getMessage());
                }
            });

            session()->flash('success', "Pemakaian {$this->usageQuantity} {$this->usageUnit} \"{$this->usageMaterialName}\" berhasil dicatat & stok terpotong.");
            $this->showUsageModal = false;
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function openRequestModal()
    {
        $this->reqMaterialName = '';
        $this->reqQuantity = 1;
        $this->reqUnit = 'pcs';
        $this->reqReason = '';
        $this->showRequestModal = true;
    }

    public function submitMaterialRequest()
    {
        $this->validate([
            'reqMaterialName' => 'required|string|max:150',
            'reqQuantity' => 'required|integer|min:1',
            'reqUnit' => 'required|string|max:20',
            'reqReason' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        // Catat sebagai log permintaan material di support_maintenance_usages dengan status/notes khusus
        try {
            DB::table('support_maintenance_usages')->insert([
                'material_id' => null,
                'material_name' => '[PERMINTAAN TAMBAHAN] ' . $this->reqMaterialName,
                'quantity' => $this->reqQuantity,
                'unit' => $this->reqUnit,
                'technician_id' => $user->id,
                'technician_name' => $user->name,
                'used_date' => now()->toDateString(),
                'notes' => 'Alasan: ' . ($this->reqReason ?: 'Kebutuhan pekerjaan lapangan (Menunggu Approval Gudang/Admin)'),
                'created_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Hubungkan otomatis ke Enterprise Inventory Requisition
            try {
                $wh = Warehouse::where('is_active', true)->first();
                $item = Item::where('name', 'like', '%' . trim($this->reqMaterialName) . '%')->first() ?? Item::first();

                $req = InventoryRequisition::create([
                    'uuid' => (string) Str::uuid(),
                    'company_id' => $user->company_id ?? 1,
                    'branch_id' => $user->branch_id,
                    'warehouse_id' => $wh ? $wh->id : 1,
                    'requester_id' => $user->id,
                    'requester_type' => 'technician',
                    'distribution_method' => 'direct_technician',
                    'designated_technician_id' => $user->id,
                    'work_order_type' => 'stock_topup',
                    'status' => 'pending',
                    'notes' => 'Permintaan mandiri teknisi: ' . ($this->reqReason ?: 'Kebutuhan pekerjaan operasional lapangan'),
                ]);

                if ($item) {
                    InventoryRequisitionItem::create([
                        'requisition_id' => $req->id,
                        'item_id' => $item->id,
                        'requested_quantity' => $this->reqQuantity,
                        'approved_quantity' => 0,
                        'notes' => $this->reqMaterialName,
                    ]);
                }
            } catch (\Throwable $re) {
                Log::warning('Error creating enterprise requisition: ' . $re->getMessage());
            }

            session()->flash('success', "Permintaan tambahan barang \"{$this->reqMaterialName}\" ({$this->reqQuantity} {$this->reqUnit}) telah dikirimkan ke Admin Gudang!");
            $this->showRequestModal = false;
            $this->activeTab = 'pemakaian';
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal mengirimkan permintaan: ' . $e->getMessage());
        }
    }

    public function openReturnModal(int $materialId, string $action = 'retur')
    {
        $mat = DB::table('support_maintenance_materials')->where('id', $materialId)->first();
        if (!$mat) return;

        $this->returnMaterialId = $mat->id;
        $this->returnMaterialName = $mat->name;
        $this->returnUnit = $mat->unit ?: 'pcs';
        $this->returnQuantity = 1;
        $this->returnSerial = '';
        $this->returnReason = '';
        $this->returnAction = in_array($action, ['retur', 'rusak', 'hilang']) ? $action : 'retur';
        $this->showReturnModal = true;
    }

    public function submitReturn()
    {
        $this->validate([
            'returnMaterialId' => 'required|integer',
            'returnQuantity' => 'required|integer|min:1',
            'returnAction' => 'required|in:retur,rusak,hilang',
            'returnReason' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        try {
            DB::transaction(function () use ($user) {
                $mat = DB::table('support_maintenance_materials')
                    ->where('id', $this->returnMaterialId)
                    ->lockForUpdate()
                    ->first();

                if (!$mat) {
                    throw new \RuntimeException('Data material tidak ditemukan.');
                }

                if ((int) $mat->stock < $this->returnQuantity) {
                    throw new \RuntimeException("Jumlah melebihi stok yang tersedia (sisa: {$mat->stock} {$mat->unit}).");
                }

                // Kurangi stok teknisi
                DB::table('support_maintenance_materials')
                    ->where('id', $mat->id)
                    ->update([
                        'stock' => max(0, (int) $mat->stock - $this->returnQuantity),
                        'updated_at' => now(),
                    ]);

                // Jika aksi retur ke gudang kantor, kembalikan stok ke Gudang Utama
                if ($this->returnAction === 'retur') {
                    $whMat = DB::table('support_maintenance_materials')
                        ->whereNull('technician_id')
                        ->where(function ($q) use ($mat) {
                            if ($mat->sku) {
                                $q->where('sku', $mat->sku);
                            } else {
                                $q->where('name', $mat->name);
                            }
                        })
                        ->first();

                    if ($whMat) {
                        DB::table('support_maintenance_materials')
                            ->where('id', $whMat->id)
                            ->update([
                                'stock' => (int) $whMat->stock + $this->returnQuantity,
                                'updated_at' => now(),
                            ]);
                    }
                }

                $tag = match($this->returnAction) {
                    'retur' => '[RETUR KE GUDANG]',
                    'rusak' => '[BARANG RUSAK/AFKIR]',
                    'hilang' => '[BARANG HILANG]',
                };

                $notes = "Status: {$tag}. " . ($this->returnSerial ? "SN: {$this->returnSerial}. " : "") . ($this->returnReason ?: 'Laporan mandiri teknisi');

                DB::table('support_maintenance_usages')->insert([
                    'material_id' => $mat->id,
                    'material_name' => "{$tag} {$mat->name}",
                    'quantity' => $this->returnQuantity,
                    'unit' => $mat->unit ?: 'pcs',
                    'technician_id' => $user->id,
                    'technician_name' => $user->name,
                    'used_date' => now()->toDateString(),
                    'notes' => $notes,
                    'created_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Sinkronisasi dengan Enterprise Inventory Custody & Returns (QC Pending)
                try {
                    $item = Item::where('sku', $mat->sku)->orWhere('name', $mat->name)->first();
                    if ($item) {
                        $custody = InventoryCustody::where('holder_type', 'technician')
                            ->where('holder_id', $user->id)
                            ->where('item_id', $item->id)
                            ->where('status', 'in_custody')
                            ->first();

                        if ($custody) {
                            if ($this->returnAction === 'retur') {
                                $custody->returned_quantity = (float)$custody->returned_quantity + $this->returnQuantity;
                            } else {
                                $custody->used_quantity = (float)$custody->used_quantity + $this->returnQuantity;
                            }
                            $custody->save();
                        }

                        $wh = Warehouse::where('is_active', true)->first();
                        $whId = $wh ? $wh->id : 1;

                        $returnDoc = InventoryReturn::create([
                            'uuid' => (string) Str::uuid(),
                            'company_id' => $user->company_id ?? 1,
                            'branch_id' => $user->branch_id,
                            'warehouse_id' => $whId,
                            'returner_id' => $user->id,
                            'returner_type' => 'technician',
                            'status' => 'pending_qc',
                            'reason' => "Status: {$tag}. " . ($this->returnReason ?: 'Laporan pengembalian/kondisi material dari teknisi'),
                        ]);

                        InventoryReturnItem::create([
                            'return_id' => $returnDoc->id,
                            'custody_id' => $custody?->id,
                            'item_id' => $item->id,
                            'quantity' => $this->returnQuantity,
                            'condition' => match($this->returnAction) {
                                'retur' => 'good',
                                'rusak' => 'damaged',
                                'hilang' => 'unusable',
                            },
                            'action' => match($this->returnAction) {
                                'retur' => 'restock',
                                'rusak' => 'repair',
                                'hilang' => 'scrap',
                            },
                            'serial_number' => $this->returnSerial ?: null,
                            'qc_status' => 'pending',
                            'notes' => $this->returnReason,
                        ]);
                    }
                } catch (\Throwable $retEx) {
                    Log::warning('Error syncing return to Enterprise Inventory: ' . $retEx->getMessage());
                }
            });

            $label = match($this->returnAction) {
                'retur' => 'Pengembalian barang ke gudang',
                'rusak' => 'Laporan barang rusak',
                'hilang' => 'Laporan barang hilang',
            };

            session()->flash('success', "{$label} ({$this->returnQuantity} {$this->returnUnit} \"{$this->returnMaterialName}\") berhasil dicatat.");
            $this->showReturnModal = false;
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $user = Auth::user();
        $q = trim($this->search);

        // 1. Data Stok Bagasi Saya (hanya barang yang dipegang teknisi ini)
        $materials = collect();
        if (Schema::hasTable('support_maintenance_materials')) {
            $builder = DB::table('support_maintenance_materials')
                ->where('technician_id', $user->id)
                ->when(!empty($q), function ($b) use ($q) {
                    $b->where(function ($sub) use ($q) {
                        $sub->where('name', 'like', "%{$q}%")
                            ->orWhere('sku', 'like', "%{$q}%")
                            ->orWhere('category', 'like', "%{$q}%");
                    });
                })
                ->orderBy('category')
                ->orderBy('name');

            $materials = $builder->get();

            // Sinkronkan juga item dari Enterprise Custody jika ada dispatch resmi dari gudang
            try {
                $custodies = InventoryCustody::with(['item', 'itemSerial'])
                    ->where('holder_id', $user->id)
                    ->where('holder_type', 'technician')
                    ->activeCustody()
                    ->get();

                $added = false;
                foreach ($custodies as $c) {
                    if ($c->item && !$materials->firstWhere('sku', $c->item->sku)) {
                        DB::table('support_maintenance_materials')->insert([
                            'sku' => $c->item->sku,
                            'name' => $c->item->name,
                            'category' => $c->item->category ?? 'Material',
                            'stock' => (int) $c->available_quantity,
                            'min_stock' => $c->item->min_stock ?? 5,
                            'unit' => $c->item->unit ?: 'pcs',
                            'location' => 'Bagasi Teknisi (' . $user->name . ')',
                            'technician_id' => $user->id,
                            'notes' => 'Dispatched from Central Warehouse',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $added = true;
                    }
                }
                if ($added) {
                    $materials = DB::table('support_maintenance_materials')
                        ->where('technician_id', $user->id)
                        ->orderBy('name')
                        ->get();
                }
            } catch (\Throwable) {}
        }

        // 2. Data Stok Gudang Kantor (technician_id IS NULL)
        $warehouseMaterials = collect();
        if (Schema::hasTable('support_maintenance_materials')) {
            $whBuilder = DB::table('support_maintenance_materials')
                ->whereNull('technician_id')
                ->when(!empty($q), function ($b) use ($q) {
                    $b->where(function ($sub) use ($q) {
                        $sub->where('name', 'like', "%{$q}%")
                            ->orWhere('sku', 'like', "%{$q}%")
                            ->orWhere('category', 'like', "%{$q}%");
                    });
                })
                ->orderBy('category')
                ->orderBy('name');

            $warehouseMaterials = $whBuilder->get();
        }

        // 3. Data Riwayat Pemakaian & Mutasi Saya
        $myUsages = collect();
        if (Schema::hasTable('support_maintenance_usages')) {
            $usageBuilder = DB::table('support_maintenance_usages')
                ->where(function ($b) use ($user) {
                    $b->where('technician_id', $user->id)
                      ->orWhere('created_by', $user->id);
                })
                ->when(!empty($q), function ($b) use ($q) {
                    $b->where(function ($sub) use ($q) {
                        $sub->where('material_name', 'like', "%{$q}%")
                            ->orWhere('notes', 'like', "%{$q}%");
                    });
                })
                ->orderByDesc('id')
                ->paginate(15);

            $myUsages = $usageBuilder;
        }

        // Tiket Aktif Teknisi untuk Dropdown Pemakaian
        $activeTickets = \App\Models\Support\Ticket::withoutGlobalScopes()
            ->where(function ($sq) use ($user) {
                $sq->where('assigned_to', $user->id)
                   ->orWhereNull('assigned_to');
            })
            ->whereIn('status', ['open', 'in_progress'])
            ->latest('id')
            ->take(20)
            ->get();

        return view('livewire.isp.technician.material.index', [
            'materials' => $materials,
            'warehouseMaterials' => $warehouseMaterials,
            'myUsages' => $myUsages,
            'activeTickets' => $activeTickets,
            'activeTab' => $this->activeTab,
            'search' => $this->search,
            'showTakeModal' => $this->showTakeModal,
            'takeWarehouseMaterialId' => $this->takeWarehouseMaterialId,
            'takeQuantity' => $this->takeQuantity,
            'takeNotes' => $this->takeNotes,
            'showUsageModal' => $this->showUsageModal,
            'selectedMaterialId' => $this->selectedMaterialId,
            'usageMaterialName' => $this->usageMaterialName,
            'usageQuantity' => $this->usageQuantity,
            'usageUnit' => $this->usageUnit,
            'usageTicketId' => $this->usageTicketId,
            'usageNotes' => $this->usageNotes,
            'showRequestModal' => $this->showRequestModal,
            'reqMaterialName' => $this->reqMaterialName,
            'reqQuantity' => $this->reqQuantity,
            'reqUnit' => $this->reqUnit,
            'reqReason' => $this->reqReason,
            'showReturnModal' => $this->showReturnModal,
            'returnAction' => $this->returnAction,
            'returnMaterialId' => $this->returnMaterialId,
            'returnMaterialName' => $this->returnMaterialName,
            'returnQuantity' => $this->returnQuantity,
            'returnUnit' => $this->returnUnit,
            'returnSerial' => $this->returnSerial,
            'returnReason' => $this->returnReason,
        ]);
    }
}
