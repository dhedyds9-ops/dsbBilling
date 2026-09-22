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
            $user = auth()->user();
            // AUTO-HEAL: Cari pelanggan yang yatim piatu (tidak punya user_id)
            $customer = \App\Models\CRM\Customer::where('phone', $user->whatsapp)
                ->orWhere('email', $user->email)
                ->orWhere('name', $user->name)
                ->first();
                
            if ($customer && empty($customer->user_id)) {
                $customer->update(['user_id' => $user->id]);
            } else {
                abort(403, 'Profil pelanggan tidak ditemukan. Pastikan data pendaftaran Anda telah diproses Admin.');
            }
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
