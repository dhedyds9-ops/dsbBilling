<?php

namespace App\Services\Adapters\Maps;

class MapsAdapterRegistry
{
    protected array $adapters = [];

    public function __construct() {
        $googleKey = (string) (config('services.google.maps.key') ?? '');
        $this->adapters = [
            'google' => new GoogleMapsAdapter($googleKey),
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
