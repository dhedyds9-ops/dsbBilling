<?php

namespace App\Livewire\CustomerPortal;

use App\Services\CustomerPortal\CustomerDashboardService;
use Livewire\Component;

use Livewire\Attributes\Layout;

#[Layout('layouts.customer-app')]
class Dashboard extends Component
{
    public $customer;

    public function mount()
    {
        $user = auth()->user();
        $this->customer = $user->customer;
        
        if (!$this->customer) {
            // AUTO-HEAL: Cari pelanggan yang yatim piatu (tidak punya user_id)
            $this->customer = \App\Models\CRM\Customer::where('code', $user->username)
                ->orWhere('phone', $user->whatsapp)
                ->orWhere('email', $user->email)
                ->first();
                
            if ($this->customer && empty($this->customer->user_id)) {
                $this->customer->update(['user_id' => $user->id]);
            } else {
                auth()->logout();
                session()->invalidate();
                session()->regenerateToken();
                
                $targetRoute = \Illuminate\Support\Facades\Route::has('customer.login') ? 'customer.login' : 'login';
                return redirect()->route($targetRoute)->withErrors([
                    'login' => 'Profil pelanggan Anda tidak ditemukan atau belum diproses. Silakan hubungi Admin.'
                ]);
            }
        }
    }

    public function render()
    {
        $dashboardService = app(CustomerDashboardService::class);
        $data = $this->customer ? $dashboardService->getDashboardData($this->customer->id) : [];

        if ($this->customer) {
            $data['customer'] = $this->customer;
        }

        // Explicitly compute primary_service once as SSOT so blade never gets Undefined variable
        if (!isset($data['primary_service']) && isset($data['customer_services'])) {
            $data['primary_service'] = $data['customer_services']->first() ?? null;
        }
        return view('livewire.customer-portal.dashboard', $data);
    }
}

