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
    ];

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 15;
    
    // CRUD Properties
    public bool $isModalOpen = false;
    public $assetId = null;
    public $code;
    public $name;
    public $category_id;
    public $warehouse_id;
    public $vendor_id;
    public $serial_number;
    public $status = 'available';
    public $purchase_date;
    public $warranty_end_date;

    protected $rules = [
        'code' => 'required|string|max:255',
        'name' => 'required|string|max:255',
        'category_id' => 'nullable|exists:asset_categories,id',
        'warehouse_id' => 'nullable|exists:warehouses,id',
        'vendor_id' => 'nullable|exists:vendors,id',
        'serial_number' => 'nullable|string|max:255',
        'status' => 'required|in:in_use,available,maintenance,retired',
        'purchase_date' => 'nullable|date',
        'warranty_end_date' => 'nullable|date',
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
        ];
        $this->resetPage();
    }

    public function getAssets()
    {
        $query = Asset::with('vendor');
        
        if (!empty($this->filters['search'])) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->filters['search'] . '%')
                  ->orWhere('code', 'like', '%' . $this->filters['search'] . '%')
                  ->orWhere('serial_number', 'like', '%' . $this->filters['search'] . '%');
            });
        }
        
        if (!empty($this->filters['status']) && $this->filters['status'] !== 'all') {
            $query->where('status', $this->filters['status']);
        }
        
        return $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);
    }

    public function getAssetStats(): array
    {
        return [
            'total' => Asset::count(),
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
        $this->reset(['assetId', 'code', 'name', 'category_id', 'warehouse_id', 'vendor_id', 'serial_number', 'purchase_date', 'warranty_end_date']);
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
        $this->category_id = $asset->category_id;
        $this->warehouse_id = $asset->warehouse_id;
        $this->vendor_id = $asset->vendor_id;
        $this->serial_number = $asset->serial_number;
        $this->status = $asset->status ?? 'available';
        $this->purchase_date = $asset->purchase_date ? \Carbon\Carbon::parse($asset->purchase_date)->format('Y-m-d') : null;
        $this->warranty_end_date = $asset->warranty_end_date ? \Carbon\Carbon::parse($asset->warranty_end_date)->format('Y-m-d') : null;
        
        $this->resetValidation();
        $this->isModalOpen = true;
    }

    public function save()
    {
        // Ignore unique code if updating the same asset
        $rules = $this->rules;
        $rules['code'] = 'required|string|max:255|unique:assets,code,' . $this->assetId;
        $this->validate($rules);

        Asset::updateOrCreate(
            ['id' => $this->assetId],
            [
                'code' => $this->code,
                'name' => $this->name,
                'category_id' => $this->category_id ?: null,
                'warehouse_id' => $this->warehouse_id ?: null,
                'vendor_id' => $this->vendor_id ?: null,
                'serial_number' => $this->serial_number ?: null,
                'status' => $this->status,
                'purchase_date' => $this->purchase_date ?: null,
                'warranty_end_date' => $this->warranty_end_date ?: null,
            ]
        );

        $this->isModalOpen = false;
        session()->flash('success', 'Data Aset berhasil disimpan!');
    }

    public function delete($id)
    {
        Asset::findOrFail($id)->delete();
        session()->flash('success', 'Aset berhasil dihapus.');
    }

    public function render()
    {
        // We'll pass categories, warehouses, and vendors for the select dropdowns
        $categories = class_exists(AssetCategory::class) ? AssetCategory::all() : collect();
        $warehouses = class_exists(Warehouse::class) ? Warehouse::all() : collect();
        $vendors = class_exists(Vendor::class) ? Vendor::all() : collect();
        
        return view('livewire.inventory.asset-list', [
            'assets' => $this->getAssets(),
            'stats' => $this->getAssetStats(),
            'categories' => $categories,
            'warehouses' => $warehouses,
            'vendors' => $vendors,
        ]);
    }
}
