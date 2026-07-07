<?php

namespace App\Livewire\Inventory;

use App\Livewire\AdminComponent;

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
    public array $selected = [];

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'inventory';
        $this->activePage = 'assets';
    }

    public function getAssets(): \Illuminate\Support\Collection
    {
        // Placeholder - dalam implementasi nyata, fetch dari repository
        return collect([
            ['id' => 1, 'name' => 'OLT Huawei MA5800', 'serial_number' => 'SN-2024-001', 'category' => 'OLT', 'status' => 'in_use', 'warehouse' => 'Warehouse A', 'location' => 'OLT Room 1', 'purchase_date' => now()->subMonths(6), 'warranty_expires' => now()->addMonths(18)],
            ['id' => 2, 'name' => 'Fiber Patchcord 10M', 'serial_number' => 'SN-2024-002', 'category' => 'Cables', 'status' => 'available', 'warehouse' => 'Warehouse B', 'location' => 'Rack B3', 'purchase_date' => now()->subMonths(3), 'warranty_expires' => now()->addMonths(21)],
            ['id' => 3, 'name' => 'ONU Huawei HG8245H', 'serial_number' => 'SN-2024-003', 'category' => 'ONU', 'status' => 'in_use', 'warehouse' => 'Warehouse A', 'location' => 'Customer Site', 'purchase_date' => now()->subMonths(4), 'warranty_expires' => now()->addMonths(20)],
            ['id' => 4, 'name' => 'Splitter 1:8 PLC', 'serial_number' => 'SN-2024-004', 'category' => 'Splitter', 'status' => 'available', 'warehouse' => 'Warehouse B', 'location' => 'Rack A1', 'purchase_date' => now()->subMonths(2), 'warranty_expires' => now()->addMonths(22)],
        ]);
    }

    public function getAssetStats(): array
    {
        $assets = $this->getAssets();
        return [
            'total' => $assets->count(),
            'in_use' => $assets->where('status', 'in_use')->count(),
            'available' => $assets->where('status', 'available')->count(),
            'maintenance' => $assets->where('status', 'maintenance')->count(),
            'retired' => $assets->where('status', 'retired')->count(),
        ];
    }

    public function getStatusColor(string $status): string
    {
        return match($status) {
            'in_use' => 'success',
            'available' => 'primary',
            'maintenance' => 'warning',
            'retired' => 'neutral',
            default => 'neutral',
        };
    }

    public function render()
    {
        return view('livewire.inventory.asset-list', [
            'assets' => $this->getAssets(),
            'stats' => $this->getAssetStats(),
        ]);
    }
}
