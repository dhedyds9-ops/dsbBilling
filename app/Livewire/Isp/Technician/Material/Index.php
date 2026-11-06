<?php

namespace App\Livewire\Isp\Technician\Material;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

#[Layout('layouts.technician-app')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $activeTab = 'stok'; // stok, pemakaian, minta

    public string $search = '';

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
        $this->activeTab = in_array($tab, ['stok', 'pemakaian', 'minta'], true) ? $tab : 'stok';
        $this->resetPage();
    }

    private function ensureDefaultMaterialsExist(): void
    {
        if (!Schema::hasTable('support_maintenance_materials')) {
            return;
        }

        try {
            $count = DB::table('support_maintenance_materials')->count();
            if ($count === 0) {
                $now = now();
                $defaults = [
                    ['sku' => 'MAT-DC-1C', 'name' => 'Kabel Dropcore Optik 1 Core (1000m)', 'category' => 'Kabel', 'stock' => 1000, 'min_stock' => 150, 'unit' => 'meter', 'location' => 'Bagasi Teknisi'],
                    ['sku' => 'MAT-FC-SC', 'name' => 'Fast Connector SC/UPC', 'category' => 'Konektor', 'stock' => 100, 'min_stock' => 20, 'unit' => 'pcs', 'location' => 'Bagasi Teknisi'],
                    ['sku' => 'MAT-ROSET-1C', 'name' => 'Roset Optik 1 Core / OTB Mini', 'category' => 'Pasif', 'stock' => 50, 'min_stock' => 10, 'unit' => 'pcs', 'location' => 'Bagasi Teknisi'],
                    ['sku' => 'MAT-PC-SC-1.5M', 'name' => 'Patchcord Fiber SC/UPC - SC/UPC 1.5m', 'category' => 'Kabel', 'stock' => 30, 'min_stock' => 5, 'unit' => 'pcs', 'location' => 'Bagasi Teknisi'],
                    ['sku' => 'MAT-CLAMP-S', 'name' => 'S-Clamp / Klem Tarik Kabel Dropcore', 'category' => 'Aksesoris', 'stock' => 150, 'min_stock' => 30, 'unit' => 'pcs', 'location' => 'Bagasi Teknisi'],
                    ['sku' => 'MAT-ADAPT-SC', 'name' => 'Adapter Fiber SC/UPC Simplex', 'category' => 'Konektor', 'stock' => 50, 'min_stock' => 10, 'unit' => 'pcs', 'location' => 'Bagasi Teknisi'],
                    ['sku' => 'DEV-ONT-ZTE-F609', 'name' => 'ONT GPON ZTE F609 (Wi-Fi 2.4GHz)', 'category' => 'Perangkat Aktif', 'stock' => 5, 'min_stock' => 2, 'unit' => 'unit', 'location' => 'Bagasi Teknisi'],
                    ['sku' => 'DEV-ONT-HW-8245H', 'name' => 'ONT GPON Huawei HG8245H', 'category' => 'Perangkat Aktif', 'stock' => 5, 'min_stock' => 2, 'unit' => 'unit', 'location' => 'Bagasi Teknisi'],
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

            session()->flash('success', "Permintaan tambahan barang \"{$this->reqMaterialName}\" ({$this->reqQuantity} {$this->reqUnit}) telah dikirimkan ke Admin Gudang!");
            $this->showRequestModal = false;
            $this->activeTab = 'pemakaian';
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal mengirimkan permintaan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $user = Auth::user();
        $q = trim($this->search);

        // 1. Data Stok Material
        $materials = collect();
        if (Schema::hasTable('support_maintenance_materials')) {
            $builder = DB::table('support_maintenance_materials')
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
        }

        // 2. Data Riwayat Pemakaian Saya
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
            'myUsages' => $myUsages,
            'activeTickets' => $activeTickets,
        ]);
    }
}
