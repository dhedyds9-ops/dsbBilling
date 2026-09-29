<?php

namespace App\Livewire\ResellerPortal\Network\Map;

use App\Livewire\AdminComponent;
use App\Models\ISP\Olt;
use App\Models\ISP\Odc;
use App\Models\ISP\Odp;
use App\Models\CRM\Customer;

class Index extends AdminComponent
{
    public function mount()
    {
        parent::mount();
        
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Network & GIS', 'url' => '#'],
            ['label' => 'Peta Satelit', 'url' => '#'],
        ];
    }

    public function render()
    {
        $user = auth()->user();
        $ownerId = $user->hasRole('reseller') ? $user->getEffectiveResellerId() : null;

        $customers = Customer::with(['customerServices.onu'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where(function($q) use ($ownerId) {
                if ($ownerId) {
                    $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
                }
            })
            ->get()
            ->map(function ($c) {
                $service = $c->customerServices->first();
                $onu = $service?->onu;
                
                // Inject properties expected by map JS
                $c->is_online = $service?->status === 'active';
                $c->odp_id = $onu?->odp_id;
                
                return $c;
            });

        return view('livewire.reseller-portal.network.map.index', [
            'customers' => $customers,
            'odps' => Odp::forReseller()->get(),
            'odcs' => Odc::forReseller()->get(),
            'olts' => Olt::forReseller()->get(),
        ]);
    }
}
