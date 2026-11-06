<?php

namespace App\Livewire\Inventory\Warehouse;

use App\Models\Inventory\Warehouse;
use App\Models\Master\Branch;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $typeFilter = '';

    // Modal Form
    public bool $showFormModal = false;
    public ?int $warehouseId = null;
    public string $code = '';
    public string $name = '';
    public string $type = 'main'; // main, branch, transit
    public ?int $branch_id = null;
    public ?int $manager_id = null;
    public string $address = '';
    public string $city = '';
    public string $province = '';
    public string $phone = '';
    public string $email = '';
    public bool $is_active = true;
    public bool $allows_returns = true;
    public ?int $capacity = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->code = $this->generateSuggestedCode();
        $this->showFormModal = true;
    }

    public function openEditModal(int $id)
    {
        $wh = Warehouse::findOrFail($id);
        $this->warehouseId = $wh->id;
        $this->code = $wh->code ?? '';
        $this->name = $wh->name ?? '';
        $this->type = $wh->type ?? 'main';
        $this->branch_id = $wh->branch_id;
        $this->manager_id = $wh->manager_id;
        $this->address = $wh->address ?? '';
        $this->city = $wh->city ?? '';
        $this->province = $wh->province ?? '';
        $this->phone = $wh->phone ?? '';
        $this->email = $wh->email ?? '';
        $this->is_active = (bool) $wh->is_active;
        $this->allows_returns = (bool) $wh->allows_returns;
        $this->capacity = $wh->capacity;

        $this->showFormModal = true;
    }

    private function generateSuggestedCode(): string
    {
        $count = Warehouse::count() + 1;
        return 'GUD-' . str_pad((string) $count, 3, '0', STR_PAD_LEFT);
    }

    public function saveWarehouse()
    {
        $this->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'type' => 'required|in:main,branch,transit',
            'branch_id' => 'nullable|integer',
            'manager_id' => 'nullable|integer',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
        ]);

        $data = [
            'code' => trim($this->code),
            'name' => trim($this->name),
            'type' => $this->type,
            'branch_id' => $this->branch_id,
            'manager_id' => $this->manager_id,
            'address' => trim($this->address) ?: null,
            'city' => trim($this->city) ?: null,
            'province' => trim($this->province) ?: null,
            'phone' => trim($this->phone) ?: null,
            'email' => trim($this->email) ?: null,
            'is_active' => $this->is_active,
            'allows_returns' => $this->allows_returns,
            'capacity' => $this->capacity,
        ];

        if ($this->warehouseId) {
            $wh = Warehouse::findOrFail($this->warehouseId);
            $wh->update($data);
            session()->flash('success', "Gudang \"{$wh->name}\" berhasil diperbarui.");
        } else {
            Warehouse::create($data);
            session()->flash('success', "Gudang baru \"{$this->name}\" berhasil ditambahkan.");
        }

        $this->showFormModal = false;
        $this->resetForm();
    }

    public function deleteWarehouse(int $id)
    {
        $wh = Warehouse::findOrFail($id);
        $totalStock = $wh->stocks()->sum('quantity');

        if ($totalStock > 0) {
            session()->flash('error', "Gagal menghapus! Gudang \"{$wh->name}\" masih menyimpan {$totalStock} barang fisik. Pindahkan stok terlebih dahulu.");
            return;
        }

        $wh->delete();
        session()->flash('success', "Gudang \"{$wh->name}\" berhasil dihapus.");
    }

    private function resetForm()
    {
        $this->warehouseId = null;
        $this->code = '';
        $this->name = '';
        $this->type = 'main';
        $this->branch_id = null;
        $this->manager_id = null;
        $this->address = '';
        $this->city = '';
        $this->province = '';
        $this->phone = '';
        $this->email = '';
        $this->is_active = true;
        $this->allows_returns = true;
        $this->capacity = null;
    }

    public function render()
    {
        $q = trim($this->search);

        $warehouses = Warehouse::with(['branch', 'manager'])
            ->withCount('stocks')
            ->when(!empty($q), function ($b) use ($q) {
                $b->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%")
                        ->orWhere('city', 'like', "%{$q}%");
                });
            })
            ->when(!empty($this->typeFilter), function ($b) {
                $b->where('type', $this->typeFilter);
            })
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(12);

        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $managers = User::where('is_active', true)->orderBy('name')->get();

        return view('livewire.inventory.warehouse.index', [
            'warehouses' => $warehouses,
            'branches' => $branches,
            'managers' => $managers,
        ])->layout('layouts.enterprise', ['title' => 'Data Gudang (Inventaris)'])->layout('layouts.app', ['title' => 'Data Gudang - dsBilling']);
    }
}
