<?php

namespace App\Services\Adapters\Maps;

class MapsAdapterRegistry
{
    protected array $adapters = [];

    public function __construct(
        protected GoogleMapsAdapter $googleMaps,
        protected OpenStreetMapAdapter $openStreetMap
    ) {
        $this->adapters = [
            'google' => $googleMaps,
            'osm' => $openStreetMap,
        ];
    }

    public function get(string $name): ?MapsAdapterInterface
    {
        return $this->adapters[$name] ?? null;
    }

    public function default(): MapsAdapterInterface
    {
        return $this->openStreetMap;
    }
}
