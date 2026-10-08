<?php

namespace App\Livewire\Isp\Technician\Odp;

use App\Models\ISP\Odp;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.technician-app')]
class Nearest extends Component
{
    public float $latitude = -6.200000; // Default Jakarta
    public float $longitude = 106.816666;
    public int $radius = 2; // km
    public $odps = [];

    public function setLocation($lat, $lng)
    {
        $this->latitude = (float) $lat;
        $this->longitude = (float) $lng;
        $this->loadNearestOdps();
    }

    public function loadNearestOdps()
    {
        $this->odps = Odp::with(['olt', 'branch'])
            ->active()
            ->near($this->latitude, $this->longitude, $this->radius)
            ->get()
            ->map(function ($odp) {
                return [
                    'id' => $odp->id,
                    'code' => $odp->code,
                    'name' => $odp->name,
                    'latitude' => $odp->latitude,
                    'longitude' => $odp->longitude,
                    'distance' => round($odp->distance, 2),
                    'port_count' => $odp->port_count,
                    'available_port_count' => $odp->availablePortCount,
                    'occupancy' => $odp->occupancyPercent,
                    'address' => $odp->address,
                ];
            })->toArray();
            
        $this->dispatch('odps-updated', odps: $this->odps);
    }

    public function render() { return view('livewire.isp.technician.odp.nearest'); }
}

