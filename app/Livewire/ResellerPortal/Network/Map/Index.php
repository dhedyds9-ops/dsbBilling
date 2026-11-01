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

        $customers = Customer::withoutGlobalScopes()->with([
            'customerServices' => function($q) {
                $q->withoutGlobalScopes()->with([
                    'onu' => fn($oq) => $oq->withoutGlobalScopes(),
                    'pppoeUser' => fn($pq) => $pq->withoutGlobalScopes(),
                    'hotspotUser' => fn($hq) => $hq->withoutGlobalScopes(),
                ]);
            }
        ])
            ->where(function($q) use ($ownerId) {
                if ($ownerId) {
                    $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
                }
            })
            ->get()
            ->filter(function ($c) {
                $hasCoords = !empty($c->latitude) && !empty($c->longitude) && is_numeric($c->latitude) && is_numeric($c->longitude);
                if ($hasCoords) return true;
                $service = $c->customerServices->first();
                $onu = $service?->onu;
                return !empty($onu?->latitude) && !empty($onu?->longitude) && is_numeric($onu->latitude) && is_numeric($onu->longitude);
            })
            ->map(function ($c) {
                $service = $c->customerServices->firstWhere('status', 'active') ?? $c->customerServices->first();
                $onu = $service?->onu;
                $pppoe = $service?->pppoeUser;
                $hotspot = $service?->hotspotUser;

                if (empty($c->latitude) || empty($c->longitude) || !is_numeric($c->latitude) || !is_numeric($c->longitude)) {
                    if ($onu && !empty($onu->latitude) && !empty($onu->longitude)) {
                        $c->latitude = $onu->latitude;
                        $c->longitude = $onu->longitude;
                    }
                }

                $isOnline = false;
                if ($pppoe && in_array(strtolower($pppoe->status ?? ''), ['active', 'online'])) {
                    $isOnline = true;
                } elseif ($hotspot && in_array(strtolower($hotspot->status ?? ''), ['active', 'online'])) {
                    $isOnline = true;
                } elseif ($service && strtolower($service->status ?? '') === 'active') {
                    $isOnline = true;
                } elseif ($onu && $onu->last_seen_at && $onu->last_seen_at->diffInMinutes(now()) <= 10) {
                    $isOnline = true;
                }

                $c->is_online = $isOnline;
                $c->odp_id = $onu?->odp_id ?? $pppoe?->odp_id ?? $c->odp_id;
                $c->ssid_name = $onu?->wifi_ssid ?: 'WIFI-' . $c->name;
                $c->service_type = $pppoe ? 'PPPoE' : ($hotspot ? 'Hotspot' : 'Internet');

                return $c;
            })
            ->values();

        return view('livewire.reseller-portal.network.map.index', [
            'customers' => $customers,
            'odps' => Odp::forReseller()->get(),
            'odcs' => Odc::forReseller()->get(),
            'olts' => Olt::forReseller()->get(),
        ]);
    }
}
