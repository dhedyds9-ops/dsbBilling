<?php

namespace App\Livewire\Inventory\Item;

use App\Models\Inventory\Item;
use App\Models\Inventory\ItemSerial;
use App\Models\Master\Branch;
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

    // Modal Detail Serial
    public bool $showSerialsModal = false;
    public ?int $selectedItemIdForSerials = null;
    public string $selectedItemNameForSerials = '';
    public string $serialSearch = '';

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
        $item = Item::findOrFail($itemId);
        $this->selectedItemIdForSerials = $item->id;
        $this->selectedItemNameForSerials = $item->name;
        $this->serialSearch = '';
        $this->showSerialsModal = true;
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
        if ($this->showSerialsModal && $this->selectedItemIdForSerials) {
            $sq = trim($this->serialSearch);
            $serials = ItemSerial::with(['warehouse', 'custodian', 'customer'])
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
            'brandList' => $brandList,
            'categories' => $categories,
            'itemId' => $this->itemId,
            'showFormModal' => $this->showFormModal,
            'showSerialsModal' => $this->showSerialsModal,
            'selectedItemNameForSerials' => $this->selectedItemNameForSerials,
            'serialSearch' => $this->serialSearch,
        ])->layout('layouts.app', ['title' => 'Master Barang - dsBilling']);
    }
}
