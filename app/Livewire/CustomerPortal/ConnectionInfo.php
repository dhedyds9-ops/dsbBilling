<?php

namespace App\Livewire\CustomerPortal;

use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\RadiusAccounting;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.customer-app')]
class ConnectionInfo extends Component
{
    use WithPagination;

    public function render()
    {
        $customer = Customer::where('user_id', auth()->id())->first();
        if (!$customer) {
            abort(403, 'Profil pelanggan tidak ditemukan.');
        }

        // Get all customer services (PPPoE and Hotspot)
        $customerServices = CustomerService::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->pluck('id');

        // Fetch radius accounting sessions linked to these services
        $sessions = RadiusAccounting::whereIn('customer_service_id', $customerServices)
            ->orderBy('acct_start_time', 'desc')
            ->paginate(10);

        return view('livewire.customer-portal.connection-info', compact('sessions'));
    }
}
