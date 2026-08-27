<?php

namespace App\Services\ISP;

use App\Models\ISP\Odp;
use App\Models\ISP\Onu;
use App\Services\Adapters\Maps\GoogleMapsAdapter;
use App\Services\Adapters\Maps\MapsAdapterRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;

class OdpOccupancyService
{
    public function __construct(protected MapsAdapterRegistry $mapsRegistry)
    {
    }

    public function recalculateAll(): array
    {
        $stats = ['processed' => 0, 'customers' => 0, 'updated' => 0];
        Odp::active()->with(['onus'])->chunk(200, function ($odps) use (&$stats) {
            foreach ($odps as $odp) {
                $this->recalculateOne($odp);
                $stats['processed']++;
                $stats['customers'] += (int)$odp->customerServices()->count();
                $stats['updated']++;
            }
        });
        return $stats;
    }

    public function recalculateOne(Odp $odp): Odp
    {
        DB::transaction(function () use ($odp) {
            $used = Onu::where('odp_id', $odp->id)->whereNotNull('odp_id')->count();
            $activeCust = DB::table('customer_services')
                ->join('onus', 'onus.id', '=', 'customer_services.onu_id')
                ->where('onus.odp_id', $odp->id)
                ->where('customer_services.status', 'active')
                ->count('customer_services.id');

            $odp->used_port_count = max((int)($odp->used_port_count ?? 0), $used, $activeCust);
            if ($odp->exists) {
                $odp->timestamps = false;
                $odp->saveQuietly();
            }
        });
        return $odp->refresh();
    }

    public function geocode(Odp $odp): ?GPSCoordinate
    {
        if (!$odp->address && !($odp->latitude === null || $odp->longitude === null)) {
            return new GPSCoordinate((float)$odp->latitude, (float)$odp->longitude);
        }
        try {
            $maps = $this->mapsRegistry->driver('google');
        } catch (\Throwable) {
            $maps = new GoogleMapsAdapter(config('services.google.maps.key', ''));
        }
        if ($odp->latitude !== null && $odp->longitude !== null) {
            $coord = new GPSCoordinate((float)$odp->latitude, (float)$odp->longitude);
            if (!$odp->address) {
                $addr = $maps->reverseGeocode($coord);
                if ($addr) {
                    $odp->updateQuietly(['address' => $addr]);
                }
            }
            return $coord;
        }
        if ($odp->address) {
            $coord = $maps->geocode($odp->address);
            if ($coord) {
                $odp->updateQuietly([
                    'latitude' => $coord->latitude,
                    'longitude' => $coord->longitude,
                ]);
            }
            return $coord;
        }
        return null;
    }

    public function listForMap(int $radiusKm = 0, ?float $lat = null, ?float $lng = null, array $filters = []): array
    {
        $query = Odp::active()->hasGps()->with(['olt:id,name,ip_address', 'onus:id,odp_id,status']);
        if ($radiusKm > 0 && $lat !== null && $lng !== null) {
            $query->near($lat, $lng, $radiusKm);
        }
        if (!empty($filters['occupancy_min'])) {
            $min = (int)$filters['occupancy_min'];
            $query->havingRaw('(used_port_count / NULLIF(port_count,0)) * 100 >= ?', [$min]);
        }
        $odps = $query->limit(1000)->get();

        $features = [];
        foreach ($odps as $o) {
            $total = max(1, (int)($o->port_count ?? 16));
            $used = (int)($o->used_port_count ?? 0);
            $occ = min(100, (int)(($used / $total) * 100));
            $color = match (true) {
                $occ >= 90 => '#ef4444',
                $occ >= 70 => '#f59e0b',
                $occ >= 40 => '#3b82f6',
                default     => '#22c55e',
            };
            $features[] = [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [(float)$o->longitude, (float)$o->latitude]],
                'properties' => [
                    'id' => $o->id,
                    'code' => $o->code,
                    'name' => $o->name,
                    'address' => $o->address,
                    'split_ratio' => $o->split_ratio,
                    'port_count' => $total,
                    'used_port_count' => $used,
                    'available' => max(0, $total - $used - (int)($o->reserved_port_count ?? 0)),
                    'occupancy_percent' => $occ,
                    'color' => $color,
                    'olt' => $o->olt?->name,
                    'onu_active' => $o->onus->where('status', 'active')->count(),
                ],
            ];
        }
        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }
}
