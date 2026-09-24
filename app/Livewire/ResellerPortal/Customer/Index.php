<?php

namespace App\Livewire\ResellerPortal\Customer;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;
use Livewire\WithPagination;

class Index extends AdminComponent
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $perPage = 15;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'customers.index';
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Pelanggan Saya', 'url' => ''],
        ];
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->perPage = 15;
        $this->resetPage();
    }

    public function render()
    {
        $ownerId = auth()->id();

        $query = Customer::with([
            'customerServices' => function ($query) {
                $query->withoutGlobalScope('branch_isolation')
                      ->with(['serviceProfile', 'service', 'pppoeUser', 'hotspotUser']);
            }
        ])->where(function($q) use ($ownerId) {
            $q->where('reseller_id', $ownerId)
              ->orWhere('created_by', $ownerId);
        });

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        // KPI (Filtered for Reseller)
        try {
            $totalCustomers   = Customer::where(function($q) use ($ownerId) { $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId); })->count();
            $activeCustomers  = Customer::where(function($q) use ($ownerId) { $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId); })->where('status', 'active')->count();
            $suspendCustomers = Customer::where(function($q) use ($ownerId) { $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId); })->where('status', 'suspend')->count();
            $newCustomers     = Customer::where(function($q) use ($ownerId) { $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId); })
                                        ->whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)
                                        ->count();
        } catch (\Exception $e) {
            $totalCustomers = $activeCustomers = $suspendCustomers = $newCustomers = 0;
        }

        $customers = $query
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.reseller-portal.customer.index', compact(
            'customers',
            'totalCustomers',
            'activeCustomers',
            'suspendCustomers',
            'newCustomers'
        ));
    }
}
