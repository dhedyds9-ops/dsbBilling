<?php

namespace App\Livewire\Crm\Customer;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\Customer;

class Index extends BaseCrmComponent
{
    public $statusFilter = '';
    public $selectedCustomers = [];
    public $selectAll = false;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'customers';
        $this->filters = ['status' => ''];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedCustomers = $this->getCustomersQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedCustomers = [];
        }
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->perPage = 15;
        $this->resetPage();
    }

    public function bulkDelete(): void
    {
        if (empty($this->selectedCustomers)) return;

        $hasActiveInvoices = Customer::whereIn('id', $this->selectedCustomers)
            ->whereHas('invoices', function($q) {
                $q->whereIn('status', ['unpaid', 'overdue']);
            })
            ->exists();

        if ($hasActiveInvoices) {
            session()->flash('error', 'Beberapa pelanggan tidak dapat dihapus karena masih memiliki tagihan aktif/belum lunas.');
            return;
        }

        $customers = Customer::whereIn('id', $this->selectedCustomers)->get();
        foreach ($customers as $customer) {
            $customer->delete(); // This triggers Eloquent deleted events
        }

        $this->selectedCustomers = [];
        $this->selectAll = false;
        
        session()->flash('success', 'Pelanggan terpilih berhasil dihapus!');
    }

    public function delete($id): void
    {
        $customer = Customer::findOrFail($id);

        $hasActiveInvoices = $customer->invoices()
            ->whereIn('status', ['unpaid', 'overdue'])
            ->exists();

        if ($hasActiveInvoices) {
            session()->flash('error', 'Tidak dapat menghapus pelanggan karena ada tagihan aktif/belum lunas.');
            return;
        }

        $customer->delete();
        session()->flash('success', 'Pelanggan berhasil dihapus!');
    }

    public function toggleStatus($id): void
    {
        $customer = Customer::findOrFail($id);
        $customer->status = $customer->status === 'active' ? 'suspend' : 'active';
        $customer->save();
    }

    public function getCustomersQuery()
    {
        $query = Customer::with([
            'customerServices.serviceProfile',
            'customerServices.service',
            'customerServices.pppoeUser',
            'customerServices.hotspotUser',
            'reseller',
        ]);

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

        return $query;
    }

    public function render()
    {
        $query = $this->getCustomersQuery();

        // KPI
        try {
            $totalCustomers   = Customer::count();
            $activeCustomers  = Customer::where('status', 'active')->count();
            $suspendCustomers = Customer::where('status', 'suspend')->count();
            $newCustomers     = Customer::whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)
                                        ->count();
        } catch (\Exception $e) {
            $totalCustomers = $activeCustomers = $suspendCustomers = $newCustomers = 0;
        }

        $customers = $query
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.crm.customer.index', compact(
            'customers',
            'totalCustomers',
            'activeCustomers',
            'suspendCustomers',
            'newCustomers'
        ));
    }
}
