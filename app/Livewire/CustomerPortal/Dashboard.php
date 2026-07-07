<?php

namespace App\Livewire\CustomerPortal;

use App\Services\CustomerPortal\CustomerDashboardService;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(CustomerDashboardService $dashboardService)
    {
        $data = $dashboardService->getDashboardData(Auth::id());
        return view('livewire.customer-portal.dashboard', $data);
    }
}

