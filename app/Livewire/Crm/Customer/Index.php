<?php

namespace App\Livewire\Crm\Customer;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\Customer;

class Index extends BaseCrmComponent
{
    public $statusFilter = '';
    public $resellerFilter = '';
    public $packageFilter = '';
    public $serviceTypeFilter = '';
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

    public function updatedResellerFilter()    { $this->resetPage(); }
    public function updatedPackageFilter()     { $this->resetPage(); }
    public function updatedServiceTypeFilter() { $this->resetPage(); }
    public function updatedStatusFilter()      { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resellerFilter = '';
        $this->packageFilter = '';
        $this->serviceTypeFilter = '';
        $this->perPage = 20;
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

    public function impersonate($id)
    {
        $customer = Customer::findOrFail($id);
        if (!$customer->user_id) {
            session()->flash('error', 'Pelanggan ini belum memiliki akun user login portal.');
            return;
        }

        $user = \App\Models\User::find($customer->user_id);
        if (!$user) {
            session()->flash('error', 'Akun user login portal tidak ditemukan.');
            return;
        }

        session(['impersonator_admin_id' => auth()->id()]);
        \Illuminate\Support\Facades\Auth::login($user);

        return redirect()->route('customer-portal.dashboard');
    }

    public function getCustomersQuery()
    {
        $query = Customer::withoutGuests()->with([
            'customerServices.serviceProfile',
            'customerServices.service',
            'customerServices.pppoeUser',
            'customerServices.hotspotUser',
            'customerServices.onu.acsDevice',
            'customerServices.acsDevice',
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

        if (!empty($this->resellerFilter)) {
            if ($this->resellerFilter === 'pusat') {
                $query->whereNull('reseller_id');
            } else {
                $query->where('reseller_id', $this->resellerFilter);
            }
        }

        if (!empty($this->packageFilter)) {
            $query->whereHas('customerServices', function($cs) {
                $cs->where('service_profile_id', $this->packageFilter);
            });
        }

        if (!empty($this->serviceTypeFilter)) {
            if ($this->serviceTypeFilter === 'pppoe') {
                $query->whereHas('customerServices', function($cs) {
                    $cs->whereHas('pppoeUser')
                       ->orWhereHas('serviceProfile', fn($sp) => $sp->whereIn('service_type', ['pppoe', 'broadband', 'dedicated']));
                });
            } elseif ($this->serviceTypeFilter === 'hotspot') {
                $query->whereHas('customerServices', function($cs) {
                    $cs->whereHas('hotspotUser')
                       ->orWhereHas('serviceProfile', fn($sp) => $sp->whereIn('service_type', ['hotspot', 'member_hotspot', 'voucher']));
                });
            }
        }

        return $query;
    }

    public function render()
    {
        $query = $this->getCustomersQuery();

        // KPI (hanya pelanggan reguler, mengecualikan guest voucher)
        try {
            $baseKpi          = Customer::withoutGuests();
            $totalCustomers   = (clone $baseKpi)->count();
            $activeCustomers  = (clone $baseKpi)->where('status', 'active')->count();
            $suspendCustomers = (clone $baseKpi)->where('status', 'suspend')->count();
            $newCustomers     = (clone $baseKpi)->whereMonth('created_at', now()->month)
                                                ->whereYear('created_at', now()->year)
                                                ->count();
        } catch (\Exception $e) {
            $totalCustomers = $activeCustomers = $suspendCustomers = $newCustomers = 0;
        }

        $customers = $query
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $resellers = \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'reseller'))
            ->orderBy('name')
            ->get(['id', 'name']);

        $serviceProfiles = \App\Models\ISP\ServiceProfile::where(function($q) {
                $q->whereNull('status')->orWhere('status', 'active');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'service_type']);

        return view('livewire.crm.customer.index', compact(
            'customers',
            'totalCustomers',
            'activeCustomers',
            'suspendCustomers',
            'newCustomers',
            'resellers',
            'serviceProfiles'
        ));
    }
}
