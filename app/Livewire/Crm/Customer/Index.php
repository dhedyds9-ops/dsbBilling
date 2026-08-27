<?php

namespace App\Livewire\Crm\Customer;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\Customer\CustomerService;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\HotspotUser;
use Carbon\Carbon;

class Index extends BaseCrmComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'customers';
        $this->filters = ['status' => ''];
    }

    public function export()
    {
        session()->flash('info', 'Export feature will be implemented later!');
    }

    public function render()
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        
        $registrationsThisMonth = CustomerService::where('activated_at', '>=', $monthStart)->count();
        $renewalsThisMonth = 0; // Will implement later
        $isolirCustomers = CustomerService::where('status', 'suspended')->count();
        $disabledAccounts = CustomerService::where('status', 'inactive')->count();

        $query = CustomerService::with(['customer', 'serviceProfile', 'pppoeUser', 'hotspotUser']);

        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('customer', function($cq) {
                    $cq->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('email', 'like', '%' . $this->search . '%')
                       ->orWhere('phone', 'like', '%' . $this->search . '%');
                })->orWhere('username', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        $customerServices = $query->orderBy($this->sortField, $this->sortDirection)
                                  ->paginate($this->perPage);

        return view('livewire.crm.customer.index', compact(
            'customerServices',
            'registrationsThisMonth',
            'renewalsThisMonth',
            'isolirCustomers',
            'disabledAccounts'
        ));
    }
}
