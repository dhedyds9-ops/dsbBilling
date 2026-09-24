<?php

namespace App\Integration\Maps\Services;

use App\Integration\Maps\Contracts\MapsAdapterInterface;
use App\Integration\Maps\Drivers\GoogleMapsDriver;
use App\Integration\Maps\Drivers\OpenStreetMapDriver;

class MapsService
{
    protected array $adapters = [];

    public function __construct(
        protected GoogleMapsDriver $googleMaps,
        protected OpenStreetMapDriver $openStreetMap
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
