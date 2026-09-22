<?php

namespace App\Livewire\Isp\Technician;


use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Provisioning\ProvisionPipeline;

#[Layout('layouts.technician-app')]
class Dashboard extends Component
{
    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Technician Dashboard', 'url' => '#'],
        ];
    }

    public function render()
    {
        $recentInstallations = ProvisionPipeline::with([
                'serviceInstance' => function ($query) {
                    $query->withTrashed()->with(['customerService' => function ($q) {
                        $q->withTrashed()->with('customer');
                    }]);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.isp.technician.dashboard', compact('recentInstallations'));
    }
}
