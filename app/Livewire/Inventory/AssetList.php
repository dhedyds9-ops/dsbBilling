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
    }

    public function getAssets()
    {
        $query = Asset::query();
        
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
                'category_id' => $this->category_id,
                'warehouse_id' => $this->warehouse_id,
                'vendor_id' => $this->vendor_id,
                'serial_number' => $this->serial_number,
                'status' => $this->status,
                'purchase_date' => $this->purchase_date,
                'warranty_end_date' => $this->warranty_end_date,
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
