<?php

namespace App\Livewire\ResellerPortal\Network\Map;

use App\Livewire\AdminComponent;
use App\Models\ISP\Olt;
use App\Models\ISP\Odc;
use App\Models\ISP\Odp;
use App\Models\ISP\JointClosure;
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

        $olts = Olt::forReseller()->get()->map(fn($o) => [
            'id' => $o->id,
            'name' => $o->name,
            'code' => $o->code,
            'ip_address' => $o->ip_address ?: ($o->host ?: '-'),
            'model' => $o->model ?: '-',
            'pon_port_count' => (int)($o->pon_port_count ?: ($o->port_count ?: 8)),
            'address' => $o->address ?: '-',
            'latitude' => $o->latitude ? (float)$o->latitude : null,
            'longitude' => $o->longitude ? (float)$o->longitude : null,
            'photos' => array_values(array_filter(array_map(function($p) {
                if (empty($p)) return null;
                if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://') || str_starts_with($p, '/storage/')) return $p;
                return '/storage/' . ltrim($p, '/');
            }, (array)($o->photos ?? [])))),
        ]);

        $odcs = Odc::forReseller()->with(['olt:id,name,latitude,longitude'])->get()->map(fn($o) => [
            'id' => $o->id,
            'name' => $o->name,
            'code' => $o->code,
            'capacity' => (int)($o->port_count ?: ($o->capacity ?: 48)),
            'olt_id' => $o->olt_id,
            'olt_name' => $o->olt?->name ?? 'OLT Default',
            'pon_port' => $o->pon_port ?? '-',
            'address' => $o->address ?: '-',
            'latitude' => $o->latitude ? (float)$o->latitude : null,
            'longitude' => $o->longitude ? (float)$o->longitude : null,
            'photos' => array_values(array_filter(array_map(function($p) {
                if (empty($p)) return null;
                if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://') || str_starts_with($p, '/storage/')) return $p;
                return '/storage/' . ltrim($p, '/');
            }, (array)($o->photos ?? [])))),
        ]);

        $closures = JointClosure::forReseller()->with(['odc:id,name,latitude,longitude'])->get()->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'code' => $c->code,
            'type' => $c->type ?? 'Dome',
            'port_count' => (int)($c->port_count ?: 24),
            'odc_id' => $c->odc_id,
            'odc_name' => $c->odc?->name ?? 'N/A',
            'address' => $c->address ?: '-',
            'latitude' => $c->latitude ? (float)$c->latitude : null,
            'longitude' => $c->longitude ? (float)$c->longitude : null,
            'photos' => array_values(array_filter(array_map(function($p) {
                if (empty($p)) return null;
                if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://') || str_starts_with($p, '/storage/')) return $p;
                return '/storage/' . ltrim($p, '/');
            }, (array)($c->photos ?? [])))),
        ]);

        $odps = Odp::forReseller()->with(['olt:id,name', 'odc:id,name,latitude,longitude'])->get()->map(function($o) {
            $totalP = (int)($o->port_count ?: 8);
            $usedP = (int)($o->used_port_count ?? 0);
            $reservedP = (int)($o->reserved_port_count ?? 0);
            $availP = max(0, $totalP - $usedP - $reservedP);
            return [
                'id' => $o->id,
                'name' => $o->name,
                'code' => $o->code,
                'port_count' => $totalP,
                'used_port_count' => $usedP,
                'available_port_count' => $availP,
                'odc_id' => $o->odc_id,
                'odc_name' => $o->odc?->name ?? 'N/A',
                'address' => $o->address ?: ($o->description ?: '-'),
                'latitude' => $o->latitude ? (float)$o->latitude : null,
                'longitude' => $o->longitude ? (float)$o->longitude : null,
                'photos' => array_values(array_filter(array_map(function($p) {
                    if (empty($p)) return null;
                    if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://') || str_starts_with($p, '/storage/')) return $p;
                    return '/storage/' . ltrim($p, '/');
                }, (array)($o->photos ?? [])))),
            ];
        });

        return view('livewire.reseller-portal.network.map.index', [
            'customers' => $customers,
            'odps' => $odps,
            'closures' => $closures,
            'odcs' => $odcs,
            'olts' => $olts,
        ]);
    }
}
