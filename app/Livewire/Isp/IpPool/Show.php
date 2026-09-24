<?php

namespace App\Livewire\Isp\IpPool;

use App\Livewire\AdminComponent;
use App\Models\ISP\IpPool;
use App\Models\Provisioning\IpAllocation;
use App\Models\Provisioning\NetworkProfile;
use Illuminate\Support\Facades\Log;
use Throwable;

class Show extends AdminComponent
{
    public ?int $poolId = null;
    public ?IpPool $pool = null;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'ip-pools';

        $this->pool = IpPool::with([
            'pop',
            'pop.router',
            'createdBy',
            'updatedBy',
        ])->findOrFail($id);
        $this->poolId = $this->pool->id;

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Paket & Layanan'],
            ['label' => 'IP Pool', 'url' => route('isp.ip-pools.index')],
            ['label' => 'Detail'],
        ];
    }

    public function getNetworkProfilesProperty()
    {
        return NetworkProfile::where('ip_pool_id', $this->poolId)
            ->with('router', 'createdBy')
            ->latest()
            ->get();
    }

    public function getIpAllocationsProperty()
    {
        return IpAllocation::where('ip_pool_id', $this->poolId)
            ->with('customerService', 'customerService.customer')
            ->latest()
            ->paginate(10);
    }

    public function getRouterListProperty()
    {
        $routers = collect();
        if ($this->pool && $this->pool->pop && $this->pool->pop->router) {
            $routers->push($this->pool->pop->router);
        }
        $profileRouters = $this->networkProfiles->pluck('router')->filter()->unique('id');
        foreach ($profileRouters as $r) {
            if (!$routers->contains('id', $r->id)) {
                $routers->push($r);
            }
        }
        return $routers;
    }

    public function getSyncLogsProperty()
    {
        return collect([]);
    }

    public function toggleStatus()
    {
        try {
            $this->pool->status = $this->pool->status === 'active' ? 'inactive' : 'active';
            $this->pool->updated_by = auth()->id();
            $this->pool->save();
            $this->pool->refresh();
            session()->flash('success', sprintf('Status diubah menjadi %s.', strtoupper($this->pool->status)));
        } catch (Throwable $e) {
            Log::error('Toggle Status IP Pool Gagal', ['id' => $this->poolId, 'error' => $e->getMessage()]);
            session()->flash('error', 'Gagal ubah status: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $networkProfiles = $this->networkProfiles;
        $ipAllocations = $this->ipAllocations;
        $routerList = $this->routerList;
        $syncLogs = $this->syncLogs;

        return view('livewire.isp.ip-pool.detail', compact(
            'networkProfiles',
            'ipAllocations',
            'routerList',
            'syncLogs'
        ))->layout('layouts.enterprise');
    }
}
