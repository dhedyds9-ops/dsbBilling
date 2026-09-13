<?php

namespace App\Livewire\Crm;

use App\Livewire\AdminComponent;
use Illuminate\Database\Eloquent\Builder;

class CustomerList extends AdminComponent
{
    public array $filters = [
        'search' => '',
        'status' => 'all',
        'package' => 'all',
        'area' => 'all',
    ];

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 15;
    public array $selected = [];
    public bool $selectAll = false;

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'customers';
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selected = $this->getCustomers()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getCustomers(): \Illuminate\Support\Collection
    {
        // Placeholder - dalam implementasi nyata, fetch dari repository
        return collect([
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'phone' => '+62812345678', 'status' => 'active', 'package' => 'Premium 100 Mbps', 'area' => 'Jakarta Selatan', 'created_at' => now()->subDays(30)],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'phone' => '+62812345679', 'status' => 'active', 'package' => 'Basic 50 Mbps', 'area' => 'Jakarta Barat', 'created_at' => now()->subDays(25)],
            ['id' => 3, 'name' => 'Bob Wilson', 'email' => 'bob@example.com', 'phone' => '+62812345680', 'status' => 'inactive', 'package' => 'Standard 75 Mbps', 'area' => 'Jakarta Timur', 'created_at' => now()->subDays(20)],
        ]);
    }

    public function getCustomerStats(): array
    {
        $customers = $this->getCustomers();
        return [
            'total' => $customers->count(),
            'active' => $customers->where('status', 'active')->count(),
            'inactive' => $customers->where('status', 'inactive')->count(),
            'new_this_month' => $customers->filter(fn($c) => $c['created_at']->isCurrentMonth())->count(),
        ];
    }

    public function exportSelected(): void
    {
        // Export logic
    }

    public function deleteSelected(): void
    {
        // Delete logic
    }

    public function render()
    {
        return view('livewire.crm.customer-list', [
            'customers' => $this->getCustomers(),
            'stats' => $this->getCustomerStats(),
        ]);
    }
}
