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
        // Ambil data yang memiliki latitude & longitude
        $olts = Olt::forReseller()->whereNotNull('latitude')->whereNotNull('longitude')->get();
        $odcs = Odc::forReseller()->whereNotNull('latitude')->whereNotNull('longitude')->get();
        $odps = Odp::forReseller()->whereNotNull('latitude')->whereNotNull('longitude')->get();
        
        // Ambil customer yang terikat ke Reseller
        $user = auth()->user();
        $customers = Customer::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where(function($q) use ($user) {
                if ($user->hasRole('reseller')) {
                    $ownerId = $user->getEffectiveResellerId();
                    $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
                }
            })->get();

        return view('livewire.reseller-portal.network.map.index', [
            'olts' => $olts,
            'odcs' => $odcs,
            'odps' => $odps,
            'customers' => $customers,
        ]);
    }
}
