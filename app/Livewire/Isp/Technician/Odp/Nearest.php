<?php

namespace App\Livewire\Isp\Technician\Odp;

use App\Models\ISP\Odp;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.technician-app')]
class Nearest extends Component
{
    public ?float $latitude = null;
    public ?float $longitude = null;
    public int $radius = 5; // Default 5 km
    public string $search = '';
    public string $statusFilter = 'all'; // all, available, full
    public array $odps = [];
    public ?int $selectedOdpId = null;

    public function mount()
    {
        $this->loadNearestOdps();
    }

    public function updatedSearch()
    {
        $this->loadNearestOdps();
    }

    public function updatedRadius()
    {
        $this->loadNearestOdps();
    }

    public function updatedStatusFilter()
    {
        $this->loadNearestOdps();
    }

    public function setLocation($lat, $lng)
    {
        $this->latitude = (float) $lat;
        $this->longitude = (float) $lng;
        $this->loadNearestOdps();
    }

    public function selectOdp(int $id)
    {
        $this->selectedOdpId = $id;
        $target = collect($this->odps)->firstWhere('id', $id);
        if ($target && !empty($target['latitude']) && !empty($target['longitude'])) {
            $this->dispatch('focus-odp', [
                'lat' => (float) $target['latitude'],
                'lng' => (float) $target['longitude'],
                'id' => $id,
                'code' => $target['code'],
            ]);
        }
    }

    public function loadNearestOdps()
    {
        $user = auth()->user();

        $query = Odp::withoutGlobalScopes()->with(['olt', 'branch'])
            ->when($user && $user->branch_id && !$user->isSuperAdmin(), function ($q) use ($user) {
                $q->where(function ($b) use ($user) {
                    $b->where('branch_id', $user->branch_id)
                      ->orWhereNull('branch_id')
                      ->orWhere('branch_id', 1);
                });
            })
            ->where('status', '!=', 'inactive');

        // Pencarian Teks ODP (Kode, Nama, atau Alamat)
        if (!empty(trim($this->search))) {
            $keyword = trim($this->search);
            $query->where(function ($sub) use ($keyword) {
                $sub->where('code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%");
            });
        }

        // Filter Port
        if ($this->statusFilter === 'available') {
            $query->whereRaw('(port_count - used_port_count - reserved_port_count) > 0');
        } elseif ($this->statusFilter === 'full') {
            $query->whereRaw('(port_count - used_port_count - reserved_port_count) <= 0');
        }

        $allOdps = $query->get();

        $hasGps = ($this->latitude !== null && $this->longitude !== null);
        $userLat = $this->latitude;
        $userLng = $this->longitude;

        $results = $allOdps->map(function ($odp) use ($hasGps, $userLat, $userLng) {
            $dist = null;
            if ($hasGps && $odp->latitude && $odp->longitude) {
                $dist = $this->calculateHaversine((float) $userLat, (float) $userLng, (float) $odp->latitude, (float) $odp->longitude);
            }

            return [
                'id' => $odp->id,
                'code' => $odp->code,
                'name' => $odp->name,
                'latitude' => $odp->latitude ? (float) $odp->latitude : null,
                'longitude' => $odp->longitude ? (float) $odp->longitude : null,
                'distance' => $dist !== null ? round($dist, 2) : null,
                'port_count' => (int) $odp->port_count,
                'used_port_count' => (int) $odp->used_port_count,
                'available_port_count' => (int) $odp->availablePortCount,
                'occupancy' => (float) $odp->occupancyPercent,
                'address' => $odp->address ?: 'Alamat belum diisi',
                'status' => $odp->status,
                'olt_name' => $odp->olt?->name ?? 'OLT Default',
            ];
        });

        // Filter radius hanya jika tidak sedang search teks bebas dan ada GPS & radius > 0
        if (empty(trim($this->search)) && $hasGps && $this->radius > 0) {
            $results = $results->filter(function ($item) {
                return $item['distance'] !== null && $item['distance'] <= $this->radius;
            });
        }

        // Urutkan jarak terdekat jika ada GPS, atau urutkan abjad jika tidak ada
        if ($hasGps) {
            $results = $results->sortBy(function ($item) {
                return $item['distance'] ?? 999999;
            });
        } else {
            $results = $results->sortBy('name');
        }

        $this->odps = $results->values()->toArray();

        // Tentukan titik pusat peta default jika belum ada GPS
        if (!$hasGps && count($this->odps) > 0) {
            foreach ($this->odps as $item) {
                if ($item['latitude'] && $item['longitude']) {
                    $this->latitude = $item['latitude'];
                    $this->longitude = $item['longitude'];
                    break;
                }
            }
        }

        $this->dispatch('odps-updated', [
            'odps' => $this->odps,
            'center' => [
                'lat' => $this->latitude ?? -6.200000,
                'lng' => $this->longitude ?? 106.816666,
            ],
            'hasGps' => $hasGps,
        ]);
    }

    private function calculateHaversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public function render()
    {
        return view('livewire.isp.technician.odp.nearest');
    }
}
