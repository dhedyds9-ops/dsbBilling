<?php

namespace App\Livewire\Inventory;

use App\Livewire\AdminComponent;
use App\Models\Inventory\Asset;
use App\Models\Inventory\AssetCategory;
use App\Models\Inventory\Warehouse;
use App\Models\ISP\Vendor;

class AssetList extends AdminComponent
{
    public array $filters = [
        'search' => '',
        'category' => 'all',
        'status' => 'all',
        'warehouse' => 'all',
        'type' => 'all', // all, tool, onu, network, vehicle, other
    ];

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public string|int $perPage = 20;
    
    // CRUD Properties
    public bool $isModalOpen = false;
    public $assetId = null;
    public $code;
    public $name;
    public $type = 'tool';
    public $condition = 'good';
    public $category_id;
    public $warehouse_id;
    public $vendor_id;
    public $serial_number;
    public $status = 'available';
    public $purchase_price;
    public $purchase_date;
    public $warranty_end_date;
    public $description;
    public $assigned_to_id = null;

    // Quick Assignment / Handover to Technician Modal
    public bool $isAssignModalOpen = false;
    public ?int $assignAssetId = null;
    public ?int $assignTechnicianId = null;
    public string $assignNotes = '';

    protected $rules = [
        'code' => 'required|string|max:255',
        'name' => 'required|string|max:255',
        'type' => 'required|string|in:tool,onu,network,vehicle,other',
        'condition' => 'required|string|in:good,fair,damaged,maintenance',
        'category_id' => 'nullable|exists:asset_categories,id',
        'warehouse_id' => 'nullable|exists:warehouses,id',
        'vendor_id' => 'nullable|exists:vendors,id',
        'serial_number' => 'nullable|string|max:255',
        'status' => 'required|in:in_use,available,maintenance,retired',
        'purchase_price' => 'nullable|numeric|min:0',
        'purchase_date' => 'nullable|date',
        'warranty_end_date' => 'nullable|date',
        'description' => 'nullable|string|max:500',
        'assigned_to_id' => 'nullable|exists:users,id',
    ];

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'inventory';
        $this->activePage = 'assets';
        $this->syncInstalledOnus(false);
    }

    public function syncInstalledOnus(bool $flash = true): void
    {
        try {
            if (!$flash && \Illuminate\Support\Facades\Cache::has('assets_onu_autosync_recent')) {
                return;
            }

            $onus = \App\Models\ISP\Onu::select(['id', 'code', 'name', 'model', 'serial_number', 'mac_address', 'vendor_id', 'status', 'provision_status', 'provisioned_at', 'created_at'])->get();
            $existingAssets = Asset::select(['id', 'code', 'serial_number', 'mac_address', 'vendor_id', 'status', 'installed_at'])->get();
            $bySn = $existingAssets->whereNotNull('serial_number')->keyBy('serial_number');
            $byCode = $existingAssets->whereNotNull('code')->keyBy('code');

            $synced = 0;
            foreach ($onus as $onu) {
                $sn = $onu->serial_number ?: ($onu->code ?: 'ONU-' . $onu->id);
                $code = $onu->code ?: ('ONU-' . substr(preg_replace('/[^A-Za-z0-9]/', '', $sn), -6));
                $name = $onu->name ?: ('ONU Modem ' . ($onu->model ?: $sn));
                $status = ($onu->status === 'active' || $onu->provision_status === 'success' || !empty($onu->provisioned_at)) ? 'in_use' : 'available';

                $asset = $bySn->get($sn) ?? $byCode->get($code);

                if (!$asset) {
                    $created = Asset::create([
                        'code' => $code,
                        'name' => $name,
                        'type' => 'onu',
                        'status' => $status,
                        'condition' => 'good',
                        'serial_number' => $sn,
                        'mac_address' => $onu->mac_address,
                        'vendor_id' => $onu->vendor_id,
                        'installed_at' => $onu->provisioned_at ?: $onu->created_at,
                    ]);
                    $bySn->put($sn, $created);
                    $byCode->put($code, $created);
                    $synced++;
                } else {
                    $newMac = $onu->mac_address ?: $asset->mac_address;
                    $newVendor = $onu->vendor_id ?: $asset->vendor_id;
                    $newInstalled = $onu->provisioned_at ?: ($asset->installed_at ?: $onu->created_at);

                    if ($asset->status !== $status || $asset->mac_address !== $newMac || $asset->vendor_id != $newVendor) {
                        $asset->update([
                            'status' => $status,
                            'mac_address' => $newMac,
                            'vendor_id' => $newVendor,
                            'installed_at' => $newInstalled,
                        ]);
                    }
                }
            }

            \Illuminate\Support\Facades\Cache::put('assets_onu_autosync_recent', true, now()->addMinutes(5));

            if ($flash) {
                session()->flash('success', $synced > 0
                    ? "Berhasil mensinkronkan {$synced} perangkat ONU baru ke daftar aset."
                    : 'Seluruh perangkat ONU sudah tersinkronisasi.');
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Sync ONUs to assets error: ' . $e->getMessage());
        }
    }

    public function resetFilters(): void
    {
        $this->filters = [
            'search' => '',
            'category' => 'all',
            'status' => 'all',
            'warehouse' => 'all',
            'type' => 'all',
        ];
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function getAssets()
    {
        $query = Asset::with(['vendor', 'category', 'assignedTo', 'warehouse']);
        
        if (!empty($this->filters['search'])) {
            $search = '%' . trim((string)$this->filters['search']) . '%';
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('code', 'like', $search)
                  ->orWhere('serial_number', 'like', $search)
                  ->orWhereHas('assignedTo', fn($uq) => $uq->where('name', 'like', $search));
            });
        }
        
        if (!empty($this->filters['status']) && $this->filters['status'] !== 'all') {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['type']) && $this->filters['type'] !== 'all') {
            $query->where('type', $this->filters['type']);
        }

        if (!empty($this->filters['category']) && $this->filters['category'] !== 'all') {
            $query->where('category_id', $this->filters['category']);
        }
        
        $perPageInt = match ((string) $this->perPage) {
            '50' => 50,
            '100' => 100,
            'all' => max(1, (clone $query)->count()),
            default => 20,
        };

        return $query->orderBy($this->sortField, $this->sortDirection)->paginate($perPageInt);
    }

    public function getAssetStats(): array
    {
        return [
            'total' => Asset::count(),
            'tools' => Asset::where('type', 'tool')->count(),
            'in_use' => Asset::where('status', 'in_use')->count(),
            'available' => Asset::where('status', 'available')->count(),
            'maintenance' => Asset::where('status', 'maintenance')->count(),
            'retired' => Asset::where('status', 'retired')->count(),
        ];
    }

    public function getStatusColor(?string $status): string
    {
        return match($status) {
            'in_use' => 'success',
            'available' => 'primary',
            'maintenance' => 'warning',
            'retired' => 'neutral',
            default => 'neutral',
        };
    }
    
    public function create()
    {
        $this->reset(['assetId', 'code', 'name', 'type', 'condition', 'category_id', 'warehouse_id', 'vendor_id', 'serial_number', 'purchase_price', 'purchase_date', 'warranty_end_date', 'description', 'assigned_to_id']);
        $this->type = 'tool';
        $this->condition = 'good';
        $this->status = 'available';
        $this->resetValidation();
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $asset = Asset::findOrFail($id);
        $this->assetId = $asset->id;
        $this->code = $asset->code;
        $this->name = $asset->name;
        $this->type = $asset->type ?: 'tool';
        $this->condition = $asset->condition ?: 'good';
        $this->category_id = $asset->category_id;
        $this->warehouse_id = $asset->warehouse_id;
        $this->vendor_id = $asset->vendor_id;
        $this->serial_number = $asset->serial_number;
        $this->status = $asset->status ?? 'available';
        $this->purchase_price = $asset->purchase_price ? (float)$asset->purchase_price : null;
        $this->purchase_date = $asset->purchase_date ? \Carbon\Carbon::parse($asset->purchase_date)->format('Y-m-d') : null;
        $this->warranty_end_date = $asset->warranty_end_date ? \Carbon\Carbon::parse($asset->warranty_end_date)->format('Y-m-d') : null;
        $this->description = $asset->description;
        $this->assigned_to_id = $asset->assigned_to_id;
        
        $this->resetValidation();
        $this->isModalOpen = true;
    }

    public function save()
    {
        // Ignore unique code if updating the same asset
        $rules = $this->rules;
        $rules['code'] = 'required|string|max:255|unique:assets,code,' . $this->assetId;
        $this->validate($rules);

        $statusToSave = $this->status;
        $assignedAt = null;

        // If assigned to a technician, status becomes in_use
        if (!empty($this->assigned_to_id)) {
            $statusToSave = 'in_use';
            $assignedAt = now();
        }

        $asset = Asset::updateOrCreate(
            ['id' => $this->assetId],
            [
                'code' => trim($this->code),
                'name' => trim($this->name),
                'type' => $this->type,
                'condition' => $this->condition,
                'category_id' => $this->category_id ?: null,
                'warehouse_id' => $this->warehouse_id ?: null,
                'vendor_id' => $this->vendor_id ?: null,
                'serial_number' => trim((string)$this->serial_number) ?: null,
                'status' => $statusToSave,
                'purchase_price' => $this->purchase_price ?: null,
                'purchase_date' => $this->purchase_date ?: null,
                'warranty_end_date' => $this->warranty_end_date ?: null,
                'description' => $this->description ?: null,
                'assigned_to_id' => $this->assigned_to_id ?: null,
                'assigned_to_type' => !empty($this->assigned_to_id) ? \App\Models\User::class : null,
                'assigned_at' => $assignedAt,
            ]
        );

        // Record history assignment if assigned
        if (!empty($this->assigned_to_id)) {
            \App\Models\Inventory\AssetAssignment::firstOrCreate(
                [
                    'asset_id' => $asset->id,
                    'assigned_to_id' => $this->assigned_to_id,
                    'returned_at' => null,
                ],
                [
                    'assigned_to_type' => \App\Models\User::class,
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                    'notes' => 'Penugasan aset dari form edit',
                ]
            );
        }

        $this->isModalOpen = false;
        session()->flash('success', 'Data Aset & Alat Kerja berhasil disimpan!');
    }

    public function openAssignModal(int $id)
    {
        $asset = Asset::findOrFail($id);
        $this->assignAssetId = $asset->id;
        $this->assignTechnicianId = $asset->assigned_to_id;
        $this->assignNotes = '';
        $this->isAssignModalOpen = true;
    }

    public function submitAssign()
    {
        $this->validate([
            'assignTechnicianId' => 'required|exists:users,id',
        ], [
            'assignTechnicianId.required' => 'Teknisi / staf pemegang alat wajib dipilih.',
        ]);

        $asset = Asset::findOrFail($this->assignAssetId);
        $tech = \App\Models\User::find($this->assignTechnicianId);

        $asset->update([
            'assigned_to_id' => $tech->id,
            'assigned_to_type' => \App\Models\User::class,
            'assigned_at' => now(),
            'status' => 'in_use',
        ]);

        \App\Models\Inventory\AssetAssignment::create([
            'asset_id' => $asset->id,
            'assigned_to_id' => $tech->id,
            'assigned_to_type' => \App\Models\User::class,
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'notes' => $this->assignNotes ?: 'Serah terima alat kerja ke teknisi lapangan',
        ]);

        $this->isAssignModalOpen = false;
        $this->assignAssetId = null;
        session()->flash('success', "Alat kerja {$asset->name} (#{$asset->code}) berhasil diserahkan ke {$tech->name}.");
    }

    public function returnAsset(int $id)
    {
        $asset = Asset::findOrFail($id);
        $techName = $asset->assignedTo?->name ?? 'Teknisi';

        // Close any active assignment
        \App\Models\Inventory\AssetAssignment::where('asset_id', $asset->id)
            ->whereNull('returned_at')
            ->update([
                'returned_at' => now(),
                'return_accepted_by' => auth()->id(),
                'condition_upon_return' => $asset->condition ?? 'good',
            ]);

        $asset->update([
            'assigned_to_id' => null,
            'assigned_to_type' => null,
            'assigned_at' => null,
            'status' => 'available',
        ]);

        session()->flash('success', "Alat kerja {$asset->name} (#{$asset->code}) telah dikembalikan oleh {$techName} ke gudang.");
    }

    public function delete($id)
    {
        Asset::findOrFail($id)->delete();
        session()->flash('success', 'Aset berhasil dihapus.');
    }

    public function render()
    {
        $categories = class_exists(AssetCategory::class) ? AssetCategory::orderBy('name')->get() : collect();
        $warehouses = class_exists(Warehouse::class) ? Warehouse::orderBy('name')->get() : collect();
        $vendors = class_exists(Vendor::class) ? Vendor::orderBy('name')->get() : collect();
        
        $technicians = \App\Models\User::where('job_function', 'TECHNICIAN')
            ->orWhereHas('roles', fn($q) => $q->whereIn('name', ['technician', 'noc', 'field-technician', 'admin_gudang']))
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        return view('livewire.inventory.asset-list', [
            'assets' => $this->getAssets(),
            'stats' => $this->getAssetStats(),
            'categories' => $categories,
            'warehouses' => $warehouses,
            'vendors' => $vendors,
            'technicians' => $technicians,
        ]);
    }
}
