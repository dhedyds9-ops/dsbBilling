<?php

namespace App\Livewire\CustomerPortal;

use App\Services\CustomerPortal\CustomerDashboardService;
use Livewire\Component;

use Livewire\Attributes\Layout;

#[Layout('layouts.customer-app')]
class Dashboard extends Component
{
    public function render()
    {
        $dashboardService = app(CustomerDashboardService::class);
        $customer = auth()->user()->customer;
        
        if (!$customer) {
            abort(403, 'Profil pelanggan tidak ditemukan.');
        }

        $data = $dashboardService->getDashboardData($customer->id);

        // Explicitly compute primary_service once as SSOT so blade never gets Undefined variable
        // (Blade computes it in @php block too, but this ensures it's bound via view()->with())
        if (!isset($data['primary_service'])) {
            $data['primary_service'] = $data['customer_services']->first() ?? null;
        }
        return view('livewire.customer-portal.dashboard', $data);
    }
}
