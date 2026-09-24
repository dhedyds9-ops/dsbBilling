<?php

namespace App\Services\Adapters\Maps;

class MapsAdapterRegistry
{
    protected array $adapters = [];

    public function __construct() {
        $this->adapters = [
            'google' => new GoogleMapsAdapter(config('services.google.maps.key', '')),
            'osm' => new OpenStreetMapAdapter(),
        ];
    }

    public function get(string $name): ?MapsAdapterInterface
    {
        return $this->adapters[$name] ?? null;
    }

    public function default(): MapsAdapterInterface
    {
        return $this->adapters['osm'];
    }
}
