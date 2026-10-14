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
        $countProses = ProvisionPipeline::whereIn('status', ['pending', 'running', 'in_progress', 'provisioning'])->count();
        $countSelesai = ProvisionPipeline::whereIn('status', ['completed', 'provisioned', 'active'])->count();
        $countGagal = ProvisionPipeline::whereIn('status', ['failed', 'cancelled', 'rolled_back'])->count();

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

        return view('livewire.isp.technician.dashboard', compact('recentInstallations', 'countProses', 'countSelesai', 'countGagal'));
    }
}
