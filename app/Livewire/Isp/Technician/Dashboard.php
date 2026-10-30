<?php

namespace App\Livewire\Isp\Technician;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Provisioning\ProvisionPipeline;
use App\Models\Support\Ticket;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.technician-app')]
class Dashboard extends Component
{
    public array $breadcrumbs = [];

    private function isAdminOversight(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->hasRole(['administrator', 'superadmin'])
            && strtoupper((string) $user->job_function) !== 'TECHNICIAN';
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Technician Dashboard', 'url' => '#'],
        ];
    }

    public function render()
    {
        $userId = Auth::id();
        $isAdmin = $this->isAdminOversight();

        $scopePipeline = function ($query) use ($userId, $isAdmin) {
            if ($isAdmin) {
                return;
            }

            $query->where(function ($sub) use ($userId) {
                $sub->where('created_by', $userId)
                    ->orWhereHas('serviceInstance', function ($siQ) use ($userId) {
                        $siQ->withTrashed()->where(function ($siSub) use ($userId) {
                            $siSub->where('created_by', $userId)
                                ->orWhereHas('customerService', function ($csQ) use ($userId) {
                                    $csQ->withoutGlobalScopes()->withTrashed()->where('created_by', $userId);
                                });
                        });
                    });
            });
        };

        $countProses = ProvisionPipeline::where($scopePipeline)
            ->whereIn('status', ['pending', 'running', 'in_progress', 'provisioning'])
            ->count();

        $countSelesai = ProvisionPipeline::where($scopePipeline)
            ->whereIn('status', ['completed', 'provisioned', 'active'])
            ->count();

        $countGagal = ProvisionPipeline::where($scopePipeline)
            ->whereIn('status', ['failed', 'cancelled', 'rolled_back'])
            ->count();

        $activeTicketsCount = Ticket::withoutGlobalScopes()
            ->when(!$isAdmin, fn ($q) => $q->where('assigned_to', $userId))
            ->whereIn('status', ['open', 'in_progress', 'pending_customer'])
            ->count();

        $recentInstallations = ProvisionPipeline::where($scopePipeline)
            ->with([
                'serviceInstance' => function ($query) {
                    $query->withTrashed()->with([
                        'customerService' => function ($q) {
                            $q->withoutGlobalScopes()->withTrashed()->with([
                                'customer' => fn ($cq) => $cq->withoutGlobalScopes(),
                            ]);
                        },
                    ]);
                },
            ])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.isp.technician.dashboard', compact(
            'recentInstallations',
            'countProses',
            'countSelesai',
            'countGagal',
            'activeTicketsCount'
        ));
    }
}
